<?php
namespace Fwlib\Web;

use Fwlib\Web\Exception\InvalidOutputPartException;

/**
 * Trait for common view
 *
 * Commonly, one view will be defined for one request. But sometimes we need a
 * view to hold more than one request, in this case we use methods with same
 * prefix as {@see $methodPrefix}, and call them in {@see getOutputBody()}. If
 * need not this feature, just overwrite {@see getOutputBody()}.
 *
 *
 * Parts to build output content is defined in {@see $outputParts}, each part
 * need a corresponding getOutput[PartName]() method.
 *
 * In default, the order of parts defined is used when combine output content,
 * but sometimes we need to combine parts with different sequence with the order
 * they are generated. For example, we need to change header content in body
 * treatment, but the generated body content is still after header content. For
 * scene like this, we can give each part an integer index, the content generate
 * will follow the index order ascending.
 *
 *
 * @see         ViewInterface
 *
 * @property    array  $outputParts
 * @property    string $title
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ViewTrait
{
    /**
     * Process output before return
     *
     * @param   string $output
     * @return  string
     */
    protected function decorateOutput($output)
    {
        return $output;
    }


    /**
     * @see ViewInterface::getOutput()
     *
     * @return  string
     * @throws  InvalidOutputPartException
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
                throw new InvalidOutputPartException(
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

        $output = $this->decorateOutput($output);

        return $output;
    }


    /**
     * Set title of view
     *
     * @param   string $title
     * @return  static
     */
    protected function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
