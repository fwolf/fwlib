<?php
namespace Fwlib\Html\Generator;

use Fwlib\Config\ConfigAwareInterface;
use Fwlib\Html\Generator\Helper\CheckOnBlurPropertyInterface;
use Fwlib\Html\Generator\Helper\CheckOnKeyupPropertyInterface;
use Fwlib\Html\Generator\Helper\ElementPropertyInterface;
use Fwlib\Html\Helper\ClassAndIdPropertyInterface;
use Fwlib\Web\HtmlHelperAwareInterface;

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
    ClassAndIdPropertyInterface,
    ElementPropertyInterface,
    HtmlHelperAwareInterface,
    CheckOnBlurPropertyInterface,
    CheckOnKeyupPropertyInterface
{
    /**
     * Append to collection
     *
     * @param   ElementCollection $collection
     * @return  static
     */
    public function appendTo(ElementCollection $collection);


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
}
