<?php

namespace premiumIconLibraries\premiumIconLibraries;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Assets;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Url;

class BundleEnqueueController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter(
            'vcv:frontend:footer:extraOutput',
            function ($response, $payload, Addons $hubAddonsHelper) {
                return $hubAddonsHelper->addFooterBundleScriptAddon(
                    $response,
                    'premium-icon-libraries',
                    'premiumIconLibraries'
                );
            }
        );
    }
}
