<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * PHP version 7
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Adminhtml\Source;

use Cmsbox\Mercanet\Gateway\Processor\Connector;

class CaptureMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible capture modes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Connector::KEY_CAPTURE_IMMEDIATE,
                'label' => __('Immediate'),
            ],
            [
                'value' => Connector::KEY_CAPTURE_DEFERRED,
                'label' => __('Deferred'),
            ],
            [
                'value' => Connector::KEY_CAPTURE_MANUAL,
                'label' => __('Validation'),
            ],
        ];
    }

}
