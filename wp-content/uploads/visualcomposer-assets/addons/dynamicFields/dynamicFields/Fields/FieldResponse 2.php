<?php

namespace dynamicFields\dynamicFields\Fields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

trait FieldResponse
{
    /**
     * Replace actual value with curren value in response.
     *
     * @param string $currentValue
     * @param string $actualValue
     * @param string $response
     *
     * @return mixed
     */
    public function parseResponse($currentValue, $actualValue, $response)
    {
        $response = vcfilter('vcv:addon:dynamicFields:parseResponse:before', $response);

        if ($response === $actualValue) {
            return $response;
        }

        $currentValue = $this->replaceLineBreakingCharacters($currentValue);
        $actualValue = $this->replaceLineBreakingCharacters($actualValue);

        $response = preg_replace("/\r\n|\r/", "\n", $response);
        $response = str_replace($currentValue, $actualValue, $response);

        $response = $this->additionalResponseParser($currentValue, $actualValue, $response);

        return vcfilter('vcv:addon:dynamicFields:parseResponse:after', $response);
    }

    /**
     * We need additional response parse for some special cases.
     *
     * @param string $currentValue
     * @param string $actualValue
     * @param string $response
     *
     * @return string
     */
    public function additionalResponseParser($currentValue, $actualValue, $response)
    {
        // Arabic encoded symbols replace in woocommerce price..
        $actualValueDecoded = html_entity_decode($actualValue);
        $currentValueDecoded = html_entity_decode($currentValue);

        if ($actualValueDecoded === $actualValue && $currentValueDecoded === $currentValue) {
            return $response;
        }

        if (strpos($response, $currentValueDecoded) !== false) {
            $response = str_replace($currentValueDecoded, $actualValueDecoded, $response);
        } else {
            // Current value was saved in encoded way
            $response = str_replace(
                htmlentities(
                    $currentValue,
                    ENT_QUOTES,
                    'utf-8'
                ),
                $actualValue,
                $response
            );
        }

        return $response;
    }

    /**
     * Replace some line breaking
     *
     * @param string $value
     *
     * @return string
     */
    public function replaceLineBreakingCharacters($value)
    {
        $value = preg_replace("/\r\n|\r/", "\n", $value);
        $value = preg_replace("/<br\s?\/>/", '<br>', $value);

        return str_replace(html_entity_decode('&nbsp;'), '&nbsp;', $value);
    }
}
