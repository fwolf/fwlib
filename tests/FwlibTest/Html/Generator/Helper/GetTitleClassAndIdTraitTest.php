<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\GetTitleClassAndIdTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetTitleClassAndIdTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|GetTitleClassAndIdTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(GetTitleClassAndIdTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetTitleClassAndId()
    {
        $trait = $this->buildMock(['getClass', 'getId']);
        $trait->expects($this->any())
            ->method('getClass')
            ->willReturnOnConsecutiveCalls('', 'foo');

        $this->assertEquals('', $this->reflectionCall($trait, 'getTitleClass'));
        $this->assertEquals(
            'foo__title',
            $this->reflectionCall($trait, 'getTitleClass')
        );


        $trait->expects($this->any())
            ->method('getId')
            ->willReturnOnConsecutiveCalls('', 'bar', 'bar');

        $this->assertEquals('', $this->reflectionCall($trait, 'getTitleId'));
        $this->assertEquals(
            'bar__title',
            $this->reflectionCall($trait, 'getTitleId')
        );
        $this->assertEquals(
            'bar__title--42',
            $this->reflectionCall($trait, 'getTitleId', [42])
        );
    }
}
