<?php
namespace Fwlib\Config\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;

/**
 * Test for Fwlib\Config\GlobalConfig
 *
 * @package     Fwlib\Config\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class GlobalConfigTest extends PHPunitTestCase
{
    /**
     * Backup config in GlobalConfig and recover after test
     *
     * For other testcase use GlobalConfig to work properly.
     *
     * @var array
     */
    protected static $configBackup = null;


    public static function setUpBeforeClass()
    {
        self::$configBackup = GlobalConfig::getInstance()->config;
    }


    public static function tearDownAfterClass()
    {
        GlobalConfig::getInstance()->config = self::$configBackup;
    }


    public function testLimitServerId()
    {
        $globalConfig = GlobalConfig::getInstance();

        $globalConfig->load(array());
        $this->assertEquals(false, $globalConfig->limitServerId(1, false));
    }


    public function testLoad()
    {
        $globalConfig = GlobalConfig::getInstance();

        // Empty config value
        $globalConfig->load(null);
        $this->assertEquals(null, $globalConfig->get('foo'));
        $this->assertEquals(42, $globalConfig->get('foo', 42));


        // Normal get
        $config = array(
            'a'         => 1,
            'b.b1'      => 2,
        );

        $globalConfig->load($config);

        $this->assertEquals(1, $globalConfig->get('a'));
        $this->assertEquals(array('b1' => 2), $globalConfig->get('b'));


        // Set
        $globalConfig->set('c.c1.c2', 3);
        $this->assertEquals(
            array('c1' => array('c2' => 3)),
            $globalConfig->get('c')
        );
    }
}
