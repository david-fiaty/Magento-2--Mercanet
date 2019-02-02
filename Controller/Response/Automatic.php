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
 
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Automatic extends \Magento\Framework\App\Action\Action {
    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Automatic constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cmsbox\Mercanet\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cmsbox\Mercanet\Helper\Watchdog $watchdog,
        \Cmsbox\Mercanet\Gateway\Config\Config $config
    ) {
        parent::__construct($context);
        
        $this->orderHandler        = $orderHandler;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
    }
 
    public function execute() {
        // Get the request data
        $responseData = $this->getRequest()->getPostValue();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler->getStaticInstance($methodId);

        // Process the response
        if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
            if ($methodInstance::isValidResponse($this->config, $methodId, $responseData)) {
                if ($methodInstance::isSuccessResponse($this->config, $responseData)) {
                    // Place order
                    $order = $this->orderHandler->placeOrder($responseData, $methodId);
                }
            }
        }

        // Stop the execution
        return $this->resultJsonFactory->create()->setData([]);
    }
}