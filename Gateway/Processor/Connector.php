<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */
 
namespace Cmsbox\Mercanet\Gateway\Processor;

class Connector {
    const KEY_REQUEST = 'request';
    const KEY_RESPONSE = 'response';
    const KEY_LOGGING = 'logging';
    const KEY_RESPONSE_ERROR = 'error';
    const KEY_RESPONSE_SUCCESS = 'success';
    const KEY_CAPTURE_MODE_FIELD = 'capture_mode_field';
    const KEY_CUSTOMER_EMAIL_FIELD = 'customer_email_field';
    const KEY_ORDER_ID_FIELD = 'order_id_field';
    const KEY_TRANSACTION_ID_FIELD = 'transaction_id_field';
    const KEY_CAPTURE_MODE = 'capture_mode';
    const KEY_CAPTURE_DAY = 'capture_day';
    const KEY_CAPTURE_IMMEDIATE = 'IMMEDIATE';
    const KEY_CAPTURE_DEFERRED = 'AUTHOR_CAPTURE';
    const KEY_CAPTURE_MANUAL = 'VALIDATION';
    const KEY_ORDER_STATUS_AUTHORIZED = 'order_status_authorized';
    const KEY_ORDER_STATUS_CAPTURED = 'order_status_captured';
    const KEY_ORDER_STATUS_REFUNDED = 'order_status_refunded';
    const KEY_ORDER_STATUS_FLAGGED = 'order_status_flagged';
    const KEY_TRANSACTION_INFO = 'transaction_info';
    const KEY_ADDITIONAL_INFORMATION = 'additional_information';
    const KEY_ACTIVE = 'active';
    const KEY_REDIRECT_METHOD = 'redirect_method';
    const KEY_FORM_TEMPLATE = 'form_template';
    const EMAIL_COOKIE_NAME = 'guestEmail';
    const METHOD_COOKIE_NAME = 'methodId';
        
    /**
     * Turns a data response string into an array.
     */
    public static function unpackData($response) {
        // Get the parameters
        $params = $response;

        // Prepare the separators
        $separator1 = '|';
        $separator2 = '=';

        // Prepare the output array
        $output = [];

        // Process first level data
        $arr = explode($separator1, $params);

        // Process second level data
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                $members = explode($separator2, $row);
                $output[$members[0]] = $members[1];
            }

            return $output;
        }

        return $arr;
    }

    /**
     * Turns a data request array into a string.
     */   
    public static function packData($arr) {
        $output = [];
        foreach ($arr as $key => $val) {
            $output[] = $key . '=' . $val;
        }

        return implode('|', $output);
    }
  
    /**
     * Returns the authorized order status.
     *
     * @return string
     */
  /*
    public function getOrderStatusAuthorized() {
        return (string) $this->getValue(self::KEY_ORDER_STATUS_AUTHORIZED);
    }
*/
    /**
     * Returns the billing address.
     */
    /*
    public static function getBillingAddress($entity) {
        // Retrieve the address object
        $address = $entity->getBillingAddress();

        // Return the formatted array
        return [
            'billingAddress.street'  => implode(', ', $address->getStreet()),
            'billingAddress.city'    => $address->getCity(),
            'billingAddress.country' => $this->tools->getCountryCodeA2A3($address->getCountryId()),
            'billingAddress.zipCode' => $address->getPostcode(),
            'billingContact.email'   => $entity->getCustomerEmail(),
            'billingAddress.state'   => !empty($address->getRegionCode()) ? $address->getRegionCode() : '',
        ];
    }
*/
    /**
     * Returns the shipping address.
     */
    /*
    public static function getShippingAddress($entity) {
        // Retrieve the address object
        $address = $entity->getBillingAddress();

        // Return the formatted array,
        return [
            'customerAddress.street'  => implode(', ', $address->getStreet()),        
            'customerAddress.city'    => $address->getCity(),
            'customerAddress.country' => $this->tools->getCountryCodeA2A3($address->getCountryId()),
            'customerAddress.zipCode' => $address->getPostcode(),
            'customerAddress.state'   => !empty($address->getRegionCode()) ? $address->getRegionCode() : '',
            'customerContact.email'   => $entity->getCustomerEmail()
        ];
    }
*/
}
