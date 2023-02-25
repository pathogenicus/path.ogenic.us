<?php

namespace postsGridFilter\postsGridFilter;

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

class CheckboxController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter('vcv:attribute:checkbox:query:postGridFilter:taxonomy:render', 'taxonomyIdSuggester');
    }

    protected function taxonomyIdSuggester($response, $payload)
    {
        $element = $payload['element'];
        // In postGridFilter element specific terms can be selected as checkbox
        // Taxonomy source is available in $element['filter_source']
        $terms = get_terms(
            [
                'taxonomy' => esc_attr($element['filter_source']),
                'hide_empty' => 0,
                'hierarchical' => 1,
                'orderby' => 'term_id',
            ]
        );
        $response['results'] = [];
        if (is_array($terms) && !empty($terms)) {
            foreach ($terms as $value) {
                /** @var \WP_Term $data */
                $data = [];
                // @codingStandardsIgnoreLine
                $data['value'] = $value->term_id;
                // @codingStandardsIgnoreLine
                $data['id'] = $value->term_id;
                $data['parent'] = $value->parent;
                $data['label'] = $value->name;
                $response['results'][] = $data;
            }
        }

        return $response;
    }
}
