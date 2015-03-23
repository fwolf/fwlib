<?php
namespace Fwlib\Cache;

/**
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractHandler implements HandlerInterface
{
    use HandlerTrait;


    /**
     * Replacement if user transfer in an empty key
     *
     * @var string
     */
    protected $emptyKeyReplacement = '[emptyKey]';


    /**
     * Algorithm when hash cache key
     *
     * Leave empty to not hash the key.
     *
     * @var string
     */
    protected $hashAlgorithm = '';
}
