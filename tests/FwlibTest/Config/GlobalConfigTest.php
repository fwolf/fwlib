<?php
namespace FwlibTest\Config;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GlobalConfigTest extends PHPunitTestCase
{
    /**
     * Config key of server id
     *
     * @type    string
     */
    const KEY_SERVER_ID = 'server.id';

    /**
     * @type    GlobalConfig
     */
    private $globalConfig;

    /**
     * @type string
     */
    public static $output = '';

    /**
     * Backup of server id
     *
     * @type    string|int
     */
    private static $serverIdBackup;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->globalConfig = $this->buildMock();
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GlobalConfig
     */
    protected function buildMock()
    {
        // Assign array() to param methods here will cause mock to be a stub,
        // reason unknown, assign any method will fix it, so use constructor
        // in parent class.
        $globalConfig = $this->getMock(
            'Fwlib\Config\GlobalConfig',
            ['__construct']
        );

        return $globalConfig;
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
        $globalConfig = $this->globalConfig;

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
        $globalConfig = $this->globalConfig;

        // TODO: Need an unset ?
        unset($globalConfig->config['server']['id']);

        $globalConfig->checkServerId('dummy', self::KEY_SERVER_ID);
    }


    public function testLimitServerId()
    {
        $globalConfig = $this->globalConfig;

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
        $globalConfig = $this->globalConfig;

        $globalConfig->set(self::KEY_SERVER_ID, 'dummy');
        $globalConfig->limitServerId('foo', self::KEY_SERVER_ID);
    }


    public function testLoad()
    {
        $globalConfig = $this->globalConfig;

        // Empty config value
        $globalConfig->load(null);
        $this->assertEquals(null, $globalConfig->get('foo'));
        $this->assertEquals(42, $globalConfig->get('foo', 42));


        // Normal get
        $config = [
            'a'         => 1,
            'b.b1'      => 2,
        ];

        $globalConfig->load($config);

        $this->assertEquals(1, $globalConfig->get('a'));
        $this->assertEquals(['b1' => 2], $globalConfig->get('b'));


        // Set
        $globalConfig->set('c.c1.c2', 3);
        $this->assertEquals(
            ['c1' => ['c2' => 3]],
            $globalConfig->get('c')
        );
    }
}
