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

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Cmsbox\Mercanet\Gateway\Config\Core;

class Tools {

    /**
     * @var Http
     */    
    protected $request;
    
    /**
     * @var ScopeConfigInterface
     */    
    protected $scopeConfig;

    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * Tools constructor.
     */
    public function __construct(
        Http $request,
        ScopeConfigInterface $scopeConfig,
        Csv $csvParser,
        Reader $moduleDirReader
    ) {
        $this->request         = $request;
        $this->scopeConfig     = $scopeConfig;
        $this->csvParser       = $csvParser;
        $this->moduleDirReader = $moduleDirReader;
    }

    /**
     * Retrieves an Alpha 3 country code from Alpha 2 code.
     */
    public function getCountryCodeA2A3($val) {
        // Get the csv file path
        $path = $this->moduleDirReader->getModuleDir('', Core::moduleName()) . '/Model/Files/countries.csv';
        
        if (is_file($path)) {
            // Read the countries
            $countries = $this->csvParser->getData($path);

            // Find the wanted result
            $res = array_filter($countries, function ($arr) use ($val) {
                return $arr[1] == $val;
            });

            // Reset the array ke
            $res = array_merge(array(), $res);
            if (isset($res[0]) && !empty($res)) {
                return $res[0][2];
            }
        }

        return null;
    }

    /**
     * Sanitize http request data.
     */
    public function getInputData() {
        // Get all parameters from request
        $params = $this->request->getParams();

        // Sanitize the array
        $params = array_map(function($val) {
            return filter_var($val, FILTER_SANITIZE_STRING);
        }, $params);

        return $params;
    }

    /**
     * Sort multi dimensional array.
     *
     * @return string
     */
    public static function multiSort(array $tab) { 
        if (is_array($tab))
        ksort($tab);
        foreach ($tab as $key => $val)
        {  
            if (is_array($val))
            {  
                $tab[$key] = $this->multiSort($val);
            }
        }

        return $tab;
    }

    /**
     * Returns the increment id of an order or a quote.
     *
     * @return string
     */
    public static function getIncrementId($entity) {
        return method_exists($entity, 'getIncrementId')
        ? $entity->getIncrementId()
        : $entity->reserveOrderId()->save()->getReservedOrderId();
    }

    /**
     * Returns the currency code of an order or a quote.
     *
     * @return string
     */
    public static function getCurrencyCode($entity) {
        // Get a reflection instance
        $reflection = new \ReflectionClass($entity);

        // Get the class name
        $className = $reflection->getShortName() == 'Interceptor'
        ? $reflection->getParentClass()->getShortName() : $reflection->getShortName();

        // Return the currency code
        $fn = 'get' . $className . 'CurrencyCode';
        return $entity->$fn();
    }
}