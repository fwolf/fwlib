<?php
namespace FwlibTest\Config;

use Fwlib\Config\Config;

/**
 * Test for Fwlib\Config\Config
 *
 * @package     FwlibTest\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-20
 */
class ConfigTest extends \PHPunit_Framework_TestCase
{
    public function testSetGet()
    {
        $config = new Config();

        // Single value
        $config->set('foo', 'bar');
        $this->assertEquals($config->get('foo'), 'bar');

        // Value with separator turns to array
        $config->set('foo1.bar', 42);
        $this->assertEquals($config->get('foo1'), array('bar' => 42));

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
        $this->assertEquals($config->get('foo2.bar', 42), 42);
    }
}
