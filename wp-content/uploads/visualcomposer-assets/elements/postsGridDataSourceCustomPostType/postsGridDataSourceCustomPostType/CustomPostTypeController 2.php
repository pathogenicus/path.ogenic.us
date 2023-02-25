<?php

namespace postsGridDataSourceCustomPostType\postsGridDataSourceCustomPostType;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Request;
use WP_Query;

/**
 * Class CustomPostTypeController
 * @package VisualComposer\Modules\Elements\Grids\DataSource
 */
class CustomPostTypeController extends Container implements Module
{
    use EventsFilters;

    protected $postType;

    protected $taxonomy;

    protected $term;

    /**
     * PostsController constructor.
     */
    public function __construct()
    {
        if (!defined('VCV_POSTS_GRID_CUSTOM_POST_TYPE_CONTROLLER')) {
            /** @see \VisualComposer\Modules\Elements\Grids\DataSource\CustomPostTypeController::queryPosts */
            $this->addFilter('vcv:elements:grids:posts', 'queryPosts');
            $this->addFilter(
                'vcv:editor:variables vcv:editor:variables/postsGridDataSourceCustomPostType',
                'getVariables'
            );
            define('VCV_POSTS_GRID_CUSTOM_POST_TYPE_CONTROLLER', true);
        }
        $this->addFilter('vcv:ajaxForm:render:response', 'renderForm');
    }

    protected function getVariables($variables)
    {
        $key = 'vcvPostsGridSourcePostTypes';
        $value = vchelper('PostType')->getPostTypes(['post', 'page']);

        $variables[] = [
            'key' => $key,
            'value' => $value,
            'type' => 'constant',
        ];

        return $variables;
    }

    /**
     * @param $posts
     * @param $payload
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return array
     */
    protected function queryPosts(
        $posts,
        $payload,
        PostType $postTypeHelper
    ) {
        global $post;

        if (isset($payload['atts']['source'], $payload['atts']['source']['tag'])
            && $payload['atts']['source']['tag'] === 'postsGridDataSourceCustomPostType'
        ) {
            $useQuery = true;
            $args = [];
            // Value:
            $value = html_entity_decode($payload['atts']['source']['value']);
            if (strpos($value, 'post_type=&') !== false) {
                $postTypes = $postTypeHelper->getPostTypes(['post', 'page']);
                $firstPostType = sprintf('post_type=%s&', $postTypes[0]['value']);
                $value = str_replace('post_type=&', $firstPostType, $value);
            }

            parse_str($value, $parameters);
            if (((isset($parameters['taxonomy']) && !empty($parameters['taxonomy']))
                    || (isset($parameters['category'])
                        && !empty($parameters['category'])))
                && $this->call('hasTaxonomy', [$parameters['post_type'], $parameters['taxonomy']])) {
                $this->taxonomy = $parameters['taxonomy'];
                $this->term = (isset($parameters['category'])) ? (int)$parameters['category'] : false;
                if ($this->taxonomy || $this->term) {
                    $postStatus = explode(',', $parameters['post_status']);
                    $useQuery = false;

                    $taxQuery = [
                        'taxonomy' => $this->taxonomy,
                    ];
                    if ($this->term) {
                        $taxQuery['field'] = 'term_id';
                        $taxQuery['terms'] = $this->term;
                    }

                    $args = [
                        'post_type' => $parameters['post_type'],
                        'post_status' => $postStatus,
                        'tax_query' => [$taxQuery],
                    ];

                    if (isset($parameters['posts_per_page']) && !empty($parameters['posts_per_page'])) {
                        $args['posts_per_page'] = $parameters['posts_per_page'];
                    }
                    if (isset($parameters['offset']) && !empty($parameters['[offset'])) {
                        $args['offset'] = $parameters['offset'];
                    }
                }
            }

            if (!$useQuery) {
                $paginationQuery = new WP_Query($args);
            } else {
                $paginationQuery = new WP_Query($value);
            }

            $newPosts = [];
            while ($paginationQuery->have_posts()) {
                $paginationQuery->the_post();
                $newPosts[] = $post;
                wp_reset_postdata();
            }
            $posts = array_merge(
                $posts,
                $newPosts
            );
        }

        return $posts;
    }

    protected function renderForm($response, $payload, Request $requestHelper)
    {
        $element = $payload['element'];
        if ($payload['action'] === 'vcv:customPostTypeTerms:form') {
            $postType = (isset($element['attsPostType'])) ? $element['attsPostType'] : '';
            $termsElement = (isset($element['attsPostTypeTerms'])) ? $element['attsPostTypeTerms'] : false;
            $currentTaxonomy = (isset($termsElement['vcvCustomPostTypeTaxonomy']) && ($this->postType !== '' && $this->postType !== $postType))
                ? $termsElement['vcvCustomPostTypeTaxonomy'] : '';
            $currentCategory = (isset($termsElement['vcvCustomPostTypeCategory'])) ? (int)$termsElement['vcvCustomPostTypeCategory'] : '';
            $taxonomies = vchelper('PostType')->getCustomPostTaxonomies($postType);
            $form = '<select name="vcvCustomPostTypeTaxonomy">';
            $form .= '<option value="">' . __('Select taxonomy', 'visualcomposer') . '</option>';
            $isTaxFound = false;
            if (!empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy) {
                    $selected = '';
                    if ($taxonomy->name === $currentTaxonomy) {
                        $selected = 'selected';
                        $isTaxFound = true;
                    }
                    $form .= '<option value="' . $taxonomy->name . '" ' . $selected . '>' . $taxonomy->label
                        . '</option>';
                }
            }
            $form .= '</select>';

            if ($currentTaxonomy && $isTaxFound) {
                $categories = vchelper('PostType')->getCustomPostCategories($currentTaxonomy);
                if (!empty($categories)) :
                    $form .= '<span class="vcv-ui-form-group-heading">' . __('Select category', 'visualcomposer') . '</span>';
                    $form .= '<select name="vcvCustomPostTypeCategory">';
                    $form .= '<option value="">' . __('Select category', 'visualcomposer') . '</option>';
                    foreach ($categories as $category) :
                        $selected = ($category['value'] === $currentCategory && $taxonomy !== '') ? 'selected' : '';
                        $form .= '<option value="' . $category['value'] . '" ' . $selected . '>' . $category['label']
                            . '</option>';
                    endforeach;
                    $form .= '</select>';
                endif;
            }

            $this->postType = $postType;

            $response['html'] = $form;
        }

        return $response;
    }

    protected function hasTaxonomy($postType, $taxonomy = null)
    {
        if (empty($postType)) {
            return false;
        }
        $taxonomies = get_object_taxonomies($postType);
        if (empty($taxonomy)) {
            $taxonomy = get_query_var('taxonomy');
        }

        return in_array($taxonomy, $taxonomies);
    }
}
