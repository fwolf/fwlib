<?php
namespace Fwlib\Html\Generator;

/**
 * Html element
 *
 * Element is base unit of html page. With proper property set, it can
 * generate output for several modes. Usually they are organized by logic
 * subject.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ElementInterface
{
    /**
     * @return  string
     */
    public function getComment();

    /**
     * @return  int
     */
    public function getIndent();

    /**
     * @param   string  $userMode
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
     * @param   string  $mode
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
     * @param   string  $class
     * @return  static
     */
    public function setClass($class);

    /**
     * @param   string $comment
     * @return  static
     */
    public function setComment($comment);

    /**
     * Set single config
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  static
     */
    public function setConfig($key, $value);

    /**
     * Setter of configs
     *
     * Configs array format:
     *  {config key: config value}
     *
     * String config moved to {@see setStringOptions()}.
     *
     * @param   array   $configs
     * @return  static
     */
    public function setConfigs(array $configs);

    /**
     * @param   string  $identity
     * @return  static
     */
    public function setId($identity);

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
     * @param   string  $name
     * @return  static
     */
    public function setName($name);

    /**
     * @param   string  $rootPath
     * @return  static
     */
    public function setRootPath($rootPath);

    /**
     * Set configs with string style
     *
     * @param   string  $optionString
     * @return  static
     */
    public function setStringOptions($optionString);

    /**
     * @param   string $tip
     * @return  static
     */
    public function setTip($tip);

    /**
     * @param   \string[] $validateRules
     * @return  static
     */
    public function setValidateRules($validateRules);

    /**
     * @param   mixed   $value
     * @return  static
     */
    public function setValue($value);
}
