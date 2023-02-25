<?php

namespace layoutPostList\layoutPostList;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use stdClass;
use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Url;
use WP_Post;

/**
 * Class LayoutPostListController
 * @package VisualComposer\Modules\Elements\Grids
 */
class LayoutPostListController extends Container implements Module
{
    use EventsFilters;

    /**
     * LayoutPostListController constructor.
     */
    public function __construct()
    {
        $this->addFilter(
            'vcv:elements:grid_item_template:variable:*',
            'replaceDummyVariable',
            100
        );

        $this->addFilter(
            'vcv:elements:grids:posts',
            'replaceQueryPosts',
            10
        );
    }

    /**
     * Check is current data source option is "Dynamic WP Query" and we on layout page.
     *
     * @param array $payload
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @return bool
     */
    protected function isLayoutPostsGridDataSourceArchive($payload, Request $requestHelper)
    {
        $sourceId = $requestHelper->input('vcv-source-id');

        $isLayouts = !empty($sourceId) && get_post($sourceId) && get_post_type($sourceId) === 'vcv_layouts';

        $isPostsGridDataSourceArchive = !empty($payload['source']['tag']) &&
            $payload['source']['tag'] === 'postsGridDataSourceArchive';

        if ($isLayouts && $isPostsGridDataSourceArchive) {
            return true;
        }

        return false;
    }

    /**
     * Substitute grid shortcode query posts with posts contain dummy data.
     *
     * @param array $posts
     * @param array $payload
     *
     * @return mixed
     */
    protected function replaceQueryPosts($posts, $payload)
    {
        if (!$this->call('isLayoutPostsGridDataSourceArchive', $payload)) {
            return $posts;
        }

        return $this->call('generateDummyPosts', $payload);
    }

    /**
     * Substitute grid shortcode variables with variables contain dummy data.
     *
     * @param mixed $response
     * @param array $payload
     * @param \VisualComposer\Helpers\Url $urlHelper
     *
     * @return mixed
     */
    protected function replaceDummyVariable($response, $payload, Url $urlHelper)
    {
        if ($payload['post']->ID !== -99) {
            return $response;
        }

        $dummyVariables = [
            'featured_image_url' => $urlHelper->assetUrl('images/post-image-placeholder.png'),
            'post_category_with_delimiter' => __(' · Category', 'visualcomposer'),
            'post_category_link_with_delimiter' => sprintf(
                ' · <a href="#">%s</a>',
                __('Category', 'visualcomposer')
            ),
            'post_tags_links' => sprintf(
                '<a href="#">%s</a>, <a href="#">%s</a>, <a href="#">%s</a>',
                __('First tag', 'visualcomposer'),
                __('Second tag', 'visualcomposer'),
                __('Third tag', 'visualcomposer')
            ),
            'post_tags' => __('First tag, Second Tag, Third tag', 'visualcomposer'),
            'post_category' => __('Category', 'visualcomposer'),
        ];

        $key = $payload['key'];
        if (array_key_exists($key, $dummyVariables)) {
            $response = $dummyVariables[ $key ];
        }

        return $response;
    }

    /**
     * Dummy query posts generator.
     *
     * @param array $payload
     *
     * @return array
     */
    protected function generateDummyPosts($payload)
    {
        // @codingStandardsIgnoreStart
        $post_id = -99;
        $post = new stdClass();
        $post->ID = $post_id;
        // get wordpress current user id
        $current_user = wp_get_current_user();
        $post->post_author = $current_user->ID;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
        $post->post_title = 'Post title';
        $post->post_content = 'This is a sample excerpt placeholder that will be replaced with the actual content.';
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_name = 'fake-page-' . rand(1, 99999);
        $post->post_type = 'page';
        $post->filter = 'raw';
        // @codingStandardsIgnoreEnd

        $postNumber = 5;
        if ($payload['pagination']) {
            $postNumber = $payload['pagination_per_page'];
        }

        $posts = [];
        for ($i = 0; $i < $postNumber; $i++) {
            $posts[] = new WP_Post($post);
        }

        return $posts;
    }
}
