<?php
namespace Fwlib\Test\Mock;

use malkusch\phpmock\Mock;
use malkusch\phpmock\MockBuilder;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ExtensionLoadedMockTrait
{
    /** @type bool */
    public static $extensionLoaded = true;

    /** @type Mock */
    private $extensionLoadedMock = null;

    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildExtensionLoadedMock($namespace)
    {
        $function = 'extension_loaded';
        $mock = $this->extensionLoadedMock;

        if (is_null($mock)) {
            $callback = function($ext) {
                return self::$extensionLoaded &&
                \extension_loaded($ext);
            };

            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->extensionLoadedMock = $mock;
        }

        return $mock;
    }
}
