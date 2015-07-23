<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\GetJsLoadHtmlTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetJsLoadHtmlTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|GetJsLoadHtmlTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(GetJsLoadHtmlTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testGetJsLoadHtml()
    {
        $trait = $this->buildMock(['getJsPath']);
        $trait->expects($this->once())
            ->method('getJsPath')
            ->willReturn('path/to/file');

        $expectedOutput = <<<TAG
<script type='text/javascript' src='path/to/file'></script>\n
TAG;
        $this->assertEquals(
            $expectedOutput,
            $this->reflectionCall($trait, 'getJsLoadHtml')
        );

        // Second call will not duplicate load
        $this->assertEmpty($this->reflectionCall($trait, 'getJsLoadHtml'));
    }
}
