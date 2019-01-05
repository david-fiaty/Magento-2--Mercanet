<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Block\Adminhtml\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\Context;
use Magento\Payment\Model\Config as PaymentModelConfig;
use Magento\Payment\Block\Form\Cc;
use Cmsbox\Mercanet\Model\Service\FormHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Gateway\Config\Config;

class Form extends Cc {

    /**
     * @var String
     */
    protected $_template = 'Cmsbox_Mercanet::payment_form.phtml';

    /**
     * @var FormHandlerService
     */
    protected $formHandler;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Array
     */
    public $months;

    /**
     * @var Array
     */
    public $years;

    /**
     * @var String
     */
    public $code;

    /**
     * Form constructor.
     */
    public function __construct(
        Context $context,
        PaymentModelConfig $paymentModelConfig,
        FormHandlerService $formHandler,
        Config $config
    ) {
        parent::__construct($context, $paymentModelConfig);
        $this->formHandler = $formHandler;
        $this->config = $config;

        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();
        $this->code  = Core::moduleId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {
        $this->_eventManager->dispatch('payment_form_block_to_html_before', ['block' => $this]);
        return parent::_toHtml();
    }
}