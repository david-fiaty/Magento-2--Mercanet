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

class FormTemplate implements ArrayInterface {

    /**
     * Possible form templates
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => 'template_1',
                'label' => __('Template 1'),
            ],
            [
                'value' => 'template_2',
                'label' => __('Template 2'),
            ],
            [
                'value' => 'template_3',
                'label' => __('Template 3'),
            ],
            [
                'value' => 'template_4',
                'label' => __('Template 4'),
            ],
            [
                'value' => 'template_5',
                'label' => __('Template 5'),
            ],
            [
                'value' => 'template_6',
                'label' => __('Template 6'),
            ],
        ];
    }

}