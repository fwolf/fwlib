<?php
namespace FwlibTest\Web;

use Fwlib\Web\HtmlHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HtmlHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return MockObject | HtmlHelper
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(HtmlHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        return $mock;
    }


    public function testSetGetCss()
    {
        $helper = $this->buildMock();

        $helper->addCss('foo', 'path/foo/');
        $helper->addCss('bar', 'path/bar/');
        $this->assertEquals(2, count($helper->getCss()));
        $this->assertEquals('path/foo/', $helper->getCss('foo'));

        $helper->removeCss('bar');
        $this->assertEquals(1, count($helper->getCss()));

        $helper->clearCss();
        $this->assertEmpty($helper->getCss('foo'));
        $this->assertEmpty($helper->getCss());
    }


    public function testSetGetJs()
    {
        $helper = $this->buildMock();

        $helper->addJs('foo', 'path/foo/');
        $helper->addJs('bar', 'path/bar/');
        $this->assertEquals(2, count($helper->getJs()));
        $this->assertEquals('path/foo/', $helper->getJs('foo'));

        $helper->removeJs('bar');
        $this->assertEquals(1, count($helper->getJs()));

        $helper->clearJs();
        $this->assertEmpty($helper->getJs('foo'));
        $this->assertEmpty($helper->getJs());
    }
}
