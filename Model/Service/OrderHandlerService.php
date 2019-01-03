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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Cart;
use Cmsbox\Mercanet\Model\Service\TransactionHandlerService;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Helper\Watchdog;

class OrderHandlerService {

    const EMAIL_COOKIE_NAME = 'guestEmail';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var TransactionHandlerService
     */
    protected $transactionHandler;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * OrderHandlerService constructor.
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        QuoteFactory $quoteFactory,
        Cart $cart,
        TransactionHandlerService $transactionHandler,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        QuoteManagement $quoteManagement, 
        OrderSender $orderSender,
        OrderRepositoryInterface $orderRepository,
        OrderInterface $orderInterface,
        Watchdog $watchdog
    ) {
        $this->cookieManager         = $cookieManager;
        $this->quoteFactory          = $quoteFactory;
        $this->cart                  = $cart;
        $this->transactionHandler    = $transactionHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->customerSession       = $customerSession;
        $this->quoteManagement       = $quoteManagement;
        $this->orderSender           = $orderSender;
        $this->orderRepository       = $orderRepository;
        $this->orderInterface        = $orderInterface;
        $this->watchdog              = $watchdog;
    }

    public function placeOrder($data = null) {
        // Get the fields
        $fields = Connector::unpackData($data);

        // If a track id is available
        if (isset($fields[Connector::KEY_ORDER_ID_FIELD])) {
            // Check if the order exists
            $order = $this->orderInterface->loadByIncrementId($fields[Connector::KEY_ORDER_ID_FIELD]);

            // Update the order
            if ($order) {
                $order = $this->createOrder($fields);
                return $order;
            }
        }

        // Fraud check
        // Todo - Handle fraud info
        //$order = $this->processor->checkFraud($order);

        return null;
    }

    public function createOrder($fields) {
        try {
            // Find the quote
            $quote = $this->findQuote($fields[Connector::KEY_ORDER_ID_FIELD]);

            // If there is a quote, create the order
            if ($quote->getId()) {
                // Prepare the inventory
                $quote->setInventoryProcessed(false);

                // Check for guest user quote
                if ($this->customerSession->isLoggedIn() === false) {
                    $quote = $this->prepareGuestQuote($quote, $fields[Connector::KEY_CUSTOMER_EMAIL_FIELD]);
                }

                // Set the payment information
                $payment = $quote->getPayment();
                $payment->setMethod($fields[Core::KEY_METHOD_ID]);
                $payment->save();

                // Create the order
                $order = $this->quoteManagement->submit($quote);

                // Update order status
                if ($fields[Connector::KEY_CAPTURE_MODE_FIELD] == Connector::KEY_CAPTURE_IMMEDIATE) {
                    // Create the transaction
                    $transactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_CAPTURE, $fields[Core::KEY_METHOD_ID]);
                } else {
                    // Update order status
                    $order->setStatus($this->params[Core::moduleId()][Connector::KEY_ORDER_STATUS_AUTHORIZED]);

                    // Create the transaction
                    $transactionId = $this->transactionHandler->createTransaction($order, $fields, Transaction::TYPE_AUTH, $fields[Core::KEY_METHOD_ID]);
                }

                // Save the order
                $this->orderRepository->save($order);

                // Send the email
                $this->orderSender->send($order);
                
                return $order;
            }
        } catch (\Exception $e) {
            $this->watchdog->log($e);
            return false;
        }
    }

    /**
     * Sets the email for guest users
     */
    public function prepareGuestQuote($quote, $email = null) {
        // Retrieve the user email
        $guestEmail = ($email) ? $email : $this->findCustomerEmail();

        // Set the quote as guest
        $quote->setCustomerId(null)
        ->setCustomerEmail($guestEmail)
        ->setCustomerIsGuest(true)
        ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        // Delete the cookie
        $this->cookieManager->deleteCookie(self::EMAIL_COOKIE_NAME);

        // Return the quote
        return $quote;
    }

    /**
     * Restore a quote
     */
    public function restoreCart() {
        $order = $this->checkoutSession->getLastRealOrder();
        $quote = $this->findQuote($order->getQuoteId());
        if ($quote->getId()) {
            $quote->setIsActive(1)->setReservedOrderId(null)->save();
        }
    }

    public function afterPlaceOrder($quote, $order) {
        // Prepare session quote info for redirection after payment
        $this->checkoutSession
        ->setLastQuoteId($quote->getId())
        ->setLastSuccessQuoteId($quote->getId())
        ->clearHelperData();

        // Prepare session order info for redirection after payment
        $this->checkoutSession->setLastOrderId($order->getId())
        ->setLastRealOrderId($order->getIncrementId())
        ->setLastOrderStatus($order->getStatus());
    } 

    public function findCustomerEmail($quote) {
        return $quote->getCustomerEmail()
        ?? $quote->getBillingAddress()->getEmail()
        ?? $this->cookieManager->getCookie(self::EMAIL_COOKIE_NAME);
    }

    /**
     * Find a quote
     */
    public function findQuote($orderId = null) {
        if ($orderId) {
            return $this->quoteFactory
            ->create()->getCollection()
            ->addFieldToFilter('reserved_order_id', $orderId)
            ->getFirstItem();
        }

        return $this->cart->getQuote();
    }
}