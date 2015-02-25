<?php
namespace FwlibTest\Db;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Db\AbstractSequence;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractSequenceTest extends PHPunitTestCase
{
    public static $storage = [];


    protected function buildMock()
    {
        $sequence = $this->getMockBuilder(
            'Fwlib\Db\AbstractSequence'
        )->setMethods([
            'increase',
            'initialize',
            'lockStorage',
            'read',
            'unlockStorage',
        ])->getMock();

        $sequence->expects($this->any())
            ->method('increase')
            ->will($this->returnCallback(function ($prefix, $step = 1) {
                AbstractSequenceTest::$storage[$prefix] += $step;
            }));

        $sequence->expects($this->any())
            ->method('initialize')
            ->will($this->returnCallback(function ($prefix, $value) {
                AbstractSequenceTest::$storage[$prefix] = $value;
            }));

        $sequence->expects($this->any())
            ->method('read')
            ->will($this->returnCallback(function ($prefix) {
                return isset(AbstractSequenceTest::$storage[$prefix])
                    ? AbstractSequenceTest::$storage[$prefix]
                    : null;
            }));

        return $sequence;
    }


    public function testGet()
    {
        $sequence = $this->buildMock();

        $this->assertEquals(1, $sequence->get('test1-'));
        $this->assertEquals(2, $sequence->get('test1-'));

        $this->assertEquals(1, $sequence->get('test2-', 2));
        $this->assertEquals(3, $sequence->get('test2-', 2));
    }
}
