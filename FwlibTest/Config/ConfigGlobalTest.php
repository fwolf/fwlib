<?php
namespace FwlibTest\Config;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\ConfigGlobal;

/**
 * Test for Fwlib\Config\ConfigGlobal
 *
 * @package     FwlibTest\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class ConfigGlobalTest extends PHPunitTestCase
{
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
