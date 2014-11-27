<?php
namespace Fwlib\Util\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Util\Rfc2047;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class Rfc2047Test extends PHPunitTestCase
{
    protected $rfc2047;


    public function __construct()
    {
        $this->rfc2047 = new Rfc2047;
    }


    public function testEncodeDecode()
    {
        $x = '一封来自远方的邮件';
        $y = '=?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($y, $this->rfc2047->encode($x));
        $this->assertEquals($x, $this->rfc2047->decode($y));

        // Useless duplicate decode
        $this->assertEquals($x, $this->rfc2047->decode($x));

        // Head and tail with space, will make result difference
        $x = ' 一封来自远方的邮件 ';
        $y = '=?utf-8?B?IOS4gOWwgeadpeiHqui/nOaWueeahOmCruS7tiA=?=';
        $this->assertEquals($y, $this->rfc2047->encode($x));
        $this->assertEquals($x, $this->rfc2047->decode($y));

        // Part encoded data
        $x = 'Re: 一封来自远方的邮件';
        $y = 'Re: =?utf-8?B?5LiA5bCB5p2l6Ieq6L+c5pa555qE6YKu5Lu2?=';
        $this->assertEquals($x, $this->rfc2047->decode($y));

        // quoted-printable encoding
        $x = '=?GBK?Q?=0D=0A?=';
        $y = "\r\n";
        $this->assertEquals($y, $this->rfc2047->decode($x));
    }
}
