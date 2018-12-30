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

use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Watchdog;

class InvoiceHandlerService {

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var InvoiceHandlerService
     */
    protected $invoiceService;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * InvoiceHandlerService constructor.
     * @param Config $config
     * @param InvoiceHandlerService $invoiceService
     * @param InvoiceRepositoryInterface $invoiceRepository
    */
    public function __construct(
        Config $config,
        InvoiceHandlerService $invoiceService,
        InvoiceRepositoryInterface $invoiceRepository,
        Watchdog $watchdog      
    ) {
        $this->config             = $config;
        $this->invoiceService     = $invoiceService;
        $this->invoiceRepository  = $invoiceRepository;
        $this->watchdog           = $watchdog;
    }

    public function processInvoice($order) {
        if ($this->shouldInvoice($order))  $this->createInvoice($order);
    }

    public function shouldInvoice($order) {
        return $order->canInvoice() && ($this->config->getAutoGenerateInvoice());
    }

    public function createInvoice($order) {
        try {
            // Prepare the invoice
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
            $invoice->setBaseGrandTotal($order->getGrandTotal());
            $invoice->register();

            // Save the invoice
            $this->invoiceRepository->save($invoice);
        } catch (\Exception $e) {
            $this->watchdog->log($e);
        }
    }
}