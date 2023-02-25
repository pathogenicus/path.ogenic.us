<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

class TemplateController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:editorTemplates:template:type', 'setTemplateType');
        $this->addFilter('vcv:template:groupName', 'getGroupName');
    }

    protected function setTemplateType($type, Request $requestHelper)
    {
        switch ($requestHelper->input('vcv-editor-type')) {
            case 'header':
                $type = 'customHeader';
                break;
            case 'footer':
                $type = 'customFooter';
                break;
            case 'sidebar':
                $type = 'customSidebar';
                break;
        }

        return $type;
    }

    protected function getGroupName($name, $payload)
    {
        switch ($payload['key']) {
            case 'hubHeader':
                $name = __('Hub Header Templates', 'visualcomposer');
                break;
            case 'hubFooter':
                $name = __('Hub Footer Templates', 'visualcomposer');
                break;
            case 'hubSidebar':
                $name = __('Hub Sidebar Templates', 'visualcomposer');
                break;
            case 'customHeader':
                $name = __('My Header Templates', 'visualcomposer');
                break;
            case 'customFooter':
                $name = __('My Footer Templates', 'visualcomposer');
                break;
            case 'customSidebar':
                $name = __('My Sidebar Templates', 'visualcomposer');
                break;
            case 'block':
                $name = __('Hub Block Templates', 'visualcomposer');
                break;
            case 'customBlock':
                $name = __('My Block Templates', 'visualcomposer');
                break;
        }

        return $name;
    }

    protected function viewVcTemplate($originalTemplate, $data)
    {
        if ($data && $data['type'] === 'vc') {
            if (in_array($data['value'], ['blank', 'boxed'])) {
                $template = $data['value'] . '-template.php';

                return vcapp()->path('visualcomposer/resources/views/editor/templates/') . $template;
            }
        }

        return $originalTemplate;
    }
}
