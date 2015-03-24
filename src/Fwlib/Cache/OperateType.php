<?php
namespace Fwlib\Cache;

/**
 * Type of cache operate
 *
 * @see https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class OperateType
{
    /** @var string */
    const DELETE = 'delete';

    /** @var string */
    const GET = 'get';

    /** @var string */
    const SET = 'set';
}
