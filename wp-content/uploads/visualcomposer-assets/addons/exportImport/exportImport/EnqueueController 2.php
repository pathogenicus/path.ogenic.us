<?php

namespace exportImport\exportImport;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Url;

/**
 * Class EnqueueController
 * @package exportImport\exportImport
 */
class EnqueueController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * @var \exportImport\exportImport\ImportPage|mixed
     */
    protected $importPage;

    /**
     * EnqueueController constructor.
     *
     * @param \VisualComposer\Helpers\Request $requestHelper
     *
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    public function __construct(Request $requestHelper)
    {
        $this->importPage = vcapp('exportImport\exportImport\ImportPage');

        if (in_array(
            $this->importPage->getSlug(),
            [$requestHelper->input('page'), $requestHelper->input('import')],
            true
        )
            && $requestHelper->input('step') === '1'
        ) {
            /** @see \exportImport\exportImport\EnqueueController::adminHead */
            $this->wpAddAction('admin_print_scripts', 'adminHead', 0);

            /** @see \exportImport\exportImport\EnqueueController::enqueueAssets */
            $this->wpAddAction('admin_enqueue_scripts', 'enqueueAssets', 10);
            $this->addFilter('vcv:editors:internationalization:printLocalizations', '__return_true');
        }
    }

    /**
     * @param \VisualComposer\Helpers\Url $urlHelper
     * @param \VisualComposer\Helpers\Hub\Addons $hubAddonsHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     */
    protected function enqueueAssets(Url $urlHelper, Addons $hubAddonsHelper, Options $optionsHelper)
    {
        wp_deregister_script('vcv:settings:script');

        $addonVersion = $optionsHelper->get('hubAction:addon/exportImport', VCV_VERSION);
        wp_enqueue_script(
            'vcv:addon:exportImport:scripts:importProgress:base',
            $urlHelper->to('public/dist/wpbase.bundle.js'), // TODO: Use global!
            ['vcv:assets:vendor:script'],
            VCV_VERSION,
            true
        );

        $addonBundleUrl = $hubAddonsHelper->getAddonUrl('exportImport/public/dist/element.bundle.js');
        wp_register_script(
            'vcv:addon:exportImport:scripts:importProgress',
            $addonBundleUrl,
            ['vcv:assets:vendor:script'],
            $addonVersion . '-' . VCV_VERSION,
            true
        );

        wp_enqueue_script('vcv:addon:exportImport:scripts:importProgress');

        wp_enqueue_script('vcv:assets:runtime:script');
    }

    /**
     * @param \VisualComposer\Helpers\Url $urlHelper
     */
    protected function adminHead(Url $urlHelper)
    {
        echo '<script>';
        echo 'window.vcvAdminAjaxUrl = "' . $urlHelper->adminAjax() . '";';
        echo sprintf(
            'window.vcvBackToImportLink = "%s";',
            esc_js(
                admin_url('admin.php?page=' . rawurlencode($this->importPage->getSlug()))
            )
        );
        echo '</script>';
    }
}
