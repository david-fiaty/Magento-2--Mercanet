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
    const KEY_RESPONSE_ERROR = 'error';
    const KEY_RESPONSE_SUCCESS = 'success';
    const KEY_RESPONSE_FRAUD = 'fraud';
    const KEY_RESPONSE_FLAG = 'flag';
    const KEY_CAPTURE_MODE_FIELD = 'capture_mode_field';
    const KEY_CUSTOMER_EMAil_FIELD = 'customer_email_field';
    const KEY_TRANSACTION_ID_FIELD = 'transactionReference';
    const KEY_CAPTURE_MODE = 'capture_mode';
    const KEY_CAPTURE_DAY = 'capture_day';
    const KEY_CAPTURE_IMMEDIATE = 'IMMEDIATE';
    const KEY_CAPTURE_DEFERRED = 'AUTHOR_CAPTURE';
    const KEY_CAPTURE_MANUAL = 'VALIDATION';
    const KEY_ORDER_STATUS_AUTHORIZED = 'order_status_authorized';
    const KEY_ORDER_STATUS_CAPTURED = 'order_status_captured';
    const KEY_ORDER_STATUS_REFUNDED = 'order_status_refunded';
    const KEY_ORDER_STATUS_FLAGGED = 'order_status_flagged';

    /**
     * Turns a data response string into an array.
     */
    public static function unpackData($params) {
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
    /**
     * Returns the available payment brands.
     *
     * @return string
     */
	public static function getPaymentBrandsList() {
        return [
            ['value' => '1EUROCOM', 'label' => __('1euro.com')],
            ['value' => '3XCBCOFINOGA', 'label' => __('Cofinoga 3xCB')],
            ['value' => 'ACCEPTGIRO', 'label' => __('AcceptGiro')],
            ['value' => 'ACCORD', 'label' => __('Carte Accord')],
            ['value' => 'ACCORD_KDO', 'label' => __('Carte Accord Cadeau (Banque Accord)')],
            ['value' => 'ACCORD_3X', 'label' => __('Carte Accord Paiement 3 fois')],
            ['value' => 'ACCORD_4X', 'label' => __('Carte Accord Paiement 4 fois')],
            ['value' => 'AMEX', 'label' => __('Carte American Express')],
            ['value' => 'AURORE', 'label' => __('Carte Aurore')],
            ['value' => 'BCACUP', 'label' => __('Carte Bancaire de Banque Casino (CUP)')],
            ['value' => 'BCMC', 'label' => __('Bancontact')],
            ['value' => 'CADHOC', 'label' => __('Cadhoc')],
            ['value' => 'CADOCARTE', 'label' => __('Cado Card')],
            ['value' => 'CB', 'label' => __('Carte Bancaire')],
            ['value' => 'CBCONLINE', 'label' => __('PayButton CBC Online')],
            ['value' => 'CETELEM_3X', 'label' => __('Cetelem 3xCB')],
            ['value' => 'CETELEM_4X', 'label' => __('Cetelem 4xCB')],
            ['value' => 'COFIDIS_3X', 'label' => __('Cofidis 3xCB')],
            ['value' => 'COFIDIS_4X', 'label' => __('Cofidis 4xCB')],
            ['value' => 'CVA', 'label' => __('Carte Visa Aurore')],
            ['value' => 'ECV', 'label' => __('e-Chèque-Vacances')],
            ['value' => 'ELV', 'label' => __('Elektronisches LastschriftVerfahren')],
            ['value' => 'Franfinance3xcb', 'label' => __('Franfinance 3xCB')],
            ['value' => 'Franfinance4xcb', 'label' => __('Franfinance 4xCB')],
            ['value' => 'GIROPAY', 'label' => __('Giropay')],
            ['value' => 'IDEAL', 'label' => __('iDeal')],
            ['value' => 'ILLICADO', 'label' => __('Illicado')],
            ['value' => 'INCASSO', 'label' => __('Incasso')],
            ['value' => 'INGHOMEPAY', 'label' => __('PayButton ING Home’Pay')],
            ['value' => 'KBCONLINE', 'label' => __('PayButton KBC Online')],
            ['value' => 'LEPOTCOMMUN', 'label' => __('Le Pot Commun')],
            ['value' => 'MAESTRO', 'label' => __('Carte Maestro (Mastercard)')],
            ['value' => 'MASTERCARD', 'label' => __('Carte Mastercard')],
            ['value' => 'MASTERPASS ([1]', 'label' => __('Portefeuille virtuel MasterPass')],
            ['value' => 'NETBANKING', 'label' => __('Netbanking')],
            ['value' => 'NXCB', 'label' => __('Carte Cetelem NxCB')],
            ['value' => 'NXCB_PREL', 'label' => __('Carte Cetelem NxCB - Partie Prélèvement')],
            ['value' => 'PASSCADEAU', 'label' => __('Pass Cadeau')],
            ['value' => 'PAYLIB (1)', 'label' => __('Portefeuille virtuel Paylib')],
            ['value' => 'PAYPAL', 'label' => __('Paypal')],
            ['value' => 'PAYTRAIL', 'label' => __('Paytrail')],
            ['value' => 'PLURIEL', 'label' => __('Franfinance')],
            ['value' => 'POSTFINANCE', 'label' => __('Carte PostFinance')],
            ['value' => 'PRESTO', 'label' => __('Presto Plus')],
            ['value' => 'SEPA_DIRECT_DEBIT', 'label' => __('SDD (SEPA Direct Debit)')],
            ['value' => 'SOFINCO', 'label' => __('Carte Sofinco')],
            ['value' => 'SOFORTUBERWEISUNG', 'label' => __('Sofort berweisung (Sofort Banking)')],
            ['value' => 'SPIRITOFCADEAU', 'label' => __('Spirit Of Cadeau')],
            ['value' => 'VISA', 'label' => __('Carte Visa')],
            ['value' => 'VISACHECKOUT', 'label' => __('Portefeuille virtuel Visa Checkout')],
            ['value' => 'VISA_ELECTRON', 'label' => __('Carte Visa Electron')],
            ['value' => 'VPAY', 'label' => __('Carte VPAY (Visa)')]
        ];
    }
}
