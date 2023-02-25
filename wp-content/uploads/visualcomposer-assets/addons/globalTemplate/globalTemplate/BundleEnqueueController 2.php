<?php

namespace globalTemplate\globalTemplate;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Assets;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Url;

class BundleEnqueueController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    public function __construct()
    {
        $this->addFilter(
            'vcv:editors:frontend:render',
            function ($response, Request $requestHelper, Frontend $frontendHelper) {
                if ($requestHelper->input('vcv-editor-type') === 'vcv_templates' && $frontendHelper->isFrontend()) {
                    $this->addFilter('vcv:editor:variables', 'addEditorTypeVariable');
                }

                $this->addFilter(
                    'vcv:frontend:footer:extraOutput',
                    function ($response, $payload, Addons $hubAddonsHelper) {
                        return $hubAddonsHelper->addFooterBundleScriptAddon(
                            $response,
                            'global-template',
                            'globalTemplate'
                        );
                    }
                );

                return $response;
            }
        );
        $this->wpAddAction('admin_enqueue_scripts', 'addAdminJs');
        $this->wpAddAction('admin_head', 'addAdminCss');
        $this->addFilter('vcv:editors:frontend:render', 'mergeHeadFooterJs');
    }

    protected function addEditorTypeVariable($variables)
    {
        $editorType = false;
        $key = 'VCV_EDITOR_TYPE';
        foreach ($variables as $i => $variable) {
            if ($variable['key'] === $key) {
                $variables[ $i ] = [
                    'key' => 'VCV_EDITOR_TYPE',
                    'value' => 'template',
                    'type' => 'constant',
                ];
                $editorType = true;
            }
        }

        if (!$editorType) {
            $variables[] = [
                'key' => $key,
                'value' => 'template',
                'type' => 'constant',
            ];
        }

        return $variables;
    }

    protected function addAdminJs(Request $requestHelper)
    {
        if (is_admin() && $requestHelper->input('post_type') === 'vcv_templates') {
            // TODO: Change class name from edit_vc5
            $script = 'jQuery(document).ready(function() {
                        jQuery(\'.post-type-vcv_templates .posts tr\').each(function( e ) {
                            if(jQuery(this).find(\'td .edit_vc5 a\').data(\'vcv-confirm-text\')) {
                                jQuery(this).find(\'a.row-title\').attr(\'onclick\', \'return confirm("\'+jQuery(this).find(\'td .edit_vc5 a\').data(\'vcv-confirm-text\')+\'")\');
                            }
                        });
                    });';

            wp_add_inline_script('jquery-core', $script);
        }
    }

    protected function addAdminCss(Request $requestHelper)
    {
        if (is_admin()
            && $requestHelper->input('post_type') === 'vcv_templates'
            && vcfilter(
                'vcv:addons:globalTemplate:adminCss',
                true
            )
        ) {
            echo <<<HTML
            <style>
            .post-type-vcv_templates .bulkactions {
                display: none;
            }
            </style>
HTML;
        }
    }

    protected function mergeHeadFooterJs(Request $requestHelper, Frontend $frontendHelper)
    {
        $sourceId = get_the_ID();
        if ($requestHelper->input('vcv-editor-type') === 'vcv_templates' && $frontendHelper->isFrontend()) {
            $sourceLocalJsHead = get_post_meta($sourceId, VCV_PREFIX . 'settingsLocalJsHead', true);
            if (!empty($sourceLocalJsHead)) {
                $sourceLocalJsFooter = $sourceLocalJsHead;
                // Merge the scripts if head is not empty
                $sourceLocalJsFooter .= get_post_meta($sourceId, VCV_PREFIX . 'settingsLocalJsFooter', true);
                update_post_meta($sourceId, VCV_PREFIX . 'settingsLocalJsHead', '');
                update_post_meta($sourceId, VCV_PREFIX . 'settingsLocalJsFooter', $sourceLocalJsFooter);
            }
        }
    }
}
