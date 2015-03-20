<?php
namespace Fwlib\Web;

use Fwlib\Bridge\Smarty;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * View
 *
 * Called by controller and generate output.
 *
 * Commonly, one view will be defined for one request. But sometimes we need a
 * view to hold more than one request, in this case we use methods with same
 * prefix as {@see $methodPrefix}, and call them in {@see getOutputBody()}. If
 * need not this feature, just overwrite {@see getOutputBody()}.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractView implements ViewInterface
{
    use HtmlHelperAwareTrait;
    use RequestAwareTrait;
    use UtilContainerAwareTrait;


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
        $action = $this->getRequest()->getAction();

        if (empty($action)) {
            return '';
        }

        $stringUtil = $this->getUtilContainer()->getString();

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
     * Set title of view
     *
     * @param   string  $title
     * @return  static
     */
    protected function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
