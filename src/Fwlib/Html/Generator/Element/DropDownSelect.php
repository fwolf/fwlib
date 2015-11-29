<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;
use Fwlib\Html\Generator\Helper\GetTitleClassAndIdTrait;

/**
 * Drop down select box
 *
 * Easy mode for child class: extend and fill initial item array, or overwrite
 * its getter method for dynamic generated item array.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DropDownSelect extends AbstractElement
{
    use GetTitleClassAndIdTrait;


    const KEY_TAG = 'tag';

    const KEY_ITEMS = 'items';

    const KEY_PROMPT = 'prompt';

    const KEY_INVALID = 'invalid';


    /**
     * @var array
     */
    protected $initialItems = [];


    /**
     * {@inheritdoc}
     *
     * Configs
     * - tag: Use div or p or none html tag in show mode.
     * - items: Select able items, {value: title}.
     * - prompt: Show welcome message like 'please select'.
     * - invalid: Show when value not found in items.
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            self::KEY_TAG     => 'span',
            self::KEY_ITEMS   => $this->getInitialItems(),
            self::KEY_PROMPT  => 'Please select',
            self::KEY_INVALID => 'Invalid Item',
        ]);
    }


    /**
     * @return  array
     */
    protected function getInitialItems()
    {
        return $this->initialItems;
    }


    /**
     * @return  string
     */
    protected function getInvalid()
    {
        return $this->getConfig(self::KEY_INVALID);
    }


    /**
     * @return  string[]
     */
    protected function getItems()
    {
        return $this->getConfig(self::KEY_ITEMS);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        $items = $this->getItems();
        $prompt = $this->getPrompt();

        $output = "<select" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim($this->getNameHtml()) .
            $this->getRawAttributesHtml() .
            ">";

        $titleClass = $this->getTitleClass();
        $titleClassHtml = $this->getClassHtml($titleClass);

        if (!empty($prompt)) {
            $output .= <<<TAG

  <option value=''{$titleClassHtml}>$prompt</option>
TAG;
        }

        foreach ($items as $value => $title) {
            $selValue = $this->getValue();
            // In case empty '' is for prompt, while 0 for first value
            $selected = (0 < strlen($selValue) && $value == $selValue)
                ? " selected='selected'" : '';
            $titleId = $this->getTitleId($value);
            $titleIdHtml = $this->getIdHtml($titleId);
            $output .= <<<TAG

  <option value='$value'{$selected}{$titleClassHtml}{$titleIdHtml}>{$title}</option>
TAG;
        }

        $output .= "
</select>";

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $items = $this->getItems();
        $value = $this->getValue();
        $title = array_key_exists($value, $items)
            ? $items[$value]
            : $this->getInvalid();

        $tag = $this->getTag();

        if (empty($tag)) {
            return $title;
        }

        // Only one value will be shown, so title id not include value
        $output = "<input type='hidden'" .
            $this->getIdHtml() .
            $this->getNameHtml() .
            $this->getValueHtml(ElementMode::EDIT) .
            $this->getRawAttributes() .
            " />\n" .
            "<$tag" .
            $this->getClassHtml($this->getTitleClass()) .
            $this->getIdHtml($this->getTitleId()) .
            $this->getRawAttributesHtml() .
            ">" .
            $title .
            "</$tag>";

        return $output;
    }


    /**
     * @return  string
     */
    protected function getPrompt()
    {
        return $this->getConfig(self::KEY_PROMPT);
    }


    /**
     * @return  string
     */
    protected function getTag()
    {
        return $this->getConfig(self::KEY_TAG);
    }
}
