<?php
namespace Fwlib\Auth;

use Fwlib\Auth\UserSessionInterface;

/**
 * Do authenticate and save user session if successful
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-18
 */
interface AuthenticationInterface
{
    /**
     * Constructor
     *
     * @param   UserSessionInterface    $userSession
     */
    public function __construct(UserSessionInterface $userSession = null);


    /**
     * Do authenticate, got identity and save session
     *
     * @return  boolean
     */
    public function authenticate();
}
