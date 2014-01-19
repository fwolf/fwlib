<?php
namespace Fwlib\Auth;

use Fwlib\Auth\AccessControlInterface;
use Fwlib\Auth\UserSessionInterface;

/**
 * Control access by information from user session
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Cbtms@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-19
 */
abstract class AbstractAccessControl implements AccessControlInterface
{
    /**
     * @var UserSessionInterface
     */
    protected $userSession = null;


    /**
     * {@inheritdoc}
     */
    public function __construct(UserSessionInterface $userSession)
    {
        $this->userSession = $userSession;
    }
}
