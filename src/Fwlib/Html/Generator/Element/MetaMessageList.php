<?php
namespace Fwlib\Html\Generator\Element;

/**
 * Meta message list
 *
 * Messages and metas are both array with same keys, the meta value explain
 * these keys, and will show together with message.
 *
 * Commonly use to show fail message, the keys are form input name.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class MetaMessageList extends PlainList
{
    /**
     * {@inheritdoc}
     *
     * Configs
     * - metas: Explain of message key, {name: {title}} or {name: title}.
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            'metas' => [],
        ]);
    }


    /**
     * @return  array
     */
    public function getMessages()
    {
        $messages = $this->getConfig('messages');
        $metas = $this->getMetas();

        foreach ($messages as $name => &$message) {
            $title = isset($metas[$name])
                ? $metas[$name]
                : $name;

            $message = "{$title}: {$message}";
        }
        unset($message);

        return $messages;
    }


    /**
     * @return  array
     */
    public function getMetas()
    {
        $metas = $this->getConfig('metas', []);

        $newMetas = [];
        foreach ($metas as $name => $meta) {
            if (is_array($meta) && array_key_exists('title', $meta)) {
                $newMetas[$name] = $meta['title'];

            } elseif (is_string($meta)) {
                $newMetas[$name] = $meta;
            }
        }

        return $newMetas;
    }


    /**
     * @param   array $messages
     * @return  static
     */
    public function setMessages(array $messages)
    {
        $this->setConfig('messages', $messages);

        return $this;
    }


    /**
     * @param   array $metas
     * @return  static
     */
    public function setMetas(array $metas)
    {
        $this->setConfig('metas', $metas);

        return $this;
    }
}
