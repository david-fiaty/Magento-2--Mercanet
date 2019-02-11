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

use Magento\Sales\Model\ResourceModel\Order\Status\Collection;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var Collection 
     */
    protected $orderStatusCollection;

    /**
     * OrderStatus constructor.
     *
     * @param Collection $statusCollection
     */
    public function __construct(Collection $orderStatusCollection)
    {
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * Return the order status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getStatusOptions();
    }

    /**
     * Get the order status options
     *
     * @return array
     */
    public function getStatusOptions()
    {   
        // Return the options as array
        return $this->orderStatusCollection->toOptionArray();
    }   
}