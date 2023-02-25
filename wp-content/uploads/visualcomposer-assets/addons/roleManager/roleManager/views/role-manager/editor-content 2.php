<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
// @codingStandardsIgnoreStart
/**
 * @var bool $stateValue
 * @var string $role
 * @var string $part
 */
?>

<div class="vcv-settings-section vcv-settings_vcv-settings-gutenberg-editor-enabled">
    <h2><?php echo __('Add Content Window', 'visualcomposer'); ?></h2>
    <div class="vcv-ui-settings-status-table">
        <p class="description"><?php echo __('Control access to features in the Add Content window in the Frontend Editor for the selected user role.', 'visualcomposer'); ?></p>
    </div>
    <div class="vcv-ui-settings-status-table">
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Adding Elements', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'element_add';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Adding Templates', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'template_add';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Adding Blocks', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'block_add';
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
                <?php echo __('Managing Element Presets', 'visualcomposer'); ?>
                <span class="vcv-help-tooltip-container">
                    <span class="vcv-help-tooltip-icon"></span>
                    <span class="vcv-help-tooltip">
                        <?php echo esc_html__('Make sure to enable “Adding Elements” to allow managing element presets.', 'visualcomposer'); ?>
                    </span>
                </span>
            </div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'presets_management';
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
                <?php echo __('Managing Templates', 'visualcomposer'); ?>
                <span class="vcv-help-tooltip-container">
                    <span class="vcv-help-tooltip-icon"></span>
                    <span class="vcv-help-tooltip">
                        <?php echo esc_html__('Make sure to enable “Adding Templates” to allow managing templates.', 'visualcomposer'); ?>
                    </span>
                </span>
            </div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'user_templates_management';
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
                <?php echo __('Managing Blocks', 'visualcomposer'); ?>
                <span class="vcv-help-tooltip-container">
                    <span class="vcv-help-tooltip-icon"></span>
                    <span class="vcv-help-tooltip">
                        <?php echo esc_html__('Make sure to enable “Adding Blocks” to allow managing blocks.', 'visualcomposer'); ?>
                    </span>
                </span>
            </div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'user_blocks_management';
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
