/**
 * Show db diff msg using js/jQuery, with a float div.
 *
 * See /css/dbdiff.css and /loader/dbdiff.css.php
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since		2012-12-26
 */


/**
 * Show dbdiff detail in float div
 *
 * o_cfg = Object(
 * 	dbdiff, string DbDiff msg, json format
 * 	id, id/class of main div, also is prefix of other inner element
 * 	lang, array, customize language
 * 	print_css_text,
 * 	print_css_url,
 * 	show_bg, boolean
 * 	show_action_top, boolean
 * 	show_action_bottom, boolean
 * 	show_print, boolean
 * 	text_after, html after diff detail table
 * 	text_before, html before diff detail table
 * )
 *
 * @param	object	o_cfg
 * @return	string	s_id
 */
function DbDiffShow (o_cfg) {
	// Parse param
	var o_dbdiff = $.parseJSON(o_cfg.dbdiff);
	if ('undefined' == typeof(o_cfg.id))
		o_cfg.id = 'db_diff';
	if ('undefined' == typeof(o_cfg.print_css_text))
		o_cfg.print_css_text = '';
	if ('undefined' == typeof(o_cfg.print_css_url))
		o_cfg.print_css_url = [];
	if ('undefined' == typeof(o_cfg.show_action_top))
		o_cfg.show_action_top = true;
	if ('undefined' == typeof(o_cfg.show_action_bottom))
		o_cfg.show_action_bottom = true;
	if ('undefined' == typeof(o_cfg.show_bg))
		o_cfg.show_bg = true;
	if ('undefined' == typeof(o_cfg.show_print))
		o_cfg.show_print = true;
	if ('undefined' == typeof(o_cfg.text_after))
		o_cfg.text_after = '';
	if ('undefined' == typeof(o_cfg.text_before))
		o_cfg.text_before = '';
	if ('undefined' == typeof(o_cfg.lang))
		o_cfg.lang = {
			'close'		: 'Close',
			'print'		: 'Print',
			'code'		: 'Code',
			'message'	: 'Message',
			'flag'		: 'Flag',
			'detail'	: 'Diff Detail',
			'table'		: 'Table',
			'mode'		: 'Mode',
			'pk'		: 'PK',
			'column'	: 'Column',
			'old'		: 'Old',
			'new'		: 'New'
		};

	// Simulate Array.length
	//alert(Object.keys(ar_cfg).length);
	// Alert object as string
	//alert(JSON.stringify(o_cfg, null, 4));

	var s_div = '';
	var s_id = o_cfg.id + '_' + Math.floor(Math.random() * 1000);

	/* Iframe is hack for IE select overwrite div. */
	s_div += '\
		<div id=\'' + s_id + '\' class=\'' + o_cfg.id + '\'>\
			<iframe style=\'width: 100%; height: 100%;\
				filter: alpha(opacity=0); opacity: 0;\'\
				class=\'' + o_cfg.id + '_iframe\'>\
			</iframe>\
			\
			<div class=\'' + o_cfg.id + '_content\'>\
	';

	/* Action link */
	var s_div_action = '\
			<div class=\'' + o_cfg.id + '_action print_hide\'>\
	';
	if (o_cfg.show_print)
		s_div_action += '\
				<a class="' + o_cfg.id + '_print"\
					href="javascript:void(0);">\
					' + o_cfg.lang.print + '</a>　　\
		';
	s_div_action += '\
				<a class="' + o_cfg.id + '_close"\
					href="javascript:void(0);"\
					onclick="return DbDiffRemove(\'' + s_id + '\');">\
				' + o_cfg.lang.close + '</a>\
			</div>\
	';
	/* Show action link, top */
	if (o_cfg.show_action_top)
		s_div += s_div_action;

	/* Text before */
	if (0 < o_cfg.text_before.length)
		s_div += o_cfg.text_before;

	/* Msg detail */
	s_div += '\
			<table>\
				<tr>\
					<th>' + o_cfg.lang.code + '</th>\
					<td colspan="2" class=\'' + o_cfg.id + '_code\'>'
						+ o_dbdiff.code + '</td>\
				</tr>\
				<tr>\
					<th>' + o_cfg.lang.message + '</th>\
					<td colspan="2" class=\'' + o_cfg.id + '_msg\'>'
						+ o_dbdiff.msg + '</td>\
				</tr>\
				<tr>\
					<th>' + o_cfg.lang.flag + '</th>\
					<td colspan="2" class=\'' + o_cfg.id + '_flag\'>'
						+ o_dbdiff.flag + '</td>\
				</tr>\
				\
				<tr>\
					<th colspan="3">' + o_cfg.lang.detail + '</th>\
				</tr>\
	';
	for (var tbl in o_dbdiff.diff) {
		for (var row in o_dbdiff.diff[tbl]) {
			/* Prepare PK for display */
			var o_pk = new Object;
			if ('DELETE' == o_dbdiff.diff[tbl][row].mode) {
				for (var pk in o_dbdiff.diff[tbl][row].pk)
					o_pk[pk] = o_dbdiff.diff[tbl][row].pk[pk].old;
			}
			else {
				for (var pk in o_dbdiff.diff[tbl][row].pk)
					o_pk[pk] = o_dbdiff.diff[tbl][row].pk[pk]['new'];
			}
			var s_pk = '';
			for (pk in o_pk)
				s_pk += pk + ' = ' + o_pk[pk] + ', ';
			s_pk = s_pk.substr(0, s_pk.length - 2);

			s_div += '\
				<tr>\
					<td colspan="3">\
						<strong>' + o_cfg.lang.table + '</strong>: '
				+ tbl + ', \
						<strong>' + o_cfg.lang.mode + '</strong>: '
				+ o_dbdiff.diff[tbl][row].mode + '<br />\
						<strong>' + o_cfg.lang.pk + '</strong>: '
				+ s_pk + '\
					</td>\
				</tr>\
				<tr>\
					<th>' + o_cfg.lang.column + '</th>\
					<th>' + o_cfg.lang.old + '</th>\
					<th>' + o_cfg.lang['new'] + '</th>\
				</tr>\
			';

			for (var col in o_dbdiff.diff[tbl][row].col) {
				s_div += '\
				<tr>\
					<td>' + col + '</td>\
					<td>' + o_dbdiff.diff[tbl][row].col[col].old + '</td>\
					<td>' + o_dbdiff.diff[tbl][row].col[col]['new'] + '</td>\
				</tr>\
				';
			}
		}
	}

	s_div += '\
				</tr>\
			</table>\
	';

	/* Text after */
	if (0 < o_cfg.text_after.length)
		s_div += o_cfg.text_after;

	/* Show action link, bottom */
	if (o_cfg.show_action_bottom)
		s_div += s_div_action;

	s_div += '\
			</div>\
		</div>\
	';

	/* Show bg */
	if (o_cfg.show_bg) {
		$('body').append('<div id="' + s_id + '_bg" class=\''
			+ o_cfg.id + '_bg\'></div>');
		/* Adjust */
		$('#' + s_id + '_bg').height($(document).height() * 1.2);
	}

	/* Show main div */
	$('body').append(s_div);
	/* Position */
	$('#' + s_id).css('top', $(window).scrollTop()
		+ ($(window).height() -	$('#' + s_id).height()) / 3
			+ 'px');

	/* For IE */
	$('.' + o_cfg.id + '_close').click(function () {
		return DbDiffRemove(s_id);
	});

	/* Press ESC to close */
	$(window).keydown(function (evt) {
		if (27 == evt.keyCode) {
			DbDiffRemove(s_id);
		}
	});

	/* Print action */
	$('.' + o_cfg.id + '_print').click(function () {
		/* Remove iframe(IE hack) and recover after print */
		var o_iframe = $('.' + o_cfg.id + '_iframe', '#' + s_id).clone();
		$('.' + o_cfg.id + '_iframe', '#' + s_id).remove();

		PrintArea({
			id: s_id,
			css_text: o_cfg.print_css_text,
			css_url: o_cfg.print_css_url,
			id_frame: o_cfg.id + '_print_frame'
		});

		$('#' + s_id).prepend(o_iframe);
	});

	return s_id;
} /* end of func DbDiffShow */


/**
 * Remove dbdiff msg
 *
 * @param	string	s_id
 * @param	string	s_class
 * @return	false
 */
function DbDiffRemove (s_id, s_class) {
	/* Remove bg first */
	if ('undefined' == typeof(s_class)) {
		/* Remove by id */
		$('#' + s_id + '_bg').remove();
		$('#' + s_id).remove();
	}
	else {
		/* Remove by class */
		$('.' + s_class + '_bg').remove();
		$('.' + s_class).remove();
	}

	return false;
} /* end of func DbDiffRemove */
