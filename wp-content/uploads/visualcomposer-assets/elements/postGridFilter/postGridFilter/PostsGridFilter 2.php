<?php

namespace postGridFilter\postGridFilter;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

class PostsGridFilter extends Container implements Module
{
    use EventsFilters;

    protected $filterTerms = [];

    public function __construct()
    {
        $this->addFilter('vcv:elements:grids:atts', 'updateFilterQuery');
        $this->addFilter('vcv:elements:grids:output', 'outputFilterHtml');
        $this->addFilter(
            'vcv:editor:variables vcv:editor:variables/postsGrid',
            'addEditorVariable'
        );
    }

    public function getFilterUrl($id, $page)
    {
        $url = add_query_arg('vcv-filter-' . $id, $page);

        // Remove pagination if posts filtered
        return esc_url(remove_query_arg('vcv-pagination-' . $id, $url) . '#grid-' . $id);
    }

    protected function addEditorVariable($variables, $payload)
    {
        $taxonomies = get_taxonomies(
            [
                'public' => true,
            ],
            'objects'
        );
        $values = [];
        foreach ($taxonomies as $taxonomy) {
            $values[] = [
                'label' => $taxonomy->label . ' (' . $taxonomy->name . ')',
                'value' => $taxonomy->name,
            ];
        }

        $variables[] = [
            'type' => 'constant',
            'key' => 'VCV_POSTS_GRID_FILTER_TAXONOMIES',
            'value' => $values,
        ];

        return $variables;
    }

    protected function getFilterTerms($atts)
    {
        if (isset($this->filterTerms[ $atts['unique_id'] ])) {
            return $this->filterTerms[ $atts['unique_id'] ];
        }
        // We decided to query terms first and then rely on terms that have our post grid posts
        $filterBy = $atts['filter_atts']['filter_source'];
        $sourceValue = $atts['source']['value'];
        $sourceValue = rawurldecode(html_entity_decode($sourceValue));
        // Remove limits
        $sourceValue = remove_query_arg('posts_per_page', $sourceValue);
        // Unset filter
        parse_str($sourceValue, $parsedQuery);
        if (!isset($parsedQuery['tax_query'])) {
            $parsedQuery['tax_query'] = [];
        }
        foreach ($parsedQuery['tax_query'] as $taxQueryIndex => $taxQuery) {
            if (is_array($taxQuery) && array_key_exists('post_grid_filter', $taxQuery)) {
                // remove it
                unset($parsedQuery['tax_query'][ $taxQueryIndex ]);
            }
        }

        $sourceValue = http_build_query($parsedQuery, '', '&');
        // Set limits
        $sourceValue = add_query_arg('posts_per_page', '1000', $sourceValue);
        $sourceValue = add_query_arg('fields', 'ids', $sourceValue);
        $postIds = new \WP_Query($sourceValue);
        $this->filterTerms[ $atts['unique_id'] ] = [];
        if (empty($postIds->posts)) {
            return ''; // Nothing to display
        }

        if (!is_wp_error($postIds)) {
            $postIdsValues = $postIds->posts;
            global $wpdb;
            // Select Terms!
            $specificEnabled = $atts['filter_atts']['filter_type_specific_terms']['enabled'];
            $termIdsValues = $atts['filter_atts']['filter_type_specific_terms']['values'];
            if ($specificEnabled && !empty($termIdsValues)) {
                $termQuery = $wpdb->prepare(
                    "select distinct wpt.name as name, wpt.term_id as term_id from {$wpdb->terms} wpt 
            inner join {$wpdb->term_taxonomy} as wptt on wptt.term_id = wpt.term_id and wptt.taxonomy = '%s' and wptt.term_id in (%2s)
        inner join {$wpdb->term_relationships} wtr on wptt.term_taxonomy_id = wtr.term_taxonomy_id where
        wtr.object_id in (%3s)",
                    $filterBy,
                    implode(',', $termIdsValues),
                    implode(',', $postIdsValues)
                );
            } else {
                $termQuery = $wpdb->prepare(
                    "select distinct wpt.name as name, wpt.term_id as term_id from {$wpdb->terms} wpt 
            inner join {$wpdb->term_taxonomy} as wptt on wptt.term_id = wpt.term_id and wptt.taxonomy = '%s'
        inner join {$wpdb->term_relationships} wtr on wptt.term_taxonomy_id = wtr.term_taxonomy_id where
        wtr.object_id in (%2s)",
                    $filterBy,
                    implode(',', $postIdsValues)
                );
            }

            $this->filterTerms[ $atts['unique_id'] ] = $wpdb->get_results($termQuery, ARRAY_A);
        }
        if ($atts['filter_atts']['filter_toggle_all']) {
            array_unshift(
                $this->filterTerms[ $atts['unique_id'] ],
                ['name' => __('All', 'visualcomposer'), 'term_id' => -1]
            );
        }

        return $this->filterTerms[ $atts['unique_id'] ];
    }

    protected function outputFilterHtml($output, $payload)
    {
        // due to string-encoded check boolean like string
        if (empty($payload['atts']['filter']) || $payload['atts']['filter'] === '0'
            || $payload['atts']['filter'] === 'false') {
            return $output;
        }

        $activeTermId = (int)vchelper('Request')->input('vcv-filter-' . $payload['atts']['unique_id'], '-1');
        $filterTerms = $this->getFilterTerms($payload['atts']);
        // ALL Disabled then if $activeTermId is -1 (default) dynamicaly set activeTermId to exact value
        if (!$payload['atts']['filter_atts']['filter_toggle_all']
            && $activeTermId === -1
            && !empty($filterTerms)
            && isset($filterTerms[0]['term_id'])
        ) {
            $activeTermId = (int)$filterTerms[0]['term_id'];
        }

        $filterOutput = vcelementview(
            'filter',
            [
                'payload' => $payload,
                'element' => 'postGridFilter',
                'filterTerms' => $filterTerms,
                'activeTermId' => $activeTermId,
                'filterAtts' => $payload['atts']['filter_atts'],
                'controller' => $this,
                'id' => $payload['atts']['unique_id'],
            ]
        );

        $output = $filterOutput . $output;

        return $output;
    }

    protected function updateFilterQuery($atts, $payload, Request $requestHelper)
    {
        // due to string-encoded check boolean like string
        if (empty($atts['filter']) || $atts['filter'] === '0' || $atts['filter'] === 'false') {
            return $atts;
        }

        // By default once filter enabled we show "ALL" and other items, but "ALL" means all items inside others tabs (summarize)
        $allAvailable = $atts['filter_atts']['filter_toggle_all'];
        $activeTermId = (int)$requestHelper->input('vcv-filter-' . $atts['unique_id'], '-1');

        if ($activeTermId === -1) {
            // No filter selected or selected first one(default one)
            // Check is "ALL" available
            $filterTerms = $this->getFilterTerms($atts);
            if (empty($filterTerms)) {
                return $atts;
            }
            if ($allAvailable) {
                // Modify query to show all by summarize items from others tabs
                // we need Term IDs
                $termsIds = [];
                foreach ($filterTerms as $term) {
                    if ($term['term_id'] > 0) {
                        $termsIds[] = $term['term_id'];
                    }
                }
                $activeTermId = $termsIds;
            } else {
                // Modify activeTermId by exact value (first tab real term id)
                $activeTermId = $filterTerms[0]['term_id'];
            }
        }
        // Now we modifying query with selected filter item
        $value = $atts['source']['value'];
        $value = html_entity_decode($value);
        // Unset filter
        parse_str($value, $parsedQuery);
        if (!isset($parsedQuery['tax_query'])) {
            $parsedQuery['tax_query'] = [];
        }
        $parsedQuery['tax_query'][] = [
            'post_grid_filter' => 1,
            'taxonomy' => $atts['filter_atts']['filter_source'],
            'field' => 'term_id',
            'terms' => $activeTermId,
        ];
        $value = http_build_query($parsedQuery, '', '&');
        $atts['source']['value'] = htmlentities($value, ENT_NOQUOTES, 'UTF-8');

        return $atts;
    }
}
