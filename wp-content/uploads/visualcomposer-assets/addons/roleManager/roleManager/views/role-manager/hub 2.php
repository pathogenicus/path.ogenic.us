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
    <h2><?php echo __('Visual Composer Hub', 'visualcomposer'); ?></h2>
    <div class="vcv-ui-settings-status-table">
        <p class="description"><?php echo __('Control access to sections in the Visual Composer Hub for the selected user role.', 'visualcomposer'); ?></p>
    </div>
    <div class="vcv-ui-settings-status-table">
        <div class="vcv-ui-settings-status-table-row">
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Elements, Templates, Blocks', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'elements_templates_blocks';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Headers, Footers, Sidebars', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'headers_footers_sidebars';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Addons', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'addons';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('Stock Images', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'unsplash';
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
            <div class="vcv-ui-settings-status-table-title description"><?php echo __('GIPHY', 'visualcomposer'); ?></div>
            <div class="vcv-ui-settings-status-table-content">
                <?php
                $index = 'giphy';
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

