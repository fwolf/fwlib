<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CurlSetopt implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'curl_setopt';


    /**
     * {@inheritdoc}
     */
    public function build($namespace)
    {
        $callback = function($handle, $option, $value) use ($namespace) {
            true || $handle;

            self::$results[$namespace][$option] = $value;
        };

        return $this->buildFunctionMock($namespace, $callback);
    }
}
