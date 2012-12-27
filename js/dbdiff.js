/**
 * Show db diff msg using js/jQuery, with a float div.
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL V3
 * @since		2012-12-26
 */


/**
 * Show dbdiff detail in float div
 *
 * o_cfg = Object(
 * 	dbdiff, string DbDiff msg, json format
 * 	id, id/class of main div, also is prefix of other inner element
 * 	show_bg, boolean
 * 	show_close_top, boolean
 * 	show_close_bottom, boolean
 * )
 *
 * @param	object	o_cfg
 */
function DbDiffShow (o_cfg) {
	// Parse param
	var o_dbdiff = $.parseJSON(o_cfg.dbdiff);
	if ('undefined' == typeof(o_cfg.id))
		o_cfg.id = 'db_diff';
	if ('undefined' == typeof(o_cfg.show_close_top))
		o_cfg.show_close_top = true;
	if ('undefined' == typeof(o_cfg.show_close_bottom))
		o_cfg.show_close_bottom = true;
	if ('undefined' == typeof(o_cfg.show_bg))
		o_cfg.show_bg = true;
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
				filter: alpha(opacity=0); opacity: 0;\'>\
			</iframe>\
			\
			<div class=\'' + o_cfg.id + '_content\'>\
	';

	/* Close link */
	var s_div_close = '\
			<div class=\'' + o_cfg.id + '_close\'>\
				<a id="' + s_id + '_print"\
					href="javascript:void(0);">\
					' + o_cfg.lang.print + '</a>　　\
				<a id="' + s_id + '_close"\
					href="javascript:void(0);"\
					onclick="return DbDiffRemove(\'' + s_id + '\');">\
				' + o_cfg.lang.close + '</a>\
			</div>\
	';
	/* Show close link, top */
	if (o_cfg.show_close_top)
		s_div += s_div_close;

	/* Msg detail */
	s_div += '\
			<table>\
				<tr>\
					<th>' + o_cfg.lang.code + '</th>\
					<td colspan="2">' + o_dbdiff.code + '</td>\
				<tr/>\
				<tr>\
					<th>' + o_cfg.lang.message + '</th>\
					<td colspan="2">' + o_dbdiff.msg + '</td>\
				<tr/>\
				<tr>\
					<th>' + o_cfg.lang.flag + '</th>\
					<td colspan="2">' + o_dbdiff.flag + '</td>\
				<tr/>\
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
					o_pk[pk] = o_dbdiff.diff[tbl][row].pk[pk].new;
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
					<th>' + o_cfg.lang.new + '</th>\
				</tr>\
			';

			for (var col in o_dbdiff.diff[tbl][row].col) {
				s_div += '\
				<tr>\
					<td>' + col + '</td>\
					<td>' + o_dbdiff.diff[tbl][row].col[col].old + '</td>\
					<td>' + o_dbdiff.diff[tbl][row].col[col].new + '</td>\
				</tr>\
				';
			}
		}
	}

	s_div += '\
				</tr>\
			</table>\
	';

	/* Show close link, bottom */
	if (o_cfg.show_close_bottom)
		s_div += s_div_close;

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
	$('#' + s_id + '_close').click(function () {
		return DbDiffRemove(s_id);
	});

	/* Press ESC to close */
	$(window).keydown(function (evt) {
		if (27 == evt.keyCode) {
			DbDiffRemove(s_id);
		}
	});

	/* Print action */
	$('#' + s_id + '_print').click(function () {
		PrintArea({id: s_id});
	});
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


/* Css example */

/* DbDiff */
/*
div.db_diff {
	left: 0px;
	position: absolute;
	text-align: center;
	top: 200px;
	width: 99%;
	z-index: 999;
}
div.db_diff_bg {
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
div.db_diff_content {
	background: #FFF;
	margin: auto;
	padding-bottom: 0.5em;
	padding-top: 0.5em;
	text-align: center;
	width: 60%;
}
div.db_diff table {
	margin: auto;
	width: 96%;
}
div.db_diff table, div.db_diff td, div.db_diff th {
	border: 1px solid gray;
	border-collapse: collapse;
	vertical-align: middle;
}
div.db_diff th {
	background-color: rgb(208, 220, 255);
}
div.db_diff .db_diff_close {
	margin: auto;
	text-align: right;
	width: 95%;
}
div.db_diff strong {
	font-weight: bold;
}
*/
