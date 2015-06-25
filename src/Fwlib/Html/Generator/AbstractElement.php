<?php
namespace Fwlib\Html\Generator;

use Fwlib\Config\StringOptionsAwareTrait;
use Fwlib\Html\Generator\Exception\NotImplementedModeException;
use Fwlib\Html\Generator\Helper\ElementPropertyTrait;
use Fwlib\Html\Helper\ClassAndIdPropertyTrait;
use Fwlib\Html\ListView\ConfigAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwlib\Web\HtmlHelperAwareTrait;

/**
 * Html element base class
 *
 * Element can have different modes, base mode is show and edit.
 * @see ElementMode
 *
 * For complicated element, assistant html tag may have id/class/name too,
 * which should come from main id/class/name by adding solid suffix or other
 * similar rule.
 *
 * In default, should use single quote in html code.
 *
 * For easier to combine later, final output html code should have NO
 * heading/tailing space or empty line, and keep inner elements correct
 * indented.
 *
 * As output generate schema may various from elements, sub class may
 * overwrite output generate method, so its difficult to make a renderer class
 * for this.
 *
 * Configs
 *  default: Default value if unassigned, may used in getValue()
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractElement implements ElementInterface
{
    use UtilContainerAwareTrait;
    use ConfigAwareTrait;
    use StringOptionsAwareTrait;
    use ClassAndIdPropertyTrait;
    use ElementPropertyTrait;
    use HtmlHelperAwareTrait;


    /**
     * Indent of output html, with space
     *
     * @var int
     */
    protected $indent = 0;

    /**
     * Mode for generate html code
     *
     * If set, will ignore user defined mode when generate output, so keep this
     * empty in most case.
     *
     * @var string
     */
    protected $mode = '';

    /**
     * Path to root, maybe needed when reference template or other resources
     *
     * @var string
     */
    protected $rootPath = null;


    /**
     * Get html code for class
     *
     * @param   string  $class  Use this instead of getClass()
     * @return  string
     */
    protected function getClassHtml($class = null)
    {
        $class = is_null($class) ? $this->getClass() : $class;

        return empty($class) ? ''
            : " class='{$class}'";
    }


    /**
     * Get html code for comment
     *
     * @return  string
     */
    protected function getCommentHtml()
    {
        $comment = $this->comment;

        if (empty($comment)) {
            return '';
        }

        $class = $this->getClass('__comment');
        $identity = $this->getId('__comment');

        $classHtml = empty($class) ? '' : $this->getClassHtml($class);
        $idHtml = empty($identity) ? '' : $this->getIdHtml($identity);

        $output = "<div" . $classHtml . $idHtml . ">
  $comment
</div>";

        return $output;
    }


    /**
     * Get html code for id
     *
     * @param   string  $identity     Use this id instead of getId()
     * @return  string
     */
    protected function getIdHtml($identity = null)
    {
        $identity = is_null($identity) ? $this->getId() : $identity;

        return empty($identity) ? ''
            : " id='{$identity}'";
    }


    /**
     * {@inheritdoc}
     */
    public function getIndent()
    {
        return $this->indent;
    }


    /**
     * {@inheritdoc}
     */
    public function getMode($userMode = ElementMode::SHOW)
    {
        if (empty($this->mode)) {
            return $userMode;
        } else {
            return $this->mode;
        }
    }


    /**
     * Get html code for name
     *
     * @param   string  $name   Use this instead of getName()
     * @return  string
     */
    protected function getNameHtml($name = null)
    {
        $name = is_null($name) ? $this->getName() : $name;

        return empty($name) ? ''
            : " name='{$name}'";
    }


    /**
     * {@inheritdoc}
     *
     * @throws  NotImplementedModeException
     */
    public function getOutput($mode = ElementMode::SHOW)
    {
        $mode = $this->getMode($mode);

        $method = 'getOutputFor' . ucfirst($mode) . 'Mode';
        if (!method_exists($this, $method)) {
            throw new NotImplementedModeException(
                "Mode '$mode' is not implemented"
            );
        }

        $output = $this->$method();

        $comment = $this->getComment();
        if (ElementMode::EDIT == $mode && !empty($comment)) {
            $output .= $this->getCommentHtml();
        }

        if (0 < $this->indent) {
            $stringUtil = $this->getUtilContainer()->getString();
            $output = $stringUtil->indentHtml($output, $this->indent);
        }

        return $output;
    }


    /**
     * Get html output for edit mode
     *
     * Some element may not have edit mode.
     *
     * @return  string
     */
    protected function getOutputForEditMode()
    {
        // Dummy
        return $this->getOutputForShowMode();
    }


    /**
     * Get html output for show mode
     *
     * @return  string
     */
    abstract protected function getOutputForShowMode();


    /**
     * {@inheritdoc}
     */
    public function getRootPath()
    {
        if (is_null($this->rootPath)) {
            $htmlHelper = $this->getHtmlHelper();
            $this->rootPath = $htmlHelper->getRootPath();
        }

        return $this->rootPath;
    }


    /**
     * Get html code for value
     *
     * Mode 'edit' will not do extra html encode.
     *
     * @param   mixed   $value  Use this value instead of getValue()
     * @param   string  $mode
     * @param   boolean $encode Encode html code
     * @return  string
     */
    protected function getValueHtml(
        $value = null,
        $mode = null,
        $encode = true
    ) {
        if (is_null($value)) {
            $value = $this->getValue();
        }

        $mode = $this->getMode($mode);

        if ($encode) {
            $extra = (ElementMode::EDIT != $mode);
            $value = $this->getUtilContainer()->getString()
                ->encodeHtml($value, true, $extra, $extra);
        }

        if (ElementMode::EDIT == $mode) {
            return " value='{$value}'";
        } else {
            return $value;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }
}
