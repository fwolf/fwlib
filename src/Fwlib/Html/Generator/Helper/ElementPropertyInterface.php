<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * Inherited by {@see \Fwlib\Html\Generator\ElementInterface}.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ElementPropertyInterface
{
    /**
     * @return  string
     */
    public function getComment();


    /**
     * @return  string
     */
    public function getName();


    /**
     * @return  string
     */
    public function getTip();


    /**
     * @return  string
     */
    public function getTitle();


    /**
     * @return  \string[]
     */
    public function getValidateRules();


    /**
     * Consider default value config if null.
     *
     * @return  mixed
     */
    public function getValue();


    /**
     * @param   string $comment
     * @return  $this
     */
    public function setComment($comment);


    /**
     * @param   string $name
     * @return  $this
     */
    public function setName($name);


    /**
     * @param   string $tip
     * @return  $this
     */
    public function setTip($tip);


    /**
     * @param   string $title
     * @return  $this
     */
    public function setTitle($title);


    /**
     * @param   \string[] $validateRules
     * @return  $this
     */
    public function setValidateRules($validateRules);


    /**
     * @param   mixed $value
     * @return  $this
     */
    public function setValue($value);
}
