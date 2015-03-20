<?php
namespace FwlibTest\Web;

use Fwlib\Web\ViewTrait;
use FwlibTest\Aide\ObjectMockBuilder\FwlibWebRequestTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ViewTraitTest extends PHPUnitTestCase
{
    use FwlibWebRequestTrait;


    /**
     * @return MockObject | ViewTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ViewTrait::class)
            ->setMethods(['getOutputBody'])
            ->getMockForTrait();

        $mock->expects($this->any())
            ->method('getOutputBody')
            ->will($this->returnValue('body for test action'));

        // Simulate property
        /** @noinspection PhpUndefinedFieldInspection */
        $mock->outputParts = [];

        return $mock;
    }


    public function testAccessors()
    {
        $view = $this->buildMock();

        $this->reflectionCall($view, 'setTitle', ['Title']);
        $this->assertEquals('Title', $this->reflectionGet($view, 'title'));
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

        $view->outputParts = [
            1 => 'header',
            2 => 'footer',
        ];
        $this->assertEquals(
            '<!-- header --><!-- footer -->',
            $view->getOutput()
        );

        $view->outputParts = [
            1 => 'header',
            0 => 'body',
            2 => 'footer',
        ];
        $this->assertEquals(
            '<!-- header -->body for test action<!-- footer -->',
            $view->getOutput()
        );
    }


    /**
     * @expectedException \Fwlib\Web\Exception\InvalidOutputPartException
     * @expectedExceptionMessage View method for part
     */
    public function testGetOutputWithInvalidPart()
    {
        $view = $this->buildMock();

        $view->outputParts = ['NotExist'];
        $view->getOutput();
    }
}
