<?php
namespace FwlibTest\Html\Generator\Helper;

use Fwlib\Html\Generator\Helper\GetHiddenValueHtmlTrait;
use Fwolf\Wrapper\PHPUnit\Helper\BuildEasyMockTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetHiddenValueHtmlTraitTest extends PHPUnitTestCase
{
    use BuildEasyMockTrait;


    /**
     * @param   string[] $methods
     * @return  MockObject|GetHiddenValueHtmlTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(GetHiddenValueHtmlTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testIncluded()
    {
        $trait = $this->buildEasyMock(GetHiddenValueHtmlTrait::class, [
            'getName'  => 'NAME',
            'getValue' => 'VALUE',
        ]);

        $expectedOutput = <<<TAG
<input type='hidden'
  name='NAME' value='VALUE' />
TAG;
        $this->assertEquals(
            $expectedOutput,
            $this->reflectionCall($trait, 'getHiddenValueHtml')
        );
    }


    public function testNotIncluded()
    {
        $trait = $this->buildEasyMock(GetHiddenValueHtmlTrait::class, [
            'getName'               => 'NAME',
            'getValue'              => 'VALUE',
            'isHiddenValueIncluded' => false,
        ]);

        $this->assertEmpty($this->reflectionCall($trait, 'getHiddenValueHtml'));
    }
}
