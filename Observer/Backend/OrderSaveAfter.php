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
use Magento\Framework\App\RequestInterface;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Http\Client;
//use Cmsbox\Mercanet\Model\Service\MethodHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Config;

class OrderSaveAfter implements ObserverInterface { 
 
    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Connector
     */
    protected $processor;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var MethodHandlerService
     */
    protected $methodHandler;

    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        Session $backendAuthSession,
        RequestInterface $request,
        Connector $processor,
        Client $client,
        //MethodHandlerService $methodHandler,
        Config $config
    ) { 
        $this->backendAuthSession = $backendAuthSession;
        $this->request            = $request;
        $this->processor          = $processor;
        $this->client             = $client;
        //$this->methodHandler      = $methodHandler;
        $this->config             = $config;
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 
        if ($this->backendAuthSession->isLoggedIn()) {
            // Get the order and method id
            $order = $observer->getEvent()->getOrder();
            $methodId = $order->getPayment()->getMethodInstance()->getCode();
            //$methodInstance = $this->methodHandler->getMethodInstance($methodId);

            // Prepare the curl options
            $this->client->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->client->setOption(CURLOPT_POST, true);
            $this->client->setOption(CURLOPT_PORT, 443);

            // Prepare the url
            //$url = $methodInstance->getApiUrl('charge');

            // Get the card data
            $post = $this->request->getPostValue();
            //$data = $methodInstance->getRequestData->getRequestData($order, $post['card_data']);

            // Retrieve the response
            $responseData =  $this->client->getPostResponse($url, $data);

            // Process the response
            if ($this->processor->isValid($responseData, $this->config) && $this->processor->isSuccess($responseData)) {
                // Todo - Add payment success info and update order
            }

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/backorder.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($url . "\n");
            $logger->info(print_r($responseData ,1) . "\n");

            exit('dd');
            // Todo - Retrieve the response and place the order
        }
    }
}