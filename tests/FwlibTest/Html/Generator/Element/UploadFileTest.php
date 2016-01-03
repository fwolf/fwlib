<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\UploadFile;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2016 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UploadFileTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|UploadFile
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|UploadFile $mock */
        $mock = $this->getMock(
            UploadFile::class,
            $methods
        );

        $mock->setClass('foo')
            ->setId('bar')
            ->setName('dummy');

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $this->assertEquals(
            "<input type='file' class='foo' id='bar'
  name='dummy' value='' />",
            $element->getOutput(ElementMode::EDIT)
        );


        // When it has non-empty value, will stick on show mode
        $element->setValue('This is UploadFile');

        $element->setConfig(UploadFile::CFG_TAG, '');
        $this->assertEquals(
            "This&nbsp;is&nbsp;UploadFile",
            $element->getOutput(ElementMode::EDIT)
        );
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();
        $element->setValue('This is UploadFile');

        $element->setConfig(UploadFile::CFG_TAG, '');
        $this->assertEquals(
            "This&nbsp;is&nbsp;UploadFile",
            $element->getOutput(ElementMode::SHOW)
        );

        $element->setConfig(UploadFile::CFG_TAG, 'p');
        $expected = <<<TAG
<input type='hidden'
  name='dummy' value='This is UploadFile' />
<p class='foo' id='bar'>This&nbsp;is&nbsp;UploadFile</p>
TAG;
        $this->assertEquals(
            $expected,
            $element->getOutput(ElementMode::SHOW)
        );
    }
}
