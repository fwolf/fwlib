<?php
namespace Fwlib\Auth;

use Fwlib\Auth\UserSessionInterface;

/**
 * Control access by information from user session
 *
 * This class have some different with ACL, which need pre-define or assign
 * resource. This class directly take user and session information like user
 * privilege, group etc, and determine allow access or not.
 *
 * Each page need authorization should use this class to check, this can be
 * done in MVC View class. Without centralize authorization, this mechanishm
 * provide more flexiblity.
 *
 * Besides allow and deny judgement, this class can also provide some assist
 * information, like if current user has specified privilege, to be used in
 * production logic.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-19
 */
interface AccessControlInterface
{
    /**
     * Constructor
     *
     * @param   UserSessionInterface    $userSession
     */
    public function __construct(UserSessionInterface $userSession);
}
