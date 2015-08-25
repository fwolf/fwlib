<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;

/**
 * Plain html list(ul and ol)
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PlainList extends AbstractElement
{
    /**
     * {@inheritdoc}
     *
     * Configs
     * - tag: Use div or p or none html tag in show mode, default: none.
     * - messages: To show as list items.
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            'tag'      => 'ul',
            'messages' => [],
        ]);
    }


    /**
     * @return  string[]
     */
    protected function getMessages()
    {
        return $this->getConfig('messages');
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $messages = $this->getMessages();
        $tag = $this->getTag();

        $output = "<$tag" .
            $this->getClassHtml() .
            $this->getIdHtml() .
            $this->getRawAttributesHtml() .
            ">";

        foreach ($messages as $message) {
            $output .= "\n  <li>{$message}</li>";
        }

        $output .= "
</$tag>";

        return $output;
    }


    /**
     * @return  string
     */
    protected function getTag()
    {
        return $this->getConfig('tag');
    }
}
