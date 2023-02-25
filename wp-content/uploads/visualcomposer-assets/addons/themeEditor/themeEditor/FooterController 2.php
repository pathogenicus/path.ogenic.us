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

class FooterController extends PostTypeController implements Module
{

    protected $postType = 'vcv_footers';

    protected $slug = 'vcv_footers';

    protected $postNameSlug = 'Footer';

    protected $postNameLowercaseSlug = 'footer';

    public function __construct()
    {
        $this->postNameSingular = __('Footer', 'visualcomposer');
        $this->postNamePlural = __('Footers', 'visualcomposer');

        add_shortcode('vcv_layouts_footer', [$this, 'addTemplateShortcode']);

        parent::__construct();

        if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
            $this->wpAddAction('render_block', 'replaceBlockTemplate', 10, 2);
        } else {
            $this->wpAddAction('get_footer', 'replaceTemplate');
        }
    }

    /**
     * Get template content wrapper start.
     *
     * @return string
     */
    protected function getTemplateWrapperStart()
    {
        return '<footer class="vcv-footer" data-vcv-layout-zone="footer">';
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
