<?php
/**
 * Cmsbox.fr Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Mercanet
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Mercanet\Block\Adminhtml\Payment;

use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var String
     */
    protected $_template;

    /**
     * @var AssetRepository
     */
    public $assetRepository;

    /**
     * @var Config
     */
    protected $paymentModelConfig;

    /**
     * @var FormHandlerService
     */
    protected $formHandler;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Array
     */
    public $months;

    /**
     * @var Array
     */
    public $years;

    /**
     * Form constructor.
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Cmsbox\Mercanet\Model\Service\FormHandlerService $formHandler,
        \Cmsbox\Mercanet\Gateway\Config\Config $config,
        \Magento\Framework\View\Asset\Repository $assetRepository
    ) {
        // Parent constructor
        parent::__construct($context, $paymentModelConfig);
        
        // Assign the parameters
        $this->formHandler = $formHandler;
        $this->config = $config;
        $this->assetRepository = $assetRepository;
        
        // Get the template config
        $template = $this->config->params[Core::moduleId() . '_admin_method'][Connector::KEY_FORM_TEMPLATE];

        // Set the template
        $this->_template = Core::moduleName() . '::payment_form/' . $template . '.phtml';

        // Prepare the field values
        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();

        // Set the block data
        $this->setData('is_admin', true);
        $this->setData('module_name', Core::moduleName());
        $this->setData('template_name', $template);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('payment_form_block_to_html_before', ['block' => $this]);
        return parent::_toHtml();
    }
}
