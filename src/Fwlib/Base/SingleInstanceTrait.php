<?php
namespace Fwlib\Base;

/**
 * Trait of getInstance() aware
 *
 * Singleton or container classes commonly have only one instance, and a
 * static getInstance() method are used to create and return its instance.
 *
 * Only for reuse, should not use as type hint, so no relevant interface,.
 *
 * The difference between this and singleton is, this class does not strictly
 * prohibit multiple instances, maybe useful in special cases.
 *
 * Can not use when constructor need parameters.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait SingleInstanceTrait
{
    /**
     * Get instance of self
     *
     * @return  static
     */
    public static function getInstance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }
}
