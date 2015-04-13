<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\Renderer;
use Fwlib\Html\ListView\RendererAwareTrait;
use Fwlib\Html\ListView\RendererInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RendererAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject|RendererAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RendererAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $rendererAware = $this->buildMock();

        /** @var MockObject|Renderer $renderer */
        $renderer = $this->getMock(Renderer::class);
        $rendererAware->setRenderer($renderer);
        $this->assertInstanceOf(
            RendererInterface::class,
            $this->reflectionCall($rendererAware, 'getRenderer')
        );
    }
}
