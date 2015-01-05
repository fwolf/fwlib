<?php
namespace FwlibTest\Config;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\Config;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConfigTest extends PHPunitTestCase
{
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
