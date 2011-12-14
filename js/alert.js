/**
 * Show msg using js/jQuery, with a float div.
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @since		2011-08-13
 */


/**
 * Show msg using js float div
 *
 * @param	array 	msg
 * @param	string	title
 * @param	string	s_id
 * @param	boolean	b_show_close
 * @param	boolean	b_show_bg
 */
function JsAlert (msg, title, s_id, b_show_close, b_show_bg) {
	if ('undefined' == typeof(s_id) || 1 > s_id.length)
		/* If conflict with other id, modify this. */
		s_id = 'alert';
	/* Store id to global var */
	s_js_alert_id = s_id;

	/* Param default value */
	if ('undefined' == typeof(b_show_close))
		b_show_close = true;
	if ('undefined' == typeof(b_show_bg))
		b_show_bg = true;

	var s_div = '';

	/* Iframe is hack for IE select overwrite div. */
	s_div += '\
		<div id=\'' + s_id + '\'>\
			<iframe style=\'width: 100%; height: 100%;\
				filter: alpha(opacity=0); opacity: 0;\'>\
			</iframe>\
			<fieldset>\
	';

	/* Title */
	if ('undefined' != typeof(title) && 0 < title.length)
		s_div += '\
				<legend alien="center">　' + title + '　</legend>\
		';

	/* Msg */
	s_div += '\
			<ul>\
	';
	if (!('object' == typeof(msg) && (msg instanceof Array)))
		msg = Array(msg);
	$(msg).each(function () {
		s_div += '\
				<li>' + this + '</li>\
		';
	});

	/* Show close link ? */
	if (true == b_show_close)
		s_div += '\
				<li><a id="' + s_id + '_close"\
						href="javascript:void(0);"\
						onclick="return JsAlertRemove();">\
					继续</a>\
				</li>\
		';

	s_div += '\
			</ul>\
			</fieldset>\
		</div>\
	';

	var s_bg = '<div id="' + s_id + '_bg"></div>';


	/* Show them */
	if (true == b_show_bg) {
		$('body').append(s_bg);
		/* Adjust */
		$('#' + s_id + '_bg').height($(document).height() * 1.2);
	}

	$('body').append(s_div);
	/* Position */
	$('#' + s_id).css('top', $(window).scrollTop()
		+ ($(window).height() -	$('#' + s_id).height()) / 3
			+ 'px');

	/* For IE */
	$('#' + s_id + '_close').click(function () {
		return JsAlertRemove();
	});
} /* end of func JsAlert */


/**
 * Remove js alert msg
 *
 * Can only call inside link in msg list, 4 level below div.
 *
 * @return	false
 */
function JsAlertRemove () {
	var s_id = ('undefined' == typeof(s_js_alert_id))
		? 'alert'
		: s_js_alert_id;
	/* Remove bg first */
	$('#' + s_id + '_bg').remove();
	$('#' + s_id).remove();
	return false;
} /* end of func JsAlertRemove */


/* Css example */

/* Js Alert */
/*
div#alert {
	left: 0px;
	position: absolute;
	text-align: center;
	top: 200px;
	width: 99%;
	z-index: 999;
}
div#alert_bg {
	background: #E5E5E5;
	filter: alpha(opacity=60);
	height: 100%;
	left: 0px;
	opacity: 0.6;
	position: absolute;
	top: 0px;
	width: 100%;
	z-index: 998;
}
div#alert fieldset {
	background: #FFF;
	border: 1px solid blue;
	font-weight: bold;
	margin: auto;
	padding-bottom: 2em;
	padding-top: 2em;
	width: 40%;
}
div#alert legend {
	color: blue;
	font-weight: bold;
	margin-left: 2em;
	margin-right: 2em;
}
*/
