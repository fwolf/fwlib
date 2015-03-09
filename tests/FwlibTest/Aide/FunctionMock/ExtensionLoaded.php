<?php
namespace FwlibTest\Aide\FunctionMock;

use FwlibTest\Aide\FunctionMockWrapperInterface;
use FwlibTest\Aide\FunctionMockWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ExtensionLoaded implements FunctionMockWrapperInterface
{
    use FunctionMockWrapperTrait;


    /** @type string */
    public $function = 'extension_loaded';


    /**
     * {@inheritdoc}
     */
    public function build($namespace)
    {
        $callback = function($ext) use ($namespace) {
            return self::$results[$namespace] &&
                \extension_loaded($ext);
        };

        return $this->buildFunctionMock($namespace, $callback);
    }
}
