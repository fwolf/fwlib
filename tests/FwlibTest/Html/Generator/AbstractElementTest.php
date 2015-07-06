<?php
namespace FwlibTest\Html\Generator;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;
use Fwlib\Web\HtmlHelper;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractElementTest extends PHPunitTestCase
{
    /**
     * @param   string[]    $methods
     * @param   array       $params
     * @return  MockObject|AbstractElement
     */
    protected function buildMock(array $methods = [], array $params = [])
    {
        $methods += ['getOutputForShowMode'];

        $mock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods($methods)
            ->setConstructorArgs($params)
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getOutputForShowMode')
            ->willReturn("<div>show\nmode</div>");

        return $mock;
    }


    public function testGetCommentHtml()
    {
        $element = $this->buildMock();

        $element->setMode(ElementMode::EDIT);

        $element->setComment('');
        $this->assertEmpty($this->reflectionCall($element, 'getCommentHtml'));

        $element->setComment('foo comment');
        $this->assertNotEmpty(
            $this->reflectionCall($element, 'getCommentHtml')
        );
    }


    public function testGetCommonPartsHtml()
    {
        $element = $this->buildMock();

        $this->assertEmpty($this->reflectionCall($element, 'getClassHtml'));
        $element->setClass('foo');
        $this->assertEquals(
            " class='foo'",
            $this->reflectionCall($element, 'getClassHtml')
        );

        $this->assertEmpty($this->reflectionCall($element, 'getIdHtml'));
        $element->setId('barId');
        $this->assertEquals(
            " id='barId'",
            $this->reflectionCall($element, 'getIdHtml')
        );

        $this->assertEmpty($this->reflectionCall($element, 'getNameHtml'));
        $element->setName('bar');
        $this->assertEquals(
            " name='bar'",
            $this->reflectionCall($element, 'getNameHtml')
        );
    }


    public function testConstructor()
    {
        $element = $this->buildMock();
        $this->assertEquals('', $element->getName());

        $element = $this->buildMock([], ['dummyName']);
        $this->assertEquals('dummyName', $element->getName());
    }


    public function testGetOutput()
    {
        $element = $this->buildMock();

        $element->setMode(ElementMode::EDIT);
        $this->assertEquals("<div>show\nmode</div>", $element->getOutput());

        $element->setComment('foo comment');
        $this->assertStringStartsWith(
            "<div>show\nmode</div>",
            $element->getOutput()
        );

        // Use default user mode: show
        $element->setMode('');
        $this->assertEquals("<div>show\nmode</div>", $element->getOutput());
    }


    public function testGetOutputWithIndent()
    {
        $element = $this->buildMock();
        $element->setMode(ElementMode::EDIT)
            ->setIndent(2);

        $this->assertEquals(2, $element->getIndent());
        $this->assertEquals("  <div>show\n  mode</div>", $element->getOutput());
    }


    /**
     * @expectedException \Fwlib\Html\Generator\Exception\ElementModeNotImplementedException
     */
    public function testGetOutputWithNotImplementedMode()
    {
        $element = $this->buildMock();
        $element->setMode('notImplemented');

        $element->getOutput();
    }


    public function testGetRootPath()
    {
        $element = $this->buildMock();

        $element->setRootPath('./foo/');
        $this->assertEquals('./foo/', $element->getRootPath());

        $htmlHelper = new HtmlHelper();
        $htmlHelper->setRootPath('./bar/');
        $element->setRootPath(null)
            ->setHtmlHelper($htmlHelper);
        $this->assertEquals('./bar/', $element->getRootPath());
    }


    public function testGetValueHtml()
    {
        $element = $this->buildMock();

        $element->setMode(ElementMode::SHOW);
        $element->setValue('&');
        $this->assertEquals(
            "&amp;",
            $this->reflectionCall($element, 'getValueHtml')
        );
        $element->setMode(ElementMode::EDIT);
        $this->assertEquals(
            " value='&amp;'",
            $this->reflectionCall($element, 'getValueHtml')
        );

        $element->setMode(ElementMode::EDIT);
        $element->setValue(null);
        $this->assertEquals(
            " value=''",
            $this->reflectionCall($element, 'getValueHtml')
        );
        $element->setValue('dummyValue');
        $this->assertEquals(
            " value='dummyValue'",
            $this->reflectionCall($element, 'getValueHtml')
        );
    }
}
