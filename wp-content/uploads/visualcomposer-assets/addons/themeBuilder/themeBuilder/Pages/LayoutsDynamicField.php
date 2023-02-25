<?php

namespace themeBuilder\themeBuilder\pages;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

class LayoutsDynamicField extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:editor:data:postFields', 'addCategoryTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addCategoryDescription');
        $this->addFilter('vcv:editor:data:postFields', 'addSearchTerm');
        $this->addFilter('vcv:editor:data:postFields', 'addTagTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addTagDescription');
        $this->addFilter('vcv:editor:data:postFields', 'addProductCategoryTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addProductCategoryDescription');
        $this->addFilter('vcv:editor:data:postFields', 'addProductTagTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addProductTagDescription');

        $this->addFilter('vcv:dynamic:value:category_title', 'taxonomyTitle');
        $this->addFilter('vcv:dynamic:value:tag_title', 'taxonomyTitle');
        $this->addFilter('vcv:dynamic:value:category_description', 'taxonomyDescription');
        $this->addFilter('vcv:dynamic:value:tag_description', 'taxonomyDescription');
        $this->addFilter('vcv:dynamic:value:search_term', 'searchTerm');
        $this->addFilter('vcv:dynamic:value:product_category_title', 'taxonomyTitle');
        $this->addFilter('vcv:dynamic:value:product_category_description', 'taxonomyDescription');
        $this->addFilter('vcv:dynamic:value:product_tag_title', 'taxonomyTitle');
        $this->addFilter('vcv:dynamic:value:product_tag_description', 'taxonomyDescription');
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addCategoryTitle($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'category_title',
                'label' => esc_html__('Category Title', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'category_title',
                'label' => esc_html__('Category Title', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'category_title',
                'label' => esc_html__('Category Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addCategoryDescription($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'category_description',
                'label' => esc_html__('Category Description', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'category_description',
                'label' => esc_html__('Category Description', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'category_description',
                'label' => esc_html__('Category Description', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addSearchTerm($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'search_term',
                'label' => esc_html__('Search Term', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'search_term',
                'label' => esc_html__('Search Term', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'search_term',
                'label' => esc_html__('Search Term', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addTagTitle($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'tag_title',
                'label' => esc_html__('Tag Title', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'tag_title',
                'label' => esc_html__('Tag Title', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'tag_title',
                'label' => esc_html__('Tag Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addTagDescription($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'tag_description',
                'label' => esc_html__('Tag Description', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'tag_description',
                'label' => esc_html__('Tag Description', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'tag_description',
                'label' => esc_html__('Tag Description', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addProductCategoryTitle($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && post_type_exists('product')
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'product_category_title',
                'label' => esc_html__('Product Category Title', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'product_category_title',
                'label' => esc_html__('Product Category Title', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'product_category_title',
                'label' => esc_html__('Product Category Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addProductCategoryDescription($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && post_type_exists('product')
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'product_category_description',
                'label' => esc_html__('Product Category Description', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'product_category_description',
                'label' => esc_html__('Product Category Description', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'product_category_description',
                'label' => esc_html__('Product Category Description', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addProductTagTitle($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && post_type_exists('product')
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'product_tag_title',
                'label' => esc_html__('Product Tag Title', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'product_tag_title',
                'label' => esc_html__('Product Tag Title', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'product_tag_title',
                'label' => esc_html__('Product Tag Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addProductTagDescription($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if (
            //@codingStandardsIgnoreLine
        (isset($post) && $post->post_type === 'vcv_layouts'
            //@codingStandardsIgnoreLine
            && post_type_exists('product')
            //@codingStandardsIgnoreLine
            && $post->post_status !== 'trash')) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'product_tag_description',
                'label' => esc_html__('Product Tag Description', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'product_tag_description',
                'label' => esc_html__('Product Tag Description', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'product_tag_description',
                'label' => esc_html__('Product Tag Description', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function taxonomyTitle($response, $payload)
    {
        // @codingStandardsIgnoreStart
        global $wp_query;
        $wpQuery = $wp_query;
        // @codingStandardsIgnoreEnd
        $atts = $payload['atts'];

        if (isset($atts['currentValue'])) {
            $taxonomyData = $wpQuery->query['taxonomyData'];
            $taxonomyArray = ['category', 'post_tag', 'product_cat', 'product_tag'];
            if (isset($taxonomyData) && in_array($taxonomyData->taxonomy, $taxonomyArray)) {
                $name = $taxonomyData->name;
                $response = vcfilter('vcv:addon:dynamicField:parseResponse', $response, [
                    'name' => $name,
                    'value' => $atts['currentValue']
                ]);
            }
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function taxonomyDescription($response, $payload)
    {
        // @codingStandardsIgnoreStart
        global $wp_query;
        $wpQuery = $wp_query;
        // @codingStandardsIgnoreEnd
        $atts = $payload['atts'];

        if (isset($atts['currentValue'])) {
            $taxonomyData = $wpQuery->query['taxonomyData'];
            $taxonomyArray = ['category', 'tag', 'product_cat', 'product_tag'];
            if (isset($taxonomyData) && in_array($taxonomyData->taxonomy, $taxonomyArray)) {
                $description = $taxonomyData->description;
                $response = vcfilter('vcv:addon:dynamicField:parseResponse', $response, [
                    'name' => $description,
                    'value' => $atts['currentValue']
                ]);
            }
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function searchTerm($response, $payload)
    {
        // @codingStandardsIgnoreStart
        global $wp_query;
        $wpQuery = $wp_query;
        // @codingStandardsIgnoreEnd
        $queriedPage = $wpQuery->query['queriedPage'];
        $atts = $payload['atts'];

        if (isset($atts['currentValue'])) {
            $query = $queriedPage->query;
            $searchedKey = $query['s'];
            $response = vcfilter('vcv:addon:dynamicField:parseResponse', $response, [
                'name' => $searchedKey,
                'value' => $atts['currentValue']
            ]);
        }

        return $response;
    }
}
