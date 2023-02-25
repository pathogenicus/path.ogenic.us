<?php

namespace dynamicFields\dynamicFields\Fields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'FieldResponse.php';

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Data;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class Toolset
 * @package dynamicFields\dynamicFields\Fields
 */
class Toolset extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    protected $fields = [];

    /**
     * Toolset constructor.
     */
    public function __construct()
    {
        /** @see \dynamicFields\dynamicFields\Fields\Toolset::init */
        $this->wpAddAction('plugins_loaded', 'init');
    }

    protected function init()
    {
        if (!defined('WPCF_VERSION') || version_compare(WPCF_VERSION, '3.3.5') < 0) {
            return;
        }
        $this->addFilter('vcv:editor:data:postFields', 'addGroup');
        $this->addFilter('vcv:editor:data:postData', 'addPostData');
        $this->addFilter('vcv:editor:data:postFields', 'addFields');
        $this->addFilter('vcv:dynamic:value:toolset:*', 'renderFields');
    }

    /**
     * @param string $sourceId
     *
     * @param $payload
     *
     * @return array
     */
    protected function fields($sourceId, $payload)
    {
        if (isset($this->fields[ $sourceId ])) {
            return $this->fields[ $sourceId ];
        }

        $isForce = isset($payload['forceAddField']) && $payload['forceAddField'];
        $toolsetData = [];
        $fields = [];
        if ($isForce) {
            $toolsetGroups = toolset_get_field_groups(['domain' => 'posts', 'is_active' => true,]);
        } else {
            $toolsetGroups = toolset_get_groups_for_element($sourceId, 'posts');
        }

        if (!empty($toolsetGroups)) {
            foreach ($toolsetGroups as $toolsetGroup) {
                $groupFields = $toolsetGroup->get_field_definitions();
                $toolsetData[] = [
                    'groupTitle' => $toolsetGroup->get_name(),
                    'groupId' => $toolsetGroup->get_id(),
                    'groupFields' => $groupFields,
                    'forceAddField' => $isForce,
                ];
            }
        }

        if (!$isForce && !is_array($toolsetData) || empty($toolsetData)) {
            return;
        }

        if (isset($this->fields[ $sourceId ]) && !empty($this->fields[ $sourceId ])) {
            return $this->fields[ $sourceId ];
        }

        $allowedFieldTypes = [
            'checkbox' => ['string', 'htmleditor', 'inputSelect'],
            'colorpicker' => ['string', 'htmleditor', 'inputSelect'],
            'date' => ['string', 'htmleditor', 'inputSelect'],
            'email' => ['string', 'htmleditor', 'inputSelect'],
            'embed' => ['url'],
            'file' => ['url'],
            'image' => ['attachimage', 'designOptions', 'designOptionsAdvanced'],
            'numeric' => ['string', 'htmleditor', 'inputSelect'],
            'phone' => ['string', 'htmleditor', 'inputSelect'],
            'radio' => ['string', 'htmleditor', 'inputSelect'],
            'select' => ['string', 'htmleditor', 'inputSelect'],
            'skype' => ['string', 'htmleditor', 'inputSelect'],
            'textarea' => ['htmleditor'],
            'textfield' => ['string', 'htmleditor', 'inputSelect'],
            'url' => ['url'],
            'wysiwyg' => ['htmleditor'],
        ];

        foreach ($toolsetData as $toolsetGroup) {
            $fields = $this->parseFields($sourceId, $toolsetGroup, $allowedFieldTypes, $fields);
        }

        $this->fields[ $sourceId ] = [];
        $this->fields[ $sourceId ] = $fields;

        return $fields;
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    protected function addGroup($response)
    {
        $values = [
            'group' => [
                'label' => __('Toolset Custom Fields', 'visualcomposer'),
                'values' => [],
            ],
        ];

        if (!isset($response['string']['toolset'])) {
            $response['string']['toolset'] = $values;
        }
        if (!isset($response['inputSelect']['toolset'])) {
            $response['inputSelect']['toolset'] = $values;
        }
        if (!isset($response['htmleditor']['toolset'])) {
            $response['htmleditor']['toolset'] = $values;
        }
        if (!isset($response['attachimage']['toolset'])) {
            $response['attachimage']['toolset'] = $values;
        }
        if (!isset($response['designOptions']['toolset'])) {
            $response['designOptions']['toolset'] = $values;
        }
        if (!isset($response['designOptionsAdvanced']['toolset'])) {
            $response['designOptionsAdvanced']['toolset'] = $values;
        }
        if (!isset($response['url']['toolset'])) {
            $response['url']['toolset'] = $values;
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostData($response, $payload)
    {
        $sourceId = false;
        if (isset($payload['sourceId'])) {
            $sourceId = $payload['sourceId'];
        }

        $fields = $this->fields($sourceId, $payload);
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field) {
                $response[ $field['value'] ] = $field['actualValue'];
            }
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function addFields($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $response = $this->call('removeMetaFieldDuplicates', [$response, $sourceId]);

        $fields = $this->fields($sourceId, $payload);
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field) {
                $values = [
                    'value' => $field['value'],
                    'label' => $field['label'],
                    'fieldType' => $field['fieldType'],
                ];

                $types = $field['type'];
                foreach ($types as $type) {
                    $response[ $type ][ $field['group'] ]['group']['values'][] = $values;
                }
            }
        }

        return $response;
    }

    /**
     * @param $needle
     * @param $haystack
     * @param string $currentKey
     *
     * @return bool|string
     */
    protected function findMetaDuplicates($needle, $haystack, $currentKey = '')
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $nextKey = $this->findMetaDuplicates($needle, $value, $currentKey . '.' . $key);
                if ($nextKey) {
                    return $nextKey;
                }
            } elseif ($value === $needle) {
                return $currentKey;
            }
        }

        return false;
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function renderFields($response, $payload)
    {
        $atts = $payload['atts'];
        $sourceId = get_the_ID();
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        $fields = $this->fields($sourceId, $payload);

        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (isset($atts['currentValue']) && $atts['value'] === $field['value']) {
                    if (empty($response)) {
                        $response = $field['actualValue'];
                    }
                    $response = $this->parseResponse($atts['currentValue'], $field['actualValue'], $response);
                }
            }
        }

        return $response;
    }

    /**
     * Remove ACF fields from meta group
     *
     * @param $response
     * @param $sourceId
     * @param \VisualComposer\Helpers\Data $dataHelper
     *
     * @return array
     */
    protected function removeMetaFieldDuplicates($response, $sourceId, Data $dataHelper)
    {
        $fieldsToUnset = [];
        if (isset($this->fields[ $sourceId ]) && !empty($this->fields[ $sourceId ])) {
            foreach ($this->fields[ $sourceId ] as $key => $field) {
                foreach ($response as $groupKey => $fieldGroup) {
                    if ($this->findMetaDuplicates($field['metaKey'], $fieldGroup)) {
                        $fieldsToUnset[] = sprintf(
                            '%s%s',
                            $groupKey,
                            $this->findMetaDuplicates(
                                'customMetaField:' . $field['metaKey'],
                                $fieldGroup
                            )
                        );
                    }
                }
            }
        }

        if (!empty($fieldsToUnset)) {
            foreach ($fieldsToUnset as $field) {
                $dataHelper->forget($response, ltrim($field, '.'));
            }
        }

        return $response;
    }

    /**
     * @param $sourceId
     * @param $toolsetField
     *
     * @return string
     */
    protected function parseFieldValues($sourceId, $toolsetField)
    {
        $value = '';
        $fieldInstance = toolset_get_field_instance($sourceId, $toolsetField->get_domain(), $toolsetField->get_slug());
        if ($fieldInstance) {
            $value = $fieldInstance->render(\Toolset_Field_Renderer_Purpose::REST);
        }

        if (empty($value)) {
            return '';
        }

        if (is_array($value)) {
            $value = $this->parseMultipleOptions($value);
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function parseMultipleOptions($value)
    {
        if (isset($value['type'])) {
            $type = $value['type'];
        }

        if (isset($value['formatted'])) {
            $value = $value['formatted'];
        } elseif (isset($value['repeatable']) && !empty($value['repeatable'])) {
            $value = $value['repeatable'][0];
            $value = $this->parseMultipleOptions($value);
        } elseif (is_array($value['raw']) && !empty($value['raw'])) {
            $value = $value['raw'][0];
        } else {
            if (!empty($value['raw'])) {
                $value = $value['raw'];
            } else {
                $value = '';
            }
        }

        if (isset($type) && in_array($type, ['textarea', 'wysiwyg'])) {
            $value = wpautop($value);
        }

        return $value;
    }

    /**
     * @param $sourceId
     * @param $toolsetGroup
     * @param array $allowedFieldTypes
     * @param array $fields
     *
     * @return array
     */
    protected function parseFields($sourceId, $toolsetGroup, array $allowedFieldTypes, $fields = [])
    {
        $toolsetFields = $toolsetGroup['groupFields'];
        foreach ($toolsetFields as $toolsetField) {
            if (key_exists($toolsetField->get_type_slug(), $allowedFieldTypes)) {
                $value = $this->parseFieldValues($sourceId, $toolsetField);
                if ((empty($value) && $toolsetGroup['forceAddField']) || !empty($value)) {
                    $fields[] = [
                        'value' => 'toolset:' . $toolsetGroup['groupId'] . '-' . $toolsetField->get_slug(),
                        'label' => $toolsetGroup['groupTitle'] . ' - ' . $toolsetField->get_display_name(),
                        'actualValue' => !is_null($value) ? $value : '',
                        'type' => $allowedFieldTypes[ $toolsetField->get_type_slug() ],
                        'fieldType' => $toolsetField->get_type_slug(),
                        'group' => 'toolset',
                        'metaKey' => $toolsetField->get_meta_key(),
                    ];
                }
            }
        }

        return $fields;
    }
}
