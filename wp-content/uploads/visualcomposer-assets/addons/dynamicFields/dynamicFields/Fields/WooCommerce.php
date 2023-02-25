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
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class WooCommerce
 * @package dynamicFields\dynamicFields\Fields
 */
class WooCommerce extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * WooCommerce constructor.
     */
    public function __construct()
    {
        /** @see \dynamicFields\dynamicFields\Fields\WooCommerce::init */
        $this->wpAddAction('woocommerce_loaded', 'init');
    }

    protected function init()
    {
        $this->addFilter('vcv:editor:data:postFields', 'addGroup');
        $this->addFilter('vcv:editor:data:postData', 'addPostData');
        $this->addFilter('vcv:editor:data:postFields', 'addFields');
        $this->addFilter('vcv:dynamic:value:woo:*', 'renderFields');
    }

    /**
     * @param string $sourceId
     * @param $payload
     *
     * @return array|bool
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function fields($sourceId, $payload)
    {
        if (isset($this->fields[ $sourceId ])) {
            return $this->fields[ $sourceId ];
        }

        $post = get_post($sourceId);
        $fields = [];
        $isForce = isset($payload['forceAddField']) && $payload['forceAddField'];
        // @codingStandardsIgnoreLine
        $isProduct = !empty($post) && $post->post_type === 'product' && $post->post_status !== 'trash';
        $woocommerce = vcapp('VendorsWooCommerceController');

        if ($isProduct || $isForce) {
            $fields = [
                [
                    'value' => 'woo:price',
                    'label' => esc_html__('Price', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getPrice($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:regular_price',
                    'label' => esc_html__('Regular Price', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getRegularPrice($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:sale_price',
                    'label' => esc_html__('Sale Price', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getSalePrice($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:short_description',
                    'label' => esc_html__('Short Description', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getShortDescription($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:purchase_note',
                    'label' => esc_html__('Purchase Note', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getPurchaseNote($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:categories',
                    'label' => esc_html__('Categories', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getCategories($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:weight',
                    'label' => esc_html__('Weight', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getWeight($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:dimensions',
                    'label' => esc_html__('Dimensions', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDimensions($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:availability',
                    'label' => esc_html__('Availability', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getAvailability($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:total_sales',
                    'label' => esc_html__('Total Sales', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getTotalSales($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:average_rating',
                    'label' => esc_html__('Average Rating', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getAverageRating($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:rating_count',
                    'label' => esc_html__('Rating Count', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getRatingCount($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:date_on_sale_to',
                    'label' => esc_html__('Date On Sale To', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDateOnSaleTo($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:date_on_sale_from',
                    'label' => esc_html__('Date On Sale From', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDateOnSaleFrom($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:downloads',
                    'label' => esc_html__('Files to Download', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDownloads($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:download_expiry',
                    'label' => esc_html__('Download Expiry', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDownloadExpiry($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:download_limit',
                    'label' => esc_html__('Download Limit', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getDownloadLimit($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:stock_quantity',
                    'label' => esc_html__('Stock Quantity', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getStockQuantity($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:sku',
                    'label' => esc_html__('SKU', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getSku($sourceId) : '',
                    'type' => ['string', 'htmleditor', 'inputSelect'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:product_url',
                    'label' => esc_html__('Product', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getProductUrl($sourceId) : '',
                    'type' => ['url'],
                    'group' => 'woocommerce',
                ],
                [
                    'value' => 'woo:add_to_cart',
                    'label' => esc_html__('Add To Cart', 'visualcomposer'),
                    'actualValue' => $isProduct ? $woocommerce->getAddToCartUrl($sourceId) : '',
                    'type' => ['url'],
                    'group' => 'woocommerce',
                ],
            ];
        }

        $urlFields = [
            [
                'value' => 'woo:shop_url',
                'label' => esc_html__('Shop', 'visualcomposer'),
                'actualValue' => $woocommerce->getShopUrl(),
                'type' => ['url'],
                'group' => 'woocommerce',
            ],
            [
                'value' => 'woo:checkout_url',
                'label' => esc_html__('Checkout', 'visualcomposer'),
                'actualValue' => $woocommerce->getCheckoutUrl(),
                'type' => ['url'],
                'group' => 'woocommerce',
            ],
            [
                'value' => 'woo:cart_url',
                'label' => esc_html__('Cart', 'visualcomposer'),
                'actualValue' => $woocommerce->getCartUrl(),
                'type' => ['url'],
                'group' => 'woocommerce',
            ],
            [
                'value' => 'woo:account_url',
                'label' => esc_html__('Account', 'visualcomposer'),
                'actualValue' => $woocommerce->getAccountUrl(),
                'type' => ['url'],
                'group' => 'woocommerce',
            ],
        ];

        $fields = array_merge($fields, $urlFields);
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
                'label' => __('WooCommerce', 'visualcomposer'),
                'values' => [],
            ],
        ];

        $urlValues = [
            'group' => [
                'label' => __('WooCommerce URLs', 'visualcomposer'),
                'values' => [],
            ],
        ];

        if (!isset($response['string']['woocoommerce'])) {
            $response['string']['woocommerce'] = $values;
        }
        if (!isset($response['htmleditor']['woocoommerce'])) {
            $response['htmleditor']['woocommerce'] = $values;
        }
        if (!isset($response['inputSelect']['woocoommerce'])) {
            $response['inputSelect']['woocommerce'] = $values;
        }
        if (!isset($response['url']['woocoommerce'])) {
            $response['url']['woocommerce'] = $urlValues;
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function addPostData($response, $payload)
    {
        $sourceId = false;
        if (isset($payload['sourceId'])) {
            $sourceId = $payload['sourceId'];
        }

        $fields = $this->fields($sourceId, $payload);
        if (is_array($fields)) {
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
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    protected function addFields($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $fields = $this->fields($sourceId, $payload);

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $values = [
                    'value' => $field['value'],
                    'label' => $field['label'],
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
     * @param $response
     * @param $payload
     *
     * @return mixed
     * @throws \VisualComposer\Framework\Illuminate\Container\BindingResolutionException
     */
    public function renderFields($response, $payload)
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
                        return $field['actualValue'];
                    }
                    $response = $this->parseResponse($atts['currentValue'], $field['actualValue'], $response);
                }
            }
        }

        return $response;
    }
}
