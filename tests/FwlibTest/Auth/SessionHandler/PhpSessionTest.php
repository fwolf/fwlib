<?php
namespace FwlibTest\Auth\SessionHandler;

use Fwlib\Auth\SessionHandler\Exception\ChangeOpenedSessionIdException;
use Fwlib\Auth\SessionHandler\Exception\PhpSessionDisabledException;
use Fwlib\Auth\SessionHandler\PhpSession;
use FwlibTest\Aide\FunctionMockFactoryAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PhpSessionTest extends PHPUnitTestCase
{
    use FunctionMockFactoryAwareTrait;


    /**
     * @var array
     */
    protected static $sessionBackup;


    /**
     * @return MockObject | PhpSession
     */
    protected function buildMock()
    {
        $mock = $this->getMock(
            PhpSession::class,
            null
        );

        return $mock;
    }


    public static function setUpBeforeClass()
    {
        if (PHP_SESSION_ACTIVE == \session_status()) {
            self::$sessionBackup = $_SESSION;
        }
    }


    public static function tearDownAfterClass()
    {
        if (PHP_SESSION_ACTIVE == \session_status()) {
            $_SESSION = self::$sessionBackup;
        }
    }


    public function testDestroy()
    {
        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionDestroyMock = $factory->get($ns, 'session_destroy', true);


        $sessionDestroyMock->setResult(false);
        $sessionHandler->destroy();
        $this->assertTrue($sessionDestroyMock->getResult());


        $sessionDestroyMock->disableAll();
    }


    public function testIsOpened()
    {
        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionStatusMock = $factory->get($ns, 'session_status', true);


        $sessionStatusMock->setResult(PHP_SESSION_NONE);
        $this->assertFalse($sessionHandler->isOpened());

        $sessionStatusMock->setResult(PHP_SESSION_ACTIVE);
        $this->assertTrue($sessionHandler->isOpened());

        $sessionStatusMock->setResult(PHP_SESSION_DISABLED);
        $this->assertFalse($sessionHandler->isOpened());


        $sessionStatusMock->disable();
    }


    public function testOpen()
    {
        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionStartMock = $factory->get($ns, 'session_start', true);
        $sessionStatusMock = $factory->get($ns, 'session_status', true);


        // Open without session id
        $sessionStatusMock->setResult(PHP_SESSION_NONE);
        $sessionStartMock->setResult(false);
        $sessionHandler->open();
        $this->assertTrue($sessionStartMock->getResult());


        // Open with session id
        $sessionStatusMock->setResult(PHP_SESSION_NONE);
        $sessionStartMock->setResult(false);
        $sessionHandler->setId('foo');
        $sessionHandler->open();
        $this->assertTrue($sessionStartMock->getResult());
        $this->assertEquals('foo', $sessionHandler->getId());


        // Reopen
        $sessionStatusMock->setResult(PHP_SESSION_ACTIVE);
        $sessionStartMock->setResult(false);
        $sessionHandler->open();
        $this->assertFalse($sessionStartMock->getResult());


        $sessionStartMock->disableAll();
    }


    public function testOpenWithSessionDisabled()
    {
        $this->setExpectedException(PhpSessionDisabledException::class);

        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionStatusMock = $factory->get($ns, 'session_status', true);


        $sessionStatusMock->setResult(PHP_SESSION_DISABLED);
        $sessionHandler->open();


        $sessionStatusMock->disableAll();
    }


    public function testRegenerateId()
    {
        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionRegenerateIdMock =
            $factory->get($ns, 'session_regenerate_id', true);
        $sessionIdMock = $factory->get($ns, 'session_id', true);


        $sessionRegenerateIdMock->setResult(false);
        $sessionIdMock->setResult('foo');
        $sessionHandler->regenerateId();
        $this->assertTrue($sessionRegenerateIdMock->getResult());
        $this->assertEquals('foo', $sessionIdMock->getResult());


        $sessionIdMock->setResult('bar');
        $sessionHandler->regenerateId();
        $this->assertEquals('bar', $sessionIdMock->getResult());


        $sessionRegenerateIdMock->disableAll();
    }


    public function testSetGetClear()
    {
        $sessionHandler = $this->buildMock();

        $sessionHandler->set('foo', 'bar');
        $this->assertEquals('bar', $sessionHandler->get('foo'));

        $sessionHandler->clear();
        $this->assertNull($sessionHandler->get('foo'));


        // For coverage
        $sessionHandler->save();
    }


    public function testSetIdWithSessionOpened()
    {
        $this->setExpectedException(ChangeOpenedSessionIdException::class);

        $sessionHandler = $this->buildMock();

        $factory = $this->getFunctionMockFactory();
        $ns = $factory->getNamespace(PhpSession::class);
        $sessionStatusMock = $factory->get($ns, 'session_status', true);


        $sessionStatusMock->setResult(PHP_SESSION_ACTIVE);
        $sessionHandler->setId('foo');
    }
}
