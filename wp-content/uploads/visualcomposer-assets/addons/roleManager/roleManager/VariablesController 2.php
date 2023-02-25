<?php

namespace roleManager\roleManager;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class VariablesController
 * @package roleManager\roleManager
 */
class VariablesController extends Container implements Module
{
    use EventsFilters;

    /**
     * VariablesController constructor.
     */
    public function __construct()
    {
        \VcvEnv::set('VCV_ADDON_ROLE_MANAGER_ENABLED', true);
        \VcvEnv::set('VCV_ADDON_ROLE_MANAGER_PARTS', true);
        $this->addFilter('vcv:editor:variables vcv:hub:variables', 'addAccessVariables');
    }

    protected function addAccessVariables($variables, $payload)
    {
        $variables[] = [
            'key' => 'VCV_USER_ACCESS',
            'type' => 'constant',
            'value' => vchelper('AccessCurrentUser')->getAllUserCaps(),
        ];

        return $variables;
    }
}
