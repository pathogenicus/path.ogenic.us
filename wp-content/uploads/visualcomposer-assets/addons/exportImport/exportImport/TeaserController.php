<?php

namespace exportImport\exportImport;

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

/**
 * Class TeaserController
 * @package exportImport\exportImport
 */
class TeaserController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * TeaserController constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:hub:addonDownloadController:download:response', 'parseDownloadResponse');
    }

    /**
     * @param $response
     * @param \VisualComposer\Helpers\Hub\Addons $addonsHelper
     *
     * @return mixed
     */
    protected function parseDownloadResponse($response, Addons $addonsHelper)
    {
        if (isset($response['addons'])) {
            $hubAddons = $addonsHelper->getAddons();
            $addons = $response['addons'];
            foreach ($addons as $key => $addon) {
                if ($addon['tag'] === 'exportImport') {
                    $response['addons'][ $key ]['addable'] = false;
                    if (isset($hubAddons[ $addon['tag'] ])) {
                        $hubAddons[ $addon['tag'] ] = array_merge($hubAddons[ $addon['tag'] ], ['addable' => false]);
                    }
                }
            }
            $addonsHelper->setAddons($hubAddons);
        }

        return $response;
    }
}
