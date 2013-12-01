<?php
namespace Fwlib\Net\Sms\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Net\Sms\SmsLogger;
use Fwlib\Util\StringUtil;

/**
 * Test for Fwlib\Net\Sms\SmsLogger
 *
 * @package     Fwlib\Net\Sms\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-30
 */
class SmsLoggerTest extends PHPunitTestCase
{
    private $smsLogger = null;


    public function __construct()
    {
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock();

        $this->smsLogger = new SmsLogger($db);
    }


    public function testCountDestCompany()
    {
        $arDest = array(
            '13912345678', '13012345678', '18012345678',
        );
        $y = array(
            'cm' => 1,
            'cu' => 1,
            'ct' => 1,
        );
        $this->assertEqualArray($y, $this->smsLogger->countDestCompany($arDest));
    }


    public function testCountPart()
    {
        $x = '';
        $this->assertEquals(0, $this->smsLogger->countPart($x));

        $x = StringUtil::random(140);
        $this->assertEquals(1, $this->smsLogger->countPart($x));

        $x = StringUtil::random(150);
        $this->assertEquals(2, $this->smsLogger->countPart($x));

        $x = '中' . StringUtil::random(137);
        $this->assertEquals(2, $this->smsLogger->countPart($x));
    }


    public function testLog()
    {
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->once())
            ->method('write')
            ->with(
                $this->isType('string'),
                $this->callback(function ($data) {
                    return 25 == strlen($data['uuid'])
                        && 0 === $data['cat']
                        && 1 === $data['cnt_dest']
                        && 1 === $data['cnt_part'];
                }),
                $this->equalTo('I')
            );

        $this->smsLogger->db = $db;

        $this->smsLogger->log(
            array('13912345678'),
            'Test sms message.',
            0
        );
    }
}
