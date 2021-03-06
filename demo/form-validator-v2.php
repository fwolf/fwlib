<?php
$pathToRoot = '../';
require __DIR__ . '/' . $pathToRoot . 'config.default.php';

use Fwlib\Base\ReturnValue;
use Fwlib\Config\GlobalConfig;
use Fwlib\Html\Generator\Component\Form\Form;
use Fwlib\Html\Generator\Element\Text;
use Fwlib\Html\Generator\Element\Textarea;
use Fwlib\Html\Generator\ElementMode;
use Fwlib\Net\Curl;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\ConstraintContainer;
use FwlibTest\Aide\TestServiceContainer;

/***************************************
 * Read post data
 **************************************/
$utilContainer = UtilContainer::getInstance();
$httpUtil = $utilContainer->getHttp();

$userTitle = $httpUtil->getPost('userTitle');
$userAge = $httpUtil->getPost('userAge');
$hiddenValue = $httpUtil->getPost('hiddenValue');
$remark = $httpUtil->getPost('remark');

$frontendCheck = 'checked="checked"';
if (!empty($_POST) && is_null($httpUtil->getPost('frontendCheck'))) {
    $frontendCheck = '';
}


/***************************************
 * Treat ajax post
 **************************************/
$action = $httpUtil->getGet('a');
if ('checkAge' == $action) {
    $age = trim($userAge);

    // Age must be positive, between 0~200
    // Assign message when new ReturnValue instance is not needed, but keep
    // return additional information is good for debug.
    if (is_numeric($age) && 0 <= $age && 200 >= $age) {
        $rv = new ReturnValue(0, 'success');
    } else {
        $rv = new ReturnValue(-1, 'fail');
    }

    echo $rv->toJson();
    exit;
}


/***************************************
 * Prepare FormValidator instance
 **************************************/
$curl = new Curl;
$curl->setSslVerify(false);
$serviceContainer = TestServiceContainer::getInstance();
$serviceContainer->register('Curl', $curl);

$constraintContainer = ConstraintContainer::getInstance();
$urlConstraint = $constraintContainer->getUrl();

$validator = $serviceContainer->getValidator();
$validator->setConstraintContainer($constraintContainer);

$form = new Form();
$form->setMode(ElementMode::EDIT)
    ->setClass('formWithValidator')
    ->setId('demoForm');
$form->getValidator()->setValidator($validator);

(new Text('userTitle'))->setTitle('名称')
    ->setValidateRules(['required'])
    ->setTip('Should not be empty')
    ->setCheckOnKeyup(true)
    ->appendTo($form);
(new Text('userAge'))->setTitle('Age')
    ->setValidateRules(['required', 'url: ?a=checkAge , userAge , '])
    ->setTip('Age should be a valid age')
    ->appendTo($form);
// :TODO: Need drop down list select box
(new Text('hiddenValue'))->setTitle('Hidden Input')
    ->setValidateRules(['required', 'regex: /11/'])
    ->setTip('Must select one, must equals 11')
    ->appendTo($form);
(new Textarea('remark'))->setTitle('Remark')
    ->setValidateRules(['required', 'regex: /g/i'])
    ->setTip('不能为空，必须包含字母 g 或者 G')
    ->setCheckOnKeyup(true)
    ->appendTo($form);


/***************************************
 * Prepare for output, backend validate
 **************************************/
if (!empty($_POST)) {
    $form->validate();
}


?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
    <meta charset='utf-8'/>
    <title>FormValidator Demo</title>

    <link rel='stylesheet' href='<?php echo $pathToRoot; ?>css/reset.css'
          type='text/css' media='all'/>
    <link rel='stylesheet' href='<?php echo $pathToRoot; ?>css/default.css'
          type='text/css' media='all'/>

    <style type='text/css' media='all'>
        /* Write CSS below */

        form {
            margin: auto;
            margin-top: 2em;
            text-align: left;
            width: 33em;
        }

        form label {
            display: inline-block;
            font-weight: bold;
            text-align: right;
            width: 8em;
        }

        form label.right-side-label {
            font-weight: normal;
            text-align: left;
            width: 30em;
        }

        form input, form textarea {
            line-height: 150%;
            margin-bottom: 0.5em;
            margin-top: 0.5em;
        }

        .submit {
            margin-top: 0.5em;
            text-align: center;
        }

        #div-remark label, #div-remark textarea {
            vertical-align: middle;
        }

        #validate-fail-message {
            margin: auto;
            margin-bottom: -2em;
            width: 33em;
        }
    </style>


    <script type="text/javascript"
            src="<?php echo GlobalConfig::getInstance()->get('lib.path.jquery'); ?>">
    </script>

    <script type="text/javascript"
            src="<?php echo $pathToRoot; ?>js/form-validator.js">
    </script>


</head>
<body>

<h2>FormValidator Demo</h2>


<?php echo $form->getOutput(); ?>


<script type="text/javascript">
    <!--

    /* Attach event for frontendCheck option */
    (function (global) {
        var setCheckOnSubmit = function (event) {
            /* Html element maybe faster */
            /*if ($(this).prop('checked')) {*/
            if (event.target.checked) {
                global.formValidator_demoForm.enableCheckOnSubmit();
            } else {
                global.formValidator_demoForm.disableCheckOnSubmit();
            }
        };

        $('#frontendCheck')
            /* Need not click event */
            /*.on('click', setCheckOnSubmit)*/
            .on('change', setCheckOnSubmit)
            .trigger('change');
    })(window);

    -->
</script>


</body>
</html>
