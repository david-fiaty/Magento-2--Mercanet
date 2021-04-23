<?php

/**
 * Naxero.com Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Naxero
 * @package   Mercanet
 * @author    Naxero Development Team <contact@naxero.com>
 * @copyright 2019 Naxero.com all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Mercanet\Model\Service;

class FormHandlerService
{

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
        \Naxero\Mercanet\Gateway\Config\Config $config,
        \Naxero\Mercanet\Helper\Watchdog $watchdog,
        \Magento\Payment\Model\Config $paymentConfig
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
    public function getMonths()
    {
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
    public function getYears()
    {
        return array_merge(
            [__('Year')],
            $this->paymentConfig->getYears()
        );
    }
}
