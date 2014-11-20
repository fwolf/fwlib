<?php
namespace Fwlib\Base;

/**
 * Singleton class template
 *
 * Should change to trait after PHP 5.4.0.
 *
 * Use only when class REALLY NEED to be singleton, that is say, if class is
 * not singleton, may cause error or waste mass resource. Follow this
 * principle to reduce class inheritance hierarchies.
 *
 * @link http://www.phptherightway.com/pages/Design-Patterns.html
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
abstract class AbstractSingleton
{
    /**
     * Prevent clone method
     */
    private function __clone()
    {
    }


    /**
     * Prevent 'new' operator
     */
    protected function __construct()
    {
    }


    /**
     * Prevent unserialize method
     *
     * Removed because this prevent mock for test.
     */
    /*
    private function __wakeup()
    {
    }
    */


    /**
     * Get instance of Singleton itself
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
