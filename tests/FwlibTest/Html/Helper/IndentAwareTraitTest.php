<?php
namespace FwlibTest\Html\Helper;

use Fwlib\Html\Helper\IndentAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class IndentAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|IndentAwareTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(IndentAwareTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testIndent()
    {
        $trait = $this->buildMock();

        $str = '<div>foo</div>';

        $this->assertEquals(
            '  <div>foo</div>',
            $this->reflectionCall($trait, 'indent', [$str, 2])
        );

        $this->assertEquals(
            '  <div>foo</div>',
            $this->reflectionCall($trait, 'indentHtml', [$str, 2])
        );
    }
}
