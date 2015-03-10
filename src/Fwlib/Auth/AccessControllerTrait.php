<?php
namespace Fwlib\Auth;

/**
 * @see AccessControllerInterface
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait AccessControllerTrait
{
    /**
     * @var UserSessionInterface
     */
    protected $userSession = null;


    /**
     * @see AccessControllerInterface::getUserSession()
     *
     * @return  UserSessionInterface
     */
    public function getUserSession()
    {
        return $this->userSession;
    }


    /**
     * @see AccessControllerInterface::setUserSession()
     *
     * @param   UserSessionInterface    $userSession
     * @return  static
     */
    public function setUserSession(UserSessionInterface $userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }
}
