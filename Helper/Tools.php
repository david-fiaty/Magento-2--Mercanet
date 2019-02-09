<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Helper;

class Tools {

    /**
     * Returns the increment id of an order or a quote.
     *
     * @return string
     */
    public static function getIncrementId($entity) {
        return method_exists($entity, 'getIncrementId')
        ? $entity->getIncrementId()
        : $entity->reserveOrderId()->save()->getReservedOrderId();
    }

    /**
     * Returns the currency code of an order or a quote.
     *
     * @return string
     */
    public static function getCurrencyCode($entity) {
        try {
            // Get a reflection instance
            $reflection = new \ReflectionClass($entity);

            // Get the class name
            $className = $reflection->getShortName() == 'Interceptor'
            ? $reflection->getParentClass()->getShortName() : $reflection->getShortName();

            // Return the currency code
            $fn = 'get' . $className . 'CurrencyCode';
            return $entity->$fn();

        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('An error occurred when processing the currency codes.'));
        }
    }
}