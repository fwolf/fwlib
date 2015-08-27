<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\Helper\GetJsLoadHtmlTrait;

/**
 * Alert box with pure js
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractJsAlert extends AbstractElement
{
    use GetJsLoadHtmlTrait;


    /**
     * {@inheritdoc}
     *
     * Configs
     * - messages: Messages to alert, string[], auto number index
     * - showBackground: boolean, default true
     * - showClose: boolean, default true
     */
    protected function getDefaultConfigs()
    {
        return array_merge(parent::getDefaultConfigs(), [
            'messages'       => [],
            'showBackground' => true,
            'showCloseLink'  => true,
        ]);
    }


    /**
     * @return string[]
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
        if (empty($messages)) {
            return '';
        }

        $title = $this->getTitle();
        $idStr = $this->getId();

        $showCloseLink = $this->isShowCloseLink()
            ? 'true' : 'false';
        $showBackground = $this->isShowBackground()
            ? 'true' : 'false';

        $output = $this->getJsLoadHtml() .
            "<script type='text/javascript'>
<!--
(function () {
  JsAlert(
    ['" . implode("', '", $messages) . "'],
    '$title',
    '$idStr',
    $showCloseLink,
    $showBackground
  );
}) ();
-->
</script>";

        return $output;
    }


    /**
     * @return  bool
     */
    protected function isShowBackground()
    {
        return $this->getConfig('showBackground');
    }


    /**
     * @return  bool
     */
    protected function isShowCloseLink()
    {
        return $this->getConfig('showCloseLink');
    }
}
