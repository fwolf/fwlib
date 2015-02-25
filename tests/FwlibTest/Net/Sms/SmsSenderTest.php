<?php
namespace FwlibTest\Net\Sms;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Net\Sms\SmsSender;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SmsSenderTest extends PHPunitTestCase
{
    public static $is_executable = true;
    public static $exec_command = '';
    public static $exec_output = '';
    public static $exec_returnValue = null;

    private $smsSender = null;


    public function __construct()
    {
        $this->smsSender = new SmsSender();
    }


    public function testGetPathOfGammuSmsdInject()
    {
        $this->smsSender->config['path.gammuSmsdInject'] = '';


        // Found
        self::$is_executable = true;

        $this->assertEquals(
            'dummy/gammu-smsd-inject',
            $this->smsSender->getPathOfGammuSmsdInject('dummy/')
        );


        // Not found
        self::$is_executable = false;

        $this->assertFalse($this->smsSender->getPathOfGammuSmsdInject());


        // Use config inject path
        $this->smsSender->config['path.gammuSmsdInject'] = 'dummy/executable-file';

        $this->assertEquals(
            'dummy/executable-file',
            $this->smsSender->getPathOfGammuSmsdInject()
        );
    }


    public function testGetSmsLogger()
    {
        // Directly mock AbstractServiceContainer is difficult because it use
        // Singleton and forbid __construct() and __wakeup(), so use dummy.
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock();
        $sc = $this->getMockBuilder('Fwlib\Base\AbstractServiceContainer')
            ->disableOriginalConstructor()
            ->getMock(
                'Fwlib\Base\AbstractServiceContainer',
                ['get']
            );
        $sc->expects($this->once())
            ->method('get')
            ->will($this->returnValue($db));

        $smsSender = new SmsSender();
        $smsSender->setServiceContainer($sc);

        $this->assertObjectHasAttribute('smsLogger', $smsSender);
        $this->assertInstanceOf(
            'Fwlib\Net\Sms\SmsLogger',
            $this->reflectionCall($smsSender, 'getSmsLogger')
        );
    }


    public function testParsePhoneNumber()
    {
        $x = [
            '+8613912345678',
            '008613912345678',
            '10086，  13912345678',
            '12345678',
        ];
        $y = [
            '13912345678',
            '10086',
        ];
        $this->assertEquals($y, $this->smsSender->parsePhoneNumber($x));
    }


    public function testSend()
    {
        $smsLogger = $this->getMock('Fwlib\Net\Sms\SmsLogger');

        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            ['getSmsLogger', 'sendUsingGammuSmsdInject']
        );
        $smsSender->expects($this->once())
            ->method('getSmsLogger')
            ->will($this->returnValue($smsLogger));
        $smsSender->expects($this->once())
            ->method('sendUsingGammuSmsdInject')
            ->will($this->returnValue(1));

        $smsSender->config['method'] = 'gammuSmsdInject';
        $x = $smsSender->send('13912345678', 'test');

        $this->assertEquals(1, $x);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Method invalidMethod not supported.
     */
    public function testSendWithInvalidMethod()
    {
        $this->smsSender->config['method'] = 'invalidMethod';

        $this->smsSender->send('13912345678', 'test');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage No valid number to sent.
     */
    public function testSendWithNoDestNumber()
    {
        $this->smsSender->send('', 'test');
    }


    public function testSendUsingGammuSmsdInject()
    {
        $smsLogger = $this->getMock('Fwlib\Net\Sms\SmsLogger');

        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            ['getPathOfGammuSmsdInject', 'getSmsLogger']
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue('dummy'));
        $smsSender->expects($this->once())
            ->method('getSmsLogger')
            ->will($this->returnValue($smsLogger));

        // Fake exec() result
        self::$exec_returnValue = 0;

        $smsSender->config['method'] = 'gammuSmsdInject';
        $i = $smsSender->send('13912345678', 'test');

        $this->assertEquals(1, $i);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Gammu inject error: output_1
     */
    public function testSendUsingGammuSmsdInjectWithExecError()
    {
        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            ['getPathOfGammuSmsdInject']
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue('dummy'));

        // Fake exec() result
        self::$exec_output = [null, 'output_1'];
        self::$exec_returnValue = -1;

        $smsSender->config['method'] = 'gammuSmsdInject';
        $smsSender->send('13912345678', 'test');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Can't find gammu smsd inject execute file.
     */
    public function testSendUsingGammuSmsdInjectWithGetPathFail()
    {
        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            ['getPathOfGammuSmsdInject']
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue(false));

        $smsSender->config['method'] = 'gammuSmsdInject';
        $smsSender->send('13912345678', 'test');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Command template of gammu smsd inject error.
     */
    public function testSendUsingGammuSmsdInjectWithWrongCmdTemplate()
    {
        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            ['getPathOfGammuSmsdInject']
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue('dummy'));

        // No [path] in cmd template
        $smsSender->config['cmd.gammuSmsdInject'] = '';

        $smsSender->config['method'] = 'gammuSmsdInject';
        $smsSender->send('13912345678', 'test');
    }
}


// Overwrite build-in function for test
// @link http://www.schmengler-se.de/-php-mocking-built-in-functions-like-time-in-unit-tests

namespace Fwlib\Net\Sms;

function exec($command, &$output, &$returnValue)
{
    $command = \FwlibTest\Net\Sms\SmsSenderTest::$exec_command;
    $output = \FwlibTest\Net\Sms\SmsSenderTest::$exec_output;
    $returnValue = \FwlibTest\Net\Sms\SmsSenderTest::$exec_returnValue;
}

function is_executable()
{
    return \FwlibTest\Net\Sms\SmsSenderTest::$is_executable;
}
