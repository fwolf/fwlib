<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Web\Helper\TidyTrait;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class TidyTraitTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return MockObject | TidyTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(TidyTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    /**
     * @requires extension tidy
     */
    public function testTidy()
    {
        $tidyTrait = $this->buildMock();

        $html = 'foo bar';
        $this->assertStringEndsWith(
            '</html>',
            $this->reflectionCall($tidyTrait, 'tidy', [$html])
        );
    }


    /**
     * @expectedException   \Fwlib\Base\Exception\ExtensionNotLoadedException
     */
    public function testTidyWithoutExtension()
    {
        $factory = $this->getFunctionMockFactory(TidyTrait::class);
        $classExistsMock = $factory->get(null, 'class_exists', true);

        $tidyTrait = $this->buildMock();

        $classExistsMock->setResult(false);
        $this->reflectionCall($tidyTrait, 'tidy', ['foo']);

        $classExistsMock->disableAll();
    }
}
