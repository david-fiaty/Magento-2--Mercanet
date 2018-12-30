<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */
 
namespace Cmsbox\Mercanet\Gateway\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Xml\Parser;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;
use Cmsbox\Mercanet\Gateway\Processor\Connector;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Model\Service\MethodHandlerService;

class Config {

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @var Parser
     */
    protected $xmlParser;

    /**
     * @var ScopeConfigInterface
     */    
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Connector
     */
    public $processor;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * @var Array
     */    
    public $params = [];

    /**
     * @var Array
     */    
    public $base = [];

    /**
     * Config constructor.
     */
    public function __construct(
        Reader $moduleDirReader,
        Parser $xmlParser,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        Cart $cart,
        Connector $processor,
        StoreManagerInterface $storeManager,
        MethodHandlerService $methodHandler
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->xmlParser       = $xmlParser;
        $this->scopeConfig     = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->cart            = $cart;
        $this->processor       = $processor;
        $this->storeManager    = $storeManager;
        $this->methodHandler   = $methodHandler;
        
        // Load the module config file
        $this->loadConfig();
    }

    /**
     * Loads the module configuration parameters.
     */
    public function loadConfig() {
        // Prepare the output container
        $output = [];

        // Get the config file
        $filePath = $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, Core::moduleName()) . '/config.xml';
        $fileData = $this->xmlParser->load($filePath)->xmlToArray()['config']['_value']['default'];

        // Set the base parameters array
        $this->base = $this->buildBase($fileData);

        // Get the config array
        $configArray = $fileData['payment'] ?? [];

        // Get the configured values
        if (!empty($configArray)) {
            foreach ($configArray as $methodId => $params) {
                $lines = [];
                foreach ($params as $key => $val) {
                    // Check a database value
                    $dbValue = $this->scopeConfig->getValue(
                        'payment/' . $methodId . '/' . $val, 
                        ScopeInterface::SCOPE_STORE
                    );
                    if ($dbValue) {
                        $lines[$key] = $dbValue;
                    }
                    else {
                        $lines[$key] = $val;
                    }
                }

                $output[$methodId] = $lines;  
            }
        }

        // Set the payment methods config array
        $this->params = $output;
    }

    /**
     * Get supported currencies.
     *
     * @return string
     */
    public function getSupportedCurrencies() {
        $output = [];
        $arr = explode(';', $this->base[Core::KEY_SUPPORTED_CURRENCIES]);
        foreach ($arr as $val) {
            $parts = explode(',', $val);
            $output[$parts[0]] = $parts[1];
        }

        return $output;
    }

    /**
     * Builds the API URL.
     *
     * @return string
     */
    public function getApiUrl($action, $methodId) {
        $mode = $this->params[Core::moduleId()][Connector::KEY_ENVIRONMENT];
        $path = 'api_url' . '_' . $mode . '_' . $action;
        return $this->params[$methodId][$path];
    }

    /**
     * Provides the frontend config parameters.
     *
     * @return string
     */
    public function getFrontendConfig() {
        // Prepare the output
        $output = [];

        // Get request data for each method
        foreach ($this->params as $key => $val) {
            $arr = explode('_', $key);
            if ($this->methodIsValid($arr, $key, $val)) {
                $methodInstance = $this->methodHandler->getStaticInstance($key);
                if ($methodInstance && $methodInstance::isFrontend($this, $key)) {
                    $output[$key] = $val;
                    $output[$key]['active'] = $methodInstance::isFrontend($this, $key);
                    $output[$key]['api_url'] = $this->getApiUrl('charge', $key);
                    $output[$key]['request_data'] = $methodInstance::getRequestData($this, $key);
                } 
            }
        } 

        // Return the formatted config array
        return [
            'payment' => [
                Core::moduleId() => array_merge(
                    $output, 
                    $this->base
                )
            ]
        ];
    }

    /**
     * Builds the base module parameters.
     *
     * @return bool
     */
    public function buildBase($fileData) {
        $output = [];
        $exclude = explode(',', $fileData['base']['exclude']);
        foreach ($fileData['payment'][Core::moduleId()] as $key => $val) {
            if (!in_array($key, $exclude)) {
                $output[$key] = $val;
            }
        }
        
        unset($fileData['base']['exclude']);

        return array_merge($fileData['base'], $output);
    }

    public function methodIsValid($arr, $key, $val) {
        return isset($arr[2]) && isset($arr[3]) 
        && isset($val['can_use_internal']) 
        && (int) $val['can_use_internal'] != 1
        && !in_array($key, $arr);
    }
}
