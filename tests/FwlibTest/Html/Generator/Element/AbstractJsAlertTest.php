<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\AbstractJsAlert;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractJsAlertTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|AbstractJsAlert
     */
    protected function buildMock(array $methods = null)
    {
        if (is_null($methods)) {
            $methods = [];
        }
        $methods = array_merge($methods, ['getJsPath']);

        $mock = $this->getMock(
            AbstractJsAlert::class,
            $methods
        );

        $mock->expects($this->any())
            ->method('getJsPath')
            ->willReturn('/path/to/js');

        return $mock;
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $element->setConfigs([
            'title'    => 'dummy',
            'messages' => [],
        ]);
        $output = $element->getOutput(ElementMode::SHOW);
        $this->assertEmpty($output);

        // First output will load js
        $element->setConfig('messages', ['foo', 'bar']);
        $output = $element->getOutput(ElementMode::SHOW);
        $expectedOutput = <<<TAG
<script type='text/javascript' src='/path/to/js'></script>
<script type='text/javascript'>
<!--
(function () {
  JsAlert(
    ['foo', 'bar'],
    'dummy',
    '',
    true,
    true
  );
}) ();
-->
</script>
TAG;
        $this->assertEquals($expectedOutput, $output);

        // Second output will not load js
        $output = $element->getOutput(ElementMode::SHOW);
        $this->assertRegExp(
            "/^(?!<script type='text\\/javascript' src=$).*/",
            $output
        );
    }
}
