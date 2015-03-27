<?php
namespace FwlibTest\Net;

use Fwlib\Net\Curl;
use FwlibTest\Aide\FunctionMockAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CurlTest extends PHPUnitTestCase
{
    use FunctionMockAwareTrait;


    /**
     * @return MockObject | Curl
     */
    protected function buildMock()
    {
        $mock = $this->getMock(Curl::class, null);

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        vfsStream::setup('CurlTest');
    }


    public function testAccessors()
    {
        $curl = $this->buildMock();

        $this->assertEquals(0, $curl->getLastCode());

        // No query yet
        $this->assertFalse($curl->getLastContentType());
    }


    public function testGet()
    {
        $curl = $this->buildMock();

        $curlExecMock = $this->getFunctionMock('curl_exec');
        $curlSetoptMock = $this->getFunctionMock('curl_setopt');

        $curlExecMock->setResult('dummy');


        $url = 'http://dummy.com/?';
        $curlSetoptMock->setResult([]);

        $result = $curl->get($url);
        $this->assertEquals('dummy', $result);

        $options = $curlSetoptMock->getResult();
        $this->assertTrue($options[CURLOPT_HTTPGET]);
        $this->assertEquals('http://dummy.com/', $options[CURLOPT_URL]);


        // With additional param combine with param in url
        $url = 'http://dummy.com/?Foo=1';
        $curlSetoptMock->setResult([]);

        $curl->get($url, ['Bar' => 2]);

        $options = $curlSetoptMock->getResult();
        $this->assertEquals(
            'http://dummy.com/?Foo=1&Bar=2',
            $options[CURLOPT_URL]
        );


        $curlExecMock->disableAll();
    }


    public function testMatch()
    {
        $curl = $this->buildMock();

        $this->assertEmpty($curl->match('', 'any'));
        $this->assertEmpty($curl->match('/foo/', 'bar'));

        $this->reflectionSet($curl, 'html', '3423');
        $this->assertEquals(42, $curl->match('/(4\d)/'));

        $this->assertEqualArray(
            ['3 4', '2 3'],
            $curl->match('/(\d \d)/', '3 42 3')
        );
    }


    public function testPost()
    {
        $curl = $this->buildMock();

        $curlExecMock = $this->getFunctionMock('curl_exec');
        $curlSetoptMock = $this->getFunctionMock('curl_setopt');

        $curlExecMock->setResult('dummy');


        $url = 'http://dummy.com/?Foo=1';
        $curlSetoptMock->setResult([]);

        $curl->post($url, ['Bar' => 2]);

        $options = $curlSetoptMock->getResult();
        $this->assertEquals('http://dummy.com/?Foo=1', $options[CURLOPT_URL]);
        $this->assertEquals('Bar=2', $options[CURLOPT_POSTFIELDS]);


        $curlExecMock->disableAll();
    }


    public function testSetOptions()
    {
        $curl = $this->buildMock();

        $curlSetoptMock = $this->getFunctionMock('curl_setopt');


        $cookieFile = vfsStream::newFile('CurlTest/cookies.txt');
        file_put_contents($cookieFile->url(), '', 0644);
        $curl->setoptCookieFile($cookieFile->url());
        $this->assertArrayHasKey(
            CURLOPT_COOKIEFILE,
            $curlSetoptMock->getResult()
        );


        $curl->setoptProxy(2, 'dummy host', '80', 'u:p');
        $options = $curlSetoptMock->getResult();
        $this->assertEquals(80, $options[CURLOPT_PROXYPORT]);
        $this->assertNotEmpty($options[CURLOPT_PROXYUSERPWD]);

        $curl->setoptProxy(0, 'dummy host', '80', 'u:p');
        $options = $curlSetoptMock->getResult();
        $this->assertEmpty($options[CURLOPT_PROXY]);


        $curl->setoptReferrer('dummy referrer');
        $options = $curlSetoptMock->getResult();
        $this->assertNotEmpty($options[CURLOPT_REFERER]);


        $curl->setoptSslVerify(false);
        $options = $curlSetoptMock->getResult();
        $this->assertFalse($options[CURLOPT_SSL_VERIFYHOST]);


        $curl->setoptUserAgent('unknown agent');
        $options = $curlSetoptMock->getResult();
        $this->assertEquals('unknown agent', $options[CURLOPT_USERAGENT]);


        $curlSetoptMock->disableAll();
    }
}
