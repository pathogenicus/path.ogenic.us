<?php

namespace exportImport\exportImport;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\File;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Hub\Bundle;
use VisualComposer\Helpers\Hub\Categories as HubCategories;
use VisualComposer\Helpers\Hub\Elements;
use VisualComposer\Helpers\Hub\Templates;
use VisualComposer\Helpers\Nonce;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Token;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\WpMedia;

/**
 * Class ImportController.
 */
class ImportController extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var bool
     */
    protected $newTemplateId = false;

    /**
     * @var bool
     */
    protected $fileId = false;

    /**
     * @var bool
     */
    protected $file = false;

    /**
     * @var bool
     */
    protected $bundleJson = false;

    /**
     * @var bool
     */
    protected $zipManifest = false;

    /**
     * @var bool|mixed|\VisualComposer\Helpers\Hub\Bundle
     */
    protected $hubBundleHelper = false;

    /**
     * ImportController constructor.
     */
    public function __construct()
    {
        $this->wpAddAction('admin_init', 'registerImporter');
        $this->addFilter('vcv:ajax:vcv:addon:exportImport:importProgress:adminNonce', 'getImportProgress');
        $this->addFilter('vcv:ajax:vcv:addon:exportImport:continueImport:adminNonce', 'continueImport');
        $this->hubBundleHelper = vchelper('HubBundle');
        $this->hubBundleHelper->setTempBundleFolder(
            VCV_PLUGIN_ASSETS_DIR_PATH . '/temp-bundle-addon-templateImport'
        );
    }

    /**
     * Must be public
     *
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    public function dispatch()
    {
        $requestHelper = vchelper('Request');

        $this->header();

        $step = !$requestHelper->exists('step') ? 0 : (int)$requestHelper->input('step');
        switch ($step) {
            case 0:
                $this->uploadForm();
                break;
            case 1:
                check_admin_referer('import-upload');
                $response = $this->call('handleUpload');
                if (!vcIsBadResponse($response)) {
                    $this->call('approveImportTemplate');
                } else {
                    $this->call('abortImport');
                    $this->call('errorMessage', [$response['message']]);
                }

                break;
        }

        $this->footer();
    }

    /**
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @param \VisualComposer\Helpers\Nonce $nonceHelper
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function continueImport(Request $requestHelper, Nonce $nonceHelper)
    {
        if ($nonceHelper->verifyAdmin($requestHelper->input('vcv-nonce'))) {
            $this->fileId = (int)$requestHelper->input('vcv-file-id');
            $this->file = get_attached_file($this->fileId);

            $importData = $this->call('parseZipManifest', [$this->file]);
            if (vcIsBadResponse($importData)) {
                $this->call('abortImport');

                return json_encode($importData);
            }

            $zipContent = $this->parseZipContent($importData);
            if (!vcIsBadResponse($zipContent)) {
                set_time_limit(0);

                return $this->call('import');
            }

            $this->call('abortImport');

            return json_encode($zipContent);
        }

        return json_encode(
            [
                'status' => false,
                'message' => __(
                    'Failed to validate nonce.',
                    'visualcomposer'
                ),
            ]
        );
    }

    /**
     *
     */
    protected function registerImporter()
    {
        register_importer(
            'vcv-import',
            'Visual Composer Website Builder',
            __(
                'Import <strong>templates, headers, footers, and sidebars</strong> from an export file (exported with Visual Composer).',
                'visualcomposer'
            ),
            [$this, 'dispatch']
        );
    }

    /**
     *
     */
    protected function uploadForm()
    {
        echo '<div class="narrow">';
        echo sprintf(
            '<p class="description">%s</p>',
            __(
                'Import your exported Visual Composer templates, to migrate them between WordPress sites. All graphic assets will be added to the Media Library.',
                'visualcomposer'
            )
        );
        echo '<p class="description">' . __('Choose a .zip file to upload and click "Upload file and import".', 'visualcomposer')
            . '</p>';
        $this->uploadFormFromWp('admin.php?page=vcv-import&step=1');
        echo '</div>';
    }

    protected function uploadFormFromWp($action)
    {
        // copy of @see \wp_import_upload_form
        /**
         * Filters the maximum allowed upload size for import files.
         *
         * @param int $max_upload_size Allowed upload size. Default 1 MB.
         *
         * @see wp_max_upload_size()
         *
         * @since 2.3.0
         *
         */
        $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
        $size = size_format($bytes);
        $uploadDir = wp_upload_dir();
        if (!empty($uploadDir['error'])) {
            echo '<div class="error"><p class="description">' .
                _e(
                    'Before you can upload your import file, you will need to fix the following error:'
                ) . '</p><p><strong>' . $uploadDir['error'] . '</strong></p></div>';
        } else {
            echo '<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="'
                .
                esc_url(wp_nonce_url($action, 'import-upload')) . '">';
            echo '<p class="description">';
            echo sprintf(
                '<label for="upload">%s</label> (%s)',
                __('Choose a file from your computer:'),
                /* translators: %s: Maximum allowed file size. */
                sprintf(__('Maximum size: %s'), $size)
            );
            echo '<input type="file" id="upload" name="import" size="25" /><input type="hidden" name="action" value="save" /><input type="hidden" name="max_file_size" value="' . $bytes . '" /></p>';

            submit_button(
                __('Upload file and import'),
                'vcv-dashboard-button vcv-dashboard-button--save vcv-dashboard-button--inline',
                'submit',
                true,
                ['disabled' => 1]
            );
            echo '</form>';
        }
    }

    /**
     *
     */
    protected function header()
    {
        echo '<div class="wrap">';
        if (vchelper('Request')->input('page') !== 'vcv-import') {
            echo '<h2>' . __('Visual Composer Website Builder', 'visualcomposer') . '</h2>';
        }
    }

    /**
     *
     */
    protected function footer()
    {
        echo '</div>';
    }

    /**
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Access\CurrentUser $accessCurrentUserHelper
     *
     * @return array|bool|mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function handleUpload(Options $optionsHelper, CurrentUser $accessCurrentUserHelper)
    {
        $this->call('removeTempContent');
        $optionsHelper->deleteTransient('import:progress');
        $file = wp_import_handle_upload(null, true);

        if (isset($file['error'])) {
            return ['status' => false, 'message' => esc_html($file['error'])];
        }

        if (!file_exists($file['file'])) {
            return [
                'status' => false,
                'message' => sprintf(
                    __(
                        'The export file could not be found at <code>%s</code>. Check your site permissions and try again.',
                        'visualcomposer'
                    ),
                    esc_html($file['file'])
                ),
            ];
        }

        $hasAccessTemplates = $accessCurrentUserHelper->part('dashboard')->can('addon_global_templates')->get();
        $hasAccessPopups = $accessCurrentUserHelper->part('dashboard')->can('addon_popup_builder')->get();
        $hasAccessHfs = $accessCurrentUserHelper->part('dashboard')->can('addon_theme_builder')->get();
        $this->fileId = (int)$file['id'];
        $importData = $this->call('parseZipManifest', [$file['file']]);

        if (vcIsBadResponse($importData)) {
            return $importData;
        }

        foreach ($importData as $id => $item) {
            $bundle = $this->getTemplateBundle($id);
            $bundle = json_decode(file_get_contents($bundle), true);

            if (($bundle['postType'] === 'vcv_templates' && !$hasAccessTemplates) ||
                ($bundle['postType'] === 'vcv_popups' && !$hasAccessPopups) ||
                (in_array($bundle['postType'], ['vcv_headers', 'vcv_footers', 'vcv_sidebars', 'vcv_layouts']) && !$hasAccessHfs)
            ) {
                return [
                    'status' => false,
                    'message' => __(
                        'You don\'t have permission to import this post type.',
                        'visualcomposer'
                    )
                ];
            }
        }

        return ['status' => true];
    }

    /**
     * @param $file
     * @param \VisualComposer\Helpers\File $fileHelper
     *
     * @return array
     */
    protected function parseZipManifest($file, File $fileHelper)
    {
        $tempBundleFolder = $this->hubBundleHelper->getTempBundleFolder('templateImport/');

        if (!$fileHelper->isDir($tempBundleFolder)) {
            $result = $fileHelper->unzip($file, $tempBundleFolder, true);

            if (is_wp_error($result)) {
                return ['status' => false, 'message' => esc_html($result->get_error_message())];
            }
        }

        $manifest = $tempBundleFolder . 'manifest.json';
        if (file_exists($manifest)) {
            $this->zipManifest = $manifest = json_decode(file_get_contents($manifest), true);

            if (isset($manifest['templates']) && !empty($manifest['templates'])) {
                $this->items = $this->items + $manifest['templates'];

                return $manifest['templates'];
            }
        }

        return [
            'status' => false,
            'message' => sprintf(
                __(
                    'Uploaded file <code>%s</code> is invalid.',
                    'visualcomposer'
                ),
                esc_html(substr(basename($file), 0, strrpos(basename($file), '.')))
            ),
        ];
    }

    /**
     * @param $data
     *
     * @return array|bool|mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function parseZipContent($data)
    {
        foreach ($data as $id => $item) {
            $bundle = $this->getTemplateBundle($id);

            if (file_exists($bundle)) {
                $bundle = json_decode(file_get_contents($bundle), true);

                if (isset($bundle['id'])
                    && isset($bundle['tags'])
                    && isset($bundle['post'])
                    && isset($bundle['post']['ID'])
                ) {
                    $templateDependencies = $this->call('parseDependencies', [$bundle['tags']]);

                    if (vcIsBadResponse($templateDependencies)) {
                        return $templateDependencies;
                    }
                }
            } else {
                return [
                    'status' => false,
                    'message' => __('Uploaded .zip file is invalid.', 'visualcomposer'),
                ];
            }
        }

        return true;
    }

    /**
     * @param $tags
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Token $tokenHelper
     * @param \VisualComposer\Helpers\Hub\Bundle $bundleHelper
     * @param \VisualComposer\Helpers\Hub\Elements $elementsHelper
     * @param \VisualComposer\Helpers\Hub\Addons $addonsHelper
     *
     * @return array|bool
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function parseDependencies(
        $tags,
        Options $optionsHelper,
        Token $tokenHelper,
        Bundle $bundleHelper,
        Elements $elementsHelper,
        Addons $addonsHelper
    ) {
        if (!$this->bundleJson) {
            $this->call('updateImportProgress', [__('Validating the license...', 'visualcomposer')]);

            $token = $tokenHelper->getToken();
            if (vcIsBadResponse($token)) {
                return [
                    'status' => false,
                    'message' => __(
                        'Token generation failed. This was likely caused by a timeout, check your server configuration, and try again.',
                        'visualcomposer'
                    ),
                ];
            }

            $url = $bundleHelper->getJsonDownloadUrl(['token' => $token]);
            $this->bundleJson = $bundleHelper->getRemoteBundleJson($url);
        }

        if ($this->bundleJson['actions']) {
            $actions = $this->call('parseJsonActions', [$this->bundleJson]);
            $bundleUpdateRequired = false;
            $hubElements = $elementsHelper->getElements();
            $hubAddons = $addonsHelper->getAddons();
            $customElements = $this->call('getCustomElements', [$tags]);

            //check if bundles are downloaded
            foreach ($tags as $tag) {
                if (!array_key_exists(str_replace('element/', '', $tag), $hubElements)
                    && !array_key_exists(str_replace('addon/', '', $tag), $hubAddons)
                ) {
                    // check we can download the missing bundles
                    if (!in_array($tag, $actions, true) && !in_array($tag, $customElements, true)) {
                        return [
                            'status' => false,
                            'message' => sprintf(
                                __(
                                    'It seems you don\'t have Visual Composer premium license to download elements from the Visual Composer Hub. Make sure to activate the premium license before importing or purchase it at %svisualcomposer.com/premium%s',
                                    'visualcomposer'
                                ),
                                sprintf(
                                    '<a href = "%s" target="_blank" rel="noopener noreferrer">',
                                    str_replace(
                                        'utm_content=button',
                                        'utm_content=text',
                                        vchelper('Utm')->premiumBtnUtm('wpdashboard')
                                    )
                                ),
                                '</a>'
                            ),
                        ];
                    }
                }
            }

            //if all elements available check if elements are in system, if not then add them to download
            $elementsToRegister = vchelper('DefaultElements')->all();
            foreach ($tags as $tag) {
                if (!$optionsHelper->get('hubAction:' . $tag)
                    && !in_array(
                        str_replace('element/', '', $tag),
                        $elementsToRegister
                    )
                    && !in_array(str_replace('addon/', '', $tag), $elementsToRegister)
                    && !in_array($tag, $customElements)
                ) {
                    $optionsHelper->set('hubAction:' . $tag, '0.0.1');
                    $bundleUpdateRequired = 1;
                }
            }

            if ($bundleUpdateRequired) {
                $optionsHelper->set('bundleUpdateRequired', $bundleUpdateRequired);
                $optionsHelper->setTransient('lastBundleUpdate', 0);

                $importProgress = __(
                    'It seems you are missing some elements included in the template. The elements will be downloaded automatically on the editor load.',
                    'visualcomposer'
                );
                $this->call('updateImportProgress', [$importProgress]);
            }
        } else {
            return [
                'status' => false,
                'message' => __(
                    'Failed to read JSON from the account. Check your connection and try again.',
                    'visualcomposer'
                ),
            ];
        }

        return ['status' => true];
    }

    /**
     * @param $tags
     * @param \VisualComposer\Helpers\Hub\Categories $hubCategories
     * @param \VisualComposer\Helpers\Hub\Elements $elementsHelper
     * @param \VisualComposer\Helpers\Hub\Addons $addonsHelper
     *
     * @return array
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function getCustomElements(
        $tags,
        HubCategories $hubCategories,
        Elements $elementsHelper,
        Addons $addonsHelper
    ) {
        $customElementExist = false;

        // Get all VC elements
        $allHubCategories = $hubCategories->getHubCategories();
        $allElements = [];
        foreach ($allHubCategories as $category) {
            foreach ($category['elements'] as $element) {
                $allElements[] = 'element/' . $element;
            }
        }

        // Get custom elements
        $customElements = [];
        $hubElements = $elementsHelper->getElements();
        $hubAddons = $addonsHelper->getAddons();
        foreach ($tags as $tag) {
            if (!array_key_exists(str_replace('element/', '', $tag), $hubElements)
                && !array_key_exists(str_replace('addon/', '', $tag), $hubAddons)
                && !in_array($tag, $allElements, true)
            ) {
                $customElements[] = $tag;
                $customElementExist = 1;
            }
        }

        // Custom element warning message
        if ($customElementExist) {
            $this->call(
                'updateImportProgress',
                [
                    __(
                        'Your export file contains custom elements. We have imported these elements but they will not appear until they are created.',
                        'visualcomposer'
                    ),
                ]
            );
        }

        return $customElements;
    }

    /**
     * @param \VisualComposer\Helpers\File $fileHelper
     * @param \VisualComposer\Helpers\Hub\Templates $hubTemplatesHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\WpMedia $wpMediaHelper
     *
     * @return array
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function import(
        File $fileHelper,
        Templates $hubTemplatesHelper,
        Options $optionsHelper,
        WpMedia $wpMediaHelper
    ) {
        $needUpdatePost = $optionsHelper->get('hubAction:updatePosts', []);

        $fileHelper->createDirectory(
            $hubTemplatesHelper->getTemplatesPath()
        );

        if (!empty($this->items)) {
            foreach ($this->items as $id => $item) {
                $bundle = $this->getTemplateBundle($id);

                if (file_exists($bundle)) {
                    if ($id > 0) {
                        $templateDir = $this->hubBundleHelper->getTempBundleFolder(
                            'templateImport/' . $id . '/templates/' . $item['templateId']
                        );
                    } else {
                        $templateDir = $this->hubBundleHelper->getTempBundleFolder(
                            'templateImport/' . 'templates/' . $item['templateId']
                        );
                    }

                    $fileHelper->createDirectory($hubTemplatesHelper->getTemplatesPath($item['templateId']));
                    $fileHelper->copyDirectory(
                        $templateDir,
                        $hubTemplatesHelper->getTemplatesPath($item['templateId'])
                    );

                    $bundle = json_decode(file_get_contents($bundle), true);

                    $this->newTemplateId = $newTemplateId = wp_insert_post(
                        [
                            'post_title' => $bundle['post']['post_title'],
                            'post_type' => $bundle['post']['post_type'],
                            'post_status' => 'publish',
                        ]
                    );

                    if ($newTemplateId) {
                        $optionsHelper->set('bundleUpdateRequired', 1);
                    } else {
                        return ['status' => false, 'message' => 'Failed to create the template.'];
                    }

                    $templateElements = $bundle['data'];
                    $elementsMedia = $wpMediaHelper->getTemplateElementMedia($templateElements);
                    $templateElements = $this->call(
                        'parseTemplateElements',
                        [
                            $elementsMedia,
                            $bundle,
                            $newTemplateId,
                            $templateElements,
                        ]
                    );
                    // Check if menu source is exist or not
                    $templateElements = $this->isMenuExist($templateElements);
                    $templateElements = $this->processDesignOptions($templateElements, $bundle, $newTemplateId);
                    $templateElements = json_decode(
                        str_replace(
                            '[publicPath]',
                            $hubTemplatesHelper->getTemplatesUrl($bundle['id']),
                            json_encode($templateElements)
                        ),
                        true
                    );

                    foreach ($bundle['postMeta'] as $metaKey => $meta) {
                        if (!in_array($metaKey, ['vcv-pageContent', 'vcvEditorTemplateElements'])) {
                            $metaValue = is_serialized($meta[0]) ? unserialize($meta[0]) : $meta[0];
                            update_post_meta($newTemplateId, $metaKey, $metaValue);
                        }
                    }

                    $pageContent = rawurlencode(
                        json_encode(
                            [
                                'elements' => $templateElements,
                            ]
                        )
                    );

                    if ($bundle['type'] === 'hub') {
                        update_post_meta($newTemplateId, 'vcvEditorTemplateElements', $templateElements);
                    } else {
                        update_post_meta($newTemplateId, VCV_PREFIX . 'pageContent', $pageContent);
                    }

                    $needUpdatePost[] = $newTemplateId;
                    $importProgress = sprintf(
                        __('Template - %s was successfully imported.', 'visualcomposer'),
                        '<strong>' . $bundle['post']['post_title'] . '</strong>'
                    );
                    $this->call('updateImportProgress', [$importProgress]);
                }
            }

            $this->call('removeTempContent');
            $optionsHelper->set('bundleUpdateRequired', 1);
            $optionsHelper->set('hubAction:updatePosts', array_unique($needUpdatePost));

            return ['status' => true];
        }

        return ['status' => false, 'message' => 'Missing import content.'];
    }

    /**
     * @param $json
     *
     * @return array
     */
    protected function parseJsonActions($json)
    {
        $actions = [];
        foreach ($json['actions'] as $item) {
            $actions[] = $item['action'];
        }

        return $actions;
    }

    /**
     * @param $templateElements
     *
     * @return mixed
     */
    protected function isMenuExist($templateElements)
    {
        foreach ($templateElements as $element) {
            if (isset($element['menuSource']) && !empty($element['menuSource'])) {
                $menusFromKey = get_terms(
                    [
                        'taxonomy' => 'nav_menu',
                        'slug' => $element['menuSource'],
                    ]
                );
                if (empty($menusFromKey)) {
                    $templateElements[ $element['id'] ]['menuSource'] = '';
                }
            }
        }

        return $templateElements;
    }

    /**
     * @param $mediaData
     * @param $template
     * @param $newTemplateId
     *
     * @return array|void
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function processWpMedia($mediaData, $template, $newTemplateId)
    {
        $newMedia = [];
        $newIds = [];

        $value = $mediaData['value'];
        $files = is_array($value) && isset($value['urls']) ? $value['urls'] : [$value];
        foreach ($files as $key => $file) {
            $newMediaData = $this->processSimple($file, $template, $newTemplateId);
            if (isset($newMediaData['newMedia'])) {
                $newMedia[] = $newMediaData['newMedia'];
            } elseif (isset($newMediaData)) {
                $newMedia[] = $newMediaData;
            } else {
                return;
            }

            if (isset($newMediaData['newIds'])) {
                $newIds[] = $newMediaData['newIds'];
            }
        }

        return ['newMedia' => $newMedia, 'newIds' => $newIds];
    }

    /**
     * @param $file
     * @param $template
     * @param $newTemplateId
     *
     * @return bool|mixed|string|void
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function processSimple($file, $template, $newTemplateId)
    {
        $fileHelper = vchelper('File');
        $hubTemplatesHelper = vchelper('HubTemplates');
        $urlHelper = vchelper('Url');
        $wpMedia = vchelper('WpMedia');
        $newIds = [];
        $default = false;

        $url = $this->findFileUrl($file);

        $templatesPath = $hubTemplatesHelper->getTemplatesPath($template['id']);

        if (!empty($url)) {
            if ($urlHelper->isUrl($url)) {
                return; //as we don't need to download external files
            }

            // File located locally
            if (strpos($url, '[publicPath]') !== false) {
                $url = str_replace('[publicPath]', '', $url);
                $localMediaPath = $templatesPath . '/' . ltrim($url, '\\/');
            } elseif (strpos($url, 'assets/elements/') !== false) {
                $localMediaPath = $templatesPath . '/' . ltrim($url, '\\/');
            } else {
                $localMediaPath = $url; // it is local file url (default file)
                $default = true;
            }

            if ($newTemplateId && !$default) {
                $attachment = $this->call('addToMedia', [$newTemplateId, $localMediaPath, $fileHelper]);

                if (isset($file['url'])) {
                    $file['url'] = $attachment['url'];
                }

                $newIds = $file['id'] = $attachment['id'];
                $file['url'] = $attachment['url'];
            }

            return ['newMedia' => $file, 'newIds' => $newIds];
        }

        //parse hub template default images
        if (is_array($file) && strpos($file[0], '[publicPath]') !== false && $wpMedia->checkIsImage($file[0])) {
            $path = str_replace('[publicPath]', '', $file[0]);
            $localMediaPath = $templatesPath . '/' . ltrim($path, '\\/');
            $wpUploadDir = wp_upload_dir();
            $url = str_replace($wpUploadDir['basedir'], $wpUploadDir['baseurl'], $localMediaPath);

            return ['newMedia' => $url, 'newIds' => []];
        }

        return false;
    }

    /**
     * @param $newTemplateId
     * @param $localMediaPath
     * @param File $fileHelper
     *
     * @return array
     */
    protected function addToMedia($newTemplateId, $localMediaPath, $fileHelper)
    {
        $fileType = wp_check_filetype(basename($localMediaPath), null);
        $wpUploadDir = wp_upload_dir();
        $fileHelper->copyFile($localMediaPath, $wpUploadDir['path'] . '/' . basename($localMediaPath));

        $attachment = [
            'guid' => $wpUploadDir['url'] . '/' . basename($localMediaPath),
            'post_mime_type' => $fileType['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($localMediaPath)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment = wp_insert_attachment(
            $attachment,
            $wpUploadDir['path'] . '/' . basename($localMediaPath),
            $newTemplateId
        );

        if (version_compare(get_bloginfo('version'), '5.3', '>=')) {
            wp_generate_attachment_metadata(
                $attachment,
                $wpUploadDir['path'] . '/' . basename($localMediaPath)
            );
        } else {
            wp_update_attachment_metadata(
                $attachment,
                wp_generate_attachment_metadata(
                    $attachment,
                    $wpUploadDir['path'] . '/' . basename($localMediaPath)
                )
            );
        }

        return ['id' => $attachment, 'url' => $wpUploadDir['url'] . '/' . basename($localMediaPath)];
    }

    /**
     * @param $templateElements
     * @param $template
     * @param $newTemplateId
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function processDesignOptions($templateElements, $template, $newTemplateId)
    {
        $this->call('updateImportProgress', [__('Processing design options...', 'visualcomposer')]);

        $arrayIterator = new \RecursiveArrayIterator($templateElements);
        $recursiveIterator = new \RecursiveIteratorIterator($arrayIterator, \RecursiveIteratorIterator::SELF_FIRST);

        $keys = [
            'videoEmbed',
            'image',
            'images',
        ];

        foreach ($recursiveIterator as $key => $value) {
            if (is_array($value) && in_array($key, $keys, true) && isset($value['urls'])) {
                $newValue = $this->processWpMedia(
                    ['value' => $value, 'key' => $key],
                    $template,
                    $newTemplateId
                );
                if ($newValue) {
                    $newMedia = [];
                    $newMedia['ids'] = $newValue['newIds'];
                    $newMedia['urls'] = $newValue['newMedia'];
                    // Get the current depth and traverse back up the tree, saving the modifications
                    $currentDepth = $recursiveIterator->getDepth();
                    for ($subDepth = $currentDepth; $subDepth >= 0; $subDepth--) {
                        // Get the current level iterator
                        $subIterator = $recursiveIterator->getSubIterator($subDepth);
                        // If we are on the level we want to change
                        // use the replacements ($value) other wise set the key to the parent iterators value
                        if ($subDepth === $currentDepth) {
                            $subIterator->offsetSet(
                                $subIterator->key(),
                                $newMedia
                            );
                        } else {
                            $subIterator->offsetSet(
                                $subIterator->key(),
                                $recursiveIterator->getSubIterator(
                                    ($subDepth + 1)
                                )->getArrayCopy()
                            );
                        }
                    }
                }
            }
        }

        return $recursiveIterator->getArrayCopy();
    }

    /**
     * @param $message
     *
     * @return bool
     */
    protected function errorMessage($message)
    {
        echo '<h2>' . __('An error has occurred.', 'visualcomposer') . '</h2> ';
        echo '<p class="description">' . $message . '</p>';
        return false;
    }

    /**
     * @param $file
     *
     * @return string|bool
     */
    protected function findFileUrl($file)
    {
        $url = false;
        if (is_string($file)) {
            $url = $file;
        } elseif (isset($file['full'])) {
            $url = $file['full'];
        } elseif (isset($file['url'])) {
            $url = $file['url'];
        }

        return $url;
    }

    /**
     * @param $elementsMedia
     * @param $bundle
     * @param $newTemplateId
     * @param $templateElements
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function parseTemplateElements($elementsMedia, $bundle, $newTemplateId, $templateElements)
    {
        $this->call('updateImportProgress', [__('Processing media...', 'visualcomposer')]);
        foreach ($elementsMedia as $element) {
            foreach ($element['media'] as $key => $media) {
                if (isset($media['complex']) && $media['complex']) {
                    $newMediaData = $this->processWpMedia(
                        $media,
                        $bundle,
                        $newTemplateId
                    );

                    $mediaData = $newMediaData['newMedia'];
                } else {
                    // it is simple url
                    $newMediaData = $this->processSimple(
                        $media,
                        $bundle,
                        $newTemplateId
                    );
                    $mediaData = $newMediaData['newMedia'];
                }
                if (!is_wp_error($mediaData) && $mediaData) {
                    if (isset($templateElements[ $element['elementId'] ][ $media['key'] ]['urls'])) {
                        $templateElements[ $element['elementId'] ][ $media['key'] ]['urls'] = $newMediaData['newMedia'];
                        $templateElements[ $element['elementId'] ][ $media['key'] ]['ids'] = $newMediaData['newIds'];
                    } elseif (!empty($mediaData[0])) {
                        $templateElements[ $element['elementId'] ][ $media['key'] ][ $key ] = $mediaData[0];
                    }
                }
            }
        }

        return $templateElements;
    }

    /**
     * Return the import progress
     *
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @param \VisualComposer\Helpers\Nonce $nonceHelper
     *
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @return array
     */
    protected function getImportProgress(Options $optionsHelper, Nonce $nonceHelper, Request $requestHelper)
    {
        if ($nonceHelper->verifyAdmin($requestHelper->input('vcv-nonce'))) {
            $importProgress = $optionsHelper->getTransient('import:progress');

            return json_encode($importProgress);
        }

        return json_encode(
            [
                'status' => false,
                'message' => __(
                    'Failed to validate nonce.',
                    'visualcomposer'
                ),
            ]
        );
    }

    /**
     * Update the import progress
     *
     * @param $progress
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return void
     */
    protected function updateImportProgress($progress, Options $optionsHelper)
    {
        $importProgress = $optionsHelper->getTransient('import:progress');
        if (!$importProgress) {
            $importProgress = ['statusMessages' => []];
        }

        $importProgress['statusMessages'][] = $progress;
        $optionsHelper->setTransient('import:progress', $importProgress, '600');
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function getTemplateBundle($id)
    {
        if ($id > 0 || !is_int($id)) {
            $bundle = $this->hubBundleHelper->getTempBundleFolder('templateImport/') . $id . '/bundle.json';
        } else {
            $bundle = $this->hubBundleHelper->getTempBundleFolder('templateImport/') . 'bundle.json';
        }

        return $bundle;
    }

    /**
     *
     */
    protected function approveImportTemplate()
    {
        $templates = $this->zipManifest['templates'];
        echo '<input type="hidden" name="vcv-file-id" value="' . $this->fileId . '" />';

        echo '<div class="vcv-start-import-inner">';
        $sprintf = sprintf(
            _n(
                'The import file contains <strong>%s</strong> template:',
                'The import file contains <strong>%s</strong> templates:',
                count($templates),
                'visualcomposer'
            ),
            count($templates)
        );
        echo '<p class="description">' . $sprintf . '</p>';
        echo '<ol>';
        foreach ($templates as $template) {
            echo '<li  class="description"<strong>' . $template['templateTitle'] . '</strong></li>';
        }
        echo '</ol>';
        echo '<p class="description">' . esc_html__('Do you want to proceed with the import?', 'visualcomposer') . '</p>';
        echo '</div>';
        echo '<div id="vcv-import-container"></div>';
    }

    /**
     * @param \VisualComposer\Helpers\File $fileHelper
     */
    protected function abortImport(File $fileHelper)
    {
        $this->hubBundleHelper->removeTempBundleFolder();
        $fileHelper->removeFile($this->file);
        wp_delete_attachment($this->fileId);
        wp_delete_post($this->newTemplateId, true);
    }

    /**
     * @param \VisualComposer\Helpers\File $fileHelper
     */
    protected function removeTempContent(File $fileHelper)
    {
        $this->hubBundleHelper->removeTempBundleFolder();
        $fileHelper->removeFile($this->file);
    }
}
