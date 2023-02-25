<?php

namespace gutenbergTemplateBlock\gutenbergTemplateBlock;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Assets;
use VisualComposer\Helpers\EditorTemplates;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Url;

/**
 * Class GutenbergTemplateBlock
 * @package gutenbergTemplateBlock\gutenbergTemplateBlock
 */
class GutenbergTemplateBlock extends Container implements Module
{
    use WpFiltersActions;

    /**
     * GutenbergTemplateBlock constructor.
     */
    public function __construct()
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        /** @see \gutenbergTemplateBlock\gutenbergTemplateBlock\GutenbergTemplateBlock::gutenbergTemplateBlock */
        $this->wpAddAction('init', 'gutenbergTemplateBlock');

        /** @see \gutenbergTemplateBlock\gutenbergTemplateBlock\GutenbergTemplateBlock::addCustomTemplatesVariable */
        $this->wpAddAction('admin_print_scripts', 'addCustomTemplatesVariable');
    }

    /**
     * @param \VisualComposer\Helpers\Assets $assetsHelper
     * @param \VisualComposer\Helpers\Url $urlHelper
     */
    protected function gutenbergTemplateBlock(Assets $assetsHelper, Url $urlHelper, Addons $addonsHelper)
    {
        $devAddonsUrl = $urlHelper->to('devAddons') . '/gutenbergTemplateBlock/public/dist';

        $addonsUrl = $assetsHelper->getAssetUrl('/addons/gutenbergTemplateBlock/public/dist');

        wp_register_script(
            'vcv-gutenberg-blocks-js',
            $addonsHelper->isDevAddons() ? $devAddonsUrl . '/element.bundle.js?' : $addonsUrl . '/element.bundle.js',
            $this->getPackageDependency(),
            VCV_VERSION
        );

        wp_register_style(
            'vcv-gutenberg-blocks-style',
            $addonsHelper->isDevAddons() ? $devAddonsUrl . '/element.bundle.css?' : $addonsUrl . '/element.bundle.css',
            [],
            VCV_VERSION
        );

        register_block_type(
            'vcv-gutenberg-blocks/template-block',
            [
                'editor_script' => 'vcv-gutenberg-blocks-js',
                'editor_style' => 'vcv-gutenberg-blocks-style',
                'render_callback' => [$this, 'getTemplate'],
            ]
        );
    }

    /**
     * Get script gutenberg package dependencies.
     *
     * @see https://developer.wordpress.org/block-editor/reference-guides/packages
     *
     * @return array
     */
    protected function getPackageDependency()
    {
        global $pagenow;

        $dependencies = array(
            'wp-blocks',
            'wp-element',
            'wp-components',
            'wp-compose',
            'wp-data',
            'wp-hooks',
            'vcv:assets:vendor:script',
            'vcv:assets:runtime:script',
        );

        if (! empty($pagenow) && 'widgets.php' === $pagenow) {
            $dependencies[] = 'wp-widgets';
        } else {
            $dependencies[] = 'wp-editor';
        }

        return $dependencies;
    }

    /**
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     */
    protected function addCustomTemplatesVariable(EditorTemplates $editorTemplatesHelper)
    {
        $currentScreen = get_current_screen();
        if (!method_exists($currentScreen, 'is_block_editor') || !$currentScreen->is_block_editor()) {
            return;
        }

        evcview(
            'partials/constant-script',
            [
                'key' => 'VCV_CUSTOM_TEMPLATES',
                'value' => $editorTemplatesHelper->getCustomTemplateOptions(),
                'type' => 'constant',
            ]
        );
    }

    /**
     * @param $atts
     *
     * @return bool|false|string
     * @throws \ReflectionException
     */
    public function getTemplate($atts)
    {
        if (!isset($atts['vcwbTemplate']) || is_admin()) {
            return false;
        }

        $templateId = $atts['vcwbTemplate'];
        vchelper('AssetsEnqueue')->addToEnqueueList(intval(esc_attr($templateId)));
        // Only if we are in content
        ob_start();
        // Trigger enqueue (only CSS)
        vcevent('vcv:assets:enqueue:css:list');

        // Forcerly print styles before content, to avoid flashing/jumping
        print_late_styles();
        $styles = ob_get_clean(); // Fix to put source-css before content to avoid flashing VC-1210

        $output = $styles;
        $previousDynamicContent = \VcvEnv::get('DYNAMIC_CONTENT_SOURCE_ID');
        if (empty($previousDynamicContent)) {
            \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', get_the_ID());
        }
        $output .= vchelper('Frontend')->renderContent($templateId);
        \VcvEnv::set(
            'DYNAMIC_CONTENT_SOURCE_ID',
            $previousDynamicContent
        ); // return back in case if multiple posts nested

        return $output;
    }
}
