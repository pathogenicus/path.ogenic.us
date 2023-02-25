<?php

namespace layoutPostFeaturedImage\layoutPostFeaturedImage;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

class ResizeController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('setData:updatePostData:content vcv:templates:create:content', 'parseContent');
    }

    protected function parseContent($content)
    {
        $parsedContent = preg_replace_callback(
            '/\[vcvLayoutPostFeaturedImage (.*?)\]/si',
            function ($matches) {
                $blockAttributes = wp_json_encode(
                    [
                        'type' => get_post_type(),
                        'value' => 'layout_featured_image',
                        'atts' => urlencode($matches[1]),
                    ]
                );
                $result = '<!-- wp:vcv-gutenberg-blocks/dynamic-field-block ' . $blockAttributes . ' -->';
                $result .= '_to_be_replaced_'; // do not remove, otherwise $response will be empty and it will not parse block
                $result .= '<!-- /wp:vcv-gutenberg-blocks/dynamic-field-block -->';

                return $result;
            },
            $content
        );

        return $parsedContent;
    }
}
