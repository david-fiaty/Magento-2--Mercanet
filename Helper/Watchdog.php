<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Watchdog {

    /**
     * @var ManagerInterface
     */
    protected $messageManager;
 
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Tools
     */ 
    protected $tools;

    /**
     * @var LoggerInterface
     */ 
    protected $logger;

    /**
     * Watchdog constructor.
     */
    public function __construct(
        ManagerInterface $messageManager,
        Config $config,
        Tools $tools,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->config         = $config;
        $this->tools          = $tools;
        $this->logger         = $logger;
    }

    /**
     * Display messages and write to custom log file.
     */
    public function bark($action, $data, $canDisplay = true, $canLog = true) {
        // Prepare the output
        $output = ($data) ? print_r($data, 1) : '';
        $output = strtoupper($action) . "\n" . $output;

        // Process file logging
        if ($this->config->params[Core::moduleId()]['logging'] && $canLog) {

            // Build the log file name
            $logFile = BP . '/var/log/' . Core::moduleId() . '_' . $action . '.log';

            // Write to the log file
            $writer = new \Zend\Log\Writer\Stream($logFile);
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($output);
        }

        // Process interface display
        if ($this->config->params[Core::moduleId()]['debug'] && $canDisplay) {
            $this->messageManager->addNoticeMessage($output);
        }
    } 

    /**
     * Write to system file.
     */
    public function logError($message, $canDisplay = true) {
        // Log to system log file
        if ((int) $this->config->params[Core::moduleId()]['logging'] == 1) {
            $output = Core::moduleId() . ' | ' . $message;
            $this->logger->log('ERROR', $output);
        }

        // Display if needed
        if ($canDisplay) {
            get_class($message) == 'Exception'
            ? $this->messageManager->addExceptionMessage($message, $message->getMessage())
            : $this->messageManager->addErrorMessage($message);            
        }
    }
}
