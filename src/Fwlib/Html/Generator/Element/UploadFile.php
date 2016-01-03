<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;
use Fwlib\Html\Generator\Helper\GetHiddenValueHtmlTrait;
use Fwlib\Html\Generator\UploadFileElementInterface;

/**
 * UploadFile input
 *
 * Uploaded file should be saved after post(set value), and keep saved id for
 * shown, so show output here is just dummy, show id of a file is nonsense.
 *
 * @copyright   Copyright 2016 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UploadFile extends AbstractElement implements UploadFileElementInterface
{
    use GetHiddenValueHtmlTrait;


    const CFG_TAG = 'tag';


    /**
     * {@inheritdoc}
     *
     * Configs
     * - tag: Use div or p or none html tag in show mode, default: none.
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            self::CFG_TAG => 'span',
        ]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        $value = $this->getValue();
        if (!empty($value)) {
            return $this->getOutputForShowMode();
        }

        $output = "<input type='file'" .
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
        $tag = $this->getTag();

        if (empty($tag)) {
            return $valueHtml;
        }

        $output = $this->getHiddenValueHtml() . "\n" .
            "<$tag" .
            $this->getClassHtml() .
            $this->getIdHtml() .
            $this->getRawAttributesHtml() .
            ">" .
            $valueHtml .
            "</$tag>";

        return $output;
    }


    /**
     * @return  string
     */
    protected function getTag()
    {
        return $this->getConfig(self::CFG_TAG);
    }
}
