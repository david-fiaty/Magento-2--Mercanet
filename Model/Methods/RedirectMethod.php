<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Methods;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class RedirectMethod extends AbstractMethod {

    protected $_code;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCancel = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;
    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder         = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->cart               = $cart;
        $this->_objectManager     = $objectManager;
        $this->invoiceSender      = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->checkoutData       = $checkoutData;
        $this->quoteRepository    = $quoteRepository;
        $this->quoteManagement    = $quoteManagement;
        $this->orderSender        = $orderSender;
        $this->sessionQuote       = $sessionQuote;
        $this->_code              = Core::methodId(get_class());
    }

    /**
     * Check whether method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
        return parent::isAvailable($quote) && null !== $quote;
    }

    public static function getRequestData($config, $methodId, $cardData = null, $entity = null) {
        // Get the order entity
        $entity = ($entity) ? $entity : $config->cart->getQuote();

        // Get the vendor instance
        $fn = "\\" . $config->params[$methodId][Core::KEY_VENDOR];
        $paymentRequest = new $fn($config->getSecretKey());

        // Prepare the request
        $paymentRequest->setMerchantId($config->getMerchantId());
        $paymentRequest->setKeyVersion($config->params[Core::moduleId()][Core::KEY_VERSION]);
        $paymentRequest->setTransactionReference($config->getTransactionReference());
        $paymentRequest->setAmount($config->formatAmount($entity->getGrandTotal()));
        $paymentRequest->setCurrency(Tools::getCurrencyCode($entity));
        $paymentRequest->setCustomerContactEmail($entity->getCustomerEmail());
        $paymentRequest->setOrderId(Tools::getIncrementId($entity));
        $paymentRequest->setCaptureMode($config->params[$methodId][Connector::KEY_CAPTURE_MODE]);
        $paymentRequest->setCaptureDay((string) $config->params[$methodId][Connector::KEY_CAPTURE_DAY]);
        $paymentRequest->setLanguage($config->getCustomerLanguage());
        $paymentRequest->setNormalReturnUrl(
            $config->storeManager->getStore()->getBaseUrl() 
            . '/' . Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_NORMAL_RETURN_URL]
        );    
        $paymentRequest->setAutomaticResponseUrl(
            $config->storeManager->getStore()->getBaseUrl() 
            . '/' . Core::moduleId() . '/' . $config->params[$methodId][Core::KEY_AUTOMATIC_RESPONSE_URL]
        );

        // Set the 3DS parameter
        if (!$config->params[$methodId][Core::KEY_VERIFY_3DS]) {
            $paymentRequest->setFraudDataBypass3DS($config->params[$methodId][Core::KEY_BYPASS_RECEIPT]);
        }

        // Todo  - add extra data
        // Set the billing address info
        /*
        $params = array_merge($params, $config->connector->getBillingAddress($entity));

        // Set the shipping address info
        $params = array_merge($params, $config->connector->getShippingAddress($entity));

        // Set the payment brands list
        $paymentBrands = $config->params[Core::moduleId()][Core::KEY_PAYMENT_BRANDS];
        if (!empty(explode(',', $paymentBrands))) {
            // Todo - check payment brand list with test mode
            //$params['paymentMeanBrandList'] = $paymentBrands;
        }
        */

        $paymentRequest->validate();

        return [
            'params' => $paymentRequest->toParameterString(),
            'seal' => $paymentRequest->getShaSign()
        ];
    }

    /**
     * Checks if a response is valid.
     *
     * @return bool
     */  
    public static function isValidResponse($config, $methodId, $asset) {
        // Get the vendor instance
        $fn = "\\" . $config->params[$methodId][Core::KEY_VENDOR];
        $paymentResponse = new $fn($config->getSecretKey());

        // Set the response
        $paymentResponse->setResponse($asset);
    
        // Return the validity status
        return $paymentResponse->isValid(); 
    }

    /**
     * Checks if a response is success.
     *
     * @return bool
     */  
    public static function isSuccessResponse($config, $methodId, $asset) {
        // Get the vendor instance
        $fn = "\\" . $config->params[$methodId][Core::KEY_VENDOR];
        $paymentResponse = new $fn($config->getSecretKey());

        // Set the response
        $paymentResponse->setResponse($asset);

        // Return the success status
        return $paymentResponse->isSuccessful();      
    }

    /**
     * Determines if the method is active on frontend.
     *
     * @return bool
     */
    public static function isFrontend($config, $methodId) {
        // Get the quote entity
        $entity = $config->cart->getQuote();

        // Check the currency status
        $currencyAccepted = in_array(
            $entity->getQuoteCurrencyCode(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_CURRENCIES])
        );

        // Check the billing country status
        $countryBillingAccepted = in_array(
            $entity->getBillingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES_BILLING])
        );

        // Check the shipping country status
        $countryShippingAccepted = in_array(
            $entity->getShippingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES_SHIPPING])
        );

        return (int) (((int)  $config->params[$methodId][Connector::KEY_ACTIVE] == 1)
        && $currencyAccepted
        && $countryBillingAccepted);
        // todo - check why this option not saving
        //&& $countryShippingAccepted;
    }
}