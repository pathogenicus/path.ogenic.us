<?php

namespace templateWidget\templateWidget;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\AssetsEnqueue;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class WidgetController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    public function __construct()
    {
        $this->wpAddAction('widgets_init', 'registerTemplateWidget');
    }

    protected function registerTemplateWidget()
    {
        register_widget('templateWidget\templateWidget\TemplateWidget');
    }

    public function getTemplateContent($templateId)
    {
        vchelper('AssetsEnqueue')->addToEnqueueList($templateId);
        // Only if we are in content
        ob_start();
        // Trigger enqueue (only CSS)
        vcevent('vcv:assets:enqueue:css:list');

        // Forcerly print styles before content, to avoid flashing/jumping
        print_late_styles();
        $styles = ob_get_clean(); // Fix to put source-css before content to avoid flashing VC-1210

        $output = $styles;
        $previousDynamicContent = \VcvEnv::get('DYNAMIC_CONTENT_SOURCE_ID');
        if (empty($previousDynamicContent)) {
            \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', get_the_ID());
        }
        $postContent = vchelper('Frontend')->renderContent($templateId);
        \VcvEnv::set(
            'DYNAMIC_CONTENT_SOURCE_ID',
            $previousDynamicContent
        ); // return back in case if multiple posts nested

        $output .= $this->addWidgetWrapper($postContent);

        return $output;
    }

    protected function addWidgetWrapper($content)
    {
        $content = '<div class="vcv-template-widget">' . $content . '</div>';

        return $content;
    }
}
