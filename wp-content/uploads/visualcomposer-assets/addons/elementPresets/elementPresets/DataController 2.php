<?php

namespace elementPresets\elementPresets;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class DataController
 * @package elementPresets\elementPresets
 */
class DataController extends Container implements Module
{
    use EventsFilters;

    /**
     * DataController constructor.
     */
    public function __construct()
    {
        /** @see \elementPresets\elementPresets\DataController::addEditorVariables */
        $this->addFilter('vcv:editor:variables vcv:wp:dashboard:variables', 'addEditorVariables');

        $this->addFilter('vcv:ajax:addon:presets:save:adminNonce', 'save');
        $this->addFilter('vcv:ajax:addon:presets:delete:adminNonce', 'delete');
        \VcvEnv::set('VCV_ADDON_ELEMENT_PRESETS_ENABLED', true);
    }

    /**
     * @param $variables
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return array
     */
    protected function addEditorVariables($variables, PostType $postTypeHelper)
    {
        // TODO: Optimize query VC-1904, use pagination via ajax don't use variable at all
        $elementPresets = $postTypeHelper->queryGroupByMetaKey(
            'post_type=vcv_element_presets&post_status=publish&numberposts=-1',
            '_vcv-element-preset-value',
            true
        );

        $values = [];
        $optionsHelper = vchelper('Options');
        $usageCount = $optionsHelper->get('usageCount', []);
        foreach ($elementPresets as $preset) {
            $metaValue = $preset['value'];
            if (isset($metaValue['tag'], $preset['post'], $preset['post']->ID)) {
                $data = [
                    'tag' => $metaValue['tag'],
                    'id' => $preset['post']->ID,
                    'type' => 'elementPreset',
                    'name' => $preset['post']->post_title,
                    'presetData' => $metaValue['value'],
                    'usageCount' => isset($usageCount[ $metaValue['tag'] ]) ? $usageCount[ $metaValue['tag'] ] : 0,
                ];
                $values[] = $data;
            }
        }

        // Output all saved element presets
        $variables[] = [
            'type' => 'constant',
            'key' => 'VCV_ADDON_ELEMENT_PRESETS',
            'value' => $values,
        ];

        return $variables;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return array
     */
    protected function save($response, $payload, Request $requestHelper, PostType $postTypeHelper)
    {
        /**
         * In request should be
         * title of preset
         * attributes value
         * tag
         */
        $tag = $requestHelper->input('vcv-preset-tag');
        $title = $requestHelper->input('vcv-preset-title');
        $inputPresetValue = $requestHelper->input('vcv-preset-value');
        $data = [
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'vcv_element_presets',
            'post_content' => '',
            'meta_input' => [
                '_vcv-element-preset-value' => [
                    'tag' => $tag,
                    'value' => $inputPresetValue,
                ],
            ],
        ];

        /** @var WP_Post $post */
        $postId = $postTypeHelper->create($data);

        $status = false;
        $data = [];
        if (!is_wp_error($postId)) {
            $data = [
                'tag' => $tag,
                'id' => $postId,
                'type' => 'elementPreset',
                'name' => $title,
                'presetData' => $inputPresetValue,
            ];
            $status = true;
        }

        return ['status' => $status, 'data' => $data];
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return array
     */
    protected function delete($response, $payload, Request $requestHelper, PostType $postTypeHelper)
    {
        /**
         * In request should be
         * id of preset
         */
        $status = $postTypeHelper->delete($requestHelper->input('vcv-preset-id'), 'vcv_element_presets');

        return ['status' => $status];
    }
}
