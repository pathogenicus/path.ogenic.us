<?php

namespace templateWidget\templateWidget;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Module;
use WP_Widget;

class TemplateWidget extends WP_Widget implements Module
{
    public function __construct()
    {

        $widgetOps = [
            'classname' => 'we-docs widget_nav_menu',
            'description' => __('Add Visual Composer templates to a sidebar.', 'visualcomposer'),
        ];

        $controlOps = ['width' => 300, 'height' => 350, 'id_base' => 'vcwb-template-widget'];

        parent::__construct(
            'vcwb-template-widget',
            __('Visual Composer Template Widget', 'visualcomposer'),
            $widgetOps,
            $controlOps
        );
    }

    public function widget($args, $instance)
    {
        if (isset($instance['template'])) {
            $widgetController = vcapp('templateWidget\templateWidget\WidgetController');
            $content = $widgetController->getTemplateContent($instance['template']);

            echo $content;
        }
    }

    public function update($newInstance, $oldInstance)
    {
        $instance = $oldInstance;
        $instance['template'] = strip_tags($newInstance['template']);

        return $instance;
    }

    public function form($instance)
    {
        $editorTemplatesHelper = vchelper('EditorTemplates');
        //Set up some default widget settings.
        $defaults = [
            'template' => '',
        ];
        $instance = wp_parse_args((array)$instance, $defaults);
        $selectedTemplate = $instance['template'];
        $options = $editorTemplatesHelper->getCustomTemplateOptions();
        echo sprintf(
            '<p><select id="%s" name="%s" class="widefat" style="width:100%%;">',
            $this->get_field_id('template'),
            $this->get_field_name('template')
        );

        foreach ($options as $key => $option) {
            if (isset($option['group'])) {
                echo $this->createGroup($option['group'], $selectedTemplate);
            } else {
                echo $this->createOption($option, $selectedTemplate);
            }
        }
        echo '</select></p>';
    }

    protected function createGroup($group, $selectedTemplate)
    {
        $options = '';

        if (!empty($group['values'])) {
            $items = $this->createOptionsList($group['values'], $selectedTemplate);
            $options .= sprintf('<optgroup label="%s">%s</optgroup>', $group['label'], $items);
        }

        return $options;
    }

    protected function createOptionsList($values, $selectedTemplate)
    {
        $items = '';
        foreach ($values as $value) {
            $items .= $this->createOption($value, $selectedTemplate);
        }

        return $items;
    }

    protected function createOption($item, $selectedTemplate)
    {
        $templateId = $item['value'];
        $label = $item['label'];
        $selected = false;
        if ((int)$selectedTemplate === (int)$templateId) {
            $selected = ' selected="selected"';
        }

        return sprintf('<option%s value="%s">%s</option>', $selected, $templateId, $label);
    }
}
