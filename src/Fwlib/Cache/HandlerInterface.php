<?php
namespace Fwlib\Cache;

use Fwlib\Config\ConfigAwareInterface;

/**
 * Cache handler interface
 *
 * The unit of lifetime is second.
 *
 * By default, data are all strings. Please notice in common application, data
 * is encoded with json for storage, and read from storage is also string, then
 * convert it to associate array or import with some class, these all should not
 * be job of cache handler. The exception is backend cache management system can
 * operate raw PHP type, do that sure exists ?
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface HandlerInterface extends ConfigAwareInterface
{
    /**
     * Delete cache data
     *
     * @param   string  $key
     * @return  static
     */
    public function delete($key);


    /**
     * Get cache data
     *
     * If $lifetime given, will check if cache data exceeds its lifetime, this
     * is needed for some cache type without auto-expire(eg: file) feature.
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  string
     */
    public function get($key, $lifetime = null);


    /**
     * Getter of logger instance
     *
     * This is public for access logs it records.
     *
     * @return  LoggerInterface | null
     */
    public function getLogger();


    /**
     * Is cache data expire ?
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  bool                True means it IS expired.
     */
    public function isExpired($key, $lifetime = null);


    /**
     * Write cache data
     *
     * If cache type not support auto-expire(eg: file), $lifetime can omit.
     *
     * @param   string  $key
     * @param   string  $val
     * @param   int     $lifetime
     * @return  static
     */
    public function set($key, $val, $lifetime = null);


    /**
     * Setter of logger instance
     *
     * @param   LoggerInterface $logger
     * @return  static
     */
    public function setLogger(LoggerInterface $logger);
}
