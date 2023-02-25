<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// @codingStandardsIgnoreStart
/** @var string $sourceId */
/** @var string $part */
$frontendHelper = vchelper('Frontend');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
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
    ?>
</head>
<body <?php body_class(); ?>>
<?php
if (function_exists('wp_body_open')) {
    wp_body_open();
}

do_action( 'vcv:themeEditor:before:header' );

if ($headerEnabled) : ?>
    <header class="vcv-header" data-vcv-layout-zone="header">
        <?php
        echo $headerContent;
        ?>
    </header>
    <?php
endif;

do_action( 'vcv:themeEditor:after:header' );
