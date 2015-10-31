<?php
namespace Fwlib\Cache;

/**
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractCachedCallerAware implements CachedCallerAwareInterface
{
    use CachedCallerAwareTrait;


    /**
     * @var boolean
     */
    protected $forceRefreshCache = false;

    /**
     * @var boolean
     */
    protected $useCache = true;
}
