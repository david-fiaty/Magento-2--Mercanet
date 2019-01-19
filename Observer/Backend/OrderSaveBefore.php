<?php
/**
 * Checkout.com Magento 2 Payment module (https://www.checkout.com)
 *
 * Copyright (c) 2017 Checkout.com (https://www.checkout.com)
 * Author: David Fiaty | integration@checkout.com
 *
 * MIT License
 */

namespace Cmsbox\Mercanet\Observer\Backend;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ObserverInterface; 
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Model\Service\MethodHandlerService;

class OrderSaveBefore implements ObserverInterface { 
 
    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MethodHandlerService
     */
    protected $methodHandler;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        Session $backendAuthSession,
        Http $request,
        Tools $tools,
        Config $config,
        MethodHandlerService $methodHandler
    ) {
        $this->backendAuthSession    = $backendAuthSession;
        $this->request               = $request;
        $this->tools                 = $tools;
        $this->config                = $config;
        $this->methodHandler         = $methodHandler;

        // Get the request parameters
        $this->params = $this->request->getParams();
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 
        if ($this->backendAuthSession->isLoggedIn()) {
            try {
                // Get the request parameters
                $params = $this->request->getParams();

                // Prepare the method id
                $methodId = $params['payment']['method'] ?? null;

                // Prepare the card data
                $cardData = $params['card_data'] ?? null;

                // Get the order
                $order = $observer->getEvent()->getOrder();

                // Get the payment info instance
                $paymentInfo = $order->getPayment()->getMethodInstance()->getInfoInstance();

                // Load the method instance if parameters are valid
                if ($methodId && is_array($cardData) && !empty($cardData)) {
                    // Load the method instance
                    $methodInstance = $this->methodHandler->getStaticInstance($methodId);

                    // Perform the charge request
                    if ($methodInstance) {
                        // Get the request object
                        $paymentRequest = $methodInstance::getRequestData($this->config, $methodId, $cardData, $order);

                        // Execute the request
                        $paymentRequest->executeRequest();

                        // Get the response
                        if ($paymentRequest->isValid()) {
                            $paymentInfo->setAdditionalInformation(
                                Connector::KEY_TRANSACTION_INFO,
                                [$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD] => $paymentRequest->getParam($this->config->base[Connector::KEY_TRANSACTION_ID_FIELD])]
                            );
                        }
                        else {
                            throw new \Magento\Framework\Exception\LocalizedException(__('The transaction could not be processed'));
                        }
                    }
                }
            }
            catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }

        return $this;
    }

}