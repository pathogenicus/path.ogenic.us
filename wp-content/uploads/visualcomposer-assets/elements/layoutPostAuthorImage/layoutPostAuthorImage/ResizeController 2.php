<?php

namespace layoutPostAuthorImage\layoutPostAuthorImage;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Image;
use VisualComposer\Helpers\Traits\EventsFilters;

class ResizeController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('setData:updatePostData:content vcv:templates:create:content', 'parseContent');
    }

    protected function parseContent($content, Image $imageHelper)
    {
        $parsedContent = preg_replace_callback(
            '/\[vcvLayoutPostAuthorImage (.*?)\]/si',
            function ($matches) use ($imageHelper) {
                $result = '';
                $parsed = vchelper('Image')->parseImage($matches);

                $src = '';
                preg_match('(\ssrc=["|\'](.*?)["|\'])', $matches[1], $matchesUrl);
                if (isset($matchesUrl[1])) {
                    $src = $matchesUrl[1];
                }
                $src = $imageHelper->getLazyLoadSrc($parsed, $src);
                $blockAttributes = wp_json_encode(
                    [
                        'type' => get_post_type(),
                        'value' => 'post_author_image',
                        'currentValue' => $src,
                        'atts' => urlencode($parsed),
                    ]
                );
                $result .= '<!-- wp:vcv-gutenberg-blocks/dynamic-field-block ' . $blockAttributes . ' -->';
                $result .= $parsed;
                $result .= '<!-- /wp:vcv-gutenberg-blocks/dynamic-field-block -->';

                return $result;
            },
            $content
        );

        return $parsedContent;
    }
}
