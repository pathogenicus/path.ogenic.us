<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once('PostTypeController.php');

use VisualComposer\Framework\Illuminate\Support\Module;

class SidebarController extends PostTypeController implements Module
{
    protected $postType = 'vcv_sidebars';

    protected $slug = 'vcv_sidebars';

    protected $postNameSlug = 'Sidebar';

    protected $postNameLowercaseSlug = 'sidebar';

    public function __construct()
    {
        $this->postNameSingular = __('Sidebar', 'visualcomposer');
        $this->postNamePlural = __('Sidebars', 'visualcomposer');

        add_shortcode('vcv_layouts_sidebar', [$this, 'addTemplateShortcode']);

        parent::__construct();
    }

    /**
     * Add shortcode for a current post type.
     *
     * @param array $atts
     *
     * @return false|mixed|string
     */
    public function addTemplateShortcode($atts)
    {
        if (empty($atts) || empty($atts['id']) || $atts['id'] === 'none') {
            return '';
        }

        $requestHelper = vchelper('Request');
        $frontendHelper = vchelper('Frontend');
        $sourceHeader = $requestHelper->input('vcv-sidebar');
        if ($sourceHeader && $frontendHelper->isPageEditable()) {
            $atts['id'] = $sourceHeader;
        }

        if (is_numeric($atts['id'])) {
            return vchelper('Frontend')->renderContent($atts['id']);
        }

        return '';
    }
}
