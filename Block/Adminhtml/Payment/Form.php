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
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Cmsbox\Mercanet\Model\Service\FormHandlerService;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Gateway\Config\Config;

class Form extends Cc {

    /**
     * @var String
     */
    protected $_template;

    /**
     * @var FormHandlerService
     */
    protected $formHandler;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var AssetRepository
     */
    public $assetRepository;

    /**
     * @var Array
     */
    public $months;

    /**
     * @var Array
     */
    public $years;

    /**
     * Form constructor.
     */
    public function __construct(
        Context $context,
        PaymentModelConfig $paymentModelConfig,
        FormHandlerService $formHandler,
        Config $config,
        AssetRepository $assetRepository
    ) {
        $this->_template = Core::moduleName() . '::payment_form/template_1.phtml';
        parent::__construct($context, $paymentModelConfig);
        $this->formHandler = $formHandler;
        $this->config = $config;
        $this->assetRepository = $assetRepository;

        // Prepare the field values
        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();

        // Set the block data
        $this->setData('is_admin', true);
        $this->setData('module_name', Core::moduleName());
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