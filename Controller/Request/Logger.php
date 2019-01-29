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
use Magento\Framework\Controller\Result\JsonFactory;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class Logger extends \Magento\Framework\App\Action\Action {
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Watchdog $watchdog
    ) {
        parent::__construct($context);

        $this->jsonFactory = $jsonFactory;
        $this->watchdog    = $watchdog;
    }
 
    public function execute() {
        if ($this->getRequest()->isAjax()) {
            // Get the request data
            $logData = $this->getRequest()->getParam('log_data');

            // Log the data
            $this->watchdog->bark(Connector::KEY_REQUEST, $logData, $canDisplay = false, $canLog = true);
        }

        return $this->jsonFactory->create()->setData([]);
    }
}