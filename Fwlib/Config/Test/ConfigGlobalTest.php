<?php
namespace Fwlib\Config\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;

/**
 * Test for Fwlib\Config\ConfigGlobal
 *
 * @package     Fwlib\Config\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class ConfigGlobalTest extends PHPunitTestCase
{
    /**
     * Backup config in ConfigGlobal and recover after test
     *
     * For other testcase use ConfigGlobal to work properly.
     *
     * @var array
     */
    protected static $configBackup = null;


    public static function setUpBeforeClass()
    {
        self::$configBackup = ConfigGlobal::$config;
    }


    public static function tearDownAfterClass()
    {
        ConfigGlobal::$config = self::$configBackup;
    }


    public function testLimitServerId()
    {
        ConfigGlobal::load(array());
        $this->assertEquals(false, ConfigGlobal::limitServerId(1, false));
    }


    public function testLoad()
    {
        // Empty config value
        ConfigGlobal::load(null);
        $this->assertEquals(null, ConfigGlobal::get('foo'));
        $this->assertEquals(42, ConfigGlobal::get('foo', 42));


        // Normal get
        $config = array(
            'a'         => 1,
            'b.b1'      => 2,
        );

        ConfigGlobal::load($config);

        $this->assertEquals(1, ConfigGlobal::get('a'));
        $this->assertEquals(array('b1' => 2), ConfigGlobal::get('b'));


        // Set
        ConfigGlobal::set('c.c1.c2', 3);
        $this->assertEquals(
            array('c1' => array('c2' => 3)),
            ConfigGlobal::get('c')
        );
    }
}
