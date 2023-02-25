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
 * Class AdvancedCustomFields
 * @package dynamicFields\dynamicFields\Fields
 */
class AdvancedCustomFields extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * List fields groups with fields belonging to that groups.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * List of fields types.
     * For some fields types should show instead metadata values some more user frontend friendly data.
     *
     * @var array
     */
    protected $replaceableFieldsTypes = [
        'image',
    ];

    /**
     * List of fields.
     * For some fields we should show instead metadata values some more user frontend friendly data.
     *
     * @var array
     */
    protected $replaceableFieldsList = [];


    /**
     * AdvancedCustomFields constructor.
     */
    public function __construct()
    {
        if (!class_exists('acf')) {
            return;
        }
        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::addGroup */
        $this->addFilter('vcv:editor:data:postFields', 'addGroup');

        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::addPostData */
        $this->addFilter('vcv:editor:data:postData', 'addPostData');

        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::replaceFieldsPostData */
        $this->addFilter('vcv:editor:data:postData', 'replaceFieldsPostData', 100);

        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::addFields */
        $this->addFilter('vcv:editor:data:postFields', 'addFields');

        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::renderFields */
        $this->addFilter('vcv:dynamic:value:acf:*', 'renderFields');

        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::addAdditinalMetaRendering */
        $this->addFilter('vcv:addon:dynamicFields:fields:meta:renderMeta', 'addMetaRendering');
    }

    /**
     * @param $sourceId
     * @param $field
     *
     * @return mixed
     */
    protected function getUserValue($sourceId, $field)
    {
        $groupObject = get_field_object($field['key'], $sourceId);
        $defaultValue = false;
        if (isset($groupObject['default_value'])) {
            $defaultValue = $groupObject['default_value'];
        }

        $currentUser = wp_get_current_user();
        if (is_array($field) && !empty($field) && !empty($currentUser->ID)
            && get_field(
                $field['key'],
                'user_' . $currentUser->ID
            )) {
            $pageValue = get_field($field['key'], $sourceId);
            //check if the field is also in post/page, if so then override it
            if ($pageValue && $defaultValue !== $pageValue) {
                $result = 'default';
            } else {
                $result = get_field($field['key'], 'user_' . $currentUser->ID);
            }
        } else {
            $result = $defaultValue;
        }

        return $result;
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
        if ($this->cachedField($sourceId, $payload)) {
            return $this->cachedField($sourceId, $payload);
        }

        $isForce = isset($payload['forceAddField']) && $payload['forceAddField'];
        $acfData = [];
        $fields = [];

        if ($isForce) {
            $acfGroups = acf_get_field_groups();
        } else {
            $acfGroups = acf_get_field_groups(['post_id' => $sourceId]);
        }

        $value = '';
        if (isset($payload['atts']['value'])) {
            $value = $payload['atts']['value'];
        }
        $acfGroups = $this->otherGroupsInPage($isForce, $value, $acfGroups);

        if (!empty($acfGroups)) {
            foreach ($acfGroups as $acfGroup) {
                $groupFields = acf_get_fields($acfGroup['ID']);

                //TODO: Add this also for categories etc per request
                $groupFields = $this->isUserGroup($sourceId, $acfGroup['location'], $groupFields);

                $acfData[] = [
                    'forceAddField' => $isForce,
                    'location' => $acfGroup['location'],
                    'groupTitle' => $acfGroup['title'],
                    'groupFields' => $groupFields,
                ];
            }
        }

        if ($this->cachedField($sourceId, $payload)) {
            return $this->cachedField($sourceId, $payload);
        }

        $allowedFieldTypes = [
            'text' => ['string', 'htmleditor', 'inputSelect'],
            'textarea' => ['string', 'htmleditor', 'inputSelect'],
            'number' => ['string', 'htmleditor', 'inputSelect'],
            'range' => ['string', 'htmleditor', 'inputSelect'],
            'email' => ['string', 'htmleditor', 'inputSelect'],
            'date_picker' => ['string', 'htmleditor', 'inputSelect'],
            'date_time_picker' => ['string', 'htmleditor', 'inputSelect'],
            'time_picker' => ['string', 'htmleditor', 'inputSelect'],
            'color_picker' => ['string', 'htmleditor', 'inputSelect'],
            'button_group' => ['string', 'htmleditor', 'inputSelect'],
            'radio' => ['string', 'htmleditor', 'inputSelect'],
            'checkbox' => ['string', 'htmleditor', 'inputSelect'],
            'select' => ['string', 'htmleditor', 'inputSelect'],
            'image' => ['attachimage', 'designOptions', 'designOptionsAdvanced'],
            'url' => ['url'],
            'user' => ['url'],
            'page_link' => ['url'],
            'link' => ['url'],
            'file' => ['url'], // return_format ==> id -> get url by id, if array -> ['url'] -> if url -> url
            'wysiwyg' => ['htmleditor'],
            'oembed' => ['htmleditor'],
        ];

        $post = get_post($sourceId);
        $postType = get_post_type($post);

        foreach ($acfData as $acfGroup) {
            $fields = $this->parseFields($sourceId, $postType, $acfGroup, $allowedFieldTypes, $fields);
        }

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
                'label' => __('Advance Custom Fields', 'visualcomposer'),
                'values' => [],
            ],
        ];

        if (!isset($response['string']['acf'])) {
            $response['string']['acf'] = $values;
        }
        if (!isset($response['inputSelect']['acf'])) {
            $response['inputSelect']['acf'] = $values;
        }
        if (!isset($response['htmleditor']['acf'])) {
            $response['htmleditor']['acf'] = $values;
        }
        if (!isset($response['attachimage']['acf'])) {
            $response['attachimage']['acf'] = $values;
        }
        if (!isset($response['designOptions']['acf'])) {
            $response['designOptions']['acf'] = $values;
        }
        if (!isset($response['designOptionsAdvanced']['acf'])) {
            $response['designOptionsAdvanced']['acf'] = $values;
        }
        if (!isset($response['url']['acf'])) {
            $response['url']['acf'] = $values;
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
        /** @see \dynamicFields\dynamicFields\Fields\AdvancedCustomFields::removeMetaFieldDuplicates */
        $response = $this->call('removeMetaFieldDuplicates', [$response, $sourceId]);

        $fields = $this->fields($sourceId, $payload);
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field) {
                $values = [
                    'value' => $field['value'],
                    'label' => $field['label'],
                    'fieldType' => $field['fieldType'],
                    'fieldSlug' => $field['name'],
                    'fieldMetaSlug' => 'customMetaField::' . $field['name'],
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

        if (!is_array($fields)) {
            return $response;
        }

        foreach ($fields as $field) {
            if ($atts['value'] === $field['value']) {
                if (isset($atts['currentValue'])) {
                    if (empty($response)) {
                        $response = $field['actualValue'];
                    }
                    // wysiwyg field itself has the additional format.
                    // And we should use already formatted value for our replacement.
                    if ($field['fieldType'] == 'wysiwyg') {
                        $response = $this->parseResponse($response, $field['actualValue'], $response);
                    } else {
                        $response = $this->parseResponse($atts['currentValue'], $field['actualValue'], $response);
                    }
                } elseif (isset($atts['type']) && $atts['type'] === 'backgroundImage') {
                    if (!empty($field['actualValue'])) {
                        $this->addDesignOptionsStyles($atts['elementId'], $field['actualValue'], $atts['device']);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @param $id
     * @param $src
     * @param $device
     */
    protected function addDesignOptionsStyles($id, $src, $device)
    {
        $frontendHelper = vchelper('Frontend');
        if ($frontendHelper->isPageEditable()) {
            return;
        }

        $this->wpAddAction(
            'wp_print_footer_scripts',
            function () use ($id, $src, $device) {
                $devicesMedia = [
                    'all' => ['', ''],
                    'xs' => ['@media (max-width: 543px) {', '}'],
                    'sm' => ['@media (max-width: 767px) and (min-width: 544px) {', '}'],
                    'md' => ['@media (max-width: 991px) and (min-width: 768px) {', '}'],
                    'lg' => ['@media (max-width: 1199px) and (min-width: 992px) {', '}'],
                    'xl' => ['@media (min-width: 1200px) {', '}'],
                ];
                // TODO: Use wp_add_inline_style
                $selector = sprintf(
                    '#el-%1$s[data-vce-do-apply*="all"][data-vce-do-apply*="el-%1$s"][data-vce-dynamic-image-%2$s="%1$s"],#el-%1$s[data-vce-do-apply*="background"][data-vce-do-apply*="el-%1$s"][data-vce-dynamic-image-%2$s="%1$s"],#el-%1$s [data-vce-do-apply*="all"][data-vce-do-apply*="el-%1$s"][data-vce-dynamic-image-%2$s="%1$s"],#el-%1$s [data-vce-do-apply*="background"][data-vce-do-apply*="el-%1$s"][data-vce-dynamic-image-%2$s="%1$s"]',
                    esc_attr($id),
                    esc_attr($device)
                );
                echo sprintf(
                    '<style>%1$s %2$s { background-image: url("%3$s"); } %4$s</style>',
                    $devicesMedia[ $device ][0],
                    $selector,
                    esc_url($src),
                    $devicesMedia[ $device ][1]
                );
            }
        );
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
                    if ($this->findMetaDuplicates($field['name'], $fieldGroup)) {
                        $fieldsToUnset[] = sprintf(
                            '%s%s',
                            $groupKey,
                            $this->findMetaDuplicates(
                                'customMetaField:' . $field['name'],
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

    protected function acfCheckValueFileImageLink($result, $field, $sourceId)
    {
        switch ($field['type']) {
            case 'file':
            case 'image':
            case 'link':
                // file can be used only in URL-type attributes,
                // so return value must be URL as string
                // possible return formats:
                // array
                switch ($field['return_format']) {
                    case 'array':
                    case 'id':
                        $field['return_format'] = 'url';
                        $result = apply_filters(
                            'acf/format_value',
                            acf_get_value($sourceId, $field),
                            $sourceId,
                            $field
                        );

                        break;
                }
                break;
        }

        return $result;
    }

    protected function acfCheckValueForm($result, $field, $sourceId)
    {
        switch ($field['type']) {
            case 'select':
            case 'radio':
                $tempResult = get_field($field['key'], $sourceId);
                if (empty($tempResult)) {
                    $result = '';
                    break;
                }
                if ((isset($field['multiple']) && !$field['multiple']) || !isset($field['multiple'])) {
                    $tempResult = [
                        $tempResult,
                    ];
                }

                $result = $this->getAcfFormTypeValue($field, $tempResult);
                break;
            case 'checkbox':
                $tempResult = get_field($field['key'], $sourceId);
                if (empty($tempResult)) {
                    $result = '';
                    break;
                }
                $result = $this->getAcfFormTypeValue($field, $tempResult);
                break;
        }

        return $result;
    }

    protected function acfCheckValueGallery($result, $field, $sourceId)
    {
        if ($field['type'] === 'gallery') {
            // file can be used only in URL-type attributes,
            // so return value must be URL as string
            // possible return formats:
            // array
            $field['return_format'] = 'url';
            $result = apply_filters(
                'acf/format_value',
                acf_get_value($sourceId, $field),
                $sourceId,
                $field
            );

            $result = $result[0];
        }

        return $result;
    }

    protected function acfCheckValueUser($result, $field, $sourceId)
    {
        if ($field['type'] === 'user') {
            // file can be used only in URL-type attributes,
            // so return value must be URL as string
            // possible return formats:
            // array
            $field['multiple'] = 0;
            $field['return_format'] = 'id';
            $tempResult = apply_filters(
                'acf/format_value',
                acf_get_value($sourceId, $field),
                $sourceId,
                $field
            );

            $result = get_author_posts_url($tempResult);
        }

        return $result;
    }

    protected function acfCheckValuePage($result, $field, $sourceId)
    {
        if ($field['type'] === 'page_link') {
            $result = get_field($field['key'], $sourceId);
            if (isset($field['multiple']) && $field['multiple'] && !empty($result)) {
                $result = $result[0];
            }
        }

        return $result;
    }

    /**
     * @param $field
     * @param $sourceId
     * @param $acfGroup
     *
     * @return string
     */
    protected function acfGetValue($field, $sourceId, $acfGroup)
    {
        $result = 'default';
        $result = $this->acfCheckValueFileImageLink($result, $field, $sourceId);
        $result = $this->acfCheckValueGallery($result, $field, $sourceId);
        $result = $this->acfCheckValueUser($result, $field, $sourceId);
        $result = $this->acfCheckValuePage($result, $field, $sourceId);
        $result = $this->acfCheckValueForm($result, $field, $sourceId);

        //TODO: Add this also for categories etc per request
        $result = $this->isUserGroup($sourceId, $acfGroup['location'], false, $result, $field);

        if ($result === 'default') {
            $result = get_field($field['key'], $sourceId);
        }
        if (is_array($result)) {
            $result = ''; // so not supported yet.
        }
        if (empty($result) && isset($field['value'])) {
            $result = $field['value'];
        }

        $result = $this->autoP($field, $result);
        //@codingStandardsIgnoreEnd

        $payload = [
            'field' => $field,
            'group' => $acfGroup,
            'sourceId' => $sourceId,
        ];

        return vcfilter('vcv:addon:dynamicFields:fields:acfGetValue', $result, $payload);
    }

    /**
     * @param $sourceId
     * @param $postType
     * @param $acfGroup
     * @param array $allowedFieldTypes
     * @param array $fields
     *
     * @return array
     */
    protected function parseFields($sourceId, $postType, $acfGroup, array $allowedFieldTypes, $fields = [])
    {
        $acfFields = $acfGroup['groupFields'];
        if (!is_array($acfFields)) {
            return $fields;
        }

        foreach ($acfFields as $acfField) {
            if (!array_key_exists($acfField['type'], $allowedFieldTypes)) {
                continue;
            }

            $value = $this->acfGetValue($acfField, $sourceId, $acfGroup);
            if ((empty($value) && $acfGroup['forceAddField']) || !empty($value)) {
                $fieldData = [
                    'value' => 'acf:' . $acfField['key'],
                    'label' => $acfGroup['groupTitle'] . ' - ' . $acfField['label'],
                    'actualValue' => is_null($value) ? '' : $value,
                    'type' => $allowedFieldTypes[ $acfField['type'] ],
                    'fieldType' => $acfField['type'],
                    'group' => 'acf',
                    'name' => $acfField['name'],
                ];

                $fields[] = $fieldData;

                if (in_array($acfField['type'], $this->replaceableFieldsTypes)) {
                    $this->replaceableFieldsList[] = $fieldData;
                }
            }
        }

        return $fields;
    }

    /**
     * Wrap text in p if needed
     *
     * @param $acfField
     * @param $value
     *
     * @return string
     */
    protected function autoP($acfField, $value)
    {
        if ($acfField['type'] === 'textarea') {
            $value = wpautop($value);
        }

        return $value;
    }

    /**
     * @param $tempResult
     *
     * @return string
     */
    protected function getImplodeArray($tempResult)
    {
        return implode(
            ', ',
            array_map(
                function ($item) {
                    return $item['label'] . ': ' . $item['value'];
                },
                $tempResult
            )
        );
    }

    /**
     * @param $field
     * @param $tempResult
     *
     * @return string
     */
    protected function getAcfFormTypeValue($field, $tempResult)
    {
        switch ($field['return_format']) {
            case 'value':
            case 'label':
                $result = !empty($tempResult) ? implode(', ', $tempResult) : '';
                break;
            case 'array':
                $result = $this->getImplodeArray($tempResult);
                break;
        }

        return $result;
    }

    /**
     * @param $sourceId
     * @param $payload
     *
     * @return false|mixed
     */
    protected function cachedField($sourceId, $payload)
    {
        if (isset($this->fields[ $sourceId ])) {
            foreach ($this->fields as $fields) {
                foreach ($fields as $field) {
                    if (!isset($payload['atts'])) {
                        continue;
                    }
                    if ($field['value'] === $payload['atts']['value']) {
                        return $this->fields[ $sourceId ];
                    }
                }
            }
        }

        return false;
    }

    /**
     * In case in the page is additional group that is not attached to the post/page but other type, like - users,
     * categories etc
     *
     * @param $isForce
     * @param $value
     * @param $acfGroups
     *
     * @return mixed
     */
    protected function otherGroupsInPage($isForce, $value, $acfGroups)
    {
        if (!$isForce) {
            $fieldObject = get_field_object(substr($value, 4));
            if (isset($fieldObject['parent'])) {
                $parent = $fieldObject['parent'];
                $userGroup = acf_get_field_group($parent);

                //Add group if it's not there
                $search = array_search($userGroup['key'], array_column($acfGroups, 'key'), true);
                if ($search === false) {
                    $acfGroups[] = $userGroup;
                }
            }
        }

        return $acfGroups;
    }

    /**
     * @param $sourceId
     * @param $locations
     * @param $groupFields
     * @param string $result
     * @param false $field
     *
     * @return array|mixed|string
     */
    protected function isUserGroup($sourceId, $locations, $groupFields, $result = 'default', $field = false)
    {
        if (is_array($locations) && !empty($locations)) {
            foreach ($locations as $location) {
                if (in_array(
                    $location[0]['param'],
                    ['current_user', 'current_user_role', 'user_form', 'user_role'],
                    true
                )) {
                    if (!empty($groupFields) && is_array($groupFields)) {
                        foreach ($groupFields as $key => $groupField) {
                            $groupFields[ $key ]['value'] = $this->getUserValue($sourceId, $groupField);
                        }
                    } else {
                        return $this->getUserValue($sourceId, $field);
                    }
                }
            }
        }

        if ($field) {
            return $result;
        } else {
            return $groupFields;
        }
    }

    /**
     * For some fields we should replace actual metadata values with some more user frontend friendly data.
     *
     * @param array $response
     *
     * @return array
     */
    protected function replaceFieldsPostData($response, $payload)
    {
        foreach ($this->replaceableFieldsList as $fieldData) {
            $fieldHash = str_replace('acf:', '', $fieldData['value']);
            $fieldSlug = array_search($fieldHash, $response);
            $fieldSlug = str_replace('customMetaField:_', 'customMetaField:', $fieldSlug);

            if (!$fieldSlug) {
                continue;
            }

            $dataThatNeedReplace = $response[$fieldSlug];
            $fieldReplaceableDta = $this->getFieldReplaceData($payload, $fieldData['fieldType'], $dataThatNeedReplace);

            $response[$fieldSlug] = $fieldReplaceableDta;
        }

        return $response;
    }

    /**
     * Try to replace metadata with more user frontend friendly data.
     *
     * @param mixed $metaValue
     * @param array $payload
     *
     * @return mixed
     */
    protected function addMetaRendering($metaValue, $payload)
    {
        $fieldData = get_field_object($payload['metaKey'], $payload['postId']);

        if (empty($fieldData['type']) || empty($metaValue)) {
            return $metaValue;
        }

        $metaValue = $this->getFieldReplaceData($payload, $fieldData['type'], $metaValue);

        return $metaValue;
    }

    /**
     * Try to replace field data with more user frontend friendly data.
     *
     * @param array $payload
     * @param string $fieldType
     * @param $dataThatNeedReplace
     *
     * @return string
     */
    protected function getFieldReplaceData($payload, $fieldType, $dataThatNeedReplace)
    {
        switch ($fieldType) {
            case 'image':
                $newData = wp_get_attachment_image($dataThatNeedReplace, 'thumbnail', true);
                break;
            default:
                $newData = $dataThatNeedReplace;
                break;
        }

        if (empty($newData)) {
            $newData = $dataThatNeedReplace;
        }

        return $newData;
    }
}
