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

trait ResponseProcessor {

    /**
     * Checks if a response is success.
     *
     * @return bool
     */  
    public function isSuccess($response) {
        $response = $this->tools->unpackData($response['Data'], '|', '=');
        if (is_array($response) && isset($response['responseCode']) && $response['responseCode'] == '00') {
            return true;
        }

        return false;
    }

    /**
     * Checks if a refund is success.
     *
     * @return bool
     */  

    public function isSuccessRefund($response) {
        if (is_array($response) && isset($response['responseCode']) && $response['responseCode'] == '00') {
            return true;
        }

        return false;
    }

    /**
     * Checks if a response is error.
     *
     * @return string
     */  

    public function isError($response) {
        if (is_array($response) && isset($response['responseCode']) && $response['responseCode'] != '00') {
            return true;
        }

        return false;
    }

    /**
     * Checks if a response is fraud
     *
     * @return string
     */  

    public function isFraud($response) {
        // Todo - Add fraud field check
        return false;
    }

    /**
     * Checks if a response is flag
     *
     * @return string
     */  

    public function isFlag($response) {
        // Todo - Add flag field check
        return false;
    }

    /**
     * Returns the key version.
     *
     * @return object
     */
    public function addFraudData($order) {
        // Todo - Add fraud check
        return $order;
    }

    /**
     * Retrieves the response transaction id
     *
     * @return string
     */  

    public function getResponseTransactionId($response) {
        return $response['transactionReference'];
    }


}
