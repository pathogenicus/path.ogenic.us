<?php

namespace globalTemplate\globalTemplate;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\EditorTemplates;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

class PostTypeController extends Container implements Module
{
    use Page;
    use EventsFilters;
    use WpFiltersActions;
    use SubMenu;

    protected $postType = 'vcv_templates';

    protected $slug = 'vcv_templates';

    protected $templatePath = 'settings/pages/custom-post-type';

    public function __construct()
    {
        // Set iframe-css for post type
        \VcvEnv::set('VCV_DASHBOARD_IFRAME_VCV_TEMPLATES', true);
        // Set dashboard modifications for addon (needed for BC when addons not updated)
        \VcvEnv::set('VCV_HUB_ADDON_DASHBOARD_GLOBALTEMPLATE', true);
        $this->addFilter('vcv:frontend:url', 'addTypeToLink');
        $this->addFilter('vcv:helpers:access:editorPostType', 'addPostType');
        $this->wpAddAction('admin_init', 'doRedirect');
        /** @see \globalTemplate\globalTemplate\PostTypeController::removePostActions */
        $this->wpAddFilter('bulk_actions-edit-' . $this->postType, 'removePostActions', 10, 1);
        $this->wpAddFilter('post_row_actions', 'updatePostEditBarLinks');
        $this->wpAddFilter('post_row_actions', 'showConfirmOnRemove');
        /** @see \globalTemplate\globalTemplate\PostTypeController::addPostStatus */
        $this->wpAddFilter('display_post_states', 'addPostStatus');
        $this->addFilter('vcv:addons:exportImport:allowedPostTypes', 'addPostType');

        $this->wpAddAction(
            'admin_menu',
            'addPage',
            20
        );
    }

    /**
     * Create settings page
     * @throws \Exception
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->postType,
            'title' => __('Global Templates', 'visualcomposer'),
            'subTitle' => __('Templates', 'visualcomposer'),
            'layout' => 'dashboard-tab-content-nopadding',
            'capability' => 'edit_vcv_templatess',
            'capabilityPart' => 'dashboard_addon_global_templates',
            'iconClass' => 'vcv-ui-icon-dashboard-template',
            'isDashboardPage' => true,
            'forceReloadOnOpen' => true,
            'hideTitle' => true,
            'hideInWpMenu' => false,
        ];
        $this->addSubmenuPage($page, $this->postType);
    }

    /**
     * Add post type support for frontend editor
     *
     * @param $postTypes
     *
     * @return array
     */
    protected function addPostType($postTypes)
    {
        if (!in_array($this->postType, $postTypes, true)) {
            $postTypes[] = $this->postType;
        }

        return $postTypes;
    }

    /**
     * @param $url
     * @param $payload
     *
     * @return string
     */
    protected function addTypeToLink($url, $payload)
    {
        if ($this->postType === get_post_type($payload['sourceId'])) {
            return add_query_arg(['vcv-editor-type' => $this->postType], $url);
        }

        return $url;
    }

    /**
     * Redirect to frontend editor
     *
     * @param \VisualComposer\Helpers\Request $requestHelper
     */
    protected function doRedirect(Request $requestHelper)
    {
        global $pagenow;
        if (($pagenow === 'post-new.php' || ($pagenow === 'post.php' && $requestHelper->input('action') === 'edit'))
            && (
                $requestHelper->input('post_type') === $this->postType
                || get_post_type($requestHelper->input('post')) === $this->postType
            )
            && !$requestHelper->exists('vcv-action')
        ) {
            $frontendHelper = vchelper('Frontend');
            //redirect from classic editor to frontend editor
            if ($pagenow === 'post.php' && $requestHelper->input('post')) {
                $sourceId = $requestHelper->input('post');
                wp_redirect(
                    $frontendHelper->getFrontendUrl($sourceId)
                );
                exit;
            }
            wp_redirect(
                add_query_arg(['vcv-action' => 'frontend', 'vcv-editor-type' => rawurlencode($this->postType)])
            );
            exit;
        }
    }

    /**
     * Remove edit/trash action from dropdown
     *
     * @param $actions
     *
     * @return mixed
     */
    protected function removePostActions($actions)
    {
        global $post;

        // @codingStandardsIgnoreLine
        if (isset($post->post_type) && $post->post_type === $this->postType) {
            unset($actions['edit']);
        }

        return $actions;
    }

    /**
     * Update update post edit bar links
     *
     * @param $actions
     * @param $post
     *
     * @return mixed
     */
    protected function updatePostEditBarLinks($actions, $post)
    {
        // @codingStandardsIgnoreLine
        if ($post->post_type === $this->postType) {
            unset($actions['inline hide-if-no-js'], $actions['edit'], $actions['view'], $actions['preview']);
            $actions = array_reverse($actions);
        }

        return $actions;
    }

    /**
     * Add confirm for hub and predefined templates
     *
     * @param $actions
     * @param $post
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     *
     * @return mixed
     */
    protected function showConfirmOnRemove($actions, $post, EditorTemplates $editorTemplatesHelper)
    {
        $templateType = get_post_meta($post->ID, '_vcv-type', true);
        if (
            // @codingStandardsIgnoreLine
            isset($actions['edit_vc5']) && $post->post_type === $this->postType
            && !$editorTemplatesHelper->isUserTemplateType($templateType)
        ) {
            $confirm = __(
                'You\'re about to edit downloaded template. The template will be converted to your personal template. You can download a new copy of this predefined template from the Hub.',
                'visualcomposer'
            );
            $actions['edit_vc5'] = preg_replace(
                '/(href=("|\')(.*?)("|\'))/',
                sprintf(
                    '$1 onclick="return confirm(this.getAttribute(\'data-vcv-confirm-text\'))" data-vcv-confirm-text="%s"',
                    esc_attr($confirm)
                ),
                $actions['edit_vc5']
            );
        }

        return $actions;
    }

    /**
     * @param $postStates
     * @param $post
     * @param \VisualComposer\Helpers\EditorTemplates $editorTemplatesHelper
     *
     * @return mixed
     */
    protected function addPostStatus($postStates, $post, EditorTemplates $editorTemplatesHelper)
    {
        // @codingStandardsIgnoreLine
        if ($post->post_type === $this->postType) {
            $sourceId = $post->ID;
            $templateType = get_post_meta($sourceId, '_vcv-type', true);

            if ($templateType) {
                $groupName = $editorTemplatesHelper->getGroupName($templateType);
                $postStates[ $templateType ] = $groupName;
            }
        }

        return $postStates;
    }
}
