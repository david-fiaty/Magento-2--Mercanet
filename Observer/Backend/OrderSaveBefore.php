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
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

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
     * OrderSaveBefore constructor.
     */
    public function __construct(
        Session $backendAuthSession,
        Http $request,
        Tools $tools,
        Config $config
    ) {
        $this->backendAuthSession    = $backendAuthSession;
        $this->request               = $request;
        $this->tools                 = $tools;
        $this->config                = $config;

        // Get the request parameters
        $this->params = $this->request->getParams();
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 
        if ($this->backendAuthSession->isLoggedIn()) {
            // Get the request parameters
            $params = $this->request->getParams();

            // Prepare the method id
            $methodId = $params['payment']['method'] ?? null;

            // Prepare the card data
            $cardData = $params['card_data'] ?? null;

            // Get the order
            $order = $observer->getEvent()->getOrder();

            var_dump($methodId);
            var_dump($cardData);
            var_dump($params);

            // Load the method instance if parameters are valid
            if ($methodId && is_array($cardData) && !empty($cardData)) {
               // Load the method instance
               $methodInstance = $this->methodHandler->getStaticInstance($methodId);

                // Perform the charge request
                if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
                    // Get the request object
                    $paymentRequest = $methodInstance::getRequestData($this->config, $methodId, $cardData);

                    // Execute the request
                    $paymentRequest->executeRequest();

                    // Get the response
                    $response = Connector::prepareResponse($paymentRequest->getResponseRequest());

                    var_dump($response);

                }
            }

            exit();

        }
    }

}