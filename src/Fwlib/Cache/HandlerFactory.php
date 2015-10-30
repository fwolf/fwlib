<?php
namespace Fwlib\Cache;

use Fwlib\Base\SingleInstanceTrait;
use Fwlib\Cache\Exception\CacheHandlerNotImplementedException;
use Fwlib\Cache\Handler\File;
use Fwlib\Cache\Handler\Memcached;
use Fwlib\Cache\Handler\MemcachedWithVersion;
use Fwlib\Cache\Handler\PhpArray;
use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HandlerFactory
{
    use SingleInstanceTrait;


    /**
     * @param   string $type Cache type, see {@see getClassMap()}
     * @return  CacheHandlerInterface
     * @throws  CacheHandlerNotImplementedException
     */
    public function create($type = '')
    {
        $classMap = $this->getClassMap();
        $type = ucfirst($type);

        if (!array_key_exists($type, $classMap)) {
            throw new CacheHandlerNotImplementedException(
                "Cache handler for type '$type' is not implemented"
            );
        }

        $className = $classMap[$type];

        return new $className;
    }


    /**
     * Getter of class map
     *
     * Can extend this method to provide more handlers
     *
     * @return  string[]    {name: FQN}
     */
    protected function getClassMap()
    {
        return [
            'File'                 => File::class,
            'Memcached'            => Memcached::class,
            'MemcachedWithVersion' => MemcachedWithVersion::class,
            'PhpArray'             => PhpArray::class,
        ];
    }
}
