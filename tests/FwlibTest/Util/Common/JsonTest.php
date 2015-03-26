<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\Json;
use FwlibTest\Aide\FunctionMockAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class JsonTest extends PHPUnitTestCase
{
    use FunctionMockAwareTrait;


    /** @type int */
    public $dummyForTestEncodeHex = 42;

    /** @type \stdClass */
    public $dummyForTestEncodeHex2 = null;


    /**
     * @return MockObject | Json
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            Json::class,
            null
        );

        return $mock;
    }


    /**
     * @requires extension json
     */
    public function testConstructor()
    {
        $factory = $this->getFunctionMockFactory(Json::class);
        $extensionLoadedMock = $factory->get(null, 'extension_loaded', true);

        $extensionLoadedMock->setResult(true);

        new Json;

        $this->assertTrue(true);

        $extensionLoadedMock->disable();
    }


    /**
     * @expectedException \Fwlib\Base\Exception\ExtensionNotLoadedException
     */
    public function testConstructorFailed()
    {
        $factory = $this->getFunctionMockFactory(Json::class);
        $extensionLoadedMock = $factory->get(null, 'extension_loaded', true);

        $extensionLoadedMock->setResult(false);

        new Json;

        $extensionLoadedMock->disable();
    }


    public function testDummy()
    {
        $factory = $this->getFunctionMockFactory(Json::class);
        $extensionLoadedMock = $factory->get(null, 'extension_loaded', true);

        $extensionLoadedMock->setResult(true);

        $jsonUtil = $this->buildMock();

        $x = ['foo' => 'bar'];
        $y = '{"foo":"bar"}';

        $this->assertEquals($y, $jsonUtil->encode($x));
        $this->assertEqualArray($x, $jsonUtil->decode($y, true));

        $extensionLoadedMock->disable();
    }


    public function testEncodeHex()
    {
        $jsonUtil = $this->buildMock();

        $x = ['<b>', "'bar'", '"baz"', '&Long&', "\xc3\xa9"];

        $this->assertEquals(
            '["<b>","\'bar\'","\"baz\"","&Long&","\u00e9"]',
            $jsonUtil->encodeHex($x, 0)
        );
        $this->assertEquals(
            '["\u003Cb\u003E","\'bar\'","\"baz\"","&Long&","\u00e9"]',
            $jsonUtil->encodeHex($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<b>","\u0027bar\u0027","\"baz\"","&Long&","\u00e9"]',
            $jsonUtil->encodeHex($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<b>","\'bar\'","\u0022baz\u0022","&Long&","\u00e9"]',
            $jsonUtil->encodeHex($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<b>","\'bar\'","\"baz\"","\u0026Long\u0026","\u00e9"]',
            $jsonUtil->encodeHex($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["\u003Cb\u003E","\u0027bar\u0027","\u0022baz\u0022","\u0026Long\u0026","\u00e9"]',
            $jsonUtil->encodeHex($x)
        );

        $x = ['foo' => 'bar'];
        $this->assertEquals(
            '{"foo":"bar"}',
            $jsonUtil->encodeHex($x)
        );

        $x = new JsonTest;
        $x->dummyForTestEncodeHex2 = new \stdClass;
        $this->assertEquals(
            '{"dummyForTestEncodeHex":42,"dummyForTestEncodeHex2":{}}',
            $jsonUtil->encodeHex($x)
        );

        $this->assertEquals(42, $jsonUtil->encodeHex(42));
    }


    public function testEncodeUnicode()
    {
        $jsonUtil = $this->buildMock();

        $x = ['<b>', "'bar'", '"baz"', '&Long&', "\xc3\xa9"];

        $this->assertEquals(
            '["<b>","\'bar\'","\"baz\"","&Long&","é"]',
            $jsonUtil->encodeUnicode($x, 0)
        );
        $this->assertEquals(
            '["\u003Cb\u003E","\'bar\'","\"baz\"","&Long&","é"]',
            $jsonUtil->encodeUnicode($x, JSON_HEX_TAG)
        );
        $this->assertEquals(
            '["<b>","\u0027bar\u0027","\"baz\"","&Long&","é"]',
            $jsonUtil->encodeUnicode($x, JSON_HEX_APOS)
        );
        $this->assertEquals(
            '["<b>","\'bar\'","\u0022baz\u0022","&Long&","é"]',
            $jsonUtil->encodeUnicode($x, JSON_HEX_QUOT)
        );
        $this->assertEquals(
            '["<b>","\'bar\'","\"baz\"","\u0026Long\u0026","é"]',
            $jsonUtil->encodeUnicode($x, JSON_HEX_AMP)
        );
        $this->assertEquals(
            '["<b>","\'bar\'","\"baz\"","&Long&","é"]',
            $jsonUtil->encodeUnicode($x)
        );

        $x = ['foo' => 'é'];
        $this->assertEquals(
            '{"foo":"é"}',
            $jsonUtil->encodeUnicode($x)
        );

        $x = ['中文', ['中' => '文']];
        $y = '["中文",{"中":"文"}]';
        $this->assertEquals($y, $jsonUtil->encodeUnicode($x));
        $this->assertEqualArray($x, json_decode($y, true));
    }
}
