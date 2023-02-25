<?php

namespace layoutPostCustomFields\layoutPostCustomFields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class LayoutPostCustomFields extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:editor:variables', 'addVariables');
    }

    /**
     * Add frontend variables.
     *
     * @param array $variables
     *
     * @return array
     */
    protected function addVariables($variables)
    {
        $variables[] = [
            'key' => 'vcvLayoutPostCustomFieldsPlaceholder',
            'value' => __(
                'Custom Field',
                'visualcomposer'
            ),
            'type' => 'variable',
        ];

        return $variables;
    }
}
