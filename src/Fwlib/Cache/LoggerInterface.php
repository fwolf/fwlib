<?php
namespace Fwlib\Cache;

/**
 * Logger for cache handler operate
 *
 * Format of log entry:
 * {operate: get/set, key: string, success: bool}
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface LoggerInterface
{
    /**
     * @return  array
     */
    public function getLogs();


    /**
     * Record a log
     *
     * @param   string $operate
     * @param   string $key
     * @param   bool   $success
     * @return  static
     */
    public function log($operate, $key, $success);
}
