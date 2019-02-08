<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Service;

class RemoteHandlerService {
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * RemoteHandlerService constructor.
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Cmsbox\Mercanet\Gateway\Config\Config $config,
        \Cmsbox\Mercanet\Helper\Tools $tools,
        \Cmsbox\Mercanet\Gateway\Http\Client $client,
        \Cmsbox\Mercanet\Gateway\Processor\Connector $connector
    ) {
        $this->orderRepository    = $orderRepository;
        $this->config             = $config;
        $this->tools              = $tools;
        $this->client             = $client;
        $this->connector          = $connector;
    }

    /**
     * Capture a transaction remotely.
     */
    public function captureRemoteTransaction($transaction, $amount, $payment = false) {
        // Get the method id
        $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

        // Prepare the request URL
        $url = Connector::getApiUrl('charge', $this->config, $methodId) . 'charges/' . $transaction->getTxnId() . '/capture';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the void transaction
            if ($payment) {
                $payment->setTransactionId($response['id']);
                $payment->setParentTransactionId($transaction->getTxnId());
                $payment->setIsTransactionClosed(1);
                $payment->save();
            }

            return true;
        }
       
        return false;
    }

    /**
     * Void a transaction remotely.
     */
    public function voidRemoteTransaction($transaction, $amount, $payment = false) {
        // Get the method id
        $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

        // Prepare the request URL
        $url = Connector::getApiUrl('void', $this->config, $methodId) . 'charges/' . $transaction->getTxnId() . '/void';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the void transaction
            if ($payment) {
                $payment->setTransactionId($response['id']);
                $payment->setParentTransactionId($transaction->getTxnId());
                $payment->setIsTransactionClosed(1);
                $payment->save();
            }

            return true;
        }
       
        return false;
    }

    /**
     * Refund a transaction remotely.
     */
    public function refundRemoteTransaction($transaction, $amount, $payment = false) {
        // Get the method id
        $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

        // Prepare the request URL
        $url = Connector::getApiUrl('refund', $this->config, $methodId) . 'charges/' . $transaction->getTxnId() . '/refund';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the refund transaction
            if ($payment) {
               $payment->setTransactionId($response['id']);
               $payment->setParentTransactionId($transaction->getTxnId());
               $payment->setIsTransactionClosed(1);
               $payment->save();
            }

            return true;
        }
       
        return false;
    }
}