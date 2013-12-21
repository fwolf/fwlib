<?php
namespace Fwlib\Config\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\Config;
use Fwlib\Util\UtilContainer;

/**
 * Test for Fwlib\Config\Config
 *
 * @package     Fwlib\Config\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-20
 */
class ConfigTest extends PHPunitTestCase
{
    public static $output = '';


    public function testLimitServerId()
    {
        $config = new Config;

        // Using phpunit/test_helpers
        // @link https://github.com/php-test-helpers/php-test-helpers
        // @link http://thedeveloperworldisyours.com/php/phpunit-tips/
        if (!extension_loaded('test_helpers')) {
            return;
        }
        set_exit_overload(
            function ($output) {
                ConfigTest::$output = $output;
                return false;
            }
        );


        $serverIdBackup = $config->get('server.id');
        unset($config->config['server']['id']);


        // Test exit with msg
        $config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'Server id not set.'
        );

        $config->set('server.id', 2);
        $this->assertEquals(true, $config->limitServerId(2));

        $config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'This program can only run on server 1.'
        );

        $config->limitServerId(array(1, 3));
        $this->assertEquals(
            self::$output,
            'This program can only run on these servers: 1, 3.'
        );


        unset_exit_overload();


        // Fail, but not exit
        $this->assertEquals(
            false,
            $config->limitServerId(array(1, 3), false)
        );


        $config->set('server.id', $serverIdBackup);
    }


    public function testSetGet()
    {
        $config = new Config;

        // Single value
        $config->set('foo', 'bar');
        $this->assertEquals($config->get('foo'), 'bar');
        $this->assertFalse(isset($config['foo2']));
        $config['foo2'] = 'bar2';
        $this->assertEquals('bar2', $config['foo2']);
        unset($config['foo2']);
        $this->assertFalse(isset($config['foo2']));

        $config->setUtilContainer(UtilContainer::getInstance());

        // Value with separator turns to array
        $config->set('foo1.bar', 42);
        $this->assertEquals($config->get('foo1'), array('bar' => 42));
        $config['foo3.bar'] = 'bar3';
        $this->assertEquals('bar3', $config['foo3.bar']);

        // Value with empty middle level
        $config->set('a.b.c', 42);
        $this->assertEquals($config->get('a.b.c', 43), 42);
        $this->assertEquals(
            $config->get('a'),
            array(
                'b' => array(
                    'c' => 42,
                ),
            )
        );

        // Default value
        $this->assertEquals(42, $config->get('notExists.bar', 42));


        // Set array data
        $ar = array(
            'a'     => 1,
            'b.1'   => 2,
            'b.2'   => 3,
            'c.1.1' => 4,
        );
        // load() will reset all previous set data.
        $config->load($ar);
        $y = array(
            'a' => 1,
            'b' => array(
                1   => 2,
                2   => 3
            ),
            'c' => array(
                1   => array(
                    1   => 4,
                ),
            ),
        );
        $this->assertEqualArray($y, $config->config);
    }
}
