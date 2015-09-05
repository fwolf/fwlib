<?php
namespace Fwlib\Html\Generator\Component;

use Fwlib\Html\Generator\Element\Button;
use Fwlib\Html\Helper\IndentAwareTrait;
use Fwlib\Util\UtilContainer;

/**
 * Html button set
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ButtonSet
{
    use IndentAwareTrait;


    /**
     * Button array, should index by button name
     *
     * @var Button[]
     */
    protected $buttons = [];

    /**
     * Container class in html
     *
     * @var string
     */
    protected $containerClass = 'form__buttons';

    /**
     * Container id in html
     *
     * @var string
     */
    protected $containerId = '';

    /**
     * Container tag in html
     *
     * @var string
     */
    protected $containerTag = 'div';

    /**
     * @var int
     */
    protected $indent = 0;

    /**
     * @var int
     */
    protected $sleepTime = 0;


    /**
     * Add/append a button
     *
     * @param   Button $button
     * @return  static
     */
    public function add(Button $button)
    {
        $name = $button->getName();
        $newButtonAr = (empty($name)) ? [$button] : [$name => $button];

        $this->buttons = array_merge($this->buttons, $newButtonAr);

        return $this;
    }


    /**
     * Apply container to html of buttons
     *
     * @param   string $html Buttons html
     * @return  string
     */
    protected function applyContainer($html)
    {
        $classHtml = empty($this->containerClass) ? ''
            : " class='{$this->containerClass}'";
        $idHtml = empty($this->containerId) ? ''
            : " id='{$this->containerId}'";

        $containerClass = $this->containerClass;
        $sleepTime = $this->sleepTime;
        if (!empty($containerClass) && 0 < $sleepTime) {
            $sleepJs = $this->getSleepJs($containerClass, $sleepTime);
        } else {
            $sleepJs = '';
        }

        $html = $this->indent($html, 2);

        $html = <<<TAG
<{$this->containerTag}{$classHtml}{$idHtml}>
$html
</{$this->containerTag}>

{$sleepJs}
TAG;

        $html = $this->indent($html, $this->indent);

        return $html;
    }


    /**
     * Get output of button set
     *
     * @return  string
     */
    public function getOutput()
    {
        $html = '';

        foreach ($this->buttons as $button) {
            $html .= $button->getOutput() . "\n";
        }

        $html = $this->applyContainer(trim($html));

        return $html;
    }


    /**
     * Disable all submit button in set
     *
     * Disabled submit button will prevent form submit, so put it in submit
     * event handler.
     *
     * @link https://stackoverflow.com/questions/5691054
     *
     * @param   string $containerClass
     * @param   int    $sleepTime
     * @return  string
     */
    protected function getSleepJs($containerClass, $sleepTime)
    {
        return <<<TAG
<script type='text/javascript'>
(function () {
  $('.{$containerClass} input[type=submit]').on('click', function () {
    $(this).closest('form').submit(function () {
      $('.{$containerClass} input[type=submit]').attr('disabled', true);
      setTimeout(function () {
        $('.{$containerClass} input[type=submit]').removeAttr('disabled');
      }, {$sleepTime});
    });
  });
}) ();
</script>
TAG;
    }


    /**
     * Prepend a button
     *
     * @param   Button $button
     * @return  static
     */
    public function prepend(Button $button)
    {
        $name = $button->getName();
        $newButtonAr = (empty($name)) ? [$button] : [$name => $button];

        $this->buttons = array_merge($newButtonAr, $this->buttons);

        return $this;
    }


    /**
     * Remove a button
     *
     * @param   string $name
     * @return  static
     */
    public function remove($name)
    {
        unset($this->buttons[$name]);

        return $this;
    }


    /**
     * @param   string $containerClass
     * @return  static
     */
    public function setContainerClass($containerClass)
    {
        $this->containerClass = $containerClass;

        return $this;
    }


    /**
     * @param   string $idStr
     * @return  static
     */
    public function setContainerId($idStr)
    {
        $this->containerId = $idStr;

        return $this;
    }


    /**
     * @param   string $containerTag
     * @return  static
     */
    public function setContainerTag($containerTag)
    {
        $this->containerTag = $containerTag;

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
     * @param   int $sleepTime
     * @return  static
     */
    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = $sleepTime;

        return $this;
    }
}
