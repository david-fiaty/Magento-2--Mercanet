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
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class PaymentBrands implements ArrayInterface {

    /**
     * Possible payment brands
     *
     * @return array
     */
    public function toOptionArray() {
        return Connector::getPaymentBrandsList();
    }
}
