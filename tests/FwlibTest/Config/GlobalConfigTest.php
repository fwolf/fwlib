<?php
namespace FwlibTest\Config;

use Fwlib\Config\GlobalConfig;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GlobalConfigTest extends PHPUnitTestCase
{
    /**
     * Config key of server id
     *
     * @var string
     */
    const KEY_SERVER_ID = 'server.id';

    /**
     * Backup of server id
     *
     * @var string|int
     */
    private static $serverIdBackup;


    /**
     * @return MockObject | GlobalConfig
     */
    protected function buildMock()
    {
        // Assign array() to param methods here will cause mock to be a stub,
        // reason unknown, assign any method will fix it, so use constructor
        // in parent class.
        $mock = $this->getMockBuilder(GlobalConfig::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        self::$serverIdBackup = GlobalConfig::getInstance()
            ->get(self::KEY_SERVER_ID);
    }


    public static function tearDownAfterClass()
    {
        GlobalConfig::getInstance()
            ->set(self::KEY_SERVER_ID, self::$serverIdBackup);
    }


    public function testCheckServerId()
    {
        $globalConfig = $this->buildMock();

        $globalConfig->set(self::KEY_SERVER_ID, 'dummy');
        $this->assertTrue(
            $globalConfig->checkServerId('dummy', self::KEY_SERVER_ID)
        );
        $this->assertTrue(
            $globalConfig->checkServerId(['dummy'], self::KEY_SERVER_ID)
        );
        $this->assertFalse(
            $globalConfig->checkServerId('foobar', self::KEY_SERVER_ID)
        );
    }


    /**
     * @expectedException   \Fwlib\Config\Exception\ServerIdNotSet
     */
    public function testCheckServerIdWithException()
    {
        $globalConfig = $this->buildMock();

        $globalConfig->delete(self::KEY_SERVER_ID);

        $globalConfig->checkServerId('dummy', self::KEY_SERVER_ID);
    }


    public function testLimitServerId()
    {
        $globalConfig = $this->buildMock();

        $globalConfig->set(self::KEY_SERVER_ID, 'dummy');
        $globalConfig->limitServerId('dummy', self::KEY_SERVER_ID);

        $globalConfig->set(self::KEY_SERVER_ID, 42);
        $globalConfig->limitServerId(42, self::KEY_SERVER_ID);

        // No exception thrown
        $this->assertTrue(true);
    }


    /**
     * @expectedException   \Fwlib\Config\Exception\ServerProhibited
     */
    public function testLimitServerIdWithException()
    {
        $globalConfig = $this->buildMock();

        $globalConfig->set(self::KEY_SERVER_ID, 'dummy');
        $globalConfig->limitServerId('foo', self::KEY_SERVER_ID);
    }
}
