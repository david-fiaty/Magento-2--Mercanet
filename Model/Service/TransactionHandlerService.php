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

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Cmsbox\Mercanet\Model\Ui\ConfigProvider;
use Cmsbox\Mercanet\Model\Service\InvoiceHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Watchdog;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class TransactionHandlerService {

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var InvoiceHandlerService
     */
    protected $invoiceHandler;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * TransactionHandlerService constructor.
     */
    public function __construct(
        BuilderInterface $transactionBuilder,
        ManagerInterface $messageManager,
        InvoiceHandlerService $invoiceHandler,
        Config $config,
        Watchdog $watchdog,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TransactionRepository $transactionRepository
    ) {
        $this->transactionBuilder    = $transactionBuilder;
        $this->messageManager        = $messageManager;
        $this->invoiceHandler        = $invoiceHandler;
        $this->config                = $config;
        $this->watchdog              = $watchdog;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Create a transaction for an order.
     */
    public function createTransaction($order, $paymentData, $transactionMode, $methodId = null) {
        // Prepare the method id
        $methodId = ($methodId) ? $methodId : Core::moduleId();

        // Process the transaction
        try {
            // Prepare payment object
            $payment = $order->getPayment();
            $payment->setMethod($methodId); 
            $payment->setLastTransId($paymentData[$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]]);
            $payment->setTransactionId($paymentData[$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]]);
            $payment->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $paymentData]);

            // Formatted price
            $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
 
            // Prepare transaction
            $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData[$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]])
            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $paymentData])
            ->setFailSafe(true)
            ->build($transactionMode);
 
            // Add authorization transaction to payment if needed
            if ($transactionMode == Transaction::TYPE_AUTH) {
                $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
                $payment->setParentTransactionId(null);
            }

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            // Create the invoice
            if ($this->config->params[$methodId][Core::KEY_INVOICE_CREATION] == $transactionMode) {
                $this->invoiceHandler->processInvoice($order);
            }   
 
            return $transaction->getTransactionId();

        } catch (Exception $e) {
            $this->watchdog->logError($e);
            return false;
        }
    }

    /**
     * Get all transactions for an order.
     */
    public function getTransactions($order) {
        try {
            // Payment filter
            $filters[] = $this->filterBuilder->setField('payment_id')
            ->setValue($order->getPayment()->getId())
            ->create();

            // Order filter
            $filters[] = $this->filterBuilder->setField('order_id')
            ->setValue($order->getId())
            ->create();

            // Build the search criteria
            $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

            return $this->transactionRepository->getList($searchCriteria)->getItems();
        } catch (Exception $e) {
            $this->watchdog->logError($e);
            return [];
        }
    }
}
