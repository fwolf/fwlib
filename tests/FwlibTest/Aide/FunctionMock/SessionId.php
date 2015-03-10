<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SessionId implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'session_id';


    /**
     * {@inheritdoc}
     */
    public function build($namespace, $enabled = false)
    {
        $callback = function($id = '') use ($namespace) {
            if (empty($id)) {
                return array_key_exists($namespace, self::$results)
                    ? self::$results[$namespace]
                    : '';

            } else {
                self::$results[$namespace] = $id;

                return $id;
            }
        };

        return $this->buildFunctionMock($namespace, $callback, $enabled);
    }
}
