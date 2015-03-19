<?php
namespace FwlibTest\Web;

use Fwlib\Web\ResponseTrait;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ResponseTraitTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @return MockObject | ResponseTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(ResponseTrait::class)
            ->disableOriginalConstructor()
            ->getMockForTrait();

        return $mock;
    }


    public function testSend()
    {
        $response = $this->buildMock();

        $factory = $this->getFunctionMockFactory(ResponseTrait::class);
        $headerMock = $factory->get(null, 'header', true);

        $headerDummy = 'ETag: "12345"';
        $headerMock->setResult([]);
        $response->addHeader($headerDummy);
        $response->setContent('foo bar');
        $response->send();

        $this->expectOutputString('foo bar');
        $this->assertEqualArray([$headerDummy], $headerMock->getResult());

        $obEndFlushMock = $factory->get(null, 'ob_end_flush', true);
        $obEndFlushMock->setResult(false);
        $response->setContent(null)->send(true);
        $this->assertTrue($obEndFlushMock->getResult());

        $headerMock->disableAll();
    }


    public function testSetGetContent()
    {
        $response = $this->buildMock();

        $response->setContent('foo bar');
        $this->assertEquals('foo bar', $response->getContent());
    }


    public function testSetGetHeaders()
    {
        $response = $this->buildMock();

        $response->addHeader('1');
        $response->addHeader('a', 'ka');
        $response->addHeader('2');
        $response->addHeader('b', 'kb');
        $this->assertEquals('a', $response->getHeader('ka'));
        $this->assertEquals('b', $response->getHeader('kb'));

        $response->removeHeader('kb');
        $response->removeHeader('not exists');
        $this->assertEqualArray(
            ['1', 'ka' => 'a', '2'],
            $response->getHeaders()
        );

        $response->setHeaders([]);
        $this->assertEmpty($response->getHeaders());
    }
}
