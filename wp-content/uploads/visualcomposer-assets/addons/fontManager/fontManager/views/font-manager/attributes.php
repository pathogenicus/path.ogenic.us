<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// @codingStandardsIgnoreFile
$fontSizeText = __('Font Size', 'visualcomposer');
$fontSizeDescription = __('Set font size in px, em, and rem.', 'visualcomposer');
$letterSpacingText = __('Letter Spacing', 'visualcomposer');
$lineHeightText = __('Line Height', 'visualcomposer');
$colorText = __('Color', 'visualcomposer');
$capitalizationText = __('Capitalization', 'visualcomposer');
$marginTopText = __('Margin Top', 'visualcomposer');
$marginBottomText = __('Margin Bottom', 'visualcomposer');
$linkStylingText = __('Link Styling', 'visualcomposer');
$linkColorText = __('Link Color', 'visualcomposer');
$linkHoverColorText = __('Link Hover Color', 'visualcomposer');
$underlineText = __('Underline', 'visualcomposer');
$hoverUnderlineText = __('Hover Underline', 'visualcomposer');
$menuDescription = __('Control typography settings for your 1st level menu.', 'visualcomposer');
$submenuDescription = __('Control typography settings for your submenus.', 'visualcomposer');
$paddingLeftText = __('Padding Left', 'visualcomposer');
$spaceText = __('Space', 'visualcomposer');

?>
    window.VCV_FONT_MANAGER_ATTRIBUTES = <?php
echo json_encode(
    [
        'menu' =>
            [
                'title' => __('Menu', 'visualcomposer'),
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                    ],
                'description' => $menuDescription,
            ],
        'submenu' =>
            [
                'title' => __('Submenu', 'visualcomposer'),
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                    ],
                'description' => $submenuDescription,
            ],
        'h1' =>
            [
                'title' => 'H1',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'h2' =>
            [
                'title' => 'H2',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'h3' =>
            [
                'title' => 'H3',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'h4' =>
            [
                'title' => 'H4',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'h5' =>
            [
                'title' => 'H5',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'h6' =>
            [
                'title' => 'H6',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'p' =>
            [
                'title' => 'Paragraph',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'blockquote' =>
            [
                'title' => 'Blockquote',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'figcaption' =>
            [
                'title' => 'Caption',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'primary_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'primary-color',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                        'margin_top' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginTopText,
                                        'cssVariable' => 'margin-top',
                                    ],
                            ],
                        'margin_bottom' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $marginBottomText,
                                        'cssVariable' => 'margin-bottom',
                                    ],
                            ],
                        'link_styling' =>
                            [
                                'type' => 'title',
                                'value' => $linkStylingText,
                            ],
                        'link_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkColorText,
                                        'cssVariable' => 'link-color',
                                    ],
                            ],
                        'link_hover_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $linkHoverColorText,
                                        'cssVariable' => 'link-hover-color',
                                    ],
                            ],
                        'link_border_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $underlineText,
                                        'cssVariable' => 'link-border-color',
                                    ],
                            ],
                        'link_border_hover_color' =>
                            [
                                'type' => 'toggle',
                                'options' =>
                                    [
                                        'label' => $hoverUnderlineText,
                                        'cssVariable' => 'link-border-hover-color',
                                    ],
                            ],
                    ],
            ],
        'bullet' =>
            [
                'title' => 'Bullet',
                'settings' =>
                    [
                        'bullet_style' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => __('Bullet style', 'visualcomposer'),
                                        'cssVariable' => 'bullet-style',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'Line',
                                                    'value' => 'line',
                                                ],
                                                [
                                                    'label' => 'Circle',
                                                    'value' => 'circle',
                                                ],
                                                [
                                                    'label' => 'Square',
                                                    'value' => 'square',
                                                ],
                                            ],
                                    ],
                            ],
                        'line_width' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Width', 'visualcomposer'),
                                        'cssVariable' => 'bullet-width',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'line',
                                                    ],
                                            ],
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Height', 'visualcomposer'),
                                        'cssVariable' => 'bullet-height',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'line',
                                                    ],
                                            ],
                                    ],
                            ],
                        'circle_width' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Width', 'visualcomposer'),
                                        'cssVariable' => 'bullet-width',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'circle',
                                                    ],
                                            ],
                                    ],
                            ],
                        'circle_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Height', 'visualcomposer'),
                                        'cssVariable' => 'bullet-height',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'circle',
                                                    ],
                                            ],
                                    ],
                            ],
                        'square_width' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Width', 'visualcomposer'),
                                        'cssVariable' => 'bullet-width',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'square',
                                                    ],
                                            ],
                                    ],
                            ],
                        'square_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => __('Height', 'visualcomposer'),
                                        'cssVariable' => 'bullet-height',
                                        'toggleVisibility' =>
                                            [
                                                'bullet_style' =>
                                                    [
                                                        'value' => 'square',
                                                    ],
                                            ],
                                    ],
                            ],
                        'bullet_color' =>
                            [
                                'type' => 'color',
                                'options' =>
                                    [
                                        'label' => $colorText,
                                        'cssVariable' => 'bullet-color',
                                    ],
                            ],
                        'padding_left' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $paddingLeftText,
                                        'cssVariable' => 'bullet-padding-left',
                                    ],
                            ],
                        'space' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $spaceText,
                                        'cssVariable' => 'bullet-space',
                                    ],
                            ],
                    ],
            ],
        'button' =>
            [
                'title' => 'Button',
                'settings' =>
                    [
                        'font_family' =>
                            [
                                'type' => 'googleFonts',
                            ],
                        'font_size' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $fontSizeText,
                                        'description' => $fontSizeDescription,
                                        'cssVariable' => 'font-size',
                                    ],
                            ],
                        'letter_spacing' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $letterSpacingText,
                                        'cssVariable' => 'letter-spacing',
                                    ],
                            ],
                        'line_height' =>
                            [
                                'type' => 'string',
                                'options' =>
                                    [
                                        'label' => $lineHeightText,
                                        'cssVariable' => 'line-height',
                                    ],
                            ],
                        'text_transform' =>
                            [
                                'type' => 'dropdown',
                                'options' =>
                                    [
                                        'label' => $capitalizationText,
                                        'cssVariable' => 'text-transform',
                                        'values' =>
                                            [
                                                [
                                                    'label' => 'None',
                                                    'value' => 'none',
                                                ],
                                                [
                                                    'label' => 'Capitalize',
                                                    'value' => 'capitalize',
                                                ],
                                                [
                                                    'label' => 'Lowercase',
                                                    'value' => 'lowercase',
                                                ],
                                                [
                                                    'label' => 'Uppercase',
                                                    'value' => 'uppercase',
                                                ],
                                            ],
                                    ],
                            ],
                    ],
            ],
    ]
);
