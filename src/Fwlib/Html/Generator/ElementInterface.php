<?php
namespace Fwlib\Html\Generator;

use Fwlib\Config\ConfigAwareInterface;
use Fwlib\Html\Helper\ClassAndIdPropertyInterface;

/**
 * Html element
 *
 * Element is base unit of html page. With proper property set, it can
 * generate output for several modes. Usually they are organized by logic
 * subject.
 *
 * To set config with string style, {@see setStringOptions()}.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ElementInterface extends
    ConfigAwareInterface,
    ClassAndIdPropertyInterface
{
    /**
     * Append to collection
     *
     * @param   ElementCollection $collection
     * @return  static
     */
    public function appendTo(ElementCollection $collection);


    /**
     * @return  string
     */
    public function getComment();


    /**
     * @return  int
     */
    public function getIndent();


    /**
     * @param   string $userMode
     * @return  string
     */
    public function getMode($userMode = ElementMode::SHOW);


    /**
     * @return  string
     */
    public function getName();


    /**
     * Get html output
     *
     * @param   string $mode
     * @return  string
     */
    public function getOutput($mode = 'show');


    /**
     * @return  string
     */
    public function getRootPath();


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
     * Get value of element
     *
     * @return  mixed
     */
    public function getValue();


    /**
     * Insert to collection
     *
     * @param   ElementCollection $collection
     * @param   string            $brother Name of element insert behind
     * @return  static
     */
    public function insertTo(ElementCollection $collection, $brother);


    /**
     * Prepend to collection
     *
     * @param   ElementCollection $collection
     * @return  static
     */
    public function prependTo(ElementCollection $collection);


    /**
     * @param   string $comment
     * @return  static
     */
    public function setComment($comment);


    /**
     * @param   int $indent
     * @return  static
     */
    public function setIndent($indent);


    /**
     * @param   string $mode
     * @return  static
     */
    public function setMode($mode);


    /**
     * @param   string $name
     * @return  static
     */
    public function setName($name);


    /**
     * @param   string $rootPath
     * @return  static
     */
    public function setRootPath($rootPath);


    /**
     * Set configs with string style
     *
     * @param   string $optionString
     * @return  static
     */
    public function setStringOptions($optionString);


    /**
     * @param   string $tip
     * @return  static
     */
    public function setTip($tip);


    /**
     * @param   string $title
     * @return  static
     */
    public function setTitle($title);


    /**
     * @param   \string[] $validateRules
     * @return  static
     */
    public function setValidateRules($validateRules);


    /**
     * @param   mixed $value
     * @return  static
     */
    public function setValue($value);
}
