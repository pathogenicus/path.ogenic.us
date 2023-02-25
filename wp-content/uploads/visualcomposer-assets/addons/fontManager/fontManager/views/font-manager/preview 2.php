<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/** @var array $currentValues */
// @codingStandardsIgnoreFile
?>
<style>
    <?php
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote', 'figcaption', 'button'] as $tag) {
            echo '#vcv-font-manager-preview ' . $tag . ' {
                line-height: var(--' . $tag . '-line-height);
                font-family: var(--' . $tag . '-font-family);
                font-weight: var(--' . $tag . '-font-weight);
                font-size: var(--' . $tag . '-font-size);
                letter-spacing: var(--' . $tag . '-letter-spacing);
                margin-top: var(--' . $tag . '-margin-top);
                color: var(--' . $tag . '-primary-color);
                font-style: var(--' . $tag . '-font-style);
                text-transform: var(--' . $tag . '-text-transform);
                margin-bottom: var(--' . $tag . '-margin-bottom);
            }';

            echo '#vcv-font-manager-preview ' . $tag . ' a {
                text-decoration: none;
                color: var(--' . $tag . '-link-color);
                border-bottom-width: 1px;
                border-bottom-style: solid;
                border-bottom-color: var(--' . $tag . '-link-border-color);
                transition: color .2s, border-bottom-color .2s;
            }';

            echo '#vcv-font-manager-preview ' . $tag . ' a:focus, #vcv-font-manager-preview ' . $tag . ' a:hover {
                color: var(--' . $tag . '-link-hover-color);
                border-bottom-color: var(--' . $tag . '-link-border-hover-color);
            }';
        }
        ?>
    #vcv-font-manager-preview ul > li::before {
      width: var(--bullet-bullet-width);
      height: var(--bullet-bullet-height);
      background-color: var(--bullet-bullet-color);
      border-radius: var(--bullet-border-radius);
    }

    #vcv-font-manager-preview ul > li {
      padding-left: calc(15px + var(--bullet-bullet-width));
    }

    #vcv-font-manager-preview blockquote {
      position: relative;
    }

    #vcv-font-manager-preview blockquote::before {
      content: "“";
      font-size: var(--blockquote-font-size);
      line-height: var(--blockquote-line-height);
    }

    #vcv-font-manager-preview blockquote::after {
      content: "”";
      font-size: var(--blockquote-font-size);
      line-height: var(--blockquote-line-height);
    }

    #vcv-font-manager-preview button {
      padding: 14px 38px;
      background: #2828C6;
      color: #fff;
      border: none;
      cursor: pointer;
    }
</style>
