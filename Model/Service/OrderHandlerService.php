<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Model\Service\TransactionHandlerService;
use Cmsbox\Mercanet\Model\Adminhtml\Source\CaptureMode;
use Cmsbox\Mercanet\Model\Service;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Model\Service\QuoteHandlerService;
use Cmsbox\Mercanet\Helper\Watchdog;

class OrderHandlerService {

    /**
     * @var TransactionHandlerService
     */
    protected $transactionService;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var Connector
     */
    protected $processor;

    /**
     * @var QuoteHandlerService
     */
    protected $quoteService;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * OrderHandlerService constructor.
     * @param TransactionHandlerService $transactionService
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     * @param OrderSender $orderSender
     * @param Tools $tools
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     * @param OrderInterface $orderInterface
     * @param Connector $processor
     * @param QuoteHandlerService $quoteService
     * @param Watchdog $watchdog
     */
    public function __construct(
        TransactionHandlerService $transactionService,
        CheckoutSession $checkoutSession,
        Config $config,
        CustomerSession $customerSession,
        QuoteManagement $quoteManagement, 
        OrderSender $orderSender,
        Tools $tools,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,                   
        OrderInterface $orderInterface,
        Connector $processor,
        QuoteHandlerService $quoteService,
        Watchdog $watchdog
    ) {
        $this->transactionService    = $transactionService;
        $this->checkoutSession       = $checkoutSession;
        $this->customerSession       = $customerSession;
        $this->quoteManagement       = $quoteManagement;
        $this->config                = $config;
        $this->orderSender           = $orderSender;
        $this->tools                 = $tools;
        $this->orderRepository       = $orderRepository;
        $this->cartRepository        = $cartRepository;
        $this->orderInterface        = $orderInterface;
        $this->processor             = $processor;
        $this->quoteService          = $quoteService;
        $this->watchdog              = $watchdog;
    }

    public function placeOrder($data) {
        // Get the fields
        $fields = $this->tools->unpackData($data['Data'], '|', '=');

        // If a track id is available
        if (isset($fields['orderId'])) {
            // Check if the order exists
            $order = $this->orderInterface->loadByIncrementId($fields['orderId']);

            // Update the order
            if (!$order->getId()) {
                $order = $this->createOrder($data, $fields);
            }
        }

        // Fraud check
        $order = $this->processor->checkFraud($order);

        return $order;
    }

    public function createOrder($data, $fields) {
        try {
            // Find the quote
            $quote = $this->quoteService->findQuote($fields);

            // If there is a quote, create the order
            if ($quote->getId()) {
                // Prepare the inventory
                $quote->setInventoryProcessed(false);

                // Check for guest user quote
                if ($this->customerSession->isLoggedIn() === false) {
                    $quote = $this->quoteService->prepareGuestQuote($quote, $fields['customerEmail']);
                }

                // Set the payment information
                $payment = $quote->getPayment();
                $payment->setMethod($this->tools->modmeta['tag']);

                // Create the order
                $order = $this->quoteManagement->submit($quote);

                // Update order status
                if ($fields['captureMode'] == CaptureMode::IMMEDIATE) {
                    // Create the transaction
                    $transactionId = $this->transactionService->createTransaction($order, $fields, Transaction::TYPE_CAPTURE);
                } else {
                    // Update order status
                    $order->setStatus($this->config->getOrderStatusAuthorized());

                    // Create the transaction
                    $transactionId = $this->transactionService->createTransaction($order, $fields, Transaction::TYPE_AUTH);
                }

                // Save the order
                $this->orderRepository->save($order);

                // Send the email
                $this->orderSender->send($order);

                return $order;
            }
        } catch (\Exception $e) {
            $this->watchdog->log($e);
            return null; 
        }
    }
}