<?php

namespace fontManager\fontManager;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

class FontManagerSettingsController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:ajax:settings:fontManager:save:adminNonce', 'saveSettings');
    }

    protected function saveSettings(
        Request $requestHelper,
        Options $optionsHelper,
        CurrentUser $accessCurrentUserHelper
    ) {
        $hasAccessHfs = $accessCurrentUserHelper->part('dashboard')->can('addon_theme_builder')->get();
        if ($hasAccessHfs) {
            $enableFontManager = (int)$requestHelper->input('vcv-font-manager-enable');

            if ($enableFontManager) {
                $enableFontManagerDarkMode = (int)$requestHelper->input('vcv-font-manager-dark-mode-enable');
                $fontManager = $requestHelper->input('vcv-font-manager');
                $optionsHelper->set('fontManager', $fontManager);
                $optionsHelper->set('fontManagerDarkMode', $enableFontManagerDarkMode);
            } else {
                $optionsHelper->delete('fontManager');
                $optionsHelper->delete('fontManagerDarkMode');
            }
            $hash = get_stylesheet();
            $optionsHelper->deleteTransient('fontManager:cache:' . $hash);
            $optionsHelper->deleteTransient('fontManager:fontFamily:cache:' . $hash);
        }

        return ['status' => true];
    }
}
