<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Gateway\Http;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class Client {

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * Client constructor.
     */     
    public function __construct(
        Curl $curl,
        Watchdog $watchdog
    ) {
        $this->curl            = $curl;
        $this->watchdog        = $watchdog;

        // Launch functions
        $this->addHeaders();
    }

    /**
     * Adds the request headers.
     */ 
    private function addHeaders() {
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Accept', 'application/json');
    }

    /**
     * Encode the response to JSON format.
     */ 
    private function formatResponse($response) {
        return isset($response) ? (array) json_decode($response) : null;
    }

    /**
     * Returns a prepared post response.
     */    
    public function getPostResponse($url, $params) {
        // Log the request
        $this->watchdog->bark(Connector::KEY_REQUEST, $params);

        // Send the request
        $response = $this->post($url, $params);

        // Format the response
        $response = $this->formatResponse($response);

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $response);

        return $response;
    }

    /**
     * Returns a prepared get response.
     */    
    public function getGetResponse($url) {
        // Send the request
        $response = $this->get($url);

        // Format the response
        $response = $this->formatResponse($response);

        // Logging
        $this->watchdog->bark(Connector::KEY_RESPONSE, $response);

        return $response;
    }

    public function post($url, $params) {
        // Send the CURL POST request
        $this->curl->post($url, json_encode($params));

        // Return the response
        return $this->curl->getBody();
    }
 
    public function get($url) {
        // Send the CURL GET request
        $this->curl->get($url);

        // Return the response
        return $this->curl->getBody();     
    }

    public function setOption($name, $value) {
        $this->curl->setOption($name, $value);
    }
}
