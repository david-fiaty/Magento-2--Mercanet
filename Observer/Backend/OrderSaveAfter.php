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
        }
    }
}