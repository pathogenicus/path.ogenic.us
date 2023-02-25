<?php

namespace themeEditor\themeEditor;

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
use VisualComposer\Modules\Settings\Traits\Fields;
use VisualComposer\Helpers\PostType;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;

class SettingsController extends Container implements Module
{
    use Page;
    use SubMenu;
    use Fields;
    use WpFiltersActions;
    use EventsFilters;

    protected $slug = 'vcv-headers-footers';

    /*
     * @var string
     */
    protected $templatePath = 'settings/pages/index';

    /**
     * General constructor.
     */
    public function __construct()
    {
        // Set dashboard modifications for addon (needed for BC when addons not updated)
        \VcvEnv::set('VCV_HUB_ADDON_DASHBOARD_THEMEEDITOR', true);
        $this->optionSlug = $this->optionGroup = $this->slug;

        $this->wpAddAction('admin_init', 'buildPage');
        $this->wpAddAction('admin_menu', 'addPage', 20);
        $this->addEvent('vcv:settings:save', 'addPage');
        $this->wpAddFilter('submenu_file', 'subMenuHighlight');
        $this->addFilter('vcv:themeEditor:settingsController:addPages', 'addPostTaxonomies');

        $this->addEvent('vcv:system:factory:reset', 'unsetOptions');
    }

    /**
     * @throws \Exception
     */
    protected function addPage()
    {
        $page = [
            'slug' => $this->getSlug(),
            'title' => __('Theme Builder', 'visualcomposer'),
            'subTitle' => __('Theme Builder Settings', 'visualcomposer'),
            'layout' => 'dashboard-tab-content-standalone',
            'iconClass' => 'vcv-ui-icon-dashboard-theme-builder',
            'capability' => 'manage_options',
            'capabilityPart' => 'dashboard_addon_theme_builder',
            'isDashboardPage' => true,
            'hideInWpMenu' => false,
        ];
        $this->addSubmenuPage($page, 'vcv-headers-footers');
    }

    /**
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
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @throws \ReflectionException
     */
    protected function buildPage(Options $optionsHelper, PostType $postTypeHelper, Frontend $frontendHelper)
    {
        /**
         * Main accordion
         */
        $this->addSection(
            [
                'page' => $this->slug,
                'slug' => 'headers-footers-accordion',
                'type' => 'accordion',
                'title' => __('Header and Footer Settings', 'visualcomposer'),
                'vcv-args' => [
                    'hideTitle' => true
                ],
                'callback' => function () {
                },
            ]
        );

        $sectionCallback = function () {
            echo sprintf(
                '<p class="description">%s</p>',
                sprintf(
                    __(
                        'Replace the theme default header and footer with your header and footer templates created with Visual Composer. %s %sNote:%s Set custom header and footer for specific pages in the Page settings in frontend editor.',
                        'visualcomposer'
                    ),
                    '<br>',
                    '<strong>',
                    '</strong>'
                )
            );
        };
        /**
         * Main section
         */
        $this->addSection(
            [
                'page' => $this->slug,
                'slug' => 'headers-footers-override',
                'callback' => $sectionCallback,
                'vcv-args' => [
                    'parent' => 'headers-footers-accordion',
                ],
            ]
        );

        $enabledOptions = [
            ['id' => 'allSite', 'title' => __('Set your header and footer (sitewide)', 'visualcomposer')],
            [
                'id' => 'customPostType',
                'title' => __('Set your header and footer (per post type)', 'visualcomposer'),
            ],
        ];

        $headerFooterSettings = $optionsHelper->get('headerFooterSettings');
        $fieldCallback = function () use ($enabledOptions, $headerFooterSettings) {
            $args = [
                'enabledOptions' => $enabledOptions,
                'name' => 'vcv-headerFooterSettings',
                'value' => $headerFooterSettings,
                'emptyTitle' => __('Theme default', 'visualcomposer'),
            ];
            echo vcview(
                'settings/fields/dropdown',
                $args
            );
        };

        /**
         * All site headers and footers section
         */
        $this->addField(
            [
                'page' => $this->slug,
                'name' => 'headerFooterSettings',
                'id' => 'vcv-headers-footers-override',
                'slug' => 'headers-footers-override',
                'fieldCallback' => $fieldCallback,
                'args' => [
                    'class' => 'vcv-no-title',
                ],
            ]
        );

        $this->addSection(
            [
                'page' => $this->slug,
                'slug' => 'headers-footers-all-site',
                'vcv-args' => [
                    'class' => 'vcv-hidden',
                    'parent' => 'headers-footers-override',
                ],
            ]
        );

        $availableHeaders = $this->getPosts(['vcv_headers'], $frontendHelper);
        $selectedAllHeader = (int)$optionsHelper->get('headerFooterSettingsAllHeader');

        $fieldCallbackAllSiteHeader = function () use ($availableHeaders, $selectedAllHeader) {
            $urlHelper = vchelper('Url');
            $args = [
                'enabledOptions' => $availableHeaders,
                'name' => 'vcv-headerFooterSettingsAllHeader',
                'value' => $selectedAllHeader,
                'emptyTitle' => __('None', 'visualcomposer'),
                'dataTitle' => __('header template', 'visualcomposer'),
                'class' => 'vcv-edit-link-selector',
                'createUrl' => vcfilter(
                    'vcv:frontend:url',
                    $urlHelper->query(admin_url('post-new.php?post_type=vcv_headers'), ['vcv-action' => 'frontend', 'vcv-editor-type' => 'vcv_headers']),
                    ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                ),
            ];
            echo vcview(
                'settings/fields/dropdown',
                $args
            );
        };
        $this->addField(
            [
                'page' => $this->slug,
                'title' => __('Header', 'visualcomposer'),
                'name' => 'headerFooterSettingsAllHeader',
                'id' => 'vcv-headers-footers-all-header',
                'slug' => 'headers-footers-all-site',
                'fieldCallback' => $fieldCallbackAllSiteHeader,
            ]
        );

        $availableFooters = $this->getPosts(['vcv_footers'], $frontendHelper);
        $selectedAllFooter = (int)$optionsHelper->get('headerFooterSettingsAllFooter');

        $fieldCallbackAllSiteFooter = function () use ($availableFooters, $selectedAllFooter) {
            $urlHelper = vchelper('Url');
            $args = [
                'enabledOptions' => $availableFooters,
                'name' => 'vcv-headerFooterSettingsAllFooter',
                'value' => $selectedAllFooter,
                'emptyTitle' => __('None', 'visualcomposer'),
                'dataTitle' => __('footer template', 'visualcomposer'),
                'class' => 'vcv-edit-link-selector',
                'createUrl' => vcfilter(
                    'vcv:frontend:url',
                    $urlHelper->query(admin_url('post-new.php?post_type=vcv_footers'), ['vcv-action' => 'frontend', 'vcv-editor-type' => 'vcv_footers']),
                    ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                ),
            ];
            echo vcview(
                'settings/fields/dropdown',
                $args
            );
        };
        $this->addField(
            [
                'page' => $this->slug,
                'title' => __('Footer', 'visualcomposer'),
                'name' => 'headerFooterSettingsAllFooter',
                'id' => 'vcv-headers-footers-all-footer',
                'slug' => 'headers-footers-all-site',
                'fieldCallback' => $fieldCallbackAllSiteFooter,
            ]
        );

        /**
         * Separate post types and page types headers and footers section
         */
        $this->addSection(
            [
                'page' => $this->slug,
                'slug' => 'headers-footers-separate-post-types',
                'vcv-args' => [
                    'class' => 'vcv-hidden',
                    'parent' => 'headers-footers-override',
                ],
            ]
        );

        /**
         * Separate post types
         */
        $enabledPostTypes = $postTypeHelper->getPostTypes(['attachment']);
        foreach ($enabledPostTypes as $postType) {
            $separateOptionPostType = (array)$optionsHelper->get(
                'headerFooterSettingsSeparatePostType-' . $postType['value']
            );
            $fieldCallbackSeparateOption = function () use ($separateOptionPostType, $postType) {
                $args = [
                    'isEnabled' => in_array('headers-footers-separate-' . $postType['value'], $separateOptionPostType),
                    'name' => 'vcv-headerFooterSettingsSeparatePostType-' . $postType['value'],
                    'value' => 'headers-footers-separate-' . $postType['value'],
                ];
                echo vcview(
                    'settings/fields/toggle',
                    $args
                );
            };
            $this->addSection(
                [
                    'page' => $this->slug,
                    'title' => $postType['label'],
                    'slug' => 'headers-footers-separate-post-type-' . $postType['value'],
                    'headerFooterCallback' => $fieldCallbackSeparateOption,
                    'vcv-args' => [
                        'parent' => 'headers-footers-separate-post-types',
                    ],
                ]
            );

            $this->addField(
                [
                    'page' => $this->slug,
                    'name' => 'headerFooterSettingsSeparatePostType-' . $postType['value'],
                    'id' => 'vcv-headers-footers-separate-' . $postType['value'],
                    'slug' => 'headers-footers-separate-post-type-' . $postType['value'],
                    'args' => [
                        'class' => 'vcv-no-title vcv-hidden',
                    ],
                ]
            );

            $selectedSeparateHeader = (int)$optionsHelper->get(
                'headerFooterSettingsSeparatePostTypeHeader-' . $postType['value']
            );
            $fieldCallback = function () use ($availableHeaders, $selectedSeparateHeader, $postType) {
                $urlHelper = vchelper('Url');
                $args = [
                    'enabledOptions' => $availableHeaders,
                    'name' => 'vcv-headerFooterSettingsSeparatePostTypeHeader-' . $postType['value'],
                    'value' => $selectedSeparateHeader,
                    'emptyTitle' => __('None', 'visualcomposer'),
                    'dataTitle' => __('header template', 'visualcomposer'),
                    'class' => 'vcv-edit-link-selector',
                    'createUrl' => vcfilter(
                        'vcv:frontend:url',
                        $urlHelper->query(
                            admin_url('post-new.php?post_type=vcv_headers'),
                            ['vcv-action' => 'frontend']
                        ),
                        ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                    ),
                ];
                echo vcview(
                    'settings/fields/dropdown',
                    $args
                );
            };

            $this->addField(
                [
                    'page' => $this->slug,
                    'title' => __('Header', 'visualcomposer'),
                    'name' => 'headerFooterSettingsSeparatePostTypeHeader-' . $postType['value'],
                    'id' => 'vcv-header-footer-settings-separate-header-' . $postType['value'],
                    'slug' => 'headers-footers-separate-post-type-' . $postType['value'],
                    'fieldCallback' => $fieldCallback,
                ]
            );

            $selectedSeparateFooter = (int)$optionsHelper->get(
                'headerFooterSettingsSeparatePostTypeFooter-' . $postType['value']
            );
            $fieldCallback = function () use ($availableFooters, $selectedSeparateFooter, $postType) {
                $urlHelper = vchelper('Url');
                $args = [
                    'enabledOptions' => $availableFooters,
                    'name' => 'vcv-headerFooterSettingsSeparatePostTypeFooter-' . $postType['value'],
                    'value' => $selectedSeparateFooter,
                    'emptyTitle' => __('None', 'visualcomposer'),
                    'dataTitle' => __('footer template', 'visualcomposer'),
                    'class' => 'vcv-edit-link-selector',
                    'createUrl' => vcfilter(
                        'vcv:frontend:url',
                        $urlHelper->query(
                            admin_url('post-new.php?post_type=vcv_footers'),
                            ['vcv-action' => 'frontend']
                        ),
                        ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                    ),
                ];
                echo vcview(
                    'settings/fields/dropdown',
                    $args
                );
            };

            $this->addField(
                [
                    'page' => $this->slug,
                    'title' => __('Footer', 'visualcomposer'),
                    'name' => 'headerFooterSettingsSeparatePostTypeFooter-' . $postType['value'],
                    'id' => 'vcv-header-footer-settings-separate-footer-' . $postType['value'],
                    'slug' => 'headers-footers-separate-post-type-' . $postType['value'],
                    'fieldCallback' => $fieldCallback,
                ]
            );
        }

        /**
         * Separate page types
         */

        $specificPages = [
            [
                'title' => __('Front Page', 'visualcomposer'),
                'name' => 'frontPage',
            ],
            [
                'title' => __('Authors', 'visualcomposer'),
                'name' => 'author',
            ],
            [
                'title' => __('Search', 'visualcomposer'),
                'name' => 'search',
            ],
            [
                'title' => __('404 Page', 'visualcomposer'),
                'name' => 'notFound',
            ],
            [
                'title' => __('Post Listing Page', 'visualcomposer'),
                'name' => 'postPage',
            ],
            [
                'title' => __('Custom Post Archive Page', 'visualcomposer'),
                'name' => 'archive',
            ],
            [
                'title' => __('Categories', 'visualcomposer'),
                'name' => 'category',
            ],
            [
                'title' => __('Tags', 'visualcomposer'),
                'name' => 'post_tag',
            ],
        ];

        $specificPages[] = [];
        $specificPages = vcfilter('vcv:themeEditor:settingsController:addPages', $specificPages);

        foreach ($specificPages as $pageType) {
            if (!isset($pageType['name'])) {
                continue;
            }
            $pageType['slug'] = 'headers-footers-page-type-' . $pageType['name'];
            $pageType['optionKey'] = 'headerFooterSettingsPageType-' . $pageType['name'];
            $pageType['optionKeyHeader'] = 'headerFooterSettingsPageTypeHeader-' . $pageType['name'];
            $pageType['optionKeyFooter'] = 'headerFooterSettingsPageTypeFooter-' . $pageType['name'];

            $separateOptionPageType = (array)$optionsHelper->get($pageType['optionKey']);
            $fieldCallbackOption = function () use ($separateOptionPageType, $pageType) {
                $args = [
                    'isEnabled' => in_array($pageType['slug'], $separateOptionPageType, true),
                    'name' => 'vcv-' . $pageType['optionKey'],
                    'value' => $pageType['slug'],
                    'class' => 'vcv-edit-link-selector',
                ];
                echo vcview(
                    'settings/fields/toggle',
                    $args
                );
            };

            $this->addSection(
                [
                    'page' => $this->slug,
                    'title' => $pageType['title'],
                    'slug' => $pageType['slug'],
                    'headerFooterCallback' => $fieldCallbackOption,
                    'vcv-args' => [
                        'parent' => 'headers-footers-separate-post-types',
                    ],
                ]
            );

            $this->addField(
                [
                    'page' => $this->slug,
                    'name' => $pageType['optionKey'],
                    'id' => 'vcv-' . $pageType['slug'],
                    'slug' => $pageType['slug'],
                    'args' => [
                        'class' => 'vcv-no-title vcv-hidden',
                    ],
                ]
            );

            $selectedHeader = (int)$optionsHelper->get($pageType['optionKeyHeader']);
            $fieldCallback = function () use ($availableHeaders, $selectedHeader, $pageType) {
                $urlHelper = vchelper('Url');
                $args = [
                    'enabledOptions' => $availableHeaders,
                    'name' => 'vcv-' . $pageType['optionKeyHeader'],
                    'value' => $selectedHeader,
                    'emptyTitle' => __('None', 'visualcomposer'),
                    'dataTitle' => __('header template', 'visualcomposer'),
                    'class' => 'vcv-edit-link-selector',
                    'createUrl' => vcfilter(
                        'vcv:frontend:url',
                        $urlHelper->query(
                            admin_url('post-new.php?post_type=vcv_headers'),
                            ['vcv-action' => 'frontend']
                        ),
                        ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                    ),
                ];
                echo vcview(
                    'settings/fields/dropdown',
                    $args
                );
            };

            $this->addField(
                [
                    'page' => $this->slug,
                    'title' => __('Header', 'visualcomposer'),
                    'name' => $pageType['optionKeyHeader'],
                    'id' => 'vcv-' . $pageType['slug'] . '-header',
                    'slug' => $pageType['slug'],
                    'fieldCallback' => $fieldCallback,
                ]
            );

            $selectedFooter = (int)$optionsHelper->get($pageType['optionKeyFooter']);
            $fieldCallback = function () use ($availableFooters, $selectedFooter, $pageType) {
                $urlHelper = vchelper('Url');
                $args = [
                    'enabledOptions' => $availableFooters,
                    'name' => 'vcv-' . $pageType['optionKeyFooter'],
                    'value' => $selectedFooter,
                    'emptyTitle' => __('None', 'visualcomposer'),
                    'dataTitle' => __('footer template', 'visualcomposer'),
                    'class' => 'vcv-edit-link-selector',
                    'createUrl' => vcfilter(
                        'vcv:frontend:url',
                        $urlHelper->query(
                            admin_url('post-new.php?post_type=vcv_footers'),
                            ['vcv-action' => 'frontend']
                        ),
                        ['query' => ['vcv-action' => 'frontend'], 'sourceId' => null]
                    ),
                ];
                echo vcview(
                    'settings/fields/dropdown',
                    $args
                );
            };

            $this->addField(
                [
                    'page' => $this->slug,
                    'title' => __('Footer', 'visualcomposer'),
                    'name' => $pageType['optionKeyFooter'],
                    'id' => 'vcv-' . $pageType['slug'] . '-footer',
                    'slug' => $pageType['slug'],
                    'fieldCallback' => $fieldCallback,
                ]
            );
        }
    }

    /**
     * Add all custom posts taxonomies to header/footer settings list.
     *
     * @param array $specificPages
     *
     * @return array
     */
    protected function addPostTaxonomies($specificPages)
    {
        $taxList = get_object_taxonomies('post');

        if (empty($taxList)) {
            return $specificPages;
        }

        foreach ($taxList as $taxTag) {
            $taxonomyDetails = get_taxonomy($taxTag);

            $predefinedList = [
                'post_format',
                'tags',
                'category',
            ];

            if (empty($taxonomyDetails->name) || in_array($taxonomyDetails->name, $predefinedList)) {
                continue;
            }

            $specificPages[] = [
                'title' => $taxonomyDetails->label,
                'name' => $taxTag,
            ];
        }

        return $specificPages;
    }

    /**
     * @param $postType
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return array
     */
    protected function getPosts($postType, $frontendHelper)
    {
        $args = [
            'numberposts' => -1,
            'post_type' => $postType,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        $availablePosts = [];
        $posts = get_posts($args);
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $postData = [];
                $postData['id'] = $post->ID;
                // @codingStandardsIgnoreLine
                $postData['title'] = $post->post_title;
                $postData['url'] = $frontendHelper->getFrontendUrl($post->ID);
                $availablePosts[] = $postData;
            }
        }

        return $availablePosts;
    }

    /**
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     */
    protected function unsetOptions(Options $optionsHelper, PostType $postTypeHelper)
    {
        $optionsHelper->delete('headerFooterSettings');
        $optionsHelper->delete('headerFooterSettingsAllHeader');
        $optionsHelper->delete('headerFooterSettingsAllFooter');
        $optionsHelper->delete('headerFooterSettingsSeparate');
        $enabledPostTypes = $postTypeHelper->getPostTypes(['attachment']);
        if (!empty($enabledPostTypes)) {
            foreach ($enabledPostTypes as $postType) {
                $optionsHelper->delete('headerFooterSettingsSeparateHeader-' . $postType['value']);
                $optionsHelper->delete('headerFooterSettingsSeparateFooter-' . $postType['value']);
            }
        }
    }
}
