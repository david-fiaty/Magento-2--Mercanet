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

use Magento\Framework\Locale\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Cart;
use Cmsbox\Mercanet\Model\Adminhtml\Source\CaptureMode;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Connector {

    use \Cmsbox\Mercanet\Gateway\Processor\ResponseProcessor;

    const KEY_SIMU_MERCHANT_ID = 'simu_merchant_id';
    const KEY_TEST_MERCHANT_ID = 'test_merchant_id';
    const KEY_PROD_MERCHANT_ID = 'prod_merchant_id';
    const KEY_SIMU_SECRET_KEY = 'simu_secret_key';
    const KEY_TEST_SECRET_KEY = 'test_secret_key';
    const KEY_PROD_SECRET_KEY = 'prod_secret_key';
    const KEY_REQUEST = 'request';
    const KEY_RESPONSE = 'response';
    const KEY_DEFAULT_LANGUAGE = 'en';
    const KEY_RESPONSE_ERROR = 'error';
    const KEY_RESPONSE_SUCCESS = 'success';
    const KEY_RESPONSE_FRAUD = 'fraud';
    const KEY_RESPONSE_FLAG = 'flag';
    const KEY_ENVIRONMENT = 'environment';

    /**
     * @var Resolver
     */    
    protected $localeResolver;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Connector constructor.
     */
    public function __construct(
        Resolver $localeResolver,
        Tools $tools,
        Cart $cart
    ) {
        $this->localeResolver  = $localeResolver;
        $this->tools           = $tools;
        $this->cart            = $cart;
    }

    /**
     * Creates a string from a request parameters array.
     *
     * @return string
     */
    public function toParameterString($params, $separator = true) {
        $arr = [];
        if ($separator) {
            // Prepare the parameters
            foreach ($params as $key => $value) {
                $arr[] = $key . '=' . $value;
            }

            // Create a string
            $str = implode('|', $arr);
        }
        else {
            $str = '';
            foreach ($params as $key => $value) {
                $str .= $value;
            }
        }

        return $str;
    }

    /**
     * Conversion to a currency code.
     *
     * @return string
     */
    public function convertCurrencyToCurrencyCode($currency, $config) {
        $currencies = $config->getSupportedCurrencies();
        if (!in_array($currency, array_keys($currencies))) {
            throw new LocalizedException(__('Currency not supported by') . ' ' . $config->base[Core::moduleLabel()]);
        }

        return $currencies[$currency];
    }

    /**
     * Creates a seal for a request.
     *
     * @return string
     */
    public function getSeal($params, $config, $exclude = []) {
        $separator = true;

        // Exclude fields by key if needed
        if (!empty($exclude)) {
            $params = array_diff_key($params, array_flip($exclude));
            $separator = false;
        }

        // Parameters to string
        $params = $this->toParameterString($params, $separator);

        // Return the seal
        return hash('sha256', $params . $this->getSecretKey($config));
    }

    /**
     * Retrieves the customer language.
     */
    public function getCustomerLanguage() {
        $lang = explode('_', $this->localeResolver->getLocale()) ;
        return (isset($lang[0]) && !empty($lang[0])) ? $lang[0] : self::KEY_DEFAULT_LANGUAGE;
    }

    /**
     * Formats an amount for a gateway request.
     */   
    public function formatAmount($amount) {
        return (number_format($amount, 2))*100;
    }

    /**
     * Returns the transaction reference.
     *
     * @return string
     */
    public function getTransactionReference() {
        return (string) time();   
    }

    /**
     * Returns the merchant ID.
     *
     * @return string
     */
    public function getMerchantId($config) {
        switch ($config->base[self::KEY_ENVIRONMENT]) {
            case 'simu': 
            $id = $config->base[self::KEY_SIMU_MERCHANT_ID];
            break;

            case 'test': 
            $id = $config->base[self::KEY_TEST_MERCHANT_ID];
            break;

            case 'prod': 
            $id = $config->base[self::KEY_PROD_MERCHANT_ID];;
            break;
        }

        return (string) $id;
    }

    /**
     * Returns the active secret key.
     *
     * @return string
     */
    public function getSecretKey($config) {
        // Return the secret key
        switch ($config->base[self::KEY_ENVIRONMENT]) {
            case 'simu': 
            $key = $config->params[Core::moduleId()][self::KEY_SIMU_SECRET_KEY];
            break;

            case 'test': 
            $key = $config->params[Core::moduleId()][self::KEY_TEST_SECRET_KEY];
            break;

            case 'prod': 
            $key = $config->params[Core::moduleId()][self::KEY_PROD_SECRET_KEY];
            break;
        }

        return $key;
    }

    /**
     * Checks if the response is valid.
     *
     * @return bool
     */
    public function isValid($response, $config) {
        // Todo - calculate seal identical to request
        return true;

        if (isset($response['Data'])) {
            // Prepare the seal
            $seal = hash('sha256', $response['Data'] . $this->getSecretKey($config));

            // Test conditions
            return isset($response['Data']) 
            && isset($response['Seal']) && $response['Seal'] == $seal;
        }
        
        return false;
    }

    /**
     * Returns the billing address.
     */
    public function getBillingAddress($entity) {
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

    /**
     * Returns the shipping address.
     */
    public function getShippingAddress($entity) {
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
