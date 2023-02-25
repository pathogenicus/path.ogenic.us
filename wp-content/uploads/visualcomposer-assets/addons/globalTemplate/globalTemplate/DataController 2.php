<?php

namespace globalTemplate\globalTemplate;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\EditorTemplates;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Str;
use VisualComposer\Helpers\Traits\EventsFilters;

class DataController extends Container implements Module
{
    use EventsFilters;

    protected $postType = 'vcv_templates';

    public function __construct()
    {
        /** @see \globalTemplate\globalTemplate\DataController::setAsCustom */
        $this->addFilter('vcv:dataAjax:setData', 'setAsCustom');
    }

    /**
     * Used when "hub" downloaded templates are being modified, saves a copy of it and removes original template
     *
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Str $strHelper
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     *
     * @return mixed
     */
    protected function setAsCustom(
        $response,
        $payload,
        Options $optionsHelper,
        Str $strHelper,
        EditorTemplates $editorTemplatesHelper
    ) {
        $post = $payload['post'];

        // @codingStandardsIgnoreLine
        if ($post->post_type === $this->postType) {
            $sourceId = $post->ID;
            $templateType = get_post_meta($sourceId, '_vcv-type', true);
            if (!$editorTemplatesHelper->isUserTemplateType($templateType)) {
                // @codingStandardsIgnoreLine
                $templateSlug = $strHelper->camel($post->post_title);
                $optionsHelper->delete('hubAction:template/' . $templateSlug);
                if ($templateType === 'predefined') {
                    $optionsHelper->delete('hubAction:predefinedTemplate/' . $templateSlug);
                }
                $newType = 'custom';
                $templateTypeCustom = str_replace(['predefined', 'hub'], '', $templateType);
                if ($templateType === 'block') {
                    $newType = 'customBlock';
                } elseif (!empty($templateTypeCustom)) {
                    // hubHeader -> customHeader
                    // hubFooter -> customFooter
                    $newType .= $templateTypeCustom;
                }
                update_post_meta($sourceId, '_vcv-type', $newType);
                delete_post_meta($sourceId, '_vcv-id');
                delete_post_meta($sourceId, '_vcv-thumbnail');
                delete_post_meta($sourceId, '_vcv-preview');
                delete_post_meta($sourceId, '_vcv-bundle');
                delete_post_meta($sourceId, '_vcv-description');
            }

            //BC for older custom templates
            if (empty($templateType)) {
                update_post_meta($sourceId, '_vcv-type', 'custom');
            }
        }

        return $response;
    }
}
