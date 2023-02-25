<?php

namespace dynamicFields\dynamicFields\Fields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'FieldResponse.php';

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class Meta
 * @package dynamicFields\dynamicFields\Fields
 */
class Meta extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * Meta constructor.
     */
    public function __construct()
    {
        /** @see \dynamicFields\dynamicFields\Fields\Meta::addMetaCustomFields */
        $this->addFilter('vcv:editor:data:postFields', 'addMetaCustomFields');

        /** @see \dynamicFields\dynamicFields\Fields\Meta::addOtherField */
        $this->addFilter('vcv:editor:data:postFields', 'addOtherField', 100);

        /** @see \dynamicFields\dynamicFields\Fields\Meta::addMetaCustomFieldsData */
        $this->addFilter('vcv:editor:data:postData', 'addMetaCustomFieldsData');

        /** @see \dynamicFields\dynamicFields\Fields\Meta::renderMeta */
        $this->addFilter('vcv:dynamic:value:customMetaField:*', 'renderMeta');
    }

    /**
     * Add custom meta fields for string/htmleditor attributes
     *
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addMetaCustomFields($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        //@codingStandardsIgnoreLine
        if (isset($post) && $post->post_status !== 'trash') {
            $postMeta = get_post_meta($sourceId);
            foreach ($postMeta as $key => $value) {
                if (strpos($key, '_') !== 0 && strpos($key, 'vcv') !== 0) {
                    $metaValue = $value[0];
                    if (!is_serialized($metaValue)) {
                        $response['string']['meta']['group']['values'][] = [
                            'value' => 'customMetaField:' . $key,
                            'label' => $key,
                        ];
                        $response['htmleditor']['meta']['group']['values'][] = [
                            'value' => 'customMetaField:' . $key,
                            'label' => $key,
                        ];
                        $response['inputSelect']['meta']['group']['values'][] = [
                            'value' => 'customMetaField:' . $key,
                            'label' => $key,
                        ];
                        $response['url']['meta']['group']['values'][] = [
                            'value' => 'customMetaField:' . $key,
                            'label' => $key,
                        ];
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Add custom meta fields for string/htmleditor attributes
     *
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addOtherField($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        //@codingStandardsIgnoreLine
        if (isset($post) && $post->post_status !== 'trash') {
            $response['string']['meta']['group']['values'][] = [
                'value' => 'customMetaField::',
                'label' => esc_html__('Other', 'visualcomposer'),
            ];
            $response['htmleditor']['meta']['group']['values'][] = [
                'value' => 'customMetaField::',
                'label' => esc_html__('Other', 'visualcomposer'),
            ];
            $response['inputSelect']['meta']['group']['values'][] = [
                'value' => 'customMetaField::',
                'label' => esc_html__('Other', 'visualcomposer'),
            ];
            $response['url']['meta']['group']['values'][] = [
                'value' => 'customMetaField::',
                'label' => esc_html__('Other', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * Add post data response with custom meta fields values
     *
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function addMetaCustomFieldsData($response, $payload)
    {
        $sourceId = false;
        if (isset($payload['sourceId'])) {
            $sourceId = $payload['sourceId'];
        }
        if (isset($payload['atts']['sourceId'])) {
            $sourceId = $payload['atts']['sourceId'];
        }
        $post = get_post($sourceId);
        //@codingStandardsIgnoreLine
        if (!isset($post) || $post->post_status === 'trash') {
            return $response;
        }
        $postMeta = get_post_meta($post->ID);
        foreach ($postMeta as $key => $value) {
            $metaValue = $value[0];
            if (!is_serialized($metaValue)) {
                $response[ 'customMetaField:' . $key ] = $metaValue;
            }
        }

        return $response;
    }

    /**
     * Render custom meta fields + private meta fields
     *
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function renderMeta($response, $payload)
    {
        if (!isset($payload['atts'], $payload['atts']['value'])) {
            return $response;
        }

        $value = preg_replace('/customMetaField::?/', '', $payload['atts']['value']);
        if (empty($value)) {
            $response = $this->parseResponse($payload['atts']['currentValue'], '', $response);
        } else {
            $sourceId = false;
            if (isset($payload['atts']['sourceId'])) {
                $sourceId = $payload['atts']['sourceId'];
            }

            $post = get_post($sourceId);
            //@codingStandardsIgnoreLine
            if (!isset($post) || $post->post_status === 'trash') {
                return $response;
            }

            $postMetaResult = vcfilter(
                'vcv:addon:dynamicFields:fields:meta:renderMeta',
                get_post_meta($post->ID, $value, true),
                ['postId' => $post->ID, 'metaKey' => $value]
            );

            if (empty($response)) {
                return $postMetaResult;
            }
            if (is_string($postMetaResult)) {
                $response = $this->parseResponse($payload['atts']['currentValue'], $postMetaResult, $response);
            }
        }

        return $response;
    }
}
