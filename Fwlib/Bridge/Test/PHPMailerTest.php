<?php
namespace Fwlib\Bridge\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Bridge\PHPMailer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PHPMailerTest extends PHPunitTestCase
{
    protected function buildMock()
    {
        /**
         * It can't mock parent::send() method, dig into parent::send() logic
         * and find it work by call preSend() and postSend(), so we mock these
         * 2 method to simulate parent::send() return true or false.
         */

        $mailer = $this->getMock(
            'Fwlib\Bridge\PHPMailer',
            array('preSend', 'postSend')
        );
        $mailer->expects($this->any())
            ->method('preSend')
            ->will($this->onConsecutiveCalls(true, false));
        $mailer->expects($this->any())
            ->method('postSend')
            ->will($this->returnValue(true));

        return $mailer;
    }


    public function testParseAddress()
    {
        $mailer = $this->buildMock();

        $x = 'A <a@a.com>; B <b@b.com>, 姓名<c@c.com>;';
        $y = array(
            'a@a.com'   => 'A',
            'b@b.com'   => 'B',
            'c@c.com'   => '姓名',
        );
        $this->assertEqualArray($y, $mailer->parseAddress($x));

        $x = 'a.a.com';
        $this->assertFalse($mailer->parseAddress($x));
    }


    public function testSend()
    {
        $mailer = $this->buildMock();

        // Dummy setter
        $mailer->setCharset('utf-8');
        $mailer->setEncoding('base64');
        $mailer->setHost('smtp.domain.tld', 25);
        $mailer->setAuth('Username', 'Password');
        $mailer->setFrom('alien@domain.tld', 'Alien');
        $mailer->setTo('Somebody <toAddress@domain.tld>');
        $mailer->setSubject('Hello from aliens');
        $mailer->setBody('This is only a test.');

        // No error counted
        $this->assertTrue($mailer->send());
        $this->assertEquals(0, $mailer->getErrorCount());
        $this->assertEmpty($mailer->getErrorMessage());

        // Error counter raised
        $this->assertFalse($mailer->send());
        $this->assertEquals(1, $mailer->getErrorCount());
    }
}
