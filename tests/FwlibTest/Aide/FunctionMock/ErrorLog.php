<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ErrorLog implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'error_log';


    /**
     * {@inheritdoc}
     */
    public function build($namespace)
    {
        $callback = function($message) use ($namespace) {
            self::$results[$namespace] = $message;

            return true;
        };

        return $this->buildFunctionMock($namespace, $callback);
    }
}
