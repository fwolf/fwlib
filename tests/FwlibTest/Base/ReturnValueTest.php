<?php
namespace FwlibTest\Base;

use Fwlib\Base\ReturnValue;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ReturnValueTest extends PHPunitTestCase
{
    public function testCommon()
    {
        $rv = new ReturnValue();

        // Default value
        $this->assertEquals(0, $rv->getCode());
        $this->assertEquals('', $rv->getMessage());
        $this->assertEquals(null, $rv->getData());
        $this->assertFalse($rv->isError());

        // Set and get code
        $rv->setCode(42);
        $this->assertEquals(42, $rv->getCode());
        $this->assertFalse($rv->isError());

        // Check error
        $rv->setCode(-42);
        $this->assertTrue($rv->isError());

        // Set and get message
        $rv->setMessage('hi');
        $this->assertEquals('hi', $rv->getMessage());

        // Set and get data
        $rv->setData([]);
        $this->assertEquals([], $rv->getData());
    }


    public function testJsonMode()
    {
        $json = '{"code":42,"message":"foo","data":null}';

        $rv = new ReturnValue($json);
        $this->assertEquals($json, $rv->getJson());
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage string to load have no
     */
    public function testJsonModeWithInvalidStringToLoad()
    {
        $json = '{"code":42,"data":"bar"}';

        $rv = new ReturnValue($json);
    }
}
