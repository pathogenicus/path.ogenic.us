<?php

namespace popupBuilder\popupBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Preview;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class DataController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    public function __construct()
    {
        $this->addFilter('vcv:dataAjax:setData', 'savePopup');
        $this->addFilter('vcv:dataAjax:getData', 'outputPopupData');
        $this->addFilter('vcv:ajax:popupBuilder:getData:adminNonce', 'getPopupData');
    }

    protected function savePopup($response, $payload, Request $requestHelper, Preview $previewHelper)
    {
        if (!vcIsBadResponse($response)) {
            if ($requestHelper->exists('vcv-extra')) {
                $extra = $requestHelper->input('vcv-extra');
                $sourceId = $payload['sourceId'];

                $sourceId = $previewHelper->updateSourceIdWithAutosaveId($sourceId);

                //save link selector data for popup
                if (is_array($extra) && array_key_exists('vcv-popup-data', $extra)) {
                    update_metadata(
                        'post',
                        $sourceId,
                        '_' . VCV_PREFIX . 'popupData',
                        $extra['vcv-popup-data']
                    );
                } else {
                    delete_post_meta($sourceId, '_' . VCV_PREFIX . 'popupData');
                }
                //save page settings for popup
                if (is_array($extra) && array_key_exists('vcv-settings-popup', $extra)) {
                    update_metadata(
                        'post',
                        $sourceId,
                        '_' . VCV_PREFIX . 'settingsPopup',
                        $extra['vcv-settings-popup']
                    );
                } else {
                    delete_post_meta($sourceId, '_' . VCV_PREFIX . 'settingsPopup');
                }
            }
        }

        return $response;
    }

    protected function outputPopupData($response, $payload)
    {
        $popups = get_post_meta($payload['sourceId'], '_' . VCV_PREFIX . 'popupData', true);
        $settingsPopup = get_post_meta($payload['sourceId'], '_' . VCV_PREFIX . 'settingsPopup', true);

        if (!empty($popups)) {
            $response['popups'] = $popups;
        }
        if (!empty($settingsPopup)) {
            $response['settingsPopup'] = $settingsPopup;
        }

        return $response;
    }

    /**
     * @param $response
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\Access\CurrentUser $currentUserAccessHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return bool|false|string
     */
    protected function getPopupData(
        $response,
        Request $requestHelper,
        PostType $postTypeHelper
    ) {
        $sourceId = (int)$requestHelper->input('vcv-source-id');
        if ($sourceId) {
            !defined('CONCATENATE_SCRIPTS') && define('CONCATENATE_SCRIPTS', false);
            $postTypeHelper->setupPost($sourceId);
            global $post;
            $this->wpAddFilter(
                'print_scripts_array',
                function ($list) {
                    return array_diff($list, ['jquery-core', 'jquery', 'jquery-migrate']);
                }
            );
            ob_start();
            do_action('template_redirect'); // This fixes visual composer shortcodes
            remove_action('wp_head', '_wp_render_title_tag', 1);
            //            remove_action( 'wp_head',             'wp_enqueue_scripts',              1     );
            remove_action('wp_head', 'wp_resource_hints', 2);
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            remove_action('wp_head', 'locale_stylesheet');
            remove_action('publish_future_post', 'check_and_publish_future_post', 10);
            remove_action('wp_head', 'noindex', 1);
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            //            remove_action('wp_head', 'wp_print_styles', 8);
            //            remove_action('wp_head', 'wp_print_head_scripts', 9);
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'rel_canonical');
            remove_action('wp_head', 'wp_shortlink_wp_head', 10);
            remove_action('wp_head', 'wp_custom_css_cb', 101);
            remove_action('wp_head', 'wp_site_icon', 99);

            wp_head();
            $headContents = ob_get_clean();
            ob_start();
            echo vchelper('Frontend')->renderContent($sourceId);
            $shortcodeContents = ob_get_clean();
            ob_start();
            wp_footer();
            $footerContents = ob_get_clean();
            $response = $headContents . $shortcodeContents . $footerContents;
        }

        return $response;
    }
}
