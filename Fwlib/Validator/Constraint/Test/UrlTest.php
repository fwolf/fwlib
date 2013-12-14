<?php
namespace Fwlib\Validator\Constraint\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Validator\Constraint\Url;

/**
 * Test for Fwlib\Validator\Constraint\Url
 *
 * @package     Fwlib\Validator\Constraint\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-12
 */
class UrlTest extends PHPunitTestCase
{
    public static $curlResult;
    public static $param;
    public static $url;


    public function testValidate()
    {
        $curl = $this->getMock('Fwlib\Net\Curl', array('post'));
        $curl->expects($this->any())
            ->method('post')
            ->will($this->returnCallback(function ($url, $param) {
                UrlTest::$url = $url;
                UrlTest::$param = $param;
                return UrlTest::$curlResult;
            }));

        $constraint = new Url();
        $constraint->setInstance($curl, 'Curl');

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


        // Url fixup
        $url = '?a=check';
        // Fake self url: http://dummy/?m=origin
        $_SERVER['HTTP_HOST'] = 'dummy/';
        $_SERVER['REQUEST_URI'] = '?m=origin';

        $constraint->validate($value, $url);
        $this->assertEquals(
            'http://dummy/?m=origin&a=check',
            self::$url
        );
    }
}
