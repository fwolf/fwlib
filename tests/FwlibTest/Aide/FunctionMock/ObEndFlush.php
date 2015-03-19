<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ObEndFlush implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'ob_end_flush';


    /**
     * {@inheritdoc}
     */
    public function build($namespace)
    {
        $callback = function() use ($namespace) {
            self::$results[$namespace] = true;
        };

        return $this->buildFunctionMock($namespace, $callback);
    }
}
