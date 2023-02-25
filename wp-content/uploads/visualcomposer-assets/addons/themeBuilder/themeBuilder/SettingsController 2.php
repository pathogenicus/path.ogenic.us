<?php

namespace themeBuilder\themeBuilder;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Url;
use VisualComposer\Modules\Settings\Traits\Fields;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

/**
 * Class SettingsController
 * @package themeBuilder\themeBuilder
 */
class SettingsController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;
    use Fields;
    use Page;
    use SubMenu;

    /**
     * @var string
     */
    protected $slug = 'vcv-headers-footers';

    /**
     * Settings template to render
     * @var string
     */
    protected $templatePath = 'settings/pages/index';

    /**
     * SettingsController constructor.
     */
    public function __construct()
    {
        $this->optionGroup = 'vcv-headers-footers';
        $this->optionSlug = 'vcv-headers-footers';

        $this->wpAddAction(
            'admin_init',
            'buildPage',
            8
        );

        $this->wpAddFilter('submenu_file', 'subMenuHighlight');

        $this->addEvent(
            'vcv:settings:page:vcv-headers-footers:beforeRender',
            'beforeRender'
        );
    }

    protected function beforeRender()
    {
        echo '<style id="vcv-addons-custom-page-templates-style" type="text/css">';
        $fileHelper = vchelper('File');
        echo $fileHelper->getContents(__DIR__ . '/../public/dist/element.bundle.css');
        echo '</style>';
    }

    /**
     * Setup the menu parent vcv-settings
     *
     * @param $submenuFile
     *
     * @return string
     */
    protected function subMenuHighlight($submenuFile)
    {
        $screen = get_current_screen();
        if (strpos($screen->id, $this->slug)) {
            $submenuFile = 'vcv-settings';
        }

        return $submenuFile;
    }

    /**
     * Add section and fields in settings page
     */
    protected function buildPage()
    {
        /**
         * Main accordion
         */
        $this->addSection(
            [
                'page' => $this->slug,
                'slug' => 'layouts-accordion',
                'type' => 'accordion',
                'title' => __('Layout Settings', 'visualcomposer'),
                'vcv-args' => [
                    'class' => 'vcv-layout-settings-accordion',
                    'hideTitle' => true
                ],
                'callback' => function () {
                },
            ]
        );

        $sectionCallback = function () {
            echo sprintf(
                '<p class="description">%s</p>',
                esc_html__(
                    'Replace the theme default theme templates like 404 page with your templates created with Visual Composer.',
                    'visualcomposer'
                )
            );
        };

        $this->addSection(
            [
                'page' => $this->optionGroup,
                'callback' => $sectionCallback,
                'vcv-args' => [
                    'parent' => 'layouts-accordion',
                ],
            ]
        );

        // 404 page dropdown
        $dropdownFieldCallback = function () {
            echo $this->call('render404Dropdown');
        };

        // 404 page fields
        $this->addField(
            [
                'page' => $this->optionGroup,
                'title' => __('404 page', 'visualcomposer'),
                'name' => 'custom-page-templates-404-page',
                'id' => 'vcv-custom-page-templates-404-page',
                'fieldCallback' => $dropdownFieldCallback,
            ]
        );

        $archiveTypes = [
            'search' => ['title' => 'Search Page', 'slug' => 'search'],
            'author' => ['title' => 'Author Page', 'slug' => 'author'],
            'post' => [
                'title' => 'Posts Archive',
                'slug' => 'post',
                'showSubTaxonomies' => true,
                'postType' => 'post',
                'toggleTitle' => esc_html__('Replace the theme default templates for categories and tags', 'visualcomposer'),
            ],
        ];

        if (post_type_exists('product')) {
            $archiveTypes['product'] = [
                'title' => 'Shop Archive',
                'slug' => 'product',
                'showSubTaxonomies' => true,
                'postType' => 'product',
                'toggleTitle' => esc_html__('Replace the theme default templates for product categories, product tags', 'visualcomposer'),
            ];
        }

        foreach ($archiveTypes as $archiveType) {
            // Archive templates and post types dropdown
            $archiveTypeTitle = strtolower($archiveType['title']);
            $archiveFieldCallback = function () use ($archiveType, $archiveTypeTitle) {
                echo $this->call(
                    'renderLayoutDropdown',
                    [
                        'type' => $archiveType['slug'],
                        'editTextTitle' => $archiveTypeTitle . ' template',
                        'archive' => true,
                    ]
                );
            };

            $fieldData = [
                'page' => $this->optionGroup,
                'title' => sprintf(__('%s', 'visualcomposer'), ucfirst($archiveTypeTitle)),
                'name' => 'custom-page-templates-' . $archiveType['slug'] . '-template',
                'id' => 'vcv-custom-page-templates-' . $archiveType['slug'] . '',
                'fieldCallback' => $archiveFieldCallback,
                'args' => [
                    'class' => 'vcv-custom-page-template-dropdown',
                ],
            ];

            if (isset($archiveType['showSubTaxonomies']) && $archiveType['showSubTaxonomies'] === true
                && isset($archiveType['postType'])
                && !empty($archiveType['postType'])
            ) {
                $fieldData['slug'] = 'vcv-custom-page-templates-section-' . $archiveType['postType'];

                $this->addSection(
                    [
                        'page' => $this->optionGroup,
                        'slug' => 'vcv-custom-page-templates-section-' . $archiveType['postType'],
                        'vcv-args' => [
                            'parent' => 'layouts-accordion',
                        ],
                    ]
                );

                $this->addField($fieldData);

                // Custom template toggle
                $toggleFieldCallback = function () use ($archiveType) {
                    echo $this->call('renderToggle', ['postType' => $archiveType]);
                };

                $this->addField(
                    [
                        'page' => $this->optionGroup,
                        'name' => 'custom-page-templates-enabled-' . $archiveType['postType'],
                        'id' => 'vcv-custom-page-templates-enabled-' . $archiveType['postType'],
                        'slug' => 'vcv-custom-page-templates-section-' . $archiveType['postType'],
                        'fieldCallback' => $toggleFieldCallback,
                        'args' => [
                            'class' => 'vcv-custom-page-template-switcher vcv-no-title',
                        ],
                    ]
                );

                $this->addSection(
                    [
                        'page' => $this->optionGroup,
                        'slug' => 'vcv-custom-page-templates-sub-section-' . $archiveType['postType'],
                        'vcv-args' => [
                            'parent' => 'vcv-custom-page-templates-section-' . $archiveType['postType'],
                        ],
                    ]
                );

                // Get custom taxonomies for post types
                $taxonomies = get_object_taxonomies($archiveType['postType'], 'object');
                foreach ($taxonomies as $taxonomy) {
                    // @codingStandardsIgnoreLine
                    if ($taxonomy->show_ui === true) {
                        $postTypePrefix = '';

                        // Set post type prefix
                        // @codingStandardsIgnoreLine
                        foreach ($taxonomy->object_type as $postType) {
                            $postTypePrefix .= vchelper('PostType')->getPostLabel($postType) . ', ';
                        }
                        $postTypePrefix = rtrim($postTypePrefix, ', ');

                        $archiveFieldCallback = function () use ($taxonomy, $postTypePrefix) {
                            echo $this->call(
                                'renderLayoutDropdown',
                                [
                                    'type' => $taxonomy->name,
                                    'editTextTitle' => strtolower($postTypePrefix . ' ' . $taxonomy->labels->singular_name) . ' template',
                                    'archive' => true,
                                ]
                            );
                        };

                        $this->addField(
                            $fieldData = [
                                'page' => $this->optionGroup,
                                'title' => sprintf(
                                    __('%s archive', 'visualcomposer'),
                                    $postTypePrefix . ' ' . lcfirst($taxonomy->labels->singular_name)
                                ),
                                'name' => 'custom-page-templates-' . $taxonomy->name . '-template',
                                'id' => 'vcv-custom-page-templates-' . $taxonomy->name . '',
                                'slug' => 'vcv-custom-page-templates-sub-section-' . $archiveType['postType'],
                                'fieldCallback' => $archiveFieldCallback,
                                'args' => [
                                    'class' => 'vcv-custom-page-template-taxonomy',
                                ],
                            ]
                        );
                    }
                }
            } else {
                $this->addField($fieldData);
            }
        }

        /**
         * Separate post types
         */
        if (vcvenv('VCV_FT_THEME_BUILDER_LAYOUTS')) {
            $enabledPostTypes = vchelper('PostType')->getPostTypes(['attachment']);
            foreach ($enabledPostTypes as $postType) {
                $this->addSection(
                    [
                        'page' => $this->slug,
                        'title' => '',
                        'slug' => 'custom-layout-post-type-' . $postType['value'],
                        'vcv-args' => [
                            'parent' => 'layouts-accordion',
                        ],
                    ]
                );

                $dropdownFieldCallback = function () use ($postType) {
                    echo $this->call(
                        'renderLayoutDropdown',
                        [
                            'type' => $postType['value'],
                            'editTextTitle' => __('Custom Layout template', 'visualcomposer'),
                            'archive' => false,
                        ]
                    );
                };

                $this->addField(
                    [
                        'page' => $this->optionGroup,
                        'title' => sprintf(__('%s template', 'visualcomposer'), $postType['label']),
                        'name' => 'custom-page-templates-' . $postType['value'] . '-layout',
                        'id' => 'vcv-custom-page-layout-' . $postType['value'] . '',
                        'fieldCallback' => $dropdownFieldCallback,
                        'slug' => 'custom-layout-post-type-' . $postType['value'],
                        'args' => [
                            'class' => 'vcv-custom-page-template-dropdown',
                        ],
                    ]
                );
            }
        }

        $layoutWidthCallback = function () {
            echo $this->call('renderLayoutInput');
        };

        $fieldData = [
            'page' => $this->optionGroup,
            'title' => __('Layout width', 'visualcomposer'),
            'name' => 'custom-page-templates-section-layout-width',
            'id' => $this->optionGroup . '-section-layout-width',
            'slug' => $this->optionGroup . '-section-layout-width',
            'fieldCallback' => $layoutWidthCallback,
        ];

        $this->addSection(
            [
                'page' => $this->optionGroup,
                'slug' => $this->optionGroup . '-section-layout-width',
                'title' => __('Layout Width', 'visualcomposer'),
                'callback' => function () {
                    echo sprintf(
                        '<p class="description">%s</p>',
                        esc_html__(
                            'Specify content area width in pixels for Visual Composer layouts.',
                            'visualcomposer'
                        )
                    );
                },
                'vcv-args' => [
                    'parent' => 'layouts-accordion',
                ],
            ]
        );

        $this->addField($fieldData);
    }

    /**
     * Render toggle for custom taxonomies
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param $archive
     *
     * @return mixed|string
     */
    protected function renderToggle(Options $optionsHelper, $archive)
    {
        return vcview(
            'settings/fields/toggle',
            [
                'name' => 'vcv-custom-page-templates-enabled-' . $archive['postType'],
                'value' => 'custom-template-enabled-' . $archive['postType'],
                'isEnabled' => $optionsHelper->get('custom-page-templates-enabled-' . $archive['postType'], ''),
                'title' => $archive['toggleTitle'],
            ]
        );
    }

    /**
     * Render dropdown for layout templates
     *
     * @param $type
     * @param $editTextTitle
     * @param $archive
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Url $urlHelper
     *
     * @return mixed|string
     */
    protected function renderLayoutDropdown(
        $type,
        $editTextTitle,
        $archive,
        Options $optionsHelper,
        Frontend $frontendHelper,
        Url $urlHelper
    ) {
        if (!$archive) {
            $metaValue = 'postTemplate';
            $optionName = 'custom-page-templates-' . $type . '-layout';
        } else {
            $metaValue = 'archiveTemplate';
            $optionName = 'custom-page-templates-' . $type . '-template';
        }

        $selectedLayout = (int)$optionsHelper->get($optionName);

        // Get available layouts templates
        $args = [
            'posts_per_page' => -1,
            'post_type' => 'vcv_layouts',
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => VCV_PREFIX . 'layoutType',
            'meta_query' => [
                [
                    'key' => VCV_PREFIX . 'layoutType',
                    'value' => $metaValue,
                    'compare' => '=',
                ],
            ],
        ];
        $query = new \WP_Query($args);
        $availableLayouts = [];
        if (is_array($query->posts) && !empty($query->posts)) {
            foreach ($query->posts as $post) {
                $availableLayouts[] = [
                    'id' => $post->ID,
                    // @codingStandardsIgnoreLine
                    'title' => $post->post_title,
                    'url' => $frontendHelper->getFrontendUrl($post->ID),
                ];
            }
        }

        return vcview(
            'settings/fields/dropdown',
            [
                'enabledOptions' => $availableLayouts,
                'name' => VCV_PREFIX . $optionName,
                'value' => $selectedLayout,
                'dataTitle' => $editTextTitle,
                'emptyTitle' => __('Theme default', 'visualcomposer'),
                'class' => 'vcv-edit-link-selector',
                'createUrl' => vcfilter(
                    'vcv:frontend:url',
                    $urlHelper->query(admin_url('post-new.php?post_type=vcv_layouts'), ['vcv-action' => 'frontend', 'vcv-editor-type' => 'vcv_layouts']),
                    ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                ),
            ]
        );
    }

    /**
     * Render dropdown for 404 page
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return mixed|string
     */
    protected function render404Dropdown(Options $optionsHelper, Frontend $frontendHelper)
    {
        $urlHelper = vchelper('Url');
        $customNotFoundPage = $optionsHelper->get('custom-page-templates-404-page', '');
        $selected = '';
        if (!empty($customNotFoundPage)) {
            $selected = (int)$customNotFoundPage;
            $selected = apply_filters('wpml_object_id', $selected, 'post', true); // if translated
            $post = get_post($selected);
            // @codingStandardsIgnoreLine
            if (!$post || $post->post_status !== 'publish') {
                $selected = '';
            }
        }

        $pages = get_pages();
        $available = [];
        foreach ($pages as $page) {
            $available[] = [
                'id' => $page->ID,
                // @codingStandardsIgnoreLine
                'title' => empty($page->post_title) ? '(no title)' : $page->post_title,
                'url' => $frontendHelper->getFrontendUrl($page->ID),
            ];
        }

        return vcview(
            'settings/fields/dropdown',
            [
                'name' => 'vcv-custom-page-templates-404-page',
                'value' => $selected,
                'enabledOptions' => $available,
                'dataTitle' => __('404 page', 'visualcomposer'),
                'emptyTitle' => __('Theme default', 'visualcomposer'),
                'class' => 'vcv-edit-link-selector',
                'createUrl' => vcfilter(
                    'vcv:frontend:url',
                    $urlHelper->query(admin_url('post-new.php?post_type=page'), ['vcv-action' => 'frontend']),
                    ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                ),
            ]
        );
    }

    /**
     * Render layout width input
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return mixed|string
     */
    protected function renderLayoutInput(Options $optionsHelper)
    {
        $customLayoutWidth = $optionsHelper->get('custom-page-templates-section-layout-width', '1140px');
        if (empty($customLayoutWidth)) {
            $customLayoutWidth = '1140px';
        }

        return vcaddonview(
            'settings/field-input',
            [
                'name' => 'vcv-custom-page-templates-section-layout-width',
                'value' => $customLayoutWidth,
                'addon' => 'themeBuilder',
            ]
        );
    }
}
