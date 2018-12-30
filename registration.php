<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

use Magento\Framework\Component\ComponentRegistrar;

const MODULE_NAME = 'Cmsbox_Mercanet';

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    MODULE_NAME, 
    __DIR__
);
