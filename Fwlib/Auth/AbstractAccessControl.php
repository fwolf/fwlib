<?php
namespace Fwlib\Auth;

/**
 * Control access by information from user session
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
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
