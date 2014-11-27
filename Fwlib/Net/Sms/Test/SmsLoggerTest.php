<?php
namespace Fwlib\Net\Sms\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Net\Sms\SmsLogger;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SmsLoggerTest extends PHPunitTestCase
{
    private $smsLogger = null;
    protected $utilContainer;


    public function __construct()
    {
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock();

        $this->smsLogger = new SmsLogger($db);

        $this->utilContainer = UtilContainer::getInstance();
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
        $stringUtil = $this->utilContainer->get('StringUtil');

        $x = '';
        $this->assertEquals(0, $this->smsLogger->countPart($x));

        $x = $stringUtil->random(140);
        $this->assertEquals(1, $this->smsLogger->countPart($x));

        $x = $stringUtil->random(150);
        $this->assertEquals(2, $this->smsLogger->countPart($x));

        $x = '中' . $stringUtil->random(137);
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

        $this->reflectionSet($this->smsLogger, 'db', $db);

        $this->smsLogger->log(
            array('13912345678'),
            'Test sms message.',
            0
        );
    }
}
