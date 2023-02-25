<?php

namespace postsGridDataSourcePage\postsGridDataSourcePage;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class PagesController
 * @package VisualComposer\Modules\Elements\Grids\DataSource
 */
class DynamicArchiveController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * PostsController constructor.
     */
    public function __construct()
    {
        if (!defined('VCV_POSTS_GRID_DYNAMIC_ARCHIVE_CONTROLLER')) {
            $this->addFilter('vcv:elements:grids:posts', 'queryPosts');
            define('VCV_POSTS_GRID_DYNAMIC_ARCHIVE_CONTROLLER', true);
        }
    }

    /**
     * @param $posts
     * @param $payload
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return array
     */
    protected function queryPosts($posts, $payload, PostType $postTypeHelper)
    {
        if (!isset($payload['atts']['source'], $payload['atts']['source']['tag'])
            || $payload['atts']['source']['tag'] !== 'postsGridDataSourceArchive'
        ) {
            return $posts;
        }

        // @codingStandardsIgnoreStart
        global $wp_the_query;
        $wpQuery = $wp_the_query;

        $taxonomyData = isset($wpQuery->query['taxonomyData']) ? $wpQuery->query['taxonomyData'] : false;
        $queriedPage = isset($wpQuery->query['queriedPage']) ? $wpQuery->query['queriedPage'] : false;
        // @codingStandardsIgnoreEnd

        $gridQuery = html_entity_decode($payload['atts']['source']['value']);
        $taxonomies = get_taxonomies(['_builtin' => false, 'public' => true]);

        // @codingStandardsIgnoreStart
        if (isset($queriedPage->query_vars['taxonomy'])
            && in_array(
            $queriedPage->query_vars['taxonomy'],
                $taxonomies
            )
            && $queriedPage->is_archive) {
            $gridQuery = $this->taxonomyReplace($gridQuery, $taxonomyData);
        } elseif (isset($queriedPage->is_author) && $queriedPage->is_author !== false) {
            $gridQuery .= '&author=' . $queriedPage->query_vars['author'] . '';
        } elseif (isset($queriedPage->is_search) && $queriedPage->is_search !== false) {
            $gridQuery = str_replace('post_type=post', 'post_type=any', $gridQuery);
            $gridQuery .= '&s=' . $queriedPage->query_vars['s'] . '';
        } elseif (isset($wpQuery->query['isShop']) && $wpQuery->query['isShop'] !== false) {
            $gridQuery = html_entity_decode(str_replace('post_type=post&', 'post_type=product&', $gridQuery));
        } elseif (isset($taxonomyData) && $taxonomyData !== false) {
            $gridQuery .= '&tax_query[0][taxonomy]=' . $taxonomyData->taxonomy
                . '&tax_query[0][field]=id&tax_query[0][terms]=' . $taxonomyData->term_id . '';
        } elseif (isset($queriedPage->is_date) && $queriedPage->is_date !== false) {
            $gridQuery = $this->dateReplace($gridQuery, $queriedPage);
        }
        // @codingStandardsIgnoreEnd

        $undoInject = $this->wpAddAction('pre_get_posts', 'undoInject404PageStatus');

        $posts = array_merge(
            $posts,
            $postTypeHelper->query(html_entity_decode($gridQuery))
        );

        remove_action('pre_get_posts', $undoInject);

        return $posts;
    }

    /**
     * We should override 404 page global post statuses
     *
     * @see \VisualComposer\Modules\Editors\PageEditable\Controller::inject404Page
     *
     * @param $wpQuery
     */
    protected function undoInject404PageStatus($wpQuery)
    {
        $wpQuery->query['post_status'] = ['publish'];
        // @codingStandardsIgnoreLine
        $wpQuery->query_vars['post_status'] = ['publish'];
        // @codingStandardsIgnoreEnd
    }

    // Replace grid query with taxonomy data
    protected function taxonomyReplace($gridQuery, $taxonomyData)
    {
        $gridQuery = html_entity_decode(str_replace('post_type=post&', '', $gridQuery));
        // @codingStandardsIgnoreLine
        $gridQuery .= '&tax_query[0][taxonomy]=' . $taxonomyData->taxonomy . '&tax_query[0][field]=id&tax_query[0][terms]=' . $taxonomyData->term_id . '';

        $postTypes = get_post_types(array('public' => true), 'names');
        $postTypeQueryCounter = 0;
        foreach ($postTypes as $postType) {
            $gridQuery .= '&post_type[' . $postTypeQueryCounter . ']=' . $postType . '';
            $postTypeQueryCounter++;
        }

        return $gridQuery;
    }

    // Replace grid query with date data for date archives
    protected function dateReplace($gridQuery, $queriedPage)
    {
        // Set year
        if (isset($queriedPage->query['year'])) {
            $gridQuery .= '&date_query[0][year]=' . $queriedPage->query['year'];
        }

        // Set month
        if ($queriedPage->query['monthnum']) {
            $gridQuery .= '&date_query[0][month]=' . $queriedPage->query['monthnum'];
        }

        return $gridQuery;
    }
}
