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

class PaymentAction implements \Magento\Framework\Option\ArrayInterface {

    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => self::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ],
        ];
    }
}
