<?php

namespace maintenanceMode\maintenanceMode;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class MaintenanceModeController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    public function __construct()
    {
        /** @see \maintenanceMode\maintenanceMode\MaintenanceModeController::renderMaintenancePage */
        $this->wpAddAction(
            'template_redirect',
            'renderMaintenancePage',
            100
        );
    }

    public static function isMaintenanceMode()
    {
        $optionsHelper = vchelper('Options');
        $frontendHelper = vchelper('Frontend');
        $isEnabled = $optionsHelper->get('settings-maintenanceMode-enabled', '');
        if ($isEnabled && !$frontendHelper->isPageEditable()) {
            $sourceId = (int)$optionsHelper->get('settings-maintenanceMode-page', '');
            if ($sourceId) {
                $sourceId = apply_filters('wpml_object_id', $sourceId, 'post', true);
                $post = get_post($sourceId);
                // Reset in case if post not published/removed
                // @codingStandardsIgnoreLine
                if ($post && $post->post_status === 'publish') {
                    return $sourceId;
                }
            }
        }

        return false;
    }

    protected function renderMaintenancePage(CurrentUser $accessCurrentUserHelper)
    {
        // @codingStandardsIgnoreLine
        global $post, $wp_query, $wp_the_query;

        $id = self::isMaintenanceMode();
        if ($id && !$accessCurrentUserHelper->wpAll('edit_pages')->get()) {
            $this->headers();
            $post = get_post($id);
            $id = $post->ID; // in case if translated
            // @codingStandardsIgnoreLine
            $wp_query = new \WP_Query(
                [
                    'suppress_filters' => true,
                    'post_type' => 'page',
                    'p' => $id,
                ]
            ); // set local current query
            // @codingStandardsIgnoreLine
            $wp_the_query = new \WP_Query(
                [
                    'suppress_filters' => true,
                    'post_type' => 'page',
                    'p' => $id,
                ]
            ); // set global query also!
            $template = get_page_template();
            if (!$template) {
                $template = get_index_template();
            }
            if ($template = apply_filters('template_include', $template)) {
                /** @noinspection PhpIncludeInspection */
                include $template;
            }
            exit;
        }
    }

    protected function headers()
    {
        // Default headers for 503 maintenance
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        // 30min delay
        header('Retry-After: 1800');

        // Set Disable WordPress caching
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        if (!defined('DONOTCDN')) {
            define('DONOTCDN', true);
        }
        if (!defined('DONOTCACHEDB')) {
            define('DONOTCACHEDB', true);
        }
        if (!defined('DONOTMINIFY')) {
            define('DONOTMINIFY', true);
        }
        if (!defined('DONOTCACHEOBJECT')) {
            define('DONOTCACHEOBJECT', true);
        }
        nocache_headers();
    }
}
