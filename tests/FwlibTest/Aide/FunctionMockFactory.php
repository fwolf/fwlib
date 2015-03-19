<?php
namespace FwlibTest\Aide;

use Fwlib\Base\SingleInstanceTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Function mock factory
 *
 * Beware of namespace current set before get function mocks.
 *
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
     * @var string
     */
    protected $namespace = '';


    /**
     * Helper method to get namespace from full qualified class name
     *
     * @param   string  $fullName
     * @return  string
     */
    public function findNamespace($fullName)
    {
        return $this->getUtilContainer()->getObject()
            ->getNamespace($fullName);
    }


    /**
     * @param   string      $namespace  Empty/null to use inner set namespace
     * @param   string      $function
     * @param   bool        $enabled    Do enable() before return
     * @return  FunctionMockWrapperInterface
     */
    public function get($namespace, $function, $enabled = false)
    {
        if (empty($namespace)) {
            $namespace = $this->namespace;
        }

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


    /**
     * Setter of $namespace
     *
     * @param   string $namespace   Full class name also works.
     * @return  static
     */
    public function setNamespace($namespace)
    {
        if (class_exists($namespace) || interface_exists($namespace) ||
            trait_exists($namespace)
        ) {
            // Is actually full qualified class name
            $namespace = $this->findNamespace($namespace);
        }

        $this->namespace = $namespace;

        return $this;
    }
}
