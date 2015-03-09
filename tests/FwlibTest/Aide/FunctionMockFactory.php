<?php
namespace FwlibTest\Aide;

use Fwlib\Base\SingleInstanceTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FunctionMockFactory
{
    use SingleInstanceTrait;
    use UtilContainerAwareTrait;


    /**
     * @var FunctionMockWrapperInterface[]
     */
    protected $instances = [];


    /**
     * @param   string      $namespace
     * @param   string      $function
     * @param   bool        $enabled    Do enable() before return
     * @return  FunctionMockWrapperInterface
     */
    public function get($namespace, $function, $enabled = false)
    {
        $functionClassName = rtrim($namespace, '\\') . "\\$function";

        if (!isset($this->instances[$functionClassName])) {
            $stringUtil = $this->getUtilContainer()->getString();
            $className = __NAMESPACE__ . '\\FunctionMock\\' .
                $stringUtil->toStudlyCaps($function);

            $wrapper = (new $className);
            /** @var FunctionMockWrapperInterface $wrapper */
            $wrapper->build($namespace, $enabled);

            $this->instances[$functionClassName] = $wrapper;
        }

        $wrapper = $this->instances[$functionClassName];
        // php-mock does not provide enabled status check yet.
        if ($enabled) {
            $wrapper->disable();
            $wrapper->enable();
        }

        return $this->instances[$functionClassName];
    }
}
