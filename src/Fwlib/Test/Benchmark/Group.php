<?php
namespace Fwlib\Test\Benchmark;

use Fwlib\Test\Benchmark\Helper\BeginAndEndTimePropertyTrait;
use Fwlib\Test\Benchmark\Helper\DescriptionPropertyTrait;
use Fwlib\Test\Benchmark\Helper\DurationPropertyTrait;
use Fwlib\Test\Benchmark\Helper\IdPropertyTrait;

/**
 * Bench markers can be organized to several groups
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Group
{
    use IdPropertyTrait;
    use DescriptionPropertyTrait;
    use BeginAndEndTimePropertyTrait;
    use DurationPropertyTrait;


    /**
     * @param   int     $groupId
     */
    public function __construct($groupId)
    {
        $this->id = $groupId;
    }
}
