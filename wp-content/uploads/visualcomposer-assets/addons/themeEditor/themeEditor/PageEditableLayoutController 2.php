<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Framework\Container;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class PageEditableLayoutController
 * @package themeEditor\themeEditor
 */
class PageEditableLayoutController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    /**
     * PageEditableLayoutController constructor.
     */
    public function __construct()
    {
        $this->wpAddAction('wp', 'addBodyHfClass');
        $this->addFilter('vcv:editor:settings:pageTemplatesLayouts:current', 'hfsEditorBlankTemplate', 30);
    }

    /**
     * The HFS editors should have always "blank" behaviour
     *
     * @param $originalTemplate
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @return string
     */
    protected function hfsEditorBlankTemplate(
        $originalTemplate,
        PostType $postTypeHelper,
        Frontend $frontendHelper,
        Request $requestHelper
    ) {
        if ((
                in_array($requestHelper->input('vcv-editor-type', ''), ['vcv_headers', 'vcv_footers', 'vcv_sidebars'])
                && $frontendHelper->isFrontend()
            )
            || $frontendHelper->isPageEditable()
        ) {
            $postId = vcfilter('vcv:editor:settings:pageTemplatesLayouts:current:custom');
            if (in_array($postTypeHelper->get($postId)->post_type, ['vcv_headers', 'vcv_footers', 'vcv_sidebars'])) {
                return ['type' => 'vc', 'value' => 'blank', 'stretchedContent' => 1];
            }
        }

        return $originalTemplate;
    }

    /**
     * Add body class in pageEditable for header/footer editors
     *
     * @param $response
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     *
     * @return mixed
     */
    protected function addBodyHfClass($response, Frontend $frontendHelper, PostType $postTypeHelper)
    {
        if ($frontendHelper->isPageEditable()
            && in_array(
                $postTypeHelper->get()->post_type,
                ['vcv_headers', 'vcv_footers']
            )
        ) {
            $this->wpAddFilter('body_class', 'addHfClass');
        }

        return $response;
    }

    /**
     * Only in vcv_headers, vcv_footers
     *
     * @param $class
     *
     * @return array
     */
    protected function addHfClass($class)
    {
        $class[] = 'vcv-editor-theme-hf';

        return $class;
    }
}
