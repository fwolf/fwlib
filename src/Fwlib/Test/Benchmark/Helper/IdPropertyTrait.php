<?php
namespace Fwlib\Test\Benchmark\Helper;

/**
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait IdPropertyTrait
{
    /**
     * @var int
     */
    protected $id = null;


    /**
     * Getter of $id
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }
}
