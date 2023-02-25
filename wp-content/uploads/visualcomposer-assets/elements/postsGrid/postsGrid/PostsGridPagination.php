<?php

namespace postsGrid\postsGrid;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use WP_Query;

class PostsGridPagination extends Container implements Module
{
    use EventsFilters;

    protected $tags = [];

    public function __construct()
    {
        if (!defined('VCV_POSTS_GRID_POSTS_GRID_PAGANATION')) {
            /** @see \VisualComposer\Modules\Elements\Grids\PostsGridPagination::addPagination */
            $this->addFilter('vcv:elements:grids:output', 'addPagination');
            /** @see \VisualComposer\Modules\Elements\Grids\PostsGridPagination::updatePosts */
            $this->addFilter('vcv:elements:grids:posts', 'updatePosts', 1);
            define('VCV_POSTS_GRID_POSTS_GRID_PAGANATION', true);
        }
    }

    protected function addPagination($output, $payload)
    {
        if ((int)$payload['atts']['pagination'] && $payload['atts']['source']['value']) {
            $output .= vcelementview(
                'pagination',
                [
                    'payload' => $payload,
                    'tag' => $this->tags[ $payload['atts']['unique_id'] ],
                    'element' => 'postsGrid',
                ]
            );
        }

        return $output;
    }

    protected function updatePosts($posts, $payload, Request $requestHelper)
    {
        // @codingStandardsIgnoreLine
        global $post, $wp_query;
        $id = $payload['atts']['unique_id'];
        if ((int)$payload['atts']['pagination'] && $posts) {
            $page = $requestHelper->exists('vcv-pagination-' . $id) ?
                (int)$requestHelper->input(
                    'vcv-pagination-' . $id
                ) : 1;
            $postIds = [];
            $postType = 'post';
            foreach ($posts as $postData) {
                $postIds[] = $postData->ID;
                // @codingStandardsIgnoreLine
                $postType = $postData->post_type;
            }

            // @codingStandardsIgnoreLine
            $isSearch = isset($wp_query->query['queriedPage']) ? $wp_query->query['queriedPage']->is_search : false;

            if (isset($payload['atts']['source']['tag']) && $payload['atts']['source']['tag'] === 'postsGridDataSourceArchive' && $isSearch) {
                $postType = 'any';
            }

            $perPage = (int)$payload['atts']['pagination_per_page'];
            $paginationQueryArgs = [
                'paged' => $page,
                'orderby' => 'post__in',
                'post_type' => $postType,
                'post_status' => 'publish,inherit',
                'post__in' => array_unique($postIds),
                'posts_per_page' => $perPage,
                'ignore_sticky_posts' => true,
            ];
            $paginationQuery = new WP_Query($paginationQueryArgs);
            $posts = [];
            while ($paginationQuery->have_posts()) {
                $paginationQuery->the_post();
                $posts[] = $post;
            }
            $this->tags[ $id ] = [
                // @codingStandardsIgnoreLine
                'total_pages' => $paginationQuery->max_num_pages,
                'current_page' => $page,
                'per_page' => $perPage,
            ];
            // @codingStandardsIgnoreLine
            if ($page > $paginationQuery->max_num_pages) {
                $posts = [];
            }
            wp_reset_postdata();
        }

        return $posts;
    }
}
