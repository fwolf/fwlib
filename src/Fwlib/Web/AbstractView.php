<?php
namespace Fwlib\Web;

use Fwlib\Bridge\Smarty;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * View in MVC
 *
 * Receive request from Controller and generate output.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractView implements ViewInterface
{
    use UtilContainerAwareTrait;
    use HtmlHelperAwareTrait;


    /**
     * Current action string
     *
     * @var string
     */
    protected $action = '';

    /**
     * Prefix of method to generate output
     *
     * @var string
     * @see getOutputBody()
     */
    protected $methodPrefix = 'fetch';

    /**
     * Current module, used to generate urls
     *
     * @var string
     */
    protected $module = '';

    /**
     * Use Smarty to build html from template
     *
     * @var Smarty
     */
    protected $smarty = null;

    /**
     * Parts of output
     *
     * The parts to build output content. Each part need a corresponding
     * getOutput[PartName]() method.
     *
     * In default, the order of parts defined is used when combine output
     * content, but sometimes we need to combine parts with different sequence
     * with the order they are generated. For example, we need to change
     * header content in body treatment, but the generated body content is
     * still after header content. For scene like this, we can give each part
     * an integer index, the content generate will follow the index order
     * ascending.
     *
     * @var array
     */
    protected $outputParts = [
        1 => 'header',
        0 => 'body',
        2 => 'footer',
    ];

    /**
     * View title
     *
     * In common, title will present as html page <title>.
     *
     * @var string
     */
    protected $title = '';


    /**
     * Get output
     *
     * When $action is empty, only show header and footer.
     *
     * @return  string
     */
    public function getOutput()
    {
        // Generate parts with index order
        $parts = $this->outputParts;
        ksort($parts);

        $outputParts = [];
        foreach ($parts as $part) {
            $method = 'getOutput' . ucfirst($part);

            if (!method_exists($this, $method)) {
                throw new \Exception(
                    "View method for part $part is not defined"
                );
            }

            $outputParts[$part] = $this->$method();
        }


        // Combine parts with define order
        $output = '';
        foreach ($this->outputParts as $part) {
            $output .= $outputParts[$part];
        }


        return $output;
    }


    /**
     * Get output of body part
     *
     * In this implement, output is retrieved from corresponding method, whose
     * name is converted from $action by adding prefix. Eg, action 'foo-bar'
     * will call fetchFooBar() for result. Child class can change
     * $methodPrefix or use different mechanism.
     *
     * @return  string
     */
    protected function getOutputBody()
    {
        if (empty($this->action)) {
            return '';
        }

        $stringUtil = $this->getUtilContainer()->getString();

        $method = $this->methodPrefix . $stringUtil->toStudlyCaps($this->action);
        if (!method_exists($this, $method)) {
            throw new \Exception(
                "View {$this->methodPrefix} method for action {$this->action} is not defined"
            );
        }

        return $this->$method();
    }


    /**
     * Get output of footer part
     *
     * @return  string
     */
    protected function getOutputFooter()
    {
        return '<!-- footer -->';
    }


    /**
     * Get output of header part
     *
     * @return  string
     */
    protected function getOutputHeader()
    {
        return '<!-- header -->';
    }


    /**
     * Setter of $action
     *
     * @param   string  $action
     * @return  AbstractView
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }


    /**
     * Setter of module
     *
     * @param   string  $module
     * @return  AbstractView
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }


    /**
     * Setter of $outputParts
     *
     * @param   array   $outputParts
     * @return  AbstractView
     */
    public function setOutputParts($outputParts)
    {
        $this->outputParts = $outputParts;

        return $this;
    }


    /**
     * Set title of view
     *
     * @param   string  $title
     * @return  AbstractView
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
