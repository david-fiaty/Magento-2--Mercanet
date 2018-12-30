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

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart;

class QuoteHandlerService {

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
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * QuoteHandlerService constructor.
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        Cart $cart
    ) {
        $this->cookieManager   = $cookieManager;
        $this->quoteFactory    = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cart            = $cart;
    }

    /**
     * Sets the email for guest users
     */
    public function prepareGuestQuote($quote, $email = null) {
        // Retrieve the user email
        $guestEmail = $email
        ?? $quote->getCustomerEmail()
        ?? $quote->getBillingAddress()->getEmail()
        ?? $this->cookieManager->getCookie(self::EMAIL_COOKIE_NAME);

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
     * Find a quote
     */
    public function findQuote($orderId) {
        // Check if the quote exists
        $quote = $this->quoteFactory
        ->create()->getCollection()
        ->addFieldToFilter('reserved_order_id', $orderId)
        ->getFirstItem();

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
}
