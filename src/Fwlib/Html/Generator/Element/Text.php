<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;

/**
 * Text input
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Text extends AbstractElement
{
    /**
     * {@inheritdoc}
     *
     * Configs
     * - tag: Use div or p or none html tag in show mode, default: none.
     *
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            'tag' => '',
        ]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        $output = "<input type='text'" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim($this->getNameHtml()) .
            $this->getValueHtml(ElementMode::EDIT) .
            $this->getRawAttributesHtml() .
            " />";

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $valueHtml = $this->getValueHtml(ElementMode::SHOW);
        $tag = $this->getConfig('tag');

        if (empty($tag)) {
            return $valueHtml;
        }

        $output = "<$tag" .
            $this->getClassHtml() .
            $this->getIdHtml() .
            $this->getRawAttributesHtml() .
            ">" .
            $valueHtml .
            "</$tag>";

        return $output;
    }
}
