<?php

namespace popupBuilder\popupBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VcvEnv;
use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class PopupController
 * @package popupBuilder\popupBuilder
 */
class PopupController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    /**
     * List of popups
     * @var array
     */
    protected $popups = [];

    /**
     * Cache to avoid popup duplicates
     * @var array
     */
    protected $renderedPopups = [];

    /**
     * Cache all popup HTML
     * @var string
     */
    protected $popupOutput = '';

    /**
     * PopupController constructor.
     */
    public function __construct()
    {
        /** @see \popupBuilder\popupBuilder\PopupController::collectAllPopups */
        $this->wpAddFilter('the_content', 'collectAllPopups', 30);
        $this->addFilter('vcv:frontend:content', 'collectAllPopups');
        /** @see \popupBuilder\popupBuilder\PopupController::getVariables */
        $this->addFilter(
            'vcv:frontView:variables',
            'getVariables'
        );

        // Add Global popups assets for regular post/page load
        /** @see \popupBuilder\popupBuilder\PopupController::addGlobalPopupMeta */
        $this->wpAddFilter('get_post_metadata', 'addGlobalPopupMeta');
        // Output all popup HTML inside footer
        $this->wpAddAction('wp_footer', 'outputPopupHtml');
        $this->addFilter('vcv:helpers:frontend:isVcvFrontend', 'setVcvFrontend');

        $this->addFilter(
            'vcv:ajax:setData:adminNonce',
            'setTemplatesElementsPopups',
            30
        );
    }

    /**
     * @param $response
     * @param $objectId
     * @param $metaKey
     * @param $single
     *
     * @return array
     */
    protected function addGlobalPopupMeta($response, $objectId, $metaKey, $single)
    {
        if ($metaKey === 'vcvSourceAssetsFiles') {
            $frontendHelper = vchelper('Frontend');
            $requestHelper = vchelper('Request');
            if ($frontendHelper->isFrontend()
                || $frontendHelper->isPageEditable()
                || $requestHelper->exists(VCV_ADMIN_AJAX_REQUEST)
                || $requestHelper->exists(VCV_AJAX_REQUEST)
            ) {
                return $response;
            }
            $originalMeta = $this->getMeta('post', $objectId, $metaKey, $single);
            if (!is_array($originalMeta)) {
                $originalMeta = [];
            }
            if (!isset($originalMeta['jsBundles'])) {
                $originalMeta['jsBundles'] = [];
            }

            if (!isset($originalMeta['cssBundles'])) {
                $originalMeta['cssBundles'] = [];
            }
            $sharedAssets = vchelper('AssetsShared')->getSharedAssets();

            list($globalSettingsOnFirstPageLoad, $globalSettingsOnPageLoad, $globalSettingsOnExitIntent) =
                $this->getGlobalPopupsSettings();

            $modified = false;
            if (!empty($globalSettingsOnFirstPageLoad) && isset($sharedAssets['popupOnFirstPageLoad'])) {
                $modified = true;
                $assetData = $sharedAssets['popupOnFirstPageLoad'];
                $originalMeta['jsBundles'][] = $assetData['jsBundle'];
                $originalMeta['cssBundles'][] = $assetData['cssBundle'];
            }
            if (!empty($globalSettingsOnPageLoad) && isset($sharedAssets['popupOnPageLoad'])) {
                $modified = true;
                $assetData = $sharedAssets['popupOnPageLoad'];
                $originalMeta['jsBundles'][] = $assetData['jsBundle'];
                $originalMeta['cssBundles'][] = $assetData['cssBundle'];
            }
            if (!empty($globalSettingsOnExitIntent) && isset($sharedAssets['popupOnExitIntent'])) {
                $modified = true;
                $assetData = $sharedAssets['popupOnExitIntent'];
                $originalMeta['jsBundles'][] = $assetData['jsBundle'];
                $originalMeta['cssBundles'][] = $assetData['cssBundle'];
            }
            $originalMeta['jsBundles'] = array_unique($originalMeta['jsBundles']);
            $originalMeta['cssBundles'] = array_unique($originalMeta['cssBundles']);

            return $modified ? [$originalMeta] : $response;
        }

        return $response;
    }

    /**
     * Get internal meta of object, same as get_post_meta but without cache check
     * Needed to overwrite get_{type}_metadata (post) with custom values
     *
     * @param $metaType
     * @param $objectId
     * @param string $metaKey
     * @param bool $single
     *
     * @return array|bool|false|mixed|string|null
     */
    protected function getMeta($metaType, $objectId, $metaKey = '', $single = false)
    {
        $metaCache = wp_cache_get($objectId, $metaType . '_meta');

        if (!$metaCache) {
            $metaCache = update_meta_cache($metaType, [$objectId]);
            if (isset($metaCache[ $objectId ])) {
                $metaCache = $metaCache[ $objectId ];
            } else {
                $metaCache = null;
            }
        }

        if (!$metaKey) {
            return $metaCache;
        }

        if (isset($metaCache[ $metaKey ])) {
            if ($single) {
                return maybe_unserialize($metaCache[ $metaKey ][0]);
            }

            return array_map('maybe_unserialize', $metaCache[ $metaKey ]);
        }

        if ($single) {
            return '';
        }

        return [];
    }

    /**
     * Collect all popups related to current post to $popups attribute.
     *
     * @param string $content
     * @param array $payload
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return string
     */
    protected function collectAllPopups($content, $payload, Frontend $frontendHelper, Options $optionsHelper)
    {
        if (vcvenv('TOGGLE_ADDON_INSIDE_POPUP_CONTENT')) {
            return $content;
        }

        $sourceId = $this->getSourceId($payload);
        if (!$sourceId) {
            return  $content;
        }

        VcvEnv::set('TOGGLE_ADDON_INSIDE_POPUP_CONTENT', true);

        $this->collectElementsPopups($sourceId, $frontendHelper);

        $settingsPopup = get_post_meta($sourceId, '_' . VCV_PREFIX . 'settingsPopup', true);
        $globalPopups = $this->collectGlobalPopups($frontendHelper, $optionsHelper);
        $this->collectSettingsPopups($frontendHelper, $settingsPopup, $globalPopups);

        VcvEnv::set('TOGGLE_ADDON_INSIDE_POPUP_CONTENT', false);

        return $content;
    }

    /**
     * Get source id
     *
     * @param array|string $payload
     *
     * @return int
     */
    protected function getSourceId($payload)
    {
        $frontendHelper = vchelper('Frontend');

        $sourceId = $frontendHelper->getCurrentBlockId();
        if (isset($payload['sourceId'])) {
            $sourceId = $payload['sourceId'];
        }

        $previewHelper = vchelper('Preview');
        $sourceId = $previewHelper->updateSourceIdWithAutosaveId($sourceId);

        return vcfilter('vcv:popupBuilder:popupController:getSourceId', $sourceId);
    }

    /**
     * Collect elements popups to popups property.
     *
     * @param int $sourceId
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     */
    protected function collectElementsPopups($sourceId, $frontendHelper)
    {
        $popupData = $this->getElementsPopups($sourceId);
        if (empty($popupData) || ! is_array($popupData)) {
            return;
        }

        foreach ($popupData as $popupId) {
            $popupId = intval($popupId);
            if (isset($this->popups[ $popupId ])) {
                continue;
            }

            $this->popups[ $popupId ] = $frontendHelper->renderContent($popupId);
        }
    }

    /**
     * Get current post elements popups.
     *
     * @note For some editor elements we can set individual popups.
     *
     * @param int $sourceId
     *
     * @return mixed
     */
    public function getElementsPopups($sourceId)
    {
        $result = get_post_meta($sourceId, '_' . VCV_PREFIX . 'popupData', true);

        if (!$result) {
            $result = [];
        }

        return $result;
    }

    /**
     * Cache all popup HTML and render in footer
     */
    protected function outputPopupHtml()
    {
        if (!empty($this->popups)) {
            $this->popupOutput .= vcaddonview(
                'popup-container',
                [
                    'addon' => 'popupBuilder',
                    'popups' => $this->popups,
                    'renderedPopups' => $this->renderedPopups,
                ]
            );
            $this->renderedPopups = array_unique(array_merge($this->renderedPopups, array_keys($this->popups)));
        }

        echo $this->popupOutput;
    }

    /**
     * @param $variables
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return array
     */
    protected function getVariables($variables, Options $optionsHelper, Frontend $frontendHelper)
    {
        if ($frontendHelper->isFrontend() || $frontendHelper->isPageEditable()) {
            return $variables;
        }
        $settingsPopup = [];

        list($globalSettingsOnFirstPageLoad, $globalSettingsOnPageLoad, $globalSettingsOnExitIntent) =
            $this->getGlobalPopupsSettings();

        if ($globalSettingsOnFirstPageLoad) {
            $settingsPopup['popupOnFirstPageLoad'] = [
                'id' => (int)$globalSettingsOnFirstPageLoad,
                'delay' => (int)$optionsHelper->get('custom-site-popups-vcv_popup_on_first_page_load_delay'),
                'expires' => (int)$optionsHelper->get('custom-site-popups-vcv_popup_on_first_page_load_expires'),
            ];
        }

        if ($globalSettingsOnPageLoad) {
            $settingsPopup['popupOnPageLoad'] = [
                'id' => (int)$globalSettingsOnPageLoad,
                'delay' => (int)$optionsHelper->get('custom-site-popups-vcv_popup_on_page_load_delay'),
                'expires' => (int)$optionsHelper->get('custom-site-popups-vcv_popup_on_page_load_expires'),
            ];
        }

        if ($globalSettingsOnExitIntent) {
            $settingsPopup['popupOnExitIntent'] = [
                'id' => (int)$globalSettingsOnExitIntent,
                'expires' => (int)$optionsHelper->get('custom-site-popups-vcv_popup_on_exit_intent_expires'),
            ];
        }

        $sourceId = vchelper('Preview')->updateSourceIdWithAutosaveId(get_the_ID());

        $settingsPopupLocal = get_post_meta($sourceId, '_' . VCV_PREFIX . 'settingsPopup', true);

        if (is_array($settingsPopupLocal) && !empty($settingsPopupLocal)) {
            $settingsPopup = array_merge($settingsPopup, $settingsPopupLocal);
        }

        if (!is_array($variables)) {
            $variables = [];
        }

        if (!empty($settingsPopup)) {
            $variables[] = [
                'key' => 'VCV_POPUP_DATA',
                'type' => 'constant',
                'value' => $settingsPopup,
            ];
        }

        return $variables;
    }

    /**
     * Get popups that we set globally in our plugin popup settings.
     *
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return array
     */
    protected function collectGlobalPopups(Frontend $frontendHelper, Options $optionsHelper)
    {
        $response = [];
        if (!$frontendHelper->isFrontend() && !$frontendHelper->isPageEditable()) {
            // Add global popups
            list($globalSettingsOnFirstPageLoad, $globalSettingsOnPageLoad, $globalSettingsOnExitIntent) =
                $this->getGlobalPopupsSettings();

            if (!empty($globalSettingsOnFirstPageLoad)) {
                $response['popupOnFirstPageLoad'] = (int)$globalSettingsOnFirstPageLoad;
            }
            if (!empty($globalSettingsOnPageLoad)) {
                $response['popupOnPageLoad'] = (int)$globalSettingsOnPageLoad;
            }
            if (!empty($globalSettingsOnExitIntent)) {
                $response['popupOnExitIntent'] = (int)$globalSettingsOnExitIntent;
            }
        }

        return $response;
    }

    /**
     * Get popup settings list.
     *
     * @return array
     */
    protected function getGlobalPopupsSettings()
    {
        $settings = [];
        $optionsHelper = vchelper('Options');

        $settings[] = $optionsHelper->get('custom-site-popups-vcv_popup_on_first_page_load');
        $settings[] = $optionsHelper->get('custom-site-popups-vcv_popup_on_page_load');
        $settings[] = $optionsHelper->get('custom-site-popups-vcv_popup_on_exit_intent');

        return $settings;
    }

    /**
     * Get popups that we set in our current post settings.
     *
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param $settingsPopup
     * @param array $globalPopups
     */
    protected function collectSettingsPopups(Frontend $frontendHelper, $settingsPopup, array $globalPopups)
    {
        $requestHelper = vchelper('Request');
        if ($frontendHelper->isFrontend()
            || $frontendHelper->isPageEditable()
            || $requestHelper->exists(VCV_ADMIN_AJAX_REQUEST)
            || $requestHelper->exists(VCV_AJAX_REQUEST)
        ) {
            return;
        }

        if (!empty($settingsPopup) && is_array($settingsPopup)) {
            foreach ($settingsPopup as $type => $popup) {
                if ($popup['id'] === 'none') {
                    unset($globalPopups[ $type ]);
                    continue;
                }
                if (!isset($this->popups[ (int)$popup['id'] ])) {
                    $this->popups[ (int)$popup['id'] ] = $frontendHelper->renderContent($popup['id']);
                }
            }
        }

        if (!empty($globalPopups) && is_array($globalPopups)) {
            foreach ($globalPopups as $globalPopupId) {
                if (!isset($this->popups[ (int)$globalPopupId ])) {
                    $this->popups[ (int)$globalPopupId ] = $frontendHelper->renderContent($globalPopupId);
                }
            }
        }
    }

    /**
     * Always should treat current page as vcv page if it's any popup enabled.
     *
     * @param bool $isVcvFrontend
     *
     * @return bool
     */
    protected function setVcvFrontend($isVcvFrontend)
    {
        list($globalSettingsOnFirstPageLoad, $globalSettingsOnPageLoad, $globalSettingsOnExitIntent) =
            $this->getGlobalPopupsSettings();

        if ($globalSettingsOnFirstPageLoad || $globalSettingsOnPageLoad || $globalSettingsOnExitIntent) {
            return true;
        }

        $sourceId = vchelper('Preview')->updateSourceIdWithAutosaveId(get_the_ID());

        $settingsPopupLocal = get_post_meta($sourceId, '_' . VCV_PREFIX . 'settingsPopup', true);

        if ($settingsPopupLocal) {
            return true;
        }

        return $isVcvFrontend;
    }

    /**
     * Add templates elements popups.
     *
     * @note For our HFS template and layouts we have can have elements with popups
     *
     * @param array $response
     * @param array $payload
     *
     * @return array
     */
    protected function setTemplatesElementsPopups($response, $payload)
    {
        if (empty($payload['sourceId'])) {
            return $response;
        }

        $sourceId = $payload['sourceId'];

        $elementsPopups = $this->getElementsPopups($sourceId);

        $elementsPopups = $this->addHfsElementsPopups($elementsPopups);
        $elementsPopups = $this->addHfsLayoutPopups($elementsPopups, $sourceId);

        update_metadata(
            'post',
            $sourceId,
            '_' . VCV_PREFIX . 'popupData',
            $elementsPopups
        );

        return $response;
    }

    /**
     * Add Hfs elements popups to all elements popups list.
     *
     * @param array $elementsPopups
     *
     * @return array
     */
    protected function addHfsElementsPopups($elementsPopups)
    {
        if (!class_exists('\themeEditor\themeEditor\LayoutController')) {
            return $elementsPopups;
        }

        $layoutController = vcapp('\themeEditor\themeEditor\LayoutController');
        foreach ($layoutController->getHfsIdList() as $templateId) {
            $templatePopups = $this->getElementsPopups($templateId);

            $elementsPopups = array_merge($templatePopups, $elementsPopups);
        }

        return $elementsPopups;
    }

    /**
     * Add layout elements popups to all elements popups list.
     *
     * @param array $elementsPopups
     *
     * @return array
     */
    protected function addHfsLayoutPopups($elementsPopups, $sourceId)
    {
        if (!class_exists('\themeBuilder\themeBuilder\pages\LayoutPostTypeController')) {
            return $elementsPopups;
        }

        $layoutPostTypeController = vcapp('\themeBuilder\themeBuilder\pages\LayoutPostTypeController');
        $layoutId = $layoutPostTypeController->getLayoutId($sourceId);

        $layoutPopups = $this->getElementsPopups($layoutId);

        $elementsPopups = array_merge($layoutPopups, $elementsPopups);

        return $elementsPopups;
    }
}
