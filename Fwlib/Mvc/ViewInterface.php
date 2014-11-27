<?php
namespace Fwlib\Mvc;

/**
 * View interface
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ViewInterface
{
    /**
     * Generate output for given action
     *
     * @return  string
     */
    public function getOutput();
}
