<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\AbstractRetriever;
use Fwlib\Html\ListView\RetrieverAwareTrait;
use Fwlib\Html\ListView\RetrieverInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class RetrieverAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | RetrieverAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RetrieverAwareTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function test()
    {
        $retrieverAware = $this->buildMock();

        /** @var MockObject|AbstractRetriever $retriever */
        $retriever = $this->getMock(AbstractRetriever::class);
        $retrieverAware->setRetriever($retriever);
        $this->assertInstanceOf(
            RetrieverInterface::class,
            $this->reflectionCall($retrieverAware, 'getRetriever')
        );
    }
}
