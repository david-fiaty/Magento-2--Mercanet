<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Request;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Cmsbox\Mercanet\Gateway\Config\Core;


class PaymentForm extends Action {

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
    }
 
    public function execute() {
        if ($this->getRequest()->isAjax()) {
            switch ($this->getRequest()->getParam('task')) {
                case 'block':
                $response = $this->runBlock();
                break;

                case 'charge':
                $response = $this->runCharge();
                break;

                default:
                $response = $this->runBlock();
                break;
            }

            return $this->jsonFactory->create()->setData(['response' => $response]);
        }

        return $this->jsonFactory->create()->setData([]);
    }

    private function runCharge() {
        return 'charge';
    }

    private function runBlock() {
        return $this->pageFactory->create()->getLayout()
                ->createBlock(Core::moduleClass() . '\Block\Payment\Form')
                ->setTemplate(Core::moduleName() . '::payment_form.phtml')
                ->toHtml();
    }
}