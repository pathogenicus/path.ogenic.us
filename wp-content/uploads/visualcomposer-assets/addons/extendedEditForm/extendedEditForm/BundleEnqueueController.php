<?php

namespace extendedEditForm\extendedEditForm;

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

/**
 * Class BundleEnqueueController
 * @package extendedEditForm\extendedEditForm
 */
class BundleEnqueueController extends Container implements Module
{
    use EventsFilters;

    /**
     * BundleEnqueueController constructor.
     */
    public function __construct()
    {
        $this->addFilter(
            'vcv:editors:frontend:render',
            function ($response) {

                $this->addFilter(
                    'vcv:frontend:footer:extraOutput',
                    function ($response, $payload, Addons $hubAddonsHelper) {
                        return $hubAddonsHelper->addFooterBundleScriptAddon(
                            $response,
                            'extended-edit-form',
                            'extendedEditForm'
                        );
                    }
                );

                return $response;
            },
            -2
        );
        /** @see \VisualComposer\Helpers\AssetsShared::assetsLibraries */
        $this->addFilter('vcv:helper:assetsShared:getLibraries', 'assetsLibraries');
    }

    /**
     * @param $assetsLibraries
     *
     * @param \VisualComposer\Helpers\Hub\Addons $hubAddonsHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return mixed
     */
    protected function assetsLibraries($assetsLibraries, Addons $hubAddonsHelper, Options $optionsHelper)
    {
        $addonUrl = $hubAddonsHelper->getAddonUrl('extendedEditForm');
        $addonVersion = $optionsHelper->get('hubAction:addon/extendedEditForm', VCV_VERSION);

        $assetsLibraries['vanillaTilt'] = [
            'dependencies' => [],
            'jsBundle' => add_query_arg(
                'v',
                $addonVersion . '-' . VCV_VERSION,
                $addonUrl . '/extendedEditForm/public/dist/vanillaTilt.min.js'
            ),
            'cssBundle' => '',
        ];
        $assetsLibraries['backgroundAnimation'] = [
            'dependencies' => ['anime'],
            'jsBundle' => add_query_arg(
                'v',
                $addonVersion . '-' . VCV_VERSION,
                $addonUrl . '/extendedEditForm/public/dist/backgroundAnimation.min.js'
            ),
            'cssBundle' => '',
        ];
        $assetsLibraries['anime'] = [
            'dependencies' => [],
            'jsBundle' => add_query_arg(
                'v',
                $addonVersion . '-' . VCV_VERSION,
                $addonUrl . '/extendedEditForm/public/dist/anime.min.js'
            ),
            'cssBundle' => '',
        ];

        return $assetsLibraries;
    }
}
