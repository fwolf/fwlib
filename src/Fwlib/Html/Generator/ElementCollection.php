<?php
namespace Fwlib\Html\Generator;

use Fwlib\Html\Helper\ClassAndIdPropertyTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Collection of html generator element
 *
 * Collection include multiple elements, can be used to generate show/detail
 * page, also as base part of form generator.
 *
 * Collection can share same class name and id prefix, but they will only
 * apply to element if element has not assigned class/id.
 *
 * Some property in Collection have same name with element, but they work in
 * different way. Eg: $mode can not overwrite element inner default mode. Eg:
 * $class and $id will combine with $mode or $name when assigned to element,
 * BTW these two property can also be used on collection output container like
 * <div>.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ElementCollection implements \ArrayAccess
{
    use UtilContainerAwareTrait;
    use ClassAndIdPropertyTrait;

    /**
     * Elements array, index by name
     *
     * @var ElementInterface[]
     */
    protected $elements = [];

    /**
     * Indent of output html, with space
     *
     * @var int
     */
    protected $indent = 0;

    /**
     * Mode for generate html code
     *
     * Note this can not overwrite element default mode.
     *
     * @var string
     */
    protected $mode = ElementMode::SHOW;

    /**
     * Path to root, maybe needed when reference template or other resources
     *
     * This maybe useless and consider remove in future.
     *
     * @var string
     */
    protected $rootPath = null;

    /**
     * Separator between element output
     *
     * @var string
     */
    protected $separator = PHP_EOL;


    /**
     * @param   ElementInterface $element
     * @return  static
     */
    public function append(ElementInterface $element)
    {
        $name = $element->getName();

        $this->elements[$name] = $element;

        return $this;
    }


    /**
     * @return  ElementInterface[]
     */
    public function getElements()
    {
        return $this->elements;
    }


    /**
     * @return  int
     */
    public function getIndent()
    {
        return $this->indent;
    }


    /**
     * @return  string
     */
    public function getMode()
    {
        return $this->mode;
    }


    /**
     * Get html output
     *
     * The output is combine of elements output.
     *
     * In this implement, output has NO container, this can change by inherit.
     *
     * @return  string
     */
    public function getOutput()
    {
        $mode = $this->getMode();
        $output = '';

        foreach ($this->elements as $element) {
            $element = $this->prepare($element);
            $output .= $element->getOutput($mode) . $this->getSeparator();
        }

        $output = trim($output);

        if (0 < $this->indent) {
            $stringUtil = $this->getUtilContainer()->getString();
            $output = $stringUtil->indentHtml($output, $this->getIndent());
        }

        return $output;
    }


    /**
     * @return  string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }


    /**
     * @return  string
     */
    public function getSeparator()
    {
        return $this->separator;
    }


    /**
     * Insert after an exists element
     *
     * @param   ElementInterface $element
     * @param   string           $brother Insert after this element
     * @return  static
     */
    public function insert(ElementInterface $element, $brother)
    {
        $name = $element->getName();

        $arrayUtil = $this->getUtilContainer()->getArray();

        $this->elements = $arrayUtil->insert(
            $this->elements,
            $brother,
            [$name => $element],
            1
        );

        return $this;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if
     *                      non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->elements);
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }


    /**
     * Assign collection shared property to element
     *
     * @param   ElementInterface $element
     * @return  ElementInterface
     */
    protected function prepare(ElementInterface $element)
    {
        $name = $element->getName();
        $mode = $element->getMode($this->getMode());

        $class = $element->getClass();
        if (empty($class)) {
            $element->setClass($this->getClass("__$mode"));
        }

        $idStr = $element->getId();
        if (empty($idStr)) {
            $element->setId($this->getId("-$name"));
        }

        return $element;
    }


    /**
     * @param   ElementInterface $element
     * @return  static
     */
    public function prepend(ElementInterface $element)
    {
        $name = $element->getName();

        $this->elements = array_merge([$name => $element], $this->elements);

        return $this;
    }


    /**
     * @param   ElementInterface[] $elements
     * @return  static
     */
    public function setElements($elements)
    {
        $this->elements = $elements;

        return $this;
    }


    /**
     * @param   int $indent
     * @return  static
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;

        return $this;
    }


    /**
     * @param   string $mode
     * @return  static
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }


    /**
     * @param   string $rootPath
     * @return  static
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }


    /**
     * @param   string $separator
     * @return  static
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }
}
