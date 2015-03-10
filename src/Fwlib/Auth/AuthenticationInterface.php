<?php
namespace Fwlib\Auth;

/**
 * Do authenticate and update user session if successful
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface AuthenticationInterface
{
    /**
     * Do authenticate, got identity and update user session
     *
     * @return  boolean
     */
    public function authenticate();


    /**
     * Get authenticated identity
     *
     * @return  string
     */
    public function getIdentity();


    /**
     * @return  UserSessionInterface
     */
    public function getUserSession();


    /**
     * @param   UserSessionInterface    $userSession
     * @return  static
     */
    public function setUserSession($userSession);
}
