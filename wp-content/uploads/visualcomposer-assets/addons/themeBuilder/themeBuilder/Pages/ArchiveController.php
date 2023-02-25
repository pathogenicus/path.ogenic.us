<?php

namespace themeBuilder\themeBuilder\pages;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VcvEnv;
use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Modules\Settings\Traits\Page;
use VisualComposer\Modules\Settings\Traits\SubMenu;
use WP_Query;

class ArchiveController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use SubMenu;
    use Page;

    protected $postType;

    public function __construct()
    {
        $this->postType = 'vcv_layouts';

        // Set iframe-css for vcv_archives post type
        VcvEnv::set('VCV_DASHBOARD_IFRAME_VCV_ARCHIVES', true);
        // Set dashboard modifications for themebuilder (needed for BC when addons not updated)
        VcvEnv::set('VCV_HUB_ADDON_DASHBOARD_THEMEBUILDER', true);

        $this->addFilter('vcv:helpers:hub:getCategories', 'checkArchiveDataSource');
        $this->wpAddAction('template_redirect', 'renderArchivePage', 100);
        $this->addFilter('vcv:editor:settings:pageTemplatesLayouts:current:custom', 'isArchivePage');
        $this->addFilter('vcv:helpers:frontend:isVcvFrontend', 'setVcvFrontend');
    }

    /**
     * Remove Dynamic Archive source if editor type is not vcv_archives
     *
     * @param $hubCategories
     *
     * @return array
     */
    protected function checkArchiveDataSource($hubCategories)
    {
        $requestHelper = vchelper('Request');
        $frontendHelper = vchelper('Frontend');

        if ($requestHelper->input('vcv-editor-type') !== $this->postType && $frontendHelper->isFrontend()) {
            foreach ($hubCategories['_postsGridSources']['elements'] as $elementIndex => $elementName) {
                if ($elementName === 'postsGridDataSourceArchive') {
                    unset($hubCategories["_postsGridSources"]["elements"][ $elementIndex ]);
                }
            }
            $hubCategories["_postsGridSources"]["elements"] = array_values(
                $hubCategories["_postsGridSources"]["elements"]
            );
        }

        return $hubCategories;
    }

    /**
     * Check is custom archive template is set
     *
     * @return bool|int|mixed|void
     */
    public function isArchivePage(Options $optionsHelper, Frontend $frontendHelper)
    {
        if (is_404()) {
            return false;
        }

        $archiveName = $this->call('getArchiveTemplateName');
        if (empty($archiveName)) {
            return false;
        }

        $archiveTemplate = $optionsHelper->get('custom-page-templates-' . $archiveName . '-template', false);

        // Check backward compatibility
        if ($archiveName === 'post' && $archiveTemplate === false) {
            $archiveTemplate = $optionsHelper->get('custom-page-templates-archive-template', false);
        }

        if ($archiveTemplate && !$frontendHelper->isPageEditable()) {
            VcvEnv::set('VCV_IS_ARCHIVE_TEMPLATE', true);
            $sourceId = (int)$archiveTemplate;
            $sourceId = apply_filters('wpml_object_id', $sourceId, 'post', true);
            $post = get_post($sourceId);
            // @codingStandardsIgnoreLine
            if ($post && $post->post_status === 'publish') {
                return $sourceId;
            }
        }

        return false;
    }

    /**
     * Get archive template name
     *
     * @return string
     */
    public function getArchiveTemplateName()
    {
        // @codingStandardsIgnoreStart
        global $wp_query;
        $taxonomyQuery = '';

        if (isset($wp_query->tax_query->queries[0]['taxonomy'])) {
            $taxonomyQuery = $wp_query->tax_query->queries[0]['taxonomy'];
        }
        $queriedCustomTaxonomy = isset($wp_query->queried_object->taxonomy) ? $wp_query->queried_object->taxonomy : '';

        $postTypeList = ['post', 'product'];
        $taxonomies = self::getTaxonomyList($postTypeList);
        $archiveName = '';
        $isPostPage = $this->call('isPostsPage');
        $isHomeArchive = $this->call('isHomeArchive');

        if (function_exists('is_shop') && is_shop()) {
            $archiveName = 'product';
        } elseif ((isset($queriedCustomTaxonomy) && in_array($queriedCustomTaxonomy, $taxonomies)
                && is_tax(
                    $queriedCustomTaxonomy
                )
            )
            || ($taxonomyQuery === 'post_tag' || $taxonomyQuery === 'category')
        ) {
            if (empty($queriedCustomTaxonomy)) {
                $queriedCustomTaxonomy = $taxonomyQuery;
            }
            $archiveName = self::checkArchiveToggle($postTypeList, $queriedCustomTaxonomy);
        } elseif (is_author()) {
            $archiveName = 'author';
        } elseif (is_search()) {
            $archiveName = 'search';
        } elseif (is_year() || is_month() || is_day() || $isPostPage || $isHomeArchive) {
            $archiveName = 'post';
        }

        return $archiveName;
    }

    /**
     * Check if current page is home page with the latest post on it and post archive is set.
     *
     * @return bool
     */
    protected function isHomeArchive() {
        $isHomeArchive = false;
        if (is_home()) {
            $optionsHelper = vchelper('Options');
            $homePageId = get_option('page_on_front');
            $postArchiveTemplate = $optionsHelper->get('custom-page-templates-post-template', false);

            if (empty($homePageId) && !empty($postArchiveTemplate)) {
                $isHomeArchive = true;
            }
        }

        return $isHomeArchive;
    }

    /**
     * Check if current page is posts page archive.
     *
     * @return bool
     */
    protected function isPostsPage() {
        global $wp_query;

        $isPostPage = false;
        if ($wp_query->is_posts_page) {
            // @codingStandardsIgnoreEnd
            $currentPage = get_queried_object();

            // Check active post page
            $pageForPostID = get_option('page_for_posts');
            $isPageForPosts = $pageForPostID !== '0' && $currentPage->ID == $pageForPostID;

            if ($isPageForPosts || ($pageForPostID === '0' && is_home())) {
                $isPostPage = true;
            }
        }

        return $isPostPage;
    }

    /**
     * Get taxonomy list
     *
     * @param $postTypes
     *
     * @return array
     */
    public static function getTaxonomyList($postTypes)
    {
        $taxonomyList = [];
        foreach ($postTypes as $postType) {
            $taxonomies = get_object_taxonomies($postType, 'object');
            foreach ($taxonomies as $taxonomy) {
                // @codingStandardsIgnoreLine
                if ($taxonomy->show_ui === true) {
                    $taxonomyList[] = $taxonomy->name;
                }
            }
        }

        return $taxonomyList;
    }

    /**
     * Get taxonomy template name
     *
     * @param $postTypes
     * @param $currentTaxonomy
     *
     * @return bool|string
     */
    public static function checkArchiveToggle($postTypes, $currentTaxonomy)
    {
        $optionsHelper = vchelper('Options');

        foreach ($postTypes as $postType) {
            $taxonomies = get_object_taxonomies($postType, 'object');
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy->name === $currentTaxonomy) {
                    $isToggleActive = $optionsHelper->get('custom-page-templates-enabled-' . $postType);
                    if ($isToggleActive === 'custom-template-enabled-' . $postType) {
                        $taxonomyTemplate = $optionsHelper->get(
                            'custom-page-templates-' . $taxonomy->name . '-template',
                            ''
                        );

                        if ($taxonomyTemplate) {
                            return $taxonomy->name;
                        }
                    } else {
                        return $postType;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Renders custom archive page template if it is set
     */
    protected function renderArchivePage()
    {
        // @codingStandardsIgnoreLine
        global $post, $wp_query, $wp_the_query;

        $id = $this->call('isArchivePage');

        if (!$id) {
            return;
        }

        $post = get_post($id);
        $id = $post->ID; // in case if translated

        // Set current page query before change wp_query
        $taxonomyData = false;
        // @codingStandardsIgnoreLine
        if (!$wp_the_query->is_posts_page) {
            $taxonomyData = get_queried_object();
        }

        $isShop = false;
        if (function_exists('is_shop') && is_shop()) {
            $isShop = true;
        }

        ob_start();
        the_archive_title('<h1>', '</h1>');
        $title = ob_get_clean();

        $queryArgs = [
            'suppress_filters' => true,
            'post_type' => $this->postType,
            'p' => $id,
            'taxonomyData' => $taxonomyData,
            'isShop' => $isShop,
        ];

        // @codingStandardsIgnoreStart
        $customWpQueryArgs = array_merge($queryArgs, ['queriedPage' => $wp_query]);
        // set local current query
        $wp_query = new WP_Query($queryArgs);

        $customWpTheQueryArgs = array_merge($queryArgs, ['queriedPage' => $wp_the_query]);
        // set global query also!
        $wp_the_query = new WP_Query($customWpTheQueryArgs);

        // set archive title to initial taxonomy title
        if (isset($wp_the_query->posts[0]->post_title)) {
            // @codingStandardsIgnoreLine
            $wp_the_query->posts[0]->post_title = $title;
        }

        if (isset($wp_query->posts[0]->post_title)) {
            // @codingStandardsIgnoreLine
            $wp_query->posts[0]->post_title = $title;
        }
        // @codingStandardsIgnoreEnd

        $template = get_page_template();

        if ($template = apply_filters('template_include', $template)) {
            include $template;
        }
        exit;
    }

    /**
     * Always should treat current page as vcv page if it's related to any of our layouts.
     *
     * @param bool $isVcvFrontend
     *
     * @return bool
     */
    protected function setVcvFrontend($isVcvFrontend)
    {
        if (get_post_type() === $this->postType) {
            return true;
        }

        return $isVcvFrontend;
    }
}
