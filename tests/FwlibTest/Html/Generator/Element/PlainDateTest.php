<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\PlainDate;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PlainDateTest extends PHPUnitTestCase
{
    /**
     * @return MockObject|PlainDate
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            PlainDate::class,
            null
        );

        return $mock;
    }


    public function testGetValue()
    {
        $element = $this->buildMock();

        $date = '2015年02月03日';
        $element->setValue($date);

        $this->assertEquals('2015-02-03', $element->getValue());
    }
}
