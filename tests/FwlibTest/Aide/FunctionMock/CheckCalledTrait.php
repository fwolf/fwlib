<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * If function is called, set result value to true
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait CheckCalledTrait
{
    use FunctionMockWrapperTrait {
        enable as parentEnable;
    }


    /**
     * @see FunctionMockWrapperInterface::build()
     * @param   string      $namespace
     * @return  static
     */
    public function build($namespace)
    {
        $callback = function() use ($namespace) {
            self::$results[$namespace] = true;
        };

        return $this->buildFunctionMock($namespace, $callback);
    }


    /**
     * {@inheritdoc}
     *
     * Set result to false when enable mock.
     */
    public function enable()
    {
        self::$results[$this->namespace] = false;

        return $this->parentEnable();
    }
}
