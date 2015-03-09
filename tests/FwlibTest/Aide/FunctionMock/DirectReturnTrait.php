<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait DirectReturnTrait
{
    use FunctionMockWrapperTrait;


    /**
     * @see FunctionMockWrapperInterface::build()
     * @param   string      $namespace
     * @return  static
     */
    public function build($namespace)
    {
        return $this->buildFunctionMock($namespace, null);
    }
}
