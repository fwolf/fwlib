<?php
namespace FwlibTest\Web;

use Fwlib\Web\ResponseAwareTrait;
use Fwlib\Web\ResponseInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ResponseAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ResponseAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ResponseAwareTrait::class)
            ->getMockForTrait();

        return $mock;
    }


    public function testSetGetResponse()
    {
        $responseAware = $this->buildMock();

        $response = $this->reflectionCall($responseAware, 'getResponse');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNull($this->reflectionGet($responseAware, 'response'));

        $responseAware->setResponse($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotNull($this->reflectionGet($responseAware, 'response'));
    }
}
