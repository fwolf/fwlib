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
    protected $css = [];

    /**
     * Js to link in output header
     *
     * Format: {key: url}
     *
     * @var array
     */
    protected $js = [];

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
     * Add content to $js
     *
     * @param   string  $name
     * @param   string  $url
     * @param   string  $media
     * @return  AbstractView
     */
    protected function addCss($name, $url, $media = 'screen, print')
    {
        $this->css[$name] = [
            'url'   => $url,
            'media' => $media,
        ];

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
     * Format html with tidy extension
     *
     * @param   string  $html
     * @return  string
     */
    protected function tidy($html)
    {
        if (!class_exists('tidy')) {
            // Not critical error, only record with error_log for reminder
            error_log('Tidy extension is not installed');

            return $html;

        } else {
            $config = [
                'doctype'       => 'strict',
                'indent'        => true,
                'indent-spaces' => 2,
                'output-xhtml'  => true,
                'wrap'          => 200
            ];

            $tidy = new \tidy;
            $tidy->parseString($html, $config, 'utf8');
            $tidy->cleanRepair();

            return tidy_get_output($tidy);
        }
    }
}
