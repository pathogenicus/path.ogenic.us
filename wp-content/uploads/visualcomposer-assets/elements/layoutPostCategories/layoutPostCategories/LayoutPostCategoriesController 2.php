<?php

namespace layoutPostCategories\layoutPostCategories;

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

class LayoutPostCategoriesController extends Container implements Module
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
            'vcv:dynamic:value:layout_post_categories_list_placeholder',
            'postCategoriesPlaceholder'
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
        if (empty($attrs['value']) || $attrs['value'] !== 'layout_post_categories_list_placeholder') {
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
        $response['layout_post_categories_list_placeholder'] = 'layout_placeholder';
        $response['layout_post_categories_list_link'] = $postDataHelper->getPostCategoriesList();

        return $response;
    }

    protected function postCategoriesPlaceholder($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }
        $separator = ', ';
        if (isset($atts['attributes']['separator'])) {
            $separator = $atts['attributes']['separator'];
        }

        if (isset($atts['currentValue'])) {
            $categoriesList = $postDataHelper->getPostCategoriesList($sourceId);

            // we need it if user trying to change color in tinyMce to prevent override theme styles
            if (!empty($payload['styles'])) {
                foreach ($categoriesList as $key => $category) {
                    $styles = 'style="' . $payload['styles'] . '"';
                    $categoriesList[$key] = str_replace('<a href', '<a ' . $styles .' href', $category);
                }
            }

            $actualValue = implode($separator, $categoriesList);
            if (empty($response)) {
                return $actualValue;
            }
            /** @var \dynamicFields\dynamicFields\DynamicFieldController $controller */
            $controller = $payload['controller'];
            $response = $controller->parseResponse($atts['currentValue'], $actualValue, $response);
        }

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
            'key' => 'vcvLayoutPostCategoriesEmptyMessage',
            'value' => __(
                'This post type doesn\'t have categories',
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
        $locale['layoutPostCategoriesFirstCategory'] =  __(
            'First category',
            'visualcomposer'
        );
        $locale['layoutPostCategoriesSecondCategory'] =  __(
            'Second category',
            'visualcomposer'
        );
        $locale['layoutPostCategoriesThirdCategory'] =  __(
            'Third category',
            'visualcomposer'
        );

        return $locale;
    }
}
