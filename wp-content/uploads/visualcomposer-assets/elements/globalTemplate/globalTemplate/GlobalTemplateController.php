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
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class GlobalTemplateController
 * @package globalTemplate\globalTemplate
 */
class GlobalTemplateController extends Container implements Module
{
    use EventsFilters;

    /**
     * GlobalTemplateController constructor.
     */
    public function __construct()
    {
        /** @see \globalTemplate\globalTemplate\GlobalTemplateController::getVariables */
        $this->addFilter(
            'vcv:editor:variables vcv:editor:variables/globalTemplate',
            'getVariables'
        );
    }

    /**
     * @param $variables
     * @param $payload
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     *
     * @return array
     */
    protected function getVariables($variables, $payload, EditorTemplates $editorTemplatesHelper)
    {
        $values = $editorTemplatesHelper->getCustomTemplateOptions();

        $variables[] = [
            'key' => 'vcvGlobalTemplatesList',
            'value' => array_values($values),
            'type' => 'variable',
        ];

        return $variables;
    }
}
