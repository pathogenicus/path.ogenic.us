<?php

namespace dynamicFields\dynamicFields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'Fields/FieldResponse.php';

use dynamicFields\dynamicFields\Fields\FieldResponse;
use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class DynamicFieldPageViewController
 * Response for dynamic field rendering in user page view.
 *
 * @package dynamicFields\dynamicFields
 */
class DynamicFieldPageViewController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * DynamicFieldPageViewController constructor.
     */
    public function __construct()
    {
        // Use WordPress 5.1 pre_render_block as it is more performance efficient
        if (version_compare(get_bloginfo('version'), '5.1', '>=')) {
            $this->wpAddFilter('pre_render_block', 'renderDynamicBlock', 1000);
        } else {
            $this->wpAddFilter('render_block', 'renderDynamicBlock');
        }
    }

    /**
     * Rendering dynamic blocks.
     *
     * @param null $response
     * @param array $block
     *
     * @return string
     * @throws \ReflectionException|\VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     *
     */
    protected function renderDynamicBlock($response, $block)
    {
        if (!$this->isBlockDynamic($block)) {
            return $response;
        }

        $block = $this->setEmptyInnerContent($block);

        if (!array_key_exists('innerContent', $block) || empty($block['innerContent'])) {
            return $response;
        }

        $response = '';
        $index = 0;
        foreach ($block['innerContent'] as $chunk) {
            $payload = ['chunk' => $chunk, 'block' => $block];
            $styles = vcfilter('vcv:addon:dynamicFields:renderDynamicBlock:styles', '', $payload);

            if (is_string($chunk) && (!empty($chunk) || $chunk === '0')) {
                $response .= $this->call(
                    'getData',
                    [
                        'atts' => $block['attrs'] ?: [],
                        'content' => $chunk,
                        'styles' => $styles,
                    ]
                );
            } elseif (isset($block['innerBlocks'][ $index ])) {
                $innerBlock = $block['innerBlocks'][ $index++ ];
                $content = render_block($innerBlock);

                $response .= $this->call(
                    'getData',
                    [
                        'atts' => $block['attrs'],
                        'content' => $content,
                        'styles' => $styles,
                    ]
                );
            }
        }

        // If there are no pre-rendered content $response should be null
        // @see render_block() function
        if (empty($response)) {
            $response = null;
        }

        return $response;
    }

    /**
     * We always should have inner some inner content.
     *
     * @param array $block
     *
     * @return array
     */
    public function setEmptyInnerContent($block)
    {
        if (empty($block['innerContent'])
            && isset($block['attrs']['currentValue'])
            && $block['attrs']['currentValue'] === '0'
        ) {
            $block['innerContent'][] = '0';
        }

        return $block;
    }

    /**
     * Check if block is dynamic content block.
     *
     * @return bool
     */
    public function isBlockDynamic($block)
    {
        if (!isset($block) || !is_array($block) || !array_key_exists('blockName', $block)) {
            return false;
        }

        if (!is_string($block['blockName'])) {
            return false;
        }

        return strpos($block['blockName'], 'vcv-gutenberg-blocks/dynamic-field-block') !== false;
    }

    /**
     * Get dynamic data.
     *
     * @param array $atts
     * @param string $content html content
     * @param string $styles
     *
     * @return string
     */
    public function getData($atts, $content, $styles)
    {
        if (!array_key_exists('value', $atts)) {
            return $content;
        }

        // Allow defining custom source id for globalTemplate/templateWidget/HFS etc.
        if (!isset($atts['sourceId'])) {
            $atts['sourceId'] = vcfilter('vcv:dynamic:sourceId', get_the_ID());
        }

        $content = vcfilter(
            'vcv:dynamic:value:' . $atts['value'],
            $content,
            [
                'atts' => $atts,
                'controller' => $this,
                'styles' => $styles,
            ]
        );

        return $content;
    }
}
