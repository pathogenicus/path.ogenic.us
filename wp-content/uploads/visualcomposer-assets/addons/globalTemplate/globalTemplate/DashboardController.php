<?php

namespace globalTemplate\globalTemplate;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class DashboardController extends Container implements Module
{
    use WpFiltersActions;

    public function __construct()
    {
        $this->wpAddFilter('register_post_type_args', [$this, 'enableTemplatePostType'], 10, 2);
    }

    public function enableTemplatePostType($args, $postType)
    {
        if ('vcv_templates' === $postType) {
            $args['public'] = false;
            $args['show_ui'] = true;
            $args['show_in_menu'] = false;
            $args['hierarchical'] = false;
            $args['has_archive'] = false;
            $args['query_var'] = false;
            $args['rewrite'] = false;
            $args['publicly_queryable'] = false;
        }

        return $args;
    }
}
