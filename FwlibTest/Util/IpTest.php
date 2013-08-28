<?php
namespace FwlibTest\Util;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Ip;

/**
 * Test for Fwlib\Util\Ip
 *
 * @package     FwlibTest\Util
 * @copyright   Copyright 2004-2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class IpTest extends PHPunitTestCase
{
    public function testToFromHex()
    {
        // Default value
        $this->assertEquals(Ip::toHex('131.2.101.10'), '8302650a');
        $this->assertEquals(Ip::fromHex('8302650a'), '131.2.101.10');

        // Loopback address
        $this->assertEquals(Ip::toHex('127.0.0.1'), '7f000001');
        $this->assertEquals(Ip::fromHex('7f000001'), '127.0.0.1');

        // Error format in ip2long()
        $this->assertEquals(Ip::toHex('127.00.00.01'), '');

        // Mask address
        $this->assertEquals(Ip::toHex('255.255.255.255'), 'ffffffff');
        $this->assertEquals(Ip::fromHex('ffffffff'), '255.255.255.255');

        // Normal address
        $this->assertEquals(Ip::toHex('202.99.160.68'), 'ca63a044');
        $this->assertEquals(Ip::fromHex('ca63a044'), '202.99.160.68');

        // Error parameters handel
        $this->assertEquals(Ip::toHex('ABCD'), '');
        $this->assertEquals(Ip::fromHex('ABCD'), '');
        $this->assertEquals(Ip::toHex('1.2.3'), '');
    }
}
