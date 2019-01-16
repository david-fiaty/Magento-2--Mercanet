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
     * @var RequestInterface
     */
    protected $request;

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
        RequestInterface $request,
        Client $client,
        TransactionHandlerService $transactionHandler,
        Config $config
    ) { 
        $this->backendAuthSession = $backendAuthSession;
        $this->request            = $request;
        $this->client             = $client;
        $this->transactionHandler = $transactionHandler;
        $this->config             = $config;
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer) { 
        if ($this->backendAuthSession->isLoggedIn()) {
            // Get the order
            $order = $observer->getEvent()->getOrder();

            // Get the method id
            $methodId = $order->getPayment()->getMethodInstance()->getCode();

            // Prepare the order data
            $fields = [
                $this->config->base[Connector::KEY_ORDER_ID_FIELD]       => $order->getIncrementId(),
                $this->config->base[Connector::KEY_TRANSACTION_ID_FIELD] => 'test_12345',
                $this->config->base[Connector::KEY_CUSTOMER_EMAil_FIELD] => 'dfiaty@gmail.com',
                $this->config->base[Connector::KEY_CAPTURE_MODE_FIELD]           => $this->config->params[$methodId][Connector::KEY_CAPTURE_MODE],
                Core::KEY_METHOD_ID                                      => $methodId
            ];

            // Create the auth transaction
            $authorizationTransactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_AUTH, $methodId);

            // Create the capture transaction if needed
            if ($this->config->params[$methodId][Connector::KEY_CAPTURE_MODE_FIELD]  == Connector::KEY_CAPTURE_IMMEDIATE) {
                $captureTransactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_CAPTURE, $methodId);
            }

            // Update order status
            $order->setStatus($this->params[Core::moduleId()][Connector::KEY_ORDER_STATUS_AUTHORIZED]);
        }

        return $this;
    }
}