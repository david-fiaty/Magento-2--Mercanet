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

class CaptureMode implements ArrayInterface {

    const IMMEDIATE = 'IMMEDIATE';
    const AUTHOR_CAPTURE = 'AUTHOR_CAPTURE';
    const VALIDATION = 'VALIDATION';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::IMMEDIATE,
                'label' => __('Immediate'),
            ],
            [
                'value' => self::AUTHOR_CAPTURE,
                'label' => __('Deferred'),
            ],
            [
                'value' => self::VALIDATION,
                'label' => __('Validation'),
            ],
        ];
    }

}
