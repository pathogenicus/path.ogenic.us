<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// @codingStandardsIgnoreFile
?>
<p class="description">
    <?php
    esc_html_e(
        'Use these settings to change default font styles (ex. headings, paragraph). Visual Composer Font Manager works with the Visual Composer layouts and Visual Composer Starter theme.',
        'visualcomposer'
    ); ?>
</p>
<?php
$optionsHelper = vchelper('Options');
$fontManagerEnabled = (bool)$optionsHelper->get('fontManager', false);
$fontManagerEnabledDarkMode = (bool)$optionsHelper->get('fontManagerDarkMode', false);
$isDarkMode = (bool)$optionsHelper->get('isDarkMode', false);
?>

<script>
  window.VCV_FONT_MANAGER_ENABLED = Boolean(<?php echo (int)$fontManagerEnabled; ?>)
  window.VCV_FONT_DARK_MODE_ENABLED = Boolean(<?php echo (int)$fontManagerEnabledDarkMode; ?>)
  window.VCV_DASHBOARD_REACT_RENDER = true
  window.VCV_FONT_MANAGER_VALUES = <?php
  $currentValues = vcapp('\fontManager\fontManager\FontManagerEnqueueController')->getFontManagerData();
  echo json_encode($currentValues);
  ?>
</script>
<script>
    <?php
    echo vcaddonview(
        'font-manager/attributes.php',
        ['addon' => 'fontManager']
    );
    ?>
</script>
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
<script>
  (function () {
      <?php
      $fontFamilyValues = [];
      foreach ($currentValues as $device => $value) {
          // loop for each value as key => inner value and merge font_family+font_style
          foreach ($value as $innerValue) {
              if (isset($innerValue['font_family'], $innerValue['font_style'])) {
                  $fontFamilyValues[] = $innerValue['font_family'] . ':' . $innerValue['font_style'];
              }
          }
      }
      $fontFamilyValues = array_values(array_unique($fontFamilyValues));
      ?>
    window.VCV_GOOGLE_FONTS_CURRENT = <?php echo json_encode(
        $fontFamilyValues
    ); ?>;
  })()
</script>
<?php
echo vcaddonview(
    'font-manager/preview.php',
    ['addon' => 'fontManager', 'currentValues' => $currentValues]
);
?>
<div class="vcv-font-manager-wrapper<?php echo !$fontManagerEnabled ? ' vcv-font-manager-disabled' : ''; ?>"></div>
