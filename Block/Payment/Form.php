<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Block\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\Context;
use Cmsbox\Mercanet\Model\Service\FormHandlerService;

class Form extends Template {

    /**
     * @var FormHandlerService
     */
    public $formHandler;

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
        FormHandlerService $formHandler
    ) {
        parent::__construct($context);
        $this->formHandler = $formHandler;

        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();
    }
}