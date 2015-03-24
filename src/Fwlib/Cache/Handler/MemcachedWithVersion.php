<?php
namespace Fwlib\Cache\Handler;

use Fwlib\Cache\Handler\Memcached as MemcachedHandler;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MemcachedWithVersion extends MemcachedHandler
{
    use WithVersionTrait;


    /**
     * @var string
     */
    protected $versionSuffix = '/ver';
}
