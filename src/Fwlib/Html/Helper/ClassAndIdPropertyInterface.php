<?php
namespace Fwlib\Html\Helper;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ClassAndIdPropertyInterface
{
    /**
     * @param   string $suffix
     * @return  string
     */
    public function getClass($suffix = '');


    /**
     * @param   string $suffix
     * @return  string
     */
    public function getId($suffix = '');


    /**
     * @param   string $class
     * @return  $this
     */
    public function setClass($class);


    /**
     * @param   string $idString
     * @return  $this
     */
    public function setId($idString);
}
