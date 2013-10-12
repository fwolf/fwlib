<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Rfc2047;

/**
 * Test for Fwlib\Util\Rfc2047
 *
 * @package     Fwlib\Util\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class Rfc2047Test extends PHPunitTestCase
{
    public function testEncodeDecode()
    {
        $x = '一封来自远方的邮件';
        $y = '=?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($y, Rfc2047::encode($x));
        $this->assertEquals($x, Rfc2047::decode($y));

        // Useless duplicate decode
        $this->assertEquals($x, Rfc2047::decode($x));

        // Head and tail with space, will make result difference
        $x = ' 一封来自远方的邮件 ';
        $y = '=?utf-8?B?IOS4gOWwgeadpeiHqui/nOaWueeahOmCruS7tiA=?=';
        $this->assertEquals($y, Rfc2047::encode($x));
        $this->assertEquals($x, Rfc2047::decode($y));

        // Part encoded data
        $x = 'Re: 一封来自远方的邮件';
        $y = 'Re: =?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($x, Rfc2047::decode($y));

        // quoted-printable encoding
        $x = '=?GBK?Q?=0D=0A?=';
        $y = "\r\n";
        $this->assertEquals($y, Rfc2047::decode($x));
    }
}
