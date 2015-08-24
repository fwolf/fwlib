<?php
namespace Fwlib\Html\Generator\Element;

/**
 * Html button: reset
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class SubmitButton extends Button
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'submit';


    /**
     * {@inheritdoc}
     *
     * Configs
     * - sleepTime: Disable microseconds after click submit button
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        return array_merge($configs, [
            'sleepTime' => 0,
        ]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $output = parent::getOutputForShowMode();

        $idStr = $this->getId();
        $sleepTime = $this->getSleepTime();

        if (!empty($idStr) && 0 < $sleepTime) {
            $output .= "\n\n" . $this->getSleepJs($idStr, $sleepTime);
        }

        return $output;
    }


    /**
     * Get js to disable button
     *
     * Only work for form submit button, by prevent submit action in submit
     * event handler.
     *
     * @link https://stackoverflow.com/questions/5691054
     *
     * @param   string $idStr
     * @param   int    $sleepTime
     * @return  string
     */
    protected function getSleepJs($idStr, $sleepTime)
    {
        return <<<TAG
<script type='text/javascript'>
(function () {
  $('#{$idStr}').on('click', function () {
    $(this).closest('form').submit(function () {
      $('#{$idStr}').attr('disabled', true);
      setTimeout(function () {
        $('#{$idStr}').removeAttr('disabled');
      }, {$sleepTime});
    });
  });
}) ();
</script>
TAG;
    }


    /**
     * @return  int
     */
    protected function getSleepTime()
    {
        return $this->getConfig('sleepTime');
    }
}
