<?php

namespace popupBuilder\popupBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class TemplatesController
 * @package popupBuilder\popupBuilder
 */
class TemplatesController extends Container implements Module
{
    use EventsFilters;

    /**
     * TemplatesController constructor.
     */
    public function __construct()
    {
        /** @see \popupBuilder\popupBuilder\TemplatesController::savePopupId */
        $this->addFilter('vcv:editorTemplates:template:type', 'setTemplateType');

        /** @see \popupBuilder\popupBuilder\TemplatesController::getGroupName */
        $this->addFilter('vcv:template:groupName', 'getGroupName');
        $this->addFilter('vcv:dataAjax:getData', 'removePopupTemplates', 2);
        $this->addFilter('vcv:helpers:templates:getCustomTemplates', 'removeCustomPopupTemplates');
        $this->addFilter('vcv:frontend:url', 'addTypeToLink', 2);
    }

    protected function setTemplateType($type, Request $requestHelper)
    {
        $isPopup = $requestHelper->input('vcv-editor-type') === 'popup';
        if ($isPopup && $type === 'customBlock') {
            $type = 'customBlockpopup';
        } elseif ($isPopup) {
            $type = 'popup';
        }

        return $type;
    }

    protected function getGroupName($name, $payload)
    {
        if ($payload['key'] === 'popup') {
            $name = __('My Popup Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'hubPopup') {
            $name = __('Hub Popup Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'customBlockpopup') {
            $name = __('My Popup Block Templates', 'visualcomposer');
        }

        return $name;
    }

    protected function removePopupTemplates($response, $payload)
    {
        if (isset($response['templates'], $payload['sourceId']) && is_numeric($payload['sourceId'])) {
            if (get_post_type($payload['sourceId']) !== 'vcv_popups') {
                unset($response['templates']['popup'], $response['templates']['hubPopup'], $response['templates']['customBlockpopup']);
            }
        }

        return $response;
    }

    /**
     * @param $url
     * @param $payload
     *
     * @return string
     */
    protected function addTypeToLink($url, $payload)
    {
        $templateType = get_post_meta(
            $payload['sourceId'],
            '_' . VCV_PREFIX . 'type',
            true
        );
        if ('vcv_templates' === get_post_type($payload['sourceId'])
            && in_array($templateType, ['popup', 'hubPopup', 'customBlockpopup'], true)
        ) {
            return add_query_arg(['vcv-editor-type' => 'vcv_popups'], $url);
        }

        return $url;
    }

    protected function removeCustomPopupTemplates($response)
    {
        $sourceId = vchelper('Request')->input('vcv-source-id');
        if (get_post_type($sourceId) !== 'vcv_popups') {
            $response = array_filter($response, function ($item) {
                return !(isset($item['group']['label']) && stripos($item['group']['label'], 'popup') !== false);
            });
        }

        return $response;
    }
}
