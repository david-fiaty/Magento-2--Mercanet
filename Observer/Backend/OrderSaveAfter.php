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
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Http\Client;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Model\Service\TransactionHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Core;

class OrderSaveAfter implements ObserverInterface { 

    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TransactionHandlerService
     */
    protected $transactionHandler;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        Session $backendAuthSession,
        Client $client,
        Config $config,
        TransactionHandlerService $transactionHandler
    ) { 
        $this->backendAuthSession = $backendAuthSession;
        $this->client             = $client;
        $this->config             = $config;
        $this->transactionHandler = $transactionHandler;
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saveafter.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('test');

        if ($this->backendAuthSession->isLoggedIn()) {
            // Get the order
            $order = $observer->getEvent()->getOrder();

            // Get the transaction id
            $paymentInfo = $order->getPayment()->getMethodInstance()->getInfoInstance();
            $transactionId = $paymentInfo->getData()
            [Connector::KEY_ADDITIONAL_INFORMATION]
            [Connector::KEY_TRANSACTION_INFO]
            [$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]];

            // Get the method id
            $methodId = $order->getPayment()->getMethodInstance()->getCode();

            // Prepare the order data
            $fields = [
                $this->config->base[Connector::KEY_ORDER_ID_FIELD]       => $order->getIncrementId(),
                $this->config->base[Connector::KEY_TRANSACTION_ID_FIELD] => $transactionId,
                $this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD] => $order->getCustomerEmail(),
                $this->config->base[Connector::KEY_CAPTURE_MODE_FIELD]   => $this->config->params[$methodId][Connector::KEY_CAPTURE_MODE],
                Core::KEY_METHOD_ID                                      => $methodId
            ];

            // Handle the transactions
            if ($this->config->params[$methodId][Connector::KEY_CAPTURE_MODE] == Connector::KEY_CAPTURE_IMMEDIATE) {
                $captureTransactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_CAPTURE, $methodId);
            }
            else {
                $authorizationTransactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_AUTH, $methodId);
            }
        }

        return $this;
    }
}