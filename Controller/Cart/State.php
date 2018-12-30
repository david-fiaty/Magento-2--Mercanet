<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Controller\Result\JsonFactory;

class State extends Action {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Checkout constructor.
     */
    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $resultJsonFactory   
    ) {
        parent::__construct($context);
        
        $this->cart              = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
    }
 
    public function execute() {
        if ($this->getRequest()->isAjax()) {
            // Count the items in cart
            $cartIsEmpty = $this->cart->getItemsCount() === 0;

            return $this->resultJsonFactory->create()->setData([
            'cartIsEmpty' => $cartIsEmpty
            ]);
        }
    }
}