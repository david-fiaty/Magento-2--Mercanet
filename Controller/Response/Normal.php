<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Response;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Model\Service\OrderHandlerService;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Model\Service\QuoteHandlerService;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Config\Config;

class Normal extends Action {
    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;
    
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Connector
     */
    protected $processor;

    /**
     * @var QuoteHandlerService
     */
    protected $quoteHandler;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        Tools $tools,
        OrderHandlerService $orderHandler,
        CheckoutSession $checkoutSession,
        Connector $processor,
        QuoteHandlerService $quoteHandler,
        ManagerInterface $messageManager,
        Watchdog $watchdog,
        Config $config
    ) {
        parent::__construct($context);

        $this->tools                 = $tools;
        $this->orderHandler          = $orderHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->processor             = $processor;
        $this->quoteHandler          = $quoteHandler;
        $this->messageManager        = $messageManager;
        $this->watchdog              = $watchdog;
        $this->config                = $config;
    }
 
    public function execute() {
        // Get the request data
        $responseData = $this->tools->getInputData();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = true, $canLog = false);

        // Check validity
        if ($this->processor->isValid($responseData, $this->config)) {
            if ($this->processor->isSuccess($responseData)) {
                // Place order
                $order = $this->orderHandler->placeOrder($responseData);

                // Process the order result
                if (isset($order) && (int)$order->getId() > 0) {
                    // Get the fields
                    $fields = $this->tools->unpackData($responseData['Data'], '|', '=');

                    // Find the quote
                    $quote = $this->quoteHandler->findQuote($fields['orderId']);

                    // Set the success redirection parameters
                    if (isset($quote) && (int)$quote->getId() > 0) {
                        // Prepare session quote info for redirection after payment
                        $this->checkoutSession
                        ->setLastQuoteId($quote->getId())
                        ->setLastSuccessQuoteId($quote->getId())
                        ->clearHelperData();

                        // Prepare session order info for redirection after payment
                        $this->checkoutSession->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastOrderStatus($order->getStatus());

                        // Display a success message
                        $this->messageManager->addSuccessMessage(__('The order was placed successfully.'));

                        // Redirect to the success page
                        return $this->_redirect('checkout/onepage/success', ['_secure' => true]);
                    } else {
                        $this->watchdog->log(__('The quote could not be found.'));
                    }
                } else {
                    $this->watchdog->log(__('The order could not be created.'));
                }
            }
            else {
                $this->watchdog->log(__('The transaction could not be processed. Please try again.'));
            }
        }
        else {
            $this->watchdog->log(__('Invalid gateway response.'));
        }

        // Restore the cart
        // todo - rebuild the cart on failure
        //$this->quoteHandler->restoreCart();

        // Redirect to the cart by default
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }
}