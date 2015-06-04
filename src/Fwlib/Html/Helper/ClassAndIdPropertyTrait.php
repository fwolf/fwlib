<?php
namespace Fwlib\Html\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ClassAndIdPropertyTrait
{
    /**
     * Class in html output
     *
     * Can have space in it to present multiple classes.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Id in html output, need to be unique
     *
     * @var string
     */
    protected $id = '';


    /**
     * Getter of class
     *
     * @return  string
     */
    protected function getClass()
    {
        return $this->class;
    }


    /**
     * Getter of id
     *
     * @return  string
     */
    protected function getId()
    {
        return $this->id;
    }


    /**
     * Setter of class
     *
     * @param   string  $class
     * @return  static
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }


    /**
     * Setter of id
     *
     * @param   string  $idString
     * @return  static
     */
    public function setId($idString)
    {
        $this->id = $idString;

        return $this;
    }
}
