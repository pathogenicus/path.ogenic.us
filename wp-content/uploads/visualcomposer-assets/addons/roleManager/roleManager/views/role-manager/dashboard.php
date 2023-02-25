<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
// @codingStandardsIgnoreStart
/**
 * @var array $stateCapabilities
 * @var string $role
 * @var string $part
 */
?>

<div class="vcv-settings-section vcv-settings_vcv-settings-gutenberg-editor-enabled">
    <h2><?php echo __('Dashboard', 'visualcomposer'); ?></h2>
    <div class="vcv-ui-settings-status-table">
        <p class="description"><?php echo __('Control access to Visual Composer features for the selected user role.', 'visualcomposer'); ?></p>
    </div>
    <div class="vcv-ui-settings-status-table">
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Theme Builder', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'addon_theme_builder';
                $capabilityKey = 'vcv_access_rules__' . $part . '_' . $index;
                $isEnabled = isset($stateCapabilities[ $capabilityKey ]) && $stateCapabilities[ $capabilityKey ];

                echo vcview(
                    'settings/fields/customtoggle',
                    [
                        'onTitle' => 'On',
                        'offTitle' => 'Off',
                        'value' => $index,
                        'name' => 'vcv-role-manager[' . $role . '][' . $part . '][]',
                        'title' => '',
                        'isEnabled' => $isEnabled,
                    ]
                ); ?>
            </div>
        </div>
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Global Templates', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'addon_global_templates';
                $capabilityKey = 'vcv_access_rules__' . $part . '_' . $index;
                $isEnabled = isset($stateCapabilities[ $capabilityKey ]) && $stateCapabilities[ $capabilityKey ];

                echo vcview(
                    'settings/fields/customtoggle',
                    [
                        'onTitle' => 'On',
                        'offTitle' => 'Off',
                        'value' => $index,
                        'name' => 'vcv-role-manager[' . $role . '][' . $part . '][]',
                        'title' => '',
                        'isEnabled' => $isEnabled,
                    ]
                ); ?>
            </div>
        </div>
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Popup Builder', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'addon_popup_builder';
                $capabilityKey = 'vcv_access_rules__' . $part . '_' . $index;
                $isEnabled = isset($stateCapabilities[ $capabilityKey ]) && $stateCapabilities[ $capabilityKey ];

                echo vcview(
                    'settings/fields/customtoggle',
                    [
                        'onTitle' => 'On',
                        'offTitle' => 'Off',
                        'value' => $index,
                        'name' => 'vcv-role-manager[' . $role . '][' . $part . '][]',
                        'title' => '',
                        'isEnabled' => $isEnabled,
                    ]
                ); ?>
            </div>
        </div>
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Export/Import', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'addon_export_import';
                $capabilityKey = 'vcv_access_rules__' . $part . '_' . $index;
                $isEnabled = isset($stateCapabilities[ $capabilityKey ]) && $stateCapabilities[ $capabilityKey ];

                echo vcview(
                    'settings/fields/customtoggle',
                    [
                        'onTitle' => 'On',
                        'offTitle' => 'Off',
                        'value' => $index,
                        'name' => 'vcv-role-manager[' . $role . '][' . $part . '][]',
                        'title' => '',
                        'isEnabled' => $isEnabled,
                    ]
                ); ?>
            </div>
        </div>
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description vcv-help">
                <?php echo __('Custom CSS & JavaScript', 'visualcomposer'); ?>
                <span class="vcv-help-tooltip-container">
                    <span class="vcv-help-tooltip-icon"></span>
                    <span class="vcv-help-tooltip">
                        <?php echo esc_html__('Control access to Custom CSS and Custom JavaScript feature for the selected user role in the Frontend Editor and Visual Composer Dashboard.', 'visualcomposer'); ?>
                    </span>
                </span>
            </div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'settings_custom_html';
                $capabilityKey = 'vcv_access_rules__' . $part . '_' . $index;
                $isEnabled = isset($stateCapabilities[ $capabilityKey ]) && $stateCapabilities[ $capabilityKey ];

                echo vcview(
                    'settings/fields/customtoggle',
                    [
                        'onTitle' => 'On',
                        'offTitle' => 'Off',
                        'value' => $index,
                        'name' => 'vcv-role-manager[' . $role . '][' . $part . '][]',
                        'title' => '',
                        'isEnabled' => $isEnabled,
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>
