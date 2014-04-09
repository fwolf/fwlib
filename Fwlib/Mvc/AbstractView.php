<?php
namespace Fwlib\Mvc;

use Fwlib\Mvc\ViewInterface;
use Fwlib\Util\UtilContainer;

/**
 * View in MVC
 *
 * Receive request from Controler and generate output.
 *
 * @copyright   Copyright 2008-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-06
 */
abstract class AbstractView implements ViewInterface
{
    /**
     * Current action string
     *
     * @var string
     */
    protected $action = '';

    /**
     * Css to link in output header
     *
     * Format: {key: {url, media}}
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
     * @var Smarty
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
     * Path to root
     *
     * External resource in application local storage will retrieve by
     * relative path to this path.
     *
     * @var string
     */
    protected $pathToRoot = '../../';

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
        $this->setPathToRoot($pathToRoot);
    }


    /**
     * Add content to $js
     *
     * @param   string  $name
     * @param   string  $url
     * @param   string  $media
     * @return  AbstractView
     */
    protected function addCss($name, $url, $media = 'screen, print')
    {
        $this->css[$name] = array(
            'url'   => $url,
            'media' => $media,
        );

        return $this;
    }


    /**
     * Add content to $js
     *
     * @param   string  $name
     * @param   string  $url
     * @return  AbstractView
     */
    protected function addJs($name, $url)
    {
        $this->js[$name] = $url;

        return $this;
    }


    /**
     * Get output
     *
     * When $action is empty, only show header and footer.
     *
     * @return  string
     */
    public function getOutput()
    {
        $output = '';

        foreach ($this->outputPart as $part) {
            $method = 'getOutput' . ucfirst($part);

            if (!method_exists($this, $method)) {
                throw new \Exception(
                    "View method for part $part is not defined"
                );
            }

            $output .= $this->$method();
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
     * @return  string
     */
    protected function getOutputBody()
    {
        if (empty($this->action)) {
            return '';
        }

        $stringUtil = UtilContainer::getInstance()->get('StringUtil');

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
        // Avoid duplicate js, css is 2-dim array, can't do unique on it
        $this->js = array_unique($this->js);

        return '<!-- header -->';
    }


    /**
     * Getter of $useTidy
     *
     * @return  boolean
     */
    public function getUseTidy()
    {
        return $this->useTidy;
    }


    /**
     * Remove css assigned to output
     *
     * @param   string  $name
     * @return  AbstractView
     */
    protected function removeCss($name)
    {
        unset($this->css[$name]);

        return $this;
    }


    /**
     * Remove js assigned to output
     *
     * @param   string  $name
     * @return  AbstractView
     */
    protected function removeJs($name)
    {
        unset($this->js[$name]);

        return $this;
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
     * Setter of $useTidy
     *
     * @param   boolean $useTidy
     * @return  AbstractView
     */
    public function setUseTidy($useTidy)
    {
        $this->useTidy = $useTidy;

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
}
