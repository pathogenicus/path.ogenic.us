<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * @var string $value
 * @var string $name
 */
// @codingStandardsIgnoreFile
?>

<style>
    .vcv-settings .vcv-settings-tab-content select.vcv-ui-form-dropdown,
    .vcv-settings .vcv-settings-tab-content input[type="number"],
    .vcv-settings .vcv-settings-tab-content input[type="text"] {
        max-width: 300px;
    }
    .vcv-settings .vcv-settings-tab-content select.vcv-ui-form-dropdown::placeholder,
    .vcv-settings .vcv-settings-tab-content input[type="number"]::placeholder,
    .vcv-settings .vcv-settings-tab-content input[type="text"]::placeholder {
        color: #9193A2;
    }
</style>
<div class="vcv-ui-form-group<?php echo isset($description) ? ' vcv-ui-form-switch-container-has-description' : ''; ?>" <?php echo isset($dataTitle) ? 'data-title="' . $dataTitle . '"' : ''; ?>>
    <div class="vcv-ui-form-input-container">
        <input type="text" placeholder="1140px" value="<?php echo esc_attr($value) !== '1140px' ? esc_attr($value) : ''; ?>" name="<?php echo esc_attr($name); ?>" />
    </div>
</div>
