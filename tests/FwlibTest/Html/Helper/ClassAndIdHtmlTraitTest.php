<?php
namespace FwlibTest\Html\Helper;

use Fwlib\Html\Helper\ClassAndIdHtmlTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ClassAndIdHtmlTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|ClassAndIdHtmlTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(ClassAndIdHtmlTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetHtml()
    {
        $trait = $this->buildMock();

        $this->assertEmpty($this->reflectionCall($trait, 'getClassHtml', ['']));
        $this->assertEquals(
            " class='foo'",
            $this->reflectionCall($trait, 'getClassHtml', ['foo'])
        );

        $this->assertEmpty($this->reflectionCall($trait, 'getIdHtml', ['']));
        $this->assertEquals(
            " id='bar'",
            $this->reflectionCall($trait, 'getIdHtml', ['bar'])
        );
    }
}
