<?php
namespace Fwlib\Web;

/**
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractController implements ControllerInterface
{
    use ControllerTrait;


    /**
     * Module name
     *
     * If module parsed from user request equals this, will call corresponding
     * View to get output.
     *
     * Root controller can use empty string as module name.
     *
     * @var string
     */
    protected $module = '';
}
