<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
// @codingStandardsIgnoreStart
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    // Get stretch status
    $requestHelper = vchelper('Request');
    $stretched = (int)get_post_meta(get_the_ID(), '_vcv-page-template-stretch', true);
    if ($requestHelper->exists('vcv-template-stretched')) {
        $stretched = (int)$requestHelper->input('vcv-template-stretched');
    }

    $headerEnabled = apply_filters('vcv:themeEditor:header:enabled', true);
    if ($headerEnabled) {
        // Render <header> contents in buffer within <head> tag to correctly locate <head> styles
        ob_start();
        $originalId = get_the_ID();
        $previousDynamicContent = \VcvEnv::get('DYNAMIC_CONTENT_SOURCE_ID');
        if (empty($previousDynamicContent)) {
            \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $originalId);
        }
        do_action('vcv:themeEditor:header');
        \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $previousDynamicContent);
        $headerContent = ob_get_clean();
        // Manually trigger wp_enqueue_scripts for pending <header> items like globalTemplate (only CSS)
        // do_action('wp_enqueue_scripts') for all pending sourceId's (globalTemplate and etc)
        vcevent('vcv:assets:enqueue:css:list');
        vchelper('AssetsEnqueue')->addToEnqueueList($originalId);
    }
    wp_head();
    $customLayoutWidth = vchelper('Options')->get('custom-page-templates-section-layout-width', '1140');
    $customLayoutWidth = (int)rtrim($customLayoutWidth, 'px');

    if (empty($customLayoutWidth)) {
        $customLayoutWidth = '1140';
    }
    ?>
    <!-- Override the main container width styles -->
    <style>
        @media (min-width: 1200px) {
            div.vcv-content--boxed .entry-content > [data-vce-boxed-width="true"],
            div.vcv-content--boxed .vcv-layouts-html > [data-vce-boxed-width="true"],
            div.vcv-content--boxed .entry-content .vcv-layouts-html > [data-vce-boxed-width="true"],
            div.vcv-editor-theme-hf .vcv-layouts-html > [data-vce-boxed-width="true"],
            div.vcv-header > [data-vce-boxed-width="true"],
            div.vcv-footer > [data-vce-boxed-width="true"],
            div.vcv-content--boxed .entry-content > * > [data-vce-full-width="true"]:not([data-vce-stretch-content="true"]) > [data-vce-element-content="true"],
            div.vcv-content--boxed .vcv-layouts-html > * > [data-vce-full-width="true"]:not([data-vce-stretch-content="true"]) > [data-vce-element-content="true"],
            div.vcv-editor-theme-hf .vcv-layouts-html > * > [data-vce-full-width="true"]:not([data-vce-stretch-content="true"]) > [data-vce-element-content="true"],
            div.vcv-header > * > [data-vce-full-width="true"]:not([data-vce-stretch-content="true"]) > [data-vce-element-content="true"],
            div.vcv-footer > * > [data-vce-full-width="true"]:not([data-vce-stretch-content="true"]) > [data-vce-element-content="true"] {
                max-width: <?php echo $customLayoutWidth . 'px' ?> !important;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php
if (function_exists('wp_body_open')) {
    wp_body_open();
}
?>
<div class="vcv-layout-wrapper">
    <?php if ($headerEnabled) : ?>
        <header class="vcv-header" data-vcv-layout-zone="header">
            <?php
            echo $headerContent;
            ?>
        </header>
    <?php endif; ?>
    <section class="vcv-content vcv-content--header-footer <?php echo $stretched ? '' : 'vcv-content--boxed' ?>">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php
        endwhile;
        ?>
    </section>
    <?php if (apply_filters('vcv:themeEditor:footer:enabled', true)) : ?>
        <footer class="vcv-footer" data-vcv-layout-zone="footer">
            <?php
            $originalId = get_the_ID();
            $previousDynamicContent = \VcvEnv::get('DYNAMIC_CONTENT_SOURCE_ID');
            if (empty($previousDynamicContent)) {
                \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $originalId);
            }
            do_action('vcv:themeEditor:footer');
            \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $previousDynamicContent);
            ?>
        </footer>
    <?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
