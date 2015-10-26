<?php
namespace Fwlib\Html\Generator\Component\Form\Helper;

use Fwlib\Html\Generator\Component\Form\Form;

/**
 * Trait for class use form as property
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FormAwareTrait
{
    /**
     * @var Form
     */
    protected $form = null;


    /**
     * @return  Form
     */
    public function getForm()
    {
        return $this->form;
    }


    /**
     * @param   Form $form
     * @return  $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }
}
