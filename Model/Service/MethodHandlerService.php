<?php
/**
 * Cmsbox.fr Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Mercanet
 * @author    Cmsbox France <contact@cmsbox.fr> 
 * @copyright Cmsbox.fr all rights reserved.
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Mercanet\Model\Service;

use Cmsbox\Mercanet\Gateway\Config\Core;

class MethodHandlerService
{
    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * MethodHandlerService constructor.
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader
    ) {
        $this->moduleDirReader = $moduleDirReader;
    }

    private function getFiles($path)
    {
        $result = [];
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($path, $flags);
 
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            if (strpos($fileName, '.') !== 0) {
                $name = basename($fileName, '.php');
                $result[$name] = $name;
            }
        }

        return $result;
    }

    /**
     * Build a payment method instance.
     */
    public static function getStaticInstance($methodId)
    {
        $classPath = "\\" . str_replace('_', "\\", Core::moduleName())
        . "\\Model\\Methods\\" . Core::methodName($methodId);
        if (class_exists($classPath)) {
            return $classPath;
        }

        return false;
    }

    private function getPath()
    {
        return $this->moduleDirReader->getModuleDir('', Core::moduleName()) . '/Model/Methods';
    }
}
