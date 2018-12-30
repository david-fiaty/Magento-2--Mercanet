<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Controller\Response;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Xml\Parser;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Model\Service\MethodHandlerService;

class Test extends Action {

    protected $moduleDirReader;
    protected $parser;
    protected $config;
    protected $methodHandler;

    /**
     * Normal constructor.
     */
    public function __construct(
        Context $context,
        Reader $moduleDirReader,
        Parser $parser,
        Config $config,
        MethodHandlerService $methodHandler
    ) {
        parent::__construct($context);

        $this->moduleDirReader = $moduleDirReader;
        $this->parser          = $parser;
        $this->config          = $config;
        $this->methodHandler   = $methodHandler;
    }
 
    public function execute() {

        
        echo "<pre>";
        var_dump($this->config->params);
        echo "</pre>";
        exit();


        $filePath = $this->moduleDirReader->getModuleDir('etc', 'Cmsbox_Mercanet') . '/config.xml';
     
        $parsedArray = $this->parser->load($filePath)->xmlToArray();

        echo "<pre>";
        print_r($parsedArray);
        echo "</pre>";
        exit();


    }
}