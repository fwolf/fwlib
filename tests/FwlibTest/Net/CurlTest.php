<?php
namespace FwlibTest\Net;

use Fwlib\Net\Curl;
use FwlibTest\Aide\FunctionMockAwareTrait;
use FwlibTest\Aide\FunctionMockFactory;
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

        // Define mock before native function usage
        $factory = FunctionMockFactory::getInstance();
        $factory->setNamespace(Curl::class);
        $factory->get(null, 'curl_exec');
        $factory->get(null, 'curl_setopt');
        $factory->get(null, 'curl_errno');
        $factory->get(null, 'curl_error');
    }


    public function testAccessors()
    {
        $curl = $this->buildMock();

        $this->assertEquals(0, $curl->getLastCode());

        // No query yet
        $this->assertFalse($curl->getLastContentType());
    }


    public function testCurlError()
    {
        $curl = $this->buildMock();

        $curlErrnoMock = $this->getFunctionMock('curl_errno');
        $curlErrorMock = $this->getFunctionMock('curl_error');
        $curlExecMock = $this->getFunctionMock('curl_exec');
        $curlSetoptMock = $this->getFunctionMock('curl_setopt');

        $logFile = vfsStream::newFile('CurlTest/log.txt');
        file_put_contents($logFile->url(), '', 0644);
        $curl->setLogFile($logFile->url());


        $curlErrnoMock->setResult(CURLE_HTTP_NOT_FOUND);
        $curlErrorMock->setResult('curl get error');
        $curl->get('dummy');
        $curlErrorMock->setResult('curl post error');
        $curl->post('dummy');

        $errorLog = file_get_contents($logFile->url());
        $this->assertRegExp("/curl get error\n/", $errorLog);
        $this->assertRegExp("/curl post error\n/", $errorLog);


        true || $curlExecMock || $curlSetoptMock;
        $curlErrnoMock->disableAll();
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


    public function testLog()
    {
        $curl = $this->buildMock();
        $curl->setDebug(true);

        $curlExecMock = $this->getFunctionMock('curl_exec');


        $logFile = vfsStream::newFile('CurlTest/log.txt');
        file_put_contents($logFile->url(), '', 0644);
        $curl->setLogFile($logFile->url());

        $curl->get('http://dummy.com/');
        $this->assertStringEndsWith(
            "Get: http://dummy.com/\n",
            file_get_contents($logFile->url())
        );


        $curl->setLogFile('');
        $this->expectOutputRegex("/Post: http:\\/\\/dummy\\.com\\/\\\n$/");
        $curl->post('http://dummy.com/');


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


    public function testRenewHandle()
    {
        $curlSetoptMock = $this->getFunctionMock('curl_setopt');

        $curl = $this->buildMock();
        $oldHandle = $curl->getHandle();

        $curl->renewHandle();
        $newHandle = $curl->getHandle();

        $this->assertNotEquals($oldHandle, $newHandle);

        $curlSetoptMock->disableAll();
    }


    public function testSetOptions()
    {
        $curl = $this->buildMock();

        $curlSetoptMock = $this->getFunctionMock('curl_setopt');


        $cookieFile = vfsStream::newFile('CurlTest/cookies.txt');
        file_put_contents($cookieFile->url(), '', 0644);
        $curl->setCookieFile($cookieFile->url());
        $this->assertArrayHasKey(
            CURLOPT_COOKIEFILE,
            $curlSetoptMock->getResult()
        );


        $curl->setProxy(2, 'dummy host', '80', 'u:p');
        $options = $curlSetoptMock->getResult();
        $this->assertEquals(80, $options[CURLOPT_PROXYPORT]);
        $this->assertNotEmpty($options[CURLOPT_PROXYUSERPWD]);

        $curl->setProxy(0, 'dummy host', '80', 'u:p');
        $options = $curlSetoptMock->getResult();
        $this->assertEmpty($options[CURLOPT_PROXY]);


        $curl->setReferrer('dummy referrer');
        $options = $curlSetoptMock->getResult();
        $this->assertNotEmpty($options[CURLOPT_REFERER]);


        $curl->setSslVerify(false);
        $options = $curlSetoptMock->getResult();
        $this->assertFalse($options[CURLOPT_SSL_VERIFYHOST]);


        $curl->setUserAgent('unknown agent');
        $options = $curlSetoptMock->getResult();
        $this->assertEquals('unknown agent', $options[CURLOPT_USERAGENT]);


        $curlSetoptMock->disableAll();
    }
}
