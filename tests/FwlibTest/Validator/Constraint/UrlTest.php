<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Config\StringOptions;
use Fwlib\Net\Curl;
use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\Constraint\Url;
use FwlibTest\Aide\ObjectMockBuilder\FwlibBaseServiceContainerTrait;
use FwlibTest\Aide\ObjectMockBuilder\FwlibNetCurlTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlTest extends PHPUnitTestCase
{
    use FwlibBaseServiceContainerTrait;
    use FwlibNetCurlTrait;


    /**
     * @var HttpUtil
     */
    protected static $originalHttpUtil;

    /**
     * @var string
     */
    public static $selfHostUrl;

    /**
     * @var string
     */
    public static $selfUrlWithoutParameter;


    /**
     * @return  MockObject|Url
     */
    public function buildMock()
    {
        $mock = $this->getMock(Url::class, null);

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$originalHttpUtil = $utilContainer->getHttp();

        $urlTest = new UrlTest;
        $httpUtil = $urlTest->getMock(
            HttpUtil::class,
            ['getSelfHostUrl', 'getSelfUrlWithoutQueryString']
        );

        $httpUtil->expects($urlTest->any())
            ->method('getSelfHostUrl')
            ->willReturnCallback(function () {
                return UrlTest::$selfHostUrl;
            });

        $httpUtil->expects($urlTest->any())
            ->method('getSelfUrlWithoutQueryString')
            ->willReturnCallback(function () {
                return UrlTest::$selfUrlWithoutParameter;
            });

        $utilContainer->register('Http', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();

        $utilContainer->register('Http', self::$originalHttpUtil);
    }


    public function testGetFullUrl()
    {
        $constraint = $this->buildMock();

        self::$selfHostUrl = 'http://domain.tld';
        self::$selfUrlWithoutParameter = 'http://domain.tld/foo/bar.php';

        $url = '?a=check';
        $fullUrl = $this->reflectionCall($constraint, 'getFullUrl', [$url]);
        $this->assertEquals('http://domain.tld/foo/bar.php?a=check', $fullUrl);


        // Url start with '.' or '/'
        $url = './?a=check';
        $fullUrl = $this->reflectionCall($constraint, 'getFullUrl', [$url]);
        $this->assertEquals('http://domain.tld/foo/./?a=check', $fullUrl);

        $url = '/?a=check';
        $fullUrl = $this->reflectionCall($constraint, 'getFullUrl', [$url]);
        $this->assertEquals('http://domain.tld/?a=check', $fullUrl);
    }


    public function testValidate()
    {
        $constraint = $this->buildMock();
        $serviceContainer = $this->buildServiceContainerMock();
        $serviceContainer->register('Curl', $this->buildCurlMock());
        $constraint->setServiceContainer($serviceContainer);

        $url = 'http://dummy/';


        // Invalid value type
        $constraint->setField($url);
        $this->assertFalse($constraint->validate('foo'));


        // Url empty
        $constraint->setField('');
        $this->assertFalse($constraint->validate([]));
        $this->assertEquals(
            'Need url target for validate',
            current($constraint->getMessages())
        );


        // Curl return success
        $this->curlPostResult = json_encode(['code' => 0, 'message' => '']);
        $constraint->setField($url);
        $this->assertTrue($constraint->validate(null));
        $this->assertEquals($url, $this->curlPostUrl);


        // Curl post data
        $value = [
            'foo' => 'Foo',
            'bar' => 'Bar',
        ];
        $this->curlPostResult = json_encode(['code' => -1, 'message' => '']);

        $constraint->setField($url);
        $constraint->validate($value);
        $this->assertEqualArray($value, $this->curlPostParams);

        $constraint->setOptionsInstance(new StringOptions('foo'));
        $constraint->validate($value);
        $this->assertEqualArray(['foo' => 'Foo'], $this->curlPostParams);

        $constraint->setOptionsInstance(new StringOptions('foo, bar, '));
        $constraint->validate($value);
        $this->assertEqualArray($value, $this->curlPostParams);

        $this->assertEquals(
            'Validate fail',
            current($constraint->getMessages())
        );


        // Curl return fail with message
        $failMessage = [
            'fail message 1',
            'fail message 2',
        ];
        $this->curlPostResult = json_encode(
            ['code' => -1, 'message' => '', 'data' => $failMessage]
        );
        $constraint->setField($url)
            ->setOptionsInstance(null);
        $this->assertFalse($constraint->validate($value));
        $this->assertEqualArray($failMessage, $constraint->getMessages());
    }


    public function testValidateWithCurlException()
    {
        $curl = $this->getmock(Curl::class, ['post']);
        $curl->expects($this->once())
            ->method('post')
            ->willThrowException(new \Exception);

        $constraint = $this->buildMock();
        $serviceContainer = $this->buildServiceContainerMock();
        $serviceContainer->register('Curl', $curl);
        $constraint->setServiceContainer($serviceContainer);

        $constraint->setField('http://dummy/');
        $constraint->validate(null);
        $this->assertStringEndsWith(
            '#curlFail',
            key($constraint->getMessages())
        );
    }
}
