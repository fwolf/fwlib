<?php
namespace FwlibTest\Net\Sms;

use Fwlib\Bridge\Adodb;
use Fwlib\Net\Sms\SmsLogger;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SmsLoggerTest extends PHPUnitTestCase
{
    use UtilContainerAwareTrait;


    /**
     * @return SmsLogger
     */
    protected function buildMock()
    {
        $db = $this->getMockBuilder(Adodb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $smsLogger = new SmsLogger($db);

        return $smsLogger;
    }


    public function testCountDestCompany()
    {
        $smsLogger = $this->buildMock();

        $arDest = [
            '13912345678', '13012345678', '18012345678',
        ];
        $y = [
            'cm' => 1,
            'cu' => 1,
            'ct' => 1,
        ];
        $this->assertEqualArray($y, $smsLogger->countDestCompany($arDest));
    }


    public function testCountPart()
    {
        $smsLogger = $this->buildMock();
        $stringUtil = $this->getUtilContainer()->getString();

        $x = '';
        $this->assertEquals(0, $smsLogger->countPart($x));

        $x = $stringUtil->random(140);
        $this->assertEquals(1, $smsLogger->countPart($x));

        $x = $stringUtil->random(150);
        $this->assertEquals(2, $smsLogger->countPart($x));

        $x = '中' . $stringUtil->random(137);
        $this->assertEquals(2, $smsLogger->countPart($x));
    }


    public function testLog()
    {
        $smsLogger = $this->buildMock();

        $db = $this->getMockBuilder(Adodb::class)
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

        $this->reflectionSet($smsLogger, 'db', $db);

        $smsLogger->log(
            ['13912345678'],
            'Test sms message.',
            0
        );
    }
}
