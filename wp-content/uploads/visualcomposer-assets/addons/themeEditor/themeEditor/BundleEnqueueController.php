<?php

namespace themeEditor\themeEditor;

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
use VisualComposer\Helpers\Url;

class BundleEnqueueController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter(
            'vcv:editors:frontend:render',
            function ($response, Request $requestHelper, Frontend $frontendHelper) {
                if (in_array(
                    $requestHelper->input('vcv-editor-type'),
                    ['vcv_headers', 'vcv_footers', 'vcv_sidebars'],
                    true
                )
                    && $frontendHelper->isFrontend()
                ) {
                    $this->addFilter(
                        'vcv:frontend:footer:extraOutput',
                        function ($response, $payload, Addons $hubAddonsHelper) {
                            return $hubAddonsHelper->addFooterBundleScriptAddon(
                                $response,
                                'theme-editor',
                                'themeEditor',
                                'themeEditor.bundle.js'
                            );
                        }
                    );
                    $this->addFilter('vcv:editor:variables', 'addEditorTypeVariable');
                } elseif ($frontendHelper->isFrontend()) {
                    if (!$requestHelper->exists('vcv-editor-type') || $requestHelper->input('vcv-editor-type') === 'vcv_layouts') {
                        $this->addFilter(
                            'vcv:frontend:footer:extraOutput',
                            function ($response, $payload, Addons $hubAddonsHelper) {
                                return $hubAddonsHelper->addFooterBundleScriptAddon(
                                    $response,
                                    'theme-layouts',
                                    'themeEditor',
                                    'layoutsView.bundle.js'
                                );
                            }
                        );
                    }
                }

                return $response;
            },
            -2
        );
    }

    protected function addEditorTypeVariable($variables, Request $requestHelper)
    {
        $key = 'VCV_EDITOR_TYPE';
        $value = 'header';
        switch ($requestHelper->input('vcv-editor-type')) {
            case 'vcv_headers':
                $value = 'header';
                break;
            case 'vcv_footers':
                $value = 'footer';
                break;
            case 'vcv_sidebars':
                $value = 'sidebar';
                break;
        }

        $variables[] = [
            'key' => $key,
            'value' => $value,
            'type' => 'constant',
        ];

        return $variables;
    }
}
