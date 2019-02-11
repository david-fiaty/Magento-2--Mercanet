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

namespace Cmsbox\Mercanet\Block\Adminhtml\Widgets;

use Cmsbox\Mercanet\Gateway\Config\Core;

class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $coreRegistry, 
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $cpPath = $this->getViewFileUrl(Core::moduleName() . '::js');

        // Build the javascript
        if (!$this->_coreRegistry->registry('colorpicker_loaded')) {
            $html .= '<script type="text/javascript" src="' . $cpPath.'/'.'jscolor.js"></script>';
            $this->_coreRegistry->registry('colorpicker_loaded', 1);
        }

        // Build the HTML
        $html .= '<script type="text/javascript">
                var el = document.getElementById("' . $element->getHtmlId() . '");
                el.className = el.className + " jscolor{hash:true}";
            </script>';
            
        return $html;
    }
}