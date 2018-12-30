<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;

class OrderStatus implements ArrayInterface {

    /**
     * @var Collection 
     */
    protected $orderStatusCollection;

    /**
     * OrderStatus constructor.
     * @param Collection $statusCollection
     */
    public function __construct(Collection $orderStatusCollection){
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * Return the order status options
     *
     * @return array
     */
    public function toOptionArray() {
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