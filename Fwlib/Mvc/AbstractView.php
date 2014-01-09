<?php
namespace Fwlib\Mvc;

use Fwlib\Base\AbstractAutoNewInstance;
use Fwlib\Bridge\Smarty;
use Fwlib\Mvc\ViewInterface;

/**
 * View in MVC
 *
 * Receive request from Controler and generate output.
 *
 * @package     Fwlib\Mvc
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-06
 */
abstract class AbstractView extends AbstractAutoNewInstance implements
    ViewInterface
{
    /**
     * Css to link in output header
     *
     * Format: {key: url}
     *
     * @var array
     */
    protected $css = array();

    /**
     * Js to link in output header
     *
     * Format: {key: url}
     *
     * @var array
     */
    protected $js = array();

    /**
     * Prefix of method to generate output
     *
     * @var string
     * @see getOutputBody()
     */
    protected $methodPrefix = 'fetch';

    /**
     * Use Smarty to build html from template
     *
     * @var Fwlib\Bridge\Smarty
     */
    protected $smarty = null;

    /**
     * Parts of output
     *
     * The parts to build output content. Each part should have a
     * corresponding getOutputPart() method, or a fatal error will throw.
     *
     * The order of parts defined is used when combine output content, so
     * footer part should not define before header part.
     *
     * @var array
     */
    protected $outputPart = array('header', 'body', 'footer');

    /**
     * Switch for format output with tidy extension
     *
     * @var bool
     */
    protected $useTidy = false;

    /**
     * View title
     *
     * In common, title will present as html page <title>.
     *
     * @var string
     */
    protected $title = '';


    /**
     * Contructor
     *
     * @param   string  $pathToRoot
     */
    public function __construct($pathToRoot = null)
    {
        // Unset for auto new
        unset($this->smarty);

        $this->setPathToRoot($pathToRoot);
    }


    /**
     * Get output
     *
     * @param   string  $action
     * @return  string
     */
    public function getOutput($action = '')
    {
        $output = '';

        foreach ($this->outputPart as $part) {
            $method = 'getOutput' . ucfirst($part);

            if (!method_exists($this, $method)) {
                throw new \Exception(
                    "View method for part $part is not defined"
                );
            }

            $output .= $this->$method($action);
        }

        if ($this->useTidy) {
            $output = $this->tidy($output);
        }

        return $output;
    }


    /**
     * Get output of body part
     *
     * In this implement, output is retrieved from corresponding method, whose
     * name is converted from $action by adding prefix. Eg, action 'foo-bar'
     * will call fetchFooBar() for result. Child class can change
     * $methodPrefix or use different mechanishm.
     *
     * @param   string  $action
     * @return  string
     */
    protected function getOutputBody($action = '')
    {
        if (empty($action)) {
            return null;
        }

        $stringUtil = $this->getUtil('StringUtil');

        $method = $this->methodPrefix . $stringUtil->toStudlyCaps($action);
        if (!method_exists($this, $method)) {
            throw new \Exception(
                "View {$this->methodPrefix} method for action {$action} is not defined"
            );
        }

        return $this->$method();
    }


    /**
     * Get output of footer part
     *
     * @param   string  $action
     * @return  string
     */
    protected function getOutputFooter($action = '')
    {
        return $this->smarty->fetch('footer.tpl');
    }


    /**
     * Get output of header part
     *
     * @param   string  $action
     * @return  string
     */
    protected function getOutputHeader($action = '')
    {
        // Avoid duplicate
        $this->css = array_unique($this->css);
        $this->js = array_unique($this->js);

        return $this->smarty->fetch('header.tpl');
    }


    /**
     * New Smarty instance
     *
     * @return  Fwlib\Bridge\Smarty
     */
    protected function newInstanceSmarty()
    {
        $smarty = $this->getService('Smarty');

        // Connect View info to Smarty
        $smarty->assignByRef('css', $this->css);
        $smarty->assignByRef('js', $this->js);
        $smarty->assignByRef('pathToRoot', $this->pathToRoot);
        $smarty->assignByRef('viewTitle', $this->title);

        return $smarty;
    }


    /**
     * Setter of $outputPart
     *
     * @param   array   $outputPart
     * @return  AbstractView
     */
    public function setOutputPart($outputPart)
    {
        $this->outputPart = $outputPart;

        return $this;
    }


    /**
     * Setter of $pathToRoot
     *
     * @param   string  $pathToRoot
     * @return  AbstractView
     */
    public function setPathToRoot($pathToRoot)
    {
        if (!is_null($pathToRoot)) {
            if (DIRECTORY_SEPARATOR != substr($pathToRoot, -1)) {
                $pathToRoot .= DIRECTORY_SEPARATOR;
            }

            $this->pathToRoot = $pathToRoot;
        }

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


    /**
     * Format html with tidy extention
     *
     * @param   string  $html
     * @return  string
     */
    protected function tidy($html)
    {
        if (!class_exists('tidy')) {
            // Not critil error, only record with error_log for reminder
            error_log('Tidy extension is not installed');

            return $html;

        } else {
            $config = array(
                'doctype'       => 'strict',
                'indent'        => true,
                'indent-spaces' => 2,
                'output-xhtml'  => true,
                'wrap'          => 200
            );

            $tidy = new \tidy;
            $tidy->parseString($html, $config, 'utf8');
            $tidy->cleanRepair();

            return tidy_get_output($tidy);
        }
    }


    /**
     * Setter of $useTidy
     *
     * @param   bool    $useTidy
     * @return  AbstractView
     */
    public function useTidy($useTidy)
    {
        $this->useTidy = $useTidy;

        return $this;
    }
}
