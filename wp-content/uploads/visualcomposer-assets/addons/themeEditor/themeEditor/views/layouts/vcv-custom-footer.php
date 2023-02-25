<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// @codingStandardsIgnoreStart
/** @var string $sourceId */
/** @var string $part */
if (apply_filters('vcv:themeEditor:footer:enabled', true)) :
    $frontendHelper = vchelper('Frontend');

    do_action( 'vcv:themeEditor:before:footer' );
    ?>

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

    <?php
    do_action( 'vcv:themeEditor:after:footer' );

endif;
wp_footer(); ?>

</body>
</html>
