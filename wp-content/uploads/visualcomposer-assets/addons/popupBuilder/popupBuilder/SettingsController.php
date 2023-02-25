<?php

namespace popupBuilder\popupBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Modules\Settings\Traits\Fields;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

/**
 * Class SettingsController
 * @package popupBuilder\popupBuilder
 */
class SettingsController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;
    use Fields;
    use Page;
    use SubMenu;

    /**
     * @var string
     */
    protected $slug = 'vcv-custom-site-popups';

    /**
     * Settings template to render
     * @var string
     */
    protected $templatePath = 'settings/pages/index';

    /**
     * SettingsController constructor.
     */
    public function __construct()
    {
        $this->optionGroup = 'vcv-custom-site-popups';
        $this->optionSlug = 'vcv-custom-site-popups';

        $this->wpAddAction(
            'admin_init',
            'buildPage',
            40
        );
        $this->wpAddAction(
            'admin_menu',
            'addPage',
            20
        );
        $this->addEvent(
            'vcv:settings:save',
            'addPage'
        );

        $this->wpAddFilter('submenu_file', 'subMenuHighlight');
    }

    /**
     * Setup the menu parent vcv-settings
     *
     * @param $submenuFile
     *
     * @return string
     */
    protected function subMenuHighlight($submenuFile)
    {
        $screen = get_current_screen();
        if (strpos($screen->id, $this->slug)) {
            $submenuFile = 'vcv-settings';
        }

        return $submenuFile;
    }

    /**
     * Prints inline styles to hide menu item immediately
     */
    protected function beforeRender()
    {
        $addonUrl = vchelper('HubAddons')->getAddonUrl('popupBuilder');
        wp_enqueue_script(
            'vcv-addons-popup-builder-settings',
            $addonUrl . '/public/dist/element.bundle.js',
            ['jquery', 'vcv:assets:vendor:script'],
            VCV_VERSION
        );
    }

    /**
     * Add section and fields in settings page
     */
    protected function buildPage()
    {
        $sectionCallback = function () {
            echo sprintf(
                '<p class="description">%s</p>',
                esc_html__(
                    'Specify sitewide popups for specific events like first page load, every page load, or exit-intent.',
                    'visualcomposer'
                )
            );
        };
        // Add description section
        $this->addSection(
            [
                'page' => $this->optionGroup,
                'callback' => $sectionCallback,
            ]
        );

        // On first page load fields
        $this->addPopupDropdownField(__('Popup on first page load', 'visualcomposer'), 'vcv_popup_on_first_page_load');
        $this->addExtraInputField(
            __('Delay (seconds)', 'visualcomposer'),
            'vcv_popup_on_first_page_load_delay',
            'vcv_popup_on_first_page_load'
        );
        $this->addExtraInputField(
            __('Show every (days)', 'visualcomposer'),
            'vcv_popup_on_first_page_load_expires',
            'vcv_popup_on_first_page_load'
        );

        // On regular page load fields
        $this->addPopupDropdownField(__('Popup on every page load', 'visualcomposer'), 'vcv_popup_on_page_load');
        $this->addExtraInputField(
            __('Delay (seconds)', 'visualcomposer'),
            'vcv_popup_on_page_load_delay',
            'vcv_popup_on_page_load'
        );
        $this->addExtraInputField(
            __('Show every (days)', 'visualcomposer'),
            'vcv_popup_on_page_load_expires',
            'vcv_popup_on_page_load'
        );

        // On exit intent fields
        $this->addPopupDropdownField(__('Popup on exit-intent', 'visualcomposer'), 'vcv_popup_on_exit_intent');
        $this->addExtraInputField(
            __('Show every (days)', 'visualcomposer'),
            'vcv_popup_on_exit_intent_expires',
            'vcv_popup_on_exit_intent'
        );
    }

    /**
     * Render dropdown for popups
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param $slug
     * @param $editTextTitle
     *
     * @return mixed|string
     */
    protected function renderPopupDropdown(Options $optionsHelper, Frontend $frontendHelper, $slug, $editTextTitle)
    {
        $urlHelper = vchelper('Url');
        $selectedPopup = (int)$optionsHelper->get('custom-site-popups-' . $slug);

        // Get Available Popups
        $args = [
            'numberposts' => -1,
            'post_type' => 'vcv_popups',
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        $posts = get_posts($args);
        $availablePopups = [];
        foreach ($posts as $post) {
            $availablePopups[] = [
                'id' => $post->ID,
                // @codingStandardsIgnoreLine
                'title' => $post->post_title,
                'url' => $frontendHelper->getFrontendUrl($post->ID),
            ];
        }

        return vcview(
            'settings/fields/dropdown',
            [
                'enabledOptions' => $availablePopups,
                'name' => 'vcv-custom-site-popups-' . $slug,
                'value' => $selectedPopup,
                'dataTitle' => $editTextTitle,
                'emptyTitle' => __('None', 'visualcomposer'),
                'class' => 'vcv-edit-link-selector',
                'createUrl' => vcfilter(
                    'vcv:frontend:url',
                    $urlHelper->query(admin_url('post-new.php?post_type=vcv_popups'), ['vcv-action' => 'frontend', 'vcv-editor-type' => 'vcv_popups']),
                    ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                ),
            ]
        );
    }

    /**
     * Render dropdown for popups
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param $popupType
     *
     * @param $title
     *
     * @return mixed|string
     */
    protected function renderInput(Options $optionsHelper, $popupType, $title)
    {
        $currentSavedDelay = (int)$optionsHelper->get($popupType);

        return vcaddonview(
            'settings/field-input',
            [
                'name' => 'vcv-' . $popupType,
                'addon' => 'popupBuilder',
                'value' => $currentSavedDelay,
                'title' => $title,
            ]
        );
    }

    /**
     * Create settings page
     * @throws \Exception
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->getSlug(),
            'title' => __('Popup Builder', 'visualcomposer'),
            'subTitle' => __('Popup Settings', 'visualcomposer'),
            'layout' => 'dashboard-tab-content-standalone',
            'capability' => 'manage_options',
            'capabilityPart' => 'dashboard_addon_popup_builder',
            'iconClass' => 'vcv-ui-icon-dashboard-popup-builder',
            'isDashboardPage' => true,
            'hideInWpMenu' => false,
        ];

        $this->addSubmenuPage($page, $this->getSlug());
    }

    /**
     * @param $title
     * @param $slug
     *
     * @return bool
     */
    protected function addPopupDropdownField($title, $slug)
    {
        $this->addSection(
            [
                'page' => $this->optionGroup,
                'slug' => 'custom-site-popups-' . $slug,
                'vcv-args' => [
                    'class' => 'vcv-custom-site-popups-dropdown-section',
                ],
            ]
        );

        $fieldData = [
            'page' => $this->optionGroup,
            'title' => $title,
            'name' => 'custom-site-popups-' . $slug,
            'slug' => 'custom-site-popups-' . $slug,
            'id' => 'vcv-custom-site-popups-' . $slug,
            'args' => [
                'class' => '',
            ],
        ];

        $fieldRenderCallback = function () use ($slug, $title) {
            echo $this->call(
                'renderPopupDropdown',
                [
                    'popupType' => $slug,
                    'editTextTitle' => strtolower($title),
                ]
            );
        };
        $fieldData['fieldCallback'] = $fieldRenderCallback;
        $this->addField($fieldData);

        return true;
    }

    /**
     * @param $title
     * @param $slug
     * @param $parentSlug
     *
     * @return bool
     */
    protected function addExtraInputField($title, $slug, $parentSlug)
    {
        $inputFieldData = [
            'page' => $this->optionGroup,
            'title' => '',
            'name' => 'custom-site-popups-' . $slug,
            'slug' => 'custom-site-popups-' . $parentSlug,
            'id' => 'vcv-custom-site-popups-' . $slug,
            'args' => [
                'class' => 'vcv-hidden vcv-custom-site-popups-input vcv-custom-site-popups-' . $slug,
            ],
        ];
        $fieldRenderCallback = function () use ($inputFieldData, $title) {
            echo $this->call(
                'renderInput',
                [
                    'popupType' => $inputFieldData['name'],
                    'title' => $title,
                ]
            );
        };
        $inputFieldData['fieldCallback'] = $fieldRenderCallback;

        $this->addField($inputFieldData);

        return true;
    }
}
