<?php
namespace Fwlib\Net\Sms\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Net\Sms\SmsSender;

/**
 * Test for Fwlib\Net\Sms\SmsSender
 *
 * @package     Fwlib\Net\Sms\Test
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-30
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


    public function testNewObjSmsLogger()
    {
        // Directly mock AbstractServiceContainer is difficult because it use
        // Singleton and forbid __construct() and __wakeup(), so use dummy.
        $db = $this->getMockBuilder('Fwlib\Bridge\Adodb')
            ->disableOriginalConstructor()
            ->getMock();
        $sc = $this->getMock('DummyServiceContainer', array('get'));
        $sc->expects($this->once())
            ->method('get')
            ->will($this->returnValue($db));

        $smsSender = new SmsSender();
        $smsSender->setServiceContainer($sc);

        $this->assertObjectHasAttribute('smsLogger', $smsSender);
        $this->assertAttributeInstanceOf(
            'Fwlib\Net\Sms\SmsLogger',
            'smsLogger',
            $smsSender
        );
    }


    public function testParsePhoneNumber()
    {
        $x = array(
            '+8613912345678',
            '008613912345678',
            '10086，  13912345678',
            '12345678',
        );
        $y = array(
            '13912345678',
            '10086',
        );
        $this->assertEquals($y, $this->smsSender->parsePhoneNumber($x));
    }


    public function testSend()
    {
        $smsLogger = $this->getMock('Fwlib\Net\Sms\SmsLogger');

        $smsSender = $this->getMock(
            'Fwlib\Net\Sms\SmsSender',
            array('newInstanceSmsLogger', 'sendUsingGammuSmsdInject')
        );
        $smsSender->expects($this->once())
            ->method('newInstanceSmsLogger')
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
            array('getPathOfGammuSmsdInject', 'newInstanceSmsLogger')
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue('dummy'));
        $smsSender->expects($this->once())
            ->method('newInstanceSmsLogger')
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
            array('getPathOfGammuSmsdInject')
        );
        $smsSender->expects($this->once())
            ->method('getPathOfGammuSmsdInject')
            ->will($this->returnValue('dummy'));

        // Fake exec() result
        self::$exec_output = array(null, 'output_1');
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
            array('getPathOfGammuSmsdInject')
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
            array('getPathOfGammuSmsdInject')
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
    $command = \Fwlib\Net\Sms\Test\SmsSenderTest::$exec_command;
    $output = \Fwlib\Net\Sms\Test\SmsSenderTest::$exec_output;
    $returnValue = \Fwlib\Net\Sms\Test\SmsSenderTest::$exec_returnValue;
}

function is_executable()
{
    return \Fwlib\Net\Sms\Test\SmsSenderTest::$is_executable;
}
