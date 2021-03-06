<?php
/**
 * Cmsbox.fr Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Mercanet
  * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Mercanet\Controller\Response;
 
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Automatic extends \Magento\Framework\App\Action\Action
{
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
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * Automatic constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cmsbox\Mercanet\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cmsbox\Mercanet\Helper\Watchdog $watchdog,
        \Cmsbox\Mercanet\Gateway\Config\Config $config,
        \Cmsbox\Mercanet\Model\Service\MethodHandlerService $methodHandler
    ) {
        parent::__construct($context);
        
        $this->orderHandler        = $orderHandler;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
        $this->methodHandler       = $methodHandler;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getParams();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler::getStaticInstance($methodId);

        if ($methodInstance) {
            // Get the response
            $response = $methodInstance::processResponse(
                $this->config,
                $methodId,
                $responseData
            );
            
            // Process the response
            if (isset($response['isValid']) && $response['isValid'] === true) {
                if (isset($response['isSuccess']) && $response['isSuccess'] === true) {
                    // Place order
                    $order = $this->orderHandler->placeOrder($responseData['Data'], $methodId);
                    
                    // Return success
                    return $this->resultJsonFactory->create()->setData([]);
                }
            }
        }

        // Stop the execution
        return $this->resultJsonFactory->create()->setData(
            [
                $this->handleError(__('Invalid request in automatic controller.'))
            ]
        );
    }

    private function handleError($errorMessage)
    {
        $this->watchdog->logError($errorMessage);
        return $errorMessage;
    }
}