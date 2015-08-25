<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\PlainList;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PlainListTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|PlainList
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|PlainList $mock */
        $mock = $this->getMock(
            PlainList::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar');

        return $mock;
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $element->setConfig('tag', 'ol')
            ->setConfig('messages', ['item1', 'item2']);

        $expected = <<<TAG
<ol class='foo' id='bar'>
  <li>item1</li>
  <li>item2</li>
</ol>
TAG;

        $this->assertEquals($expected, $element->getOutput(ElementMode::SHOW));
    }
}
