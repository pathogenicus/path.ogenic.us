<?php

namespace fontManager\fontManager;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Modules\Settings\Traits\Fields;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

class FontManagerSettingsPage extends Container implements Module
{
    use Page;
    use Fields;
    use SubMenu;
    use WpFiltersActions;
    use EventsFilters;

    /**
     * @var string
     */
    protected $slug = 'vcv-font-manager';

    /*
     * @var string
     */
    protected $templatePath = 'font-manager/font-manager.php';

    public function __construct()
    {
        $this->wpAddAction(
            'admin_menu',
            'addPage',
            20
        );
        $this->wpAddAction(
            'admin_init',
            'buildPage'
        );
    }

    /**
     * @throws \Exception
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->slug,
            'title' => __('Font Manager', 'visualcomposer'),
            'description' => __('', 'visualcomposer'),
            'layout' => 'dashboard-tab-content-standalone',
            'capability' => 'manage_options',
            'iconClass' => 'vcv-ui-icon-dashboard-a-letter',
            'isDashboardPage' => true,
            'premiumTitle' => __('FONT MANAGER', 'visualcomposer'),
            'premiumDescription' => __(
                'Control the typography and other font styling of your site, including links, paragraphs, headings.',
                'visualcomposer'
            ),
            'premiumUrl' => vchelper('Utm')->get('vcdashboard-teaser-fontmanager'),
            'premiumActionBundle' => 'fontManager',
        ];
        $this->addSubmenuPage($page, false);
    }

    /**
     * @param \VisualComposer\Helpers\Options $optionsHelper
     */
    protected function buildPage(Options $optionsHelper)
    {
        $sectionCallback = function () {
            echo sprintf(
                '<p class="description">%s</p>',
                esc_html__(
                    'Use these settings to change default font styles (ex. headings, paragraph). Visual Composer Font Manager works with the Visual Composer layouts and Visual Composer Starter theme.',
                    'visualcomposer'
                )
            );
        };
        $this->addSection(
            [
                'title' => '',
                'slug' => $this->slug,
                'group' => $this->slug,
                'page' => $this->slug,
                'callback' => $sectionCallback,
            ]
        );
        $fieldCallback = function ($data) {
            echo vcview(
                'settings/fields/toggle',
                [
                    'name' => $this->slug . '_enable',
                    'value' => 'fontManager-enabled',
                    'isEnabled' => vchelper('Options')->get($this->slug . '_enable'),
                ]
            );
        };

        $this->addField(
            [
                'page' => $this->slug,
                'slug' => $this->slug,
                'name' => $this->slug . '_enable',
                'title' => __('Enable Font Manager', 'visualcomposer'),
                'description' => __('Enable Font Manager', 'visualcomposer'),
                'id' => $this->slug . '_enable',
                'fieldCallback' => $fieldCallback,
                'args' => [
                    'class' => $this->slug . '_enable',
                ],
            ]
        );
    }

    /**
     * Render page.
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
                'addon' => 'fontManager',
                'controller' => $this,
                'slug' => $this->getSlug(),
                'path' => $this->getTemplatePath(),
                'page' => $page,
            ]
        );

        return vcaddonview($this->getTemplatePath(), $args);
    }

    protected function beforeRender(Addons $addonsHelper)
    {
        $addonUrl = $addonsHelper->getAddonUrl('fontManager');
        wp_enqueue_script(
            'vcv-addons-font-manager-settings',
            $addonUrl . '/public/dist/element.bundle.js',
            ['jquery'],
            VCV_VERSION
        );
        wp_enqueue_style(
            'vcv-addons-font-manager-settings-style',
            $addonUrl . '/public/dist/element.bundle.css',
            [],
            VCV_VERSION
        );
    }
}
