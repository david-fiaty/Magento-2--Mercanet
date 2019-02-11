<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Mercanet
 * @author    Cmsbox.fr <contact@cmsbox.fr> 
 * @copyright Cmsbox.fr all rights reserved.
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Mercanet\Model\Adminhtml\Source;

use Magento\Sales\Model\Order\Payment\Transaction;

class InvoiceCreation implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Transaction::TYPE_CAPTURE,
                'label' => __('Capture')
            ],
            [
                'value' => Transaction::TYPE_AUTH,
                'label' => 'Authorisation'
            ],    
        ];
    }

}