<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
?>
<style>
    .vcv-custom-site-popups-input input {
        width: 100%;
        max-width: 146px;
        border: 1px solid #d1d1d1;
        border-radius: 3px;
        box-sizing: border-box;
        box-shadow: none;
        color: #5c5b5b;
        display: inline-block;
        font-size: 12px;
        font-weight: 400;
        height: auto;
        line-height: 1.5;
        padding: 5px 14px;
        transition-duration: 0.2s;
        transition-property: all;
        transition-timing-function: ease-in-out;
        vertical-align: middle;
    }
    .vcv-custom-site-popups-input input:hover {
        border-color: #c4c4c4;
    }
    .vcv-custom-site-popups-input input:focus {
        border-color: #c4c4c4;
        outline: none;
        animation: vcv-ui-form-shadow-blink 0.4s ease-out;
        box-shadow: inset 0 6px 6px -6px rgba(0, 0, 0, 0.1);
    }
    .vcv-custom-site-popups-section .vcv-settings-popup-child-title {
        margin: 0;
        display: inline-block;
    }
    .vcv-dashboard-main .vcv-custom-site-popups-section .vcv-custom-site-popups-input td {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 311px;
    }

    @media screen and (min-width: 783px){
        .vcv-dashboard-main .vcv-custom-site-popups-section .vcv-custom-site-popups-input td {
            flex: 0 1 311px;
            max-width: initial;
        }
    }
</style>
<span class="vcv-settings-popup-child-title"><?php echo $title; ?></span>
<input type="number" min="0" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
