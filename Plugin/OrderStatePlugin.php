<?php
/**
 * Naxero.com Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Naxero
 * @package   Mercanet
 * @author    Naxero Development Team <contact@naxero.com>
 * @copyright 2019 Naxero.com all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Mercanet\Plugin;

use Magento\Sales\Model\Order;
use Naxero\Mercanet\Gateway\Config\Core;

class OrderStatePlugin
{
    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderStatePlugin constructor.
     */
    public function __construct(
        \Naxero\Mercanet\Helper\Tools $tools,
        \Naxero\Mercanet\Gateway\Config\Config $config
    ) {
        $this->tools  = $tools;
        $this->config = $config;
    }

    public function aroundExecute(
        \Magento\Sales\Model\Order\Payment\State\CommandInterface $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderPaymentInterface $payment,
        $amount,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        // Prepare the result
        $result = $proceed($payment, $amount, $order);

        // Build the module id from the payment method
        $methodCode = $payment->getMethodInstance()->getCode();
        $members = explode('_', $methodCode);
        $moduleId = isset($members[0]) && isset($members[1])
        ? $members[0] . $members[1] : '';

        // Check the payment method and update order status
        if (!empty($moduleId) && $moduleId == Core::moduleId()) {
            if ($order->getState() == Order::STATE_PROCESSING) {
                $order->setStatus($this->config->params[Core::moduleId()][Core::KEY_ORDER_STATUS_CAPTURED]);
            }
        }

        return $result;
    }
}
