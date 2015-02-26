<?php
namespace FwlibTest\Validator\Constraint;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Url;
use Fwlib\Test\ServiceContainerTest;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlTest extends PHPunitTestCase
{
    public static $curlResult;

    /**
     * @type    HttpUtil
     */
    protected static $originalHttpUtil;

    public static $param;

    /**
     * @type    string
     */
    public static $selfHostUrl;

    /**
     * @type    string
     */
    public static $selfUrlWithoutParameter;

    public static $url;


    public function buildMock()
    {
        $curl = $this->getMock('Fwlib\Net\Curl', ['post']);
        $curl->expects($this->any())
            ->method('post')
            ->will($this->returnCallback(function ($url, $param) {
                UrlTest::$url = $url;
                UrlTest::$param = $param;
                return UrlTest::$curlResult;
            }));

        $serviceContainer = ServiceContainerTest::getInstance();
        $serviceContainer->register('Curl', $curl);

        $constraint = new Url();
        $constraint->setServiceContainer($serviceContainer);

        return $constraint;
    }


    public static function setUpBeforeClass()
    {
        $utilContainer = UtilContainer::getInstance();
        self::$originalHttpUtil = $utilContainer->getHttp();

        $urlTest = new UrlTest;
        $httpUtil = $urlTest->getMock(
            'Fwlib\Util\HttpUtil',
            ['getSelfHostUrl', 'getSelfUrlWithoutParameter']
        );

        $httpUtil->expects($urlTest->any())
            ->method('getSelfHostUrl')
            ->willReturnCallback(function () {
                return UrlTest::$selfHostUrl;
            });

        $httpUtil->expects($urlTest->any())
            ->method('getSelfUrlWithoutParameter')
            ->willReturnCallback(function () {
                return UrlTest::$selfUrlWithoutParameter;
            });

        $utilContainer->register('HttpUtil', $httpUtil);
    }


    public static function tearDownAfterClass()
    {
        $utilContainer = UtilContainer::getInstance();

        $utilContainer->register('HttpUtil', self::$originalHttpUtil);
    }


    public function testValidate()
    {
        $constraint = $this->buildMock();
        $url = 'http://dummy/';


        // Invalid value type
        $this->assertFalse($constraint->validate('foo', $url));


        // Url empty
        $this->assertFalse($constraint->validate([], ''));
        $this->assertEquals(
            'The input need url target for validate',
            current($constraint->getMessage())
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
            current($constraint->getMessage())
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
        $this->assertEqualArray($failMessage, $constraint->getMessage());


        // Url fix up
        self::$selfUrlWithoutParameter = 'http://domain.tld/';
        $url = '?a=check';
        $constraint->validate($value, $url);
        $this->assertEquals(
            'http://domain.tld/?a=check',
            self::$url
        );


        // Url start with '.' or '/'
        self::$selfUrlWithoutParameter = 'http://domain.tld/foo/bar.php';
        $url = './?a=check';
        $constraint->validate($value, $url);
        $this->assertEquals(
            'http://domain.tld/foo/./?a=check',
            self::$url
        );

        self::$selfHostUrl = 'http://domain.tld';
        $url = '/?a=check';
        $constraint->validate($value, $url);
        $this->assertEquals(
            'http://domain.tld/?a=check',
            self::$url
        );
    }
}
