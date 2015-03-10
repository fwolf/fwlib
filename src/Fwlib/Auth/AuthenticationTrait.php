<?php
namespace Fwlib\Auth;

/**
 * @see AuthenticationInterface
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait AuthenticationTrait
{
    /**
     * Authenticated user identity
     *
     * @var string
     */
    protected $identity = '';

    /**
     * Leave this null to do authenticate without save operate.
     *
     * @var UserSessionInterface
     */
    protected $userSession = null;


    /**
     * @see AuthenticationInterface::getIdentity()
     *
     * @return  string
     */
    public function getIdentity()
    {
        return $this->identity;
    }


    /**
     * @see AuthenticationInterface::getUserSession()
     *
     * @return  UserSessionInterface
     */
    public function getUserSession()
    {
        return $this->userSession;
    }


    /**
     * @see AuthenticationInterface::setUserSession()
     *
     * @param   UserSessionInterface    $userSession
     * @return  static
     */
    public function setUserSession($userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }
}
