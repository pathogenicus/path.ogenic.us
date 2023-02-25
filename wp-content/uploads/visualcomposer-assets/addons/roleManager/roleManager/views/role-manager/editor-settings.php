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
    <h2><?php echo __('Settings (Frontend editor)', 'visualcomposer'); ?></h2>
    <div class="vcv-ui-settings-status-table">
        <p class="description"><?php echo __('Control access to features in settings in the Frontend Editor for the selected user role.', 'visualcomposer'); ?></p>
    </div>
    <div class="vcv-ui-settings-status-table">
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Page Options', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'page';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Popups', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'popup';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Element Lock', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'element_lock';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Page Design Options', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'page_design_options';
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
