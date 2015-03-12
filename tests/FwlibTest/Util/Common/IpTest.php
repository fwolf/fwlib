<?php
namespace FwlibTest\Util\Common;

use Fwlib\Util\Common\Ip;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class IpTest extends PHPUnitTestCase
{
    /**
     * @return Ip
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getIp();
    }


    public function testToFromHex()
    {
        $ipUtil = $this->buildMock();

        // Default value
        $this->assertEquals($ipUtil->toHex('131.2.101.10'), '8302650a');
        $this->assertEquals($ipUtil->fromHex('8302650a'), '131.2.101.10');

        // Loopback address
        $this->assertEquals($ipUtil->toHex('127.0.0.1'), '7f000001');
        $this->assertEquals($ipUtil->fromHex('7f000001'), '127.0.0.1');

        // Error format in ip2long()
        $this->assertEquals($ipUtil->toHex('127.00.00.01'), '');

        // Mask address
        /** @noinspection SpellCheckingInspection */
        {
            $this->assertEquals(
                $ipUtil->toHex('255.255.255.255'),
                'ffffffff'
            );
            $this->assertEquals(
                $ipUtil->fromHex('ffffffff'),
                '255.255.255.255'
            );
        }

        // Normal address
        $this->assertEquals($ipUtil->toHex('202.99.160.68'), 'ca63a044');
        $this->assertEquals($ipUtil->fromHex('ca63a044'), '202.99.160.68');

        // Error parameters handel
        /** @noinspection SpellCheckingInspection */
        {
            $this->assertEquals($ipUtil->toHex('ABCD'), '');
            $this->assertEquals($ipUtil->fromHex('ABCD'), '');
            $this->assertEquals($ipUtil->toHex('1.2.3'), '');
        }
    }
}
