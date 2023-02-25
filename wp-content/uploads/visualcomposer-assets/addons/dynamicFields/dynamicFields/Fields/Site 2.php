<?php

namespace dynamicFields\dynamicFields\Fields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'FieldResponse.php';

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostData;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class Site
 * @package dynamicFields\dynamicFields\Fields
 */
class Site extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * Site constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:editor:data:postFields', 'addSiteTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addSiteTagline');
        $this->addFilter('vcv:editor:data:postFields', 'addCurrentYear');
        $this->addFilter('vcv:editor:data:postFields', 'addSiteUrl');

        $this->addFilter('vcv:dynamic:value:site_title', 'siteTitle');
        $this->addFilter('vcv:dynamic:value:site_tagline', 'siteTagline');
        $this->addFilter('vcv:dynamic:value:current_year', 'currentYear');
        $this->addFilter('vcv:dynamic:value:site_url', 'siteUrl');
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addSiteTitle($response, $payload)
    {
        if (get_bloginfo('name')) {
            $response['string']['site']['group']['values'][] = [
                'value' => 'site_title',
                'label' => esc_html__('Site Title', 'visualcomposer'),
            ];

            $response['htmleditor']['site']['group']['values'][] = [
                'value' => 'site_title',
                'label' => esc_html__('Site Title', 'visualcomposer'),
            ];

            $response['inputSelect']['site']['group']['values'][] = [
                'value' => 'site_title',
                'label' => esc_html__('Site Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addSiteTagline($response, $payload)
    {
        if (get_bloginfo('description')) {
            $response['string']['site']['group']['values'][] = [
                'value' => 'site_tagline',
                'label' => esc_html__('Site Tagline', 'visualcomposer'),
            ];

            $response['htmleditor']['site']['group']['values'][] = [
                'value' => 'site_tagline',
                'label' => esc_html__('Site Tagline', 'visualcomposer'),
            ];

            $response['inputSelect']['site']['group']['values'][] = [
                'value' => 'site_tagline',
                'label' => esc_html__('Site Tagline', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addSiteUrl($response, $payload)
    {
        if (get_bloginfo('url')) {
            $response['string']['site']['group']['values'][] = [
                'value' => 'site_url',
                'label' => esc_html__('Site URL', 'visualcomposer'),
            ];

            $response['htmleditor']['site']['group']['values'][] = [
                'value' => 'site_url',
                'label' => esc_html__('Site URL', 'visualcomposer'),
            ];

            $response['inputSelect']['site']['group']['values'][] = [
                'value' => 'site_url',
                'label' => esc_html__('Site URL', 'visualcomposer'),
            ];

            $response['url']['site']['group']['values'][] = [
                'value' => 'site_url',
                'label' => esc_html__('Site', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addCurrentYear($response, $payload)
    {
        $response['string']['site']['group']['values'][] = [
            'value' => 'current_year',
            'label' => esc_html__('Current Year', 'visualcomposer'),
        ];

        $response['htmleditor']['site']['group']['values'][] = [
            'value' => 'current_year',
            'label' => esc_html__('Current Year', 'visualcomposer'),
        ];

        $response['inputSelect']['site']['group']['values'][] = [
            'value' => 'current_year',
            'label' => esc_html__('Current Year', 'visualcomposer'),
        ];

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postAuthor($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostAuthor($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function siteTitle($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getSiteTitle();
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function siteTagline($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getSiteTagline();
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function siteUrl($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getSiteUrl();
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function currentYear($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getCurrentYear();
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }
}
