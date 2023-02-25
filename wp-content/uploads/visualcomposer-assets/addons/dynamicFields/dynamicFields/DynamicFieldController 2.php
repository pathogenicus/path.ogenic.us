<?php

namespace dynamicFields\dynamicFields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'Fields/FieldResponse.php';

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class DynamicFieldController
 * @package dynamicFields\dynamicFields
 */
class DynamicFieldController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use \dynamicFields\dynamicFields\Fields\FieldResponse;

    /**
     * DynamicFieldController constructor.
     */
    public function __construct()
    {
        $this->addFilter(
            'vcv:dataAjax:getData vcv:ajax:getDynamicPost:adminNonce',
            'forceFields',
            -1
        );

        $this->addFilter('vcv:addon:dynamicField:parseResponse', 'parseDynamicResponseValue');

        \VcvEnv::set('VCV_JS_FT_DYNAMIC_FIELDS', true);
    }

    /**
     * Force field display in our custom posts
     *
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @return mixed
     */
    protected function forceFields($response, $payload, Request $requestHelper)
    {
        $post = get_post($payload['sourceId']);
        $postType = get_post_type($post);
        $customPost = $requestHelper->input('vcv-custom-post');
        $postTypesList = [
            'vcv_headers',
            'vcv_footers',
            'vcv_sidebars',
            'vcv_templates',
            'vcv_layouts',
        ];
        if (empty($customPost) && empty($payload['vcv-custom-post'])
            && in_array(
                $postType,
                $postTypesList
            )
        ) {
            $response['forceAddField'] = true;
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return string
     */
    protected function parseDynamicResponseValue($response, $payload)
    {
        $currentKey = $payload['name'];
        $currentValue = $payload['value'];
        $response = $this->parseResponse($currentValue, $currentKey, $response);

        return $response;
    }
}
