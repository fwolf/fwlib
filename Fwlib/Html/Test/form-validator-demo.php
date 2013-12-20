<?php
require __DIR__ . '/../../../config.default.php';

use Fwlib\Base\ReturnValue;
use Fwlib\Config\GlobalConfig;
use Fwlib\Html\FormValidator;
use Fwlib\Net\Curl;
use Fwlib\Util\UtilContainer;
use Fwlib\Validator\Validator;

/***************************************
 * Read post data
 **************************************/
$utilContainer = UtilContainer::getInstance();
$httpUtil = $utilContainer->get('HttpUtil');

$userTitle = $httpUtil->getPost('userTitle');
$userAge = $httpUtil->getPost('userAge');
$remark = $httpUtil->getPost('remark');
if (1 == $httpUtil->getPost('frontendCheck')) {
    $frontendCheck = 'checked="checked"';
} else {
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

    echo $rv->getJson();
    exit;
}


/***************************************
 * Prepare FormValidator instance
 **************************************/
$formValidator = new FormValidator(
    array(
        'withClosure'   => false,
    )
);

$validator = new Validator;
$urlConstraint = $validator->getConstraint('url');
$curl = new Curl;
$curl->setoptSslverify(false);
$urlConstraint->setInstance($curl);
$formValidator->setInstance($validator);

$rule = array(
    'userTitle' => array(
        'title' => '名称',
        'check' => 'required',
        'tip'   => 'should not be empty',
        'checkOnKeyup'  => true,
    ),
    'userAge' => array(
        'check' => array(
            'required',
            'url: ?a=checkAge , userAge , ',
        ),
        'tip'   => 'Age should be a valid age',
    ),
    'remark' => array(
        'check' => array(
            'required',
            'regex: /g/i',
        ),
        'tip'   => '不能为空，必须包含字母 g 或者 G',
        'checkOnKeyup'  => true,
    ),
);

$formValidator->setRuleArray($rule);


/***************************************
 * Prepare for output, backend validate
 **************************************/
$validateJs = $formValidator->getJs();

// Backend validate
$validateMessage = '';
if (!empty($_POST)) {
    $postData = array(
        'userTitle' => $userTitle,
        'userAge'   => $userAge,
        'remark'    => $remark,
    );

    if (!$formValidator->validate($postData)) {
        $validateMessage = '
<ul id="validate-fail-message">';

        foreach ($formValidator->getMessage() as $name => $message) {
            if (isset($rule[$name]['title'])) {
                $message = $rule[$name]['title'] . ': ' . $message;
            }

            $validateMessage .= "
  <li>$message</li>";
        }

        $validateMessage .= '
</ul>';
    }
}


?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
  <meta charset='utf-8' />
  <title>FormValidator Demo</title>

  <link rel='stylesheet' href='../../../css/reset.css'
    type='text/css' media='all' />
  <link rel='stylesheet' href='../../../css/default.css'
    type='text/css' media='all' />

  <style type='text/css' media='all'>
  /*<![CDATA[*/
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

  /*]]>*/
  </style>


  <script type="text/javascript"
    src="<?php echo GlobalConfig::getInstance()->get('lib.path.jquery'); ?>">
  </script>

  <script type="text/javascript"
    src="../../../js/form-validator.js">
  </script>


</head>
<body>

  <h2>FormValidator Demo</h2>


<?php echo $validateMessage; ?>


  <form method='post'>

    <label for='userTitle'>User Title:</label>
    <input type='text' name='userTitle' id='userTitle'
      value='<?php echo $userTitle; ?>' />

    <br />

    <label for='userAge'>User Age:</label>
    <input type='text' name='userAge' id='userAge'
      value='<?php echo $userAge; ?>' />

    <br />

    <div id='div-remark'>
      <label for='remark'>Remark:</label>
      <textarea rows='3' cols='30' name='remark' id='remark'
        ><?php echo $remark; ?></textarea>
    </div>

    <input type='checkbox' name='frontendCheck' id='frontendCheck'
    value='1' <?php echo $frontendCheck; ?> />
    <label for='frontendCheck' class='right-side-label'>
      Enable frontend validate, uncheck to see backend validate.
    </label>

    <div class='submit'>
      <input type='submit' value='Submit' />
    </div>

  </form>


<?php echo $validateJs; ?>


  <script type="text/javascript">
  <!--//--><![CDATA[//>
  <!--

  /* Attach event for frontendCheck option */
  (function () {
    var setCheckOnSubmit = function(event)
    {
      /* Html element maybe faster */
      /*if ($(this).prop('checked')) {*/
      if (event.target.checked) {
        formValidator.enableCheckOnSubmit();
      } else {
        formValidator.disableCheckOnSubmit();
      }
    };

    $('#frontendCheck')
      /* Need not click event */
      /*.on('click', setCheckOnSubmit)*/
      .on('change', setCheckOnSubmit)
      .trigger('change');
  }) ();

  //--><!]]>
  </script>


</body>
</html>
