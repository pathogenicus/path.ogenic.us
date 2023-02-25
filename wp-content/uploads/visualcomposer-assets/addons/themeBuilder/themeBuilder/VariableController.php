<?php

namespace themeBuilder\themeBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\EditorTemplates;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class VariableController
 * @package themeBuilder\themeBuilder
 */
class VariableController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:wp:dashboard:variables', 'addTemplatesVariables');
    }

    /**
     * @param $variables
     * @param $payload
     *
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     *
     * @return array
     */
    protected function addTemplatesVariables($variables, $payload, EditorTemplates $editorTemplatesHelper)
    {
        // TODO: Remove this VC-1904, performance improvements change to pagination(via ajax) or completely remove
        $key = 'VCV_HUB_GET_TEMPLATES';

        $variables[] = [
            'key' => $key,
            'value' => $editorTemplatesHelper->all(),
            'type' => 'constant',
        ];

        return $variables;
    }
}
