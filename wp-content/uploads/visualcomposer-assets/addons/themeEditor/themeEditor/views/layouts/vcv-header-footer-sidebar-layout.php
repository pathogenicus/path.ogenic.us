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
    wp_head();
?>
</head>
<body <?php body_class(); ?>>
<?php
if (function_exists('wp_body_open')) {
    wp_body_open();
}
?>
<div class="vcv-layout-wrapper">
    <?php if (apply_filters('vcv:themeEditor:header:enabled', true)) : ?>
        <header class="vcv-header" data-vcv-layout-zone="header">
            <?php
            echo $headerContent;
            ?>
        </header>
    <?php endif; ?>
    <section class="vcv-content">
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
    <?php if (apply_filters('vcv:themeEditor:sidebar:enabled', true)) : ?>
        <aside class="vcv-sidebar" data-vcv-layout-zone="sidebar">
            <?php
            $originalId = get_the_ID();
            $previousDynamicContent = \VcvEnv::get('DYNAMIC_CONTENT_SOURCE_ID');
            if (empty($previousDynamicContent)) {
                \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $originalId);
            }
            do_action('vcv:themeEditor:sidebar');
            \VcvEnv::set('DYNAMIC_CONTENT_SOURCE_ID', $previousDynamicContent);
            ?>
        </aside>
    <?php endif; ?>
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
