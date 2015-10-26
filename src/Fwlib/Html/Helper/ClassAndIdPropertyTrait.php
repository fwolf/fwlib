<?php
namespace Fwlib\Html\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ClassAndIdPropertyTrait
{
    /**
     * Class in html output, or class prefix of its child element
     *
     * Can have space in it to present multiple classes, leave the one need
     * to attach suffix at last position.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Id in html output, or id prefix of its child element
     *
     * Need to be unique.
     *
     * @var string
     */
    protected $id = '';


    /**
     * @see ClassAndIdPropertyInterface::getClass()
     *
     * @param   string $suffix
     * @return  string
     */
    public function getClass($suffix = '')
    {
        return empty($this->class) ? ''
            : $this->class . $suffix;
    }


    /**
     * @see ClassAndIdPropertyInterface::getId()
     *
     * @param   string $suffix
     * @return  string
     */
    public function getId($suffix = '')
    {
        return empty($this->id) ? ''
            : $this->id . $suffix;
    }


    /**
     * @see ClassAndIdPropertyInterface::setClass()
     *
     * @param   string $class
     * @return  static
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }


    /**
     * @see ClassAndIdPropertyInterface::setId()
     *
     * @param   string $idString
     * @return  static
     */
    public function setId($idString)
    {
        $this->id = $idString;

        return $this;
    }
}
