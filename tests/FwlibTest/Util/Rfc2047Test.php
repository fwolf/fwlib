<?php
namespace FwlibTest\Util;

use Fwlib\Util\Rfc2047;
use Fwlib\Util\UtilContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Rfc2047Test extends PHPUnitTestCase
{
    /**
     * @return Rfc2047
     */
    protected function buildMock()
    {
        return UtilContainer::getInstance()->getRfc2047();
    }


    public function testEncodeDecode()
    {
        $rfc2047 = $this->buildMock();

        $x = '一封来自远方的邮件';
        $y = '=?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($y, $rfc2047->encode($x));
        $this->assertEquals($x, $rfc2047->decode($y));

        // Useless duplicate decode
        $this->assertEquals($x, $rfc2047->decode($x));

        // Head and tail with space, will make result difference
        $x = ' 一封来自远方的邮件 ';
        /** @noinspection SpellCheckingInspection */
        $y = '=?utf-8?B?IOS4gOWwgeadpeiHqui/nOaWueeahOmCruS7tiA=?=';
        $this->assertEquals($y, $rfc2047->encode($x));
        $this->assertEquals($x, $rfc2047->decode($y));

        // Part encoded data
        $x = 'Re: 一封来自远方的邮件';
        $y = 'Re: =?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($x, $rfc2047->decode($y));

        // quoted-printable encoding
        $x = '=?GBK?Q?=0D=0A?=';
        $y = "\r\n";
        $this->assertEquals($y, $rfc2047->decode($x));
    }
}
