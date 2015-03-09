<?php
namespace FwlibTest\Aide\Mock;

use malkusch\phpmock\Mock;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ExtensionLoadedMockTrait
{
    use FunctionMockTrait;


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
        return $this->buildFunctionMock(
            $namespace,
            'extension_loaded',
            function($ext) {
                return self::$extensionLoaded &&
                    \extension_loaded($ext);
            }
        );
    }


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionDestroyMock($namespace)
    {
        return $this->buildFunctionMock(
            $namespace,
            'session_destroy',
            function() {
                self::$sessionDestroy = true;
            }
        );
    }


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionStartMock($namespace)
    {
        return $this->buildFunctionMock(
            $namespace,
            'session_start',
            function() {
                self::$sessionStart = true;
            }
        );
    }


    /**
     * @param   string  $namespace
     * @return  Mock
     */
    public function buildSessionStatusMock($namespace)
    {
        return $this->buildFunctionMock(
            $namespace,
            'session_status',
            null
        );
    }
}
