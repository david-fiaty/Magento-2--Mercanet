<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Response\Payment;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Model\Service\OrderHandlerService;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Config\Config;

class Automatic extends Action
{
    /**
     * @var Tools
     */    
    protected $tools;

    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;

    /**
     * @var Connector
     */
    protected $processor;

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
        Context $context,
        Tools $tools,
        OrderHandlerService $orderHandler,
        Connector $processor,
        JsonFactory $resultJsonFactory,
        Watchdog $watchdog,
        Config $config
    ) {
        parent::__construct($context);
        
        $this->tools               = $tools;
        $this->orderHandler        = $orderHandler;
        $this->processor           = $processor;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
    }
 
    public function execute() {
        // Get the request data
        $responseData = $this->tools->getInputData();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Check validity
        // Todo - check isvalid function
        if ($this->processor->isValid($responseData, $this->config) && $this->processor->isSuccess($responseData)) {    
            // Place order
            $order = $this->orderHandler->placeOrder($responseData);
        }

        // Stop the execution
        return $this->resultJsonFactory->create()->setData([]);
    }
}