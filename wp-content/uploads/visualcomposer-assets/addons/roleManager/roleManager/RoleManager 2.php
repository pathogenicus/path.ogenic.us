<?php

namespace roleManager\roleManager;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class RoleManager extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    public function __construct()
    {
        $this->addFilter(
            'vcv:access:role:parts',
            function ($parts) {
                $parts[] = 'dashboard';
                $parts[] = 'hub';
                $parts[] = 'editor_settings';
                $parts[] = 'editor_content';

                return $parts;
            }
        );

        $this->addFilter(
            'vcv:render:settings:roleManager:part:dashboard',
            function ($content, $payload) {
                $payload['addon'] = 'roleManager';
                $content = vcaddonview(
                    'role-manager/dashboard.php',
                    $payload
                );

                return $content;
            }
        );
        $this->addFilter(
            'vcv:render:settings:roleManager:part:hub',
            function ($content, $payload) {
                $payload['addon'] = 'roleManager';
                $content = vcaddonview(
                    'role-manager/hub.php',
                    $payload
                );

                return $content;
            }
        );
        $this->addFilter(
            'vcv:render:settings:roleManager:part:editor_settings',
            function ($content, $payload) {
                $payload['addon'] = 'roleManager';
                $content = vcaddonview(
                    'role-manager/editor-settings.php',
                    $payload
                );

                return $content;
            }
        );
        $this->addFilter(
            'vcv:render:settings:roleManager:part:editor_content',
            function ($content, $payload) {
                $payload['addon'] = 'roleManager';
                $content = vcaddonview(
                    'role-manager/editor-content.php',
                    $payload
                );

                return $content;
            }
        );

        $this->wpAddFilter('register_post_type_args', 'updateVcvPostTypes', 100, 2);
        $this->addFilter('vcv:helper:access:role:defaultCapabilities', 'extendRolePresetCapabilities');
    }

    protected function extendRolePresetCapabilities($defaultCapabilities)
    {
        $addonDefaultCapabilities = [
            'editor' => [
                'dashboard' => [
                    'addon_global_templates',
                    'addon_popup_builder',
                    'settings_custom_html',
                ],
                'hub' => [
                    'elements_templates_blocks',
                    'unsplash',
                    'giphy',
                ],
                'editor_settings' => [
                    'page',
                    'popup',
                    'page_design_options',
                ],
                'editor_content' => [
                    'element_add',
                    'template_add',
                    'block_add',
                    'user_templates_management',
                    'presets_management',
                    'user_blocks_management',
                ],
            ],
            'author' => [
                'hub' => [
                    'unsplash',
                    'giphy',
                ],
                'editor_settings' => [
                    'page',
                    'popup',
                ],
                'editor_content' => [
                    'element_add',
                    'template_add',
                ],
            ],
            'contributor' => [
                'editor_settings' => [
                    'page',
                ],
                'editor_content' => [
                    'element_add',
                    'template_add',
                ],
            ],
            'senior_editor' => [
                'dashboard' => [
                    'addon_theme_builder',
                    'addon_global_templates',
                    'addon_popup_builder',
                    'settings_custom_html',
                ],
                'hub' => [
                    'elements_templates_blocks',
                    'headers_footers_sidebars',
                    'unsplash',
                    'giphy',
                ],
                'editor_settings' => [
                    'page',
                    'popup',
                    'page_design_options',
                ],
                'editor_content' => [
                    'element_add',
                    'template_add',
                    'block_add',
                    'user_templates_management',
                    'presets_management',
                    'user_blocks_management',
                ],
            ],
            'content_manager' => [
                'hub' => [
                    'unsplash',
                    'giphy',
                ],
            ]
        ];

        $result = array_merge_recursive($defaultCapabilities, $addonDefaultCapabilities);

        return $result;
    }

    protected function updateVcvPostTypes($args, $postType)
    {
        if (strpos($postType, 'vcv_') !== false && !in_array($postType, ['vcv_presets', 'vcv_tutorials'])) {
            if (in_array($postType, ['vcv_headers', 'vcv_footers', 'vcv_sidebars', 'vcv_layouts'])) {
                $args['capabilities'] = array_merge(
                    $args['capabilities'],
                    [
                        // Comes from dashboard -> addon_theme_builder
                        'read_post' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'edit_post' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'edit_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'edit_others_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'edit_published_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'publish_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'read' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'read_private_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'delete_post' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'delete_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'delete_published_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'delete_others_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                        'create_posts' => 'vcv_access_rules__dashboard_addon_theme_builder',
                    ]
                );
            }
            if (in_array($postType, ['vcv_templates'])) {
                $args['capabilities'] = array_merge(
                    $args['capabilities'],
                    [
                        // Comes from dashboard -> addon global_templates
                        'read_post' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'edit_post' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'edit_posts' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'edit_others_posts' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'edit_published_posts' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'publish_posts' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'read' => 'vcv_access_rules__dashboard_addon_global_templates',
                        'read_private_posts' => 'vcv_access_rules__dashboard_addon_global_templates',

                        // Removing and Saving Templates
                        'delete_post' => 'vcv_access_rules__editor_content_user_templates_management',
                        'delete_posts' => 'vcv_access_rules__editor_content_user_templates_management',
                        'delete_published_posts' => 'vcv_access_rules__editor_content_user_templates_management',
                        'delete_others_posts' => 'vcv_access_rules__editor_content_user_templates_management',

                        'create_posts' => 'vcv_access_rules__editor_content_user_templates_management',
                    ]
                );
            }
            if (in_array($postType, ['vcv_popups'])) {
                $args['capabilities'] = array_merge(
                    $args['capabilities'],
                    [
                        // Comes from dashboard -> addon_popup_builder
                        'read_post' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'edit_post' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'edit_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'edit_others_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'edit_published_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'publish_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'read' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'read_private_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'delete_post' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'delete_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'delete_published_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'delete_others_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                        'create_posts' => 'vcv_access_rules__dashboard_addon_popup_builder',
                    ]
                );
            }
        }

        return $args;
    }
}
