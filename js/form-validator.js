/* global $: false, jQuery: false, alert: false */
/* jshint maxlen: 130 */

/**
 * Form validator
 *
 * @link http://www.ruanyifeng.com/blog/2012/07/three_ways_to_define_a_javascript_class.html
 * @link http://www.gabordemooij.com/articles/jsoop.html
 *
 * @package     fwlib/js
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-10
 * @version     1.1.1
 */
var FormValidator =
{

  classFailed: 'validate-failed',
  classLoading: 'validate-loading',
  classRequired: 'validate-required',
  idTip: 'validate-tip',


  /**
   * Create new FormValidator instance
   *
   * @returns {Object}  FormValidator
   */
  createNew: function()
  {
    'use strict';

    /* Instance */
    var formValidator = {};


    /* Public property */
    formValidator.checkOnSubmit = true;
    formValidator.formSelector = '';
    formValidator.$form = null;
    /* Message is indexed by name of input element validte fail */
    formValidator.message = {};
    formValidator.rules = null;


    /**
     * Treat ajax post error
     *
     * @param {jqXHR} jqXHR
     * @param {string}  textStatus
     * @param {string}  errorThrown
     */
    formValidator.ajaxError = function(jqXHR, textStatus, errorThrown)
    {
      var message = 'Ajax request error with code ' + jqXHR.status +
        ': ' + jqXHR.responseText;

      formValidator.showMessage(message);
    };


    /**
     * Bind need event to input element
     *
     * Event is to show tip, do validate etc.
     */
    formValidator.bind = function()
    {
      var validateMethod = null;
      var rule = null;
      var $input = null;
      var $visualInput = null;

      for (var name in formValidator.rules) {
        rule = formValidator.rules[name];
        $input = formValidator.getInput(name);
        $visualInput = formValidator.getInputOrPuppet(name);

        /* Try to get title if not assigned */
        if (!rule.title || 0 === rule.title.length) {
          rule.title = formValidator.getInputTitle($input);
        }

        /* Show tip by hover event */
        $visualInput
          .on('mouseenter', formValidator.onMouseEnter)
          .on('mouseleave', formValidator.onMouseLeave);

        /* Mark requried */
        if (formValidator.isRequired(rule.check)) {
          formValidator.markRequired($visualInput);
        }

        /* Bind validate method on input */
        validateMethod = formValidator.generateValidateInput($input, rule);
        if (rule.checkOnBlur) {
          $visualInput.on('blur', validateMethod);
        }
        if (rule.checkOnKeyup) {
          $visualInput.on('keyup', validateMethod);
        }
      }

      /* Bind validate method on form */
      validateMethod = formValidator.generateValidateForm();
      formValidator.$form.on('submit', validateMethod);
    };


    /**
     * Option checkOnSubmit setter
     *
     * @returns {Object}  FormValidator
     */
    formValidator.disableCheckOnSubmit = function()
    {
      formValidator.checkOnSubmit = false;

      return formValidator;
    };


    /**
     * Option checkOnSubmit setter
     *
     * @returns {Object}  FormValidator
     */
    formValidator.enableCheckOnSubmit = function()
    {
      formValidator.checkOnSubmit = true;

      return formValidator;
    };


    /**
     * Generate tip element, forkable
     *
     * @param {string}  id
     * @param {string}  tipMessage
     * @returns {jQuery}
     */
    formValidator.generateTip = function(id, tipMessage)
    {
      var $validateTip = $('<div></div>');

      $validateTip
        .attr('id', id)
        .text(tipMessage);

      return $validateTip;
    };


    /**
     * Generate validate method on form
     *
     * @returns {callback}
     */
    formValidator.generateValidateForm = function()
    {
      var method = function(event)
      {
        if (!formValidator.checkOnSubmit) {
          return true;

        } else {
          var isValid = true;
          var rule = null;
          var $input = null;
          var $visualInput = null;

          for (var name in formValidator.rules) {
            rule = formValidator.rules[name];
            $input = formValidator.getInput(name);
            $visualInput = formValidator.getInputOrPuppet(name);

            /* Like check on single input, a little different on isValid */
            if (!formValidator.validate($input, rule)) {
              var tip = formValidator.getTip(name);
              formValidator.message[name] = tip;
              formValidator.markFailed($visualInput, tip);
              isValid = false;
            } else {
              delete formValidator.message[name];
              formValidator.unmarkFailed($visualInput);
            }
          }

          if (!isValid) {
            formValidator.showMessage(formValidator.getMessage());
          }

          return isValid;
        }
      };

      return method;
    };


    /**
     * Generate validate method on input
     *
     * @param {jQuery}  $input
     * @param {Object}  rule
     * @returns {callback}
     */
    formValidator.generateValidateInput = function($input, rule)
    {
      var method = function(event)
      {
        var name = $input.attr('name');
        var $visualInput = formValidator.getInputOrPuppet(name);

        if (!formValidator.validate($input, rule)) {
          var tip = formValidator.getTip(name);
          formValidator.message[name] = tip;
          formValidator.markFailed($visualInput, tip);
        } else {
          delete formValidator.message[name];
          formValidator.unmarkFailed($visualInput);
        }
      };

      return method;
    };


    /**
     * Get form data array
     *
     * @param {string|Array}  nameArray
     * @returns {Object}  Assoc array {inputName: value}
     */
    formValidator.getFormDataArray = function(nameArray)
    {
      var fetchAll = ('*' == nameArray);

      var dataArray = {};
      var name = '';

      $('input, textarea', formValidator.$form).each(function (index, element) {
        name = $(element).attr('name');

        /* Skip element has no name attribute */
        if ('undefined' == typeof(name)) {
          return;
        }

        if (fetchAll || -1 !== nameArray.indexOf(name)) {
          dataArray[name] = $(element).val();
        }
      });

      return dataArray;
    };


    /**
     * Get jQuery object of form input
     *
     * @param {string}  name
     * @returns {jQuery}
     */
    formValidator.getInput = function(name)
    {
      /* Search in every input or html tag */
      var $input = $('[name="' + name + '"]', formValidator.$form);

      /* Try name as id if upper find failed */
      if (0 === $input.length) {
        $input = $('#' + name, formValidator.$form);
      }

      return $input;
    };


    /**
     * Get jQuery object of form input, return puppet if set
     *
     * @param {string}  name
     * @returns {jQuery}
     */
    formValidator.getInputOrPuppet = function(name)
    {
      /* Use puppet if set */
      if ('undefined' != typeof(formValidator.rules[name]['puppet'])) {
        var visualName = formValidator.rules[name]['puppet'];
      } else {
        var visualName = name;
      }

      return formValidator.getInput(visualName);
    };


    /**
     * Try to get title of input element, forkable
     *
     * @param {jQuery}  $input
     * @returns {string}
     */
    formValidator.getInputTitle = function($input)
    {
      var title = $.trim($('label[for="' + $input.attr('name') + '"]').text())
        .replace(/(:|：)/g, '');

      return title;
    };


    /**
     * Get all fail message
     *
     * @returns {Object}  Array indexed by name of input validate failed.
     */
    formValidator.getMessage = function()
    {
      return formValidator.message;
    };


    /**
     * Get tip
     *
     * @param {string}  name  Maybe name of puppet
     * @return  {string}
     */
    formValidator.getTip = function(name)
    {
      if ('undefined' != typeof(formValidator.rules[name])) {
        return formValidator.rules[name]['tip'] || '';

      } else {
        // Is puppet
        for (var hiddenName in formValidator.rules) {
          if ('undefined' != formValidator.rules[hiddenName]['puppet'] &&
            name == formValidator.rules[hiddenName]['puppet']) {
            return formValidator.rules[hiddenName]['tip'] || '';
          }
        }
      }
    };


    /**
     * Hide loading when do Ajax action, forkable
     *
     * @param {jQuery}  $input
     */
    formValidator.hideAjaxLoading = function($input)
    {
      $input.next('.' + FormValidator.classLoading).remove();
    };


    /**
     * Hide validate tip, forkable
     *
     * @param {jQuery}  $input
     * @param {jQuery}  $tip
     */
    formValidator.hideTip = function($input, $tip)
    {
      $tip.remove();
    };


    /**
     * If a rule check include required constraint
     *
     * @param {string|Array}  check
     * @returns {bool}
     */
    formValidator.isRequired = function(check)
    {
      var checkAr = [].concat(check);
      var isRequired = false;

      for (var i = 0; i < checkAr.length; i++) {
        if ('required' == checkAr[i].substr(0, 8)) {
          isRequired = true;
          break;
        }
      }

      return isRequired;
    };


    /**
     * Mark input element as validate failed, forkable
     *
     * @param {jQuery}  $input
     * @param {string}  tip
     */
    formValidator.markFailed = function($input, tip)
    {
      $input.addClass(FormValidator.classFailed);
    };


    /**
     * Mark input element as required, forkable
     *
     * @param {jQuery}  $input
     */
    formValidator.markRequired = function($input)
    {
      $input.after('<span class="' + FormValidator.classRequired + '">*</span>');
    };


    /**
     * Render method when mouse enter input element
     *
     * In common, this method deal with validate tip.
     *
     * @param Event
     */
    formValidator.onMouseEnter = function(event)
    {
      var $input = $(event.target);
      var name = $input.attr('name');
      var tip = formValidator.getTip(name);

      var $validateTip = formValidator.generateTip(
        FormValidator.idTip,
        tip
      );

      formValidator.showTip($input, $validateTip);
    };


    /**
     * Render method when mouse leave input element
     *
     * In common, this method deal with validate tip.
     *
     * @param Event
     */
    formValidator.onMouseLeave = function(event)
    {
      var $input = $(event.target);
      var $validateTip = $('#' + FormValidator.idTip);

      formValidator.hideTip($input, $validateTip);
    };


    /**
     * Set validate target form by jQuery selector
     *
     * @param {string}  formSelectorParam
     * @returns {Object}  FormValidator
     */
    formValidator.setForm = function(formSelectorParam)
    {
      formValidator.formSelector = formSelectorParam || {};

      formValidator.$form = $(formValidator.formSelector);

      return formValidator;
    };


    /**
     * Set validate rules
     *
     * @param {Object}  rulesParam
     * @returns {Object}  FormValidator
     */
    formValidator.setRules = function(rulesParam)
    {
      formValidator.rules = rulesParam || {};

      return formValidator;
    };


    /**
     * Show loading when do Ajax action, forkable
     *
     * @param {jQuery}  $input
     */
    formValidator.showAjaxLoading = function($input)
    {
      var $loading = $('<span class="' + FormValidator.classLoading +
        '">Checking</span>');

      $input.after($loading);
    };


    /**
     * Show message, validate fail or other error, forkable
     *
     * @param {string|Array}  message
     */
    formValidator.showMessage = function(message)
    {
      var formattedMessage = '';

      if ('string' == typeof(message)) {
        formattedMessage = message;

      } else {
        $.each(message, function (index, value) {
          formattedMessage += formValidator.rules[index].title + ': ' +
            value + '\n';
        });
      }

      alert(formattedMessage);
    };


    /**
     * Show validate tip element, forkable
     *
     * @param {jQuery}  $input
     * @param {jQuery}  $tip
     */
    formValidator.showTip = function($input, $tip)
    {
      /* Input may have containing blolock, which imfluenct its position, so
       * we use <body> as container of tip. */
      $('body').append($tip);

      /* Adjust tip position */
      $input.on('mousemove', function(event) {
        $tip
          .css('top', (event.pageY - 60) + 'px')
          .css('left', (event.pageX - 20) + 'px');
      });
    };


    /**
     * Unmark input element as validate failed, forkable
     *
     * @param {jQuery}  $input
     */
    formValidator.unmarkFailed = function($input)
    {
      $input.removeClass(FormValidator.classFailed);
    };


    /**
     * Validate input value by rule(array)
     *
     * @param {jQuery}  $input
     * @param {string|Array}  rule
     * @returns {bool}
     */
    formValidator.validate = function($input, rule)
    {
      /* rule.check can be array, so convert to array and loop with it */
      var checkAr = [].concat(rule.check);

      var check = null;
      var isValid = true;
      var j = 0;
      var ruleData = null;
      var validateMethod = null;
      var validateType = '';

      for (var i = 0; i < checkAr.length; i++) {
        /* Single check, is string */
        check = checkAr[i];
        j = check.indexOf(':');

        if (-1 == j) {
          validateType = check.toLowerCase();
        } else {
          validateType = check.substr(0, j).toLowerCase();
          ruleData = check.substr(j + 1);
        }

        if (
          'undefined' !== typeof(formValidator.validateMethod[validateType])
        ) {
          validateMethod = formValidator.validateMethod[validateType];

          if (!validateMethod($input, ruleData)) {
            isValid = false;
            /* If one of check fail, other check will skip */
            break;
          }

        } else {
          /* Un-registered validate method */
          isValid = false;
          break;
        }
      }

      return isValid;
    };


    /**
     * Validate by constraint Required
     *
     * @param {jQuery}  $input
     * @param {string}  ruleData
     * @returns {bool}
     */
    formValidator.validateRequired = function($input, ruleData)
    {
      return $.trim($input.val()).length > 0;
    };


    /**
     * Validate by constraint Regex
     *
     * @param {jQuery}  $input
     * @param {string}  ruleData
     * @returns {bool}
     */
    formValidator.validateRegex = function($input, ruleData)
    {
      ruleData = $.trim(ruleData);

      var regex = null;

      if ('/' == ruleData.charAt(0)) {
        /* Regex string MUST have ending '/', may have modifier after */
        var i = ruleData.lastIndexOf('/');
        regex = new RegExp(ruleData.slice(1, i), ruleData.slice(i + 1));

      } else {
        /* Simple regex string without '/' and modifier */
        regex = new RegExp(ruleData);
      }

      return regex.test($input.val());
    };


    /**
     * Validate by constraint Url
     *
     * Url check use ajax and post a assoc array, the $input is ignored here,
     * and use formValidator.$form and ruleData to build post array.
     *
     * @param {jQuery}  $input
     * @param {string}  ruleData
     * @returns {bool}
     */
    formValidator.validateUrl = function($input, ruleData)
    {
      /* Find url part */
      var ruleDataArray = ruleData.split(',');
      var url = $.trim(ruleDataArray.shift());

      /* Cleanup rule data */
      ruleDataArray = $.map(ruleDataArray, function (value, index) {
        value = $.trim(value);
        if (0 < value.length) {
          return value;
        }
      });

      /* Build post data array */
      var postData = {};
      if (0 === ruleDataArray.length) {
        postData = formValidator.getFormDataArray('*');
      } else {
        postData = formValidator.getFormDataArray(ruleDataArray);
      }

      var isValid = true;
      formValidator.showAjaxLoading($input);
      $.ajax({
        async: false,
        url: url,
        data: $.param(postData),
        dataType: 'json',
        type: 'POST',
        success: function(returnValue) {
          formValidator.hideAjaxLoading($input);
          isValid = (0 <= returnValue.code);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          formValidator.hideAjaxLoading($input);
          formValidator.ajaxError(jqXHR, textStatus, errorThrown);
        }
      });

      return isValid;
    };


    /* Register validate method, forkable */
    formValidator.validateMethod = {
      'regex'    : formValidator.validateRegex,
      'required' : formValidator.validateRequired,
      'url'      : formValidator.validateUrl
    };


    return formValidator;
  }
};
