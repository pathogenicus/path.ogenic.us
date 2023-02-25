<?php

namespace exportImport\exportImport;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\File;
use VisualComposer\Helpers\Request;
use ZipArchive;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class ExportController
 * @package exportImport\exportImport
 */
class ExportController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * @var array
     */
    protected $templateFolders = [];

    /**
     * @var array
     */
    protected $postsToExport = [];

    /**
     * @var array
     */
    protected $exportContent = [];

    /**
     * @var array
     */
    protected $imageData = [];

    /**
     * @var array
     */
    protected $videosData = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * ExportController constructor.
     */
    public function __construct()
    {
        $this->wpAddFilter('post_row_actions', 'addExportLink', 100);
        $this->addFilter('vcv:addons:globalTemplate:adminCss', '__return_false');
        $this->addFilter('vcv:ajax:exportImport:export:adminNonce', 'handleSingleExport');
        $this->addFilter('vcv:addons:exportImport:export:postMeta', 'filterPostMeta');
        $this->addEvent('vcv:admin:inited', 'exportActionHandler');
    }

    /**
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function handleSingleExport(Request $requestHelper)
    {
        $sourceId = $requestHelper->input('sourceId');

        $this->addToExportQueue($sourceId);
        $this->call('prepareExportPost');
    }

    /**
     * Add post to export queue
     *
     * @param $sourceId
     */
    protected function addToExportQueue($sourceId)
    {
        $this->postsToExport = array_merge($this->postsToExport, (array)$sourceId);
    }

    /**
     * Parse export content and crete download zip
     *
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function prepareExportPost()
    {
        $postsToExport = $this->postsToExport;

        foreach ($postsToExport as $sourceId) {
            $post = get_post($sourceId);

            $pageContent = get_post_meta($sourceId, VCV_PREFIX . 'pageContent', true);
            if (!empty($pageContent)) {
                $data = $pageContent;
                // @codingStandardsIgnoreLine
            } elseif ($post->post_type === 'vcv_templates') {
                $data = rawurlencode(
                    wp_json_encode(
                        [
                            'elements' => get_post_meta($sourceId, 'vcvEditorTemplateElements', true),
                        ]
                    )
                );
            }

            $content = [];
            $decoded = json_decode(rawurldecode($data), true);
            if ($decoded && isset($decoded['elements'])) {
                $content = $decoded['elements'];
            }

            $postUniqueId = get_post_meta($sourceId, '_' . VCV_PREFIX . 'id', true);
            if (!$postUniqueId) {
                $postUniqueId = uniqid('', true);
                update_post_meta($sourceId, '_' . VCV_PREFIX . 'id', $postUniqueId);
            }

            $this->templateFolders[ $postUniqueId ] = 'templates/' . $postUniqueId;

            $parsedContent = $this->parseContent($content);
            $this->addExportContent($post, $sourceId, $parsedContent, $postUniqueId);
        }
        $this->call('export');
    }

    /**
     * Find all images in content
     *
     * @param $content
     *
     * @return array
     */
    protected function parseContent($content)
    {
        $this->imageData = [];
        $this->videosData = [];
        $this->tags = [];

        array_walk_recursive($content, [$this, 'findUrls']);
        array_walk_recursive($content, [$this, 'findTags']);

        return [
            'imagesData' => $this->imageData,
            'videosData' => $this->videosData,
            'content' => $content,
            'tags' => array_values(array_unique($this->tags)),
        ];
    }

    /**
     * @param $value
     * @param $key
     */
    public function findUrls(&$value, $key)
    {
        $skippedKeys = ['metaPreviewUrl', 'metaThumbnailUrl'];

        if (!in_array($key, $skippedKeys, true)) {
            /** @see make_clickable() */
            $urlRegex = '@(https?:\/\/([-\w\.]+[-\w])+(:\d+)?(\/([\w/_\.#-]*(\?\S+)?[^\.\s\"\'\]\[])?)?)@';
            $value = preg_replace_callback($urlRegex, [$this, 'replaceUrls'], $value);
        }
    }

    /**
     * @param $value
     * @param $key
     */
    public function findTags(&$value, $key)
    {
        if ($key === 'tag') {
            $this->tags[] = 'element/' . $value;
        }
    }

    /**
     * Replace found images with placeholders
     *
     * @param $matches
     *
     * @return string
     */
    protected function replaceUrls($matches)
    {
        //check if it's a image and replace it with placeholders
        if (vchelper('WpMedia')->checkIsImage($matches[0])) {
            $imageLink = $matches[0];
            $imageName = pathinfo($imageLink, PATHINFO_FILENAME) . '.' . pathinfo($imageLink, PATHINFO_EXTENSION);
            $this->imageData[ $imageName ] = $imageLink;

            return '[publicPath]/assets/elements/' . $imageName;
        }

        //check if it's a video and replace it with placeholders
        if (vchelper('WpMedia')->checkIsVideo($matches[0])) {
            $videoLink = $matches[0];
            $videoName = pathinfo($videoLink, PATHINFO_FILENAME) . '.' . pathinfo($videoLink, PATHINFO_EXTENSION);
            $this->videosData[ $videoName ] = $videoLink;

            return '[publicPath]/assets/elements/' . $videoName;
        }

        return $matches[0];
    }

    /**
     * Create a export zip file
     *
     * @param $post
     * @param $sourceId
     * @param $parsedContent
     * @param $postUniqueId
     */
    protected function addExportContent($post, $sourceId, $parsedContent, $postUniqueId)
    {
        $type = get_post_meta($sourceId, '_' . VCV_PREFIX . 'type', true);
        $this->exportContent[ $postUniqueId ] = [
            'id' => $postUniqueId,
            'type' => !empty($type) ? $type : 'custom',
            'tags' => $parsedContent['tags'],
            'data' => $parsedContent['content'],
            'images' => $parsedContent['imagesData'],
            'videos' => $parsedContent['videosData'],
            'postMeta' => vcfilter(
                'vcv:addons:exportImport:export:postMeta',
                get_post_meta($sourceId),
                // @codingStandardsIgnoreLine
                ['sourceId' => $sourceId, 'postType' => $post->post_type]
            ),
            // @codingStandardsIgnoreLine
            'postName' => $post->post_name,
            // @codingStandardsIgnoreLine
            'postType' => $post->post_type,
            'post' => $post,
        ];
    }

    /**
     * Create an export zip file and download it
     *
     * @param \VisualComposer\Helpers\File $fileHelper
     */
    protected function export(File $fileHelper)
    {
        $zip = new ZipArchive();

        $tempDir = get_temp_dir();
        $posts = $this->exportContent;
        $manifest = [];
        $manifest['blogUrl'] = get_site_url();
        $manifest['templates'] = [];

        if (count($posts) > 1) {
            $zipName = 'vc-' . 'templates-' . date('d-m-Y-H-i') . '.zip';
            $zip->open($tempDir . $zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($posts as $postUniqueId => $post) {
                $manifest['templates'] = $manifest['templates'] + [
                        $post['postName'] => [
                            'templateId' => $postUniqueId,
                            'templateTitle' => $post['post']->post_title,
                        ],
                    ];

                $zip->addFromString($post['postName'] . '/bundle.json', json_encode($post));

                $prefixName = $post['postName'] . '/' . $this->templateFolders[ $postUniqueId ] . '/assets/elements/';
                $zip = $this->addPostFilesToZip($zip, $post, $prefixName);
            }
        } else {
            $post = current($posts);
            $zipName = 'vc-' . $post['post']->post_name . '-' . date('d-m-Y-H-i') . '.zip';
            $zip->open($tempDir . $zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $manifest['templates'][] = [
                'templateId' => $post['id'],
                'templateTitle' => $post['post']->post_title,
            ];

            $zip->addFromString('bundle.json', json_encode($post));

            $prefixName = $this->templateFolders[ $post['id'] ] . '/assets/elements/';
            $zip = $this->addPostFilesToZip($zip, $post, $prefixName);
        }

        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $zipName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempDir . $zipName));
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($tempDir . $zipName);
        $fileHelper->removeFile($tempDir . $zipName);
        exit;
    }

    /**
     * Add post files to export zip archive.
     *
     * @param ZipArchive $zip
     * @param \WP_Post $post
     * @param string $prefixName
     *
     * @return mixed
     */
    protected function addPostFilesToZip($zip, $post, $prefixName)
    {
        $files = array_merge($post['images'], $post['videos']);
        if (empty($files)) {
            return $zip;
        }
        
        $fileHelper = vchelper('File');

        foreach ($files as $name => $file) {
            $downloadedFile = $fileHelper->getRemoteContents($file);
            if (!$downloadedFile) {
                continue;
            }
            $zip->addFromString(
                $prefixName . $name,
                $downloadedFile
            );
        }

        return $zip;
    }

    /**
     * Add export links to post
     *
     * @param $actions
     * @param $post
     *
     * @return mixed
     */
    protected function addExportLink(
        $actions,
        $post
    ) {
        if (!$this->isCanPostExport($post)) {
            return $actions;
        }

        $url = $this->getExportUrl($post);

        $statusHelper = vchelper('Status');
        $exportTitle = esc_html__('Export', 'visualcomposer');
        if ($statusHelper->getZipStatus()) {
            $actions['vcv_export'] = sprintf('<a href="%s">%s</a>', $url, $exportTitle);
        } else {
            $errorMessage = esc_html__("Can't export because php-zip extension is missing or not activated", 'visualcomposer');
            $actions['vcv_export'] = sprintf('<a onclick="event.preventDefault(); confirm(\'%s\')" href="">%s</a>', $errorMessage, $exportTitle);
        }

        return $actions;
    }

    /**
     * Get export url link.
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    protected function getExportUrl($post)
    {
        $nonceHelper = vchelper('Nonce');
        $urlHelper = vchelper('Url');

        return $urlHelper->adminAjax(
            [
                'vcv-action' => 'exportImport:export:adminNonce',
                'vcv-nonce' => $nonceHelper->admin(),
                'sourceId' => $post->ID,
            ]
        );
    }

    /**
     * Check if post can be exported.
     *
     * @param \WP_Post $post
     *
     * @return bool
     */
    protected function isCanPostExport($post)
    {
        $userCapabilitiesHelper = vchelper('AccessUserCapabilities');

        $postType = vcfilter('vcv:addons:exportImport:allowedPostTypes', []);

        $isCanExport =
            // @codingStandardsIgnoreLine
            in_array($post->post_type, $postType, true) &&
            $userCapabilitiesHelper->canEdit($post->ID);

        return $isCanExport;
    }

    /**
     * Register bulk action
     */
    protected function exportActionHandler()
    {
        $allowedPostTypes = vcfilter('vcv:addons:exportImport:allowedPostTypes', []);

        if (!empty($allowedPostTypes)) {
            foreach ($allowedPostTypes as $postType) {
                $this->wpAddFilter('bulk_actions-edit-' . $postType, 'addExportAction');
                $this->wpAddFilter('handle_bulk_actions-edit-' . $postType, 'actionHandler', 10, 3);
            }
        }
    }

    /**
     * @param $actions
     *
     * @return mixed
     */
    protected function addExportAction($actions)
    {
        $actions['vcv_export'] = __('Export', 'visualcomposer');

        return $actions;
    }

    /**
     * @param $redirectTo
     * @param $action
     * @param $sourceIds
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function actionHandler($redirectTo, $action, $sourceIds)
    {
        if ($action !== 'vcv_export') {
            return $redirectTo;
        }

        $this->addToExportQueue($sourceIds);

        $this->call('prepareExportPost');

        return $redirectTo; // die will be called in prepareExportPost
    }

    /**
     * @param $meta
     * @param $payload
     *
     * @return mixed
     */
    protected function filterPostMeta($meta, $payload)
    {
        $postType = $payload['postType'];
        if ($postType === 'vcv_template') {
            unset($meta['_vcv-HeaderTemplateId']);
            unset($meta['_vcv-FooterTemplateId']);
            unset($meta['_vcv-SidebarTemplateId']);
        }

        return $meta;
    }
}
