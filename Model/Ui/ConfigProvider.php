<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Ui;

use Cmsbox\Mercanet\Gateway\Config\Config;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     */
    public function __construct(
         Config $config
    ) {
        $this->config = $config;
   }

    /**
     * Send the configuration to the frontend
     *
     * @return array
     */
    public function getConfig() {
        return $this->config->getFrontendConfig();
    }
}



