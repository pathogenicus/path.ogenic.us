<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\PageLayout;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Str;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class LayoutController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    protected $addonPath;

    public function __construct(Addons $addonsHelper)
    {
        $this->addonPath = rtrim($addonsHelper->getAddonRealPath('themeEditor'), '\\/');

        $this->wpAddAction(
            'wp',
            'addLayoutCss'
        );

        $this->addFilter('vcv:themeEditor:layoutController:getTemplatePartId', 'isCustomPage', 20);
        $this->addFilter('vcv:themeEditor:layoutController:getTemplatePartId', 'isPost', 30);

        $this->addFilter('vcv:editor:variables', 'addTemplatesVariables');
    }

    protected function addTemplatesVariables($variables, $payload)
    {
        if (isset($payload['sourceId'])) {
            $layoutController = vcapp('\themeEditor\themeEditor\LayoutController');
            $globalTemplatePartData['header'] = $layoutController->getGlobalTemplatePartData('header');
            $globalTemplatePartData['footer'] = $layoutController->getGlobalTemplatePartData('footer');
            $globalTemplatePartData['sidebar'] = get_post_meta(
                $payload['sourceId'],
                '_' . VCV_PREFIX . 'SidebarTemplateId'
            );

            $variables[] = [
                'key' => 'VCV_GLOBAL_DATA',
                'value' => $globalTemplatePartData,
                'type' => 'constant',
            ];
        }

        return $variables;
    }

    protected function templatePath()
    {
        return $this->addonPath . '/views/layouts/';
    }

    protected function assetsPath()
    {
        return $this->addonPath . '/public/layouts/';
    }

    protected function addLayoutCss(
        Str $strHelper,
        Addons $addonsHelper,
        Request $requestHelper,
        PageLayout $pageLayoutHelper
    ) {
        $pageTemplate = false;
        $stretched = false;
        if ($requestHelper->input('vcv-template-type', '') === 'vc-theme') {
            $pageTemplate = $requestHelper->input('vcv-template');
            $stretched = intval($requestHelper->input('vcv-template-stretched'));
        } else {
            $currentTemplate = $pageLayoutHelper->getCurrentPageLayout(
                [
                    'type' => 'theme',
                    'value' => 'default',
                ]
            );
            if (!empty($currentTemplate) && is_array($currentTemplate)
                && isset($currentTemplate['type'])
                && $currentTemplate['type'] === 'vc-theme'
            ) {
                $pageTemplate = $currentTemplate['value'];
                $stretched = $currentTemplate['stretchedContent'];
            }
        }
        $this->enqueueLayoutCss($strHelper, $addonsHelper, $pageTemplate, $stretched);
    }

    /**
     * @param \VisualComposer\Helpers\Str $strHelper
     * @param \VisualComposer\Helpers\Hub\Addons $addonsHelper
     * @param $pageTemplate
     * @param $stretched
     */
    protected function enqueueLayoutCss(Str $strHelper, Addons $addonsHelper, $pageTemplate, $stretched)
    {
        if ($pageTemplate
            && in_array(
                $pageTemplate,
                [
                    'header-footer-sidebar-layout',
                    'header-footer-sidebar-left-layout',
                ]
            )
        ) {
            $addonUrl = $addonsHelper->getAddonUrl('themeEditor/themeEditor');
            $cssUrl = $addonUrl . '/public/layouts/css/bundle.min.css';
            wp_enqueue_style('vcv:theme:layout:bundle:css', $cssUrl);

            $file = $this->templatePath() . VCV_PREFIX . $pageTemplate . '.php';
            $fileName = basename($pageTemplate, '.php');
            $cssFilePart = 'css/' . VCV_PREFIX . $fileName . ($stretched ? '-stretched' : '') . '.min.css';
            $cssPath = $this->assetsPath() . $cssFilePart;
            if (file_exists($file) && file_exists($cssPath)) {
                $cssUrl = $addonUrl . '/public/layouts/' . $cssFilePart;
                $assetName = 'vcv:theme:layout:' . $strHelper->slugify($fileName) . ':css';
                wp_enqueue_style($assetName, $cssUrl);

                if (!$stretched) {
                    $customLayoutWidth = vchelper('Options')->get('custom-page-templates-section-layout-width', '1140px');
                    $customLayoutWidth = (int)preg_replace('/[^0-9.]/', '', $customLayoutWidth);
                    if (empty($customLayoutWidth)) {
                        $customLayoutWidth = 1140;
                    }
                    $sidebarWidth = (int)vcfilter('vcv:addons:themeEditor:sidebarWidth', 320, ['layoutWidth' => $customLayoutWidth]);
                    $customLayoutWidth = $customLayoutWidth >= $sidebarWidth ? $customLayoutWidth : $sidebarWidth;

                    // add inline style after enqueue
                    $contentWidth = $customLayoutWidth - $sidebarWidth;
                    // fileName is left sidebar
                    if (strpos($fileName, 'left') !== false) {
                        $css = <<<CSS
                    @media (min-width: 1200px) {
                         .vcv-layout-wrapper {
                             grid-template-columns: 1fr ${sidebarWidth}px ${contentWidth}px 1fr;
                         }
                     }
CSS;
                    } else {
                        $css = <<<CSS
                    @media (min-width: 1200px) {
                         .vcv-layout-wrapper {
                             grid-template-columns: 1fr ${contentWidth}px ${sidebarWidth}px 1fr;
                         }
                     }
CSS;
                    }

                    wp_add_inline_style($assetName, $css);
                }
            }
        }
    }

    /**
     * Find specific template part id
     *
     * @param $templatePart
     *
     * @return bool|mixed
     */
    public function getTemplatePartId($templatePart)
    {
        $specificPost = $this->getSpecificPostTemplatePartData($templatePart);

        if ($specificPost['replaceTemplate']) {
            return $specificPost;
        } else {
            return $this->getGlobalTemplatePartData($templatePart);
        }
    }

    /**
     * Get id list of HFS assigned to specific post.
     *
     * @return array
     */
    public function getHfsIdList()
    {
        $hfsNameList = [
            'header',
            'footer',
            'sidebar',
        ];

        $hfsIdList = [];
        foreach ($hfsNameList as $name) {
            $getTemplatePartId = $this->getTemplatePartId($name);

            if (empty($getTemplatePartId['sourceId'])) {
                continue;
            }

            $templateId = $getTemplatePartId['sourceId'];

            if (!in_array($templateId, $hfsIdList)) {
                $hfsIdList[] = $getTemplatePartId['sourceId'];
            }
        }

        return $hfsIdList;
    }

    /**
     * @param $templatePart
     *
     * @return bool|mixed
     */
    public function getGlobalTemplatePartData($templatePart)
    {
        $optionsHelper = vchelper('Options');
        $headerFooterSettings = $optionsHelper->get('headerFooterSettings');

        if ($headerFooterSettings === 'allSite') {
            return $this->allContent($templatePart);
        } elseif ($headerFooterSettings === 'customPostType') {
            $customTemplatePart = vcfilter(
                'vcv:themeEditor:layoutController:getTemplatePartId',
                ['pageFound' => false, 'replaceTemplate' => true, 'sourceId' => false],
                ['templatePart' => $templatePart]
            );
            if ($customTemplatePart && $customTemplatePart['replaceTemplate'] && $customTemplatePart['pageFound']) {
                return $customTemplatePart;
            }
        }

        return ['pageFound' => false, 'replaceTemplate' => false, 'sourceId' => false];
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return bool|mixed
     */
    protected function isPost($response, $payload)
    {
        if (!$response['pageFound'] && $response['replaceTemplate']) {
            $templatePart = $payload['templatePart'];
            $optionsHelper = vchelper('Options');

            $postType = get_post_type();
            $separatePostType = $optionsHelper->get('headerFooterSettingsSeparatePostType-' . $postType);
            if ($separatePostType && !empty($separatePostType) && is_singular()) {
                $key = 'headerFooterSettingsSeparatePostType' . ucfirst($templatePart) . '-' . $postType;
                $templatePartId = $optionsHelper->get($key);
                if ($templatePart) {
                    return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => $templatePartId];
                }

                return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => false];
            }
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return bool|mixed
     */
    protected function isCustomPage($response, $payload)
    {
        if ($response['pageFound'] && !$response['replaceTemplate']) {
            return $response;
        }

        $templatePart = $payload['templatePart'];

        if (is_front_page() || is_home()) {
            return $this->getFrontPageTemplatePartData($templatePart);
        } else {
            return $this->getOtherPageTemplatePartData($response, $templatePart);
        }
    }

    /**
     * @param $templatePart
     *
     * @return bool|mixed
     */
    protected function allContent($templatePart)
    {
        $optionsHelper = vchelper('Options');
        $templatePartId = $optionsHelper->get('headerFooterSettingsAll' . ucfirst($templatePart));

        if ($templatePart) {
            return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => (int)$templatePartId];
        }

        return false;
    }

    /**
     * @param $templatePart
     *
     * @return array
     */
    protected function getFrontPageTemplatePartData($templatePart)
    {
        $optionsHelper = vchelper('Options');
        if ($optionsHelper->get('headerFooterSettingsPageType-frontPage')) {
            $templatePartId = $optionsHelper->get(
                'headerFooterSettingsPageType' . ucfirst($templatePart) . '-frontPage'
            );
            if ($templatePartId) {
                return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => $templatePartId];
            }

            return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => false];
        }

        return ['pageFound' => true, 'replaceTemplate' => false, 'sourceId' => false];
    }

    /**
     * @param $templatePart
     *
     * @return array
     */
    protected function getPostPageTemplatePartData($templatePart)
    {
        $optionsHelper = vchelper('Options');
        if ($optionsHelper->get('headerFooterSettingsPageType-postPage')) {
            $templatePartId = $optionsHelper->get(
                'headerFooterSettingsPageType' . ucfirst($templatePart) . '-postPage'
            );
            if ($templatePartId) {
                return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => $templatePartId];
            }

            return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => false];
        }

        return ['pageFound' => true, 'replaceTemplate' => false, 'sourceId' => false];
    }

    /**
     * @param $response
     * @param $templatePart
     *
     * @return bool|array
     */
    protected function getOtherPageTemplatePartData($response, $templatePart)
    {
        $pageType = false;

        if (is_author()) {
            $pageType = 'author';
        } elseif (is_search()) {
            $pageType = 'search';
        } elseif (is_404()) {
            $pageType = 'notFound';
        } elseif (vcfilter('vcv:themeEditor:layoutController:getOtherPageTemplatePartData:isArchive', is_archive())) {
            $query = get_queried_object();

            // currently, we support all custom taxonomies only for posts
            $taxList = get_object_taxonomies('post');
            if (in_array($query->taxonomy, $taxList)) {
                $pageType = $query->taxonomy;
            } else {
                $pageType = 'archive';
            }
        }

        if ($pageType) {
            $optionsHelper = vchelper('Options');
            $separatePageType = $optionsHelper->get('headerFooterSettingsPageType-' . $pageType);

            if ($separatePageType && !empty($separatePageType)) {
                $key = 'headerFooterSettingsPageType' . ucfirst($templatePart) . '-' . $pageType;

                $templatePartId = $optionsHelper->get($key);
                if ($templatePartId) {
                    return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => $templatePartId];
                }

                return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => false];
            }
        }

        return $response;
    }

    /**
     * Find header and footer for view page and page editable
     *
     * @param $templatePart
     *
     * @return array
     */
    protected function getSpecificPostTemplatePartData($templatePart)
    {
        $frontendHelper = vchelper('Frontend');
        $requestHelper = vchelper('Request');

        $sourceId = vchelper('Preview')->updateSourceIdWithAutosaveId(get_the_ID());

        if ($frontendHelper->isPageEditable() && $requestHelper->exists('vcv-' . $templatePart)) {
            return $this->getPageEditableTemplatePartData($templatePart, $requestHelper);
        }

        $currentTemplateId = get_post_meta(
            $sourceId,
            '_' . VCV_PREFIX . ucfirst($templatePart) . 'TemplateId',
            true
        );

        $footerId = get_post_meta(
            $sourceId,
            '_' . VCV_PREFIX . 'FooterTemplateId',
            true
        );
        $headerId = get_post_meta(
            $sourceId,
            '_' . VCV_PREFIX . 'HeaderTemplateId',
            true
        );

        if ($currentTemplateId && $currentTemplateId !== 'default') {
            return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => (int)$currentTemplateId];
        }

        if ($footerId === 'default' && $headerId === 'default') {
            return ['pageFound' => true, 'replaceTemplate' => false, 'sourceId' => false];
        }

        if ($footerId || $headerId) {
            $getGlobalTemplatePartId = $this->getGlobalTemplatePartData($templatePart);

            return [
                'pageFound' => true,
                'replaceTemplate' => true,
                'sourceId' => $getGlobalTemplatePartId['sourceId'],
            ];
        }

        return ['pageFound' => false, 'replaceTemplate' => false, 'sourceId' => false];
    }

    /**
     * Find header and footer on page editable
     *
     * @param $templatePart
     * @param Request $requestHelper
     *
     * @return array
     */
    protected function getPageEditableTemplatePartData($templatePart, Request $requestHelper)
    {
        $currentTemplateId = $requestHelper->input('vcv-' . $templatePart);
        $footerId = $requestHelper->input('vcv-footer');
        $headerId = $requestHelper->input('vcv-header');
        if ($currentTemplateId !== 'default') {
            return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => intval($currentTemplateId)];
        }

        if (($footerId === 'default' && $headerId === 'default')
            || !in_array('default', [$headerId, $footerId], true)
        ) {
            return ['pageFound' => true, 'replaceTemplate' => false, 'sourceId' => false];
        }

        $getGlobalTemplatePartId = $this->getGlobalTemplatePartData($templatePart);

        return ['pageFound' => true, 'replaceTemplate' => true, 'sourceId' => $getGlobalTemplatePartId['sourceId']];
    }
}
