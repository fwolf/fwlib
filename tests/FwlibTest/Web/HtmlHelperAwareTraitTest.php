<?php
namespace FwlibTest\Web;

use Fwlib\Web\HtmlHelper;
use Fwlib\Web\HtmlHelperAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HtmlHelperAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | HtmlHelperAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(HtmlHelperAwareTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    public function testSetGetHtmlHelper()
    {
        $htmlHelperAware = $this->buildMock();

        $htmlHelper = $this->reflectionCall($htmlHelperAware, 'getHtmlHelper');
        $this->assertInstanceOf(HtmlHelper::class, $htmlHelper);
        $this->assertNull(
            $this->reflectionGet($htmlHelperAware, 'htmlHelper')
        );

        $htmlHelperAware->setHtmlHelper($htmlHelper);
        $this->assertInstanceOf(HtmlHelper::class, $htmlHelper);
        $this->assertNotNull(
            $this->reflectionGet($htmlHelperAware, 'htmlHelper')
        );
    }
}
