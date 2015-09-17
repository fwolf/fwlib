<?php
namespace FwlibTest\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Component\Form\Renderer;
use Fwlib\Html\Generator\Component\Form\Validator;
use Fwlib\Html\Generator\Element\Text;
use Fwlib\Web\HttpRequest;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FormTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Form
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMock(
            Form::class,
            $methods
        );

        return $mock;
    }


    public function testAccessors()
    {
        $form = $this->buildMock();

        $form->setAction('foo/bar.php');
        $this->assertEquals('foo/bar.php', $form->getAction());

        // Auto new buttons instance
        $buttons = $form->getButtons();
        $this->assertNotNull($buttons);
        $form->setButtons($buttons);

        $form->setMethod(Form::METHOD_GET);
        $this->assertEquals(Form::METHOD_GET, $form->getMethod());

        $renderer = $form->getRenderer();
        $this->assertNotNull($renderer);
        $form->setRenderer($renderer);

        $request = $this->reflectionCall($form, 'getRequest');
        $this->assertNotNull($request);
        $form->setRequest($request);

        $this->assertNotNull($form->getValidator());
    }


    public function testGetOutput()
    {
        $form = $this->buildMock(['receiveContents']);
        $form->expects($this->once())
            ->method('receiveContents');

        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(Renderer::class, ['getOutput']);
        $renderer->expects($this->once())
            ->method('getOutput');

        $form->setRenderer($renderer);

        $form->getOutput();
    }


    public function testReceiveContents()
    {
        /** @var MockObject|HttpRequest $request */
        $request = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['getPosts', 'getGets'])
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->any())
            ->method('getPosts')
            ->willReturnOnConsecutiveCalls(['a' => 'foo'], []);

        $request->expects($this->any())
            ->method('getGets')
            ->willReturnOnConsecutiveCalls(['a' => 'bar'], []);


        $form = $this->buildMock();
        $form->setRequest($request)
            ->setElements(['a' => (new Text())->setName('a')]);


        // Use post
        $form->setMethod(Form::METHOD_POST)
            ->receiveContents();
        $elements = $form->getElements();
        $this->assertEquals('foo', $elements['a']->getValue());

        // Use get
        $this->reflectionSet($form, 'contentsReceived', false);
        $form->setMethod(Form::METHOD_GET)
            ->receiveContents();
        $elements = $form->getElements();
        $this->assertEquals('bar', $elements['a']->getValue());

        // Will not duplicate receive
        $form->receiveContents();
        $elements = $form->getElements();
        $this->assertEquals('bar', $elements['a']->getValue());
    }


    public function testValidate()
    {
        /** @var MockObject|Validator $validator */
        $validator = $this->getMock(
            Validator::class,
            ['validate', 'getMessages']
        );
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $validator->expects($this->once())
            ->method('getMessages')
            ->willReturn(['foo', 'bar']);

        $form = $this->buildMock(['receiveContents']);
        $form->setValidator($validator);

        $this->assertTrue($form->isValid());
        $this->assertNotEmpty($form->getValidateMessages());
    }
}
