<?php

namespace themeBuilder\themeBuilder\pages;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Assets;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\PageLayout;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Url;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

/**
 * Class LayoutPostTypeController
 * @package themeBuilder\themeBuilder\pages
 */
class LayoutPostTypeController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use SubMenu;
    use Page;

    /**
     * @var string
     */
    protected $postType;

    /**
     * @var string|void
     */
    protected $postNameSingular;

    /**
     * @var string|void
     */
    protected $postNamePlural;

    /**
     * @var string|void
     */
    protected $slug = 'vcv_layouts';

    /**
     * @var string|void
     */
    protected $templatePath;

    /**
     * @var int
     */
    private $layoutSourceId;

    /**
     * @var int
     */
    private $currentSourceId;

    /**
     * LayoutPostTypeController constructor.
     */
    public function __construct()
    {
        if (!vcvenv('VCV_FT_THEME_BUILDER_LAYOUTS')) {
            return;
        }
        $this->postType = 'vcv_layouts';
        $this->postNameSingular = __('Layout', 'visualcomposer');
        $this->postNamePlural = __('Layouts', 'visualcomposer');
        $this->templatePath = 'settings/pages/custom-post-type';

        \VcvEnv::set('VCV_DASHBOARD_IFRAME_' . strtoupper($this->postType), true);
        \VcvEnv::set('VCV_FT_JS_THEME_BUILDER_CUSTOM_LAYOUTS', true);
        $this->addEvent('vcv:inited', 'registerLayoutPostType', 9);
        $this->wpAddAction('admin_init', 'doRedirect');
        $this->addFilter('vcv:frontend:url', 'addTypeToLink');
        $this->addFilter('vcv:helpers:access:editorPostType', 'addEditorPostType');
        $this->addEvent('vcv:inited', 'coreCapabilities');
        $this->wpAddAction('template_redirect', 'renderLayouts', 110);
        $this->addFilter('vcv:editor:variables', 'addCustomLayoutVariables', 11); //to load after editor on windows

        $this->addFilter('vcv:dataAjax:getData', 'removeLayoutTemplates', 2);
        $this->addFilter('vcv:helpers:templates:getCustomTemplates', 'removeCustomLayoutTemplates');
        $this->addFilter('vcv:dataAjax:getData', 'addLayoutType');
        $this->addFilter('vcv:dataAjax:setData', 'saveLayoutType');

        $this->wpAddFilter('manage_vcv_layouts_posts_columns', 'addCustomColumn');
        $this->wpAddAction('manage_vcv_layouts_posts_custom_column', 'addCustomColumnValues', 10, 2);
        $this->wpAddFilter('manage_edit-vcv_layouts_sortable_columns', 'addCustomColumnSorting');
        $this->wpAddAction('pre_get_posts', 'addCustomColumnOrderBy');

        // Save custom template type as Layouts type
        $this->addFilter(
            'vcv:editorTemplates:template:type',
            'setTemplateType'
        );
        $this->addFilter('vcv:template:groupName', 'getGroupName');

        // Set default template type to vc-custom-layout
        $this->addFilter('vcv:editor:settings:pageTemplatesLayouts:current', 'setDefaultPageTemplatesLayout', 30);
        $this->addFilter(
            'vcv:editor:settings:pageTemplatesLayouts:theme',
            function ($themeTemplates) {
                for ($i = 0, $iMax = count($themeTemplates); $i < $iMax; $i++) {
                    if ($themeTemplates[ $i ]['value'] === 'default') {
                        $themeTemplates[ $i ]['label'] = __('WordPress: Default', 'visualcomposer');
                    }
                    $themeTemplates[ $i ]['value'] = 'theme:' . $themeTemplates[ $i ]['value'];
                }

                return $themeTemplates;
            },
            10
        );
        $this->addFilter(
            'vcv:ajax:layoutDropdown:vc-custom-layout:updateList:adminNonce',
            'getPostList',
            11
        );

        // Load Env Variables
        $this->addFilter(
            'vcv:editors:frontend:render',
            function ($response, Request $requestHelper, Frontend $frontendHelper) {
                if ($requestHelper->input('vcv-editor-type') === $this->postType && $frontendHelper->isFrontend()) {
                    $this->addFilter(
                        'vcv:frontend:footer:extraOutput',
                        function ($response, $payload, Addons $hubAddonsHelper) {
                            return $hubAddonsHelper->addFooterBundleScriptAddon(
                                $response,
                                'theme-builder',
                                'themeBuilder'
                            );
                        },
                        11
                    );
                    $this->addFilter('vcv:editor:variables', 'addEditorTypeVariable');
                    /** @see \themeBuilder\themeBuilder\pages\LayoutPostTypeController::setCurrentPageLayout */
                    $this->addFilter('vcv:editor:settings:pageTemplatesLayouts:current', 'setCurrentPageLayout');
                    /** @see \themeBuilder\themeBuilder\pages\LayoutPostTypeController::updateEditorBlankLayout */
                    $this->addFilter('vcv:editor:settings:pageTemplatesLayouts', 'updateEditorBlankLayout');
                }

                return $response;
            }
        );
        $this->wpAddFilter('template_include', 'layoutEditorBlankTemplate', 30);
        add_shortcode(
            'vcwb_layout_comments_area',
            function ($atts, $content, $tag) {
                return $this->getCommentsTemplate();
            }
        );

        $this->wpAddAction(
            'admin_menu',
            'addPage'
        );
        $this->wpAddFilter('bulk_actions-edit-' . $this->postType, 'removePostActions', 10, 1);
        $this->wpAddFilter('post_row_actions', 'updatePostEditBarLinks');

        $this->addFilter('vcv:dynamic:sourceId', function ($sourceId) {
            // Needed to provide proper scope default sourceId for dynamic content rendering
            if (!empty($this->currentSourceId)) {
                return $this->currentSourceId;
            }

            return $sourceId;
        });
    }

    /**
     * Create settings page
     * @throws \Exception
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->postType,
            'title' => $this->postNamePlural,
            'layout' => 'dashboard-tab-content-nopadding',
            'capability' => 'edit_vcv_layouts',
            'capabilityPart' => 'dashboard_addon_theme_builder',
            'isDashboardPage' => true,
            'forceReloadOnOpen' => true,
            'hideTitle' => true,
            'hideInWpMenu' => true,
        ];
        $this->addSubmenuPage($page, 'vcv-headers-footers');
    }

    /**
     * The HFS editors should have always "blank" behaviour
     *
     * @param $originalTemplate
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return string
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function layoutEditorBlankTemplate(
        $originalTemplate,
        PostType $postTypeHelper,
        Frontend $frontendHelper
    ) {
        $post = $postTypeHelper->get();
        // @codingStandardsIgnoreLine
        if ($post && $frontendHelper->isPageEditable() && $post->post_type === 'vcv_layouts') {
            $template = 'blank-template.php';

            return vcapp()->path('visualcomposer/resources/views/editor/templates/') . $template;
        }

        return $originalTemplate;
    }

    protected function addCustomColumn($columns)
    {
        return array_slice($columns, 0, 2) + ['layoutType' => __('Layout Type', 'visualcomposer')] + array_slice($columns, 2);
    }

    protected function addCustomColumnValues($columnKey, $postId)
    {
        if ($columnKey == 'layoutType') {
            $layoutType = get_post_meta($postId, VCV_PREFIX . 'layoutType', true);
            if ($layoutType === 'archiveTemplate') {
                echo esc_attr__('Archive layout', 'visualcomposer');
            } elseif (empty($layoutType) || $layoutType === 'postTemplate') {
                echo esc_attr__('Singular layout', 'visualcomposer');
            }
        }
    }

    protected function addCustomColumnSorting($columns)
    {
        $columns['layoutType'] = 'layoutType';

        return $columns;
    }

    protected function addCustomColumnOrderBy($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ('layoutType' === $query->get('orderby')) {
            $query->set('orderby', 'meta_value');
            $query->set('meta_key', VCV_PREFIX . 'layoutType');
        }
    }

    protected function updateEditorBlankLayout($layouts, $payload)
    {
        foreach ($layouts as $layoutTypeIndex => $layoutType) {
            if ($layoutType['type'] === 'vc') {
                foreach ($layoutType['values'] as $layoutIndex => $layout) {
                    if ($layout['value'] === 'blank') {
                        $layouts[ $layoutTypeIndex ]['values'][ $layoutIndex ]['header'] = true;
                        $layouts[ $layoutTypeIndex ]['values'][ $layoutIndex ]['footer'] = true;
                        $layouts[ $layoutTypeIndex ]['values'][ $layoutIndex ]['sidebar'] = true;
                    }
                }
            }
        }

        return $layouts;
    }

    protected function setCurrentPageLayout($response, $payload)
    {
        return [
            'value' => 'blank',
            'type' => 'vc',
            'header' => true,
            'footer' => true,
        ];
    }

    /**
     * @param $type
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @return string
     */
    protected function setTemplateType($type, Request $requestHelper)
    {
        if ($requestHelper->input('vcv-editor-type') === 'vcv_layouts') {
            $type .= 'Layout';
        }
        if ($requestHelper->exists('vcv-layout-type')) {
            $type .= $requestHelper->input('vcv-layout-type');
        }

        return $type;
    }

    protected function getGroupName($name, $payload)
    {
        if ($payload['key'] === 'customLayout') {
            $name = __('My Layout Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'hubLayout') {
            $name = __('Hub Layout Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'customLayoutpostTemplate') {
            $name = __('My Singular Layout Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'customLayoutarchiveTemplate') {
            $name = __('My Archive Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'customBlockLayoutarchiveTemplate') {
            $name = __('My Archive Block Templates', 'visualcomposer');
        } elseif ($payload['key'] === 'customBlockLayoutpostTemplate') {
            $name = __('My Singular Layout Block Templates', 'visualcomposer');
        }

        return $name;
    }

    /**
     * Create layout post type
     *
     */
    protected function registerLayoutPostType()
    {
        register_post_type(
            $this->postType,
            [
                'labels' => [
                    'name' => $this->postNamePlural,
                    'singular_name' => $this->postNameSingular,
                    'menu_name' => $this->postNamePlural,
                    'add_new' => sprintf(__('Add %s', 'visualcomposer'), $this->postNameSingular),
                    'add_new_item' => sprintf(__('Add New %s', 'visualcomposer'), $this->postNameSingular),
                    'edit' => __('Edit', 'visualcomposer'),
                    'edit_item' => sprintf(__('Edit %s', 'visualcomposer'), $this->postNameSingular),
                    'new_item' => sprintf(__('New %s', 'visualcomposer'), $this->postNameSingular),
                    'view' => sprintf(__('View %s', 'visualcomposer'), $this->postNamePlural),
                    'view_item' => sprintf(__('View %s', 'visualcomposer'), $this->postNameSingular),
                    'search_items' => sprintf(__('Search %s', 'visualcomposer'), $this->postNameSingular),
                    'not_found' => sprintf(__('No %s Found', 'visualcomposer'), $this->postNamePlural),
                    'not_found_in_trash' => sprintf(
                        __('No %s Found in Trash', 'visualcomposer'),
                        $this->postNamePlural
                    ),
                    'parent' => sprintf(__('Parent %s', 'visualcomposer'), $this->postNameSingular),
                    'filter_items_list' => sprintf(__('Filter %s', 'visualcomposer'), $this->postNamePlural),
                    'items_list_navigation' => sprintf(__('%s Navigation', 'visualcomposer'), $this->postNamePlural),
                    'items_list' => sprintf(__('%s List', 'visualcomposer'), $this->postNamePlural),
                ],
                'public' => false,
                'capability_type' => [$this->postType, $this->postType . 's'],
                'capabilities' => [
                    'edit_post' => 'edit_' . $this->postType,
                    'read_post' => 'read_' . $this->postType,
                    'delete_post' => 'delete_' . $this->postType,
                    'edit_posts' => 'edit_' . $this->postType . 's',
                    'edit_others_posts' => 'edit_others_' . $this->postType . 's',
                    'publish_posts' => 'publish_' . $this->postType . 's',
                    'read_private_posts' => 'read_private_' . $this->postType . 's',
                    'create_posts' => 'edit_' . $this->postType . 's',
                    'edit_published_posts' => 'edit_published_' . $this->postType . 's',
                    'delete_posts' => 'delete_' . $this->postType . 's',
                    'delete_private_posts' => 'delete_private_' . $this->postType . 's',
                    'delete_published_posts' => 'delete_published_' . $this->postType . 's',
                    'delete_others_posts' => 'delete_others_' . $this->postType . 's',
                    'read' => 'read_' . $this->postType,
                ],
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_icon' => 'dashicons-admin-page',
                'hierarchical' => false,
                'taxonomies' => [],
                'has_archive' => false,
                'rewrite' => false,
                'query_var' => false,
                'show_in_nav_menus' => false,
            ]
        );
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
            //redirect from classic editor to frontend editor
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

    protected function renderLayouts(Request $requestHelper, Frontend $frontendHelper)
    {
        // Skip for ajax request prevent to broke shortcode elements
        if ($requestHelper->isAjax()) {
            return;
        }
        // Skip if different template selected
        if ($frontendHelper->isPageEditable()
            && $requestHelper->input('vcv-template-type') !== 'vc-custom-layout'
            && $requestHelper->exists('vcv-template')
        ) {
            return;
        }

        $keepOriginalPostScope = true;
        $originalSourceId = get_the_ID();
        $sourceId = $originalSourceId;

        $sourceId = vchelper('Preview')->updateSourceIdWithAutosaveId($sourceId);
        $this->layoutSourceId = $this->getLayoutId($sourceId);

        $this->currentSourceId = $originalSourceId;

        if (!empty($this->layoutSourceId) && !empty($this->currentSourceId)) {
            $this->printLayout($keepOriginalPostScope);
        }
    }

    /**
     * Get layout is for a certain post.
     *
     * @param $sourceId
     *
     * @return int
     */
    public function getLayoutId($sourceId)
    {
        $frontendHelper = vchelper('Frontend');
        $requestHelper = vchelper('Request');
        $optionsHelper = vchelper('Options');

        // we can set layout individually for a current post in the editor post settings.
        $isCustomLayoutTemplate = $frontendHelper->isPageEditable()
            && $requestHelper->input('vcv-template-type') === 'vc-custom-layout'
            && $requestHelper->exists('vcv-template');

        if ($isCustomLayoutTemplate) {
            $currentPostType = get_post_type($sourceId);
            if ($requestHelper->input('vcv-template') === 'default' && $this->postType !== $currentPostType) {
                $this->layoutSourceId = (int)$optionsHelper->get(
                    'custom-page-templates-' . $currentPostType . '-layout',
                    ''
                );
            } else {
                $this->layoutSourceId = (int)$requestHelper->input('vcv-template');
            }
        } elseif (is_page() || is_singular()) {
            $this->setLayoutId($sourceId, $optionsHelper);
        }

        return $this->layoutSourceId;
    }

    /**
     * Update default page template layout
     *
     * @param array $output
     * @param array $payload
     *
     * @return array
     */
    protected function setDefaultPageTemplatesLayout($output, $payload)
    {
        // BC break for v41 all previously theme&default become vc-custom-layout&default.
        if ($output['type'] === 'theme' && $output['value'] === 'default') {
            $output = [
                'type' => 'vc-custom-layout',
                'value' => 'default',
            ];
        }

        // Fix BC for old values
        if ($output['type'] === 'theme' && $output['value'] !== 'default') {
            $output = [
                'type' => 'vc-custom-layout',
                'value' => 'theme:' . $output['value'],
            ];
        }

        return $output;
    }

    protected function printLayout($keepOriginalPostScope)
    {
        global $post;

        if ($keepOriginalPostScope) {
            $templatePost = get_post($this->layoutSourceId);
            if (!$templatePost || get_post_status($templatePost) !== 'publish') {
                return;
            }
            $templateId = $templatePost->ID;
            vchelper('AssetsEnqueue')->addToEnqueueList($templateId);

            // @codingStandardsIgnoreLine
            $layoutContent = apply_filters('the_content', $templatePost->post_content);
        } else {
            $layoutContent = vchelper('Frontend')->renderContent($this->layoutSourceId);
        }

        // @codingStandardsIgnoreStart
        global $wp_query, $wp_the_query;
        $backup = $wp_query;
        $backupGlobal = $wp_the_query;
        list($post, $wp_query, $wp_the_query) = $this->setGlobalLayoutQuery();

        $template = $this->getLayoutTemplateFile();

        // Now need to revert back all the queries
        $wp_query = $backup;
        $wp_the_query = $backupGlobal; // fix wp_reset_query
        // @codingStandardsIgnoreEnd
        wp_reset_postdata();

        // we shouldn't run wpautop()
        $priority = has_filter('the_content', 'wpautop');
        if (false !== $priority) {
            remove_filter('the_content', 'wpautop', $priority);
            add_filter('the_content', '_restore_wpautop_hook', $priority + 1);
        }

        $this->mergeLayoutAndPostContent($layoutContent);

        if ($template) {
            include $template;
        }
        exit;
    }

    /**
     * Merge layout and post content.
     *
     * @param string $layoutContent
     */
    protected function mergeLayoutAndPostContent($layoutContent)
    {
        do_action('vcv:addons:themeBuilder:mergeLayoutAndPostContent:before');

        add_filter(
            'the_content',
            function ($content) use ($layoutContent) {

                $isCurrentPostHasLayoutPlaceholder = get_the_ID() === $this->currentSourceId
                    && strpos($layoutContent, '{{vcwb-layout-content-area}}') !== false;

                if ($isCurrentPostHasLayoutPlaceholder) {
                    return str_replace('{{vcwb-layout-content-area}}', $content, $layoutContent);
                }

                return $content;
            },
            98
        );

        do_action('vcv:addons:themeBuilder:mergeLayoutAndPostContent:after');
    }

    protected function getCommentsTemplate()
    {
        ob_start();
        $requestHelper = vchelper('Request');
        $force = false;
        if ($requestHelper->isAjax()) {
            $GLOBALS['withcomments'] = true;
            add_filter('comments_open', '__return_true');
            $force = true;
        }
        if ($force || get_post()) {
            $addonsHelper = vchelper('HubAddons');
            $path = rtrim($addonsHelper->getAddonRealPath('themeBuilder'), '/\\') . '/views/comments.php';
            $templatePathCallback = function ($file) use ($path) {
                if (strpos($file, $path) !== false) {
                    // Fix for $theme_template = STYLESHEETPATH . $file
                    return $path;
                }

                return $file;
            };
            add_filter('comments_template', $templatePathCallback);
            comments_template($path);
            remove_filter('comments_template', $templatePathCallback);
        }
        if ($force) {
            remove_filter('comments_open', '__return_true');
        }

        return ob_get_clean();
    }

    /**
     * @param $url
     * @param $payload
     *
     * @return string
     */
    protected function addTypeToLink($url, $payload)
    {
        $templateType = get_post_meta(
            $payload['sourceId'],
            '_' . VCV_PREFIX . 'type',
            true
        );
        if ('vcv_templates' === get_post_type($payload['sourceId'])
            && strpos($templateType, 'Layout') !== false
        ) {
            return add_query_arg(['vcv-editor-type' => $this->postType], $url);
        }

        if ($this->postType === get_post_type($payload['sourceId'])) {
            return add_query_arg(['vcv-editor-type' => $this->postType], $url);
        }

        return $url;
    }

    /**
     * Add post type support for frontend editor
     *
     * @param $postTypes
     *
     * @return array
     */
    protected function addEditorPostType($postTypes)
    {
        if (!in_array($this->postType, $postTypes, true)) {
            $postTypes[] = $this->postType;
        }

        return $postTypes;
    }

    /**
     * Add Post Type Capabilities to User Roles
     */
    protected function coreCapabilities()
    {
        $optionsHelper = vchelper('Options');

        // Capability migration for custom VC post types
        if (!$optionsHelper->get($this->postType . '-capability-migration')) {
            // @codingStandardsIgnoreStart
            global $wp_roles;
            $optionsHelper->delete($this->postType . '-capabilities-set');
            $wp_roles->remove_cap('contributor', 'read_' . $this->postType);
            $wp_roles->remove_cap('contributor', 'edit_' . $this->postType);
            $wp_roles->remove_cap('contributor', 'delete_' . $this->postType);
            $wp_roles->remove_cap('contributor', 'edit_' . $this->postType . 's');
            $wp_roles->remove_cap('contributor', 'delete_' . $this->postType . 's');
            $optionsHelper->set($this->postType . '-capability-migration', 1);
            // @codingStandardsIgnoreEnd
        }

        if ($optionsHelper->get($this->postType . '-capabilities-set')) {
            return;
        }

        $roles = ['administrator', 'editor'];

        foreach ($roles as $role) {
            $roleObject = get_role($role);
            if (!$roleObject) {
                continue;
            }

            $capabilities = [
                "read_{$this->postType}",
                "edit_{$this->postType}",
                "delete_{$this->postType}",
                "edit_{$this->postType}s",
                "delete_{$this->postType}s",
            ];

            if ($role === 'contributor') {
                $capabilities = [
                    "read_{$this->postType}",
                    "edit_{$this->postType}s",
                    "delete_{$this->postType}s",
                ];
            }

            if (in_array($role, ['administrator', 'editor', 'author'])) {
                $capabilities = array_merge(
                    $capabilities,
                    [
                        "delete_published_{$this->postType}s",
                        "publish_{$this->postType}s",
                        "edit_published_{$this->postType}s",
                    ]
                );
            }

            if (in_array($role, ['administrator', 'editor'])) {
                $capabilities = array_merge(
                    $capabilities,
                    [
                        "read_private_{$this->postType}s",
                        "edit_private_{$this->postType}s",
                        "delete_private_{$this->postType}s",
                        "delete_others_{$this->postType}s",
                        "delete_{$this->postType}",
                        "edit_others_{$this->postType}s",
                        "create_{$this->postType}s",
                    ]
                );
            }

            if ($roleObject) {
                foreach ($capabilities as $cap) {
                    $roleObject->add_cap($cap);
                }

                $optionsHelper->set($this->postType . '-capabilities-set', 1);
            }
        }
        // reset current user all caps
        wp_get_current_user()->get_role_caps();
    }

    /**
     * @param $variables
     *
     * @return array
     */
    protected function addEditorTypeVariable($variables)
    {
        $editorType = false;
        $key = 'VCV_EDITOR_TYPE';
        foreach ($variables as $i => $variable) {
            if ($variable['key'] === $key) {
                $variables[ $i ] = [
                    'key' => 'VCV_EDITOR_TYPE',
                    'value' => $this->postType,
                    'type' => 'constant',
                ];
                $editorType = true;
            }
        }

        if (!$editorType) {
            $variables[] = [
                'key' => $key,
                'value' => $this->postType,
                'type' => 'constant',
            ];
        }

        return $variables;
    }

    /**
     * @return array
     */
    protected function setGlobalLayoutQuery()
    {
        // Convert query to layout post
        $post = get_post($this->layoutSourceId);

        $tempPostQuery = new \WP_Query(
            [
                'p' => $this->layoutSourceId,
                'post_status' => get_post_status($this->layoutSourceId),
                'post_type' => $this->postType,
            ]
        );
        // @codingStandardsIgnoreStart
        $wp_query = $tempPostQuery; // set local current query
        $wp_the_query = $tempPostQuery;

        $result = [$post, $wp_query, $wp_the_query]; // set global query also!
        // @codingStandardsIgnoreEnd

        return $result;
    }

    /**
     * @return mixed|string|void
     */
    protected function getLayoutTemplateFile()
    {
        $template = get_page_template();
        if (!$template) {
            $template = get_index_template();
        }
        $template = apply_filters('template_include', $template);

        return $template;
    }

    protected function addCustomLayoutVariables($variables, $payload)
    {
        if (isset($payload['sourceId'])) {
            $variables[] = [
                'key' => 'vcvCreatevcv_layouts',
                'value' => admin_url('post-new.php?post_type=' . $this->postType),
                'type' => 'variable',
            ];
            $dataHelper = vchelper('Data');
            $pageTemplatesLayoutsIndex = $dataHelper->arraySearch(
                $variables,
                'key',
                'VCV_PAGE_TEMPLATES_LAYOUTS',
                true
            );

            if ($pageTemplatesLayoutsIndex !== false) {
                $args = [
                    'posts_per_page' => -1,
                    'post_type' => $this->postType,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'meta_key' => VCV_PREFIX . 'layoutType',
                    'meta_query' => [
                        [
                            'key' => VCV_PREFIX . 'layoutType',
                            'value' => 'postTemplate',
                            'compare' => '=',
                        ],
                    ],
                ];

                $customLayouts = $this->getCustomLayouts($args);

                $variables[ $pageTemplatesLayoutsIndex ]['value'][] = [
                    'type' => 'vc-custom-layout',
                    'title' => 'Custom Layouts',
                    'values' => $customLayouts,
                ];
            }
        }

        return $variables;
    }

    /**
     * @param $sourceId
     * @param \VisualComposer\Helpers\Options $optionsHelper
     */
    protected function setLayoutId($sourceId, Options $optionsHelper)
    {
        /** @var PageLayout $pageLayoutHelper */
        $pageLayoutHelper = vchelper('PageLayout');

        $savedWpPageTemplate = get_post($sourceId)->page_template;
        $currentLayout = $pageLayoutHelper->getCurrentPageLayout(
            [
                'type' => 'theme',
                'value' => $savedWpPageTemplate,
            ]
        );

        $layoutType = $currentLayout['type'];
        $pageTemplate = $currentLayout['value'];

        if (empty($layoutType) || ($layoutType === 'vc' && $pageTemplate === 'default')) {
            $this->setDefaultLayoutId($sourceId, $optionsHelper);
        } elseif (in_array($layoutType, ['theme', 'vc-custom-layout'], true) && $pageTemplate === 'default') {
            $this->setDefaultLayoutId($sourceId, $optionsHelper);
        } elseif ($layoutType === 'vc-custom-layout') {
            if (strpos($pageTemplate, 'theme:') !== false) {
                return; // we use some of theme templates
            }
            if (is_numeric($pageTemplate) && get_post_status($pageTemplate) === 'publish') {
                $this->layoutSourceId = (int)$pageTemplate;
            } else {
                $isRevisionId = wp_is_post_revision($sourceId);
                $originalSourceId = $isRevisionId ? $isRevisionId : $sourceId;
                $currentPostType = get_post_type($originalSourceId);
                $this->layoutSourceId = (int)$optionsHelper->get(
                    'custom-page-templates-' . $currentPostType . '-layout',
                    ''
                );
            }
        }
    }

    /**
     * @param $sourceId
     * @param \VisualComposer\Helpers\Options $optionsHelper
     */
    protected function setDefaultLayoutId($sourceId, Options $optionsHelper)
    {
        $postType = get_post_type($sourceId);
        if ($postType == 'revision') {
            $revisionHelper = vchelper('Revision');
            $postType = $revisionHelper->getRevisionParentPostType($sourceId);
        }
        $customPostTypeLayout = (int)$optionsHelper->get(
            'custom-page-templates-' . $postType . '-layout',
            ''
        );
        if (!empty($customPostTypeLayout)
            && get_post($customPostTypeLayout)
            && get_post_status($customPostTypeLayout) === 'publish'
        ) {
            $this->layoutSourceId = (int)$customPostTypeLayout;
        }
    }

    protected function removeLayoutTemplates($response, $payload)
    {
        if (isset($response['templates'], $payload['sourceId']) && is_numeric($payload['sourceId'])) {
            if (get_post_type($payload['sourceId']) !== $this->postType) {
                unset(
                    $response['templates']['customLayout'],
                    $response['templates']['hubLayout'],
                    $response['templates']['customLayoutpostTemplate'],
                    $response['templates']['customLayoutarchiveTemplate'],
                    $response['templates']['customBlockLayoutpostTemplate'],
                    $response['templates']['customBlockLayoutarchiveTemplate']
                );
            }
        }

        return $response;
    }

    protected function removeCustomLayoutTemplates($response)
    {
        $sourceId = vchelper('Request')->input('vcv-source-id');
        if (get_post_type($sourceId) !== $this->postType) {
            $response = array_filter($response, function ($item) {
                return !(isset($item['group']['label']) && (stripos($item['group']['label'], 'layout') !== false || stripos($item['group']['label'], 'archive') !== false));
            });
        }

        return $response;
    }

    protected function addLayoutType($response, $payload)
    {
        if (isset($payload['sourceId']) && is_numeric($payload['sourceId'])) {
            $templateType = get_post_meta(
                $payload['sourceId'],
                '_' . VCV_PREFIX . 'type',
                true
            );
            if ((
                    'vcv_templates' === get_post_type($payload['sourceId'])
                    && strpos($templateType, 'Layout') !== false
                )
                || (
                    get_post_type($payload['sourceId']) === $this->postType
                )
            ) {
                // get layoutType from post meta
                $layoutType = get_post_meta($payload['sourceId'], VCV_PREFIX . 'layoutType', true);
                if (empty($layoutType)) {
                    $layoutType = 'postTemplate';
                }
                $response['layoutType'] = $layoutType;
            }
        }

        return $response;
    }

    protected function saveLayoutType($response, $payload, Request $requestHelper)
    {
        if (isset($payload['sourceId']) && is_numeric($payload['sourceId'])) {
            if (in_array(get_post_type($payload['sourceId']), [$this->postType, 'vcv_templates'], true)) {
                $layoutType = $requestHelper->input('vcv-layout-type');
                update_post_meta($payload['sourceId'], VCV_PREFIX . 'layoutType', $layoutType);
            }
        }

        return $response;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function getCustomLayouts(array $args)
    {
        $customLayouts = [];
        $query = new \WP_Query($args);
        $posts = is_array($query->posts) && !empty($query->posts) ? $query->posts : [];
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $customLayout = [];
                $customLayout['value'] = $post->ID;
                // @codingStandardsIgnoreLine
                $customLayout['label'] = 'Layout Builder: ' . $post->post_title;

                // force set header/footer/sidebar to false as since v41 we decided to disable such option
                $customLayout['header'] = false;
                $customLayout['sidebar'] = false;
                $customLayout['footer'] = false;
                $customLayouts[] = $customLayout;
            }
        }

        return $customLayouts;
    }

    /**
     * Remove edit action from dropdown
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
    protected function updatePostEditBarLinks(
        $actions,
        $post
    ) {
        // @codingStandardsIgnoreLine
        if ($post->post_type === $this->postType) {
            $templateType = get_post_meta($post->ID, '_vcv-type', true);
            unset($actions['inline hide-if-no-js'], $actions['edit'], $actions['view'], $actions['preview']);
            if (!in_array($templateType, ['', 'custom'])) {
                unset($actions['trash']);
            }
            $actions = array_reverse($actions);
        }

        return $actions;
    }

    /**
     * Return layouts on hfs dropdown open
     *
     * @return array
     */
    protected function getPostList()
    {
        $args = [
            'posts_per_page' => -1,
            'post_type' => $this->postType,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => VCV_PREFIX . 'layoutType',
            'meta_query' => [
                [
                    'key' => VCV_PREFIX . 'layoutType',
                    'value' => 'postTemplate',
                    'compare' => '=',
                ],
            ],
        ];

        $customLayouts = $this->getCustomLayouts($args);

        return ['status' => true, 'data' => $customLayouts];
    }
}
