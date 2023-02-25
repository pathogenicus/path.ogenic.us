<?php

namespace fontManager\fontManager;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class FontManagerEnqueueController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    protected static $fontFamilies = [];

    protected static $fontManagerCache = [];

    public function __construct(Options $optionsHelper)
    {
        $this->addFilter('vcv:fontManager:css:styles', 'addBlockquoteStyles');
        $this->addFilter('vcv:fontManager:css:styles', 'addGfontStyles');
        $this->addFilter('vcv:fontManager:css:styles', 'addBulletStyles');
        $this->wpAddAction('wp_enqueue_scripts', 'loadFonts', 1000);

        $isFontManagerEnabled = $optionsHelper->get('fontManager', false);
        if ($isFontManagerEnabled) {
            $this->wpAddFilter('body_class', 'addBodyClass');
            $this->addFilter('vcv:assets:source:main:styles', 'setElementStylesImportant');
            $this->wpAddAction('wp_enqueue_scripts', 'enqueueStyles', 100);
        }
    }

    public function getFontManagerData()
    {
        // check if cache exists then return it
        if (!empty(self::$fontManagerCache)) {
            return self::$fontManagerCache;
        }

        $defaults = $this->getFontManagerDefaults();
        $optionsHelper = vchelper('Options');
        $fontManager = $optionsHelper->get('fontManager', false);

        if ($fontManager) {
            // check for BC
            if (isset($fontManager['h1'])) {
                $fontManager = ['all' => array_merge($defaults, $fontManager)];
            } else {
                // only all screen version has all defaults attributes all others has only that user set
                $fontManager['all'] =
                    array_merge($defaults, isset($fontManager['all']) ? $fontManager['all'] : []);
            }
        } else {
            $fontManager = ['all' => $defaults];
        }

        // set the cache
        self::$fontManagerCache = $fontManager;

        return self::$fontManagerCache;
    }

    public function getFontManagerDefaults()
    {
        $defaults = [
            'h1' => [
                'font_family' => 'Montserrat',
                'font_size' => '36px',
                'line_height' => '50px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '15px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'h2' => [
                'font_family' => 'Montserrat',
                'font_size' => '28px',
                'line_height' => '42px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '13px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'h3' => [
                'font_family' => 'Montserrat',
                'font_size' => '24px',
                'line_height' => '36px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '13px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'h4' => [
                'font_family' => 'Montserrat',
                'font_size' => '20px',
                'line_height' => '30px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '10px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'h5' => [
                'font_family' => 'Montserrat',
                'font_size' => '18px',
                'line_height' => '26px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '8px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'h6' => [
                'font_family' => 'Montserrat',
                'font_size' => '16px',
                'line_height' => '22px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#55555F',
                'font_style' => '700normal',
                'text_transform' => 'none',
                'margin_bottom' => '6px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'p' => [
                'font_family' => 'Roboto',
                'font_size' => '16px',
                'font_style' => '500normal',
                'line_height' => '26px',
                'letter_spacing' => '0px',
                'margin_top' => '0',
                'primary_color' => '#6C6C72',
                'text_transform' => 'none',
                'margin_bottom' => '15px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'blockquote' => [
                'font_family' => 'Montserrat',
                'font_size' => '18px',
                'line_height' => '26px',
                'letter_spacing' => '0px',
                'margin_top' => '20px',
                'primary_color' => '#55555F',
                'font_style' => '600italic',
                'text_transform' => 'none',
                'margin_bottom' => '20px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'figcaption' => [
                'font_family' => 'Roboto',
                'font_size' => '14px',
                'line_height' => '24px',
                'letter_spacing' => '0.17px',
                'margin_top' => '8px',
                'primary_color' => '#777777',
                'font_style' => '500italic',
                'text_transform' => 'none',
                'margin_bottom' => '16px',
                'link_color' => '#2828C6',
                'link_hover_color' => '#23238e',
                'link_border_color' => 0,
                'link_border_hover_color' => 1,
            ],
            'bullet' => [
                'bullet_style' => 'line',
                'line_width' => '12px',
                'line_height' => '2px',
                'circle_width' => '6px',
                'circle_height' => '6px',
                'square_width' => '6px',
                'square_height' => '6px',
                'bullet_color' => '#557cbf',
                'padding_left' => '0px',
                'space' => '0px',
            ],
            'button' => [
                'font_family' => 'Montserrat',
                'font_size' => '12px',
                'font_style' => '700normal',
                'line_height' => '22px',
                'letter_spacing' => '1px',
                'text_transform' => 'uppercase',
            ],
            'menu' => [
                'font_family' => 'Montserrat',
                'letter_spacing' => '0px',
                'font_style' => '700normal',
            ],
            'submenu' => [
                'font_family' => 'Montserrat',
                'letter_spacing' => '0px',
                'font_style' => '700normal',
            ],
        ];

        return $defaults;
    }

    protected function addBodyClass($classes)
    {
        $classes[] = 'vcwb-font-manager';

        return $classes;
    }

    protected function setElementStylesImportant($css)
    {
        $css = preg_replace(
            [
                '/({font-family:)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({font-size:)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({color:#)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({font-weight:)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({font-style:)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({line-height:)((?:[^}](?!(?:\!important|;)))+)}/',
                '/({letter-spacing:)((?:[^}](?!(?:\!important|;)))+)}/',
            ],
            '$1$2 !important}',
            $css
        );

        return $css;
    }

    protected function enqueueStyles()
    {
        $fontManager = $this->getFontManagerData();
        wp_register_style(VCV_PREFIX . 'fontManager:css', false);
        wp_enqueue_style(VCV_PREFIX . 'fontManager:css');
        wp_add_inline_style(VCV_PREFIX . 'fontManager:css', $this->generateStylesCss($fontManager));
    }

    protected function loadFonts()
    {
        if (empty(self::$fontFamilies)) {
            return;
        }

        foreach (self::$fontFamilies as $family => $styles) {
            $styles = array_unique($styles);
            $stylesKey = implode(',', $styles);
            $enqueueKey = 'vcv:fontManager:font:' . $family . ':' . $stylesKey;
            wp_register_style($enqueueKey, 'https://fonts.googleapis.com/css?family=' . $family . ':' . $stylesKey);
            wp_enqueue_style($enqueueKey);
        }
    }

    protected function generateStylesCss($fontManager)
    {
        $hash = get_stylesheet();
        $optionsHelper = vchelper('Options');
        $stylesCache = $optionsHelper->getTransient('fontManager:cache:' . $hash);
        if (!empty($stylesCache)) {
            self::$fontFamilies = $optionsHelper->getTransient('fontManager:fontFamily:cache:' . $hash);

            return $stylesCache;
        }

        $commonClasses = $this->getCommonClasses();

        $styles = $this->getVariableStyles($fontManager);
        $styles = $this->getBaseStyles($fontManager, $styles, $commonClasses);

        // Minify
        $minStyles = preg_replace('/\n|\s+/', ' ', $styles);

        // Save Cache
        $optionsHelper->setTransient('fontManager:cache:' . $hash, $minStyles, DAY_IN_SECONDS);
        $optionsHelper->setTransient('fontManager:fontFamily:cache:' . $hash, self::$fontFamilies, DAY_IN_SECONDS);

        return $minStyles;
    }

    /**
     * @param $fontManager
     * @param $styles
     * @param array $commonClasses
     *
     * @return mixed|string
     */
    protected function getBaseStyles($fontManager, $styles, array $commonClasses)
    {
        foreach ($fontManager as $deviceData) {
            foreach ($deviceData as $tag => $values) {
                if (empty($commonClasses[ $tag ])) {
                    continue; // no styles available
                }
                if (is_array($commonClasses[ $tag ])) {
                    // we have split logic for typography and margins
                    if (isset($commonClasses[ $tag ]['typography'])) {
                        $styles .= $commonClasses[ $tag ]['typography'];
                        $styles .= ' {';
                        $styles .= $this->addTypographyVariables($tag);
                        $styles .= '}';
                    }
                    if (isset($commonClasses[ $tag ]['margins'])) {
                        $styles .= $commonClasses[ $tag ]['margins'];
                        $styles .= ' {';
                        $styles .= $this->addMarginVariables($tag);
                        $styles .= '}';
                    }
                } else {
                    $styles .= $commonClasses[ $tag ];
                    $styles .= ' {';
                    $styles .= $this->addTypographyVariables($tag);
                    $styles .= $this->addMarginVariables($tag);
                    $styles .= '}';
                }

                $styles = $this->addLinksToBaseStyle($styles, $tag, $commonClasses, $values);
            }
        }

        return vcfilter('vcv:fontManager:css:styles', $styles);
    }

    /**
     * Add links to base styles.
     *
     * @param string $styles
     * @param string $tag
     * @param array $commonClasses
     * @param array $values
     *
     * @return string
     */
    protected function addLinksToBaseStyle($styles, $tag, $commonClasses, $values)
    {
        $styles = $this->addUnderlineLinkStyle($styles, $tag, $commonClasses, $values);
        $styles = $this->addUnderlineHoverLinkStyle($styles, $tag, $commonClasses, $values);

        return $styles;
    }

    /**
     * Add styles for links underline.
     *
     * @param string $styles
     * @param string $tag
     * @param array $commonClasses
     * @param array $values
     *
     * @return string
     */
    protected function addUnderlineLinkStyle($styles, $tag, $commonClasses, $values)
    {
        $aKey = $tag . '.a';
        if (empty($commonClasses[ $aKey ]) || !isset($values['link_border_color'])) {
            return $styles;
        }

        $styles .= $commonClasses[ $aKey ];

        $isLinkUnderline =
            $values['link_border_color'] !== 'false' &&
            $values['link_border_color'] !== '0' &&
            $values['link_border_color'] !== 0 &&
            $values['link_border_color'] !== false
        ;

        if ($isLinkUnderline) {
            $styles .= ' {
                text-decoration: none;
                color: var(--' . $tag . '-link-color);
                border-bottom-width: 1px;
                border-bottom-style: solid;
                border-bottom-color: var(--' . $tag . '-link-border-color);
                transition: color .2s, border .2s;
            }';
        } else {
            $styles .= ' {
                text-decoration: none;
                color: var(--' . $tag . '-link-color);
                border: none;
                box-shadow: none;
                transition: color .2s;
            }';
        }

        return $styles;
    }

    /**
     * Add styles for hover links underline.
     *
     * @param string $styles
     * @param string $tag
     * @param array $commonClasses
     * @param array $values
     *
     * @return string
     */
    protected function addUnderlineHoverLinkStyle($styles, $tag, $commonClasses, $values)
    {
        $aHoverKey = $tag . '.a:hover';
        if (empty($commonClasses[ $aHoverKey ]) || !isset($values['link_border_hover_color'])) {
            return $styles;
        }

        $styles .= $commonClasses[ $aHoverKey ];

        $isLinkHoverUnderline =
            $values['link_border_hover_color'] !== 'false' &&
            $values['link_border_hover_color'] !== '0' &&
            $values['link_border_hover_color'] !== 0 &&
            $values['link_border_hover_color'] !== false
        ;

        if ($isLinkHoverUnderline) {
            $styles .= ' {
                color: var(--' . $tag . '-link-hover-color);
                border-bottom: 1px solid var(--' . $tag . '-link-border-hover-color);
            }';
        } else {
            $styles .= ' {
                color: var(--' . $tag . '-link-hover-color);
                border-color: transparent;
            }';
        }

        return $styles;
    }



    protected function addBlockquoteStyles($styles)
    {
        $styles .= '  .vcwb.vcwb-font-manager blockquote {
                position: relative;
                border: none;
            }';

        return $styles;
    }

    protected function addGfontStyles($styles)
    {
        $styles .= '/* gfonts */
            .vce-google-fonts-heading-link, .vce-google-fonts-heading-link:hover, .vce-google-fonts-heading-link:focus, .vce-google-fonts-heading-link:visited {
    border: none !important;
    text-decoration: inherit !important;
    color: inherit !important;
    background: inherit !important;
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    font-style: inherit !important;
    letter-spacing: inherit !important;
    line-height: inherit !important;
    cursor: pointer !important;
}
            ';

        return $styles;
    }

    protected function addBulletStyles($styles)
    {
        $styles .= '
.vcwb-font-manager ul:not([class]):not([id]) {
    list-style: none;
}
.vcwb-font-manager ul:not([class]):not([id]) > li {
    margin-bottom: 0;
    position: relative;
    list-style: none;
    line-height: var(--p-line-height);
    border: none;
    padding-left: calc(15px + var(--bullet-padding-left) + var(--bullet-width));
}
.vcwb-font-manager ul:not([class]):not([id])  > li:not(:last-child) {
    margin-bottom: var(--bullet-space);
}
.vcwb-font-manager ul:not([class]):not([id]) > li:before {
    content: \'\';
    position: absolute;
    top: calc((var(--p-line-height) / 2) - (var(--bullet-height) / 2));
    left: calc(5px + var(--bullet-padding-left));
    margin: auto;
    width: var(--bullet-width);
    height: var(--bullet-height);
    background-color: var(--bullet-color);
    border-radius: var(--bullet-radius);
}
.vcwb-font-manager .vce-woocommerce-wrapper ul > li{padding:0;line-height:initial;}
.vcwb-font-manager .vce-woocommerce-wrapper ul > li:before {display:none}
            ';

        return $styles;
    }

    /**
     * @param $fontManager
     *
     * @return string
     */
    protected function getVariableStyles($fontManager)
    {
        $styles = '';

        // output like before
        $deviceData = $fontManager['all'];
        $styles .= $this->getVariablesStylesLoop($deviceData);

        unset($fontManager['all']);

        // loop thru all devices and add media query
        foreach ($fontManager as $device => $deviceData) {
            // wrap with media query
            $media = [
                'xs' => '@media (max-width: 575px)',
                'sm' => '@media (min-width: 576px) and (max-width: 767px)',
                'md' => '@media (min-width: 768px) and (max-width: 991px)',
                'lg' => '@media (min-width: 992px) and (max-width: 1199px)',
                'xl' => '@media (min-width: 1200px)',
            ];
            $styles .= $media[ $device ] . ' {';
            $styles .= $this->getVariablesStylesLoop($deviceData);
            $styles .= '}';
        }

        return $styles;
    }

    protected function addBulletVariables($variableStyles, $values)
    {
        if (isset($values['bullet_style'])) {
            $style = $values['bullet_style'];
            if (isset($values[ $style . '_' . 'width' ])) {
                $variableStyles .= '--bullet-width: ' . $values[ $style . '_' . 'width' ] . ';';
            }
            if (isset($values[ $style . '_' . 'height' ])) {
                $variableStyles .= '--bullet-height: ' . $values[ $style . '_' . 'height' ] . ';';
            }
            if ($style === 'circle') {
                $variableStyles .= '--bullet-radius: 50%;';
            } else {
                $variableStyles .= '--bullet-radius: 0;';
            }
        }
        if (isset($values['bullet_color'])) {
            $variableStyles .= '--bullet-color: ' . $values['bullet_color'] . ';';
        }
        if (isset($values['padding_left'])) {
            $variableStyles .= '--bullet-padding-left: ' . $values['padding_left'] . ';';
        }
        if (isset($values['space'])) {
            $variableStyles .= '--bullet-space: ' . $values['space'] . ';';
        }

        return $variableStyles;
    }

    /**
     * @param $tag
     *
     * @return string
     */
    protected function addTypographyVariables($tag)
    {
        $styles = 'line-height: var(--' . $tag . '-line-height);
              font-family: var(--' . $tag . '-font-family);
              font-weight: var(--' . $tag . '-font-weight);
              font-size: var(--' . $tag . '-font-size);
              letter-spacing: var(--' . $tag . '-letter-spacing);
              font-style: var(--' . $tag . '-font-style);
              text-transform: var(--' . $tag . '-text-transform);';

        if ($tag !== 'button') {
            $styles .= 'color: var(--' . $tag . '-primary-color);';
        }

        return $styles;
    }

    /**
     * @param $tag
     *
     * @return string
     */
    protected function addMarginVariables($tag)
    {
        return '
            margin-top: var(--' . $tag . '-margin-top);
            margin-bottom: var(--' . $tag . '-margin-bottom);';
    }

    /**
     * @param $values
     * @param $tag
     *
     * @return string
     */
    protected function addLinkVariables($values, $tag)
    {
        $color = $values['link_color'];
        $hoverColor = $values['link_hover_color'];
        if (empty($values['link_border_color'])) {
            $color = 'transparent';
        }
        if (empty($values['link_border_hover_color'])) {
            $hoverColor = 'transparent';
        }
        $variableStyles = '--' . $tag . '-link-border-color: ' . $color . ';';
        $variableStyles .= '--' . $tag . '-link-border-hover-color: ' . $hoverColor . ';';

        return $variableStyles;
    }

    /**
     * @param $values
     * @param $tag
     *
     * @return string
     */
    protected function addTextTransformVariable($values, $tag)
    {
        $variableStyles = '';
        if (isset($values['text_transform'])) {
            $variableStyles .= '--' . $tag . '-text-transform: ' . $values['text_transform'] . ';';
        } elseif (isset($values['font_transform'])) {
            $variableStyles .= '--' . $tag . '-text-transform: ' . $values['font_transform'] . ';';
        }

        return $variableStyles;
    }

    /**
     * @param $deviceData
     *
     * @return string
     */
    protected function getVariablesStylesLoop($deviceData)
    {
        $variableStyles = '.vcwb.vcwb-font-manager {';

        foreach ($deviceData as $tag => $values) {
            if ($tag === 'bullet') {
                $variableStyles = $this->addBulletVariables($variableStyles, $values);
                continue;
            }

            $this->setVariablesFontFamiliesValues($values);

            $styleList = [
                'line-height' => 'line_height',
                'font-family' => 'font_family',
                'font-size' => 'font_size',
                'letter-spacing' => 'letter_spacing',
                'primary-color' => 'primary_color',
                'link-color' => 'link_color',
                'link-hover-color' => 'link_hover_color',
                'margin-top' => 'margin_top',
                'margin-bottom' => 'margin_bottom',
            ];

            foreach ($styleList as $styleName => $styleIndex) {
                if (!empty($values[ $styleIndex ])) {
                    $variableStyles .= '--' . $tag . '-' . $styleName . ': ' . $values[ $styleIndex ] . ';';
                }
            }

            if (isset($values['font_style'])) {
                preg_match('/[a-z]+$/', $values['font_style'], $style);
                preg_match('/\d+/', $values['font_style'], $weight);

                if (isset($style[0]) && $style[0] === 'regular') {
                    $style[0] = 'normal';
                }
                $variableStyles .= '--' . $tag . '-font-style: ' . (empty($style[0]) ? 'normal' : $style[0]) . ';';
                $variableStyles .= '--' . $tag . '-font-weight: ' . (empty($weight[0]) ? 400 : $weight[0]) . ';';
            }

            // Add BC for font_transform -> text_transform
            $variableStyles .= $this->addTextTransformVariable($values, $tag);

            // Custom styles for link border color logic
            if (isset($values['link_border_color'], $values['link_border_hover_color'])) {
                $variableStyles .= $this->addLinkVariables($values, $tag);
            }
        }
        $variableStyles .= '}';

        return $variableStyles;
    }

    /**
     * Set values for fontFamilies attribute.
     *
     * @param array $values
     */
    protected function setVariablesFontFamiliesValues($values)
    {
        if (isset($values['font_family'])) {
            if (!isset(self::$fontFamilies[ $values['font_family'] ])) {
                self::$fontFamilies[ $values['font_family'] ] = [];
            }

            if (isset($values['font_style'])) {
                self::$fontFamilies[ $values['font_family'] ][] = $values['font_style'];
            }
        }
    }

    /**
     * Get font manager common classes.
     *
     * @return array
     */
    protected function getCommonClasses()
    {
        $commonClasses = [
            'h1' => '
            h1,
             .vcwb.vcwb-font-manager .h1,
             .vcwb.vcwb-font-manager .h1.main-title,
             .vcwb.vcwb-font-manager .h1.entry-title,
             .vcwb.vcwb-font-manager .comments-area h1#reply-title,
             .vcwb.vcwb-font-manager h1.comments-title,
             .vcwb.vcwb-font-manager .entry-content h1,
             .vcwb.vcwb-font-manager #header h1,
             .vcwb.vcwb-font-manager #footer h1,
             .vcwb.vcwb-font-manager #content h1,
             .vcwb.vcwb-font-manager h1.entry-title',
            'h1.a' => '
            h1 a,
             .vcwb.vcwb-font-manager .h1 a,
             .vcwb.vcwb-font-manager #header h1 a,
             .vcwb.vcwb-font-manager #footer h1 a,
             .vcwb.vcwb-font-manager #content h1 a,
             .vcwb.vcwb-font-manager .h1.main-title a,
             .vcwb.vcwb-font-manager .h1.entry-title a,
             .vcwb.vcwb-font-manager h1.comments-title a,
             .vcwb.vcwb-font-manager .entry-content h1 a,
             .vcwb.vcwb-font-manager h1.entry-title a',
            'h1.a:hover' => '
            h1 a:hover,
            h1 a:focus,
             .vcwb.vcwb-font-manager .h1 a:hover,
             .vcwb.vcwb-font-manager #header h1 a:hover,
             .vcwb.vcwb-font-manager #header h1 a:focus,
             .vcwb.vcwb-font-manager #footer h1 a:hover,
             .vcwb.vcwb-font-manager #footer h1 a:focus,
             .vcwb.vcwb-font-manager #content h1 a:hover,
             .vcwb.vcwb-font-manager #content h1 a:focus,
             .vcwb.vcwb-font-manager .h1 a:focus,
             .vcwb.vcwb-font-manager .h1.main-title a:hover,
             .vcwb.vcwb-font-manager .h1.main-title a:focus,
             .vcwb.vcwb-font-manager .h1.entry-title a:hover,
             .vcwb.vcwb-font-manager .h1.entry-title a:focus,
             .vcwb.vcwb-font-manager h1.comments-title a:hover,
             .vcwb.vcwb-font-manager h1.comments-title a:focus,
             .vcwb.vcwb-font-manager .entry-content h1 a:hover,
             .vcwb.vcwb-font-manager .entry-content h1 a:focus,
             .vcwb.vcwb-font-manager h1.entry-title a:hover,
             .vcwb.vcwb-font-manager h1.entry-title a:focus',

            'h2' => '
            h2,
             .vcwb.vcwb-font-manager .h2,
             .vcwb.vcwb-font-manager .entry-content h2,
             .vcwb.vcwb-font-manager #header h2,
             .vcwb.vcwb-font-manager #footer h2,
             .vcwb.vcwb-font-manager #content h2,
             .vcwb.vcwb-font-manager h2.reply-title,
             .vcwb.vcwb-font-manager .comments-area h2#reply-title,
             .vcwb.vcwb-font-manager h2.comments-title,
             .vcwb.vcwb-font-manager h2.entry-title',
            'h2.a' => '
            h2 a,
             .vcwb.vcwb-font-manager .h2 a,
             .vcwb.vcwb-font-manager .entry-content h2 a,
             .vcwb.vcwb-font-manager #header h2 a,
             .vcwb.vcwb-font-manager #footer h2 a,
             .vcwb.vcwb-font-manager #content h2 a,
             .vcwb.vcwb-font-manager h2.reply-title a,
             .vcwb.vcwb-font-manager h2.comments-title a,
             .vcwb.vcwb-font-manager h2.entry-title a',
            'h2.a:hover' => '
            h2 a:hover,
            h2 a:focus,
             .vcwb.vcwb-font-manager .h2 a:hover,
             .vcwb.vcwb-font-manager .h2 a:focus,
             .vcwb.vcwb-font-manager .entry-content h2 a:hover,
             .vcwb.vcwb-font-manager .entry-content h2 a:focus,
             .vcwb.vcwb-font-manager #header h2 a:hover,
             .vcwb.vcwb-font-manager #header h2 a:focus,
             .vcwb.vcwb-font-manager #footer h2 a:hover,
             .vcwb.vcwb-font-manager #footer h2 a:focus,
             .vcwb.vcwb-font-manager #content h2 a:hover,
             .vcwb.vcwb-font-manager #content h2 a:focus,
             .vcwb.vcwb-font-manager h2.reply-title a:hover,
             .vcwb.vcwb-font-manager h2.reply-title a:focus,
             .vcwb.vcwb-font-manager h2.comments-title a:hover,
             .vcwb.vcwb-font-manager h2.comments-title a:focus,
             .vcwb.vcwb-font-manager h2.entry-title a:hover,
             .vcwb.vcwb-font-manager h2.entry-title a:focus',

            'h3' => '
            h3,
             .vcwb.vcwb-font-manager .h3,
             .vcwb.vcwb-font-manager .entry-content h3,
             .vcwb.vcwb-font-manager #header h3,
             .vcwb.vcwb-font-manager #footer h3,
             .vcwb.vcwb-font-manager #content h3,
             .vcwb.vcwb-font-manager h3.reply-title,
             .vcwb.vcwb-font-manager .comments-area h3#reply-title,
             .vcwb.vcwb-font-manager h3.comments-title,
             .vcwb.vcwb-font-manager h3.entry-title',
            'h3.a' => '
            h3 a,
             .vcwb.vcwb-font-manager .h3 a,
             .vcwb.vcwb-font-manager .entry-content h3 a,
             .vcwb.vcwb-font-manager #header h3 a,
             .vcwb.vcwb-font-manager #footer h3 a,
             .vcwb.vcwb-font-manager #content h3 a,
             .vcwb.vcwb-font-manager h3.reply-title a,
             .vcwb.vcwb-font-manager h3.comments-title a,
             .vcwb.vcwb-font-manager h3.entry-title a',
            'h3.a:hover' => '
             h3 a:hover,
             h3 a:focus,
             .vcwb.vcwb-font-manager .h3 a:hover,
             .vcwb.vcwb-font-manager .h3 a:focus,
             .vcwb.vcwb-font-manager .entry-content h3 a:hover,
             .vcwb.vcwb-font-manager .entry-content h3 a:focus,
             .vcwb.vcwb-font-manager #header h3 a:hover,
             .vcwb.vcwb-font-manager #header h3 a:focus,
             .vcwb.vcwb-font-manager #footer h3 a:hover,
             .vcwb.vcwb-font-manager #footer h3 a:focus,
             .vcwb.vcwb-font-manager #content h3 a:hover,
             .vcwb.vcwb-font-manager #content h3 a:focus,
             .vcwb.vcwb-font-manager h3.reply-title a:hover,
             .vcwb.vcwb-font-manager h3.reply-title a:focus,
             .vcwb.vcwb-font-manager h3.comments-title a:hover,
             .vcwb.vcwb-font-manager h3.comments-title a:focus,
             .vcwb.vcwb-font-manager h3.entry-title a:hover,
             .vcwb.vcwb-font-manager h3.entry-title a:focus',

            'h4' => '
             h4,
             .vcwb.vcwb-font-manager .h4,
             .vcwb.vcwb-font-manager .entry-content h4,
             .vcwb.vcwb-font-manager #header h4,
             .vcwb.vcwb-font-manager #footer h4,
             .vcwb.vcwb-font-manager #content h4,
             .vcwb.vcwb-font-manager h4.reply-title,
             .vcwb.vcwb-font-manager .comments-area h4#reply-title,
             .vcwb.vcwb-font-manager h4.comments-title,
             .vcwb.vcwb-font-manager h4.entry-title',
            'h4.a' => '
             h4 a,
             .vcwb.vcwb-font-manager .h4 a,
             .vcwb.vcwb-font-manager .entry-content h4 a,
             .vcwb.vcwb-font-manager #header h4 a,
             .vcwb.vcwb-font-manager #footer h4 a,
             .vcwb.vcwb-font-manager #content h4 a,
             .vcwb.vcwb-font-manager h4.reply-title a,
             .vcwb.vcwb-font-manager h4.comments-title a,
             .vcwb.vcwb-font-manager h4.entry-title a',
            'h4.a:hover' => '
             h4 a:hover,
             h4 a:focus,
             .vcwb.vcwb-font-manager .h4 a:hover,
             .vcwb.vcwb-font-manager .h4 a:focus,
             .vcwb.vcwb-font-manager .entry-content h4 a:hover,
             .vcwb.vcwb-font-manager .entry-content h4 a:focus,
             .vcwb.vcwb-font-manager #header h4 a:hover,
             .vcwb.vcwb-font-manager #header h4 a:focus,
             .vcwb.vcwb-font-manager #footer h4 a:hover,
             .vcwb.vcwb-font-manager #footer h4 a:focus,
             .vcwb.vcwb-font-manager #content h4 a:hover,
             .vcwb.vcwb-font-manager #content h4 a:focus,
             .vcwb.vcwb-font-manager h4.reply-title a:hover,
             .vcwb.vcwb-font-manager h4.reply-title a:focus,
             .vcwb.vcwb-font-manager h4.comments-title a:hover,
             .vcwb.vcwb-font-manager h4.comments-title a:focus,
             .vcwb.vcwb-font-manager h4.entry-title a:hover,
             .vcwb.vcwb-font-manager h4.entry-title a:focus',

            'h5' => '
             h5,
             .vcwb.vcwb-font-manager .h5,
             .vcwb.vcwb-font-manager h5.reply-title,
             .vcwb.vcwb-font-manager .comments-area h5#reply-title,
             .vcwb.vcwb-font-manager #header h5,
             .vcwb.vcwb-font-manager #footer h5,
             .vcwb.vcwb-font-manager #content h5,
             .vcwb.vcwb-font-manager h5.comments-title,
             .vcwb.vcwb-font-manager .entry-content h5',
            'h5.a' => '
             h5 a,
             .vcwb.vcwb-font-manager .h5 a,
             .vcwb.vcwb-font-manager h5.reply-title a,
             .vcwb.vcwb-font-manager #header h5 a,
             .vcwb.vcwb-font-manager #footer h5 a,
             .vcwb.vcwb-font-manager #content h5 a,
             .vcwb.vcwb-font-manager h5.comments-title a,
             .vcwb.vcwb-font-manager .entry-content h5 a',
            'h5.a:hover' => '
             h5 a:hover,
             h5 a:focus,
             .vcwb.vcwb-font-manager .h5 a:hover,
             .vcwb.vcwb-font-manager .h5 a:focus,
             .vcwb.vcwb-font-manager h5.reply-title a:hover,
             .vcwb.vcwb-font-manager h5.reply-title a:focus,
             .vcwb.vcwb-font-manager #header h5 a:hover,
             .vcwb.vcwb-font-manager #header h5 a:focus,
             .vcwb.vcwb-font-manager #footer h5 a:hover,
             .vcwb.vcwb-font-manager #footer h5 a:focus,
             .vcwb.vcwb-font-manager #content h5 a:hover,
             .vcwb.vcwb-font-manager #content h5 a:focus,
             .vcwb.vcwb-font-manager h5.comments-title a:hover,
             .vcwb.vcwb-font-manager h5.comments-title a:focus,
             .vcwb.vcwb-font-manager .entry-content h5 a:hover,
             .vcwb.vcwb-font-manager .entry-content h5 a:focus',

            'h6' => '
             h6,
             .vcwb.vcwb-font-manager .h6,
             .vcwb.vcwb-font-manager h6.reply-title,
             .vcwb.vcwb-font-manager .comments-area h6#reply-title,
             .vcwb.vcwb-font-manager #header h6,
             .vcwb.vcwb-font-manager #footer h6,
             .vcwb.vcwb-font-manager #content h6,
             .vcwb.vcwb-font-manager h6.comments-title,
             .vcwb.vcwb-font-manager .entry-content h6',
            'h6.a' => '
             h6 a,
             .vcwb.vcwb-font-manager .h6 a,
             .vcwb.vcwb-font-manager h6.reply-title a,
             .vcwb.vcwb-font-manager #header h6 a,
             .vcwb.vcwb-font-manager #footer h6 a,
             .vcwb.vcwb-font-manager #content h6 a,
             .vcwb.vcwb-font-manager h6.comments-title a,
             .vcwb.vcwb-font-manager .entry-content h6 a',
            'h6.a:hover' => '
             h6 a:hover,
             h6 a:focus,
             .vcwb.vcwb-font-manager .h6 a:hover,
             .vcwb.vcwb-font-manager .h6 a:focus,
             .vcwb.vcwb-font-manager h6.reply-title a:hover,
             .vcwb.vcwb-font-manager h6.reply-title a:focus,
             .vcwb.vcwb-font-manager #header h6 a:hover,
             .vcwb.vcwb-font-manager #header h6 a:focus,
             .vcwb.vcwb-font-manager #footer h6 a:hover,
             .vcwb.vcwb-font-manager #footer h6 a:focus,
             .vcwb.vcwb-font-manager #content h6 a:hover,
             .vcwb.vcwb-font-manager #content h6 a:focus,
             .vcwb.vcwb-font-manager h6.comments-title a:hover,
             .vcwb.vcwb-font-manager h6.comments-title a:focus,
             .vcwb.vcwb-font-manager .entry-content h6 a:hover,
             .vcwb.vcwb-font-manager .entry-content h6 a:focus',

            'p' => [
                'typography' => '
                    body.vcwb.vcwb-font-manager,
                    body.vcwb.vcwb-font-manager .entry-content p',
                'margins' => '
                    ul, ol, p,
                    .vcwb.vcwb-font-manager .entry-content p,
                    .vcwb.vcwb-font-manager #header p,
                    .vcwb.vcwb-font-manager #footer p,
                    .vcwb.vcwb-font-manager #content p,
                    .vcwb.vcwb-font-manager .comment-content p',
            ],
            'p.a' => '
            .vcwb-font-manager :not(li[class*="menu"], div[class*="brand"]) > a:not(.vce-single-image-inner, [class*="vce-post-grid"], [class*="button"], [class*="logo"])',
            'p.a:hover' => '
            .vcwb-font-manager :not(li[class*="menu"], div[class*="brand"]) > a:not(.vce-single-image-inner, [class*="vce-post-grid"], [class*="button"], [class*="logo"]):hover,
            .vcwb-font-manager :not(li[class*="menu"], div[class*="brand"]) > a:not(.vce-single-image-inner, [class*="vce-post-grid"], [class*="button"], [class*="logo"]):focus',

            'blockquote' => '
            blockquote,
            .vcwb.vcwb-font-manager blockquote,
             .vcwb.vcwb-font-manager .entry-content blockquote,
            .vcwb.vcwb-font-manager #header blockquote,
            .vcwb.vcwb-font-manager #footer blockquote,
            .vcwb.vcwb-font-manager #content blockquote,
             .vcwb.vcwb-font-manager .entry-content blockquote p
            ',
            'blockquote.a' => 'blockquote a, .vcwb.vcwb-font-manager .entry-content blockquote a',
            'blockquote.a:hover' => '
            blockquote a:hover,
            .vcwb.vcwb-font-manager blockquote a:hover,
            blockquote a:focus,
            .vcwb.vcwb-font-manager blockquote a:focus,
            .vcwb.vcwb-font-manager .entry-content blockquote a:hover,
            .vcwb.vcwb-font-manager .entry-content blockquote a:focus',

            'figcaption' => 'figcaption,
             .vcwb.vcwb-font-manager .entry-content figcaption,
            .vcwb.vcwb-font-manager #header figcaption,
            .vcwb.vcwb-font-manager #footer figcaption,
            .vcwb.vcwb-font-manager #content figcaption,
             .vcwb.vcwb-font-manager .entry-content figcaption p
            ',
            'figcaption.a' => 'figcaption a, .vcwb.vcwb-font-manager .entry-content figcaption a',
            'figcaption.a:hover' => '
            figcaption a:hover,
            figcaption a:focus,
            .vcwb.vcwb-font-manager .entry-content figcaption a:hover,
            .vcwb.vcwb-font-manager .entry-content figcaption a:focus',

            'button' => [
                'typography' => '
                    .vcwb.vcwb-font-manager [class*="vce-button"] button[class*="vce-button"],
                    .vcwb.vcwb-font-manager [class*="vce-button"] a[class*="vce-button"],
                    .vcwb.vcwb-font-manager .vce-basic-shadow-button,
                    .vcwb.vcwb-font-manager .vce-call-to-action-button,
                    .vcwb.vcwb-font-manager .vce-double-text-button,
                    .vcwb.vcwb-font-manager .vce-icon-button,
                    .vcwb.vcwb-font-manager .vce-separated-button',
                'margins' => '
                    .vcwb.vcwb-font-manager [class*="vce-button"] button[class*="vce-button"],
                    .vcwb.vcwb-font-manager [class*="vce-button"] a[class*="vce-button"]',
            ],

            'menu' => '.vcwb.vcwb-font-manager ul.menu > li.menu-item > a,
                       .vcwb.vcwb-font-manager nav[class^="menu"] > ul > li.menu-item > a',

            'submenu' => '.vcwb.vcwb-font-manager ul.sub-menu > li.menu-item > a,
                           .vcwb.vcwb-font-manager nav[class^="menu"] ul.sub-menu  > li.menu-item > a',
        ];

        return vcfilter('vcv:fontManager:css:classes', $commonClasses);
    }
}
