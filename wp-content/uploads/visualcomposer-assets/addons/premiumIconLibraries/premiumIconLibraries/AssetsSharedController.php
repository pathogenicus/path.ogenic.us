<?php

namespace premiumIconLibraries\premiumIconLibraries;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Traits\EventsFilters;

class AssetsSharedController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:helper:assetsShared:getLibraries', 'addSharedLibraries');
    }

    protected function addSharedLibraries($libraries, Addons $addonsHelper)
    {
        if (vcvenv('VCV_ENV_EXTENSION_DOWNLOAD') && !$addonsHelper->isDevAddons()) {
            $optionsHelper = vchelper('Options');
            $assetSharedHelper = vchelper('AssetsShared');
            $assets = $optionsHelper->get('assetsLibrary', []);
            if (isset($assets['iconpicker'])) {
                $value = $assets['iconpicker'];
                $key = 'iconpicker';
                $libraries[ $key ] = $value;

                if (isset($value['cssSubsetBundles'])) {
                    $cssSubsetBundles = [];
                    foreach ($value['cssSubsetBundles'] as $singleKey => $single) {
                        $cssSubsetBundles[ $singleKey ] = $assetSharedHelper->getBundleUrl($single);
                    }
                    $libraries[ $key ]['cssSubsetBundles'] = $cssSubsetBundles;
                }
            }
        }

        return $libraries;
    }
}
