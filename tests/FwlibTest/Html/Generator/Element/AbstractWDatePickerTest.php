<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\AbstractWDatePicker;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractWDatePickerTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|AbstractWDatePicker
     */
    protected function buildMock(array $methods = null)
    {
        if (is_null($methods)) {
            $methods = [];
        }
        $methods = array_merge($methods, ['getJsPath']);

        $mock = $this->getMock(
            AbstractWDatePicker::class,
            $methods
        );

        $mock->expects($this->any())
            ->method('getJsPath')
            ->willReturn('path/to/file');

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $element->setConfigs([
            'minDate' => '2012-01-01',
            'maxDate' => '2015-12-31',
        ])
            ->setValue('2015-07-24');

        $expectedOutput = <<<TAG
<script type='text/javascript' src='path/to/file'></script>
<input type='text' class='Wdate'
  value='2015-07-24' size='11'
  onfocus='WdatePicker("{\"dateFmt\":\"yyyy-MM-dd\",\"maxDate\":\"2015-12-31\",\"minDate\":\"2012-01-01\"}");' />
TAG;
        $actualOutput = $element->getOutput(ElementMode::EDIT);
        $this->assertEquals($expectedOutput, $actualOutput);
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $expectedOutput = <<<TAG
<input type='hidden' class='Wdate'
  value='' />
<span class='Wdate'></span>
TAG;
        $actualOutput = $element->getOutput(ElementMode::SHOW);
        $this->assertEquals($expectedOutput, $actualOutput);
    }
}
