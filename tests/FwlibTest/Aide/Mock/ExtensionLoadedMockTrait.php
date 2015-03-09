<?php
namespace FwlibTest\Aide\Mock;

use Fwlib\Util\UtilContainer;
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
     * Template of build mock
     *
     * @param   string      $namespace
     * @param   string      $function
     * @param   callable    $callback   Null to return property with same name
     * @return  Mock
     */
    protected function buildFunctionMock(
        $namespace,
        $function,
        callable $callback = null
    ) {
        // Do not want to import UtilContainerAwareTrait for test case
        $stringUtil = UtilContainer::getInstance()->getString();

        $functionName = $stringUtil->toCamelCase($function);
        $mockName = "{$functionName}Mock";

        $mock = $this->$mockName;

        if (is_null($callback)) {
            $callback = function() use ($functionName) {
                return self::$$functionName;
            };
        }

        if (is_null($mock)) {
            $mock = (new MockBuilder())
                ->setNamespace($namespace)
                ->setName($function)
                ->setFunction($callback)
                ->build();

            $mock->define();

            $this->$mockName = $mock;
        }

        return $mock;
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
