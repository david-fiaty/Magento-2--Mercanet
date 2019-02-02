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
 
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class Form extends \Magento\Framework\App\Action\Action {
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
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Cmsbox\Mercanet\Model\Service\MethodHandlerService $methodHandler,
        \Cmsbox\Mercanet\Gateway\Config\Config $config,
        \Cmsbox\Mercanet\Model\Service\OrderHandlerService $orderHandler,
        \Cmsbox\Mercanet\Helper\Tools $tools,
        \Cmsbox\Mercanet\Helper\Watchdog $watchdog
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

            return $this->jsonFactory->create()->setData([Connector::KEY_RESPONSE => $response]);
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
                    // Process the payment
                    $paymentObject = $methodInstance::getRequestData($this->config, $methodId, $cardData);

                    // Log the response
                    $methodInstance::logResponseData(Connector::KEY_RESPONSE, $this->watchdog, $paymentObject);

                    // Process the response
                    if ($methodInstance::isValidResponse($this->config, $methodId, $paymentObject) && $methodInstance::isSuccessResponse($this->config, $methodId, $paymentObject)) {
                        // Get the quote
                        $quote = $this->orderHandler->findQuote();

                        // Prepare the order data
                        $params = Connector::packData([
                            $this->config->base[Connector::KEY_ORDER_ID_FIELD]       => $this->tools->getIncrementId($quote),
                            $this->config->base[Connector::KEY_TRANSACTION_ID_FIELD] => $methodInstance::getTransactionId($this->config, $paymentObject),
                            $this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD] => isset($response[$this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD]])
                                ? $response[$this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD]]
                                : $this->orderHandler->findCustomerEmail($quote),
                            $this->config->base[Connector::KEY_CAPTURE_MODE_FIELD]   => $this->config->params[$methodId][Connector::KEY_CAPTURE_MODE]
                        ]);

                        // Place the order
                        $order = $this->orderHandler->placeOrder($params, $methodId);

                        // Perform after place order actions
                        $this->orderHandler->afterPlaceOrder($quote, $order);

                        // Return the result
                        return true;
                    }
                }

                return __('The transaction data is invalid.');
            } 
        }
        catch (\Exception $e) {
            $this->watchdog->logError($e);
            return __($e->getMessage());
        }
    }

    private function runBlock() {
        // Retrieve the expected parameters
        $methodId = $this->getRequest()->getParam('method_id', null);
        $template = $this->config->params[$methodId][Connector::KEY_FORM_TEMPLATE];

        // Create the block
        return $this->pageFactory->create()->getLayout()
        ->createBlock(Core::moduleClass() . '\Block\Payment\Form')
        ->setData('area', 'adminhtml')
        ->setTemplate(Core::moduleName() . '::payment_form/' . $template . '.phtml')
        ->setData('method_id', $methodId)
        ->setData('module_name', Core::moduleName())
        ->setData('template_name', $template)
        ->setData('is_admin', false)
        ->toHtml();
    }
}