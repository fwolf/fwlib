<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Ip;

/**
 * @copyright   Copyright 2004-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class IpTest extends PHPunitTestCase
{
    protected $ip;

    public function __construct()
    {
        $this->ip = new Ip;
    }


    public function testToFromHex()
    {
        // Default value
        $this->assertEquals($this->ip->toHex('131.2.101.10'), '8302650a');
        $this->assertEquals($this->ip->fromHex('8302650a'), '131.2.101.10');

        // Loopback address
        $this->assertEquals($this->ip->toHex('127.0.0.1'), '7f000001');
        $this->assertEquals($this->ip->fromHex('7f000001'), '127.0.0.1');

        // Error format in ip2long()
        $this->assertEquals($this->ip->toHex('127.00.00.01'), '');

        // Mask address
        $this->assertEquals($this->ip->toHex('255.255.255.255'), 'ffffffff');
        $this->assertEquals($this->ip->fromHex('ffffffff'), '255.255.255.255');

        // Normal address
        $this->assertEquals($this->ip->toHex('202.99.160.68'), 'ca63a044');
        $this->assertEquals($this->ip->fromHex('ca63a044'), '202.99.160.68');

        // Error parameters handel
        $this->assertEquals($this->ip->toHex('ABCD'), '');
        $this->assertEquals($this->ip->fromHex('ABCD'), '');
        $this->assertEquals($this->ip->toHex('1.2.3'), '');
    }
}
