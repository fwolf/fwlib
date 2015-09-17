<?php
namespace Fwlib\Html\Generator\Component\Form;

use Fwlib\Html\Generator\Component\ButtonSet;
use Fwlib\Html\Generator\ElementCollection;
use Fwlib\Web\HttpRequest;

/**
 * Form
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Form extends ElementCollection
{
    const METHOD_GET = 'get';

    const METHOD_POST = 'post';


    /**
     * Form action in edit mode
     *
     * @var string
     */
    protected $action = '';

    /**
     * @var ButtonSet
     */
    protected $buttons = null;

    /**
     * @var bool
     */
    protected $contentsReceived = false;

    /**
     * Form submit method
     *
     * @var string
     */
    protected $method = self::METHOD_POST;

    /**
     * @var Renderer
     */
    protected $renderer = null;

    /**
     * @var HttpRequest
     */
    protected $request = null;

    /**
     * Should only be used when $validateResult is false.
     *
     * @var string[]
     */
    protected $validateMessages = [];

    /**
     * Only have value when validate() is called.
     *
     * @var bool
     */
    protected $validatePass = null;

    /**
     * @var Validator
     */
    protected $validator = null;


    /**
     * @return  string
     */
    public function getAction()
    {
        return $this->action;
    }


    /**
     * @return  ButtonSet
     */
    public function getButtons()
    {
        if (is_null($this->buttons)) {
            $this->buttons = new ButtonSet;
        }

        return $this->buttons;
    }


    /**
     * @return  string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @return  string
     */
    public function getOutput()
    {
        $this->receiveContents();

        $renderer = $this->getRenderer();

        $renderer->setForm($this);

        return $renderer->getOutput();
    }


    /**
     * @return  Renderer
     */
    public function getRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = new Renderer();
        }

        return $this->renderer;
    }


    /**
     * @return  HttpRequest
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = HttpRequest::getInstance();
        }

        return $this->request;
    }


    /**
     * @return  string[]
     */
    public function getValidateMessages()
    {
        return $this->validateMessages;
    }


    /**
     * @return  Validator
     */
    public function getValidator()
    {
        if (is_null($this->validator)) {
            $this->validator = new Validator;
        }

        return $this->validator;
    }


    /**
     * @return  boolean
     */
    public function isValid()
    {
        if (is_null($this->validatePass)) {
            $this->validatePass = $this->validate();
        }

        return $this->validatePass;
    }


    /**
     * Receive contents from previous form submit, if submitted
     *
     * @return  static
     */
    public function receiveContents()
    {
        if ($this->contentsReceived) {
            return $this;
        }

        $method = $this->getMethod();
        $request = $this->getRequest();
        $contents = [];
        if (self::METHOD_POST == $method) {
            $contents = $request->getPosts();
        } elseif (self::METHOD_GET == $method) {
            $contents = $request->getGets();
        }

        $elements = $this->getElements();
        foreach ($contents as $key => $val) {
            if (array_key_exists($key, $elements)) {
                $elements[$key]->setValue($val);
            }
        }

        $this->contentsReceived = true;

        return $this;
    }


    /**
     * Setter of form action
     *
     * @param   string $action
     * @return  static
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }


    /**
     * @param   ButtonSet $buttons
     * @return  static
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;

        return $this;
    }


    /**
     * @param   string $method
     * @return  static
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }


    /**
     * @param   Renderer $renderer
     * @return  static
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }


    /**
     * @param   HttpRequest $request
     * @return  static
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }


    /**
     * @param   Validator $validator
     * @return  static
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }


    /**
     * Do validate and grab result
     *
     * @return  bool
     */
    public function validate()
    {
        $this->receiveContents();

        $validator = $this->getValidator();

        $validator->setForm($this);

        $this->validatePass = $validator->validate();
        $this->validateMessages = $validator->getMessages();

        return $this->validatePass;
    }
}
