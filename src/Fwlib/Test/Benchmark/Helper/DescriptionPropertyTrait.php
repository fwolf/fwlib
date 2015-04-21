<?php
namespace Fwlib\Test\Benchmark\Helper;

/**
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait DescriptionPropertyTrait
{
    /**
     * @var string
     */
    protected $description = '';


    /**
     * Getter of $description
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Setter of $description
     *
     * @param   string  $description
     * @return  static
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
