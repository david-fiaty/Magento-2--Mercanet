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

use Magento\Payment\Model\Config as PaymentConfig;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Watchdog;

class FormHandlerService {

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    /**
     * FormHandlerService constructor.
    */
    public function __construct(
        Config $config,
        Watchdog $watchdog,
        PaymentConfig $paymentConfig
    ) {
        $this->config             = $config;
        $this->watchdog           = $watchdog;
        $this->paymentConfig      = $paymentConfig;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getMonths() {
        return array_merge(
            [__('Month')],
            $this->paymentConfig->getMonths()
        );
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getYears() {
        return array_merge(
            [__('Year')],
            $this->paymentConfig->getYears()
        );
    }
}