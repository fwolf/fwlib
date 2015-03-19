<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Header implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'header';


    /**
     * {@inheritdoc}
     */
    public function build($namespace)
    {
        $callback = function($headerString) use ($namespace) {
            self::$results[$namespace][] = $headerString;
        };

        return $this->buildFunctionMock($namespace, $callback);
    }
}
