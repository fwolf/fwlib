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

    /** @type bool */
    public static $sessionDestroy = true;

    /** @type Mock */
    private $sessionDestroyMock = null;

    /** @type bool */
    public static $sessionStart = true;

    /** @type Mock */
    private $sessionStartMock = null;

    /** @type bool */
    public static $sessionStatus = true;

    /** @type Mock */
    private $sessionStatusMock = null;


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


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionDestroyMock($namespace)
    {
        $function = 'session_destroy';
        $mock = $this->sessionDestroyMock;

        if (is_null($mock)) {
            $callback = function() {
                self::$sessionDestroy = true;
            };

            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->sessionDestroyMock = $mock;
        }

        return $mock;
    }


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionStartMock($namespace)
    {
        $function = 'session_start';
        $mock = $this->sessionStartMock;

        if (is_null($mock)) {
            $callback = function() {
                self::$sessionStart = true;
            };

            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->sessionStartMock = $mock;
        }

        return $mock;
    }


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionStatusMock($namespace)
    {
        $function = 'session_status';
        $mock = $this->sessionStatusMock;

        if (is_null($mock)) {
            $callback = function() {
                return self::$sessionStatus;
            };

            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->sessionStatusMock = $mock;
        }

        return $mock;
    }
}
