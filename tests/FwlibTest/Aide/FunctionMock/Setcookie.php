<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Setcookie implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'setcookie';


    /**
     * {@inheritdoc}
     */
    public function build($namespace, $enabled = false)
    {
        $callback = function($name, $value, $expire) use ($namespace) {
            if (time() > $expire) {
                unset(self::$results[$namespace][$name]);

            } else {
                self::$results[$namespace][$name] = $value;
            }
        };

        return $this->buildFunctionMock($namespace, $callback, $enabled);
    }
}
