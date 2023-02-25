<?php

namespace maintenanceMode\maintenanceMode;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class AdminBarMenuController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    protected $isMaintenanceMode = false;

    public function __construct()
    {
        $this->wpAddAction('init', 'init');
        $this->wpAddAction('admin_bar_menu', 'adminBarMenu', 1000);
    }

    protected function init(Addons $addonsHelper)
    {
        $this->isMaintenanceMode = MaintenanceModeController::isMaintenanceMode();
        if ($this->isMaintenanceMode) {
            $addonUrl = $addonsHelper->getAddonUrl('maintenanceMode');
            wp_enqueue_style(
                'vcv-addons-maintenance-mode-settings-style',
                $addonUrl . '/public/dist/element.bundle.css',
                [],
                VCV_VERSION
            );
        }
    }

    protected function adminBarMenu()
    {
        // @codingStandardsIgnoreLine
        global $wp_admin_bar;
        if ($this->isMaintenanceMode) {
            // @codingStandardsIgnoreLine
            $wp_admin_bar->add_menu(
                [
                    'id' => 'vcwb-maintenance-mode',
                    'href' => admin_url() . 'admin.php?page=vcv-maintenance-mode',
                    'parent' => 'top-secondary',
                    'title' => __('Maintenance Mode is Active', 'visualcomposer'),
                    'meta' => ['class' => 'vcv-maintenance-mode-active'],
                ]
            );
        }
    }
}
