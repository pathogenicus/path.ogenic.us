<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
require_once('PostTypeController.php');

use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\PageLayout;
use VisualComposer\Helpers\Traits\EventsFilters;

class HeaderController extends PostTypeController implements Module
{
    use EventsFilters;

    protected $postType = 'vcv_headers';

    protected $slug = 'vcv_headers';

    protected $postNameSlug = 'Header';

    protected $postNameLowercaseSlug = 'header';

    public function __construct()
    {
        $this->postNameSingular = __('Header', 'visualcomposer');
        $this->postNamePlural = __('Headers', 'visualcomposer');

        add_shortcode('vcv_layouts_header', [$this, 'addTemplateShortcode']);

        parent::__construct();

        if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
            $this->wpAddAction('render_block', 'replaceBlockTemplate', 10, 2);
        } else {
            $this->wpAddAction('get_header', 'replaceTemplate');
        }
    }

    /**
     * Get template content wrapper start.
     *
     * @return string
     */
    protected function getTemplateWrapperStart()
    {
        return '<header class="vcv-header" data-vcv-layout-zone="header">';
    }

    /**
     * Get template content wrapper end.
     *
     * @return string
     */
    protected function getTemplateWrapperEnd()
    {
        return '</header>';
    }
}
