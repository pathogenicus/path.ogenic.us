<?php

namespace themeBuilder\themeBuilder\pages;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class NotFoundController
 * @package themeBuilder\themeBuilder\pages
 */
class NotFoundController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * NotFoundController constructor.
     */
    public function __construct()
    {
        $this->wpAddAction(
            'template_redirect',
            'render404Page',
            100
        );

        $this->addFilter(
            'vcv:editor:settings:pageTemplatesLayouts:current:custom',
            'get404PageId'
        );
    }

    /**
     * Check is custom 404 is set
     *
     * @return bool|int|mixed|void
     */
    public static function get404PageId()
    {
        if (is_404()) {
            $optionsHelper = vchelper('Options');
            $frontendHelper = vchelper('Frontend');
            $customNotFoundPage = $optionsHelper->get('custom-page-templates-404-page', '');
            if ($customNotFoundPage && !$frontendHelper->isPageEditable()) {
                $sourceId = (int)$customNotFoundPage;
                $sourceId = apply_filters('wpml_object_id', $sourceId, 'post', true);
                $post = get_post($sourceId);
                // @codingStandardsIgnoreLine
                if ($post && $post->post_status === 'publish') {
                    return $sourceId;
                }
            }
        }

        return false;
    }

    /**
     * Renders custom 404 page template if it is set
     */
    protected function render404Page()
    {
        // @codingStandardsIgnoreLine
        global $post, $wp_query, $wp_the_query;

        $id = self::get404PageId();
        if ($id) {
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

            if ($template = apply_filters('template_include', $template)) {
                /** @noinspection PhpIncludeInspection */
                include $template;
            }
            exit;
        }
    }

    /**
     * 404 and no-cache headers
     */
    protected function headers()
    {
        // Default headers for 404
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
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
