<?php
namespace FwlibTest\Util;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\Json;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class JsonTest extends PHPunitTestCase
{
    /** @type bool */
    public static $extension_loaded = true;

    public $dummyForTestEncodeHex = 42;
    public $dummyForTestEncodeHex2;
    protected $json;


    public function __construct()
    {
        $this->json = new Json;
    }


    /**
     * @requires extension json
     */
    public function testConstructor()
    {
        self::$extension_loaded = true;

        new Json;

        $this->assertTrue(true);
    }


    /**
     * @expectedException \Fwlib\Base\Exception\ExtensionNotLoadedException
     */
    public function testConstructorFailed()
    {
        self::$extension_loaded = false;

        new Json;
    }


    public function testDummy()
    {
        self::$extension_loaded = true;

        $x = ['foo' => 'bar'];
        $y = '{"foo":"bar"}';

        $this->assertEquals($y, $this->json->encode($x));
        $this->assertEqualArray($x, $this->json->decode($y, true));

    }


    public function testEncodeHex()
    {
        self::$extension_loaded = true;

        $x = ['<foo>', "'bar'", '"baz"', '&blong&', "\xc3\xa9"];

        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            $this->json->encodeHex($x, 0)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\'bar\'","\"baz\"","&blong&","\u00e9"]',
            $this->json->encodeHex($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<foo>","\u0027bar\u0027","\"baz\"","&blong&","\u00e9"]',
            $this->json->encodeHex($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\u0022baz\u0022","&blong&","\u00e9"]',
            $this->json->encodeHex($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","\u0026blong\u0026","\u00e9"]',
            $this->json->encodeHex($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\u0027bar\u0027","\u0022baz\u0022","\u0026blong\u0026","\u00e9"]',
            $this->json->encodeHex($x)
        );

        $x = ['foo' => 'bar'];
        $this->assertEquals(
            '{"foo":"bar"}',
            $this->json->encodeHex($x)
        );

        $x = new JsonTest;
        $x->dummyForTestEncodeHex2 = new \stdClass;
        $this->assertEquals(
            '{"dummyForTestEncodeHex":42,"dummyForTestEncodeHex2":{}}',
            $this->json->encodeHex($x)
        );
    }


    public function testEncodeUnicode()
    {
        self::$extension_loaded = true;

        $x = ['<foo>', "'bar'", '"baz"', '&blong&', "\xc3\xa9"];

        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","é"]',
            $this->json->encodeUnicode($x, 0)
        );
        $this->assertEquals(
            '["\u003Cfoo\u003E","\'bar\'","\"baz\"","&blong&","é"]',
            $this->json->encodeUnicode($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<foo>","\u0027bar\u0027","\"baz\"","&blong&","é"]',
            $this->json->encodeUnicode($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\u0022baz\u0022","&blong&","é"]',
            $this->json->encodeUnicode($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","\u0026blong\u0026","é"]',
            $this->json->encodeUnicode($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["<foo>","\'bar\'","\"baz\"","&blong&","é"]',
            $this->json->encodeUnicode($x)
        );

        $x = ['foo' => 'é'];
        $this->assertEquals(
            '{"foo":"é"}',
            $this->json->encodeUnicode($x)
        );

        $x = ['中文', ['中' => '文']];
        $y = '["中文",{"中":"文"}]';
        $this->assertEquals($y, $this->json->encodeUnicode($x));
        $this->assertEqualArray($x, json_decode($y, true));
    }
}

namespace Fwlib\Util;

/**
 * @return bool
 */
function extension_loaded()
{
    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    return \FwlibTest\Util\JsonTest::$extension_loaded;
}
