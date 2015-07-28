<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;

/**
 * Radio input
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Radio extends AbstractElement
{
    /**
     * @var string
     */
    protected $unknownValueTitle = 'Invalid Data';


    /**
     * {@inheritdoc}
     *
     * Configs
     * - values: Value => title pair, value should not include whitespace.
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            'values' => [],
        ]);
    }


    /**
     * {@inheritdoc}
     *
     * @see http://html.cita.illinois.edu/nav/form/radio/
     */
    protected function getOutputForEditMode()
    {
        $values = $this->getValues();

        $radioValue = $this->getValue();

        $output = '';
        $rawAttributes = $this->getRawAttributes();

        foreach ($values as $value => $title) {
            $checked = ($radioValue == $value) ? " checked='checked'" : '';
            $valueId = $this->getId("--$value");
            $titleClass = $this->getTitleClass();
            $titleId = $this->getTitleId($value);

            $nameHtml = trim($this->getNameHtml());

            $output .= <<<TAG
  <input type='radio'{$this->getClassHtml()}{$this->getIdHtml($valueId)}
    {$nameHtml} value='{$value}'{$checked}{$rawAttributes} />
  <label{$this->getClassHtml($titleClass)}{$this->getIdHtml($titleId)}
    for='{$valueId}'>{$title}</label>

TAG;
        }

        $containerClass = $this->getClass('__container');
        $containerId = $this->getId('__container');
        $output = trim($output);
        $output = <<<TAG
<fieldset{$this->getClassHtml($containerClass)}{$this->getIdHtml($containerId)}>
  $output
</fieldset>
TAG;

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $values = $this->getValues();
        $radioValue = $this->getValue();

        $title = array_key_exists($radioValue, $values)
            ? $values[$radioValue]
            : $this->unknownValueTitle;

        $output = "<input type='hidden'" .
            $this->getIdHtml() .
            $this->getNameHtml() .
            $this->getValueHtml(ElementMode::EDIT) .
            $this->getRawAttributes() .
            " />\n" .
            "<span" .
            $this->getClassHtml($this->getTitleClass()) .
            $this->getIdHtml($this->getTitleId($radioValue)) .
            ">" .
            $title .
            "</span>";

        return $output;
    }


    /**
     * @return string
     */
    protected function getTitleClass()
    {
        $class = $this->getClass();

        $class = empty($class) ? '' : $class . '__title';

        return $class;
    }


    /**
     * @param   string $value
     * @return string
     */
    protected function getTitleId($value)
    {
        $idStr = $this->getId();

        $idStr = empty($idStr) ? '' : $idStr . "__title--$value";

        return $idStr;
    }


    /**
     * @return  array
     */
    protected function getValues()
    {
        return $this->getConfig('values');
    }
}
