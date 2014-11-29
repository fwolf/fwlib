<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Url;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlTest extends PHPunitTestCase
{
    public static $curlResult;
    public static $param;
    public static $url;


    public function buildMock()
    {
        $curl = $this->getMock('Fwlib\Net\Curl', array('post'));
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


    public function testValidate()
    {
        $constraint = $this->buildMock();
        $url = 'http://dummy/';


        // Invalid value type
        $this->assertFalse($constraint->validate('foo', $url));


        // Url empty
        $this->assertFalse($constraint->validate(array(), ''));
        $this->assertEquals(
            'The input need url target for validate',
            current($constraint->getMessage())
        );


        // Curl return success
        self::$curlResult = json_encode(array('code' => 0, 'message' => ''));
        $this->assertTrue($constraint->validate(null, $url));
        $this->assertEquals($url, self::$url);


        // Curl post data
        $value = array(
            'foo'   => 'Foo',
            'bar'   => 'Bar',
        );
        self::$curlResult = json_encode(array('code' => -1, 'message' => ''));

        $constraint->validate($value, $url);
        $this->assertEqualArray($value, self::$param);

        $constraint->validate($value, "$url ,foo");
        $this->assertEqualArray(array('foo' => 'Foo'), self::$param);

        $constraint->validate($value, "$url, foo, bar, ");
        $this->assertEqualArray($value, self::$param);

        $this->assertEquals(
            'The input must pass validate',
            current($constraint->getMessage())
        );


        // Curl return fail with message
        $failMessage = array(
            'fail message 1',
            'fail message 2',
        );
        self::$curlResult = json_encode(
            array('code' => -1, 'message' => '', 'data' => $failMessage)
        );
        $this->assertFalse($constraint->validate($value, $url));
        $this->assertEqualArray($failMessage, $constraint->getMessage());


        // Url fix up
        $url = '?a=check';
        // Fake self url: http://domain.tld/?a=origin
        unset($_SERVER['HTTPS']);
        $_SERVER['HTTP_HOST'] = 'domain.tld';
        $_SERVER['REQUEST_URI'] = '/?a=origin';
        $_SERVER['SCRIPT_NAME'] = '/';

        $constraint->validate($value, $url);
        $this->assertEquals(
            'http://domain.tld/?a=check',
            self::$url
        );
    }
}
