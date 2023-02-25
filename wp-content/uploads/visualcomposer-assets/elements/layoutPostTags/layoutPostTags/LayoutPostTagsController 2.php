<?php

namespace layoutPostTags\layoutPostTags;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}


use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PostData;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class LayoutPostTagsController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:editor:variables', 'addVariables');
        $this->addFilter('vcv:helpers:localizations:i18n', 'addLocalization');
        $this->addFilter(
            'vcv:dynamic:value:layout_post_tags_list_placeholder',
            'postTagsPlaceholder'
        );
        $this->addFilter('vcv:editor:data:postData', 'setPostData');
        $this->addFilter(
            'vcv:addon:dynamicFields:renderDynamicBlock:styles',
            'findTinyMceStyles'
        );
    }

    /**
     * For elements base on a dynamic fields we should find tinyMce additional styles.
     * and use them for our inner elements.
     *
     * @param string $styles
     * @param array $payload
     *
     * @return string
     */
    protected function findTinyMceStyles($styles, $payload)
    {
        $attrs = $payload['block']['attrs'];
        if (empty($attrs['value']) || $attrs['value'] !== 'layout_post_tags_list_placeholder') {
            return $styles;
        }

        if (empty($attrs['currentValue']) || $attrs['currentValue'] !== 'layout_placeholder') {
            return $styles;
        }

        $regex = '/<span style="(.*)">(.*)layout_placeholder(.*)<\/span>/';
        if (preg_match($regex, $payload['chunk'], $match)) {
            if (!empty($match[1])) {
                $styles = $match[1];
            }
        }

        return $styles;
    }

    protected function setPostData($response, PostData $postDataHelper)
    {
        $response['layout_post_tags_list_placeholder'] = 'layout_placeholder';
        $response['layout_post_tags_list_link'] = $postDataHelper->getPostTagsList();

        return $response;
    }

    protected function postTagsPlaceholder($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];

        if (!isset($atts['currentValue'])) {
            return $response;
        }

        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }
        $separator = ', ';
        if (isset($atts['attributes']['separator'])) {
            $separator = $atts['attributes']['separator'];
        }

        $tagsList = $postDataHelper->getPostTagsList($sourceId);

        // we need it if user trying to change color in tinyMce to prevent override theme styles
        if (!empty($payload['styles'])) {
            foreach ($tagsList as $key => $tag) {
                $styles = 'style="' . $payload['styles'] . '"';
                $tagsList[$key] = str_replace('<a href', '<a ' . $styles .' href', $tag);
            }
        }

        $actualValue = implode($separator, $tagsList);
        if (empty($response)) {
            return $actualValue;
        }
        /** @var \dynamicFields\dynamicFields\DynamicFieldController $controller */
        $controller = $payload['controller'];
        $response = $controller->parseResponse($atts['currentValue'], $actualValue, $response);

        return $response;
    }

    /**
     * Add frontend variables.
     *
     * @param array $variables
     *
     * @return array
     */
    protected function addVariables($variables)
    {
        $variables[] = [
            'key' => 'vcvLayoutPostTagsEmptyMessage',
            'value' => __(
                'This post type doesn\'t have tags',
                'visualcomposer'
            ),
            'type' => 'variable',
        ];

        return $variables;
    }

    /**
     * Element localization
     *
     * @param $locale
     */
    protected function addLocalization($locale)
    {
        $locale['layoutPostTagsFirstTag'] =  __(
            'First tag',
            'visualcomposer'
        );
        $locale['layoutPostTagsSecondTag'] =  __(
            'Second tag',
            'visualcomposer'
        );
        $locale['layoutPostTagsThirdTag'] =  __(
            'Third tag',
            'visualcomposer'
        );

        return $locale;
    }
}
