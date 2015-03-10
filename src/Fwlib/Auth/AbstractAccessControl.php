<?php
namespace Fwlib\Auth;

/**
 * Control access by information from user session
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractAccessControl implements AccessControlInterface
{
    /**
     * @var UserSessionInterface
     */
    protected $userSession = null;


    /**
     * @param   UserSessionInterface    $userSession
     */
    public function __construct(UserSessionInterface $userSession)
    {
        $this->setUserSession($userSession);
    }


    /**
     * {@inheritdoc}
     */
    public function getUserSession()
    {
        return $this->userSession;
    }


    /**
     * {@inheritdoc}
     */
    public function setUserSession(UserSessionInterface $userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }
}
