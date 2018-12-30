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
     * OrderSaveBefore constructor.
     */
    public function __construct(
        Session $backendAuthSession,
        Http $request,
        Tools $tools
    ) {
        $this->backendAuthSession    = $backendAuthSession;
        $this->request               = $request;
        $this->tools                 = $tools;

        // Get the request parameters
        $this->params = $this->request->getParams();
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 
        if ($this->backendAuthSession->isLoggedIn()) {
            // Get the order
            $order = $observer->getEvent()->getOrder();

            /*
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/before.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(print_r($order->getData(), 1));
            */
        }
    }

}