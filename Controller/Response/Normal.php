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
use Cmsbox\Mercanet\Model\Service\OrderHandlerService;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Model\Service\MethodHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Normal extends Action {
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
    protected $connector;

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
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        OrderHandlerService $orderHandler,
        CheckoutSession $checkoutSession,
        Connector $connector,
        ManagerInterface $messageManager,
        Watchdog $watchdog,
        Config $config,
        MethodHandlerService $methodHandler
    ) {
        parent::__construct($context);

        $this->orderHandler          = $orderHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->connector             = $connector;
        $this->messageManager        = $messageManager;
        $this->watchdog              = $watchdog;
        $this->config                = $config;
        $this->methodHandler         = $methodHandler;
    }
 
    public function execute() {
        // Get the request data
        $responseData = $this->getRequest()->getPostValue();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = true, $canLog = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler->getStaticInstance($methodId);

        // Process the response
        if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
            if ($methodInstance::isValidResponse($this->config, $methodId, $responseData)) {
                if ($methodInstance::isSuccessResponse($this->config, $methodId, $responseData)) {
                    // Place order
                    $order = $this->orderHandler->placeOrder($responseData);

                    // Process the order result
                    if (isset($order) && (int)$order->getId() > 0) {
                        // Get the fields
                        $fields = Connector::unpackData($responseData);

                        // Find the quote
                        $quote = $this->orderHandler->findQuote($fields[$this->config->base[Connector::KEY_ORDER_ID_FIELD]]);

                        // Set the success redirection parameters
                        if (isset($quote) && (int)$quote->getId() > 0) {
                            // Perform after place order actions
                            $this->orderHandler->afterPlaceOrder($quote, $order);

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
        }
        else {
            $this->watchdog->log(__('Invalid payment method.'));
        }

        // Restore the cart
        // todo - rebuild the cart on failure
        //$this->orderHandler->restoreCart();

        // Redirect to the cart by default
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }
}