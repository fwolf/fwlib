<?php
namespace FwlibTest\Util;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Util\UuidBase36;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UuidBase36Test extends PHPunitTestCase
{
    protected $utilContainer;
    protected $uuid;


    public function __construct()
    {
        $this->utilContainer = UtilContainer::getInstance();
        $this->uuid = new UuidBase36;
        $this->uuid->setUtilContainer($this->utilContainer);
    }


    public function testAddCheckDigit()
    {
        $y = $this->uuid->generate(null, null, true);
        $this->assertEquals($y, $this->uuid->addCheckDigit($y));
    }


    public function testParse()
    {
        // Group
        $ar = $this->uuid->parse($this->uuid->generate());
        $this->assertEquals('10', $ar['group']);
        $ar = $this->uuid->parse($this->uuid->generate('1'));
        $this->assertEquals('01', $ar['group']);

        // Custom
        $ar = $this->uuid->parse($this->uuid->generate('', '000'));
        $this->assertEquals('000', substr($ar['custom'], -3));

        // Parae data
        $ar = $this->uuid->parse('mvqtti07x4a01a93alw6tz9qp');
        $this->assertEquals(1383575670, $ar['second']);
        $this->assertEquals(10264, $ar['microsecond']);
        $this->assertEquals('a0', $ar['group']);
        $this->assertEquals('1a93alw', $ar['custom']);
        $this->assertEquals('166.178.121.116', $ar['ip']);

        $this->assertNull($this->uuid->parse(null));
    }


    public function testVerify()
    {
        $x = '';
        $this->assertFalse($this->uuid->verify($x));

        $x = 'mvqwzsaypm00sa2t8f0i9ooky';
        $this->assertTrue($this->uuid->verify($x, true));

        $x = 'mvqwzsaypm00sa2t8f0i9ook+';
        $this->assertFalse($this->uuid->verify($x));

        $x = 'mvqwzsaypm00sa2t8f0i9ookx';
        $this->assertFalse($this->uuid->verify($x, true));

        $x = $this->uuid->generate(null, null, true);
        $this->assertTrue($this->uuid->verify($x, true));
    }
}
