<?php
namespace Fwlib\Auth\SessionHandler;

use Fwlib\Auth\SessionHandler\Exception\ChangeOpenedSessionIdException;
use Fwlib\Auth\SessionHandler\Exception\PhpSessionDisabledException;
use Fwlib\Auth\SessionHandlerInterface;

/**
 * {@inheritdoc}
 *
 * Use PHP native session functions.
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PhpSession implements SessionHandlerInterface
{
    /**
     * @var string
     */
    protected $sessionId = '';


    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $_SESSION = [];

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        session_destroy();

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return array_key_exists($name, $_SESSION)
            ? $_SESSION[$name]
            : null;
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->sessionId;
    }


    /**
     * {@inheritdoc}
     */
    public function isOpened()
    {
        return PHP_SESSION_ACTIVE == session_status();
    }


    /**
     * {@inheritdoc}
     *
     * @throws  PhpSessionDisabledException
     */
    public function open()
    {
        if (PHP_SESSION_DISABLED == session_status()) {
            throw new PhpSessionDisabledException;
        }

        if (!$this->isOpened()) {
            if (!empty($this->sessionId)) {
                session_id($this->sessionId);
            }

            session_start();

            $this->sessionId = session_id();
        }

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function regenerateId()
    {
        session_regenerate_id();

        $this->sessionId = session_id();

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function save()
    {
    }


    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;

        return $this;
    }


    /**
     * {@inheritdoc}
     *
     * Notice: Will cause problem after
     *
     * @throws  ChangeOpenedSessionIdException
     */
    public function setId($id)
    {
        if ($this->isOpened()) {
            throw new ChangeOpenedSessionIdException;
        }

        $this->sessionId = $id;

        return $this;
    }
}
