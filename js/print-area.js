/**
 * Print area in html, exclude other part.
 *
 * @copyright	Copyright 2012-2016, Fwolf
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL V3
 */


/**
 * Print preferred area
 *
 * o_cfg = Object(
 * 	id,	id of area to print
 * 	css_text, override css_url
 * 	css_url, string or array of css url
 * 	id_frame, id of print frame
 * )
 *
 * @param	{object} o_cfg
 */
function PrintArea (o_cfg) {
	/* Parse param */
	if ('undefined' == typeof(o_cfg.css_text))
		o_cfg.css_text = '';
	if ('undefined' == typeof(o_cfg.css_url))
		o_cfg.css_url = [];
	if ('undefined' == typeof(o_cfg.id_frame))
		o_cfg.id_frame = 'frame_print';

	/* Create print frame if not exists */
	if ('undefined' === typeof($('#' + o_cfg.id_frame).attr('id'))) {
		$('body').append('<iframe id=\'' + o_cfg.id_frame + '\' \
			name=\'' + o_cfg.id_frame + '\' \
			width=\'0\' height=\'0\' frameborder=\'0\' \
			src=\'about:blank\' ></iframe>');
	}

	/* Prepare css */
	var s_css = '';
	if (0 < o_cfg.css_url.length) {
		if ('string' == typeof(o_cfg.css_url))
			o_cfg.css_url = [o_cfg.css_url];
		for (var i in o_cfg.css_url) {
			s_css += '\
				<link rel=\'stylesheet\' type=\'text/css\'\
					media=\'print\'\
					href=\'' + o_cfg.css_url[i] + '\'\
				/>\
			';
		}
	}
	if (0 < o_cfg.css_text.length) {
		s_css += '\
			<style type=\'text/css\' media=\'print\'>\
			' + o_cfg.css_text + '\
			</style>\
		';
	}

	/* Write content */
	/* Use document.open/write fix FF need print twice problem. */
	var o_f = document.frames ? document.frames[o_cfg.id_frame]
		: document.getElementById(o_cfg.id_frame);
	var o_fw = o_f.contentWindow || o_f;
	o_fw.document.open('text/html', 'replace');
	o_fw.document.write(s_css + $('#' + o_cfg.id).html());
	o_fw.document.close();

	/* Print */
	o_fw.focus();
	o_fw.print();
}


/* Css example */

/* Print Area */
/*
*/
