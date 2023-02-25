<?php

namespace dynamicFields\dynamicFields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class DynamicFieldAutocomplete
 * @package dynamicFields\dynamicFields
 */
class DynamicFieldAutocomplete extends Container implements Module
{
    use EventsFilters;

    /**
     * DynamicFieldAutocomplete constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:autocomplete:dynamicPosts:render', 'getSuggestion');
    }


    /**
     * @param $response
     * @param $payload
     *
     * @return array
     */
    protected function getSuggestion($response, $payload)
    {
        global $wpdb;
        $searchValue = $payload['searchValue'];
        $postId = (int)$searchValue;
        $postTypes = get_post_types(['public' => true], 'names');
        $postTypeQuery = [];

        foreach ($postTypes as $postType) {
            $postTypeQuery[] = "post_type='" . esc_sql($postType) . "'";
        }

        $postQuery = implode(" OR ", $postTypeQuery);

        $posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID AS id, post_title AS title FROM {$wpdb->posts} WHERE ( post_status = 'publish' ) AND ({$postQuery}) AND ( ID = '%d' OR post_title LIKE '%%%s%%' ) LIMIT 30",
                $postId > 0 ? $postId : -1,
                stripslashes($searchValue)
            ),
            ARRAY_A
        );
        $response['results'] = [];
        if (is_array($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                $data = [];
                $data['value'] = $post['id'];
                $data['label'] = __('Id', 'visualcomposer') . ': ' . $post['id'] . ' - ' . __('Title', 'visualcomposer') . ': '
                    . $post['title'];
                $response['results'][] = $data;
            }
        }

        return $response;
    }
}
