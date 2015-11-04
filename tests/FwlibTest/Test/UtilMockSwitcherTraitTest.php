<?php
namespace FwlibTest\Test;

use Fwlib\Test\UtilMockSwitcherTrait;
use Fwlib\Util\Common\StringUtil;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UtilMockSwitcherTraitTest extends PHPUnitTestCase
{
    use UtilMockSwitcherTrait;


    /**
     * @param   string[] $methods
     * @return  MockObject
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(UtilMockSwitcherTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testBeginAndEnd()
    {
        $this->assertEquals(
            'any',
            UtilContainer::getInstance()->getString()->toCamelCase('any')
        );


        /** @var MockObject|StringUtil $stringUtil */
        $stringUtil = $this->beginUtilMock('String', ['toCamelCase']);
        $stringUtil->expects($this->any())
            ->method('toCamelCase')
            ->willReturn('fooBar');

        $this->assertEquals(
            'fooBar',
            UtilContainer::getInstance()->getString()->toCamelCase('any')
        );


        $this->endUtilMock('String');
        $this->assertEquals(
            'any',
            UtilContainer::getInstance()->getString()->toCamelCase('any')
        );
    }
}
