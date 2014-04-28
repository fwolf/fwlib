<?php
namespace Fwlib\Db;


/**
 * Sequences of number management
 *
 * Design for profile unique code generate. Use prefix to identify different
 * profile, or profile in different category.
 *
 * Should use db transaction for concurrence, or similar mechanishm for non-db
 * storage. Even though this class may not suitable for high concurrence
 * system, which should use native db sequence/identity or better others.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-04-28
 */
abstract class AbstractSequence
{
    /**
     * Get a number and prepare next
     *
     * @param   string  $prefix
     * @param   integer $step
     * @return  integer
     */
    public function get($prefix, $step = 1)
    {
        $this->lockStorage($prefix);

        $current = $this->read($prefix);

        if (is_null($current)) {
            $current = $this->getStartNumber($prefix);
            $this->initialize($prefix, $current + $step);

        } else {
            $this->increase($prefix, $step);
        }

        $this->unlockStorage($prefix);

        return $current;
    }


    /**
     * Get start number of prefix
     *
     * In common the value will be 1, but sometimes we need extend and rewrite
     * this method in child class, to get start number from exists used code.
     *
     * @param   string  $prefix
     * @return  integer
     */
    protected function getStartNumber($prefix)
    {
        return 1;
    }


    /**
     * Increase value of a prefix
     *
     * @param   string  $prefix
     * @param   integer $step
     */
    abstract protected function increase($prefix, $step);


    /**
     * Create new record in storage for prefix and set value
     *
     * @param   string  $prefix
     * @param   integer $value
     */
    abstract protected function initialize($prefix, $value);


    /**
     * Lock storage, prohibit others to read/write
     *
     * @param   string  $prefix
     */
    abstract protected function lockStorage($prefix);


    /**
     * Read current value of prefix from storage
     *
     * Return null if current prefix has no corresponding value.
     *
     * @param   string  $prefix
     * @return  integer|null
     */
    abstract protected function read($prefix);


    /**
     * Unlock storage, allow others to read/write
     *
     * @param   string  $prefix
     */
    abstract protected function unlockStorage($prefix);
}
