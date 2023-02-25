<?php

namespace dynamicFields\dynamicFields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'Fields/FieldResponse.php';

use dynamicFields\dynamicFields\Fields\FieldResponse;
use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class DynamicFieldEditorController
 * Response for dynamic field rendering in our editor.
 *
 * @package dynamicFields\dynamicFields
 */
class DynamicFieldEditorController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * DynamicFieldEditorController constructor.
     */
    public function __construct()
    {
        if (version_compare(get_bloginfo('version'), '5.0', '>=')) {
            $this->addFilter(
                'vcv:ajax:getData:adminNonce',
                'getData',
                11
            );
        }
    }

    /**
     * Parse dynamic blocks.
     *
     * @param array $response
     * @param array $blocks
     *
     * @return array
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function parseBlockData($response, $blocks)
    {
        foreach ($blocks as $block) {
            if (!isset($block) || !is_array($block) || !array_key_exists('blockName', $block)) {
                continue;
            }

            $isDynamicBlock = strpos($block['blockName'], 'vcv-gutenberg-blocks/dynamic-field-block') !== false;
            if (!$isDynamicBlock) {
                continue;
            }

            if (isset($block['attrs']) && isset($block['attrs']['currentValue'])
                && $block['attrs']['currentValue'] === '0'
                && empty($block['innerContent'])
            ) {
                $block['innerContent'][] = '0';
            }

            if (array_key_exists('innerContent', $block) && !empty($block['innerContent'])) {
                $index = 0;
                if (isset($block['attrs']['sourceId'])) {
                    $sourceId = $block['attrs']['sourceId'];

                    $response[ $sourceId ] = vcfilter(
                        'vcv:ajax:getDynamicPost:adminNonce',
                        '',
                        [
                            'sourceId' => $sourceId,
                            'vcv-custom-post' => 1,
                        ]
                    );
                }

                foreach ($block['innerContent'] as $chunk) {
                    if (is_string($chunk) && (!empty($chunk) || $chunk === '0')) {
                        $response = $this->call(
                            'parseBlockData',
                            [
                                'response' => $response,
                                'blocks' => parse_blocks($chunk),
                            ]
                        );
                    } elseif (isset($block['innerBlocks'][ $index++ ])) {
                        $response = $this->call(
                            'parseBlockData',
                            [
                                'response' => $response,
                                'blocks' => $block['innerBlocks'],
                            ]
                        );
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Get dynamic data.
     *
     * @param $response
     * @param $payload
     *
     * @return array
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function getData($response)
    {
        if (!isset($response['postData']) ||
            !isset($response['post_content']) ||
            !has_blocks($response['post_content'])) {
            return $response;
        }

        $blocks = parse_blocks($response['post_content']);

        $blockResponse = $this->call(
            'parseBlockData',
            [
                'response' => [],
                'blocks' => $blocks,
            ]
        );

        if (is_array($blockResponse) && !empty($blockResponse)) {
            $response['postFields']['dynamicFieldCustomPostData'] = $blockResponse;
        }

        return $response;
    }
}
