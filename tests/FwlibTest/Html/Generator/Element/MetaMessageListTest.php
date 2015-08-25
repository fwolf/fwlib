<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\MetaMessageList;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MetaMessageListTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|MetaMessageList
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|MetaMessageList $mock */
        $mock = $this->getMock(
            MetaMessageList::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar');

        return $mock;
    }


    public function testGetMessages()
    {
        $element = $this->buildMock();

        $element->setMessages([
            'i1' => 'Item 1',
            'i2' => 'Item 2',
        ]);
        $element->setMetas([
            'i1' => 'Key 1',
            'i2' => 'Key 2',
        ]);

        $messages = $element->getMessages();

        $this->assertEqualArray(
            [
                'i1' => 'Key 1: Item 1',
                'i2' => 'Key 2: Item 2',
            ],
            $messages
        );
    }


    public function testGetMetas()
    {
        $element = $this->buildMock();

        $element->setMetas([
            'i1' => ['title' => 'Key 1'],
            'i2' => ['title' => 'Key 2'],
        ]);

        $metas = $element->getMetas();

        $this->assertEqualArray(
            [
                'i1' => 'Key 1',
                'i2' => 'Key 2',
            ],
            $metas
        );
    }
}
