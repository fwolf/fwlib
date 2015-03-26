<?php
namespace FwlibTest\Validator\Constraint;

use Fwlib\Net\Curl;
use Fwlib\Util\Common\HttpUtil;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\Constraint\Url;
use FwlibTest\Aide\ObjectMockBuilder\FwlibBaseServiceContainerTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlTest extends PHPUnitTestCase
{
    use FwlibBaseServiceContainerTrait;


    /** @var string */
    public static $curlResult;

    /**
     * @type    HttpUtil
     */
    protected static $originalHttpUtil;

    /** @var string|array */
    public static $param;

    /**
     * @type    string
     */
    public static $selfHostUrl;

    /**
     * @type    string
     */
    public static $selfUrlWithoutParameter;

    /** @var string */
    public static $url;


    /**
     * @return  MockObject | Url
     */
    public function buildMock()
    {
        $mock = $this->getMock(Url::class, null);

        return $mock;
    }


    /**
     * @return  MockObject | Curl
     */
    protected function buildCurlMock()
    {
        $mock = $this->getMock(Curl::class, ['post']);

        $mock->expects($this->any())
            ->method('post')
            ->will($this->returnCallback(function ($url, $param) {
                self::$url = $url;
                self::$param = $param;
                return self::$curlResult;
            }));

        /** @var Curl $mock */
        $mock->setoptSslVerify(false);

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
        $this->assertFalse($constraint->validate('foo', $url));


        // Url empty
        $this->assertFalse($constraint->validate([], ''));
        $this->assertEquals(
            'The input need url target for validate',
            current($constraint->getMessages())
        );


        // Curl return success
        self::$curlResult = json_encode(['code' => 0, 'message' => '']);
        $this->assertTrue($constraint->validate(null, $url));
        $this->assertEquals($url, self::$url);


        // Curl post data
        $value = [
            'foo'   => 'Foo',
            'bar'   => 'Bar',
        ];
        self::$curlResult = json_encode(['code' => -1, 'message' => '']);

        $constraint->validate($value, $url);
        $this->assertEqualArray($value, self::$param);

        $constraint->validate($value, "$url ,foo");
        $this->assertEqualArray(['foo' => 'Foo'], self::$param);

        $constraint->validate($value, "$url, foo, bar, ");
        $this->assertEqualArray($value, self::$param);

        $this->assertEquals(
            'The input must pass validate',
            current($constraint->getMessages())
        );


        // Curl return fail with message
        $failMessage = [
            'fail message 1',
            'fail message 2',
        ];
        self::$curlResult = json_encode(
            ['code' => -1, 'message' => '', 'data' => $failMessage]
        );
        $this->assertFalse($constraint->validate($value, $url));
        $this->assertEqualArray($failMessage, $constraint->getMessages());
    }
}
