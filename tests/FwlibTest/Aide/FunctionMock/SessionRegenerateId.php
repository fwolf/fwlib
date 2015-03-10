<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SessionRegenerateId implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'session_regenerate_id';


    /**
     * {@inheritdoc}
     */
    public function build($namespace, $enabled = false)
    {
        $callback = function() use ($namespace) {
            self::$results[$namespace] = true;
        };

        return $this->buildFunctionMock($namespace, $callback, $enabled);
    }
}
