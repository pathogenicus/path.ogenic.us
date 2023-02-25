<?php

namespace exportImport\exportImport;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

/**
 * Class ImportPage.
 */
class ImportPage extends Container implements Module
{
    use Page;
    use SubMenu;
    use WpFiltersActions;

    /**
     * @var string
     */
    protected $slug = 'vcv-import';

    /**
     * @var string
     */
    protected $templatePath = 'import';

    /**
     * About constructor.
     */
    public function __construct()
    {
        // Set dashboard modifications for addon (needed for BC when addons not updated)
        \VcvEnv::set('VCV_HUB_ADDON_DASHBOARD_EXPORTIMPORT', true);
        /** @see \VisualComposer\Modules\Settings\Pages\Settings::addPage */
        $this->wpAddAction(
            'admin_menu',
            'addPage',
            20
        );
    }

    /**
     * Add import page
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->slug,
            'title' => __('Import', 'visualcomposer'),
            'layout' => 'dashboard-tab-content-standalone',
            'iconClass' => 'vcv-ui-icon-dashboard-import',
            'controller' => $this,
            'capability' => 'upload_files',
            'capabilityPart' => 'dashboard_addon_export_import',
            'isDashboardPage' => true,
            'hideInWpMenu' => false,
        ];
        $this->addSubmenuPage($page, $this->slug);
    }

    /**
     * Render page
     *
     * @param $page
     *
     * @return mixed|string
     * @throws \ReflectionException
     */
    public function render($page)
    {
        /**
         * @var $this \VisualComposer\Application|\VisualComposer\Framework\Container
         * @see \VisualComposer\Framework\Container::call
         * @see \VisualComposer\Modules\Settings\Traits\Page::beforeRender
         */
        $this->call('beforeRender');
        /** @var $this Page */
        $args = array_merge(
            method_exists($this, 'getRenderArgs') ? (array)$this->call('getRenderArgs') : [],
            [
                'addon' => 'exportImport',
                'controller' => $this,
                'slug' => $this->getSlug(),
                'path' => $this->getTemplatePath(),
                'page' => $page,
            ]
        );

        return vcaddonview($this->getTemplatePath(), $args);
    }
}
