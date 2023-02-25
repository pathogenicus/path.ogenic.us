<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;

class SaveController extends Container implements Module
{
    use EventsFilters;

    const HFS_POST_TYPE_LIST = [
        'vcv_headers',
        'vcv_footers',
        'vcv_sidebars',
    ];

    public function __construct()
    {
        $this->addFilter('vcv:dataAjax:setData', 'savePostLayouts');
        $this->addFilter('vcv:assets:postTypes', 'addPostTypes');
        $this->addFilter(
            'vcv:popupBuilder:popupController:getSourceId',
            'removeHfsFromPopupRender',
            10,
            1
        );
    }

    protected function addPostTypes($postTypes)
    {
        $postTypes = array_merge($postTypes, self::HFS_POST_TYPE_LIST);

        return $postTypes;
    }

    protected function savePostLayouts($response, $payload, Request $requestHelper)
    {
        if (!vcIsBadResponse($response)) {
            if ($requestHelper->exists('vcv-extra')) {
                $extra = $requestHelper->input('vcv-extra');

                $sourceId = vchelper('Preview')->updateSourceIdWithAutosaveId($payload['sourceId']);

                $headerId = '';
                if (is_array($extra) && array_key_exists('vcv-header-id', $extra)) {
                    $headerId = $extra['vcv-header-id'];
                }
                $sidebarId = '';
                if (is_array($extra) && array_key_exists('vcv-sidebar-id', $extra)) {
                    $sidebarId = $extra['vcv-sidebar-id'];
                }
                $footerId = '';
                if (is_array($extra) && array_key_exists('vcv-footer-id', $extra)) {
                    $footerId = $extra['vcv-footer-id'];
                }

                $this->updateMetadata($sourceId, $headerId, $sidebarId, $footerId);
            }
        }

        return $response;
    }

    /**
     * @param $sourceId
     * @param $headerId
     * @param $sidebarId
     * @param $footerId
     */
    protected function updateMetadata($sourceId, $headerId, $sidebarId, $footerId)
    {
        update_metadata(
            'post',
            $sourceId,
            '_' . VCV_PREFIX . 'HeaderTemplateId',
            $headerId
        );
        update_metadata(
            'post',
            $sourceId,
            '_' . VCV_PREFIX . 'SidebarTemplateId',
            $sidebarId
        );
        update_metadata(
            'post',
            $sourceId,
            '_' . VCV_PREFIX . 'FooterTemplateId',
            $footerId
        );
    }

    /**
     * Remove popups from HFS post types.
     *
     * @param $sourceId
     *
     * @return false|int|string
     */
    protected function removeHfsFromPopupRender($sourceId)
    {
        $postType = get_post_type($sourceId);

        if (in_array($postType, self::HFS_POST_TYPE_LIST)) {
            $sourceId = 0;
        }

        return $sourceId;
    }
}
