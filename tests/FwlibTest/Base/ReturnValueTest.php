<?php
namespace FwlibTest\Base;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\ReturnValue;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ReturnValueTest extends PHPunitTestCase
{
    protected $utilContainer;
    protected $json;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->json = $this->utilContainer->get('Json');
    }


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
        $rv->setData(array());
        $this->assertEquals(array(), $rv->getData());
    }


    public function testJsonMode()
    {
        $json = '{"code":42,"message":"foo","data":null}';

        $rv = new ReturnValue($json);
        $this->assertEquals($json, $rv->getJson());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage string to load have no
     */
    public function testJsonModeWithInvalidStringToLoad()
    {
        $json = '{"code":42,"data":"bar"}';

        $rv = new ReturnValue($json);
    }
}
