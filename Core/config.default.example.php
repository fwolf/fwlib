<?php
/**
 * Configure file
 *
 * Usage:
 *  1. Copy to a new file named 'config.php'
 *  2. (Optional)Remove code outside of 'Config define area'
 *  3. Change defines
 *  4. (Optional)Remove defines no change needed
 *
 * For defines which need compute to get final result:
 *  1. Remove from 'config.php', use compute job in 'config.default.php'
 *  2. Do compute in 'config.php'
 *
 * DO NOT MODIFY 'config.default.php' DIRECTLY.
 *
 * @package     Fwlib
 * @subpackage  Core
 * @copyright   Copyright © 2013, Fwolf
 * @author      Fwolf <fwolf.aide+fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-07-26
 */

namespace Vendor\App;


// Init global config array
if ('config.default.php' == basename(__FILE__)) {
    $cfg = array();


    // Load user config if exists
    if (file_exists(__DIR__ . '/config.php')) {
        require __DIR__ . '/config.php';
    }
    $cfgUser = $cfg;


    // Load requirement lib autoload file
    // Fwlib
    if (!isset($cfg['lib.path.fwlib'])) {
        $cfg['lib.path.fwlib'] = 'Fwlib/';
    }
    require $cfg['lib.path.fwlib'] . 'autoload.php';
}


/***********************************************************
 * Config define area
 *
 * Use $cfgUser to compute value if needed.
 *
 * In config.php, code outside this area can be removed.
 **********************************************************/


// Group 1
$cfg['group.key'] = 'val';


/***********************************************************
 * Config define area end
 **********************************************************/


// Merge config
$cfg = array_merge($cfg, $cfgUser);
