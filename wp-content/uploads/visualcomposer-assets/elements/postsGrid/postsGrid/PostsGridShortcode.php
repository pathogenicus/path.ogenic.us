<?php

namespace postsGrid\postsGrid;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostsGridPostIterator;
use VisualComposer\Modules\Elements\Traits\AddShortcodeTrait;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class PostsGridShortcode extends Container implements Module
{
    use AddShortcodeTrait;
    use WpFiltersActions;

    protected $shortcodeTag = 'vcv_posts_grid';

    /**
     * PostsGridController constructor.
     */
    public function __construct()
    {
        if (!defined('VCV_POSTS_GRID_POSTS_GRID_SHORTCODE')) {
            /** @see \VisualComposer\Modules\Elements\Grids\PostsGridShortcode::render */
            $this->addShortcode($this->shortcodeTag, 'render');
            define('VCV_POSTS_GRID_POSTS_GRID_SHORTCODE', true);
        }
    }

    /**
     * @param $atts
     * @param $content
     * @param $tag
     *
     * @return string
     */
    protected function render($atts, $content, $tag)
    {
        // Build Query from $atts
        $atts = shortcode_atts(
            [
                'unique_id' => '',
                'source' => '',
                'pagination' => '',
                'pagination_color' => '',
                'pagination_per_page' => '',
                'excerpt' => 0,
                'excerpt_length' => '',
                'filter' => 0,
                'filter_atts' => '',
            ],
            $atts
        );

        $atts['source'] = json_decode(rawurldecode($atts['source']), true);
        $atts['filter_atts'] = json_decode(rawurldecode($atts['filter_atts']), true);

        $atts = vcfilter(
            'vcv:elements:grids:atts',
            $atts,
            [
                'tag' => $tag,
            ]
        );
        $posts = vcfilter(
            'vcv:elements:grids:posts',
            [],
            [
                'atts' => $atts,
                'tag' => $tag,
            ]
        );

        $output = $this->getPostOutput($posts, $content, $tag, $atts);

        // Add excerpt length limit control. Default 55 by WordPress
        $defaultExcerptLength = apply_filters('excerpt_length', '55');
        $excerptLength = $atts['excerpt'] ? !empty($atts['excerpt_length']) ? $atts['excerpt_length'] : $defaultExcerptLength : $defaultExcerptLength;

        $excerptLengthFilterCallback = $this->wpAddFilter('excerpt_length', function () use ($excerptLength) {
            return (int)$excerptLength;
        }, 999);
        $excerptMoreFilterCallback = $this->wpAddFilter('excerpt_more', function () {
            return '&hellip;';
        }, 999);


        // Remove the length limit control after rendering post grid
        $this->wpRemoveFilter('excerpt_length', $excerptLengthFilterCallback);
        $this->wpRemoveFilter('excerpt_more', $excerptMoreFilterCallback);

        return $output;
    }

    /**
     * Get filter content output.
     *
     * @param array $posts
     * @param string $content
     * @param string $tag
     * @param array $atts
     *
     * @return mixed|string|null
     */
    protected function getPostOutput($posts, $content, $tag, $atts)
    {
        $output = '';

        if (is_array($posts) && !count($posts)) {
            $filterTaxonomy =
                isset($atts['filter_atts']['filter_source']) ? $atts['filter_atts']['filter_source'] : '';
            $postType = $this->getPostTypeFromFilterSource($atts);
            $taxonomies = get_object_taxonomies($postType);
            $terms = get_terms($filterTaxonomy);
            $requestHelper = vchelper('Request');
            $isEditor = $requestHelper->input('vcv-action') == 'elements:posts_grid:adminNonce';

            $isFilter = empty($atts['filter']) || $atts['filter'] === 'false' ? false : true;

            if ($isFilter && !in_array($filterTaxonomy, $taxonomies)) {
                if ($isEditor) {
                    $output = __('There are no taxonomies available for this post type.', 'visualcomposer');
                }
            } elseif ($isFilter && empty($terms)) {
                if ($isEditor) {
                    $output = __('There are no terms available for taxonomy.', 'visualcomposer');
                }
            } elseif ($this->isEmptySearch()) {
                $output = sprintf(
                    '<div class="vce-posts-grid-no-result">%s</div>',
                    __('Nothing Found', 'visualcomposer')
                );
            }
        } else {
            $content = vcfilter(
                'vcv:elements:grids:content',
                $content,
                ['atts' => $atts]
            );

            $postsGridPostIteratorHelper = vchelper('PostsGridPostIterator');
            $postsOutput = $postsGridPostIteratorHelper->loopPosts($posts, $content);

            $output = sprintf('<div class="vce-posts-grid-list">%s</div>', $postsOutput);

            $output = vcfilter(
                'vcv:elements:grids:output',
                $output,
                [
                    'atts' => $atts,
                    'tag' => $tag,
                ]
            );
        }

        return $output;
    }

    /**
     * Check if request is empty search.
     *
     * @return bool
     */
    protected function isEmptySearch()
    {
        // @codingStandardsIgnoreLine
        global $wp_query;
        // @codingStandardsIgnoreLine
        if (!empty($wp_query->query['queriedPage'])) {
            // @codingStandardsIgnoreLine
            $queriedPage = $wp_query->query['queriedPage'];

            // @codingStandardsIgnoreLine
            if ($queriedPage->is_search) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get post type from get query filter request.
     *
     * @param array $atts
     *
     * @return string
     */
    protected function getPostTypeFromFilterSource($atts)
    {
        $postType = '';

        if (empty($atts['source']['value'])) {
            return $postType;
        }

        $query = explode('&amp;', $atts['source']['value']);

        foreach ($query as $request) {
            if (str_contains($request, 'post_type=')) {
                $postType = str_replace('post_type=', '', $request);
            }
        }

        return $postType;
    }
}
