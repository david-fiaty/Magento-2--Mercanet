<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Request;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Model\Service\MethodHandlerService;
use Cmsbox\Mercanet\Model\Service\OrderHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Helper\Watchdog;
use Magento\Framework\Exception\LocalizedException;

class Form extends Action {

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        MethodHandlerService $methodHandler,
        Config $config,
        OrderHandlerService $orderHandler,
        Tools $tools,
        Watchdog $watchdog
    ) {
        parent::__construct($context);

        $this->pageFactory   = $pageFactory;
        $this->jsonFactory   = $jsonFactory;
        $this->methodHandler = $methodHandler;
        $this->config        = $config;
        $this->orderHandler  = $orderHandler;
        $this->tools         = $tools;
        $this->watchdog      = $watchdog;
    }
 
    public function execute() {
        if ($this->getRequest()->isAjax()) {
            switch ($this->getRequest()->getParam('task')) {
                case 'block':
                $response = $this->runBlock();
                break;

                case 'charge':
                $response = $this->runCharge();
                break;

                default:
                $response = $this->runBlock();
                break;
            }

            return $this->jsonFactory->create()->setData(['response' => $response]);
        }

        return $this->jsonFactory->create()->setData([]);
    }

    private function runCharge() {
        try {
            // Retrieve the expected parameters
            $methodId = $this->getRequest()->getParam('method_id', null);
            $cardData = $this->getRequest()->getParam('card_data', []);

            // Load the method instance if parameters are valid
            if ($methodId && !empty($methodId) && is_array($cardData) && !empty($cardData)) {
                // Load the method instance
                $methodInstance = $this->methodHandler->getStaticInstance($methodId);

                // Perform the charge request
                if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
                    // Get the request object
                    $paymentRequest = $methodInstance::getRequestData($this->config, $methodId, $cardData);

                    // Execute the request
                    $paymentRequest->executeRequest();

                    // Get the response
                    $paymentRequest->getResponseRequest();

                    // Process the response
                    if ($paymentRequest->isValid()) {
                        // Get the quote
                        $quote = $this->orderHandler->findQuote();

                        // Prepare the order data
                        // Todo - Replace the fields by config constants
                        $params = Connector::packData([
                            $this->config->base['order_id_field']          => $this->tools->getIncrementId($quote),
                            Connector::KEY_TRANSACTION_ID_FIELD            => $paymentRequest->getParam($this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]),
                            $this->config->base[Connector::KEY_CUSTOMER_EMAil_FIELD]    => isset($response[$this->config->base[Connector::KEY_CUSTOMER_EMAil_FIELD]])
                                ? $response[$this->config->base['customer_email_field']]
                                : $this->orderHandler->findCustomerEmail($quote),
                            $this->base[Connector::KEY_CAPTURE_MODE_FIELD] => $this->config->params[$methodId][Connector::KEY_CAPTURE_MODE],
                            Core::KEY_METHOD_ID                            => $methodId
                        ]);

                        // Place the order
                        $order = $this->orderHandler->placeOrder($params);

                        // Perform after place order actions
                        $this->orderHandler->afterPlaceOrder($quote, $order);

                        // Return the result
                        return true;
                    }
                }

                return __('Invalid method id or card data.');
            } 
        }
        catch (\Exception $e) {
            $this->watchdog->log($e);
            return __($e->getMessage());
        }
    }

    private function runBlock() {
    // Retrieve the expected parameters
    $methodId = $this->getRequest()->getParam('method_id', null);

    // Create the block
    return $this->pageFactory->create()->getLayout()
        ->createBlock(Core::moduleClass() . '\Block\Payment\Form')
        ->setTemplate(Core::moduleName() . '::payment_form.phtml')
        ->setData('method_id', $methodId)
        ->toHtml();
    }
}