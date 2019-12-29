<?php
namespace Naxero\Mercanet\Gateway\Vendor;

class GestionPlusInterface
{
    const TRACE      = 0; // 1 = Display trace, 0 = No trace display

    const TEST       = "https://office-server-mercanet.test.sips-atos.com/rs-services/v2/";
    const PRODUCTION = "https://office-server.mercanet.bnpparibas.net/rs-services/v2/";
    
    const INTERFACE_VERSION_CASH     = "CR_WS_2.20";
    const INTERFACE_VERSION_CHECKOUT = "IR_WS_2.20";
    const INTERFACE_VERSION_DIAG     = "DR_WS_2.20";
    const INTERFACE_VERSION_WALLET   = "WR_WS_2.20";
    
    const INSTALMENT                       = "INSTALMENT";
    const REQUEST_CARD_ORDER              = "checkout/cardOrder";
    const REQUEST_WALLET_ORDER          = "checkout/walletOrder";
    const REQUEST_DIRECT_DEBIT_ORDER      = "checkout/directDebitOrder";
    const REQUEST_CARD_CHECK_ENROLLMENT = "checkout/cardCheckEnrollment";
    const REQUEST_CARD_VALIDATE_AUTHENT = "checkout/cardValidateAuthenticationAndOrder";
    const REQUEST_PAYMENT_PROVIDER_INIT = "checkout/paymentProviderInitialize";
    const REQUEST_CASH_MGT_CANCEL       = "cashManagement/cancel";
    const REQUEST_CASH_MGT_DUPLICATE       = "cashManagement/duplicate";
    const REQUEST_CASH_MGT_REFERRAL       = "cashManagement/referall";
    const REQUEST_CASH_MGT_REFUND       = "cashManagement/refund";
    const REQUEST_CASH_MGT_VALIDATE      = "cashManagement/validate";
    const REQUEST_CASH_MGT_RECYCLE      = "cashManagement/recycle";
    const REQUEST_CASH_MGT_CREDIT       = "cashManagement/creditHolder";
    const REQUEST_DIAG_GET_TRX_DATA      = "diagnostic/getTransactionData";
    const REQUEST_WALLET_ADD_CARD          = "wallet/addCard";
    const REQUEST_PAYMENT_MEAN_INFO      = "paymentMeanInfo/getCardData";
    
    // BYPASS3DS
    const BYPASS3DS_ALL = "ALL";
    const BYPASS3DS_MERCHANTWALLET = "MERCHANTWALLET";

    private $brandsmap = array(
        'ACCEPTGIRO' => 'CREDIT_TRANSFER',
        'AMEX' => 'CARD',
        'BCMC' => 'CARD',
        'BUYSTER' => 'CARD',
        'BANK CARD' => 'CARD',
        'CB' => 'CARD',
        'IDEAL' => 'CREDIT_TRANSFER',
        'INCASSO' => 'DIRECT_DEBIT',
        'MAESTRO' => 'CARD',
        'MASTERCARD' => 'CARD',
        'MASTERPASS' => 'CARD',
        'MINITIX' => 'OTHER',
        'NETBANKING' => 'CREDIT_TRANSFER',
        'PAYPAL' => 'CARD',
        'PAYLIB' => 'CARD',
        'REFUND' => 'OTHER',
        'SDD' => 'DIRECT_DEBIT',
        'SOFORT' => 'CREDIT_TRANSFER',
        'VISA' => 'CARD',
        'VPAY' => 'CARD',
        'VISA ELECTRON' => 'CARD',
        'CBCONLINE' => 'CREDIT_TRANSFER',
        'KBCONLINE' => 'CREDIT_TRANSFER'
    );

    /**
     * @var ShaComposer
     */
    private $secretKey;

    private $pspURL = self::TEST;

    private $pspRequest = self::REQUEST_CARD_ORDER;
    
    private $parameters = array();

    private $shaString;

    private $pspFields = array(
        'amount', 'cardExpiryDate', 'cardNumber', 'cardCSCValue',
    'currencyCode', 'merchantId', 'interfaceVersion',
        'transactionReference', 'keyVersion', 'paymentMeanBrand', 'customerLanguage',
        'billingAddress.city', 'billingAddress.company', 'billingAddress.country',
        'billingAddress', 'billingAddress.postBox', 'billingAddress.state',
        'billingAddress.street', 'billingAddress.streetNumber', 'billingAddress.zipCode',
        'billingContact.email', 'billingContact.firstname', 'billingContact.gender',
        'billingContact.lastname', 'billingContact.mobile', 'billingContact.phone',
        'customerAddress', 'customerAddress.city', 'customerAddress.company',
        'customerAddress.country', 'customerAddress.postBox', 'customerAddress.state',
        'customerAddress.street', 'customerAddress.streetNumber', 'customerAddress.zipCode',
        'customerContact', 'customerContact.email', 'customerContact.firstname',
        'customerContact.gender', 'customerContact.lastname', 'customerContact.mobile',
        'customerContact.phone', 'customerContact.title', 'expirationDate', 'automaticResponseUrl',
        'templateName','paymentMeanBrandList', 'instalmentData', 'paymentPattern',
        'captureDay', 'captureMode', 'merchantTransactionDateTime', 'fraudData.bypass3DS', 'seal',
        'orderChannel', 'orderId', 'returnContext', 'transactionOrigin', 'merchantWalletId', 'paymentMeanId',
        'operationAmount', 'operationOrigin', 's10TransactionReference', 'fromTransactionReference',
        'fromMerchantId', 's10FromTransactionReference', 'statementReference', 'shoppingCartDetail',
        'redirectionData', 'paResMessage', 'messageVersion', 'customerContact', 'customerContact.email'
    );

    private $requiredFieldsCardOrder = array(
        'amount', 'cardExpiryDate', 'cardNumber', 'currencyCode', 'interfaceVersion', 'merchantId', 'keyVersion'
    );
    
    private $requiredFieldsWalletOrder = array(
        'amount', 'currencyCode', 'interfaceVersion', 'keyVersion', 'merchantId', 'merchantWalletId', 'orderChannel'
    );

    public $allowedlanguages = array(
        'nl', 'fr', 'de', 'it', 'es', 'cy', 'en'
    );
    
    private static $currencies = array(
        'EUR' => '978', 'USD' => '840', 'CHF' => '756', 'GBP' => '826',
        'CAD' => '124', 'JPY' => '392', 'MXP' => '484', 'TRY' => '949',
        'AUD' => '036', 'NZD' => '554', 'NOK' => '578', 'BRC' => '986',
        'ARP' => '032', 'KHR' => '116', 'TWD' => '901', 'SEK' => '752',
        'DKK' => '208', 'KRW' => '410', 'SGD' => '702', 'XPF' => '953',
        'XOF' => '952'
    );

    public static function convertCurrencyToCurrencyCode($currency)
    {
        if (!in_array($currency, array_keys(self::$currencies))) {
            throw new \InvalidArgumentException("Unknown currencyCode $currency.");
        }
        return self::$currencies[$currency];
    }

    public static function convertCurrencyCodeToCurrency($code)
    {
        if (!in_array($code, array_values(self::$currencies))) {
            throw new \InvalidArgumentException("Unknown Code $code.");
        }
        return array_search($code, self::$currencies);
    }

    public static function getCurrencies()
    {
        return self::$currencies;
    }

    public function __construct($secret)
    {
        global $parameters, $secretKey, $shaString;
        $this->secretKey = $secret;
        $this->shaString = "";
        unset($this->parameters);
        $this->parameters = array();
    }
    
    public function shaCompose($item, $key)
    {
        global $shaString;
        if (($key != "keyVersion") && ($key != "seal")) {
            $this->shaString .= $item;
        }
    }
    
    /**
     * @return string
     */
    public function getShaSign()
    {
        global $shaString;
        $this->validate();
        $arr = $this->toArray();
        array_walk_recursive($arr, [$this, 'shaCompose']);
        if (self::TRACE == 1) {
            echo "<br>Seal composed by : " . $this->shaString . "<br>";
        }
        return hash_hmac('sha256', utf8_encode($this->shaString), $this->secretKey);
    }
    
    /**
     * @return string
     */
    public function getPspRequest()
    {
        return $this->pspRequest;
    }

    public function setPspRequest($pspRequest)
    {
        $this->pspRequest = $pspRequest;
    }

    public function setStatementReference($statementReference)
    {
        $this->parameters['statementReference'] = $statementReference;
    }

    public function setShoppingCartDetail($shoppingCartDetail)
    {
        $this->parameters['shoppingCartDetail'] = $shoppingCartDetail;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->pspURL.$this->pspRequest;
    }

    public function setUrl($pspUrl)
    {
        $this->validateUri($pspUrl);
        $this->pspURL = $pspUrl;
    }

    public function setNormalReturnUrl($url)
    {
        $this->validateUri($url);
        $this->parameters['normalReturnUrl'] = $url;
    }

    public function setAutomaticResponseUrl($url)
    {
        $this->validateUri($url);
        $this->parameters['automaticResponseUrl'] = $url;
    }

    public function setTransactionReference($transactionReference)
    {
        if (preg_match('/[^a-zA-Z0-9_-]/', $transactionReference)) {
            throw new \InvalidArgumentException("TransactionReference cannot contain special characters");
        }
        $this->parameters['transactionReference'] = $transactionReference;
    }

    /**
     * Set amount in cents, eg EUR 12.34 is written as 1234
     */
    public function setAmount($amount)
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount must be a positive number");
        }
        $this->parameters['amount'] = $amount;
    }
    
    public function setOperationAmount($amount)
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount must be a positive number");
        }
        $this->parameters['operationAmount'] = $amount;
    }
    
    public function setOperationOrigin($origin)
    {
        $this->parameters['operationOrigin'] = $origin;
    }

    public function setFromMerchantId($valeur)
    {
        $this->parameters['fromMerchantId'] = $valeur;
    }

    public function setS10TransactionReferenceTransactionId($valeur)
    {
        $this->parameters['s10TransactionReference']['s10TransactionId'] = $valeur;
    }

    public function setS10TransactionReferenceTransactionIdDate($valeur)
    {
        $this->parameters['s10TransactionReference']['s10TransactionIdDate'] = $valeur;
    }

    public function setS10FromTransactionReferenceTransactionId($valeur)
    {
        $this->parameters['s10FromTransactionReference']['s10FromTransactionId'] = $valeur;
    }

    public function setS10FromTransactionReferenceTransactionIdDate($valeur)
    {
        $this->parameters['s10FromTransactionReference']['s10FromTransactionIdDate'] = $valeur;
    }

    public function fromTransactionReference($valeur)
    {
        $this->parameters['fromTransactionReference'] = $valeur;
    }

    public function setCurrency($currency)
    {
        if (!array_key_exists(strtoupper($currency), self::getCurrencies())) {
            throw new \InvalidArgumentException("Unknown currency");
        }
        $this->parameters['currencyCode'] = self::convertCurrencyToCurrencyCode($currency);
    }

    public function setLanguage($language)
    {
        if (!in_array($language, $this->allowedlanguages)) {
            throw new \InvalidArgumentException("Invalid language locale");
        }
        $this->parameters['customerLanguage'] = $language;
    }

    public function setRedirectionData($valeur)
    {
            $this->parameters['redirectionData'] = $valeur;
    }

    public function setPaResMessage($valeur)
    {
            $this->parameters['paResMessage'] = $valeur;
    }

    public function setMessageVersion($valeur)
    {
            $this->parameters['messageVersion'] = $valeur;
    }


    public function setPaymentBrand($brand)
    {
        $this->parameters['paymentMeanBrandList'] = '';
        if (!array_key_exists(strtoupper($brand), $this->brandsmap)) {
            throw new \InvalidArgumentException("Unknown Brand [$brand].");
        }
        $this->parameters['paymentMeanBrandList'] = strtoupper($brand);
    }

    public function setCustomerContactEmail($email)
    {
        if (strlen($email) > 50) {
            throw new \InvalidArgumentException("Email is too long");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
           // throw new \InvalidArgumentException("Email is invalid");
        }
        $this->parameters['customerContact'] = ['email' => $email];
    }

    public function setBillingContactEmail($email)
    {
        if (strlen($email) > 50) {
            throw new \InvalidArgumentException("Email is too long");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
           // throw new \InvalidArgumentException("Email is invalid");
        }
        $this->parameters['billingContact.email'] = $email;
    }

    public function setBillingAddressStreet($street)
    {
        if (strlen($street) > 35) {
            throw new \InvalidArgumentException("street is too long");
        }
        $this->parameters['billingAddress.street'] = Normalizer::normalize($street);
    }

    public function setBillingAddressStreetNumber($nr)
    {
        if (strlen($nr) > 10) {
            throw new \InvalidArgumentException("streetNumber is too long");
        }
        $this->parameters['billingAddress.streetNumber'] = Normalizer::normalize($nr);
    }

    public function setBillingAddressZipCode($zipCode)
    {
        if (strlen($zipCode) > 10) {
            throw new \InvalidArgumentException("zipCode is too long");
        }
        $this->parameters['billingAddress.zipCode'] = Normalizer::normalize($zipCode);
    }

    public function setBillingAddressCity($city)
    {
        if (strlen($city) > 25) {
            throw new \InvalidArgumentException("city is too long");
        }
        $this->parameters['billingAddress.city'] = Normalizer::normalize($city);
    }

    public function setBillingContactPhone($phone)
    {
        if (strlen($phone) > 30) {
            throw new \InvalidArgumentException("phone is too long");
        }
        $this->parameters['billingContact.phone'] = $phone;
    }

    public function setBillingContactFirstname($firstname)
    {
        $this->parameters['billingContact.firstname'] = str_replace(array("'", '"'), '', Normalizer::normalize($firstname)); // replace quotes
    }

    public function setBillingContactLastname($lastname)
    {
        $this->parameters['billingContact.lastname'] = str_replace(array("'", '"'), '', Normalizer::normalize($lastname)); // replace quotes
    }
    
    public function setCaptureDay($number)
    {
        if (strlen($number) > 2) {
            throw new \InvalidArgumentException("captureDay is too long");
        }
        $this->parameters['captureDay'] = $number;
    }
    
    public function setCaptureMode($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("captureMode is too long");
        }
        $this->parameters['captureMode'] = $value;
    }
    
    public function setMerchantTransactionDateTime($value)
    {
        if (strlen($value) > 25) {
            throw new \InvalidArgumentException("merchantTransactionDateTime is too long");
        }
        $this->parameters['merchantTransactionDateTime'] = $value;
    }
    
    public function setInterfaceVersion($value)
    {
        $this->parameters['interfaceVersion'] = $value;
    }
    
    public function setOrderChannel($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("orderChannel is too long");
        }
        $this->parameters['orderChannel'] = $value;
    }
    
    public function setOrderId($value)
    {
        if (strlen($value) > 32) {
            throw new \InvalidArgumentException("orderId is too long");
        }
        $this->parameters['orderId'] = $value;
    }
    
    public function setReturnContext($value)
    {
        if (strlen($value) > 255) {
            throw new \InvalidArgumentException("returnContext is too long");
        }
        $this->parameters['returnContext'] = $value;
    }
    
    public function setTransactionOrigin($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("transactionOrigin is too long");
        }
        $this->parameters['transactionOrigin'] = $value;
    }
        
    // Methodes liees a la carte
    public function setCardNumber($number)
    {
        if (strlen($number) > 19) {
            throw new \InvalidArgumentException("cardNumber is too long");
        }
        if (strlen($number) < 12) {
            throw new \InvalidArgumentException("cardNumber is too short");
        }
        $this->parameters['cardNumber'] = $number;
    }
    
    public function setCardExpiryDate($date)
    {
        if (strlen($date) != 6) {
            throw new \InvalidArgumentException("cardExpiryDate value is invalid");
        }
        $this->parameters['cardExpiryDate'] = $date;
    }
    
    public function setCardCSCValue($value)
    {
        if (strlen($value) > 4) {
            throw new \InvalidArgumentException("cardCSCValue value is invalid");
        }
        $this->parameters['cardCSCValue'] = $value;
    }
    
    // Methodes liees a la lutte contre la fraude
    
    public function setFraudDataBypass3DS($value)
    {
        if (strlen($value) > 128) {
            throw new \InvalidArgumentException("fraudData.bypass3DS is too long");
        }
        $this->parameters['fraudData.bypass3DS'] = $value;
    }
    
    // Methodes liees au paiement one-click
    
    public function setMerchantWalletId($wallet)
    {
        if (strlen($wallet) > 21) {
            throw new \InvalidArgumentException("merchantWalletId is too long");
        }
        $this->parameters['merchantWalletId'] = $wallet;
    }
    
    public function setPaymentMeanId($value)
    {
        if (strlen($value) > 6) {
            throw new \InvalidArgumentException("paymentMeanId is too long");
        }
        $this->parameters['paymentMeanId'] = $value;
    }
        
    // Methodes liees au paiement en n-fois
    
    public function setInstalmentData(array $data)
    {
        $this->parameters['instalmentData'] = $data;
    }
    
    public function setPaymentPattern($paymentPattern)
    {
        $this->parameters['paymentPattern'] = $paymentPattern;
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 3) == 'set') {
            $field = lcfirst(substr($method, 3));
            if (in_array($field, $this->pspFields)) {
                $this->parameters[$field] = $args[0];
                return;
            }
        }

        if (substr($method, 0, 3) == 'get') {
            $field = lcfirst(substr($method, 3));
            if (array_key_exists($field, $this->parameters)) {
                return $this->parameters[$field];
            }
        }

        throw new \BadMethodCallException("Unknown method $method");
    }

    public function toArray()
    {
        global $parameters;
        return $this->parameters;
    }

    public function toParameterString()
    {
        $this->parameters = $this->mulsort($this->parameters);
        if (self::TRACE == 1) {
            echo "<pre>";
            print_r($this->parameters);
            echo "</pre><br>";
        }
        $seal = $this->getShaSign();
        $this->parameters['seal'] = $seal;
        $chaine = json_encode($this->parameters);
        if (self::TRACE == 1) {
            echo "JSON envoy&eacute; : " . $chaine . "<br>";
        }
        return $chaine;
    }

    /**
     * @return PaymentRequest
     */
    public static function createFromArray(ShaComposer $shaComposer, array $parameters)
    {
        $instance = new static($shaComposer);
        foreach ($parameters as $key => $value) {
            $instance->{"set$key"}($value);
        }
        return $instance;
    }

    public function validate()
    {
        if ($this->pspRequest == self::REQUEST_CARD_ORDER) {
            foreach ($this->requiredFieldsCardOrder as $field) {
                if (empty($this->parameters[$field])) {
                    throw new \RuntimeException($field . " can not be empty");
                }
            }
        }
    
        if ($this->pspRequest == self::REQUEST_WALLET_ORDER) {
            foreach ($this->requiredFieldsWalletOrder as $field) {
                if (empty($this->parameters[$field])) {
                    throw new \RuntimeException($field . " can not be empty");
                }
            }
        }
    }

    protected function validateUri($uri)
    {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Uri is not valid");
        }
        if (strlen($uri) > 200) {
            throw new \InvalidArgumentException("Uri is too long");
        }
    }
    
    // Traitement des reponses de Mercanet
    // -----------------------------------
    
    /**
 * @var string
*/
    const SHASIGN_FIELD = "SEAL";

    /**
 * @var string
*/
    const DATA_FIELD = "DATA";

    public function setResponse(array $httpRequest)
    {
        // use lowercase internally
        $httpRequest = array_change_key_case($httpRequest, CASE_UPPER);

        // set sha sign
        $this->shaSign = $this->extractShaSign($httpRequest);

        // filter request for Sips parameters
        $this->parameters = $this->filterRequestParameters($httpRequest);
    }
    
    /**
     * @var string
     */
    private $shaSign;

    private $dataString;
    
    private $responseRequest;
    private $responseStatus;
    private $parameterArray;
    
    /**
     * Filter http request parameters
     *
     * @param array $requestParameters
     */
    private function filterRequestParameters(array $httpRequest)
    {
        //filter request for Sips parameters
        if (!array_key_exists(self::DATA_FIELD, $httpRequest) || $httpRequest[self::DATA_FIELD] == '') {
            throw new \InvalidArgumentException('Data parameter not present in parameters.');
        }
        $parameters = array();
        $dataString = $httpRequest[self::DATA_FIELD];
        $this->dataString = $dataString;
        $dataParams = explode('|', $dataString);
        foreach ($dataParams as $dataParamString) {
            $dataKeyValue = explode('=', $dataParamString, 2);
            $parameters[$dataKeyValue[0]] = $dataKeyValue[1];
        }

        return $parameters;
    }

    public function getSeal()
    {
        return $this->shaSign;
    }

    private function extractShaSign(array $parameters)
    {
        if (!array_key_exists(self::SHASIGN_FIELD, $parameters) || $parameters[self::SHASIGN_FIELD] == '') {
            throw new \InvalidArgumentException('SHASIGN parameter not present in parameters.');
        }
        return $parameters[self::SHASIGN_FIELD];
    }

    function mulsort(array $tab)
    {
        if (is_array($tab)) {
            ksort($tab);
        }
        foreach ($tab as $key => $val) {
            if (is_array($val)) {
                $tab[$key] = $this->mulsort($val);
            }
        }
        return $tab;
    }

    /**
     * Checks if the response is valid
     *
     * @param  ShaComposer $shaComposer
     * @return bool
     */
    public function isValid()
    {
        global $shaString;

        $resultat = false;
        if ($this->responseStatus) {
            $this->parameterArray = json_decode($this->responseRequest, true);
            
            $result = $this->mulsort($this->parameterArray);
            $this->shaString = "";
            if (self::TRACE == 1) {
                echo "<br><br>JSON array received = <pre>" . print_r($result, true) . "</pre><br><br>";
            }
            array_walk_recursive($result, [$this, 'shaCompose']);
            $compute = hash_hmac('sha256', utf8_encode($this->shaString), $this->secretKey);
    
            if (self::TRACE == 1) {
                echo "<br>signature compute = " . $compute . " from " . $this->shaString . "<br><br>signature received = " . $result['seal'] . "<br><br>";
            }
            if ($compute == $result['seal']) {
                if ((strcmp($result['responseCode'], "00") == 0) || (strcmp($result['responseCode'], "60") == 0)) {
                    $resultat = true;
                }
            }
        }
        return $resultat;
    }

    /**
     * Retrieves a response parameter
     *
     * @param  string $param
     * @throws \InvalidArgumentException
     */
    public function getParam($key)
    {
        return $this->parameterArray[$key];
    }
    
    public function getResponseRequest()
    {
        return $this->responseRequest;
    }
    
    public function executeRequest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->toParameterString());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept:application/json'));
        curl_setopt($ch, CURLOPT_PORT, 443);
        $this->responseRequest = curl_exec($ch);
        $info = curl_getinfo($ch);
        // Manage errors
        if ($this->responseRequest == false || $info['http_code'] != 200) {
            if (curl_error($ch)) {
                $this->responseRequest .= "\n". curl_error($ch);
            }
            $this->responseStatus = false;
        } else {
            $this->responseStatus = true;
        }
        curl_close($ch);
    }
}
