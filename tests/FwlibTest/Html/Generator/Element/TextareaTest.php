<?php
namespace FwlibTest\Html\Generator\Element;

use Fwlib\Html\Generator\Element\Textarea;
use Fwlib\Html\Generator\ElementMode;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @see         https://jsfiddle.net/h2awgr5e/
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class TextareaTest extends PHPUnitTestCase
{
    /**
     * @param   string[] $methods
     * @return  MockObject|Textarea
     */
    protected function buildMock(array $methods = null)
    {
        /** @var MockObject|Textarea $mock */
        $mock = $this->getMock(
            Textarea::class,
            $methods
        );

        $value = <<<TAG
    First &'">< line
    Second &'">< line
TAG;
        $mock->setValue($value);

        return $mock;
    }


    public function testGetOutputForEditMode()
    {
        $element = $this->buildMock();

        $this->assertEquals(
            "<textarea
  rows='4' cols='40'>    First &amp;'&quot;&gt;&lt; line
    Second &amp;'&quot;&gt;&lt; line</textarea>",
            $element->getOutput(ElementMode::EDIT)
        );
    }


    public function testGetOutputForShowMode()
    {
        $element = $this->buildMock();

        $this->assertEquals(
            "<div>&nbsp; &nbsp; First&nbsp;&amp;&#039;&quot;&gt;&lt;&nbsp;line<br />
&nbsp; &nbsp; Second&nbsp;&amp;&#039;&quot;&gt;&lt;&nbsp;line</div>",
            $element->getOutput(ElementMode::SHOW)
        );
    }
}
