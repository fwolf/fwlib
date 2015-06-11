<?php
namespace Fwlib\Config;

/**
 * Trait for Config aware class also accept StringOptions
 *
 * @see ConfigAwareTrait
 *
 * @method  static  setConfigs(array $configs)
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait StringOptionsAwareTrait
{
    /**
     * Set configs with string style
     *
     * @param   string  $optionString
     * @return  static
     */
    public function setStringOptions($optionString)
    {
        $stringOptions = new StringOptions($optionString);

        $this->setConfigs($stringOptions->getAll());

        return $this;
    }
}
