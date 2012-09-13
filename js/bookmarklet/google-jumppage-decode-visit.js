/**
 * Decode google un-visit-able jump url and visit dest directly.
 *
 * Convert to bookmarklet using:
 * http://chris.zarate.org/bookmarkleter
 *
 * @package		fwolflib
 * @subpackage	js.bookmarklet
 * @copyright	Copyright © 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js.bookmarklet@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL
 * @since		2012-02-09
 */


javascript:(function () {
	 function Utf8To16 (str) {
		 var out, i, len, c;
		 var char2, char3;
		 out = '';
		 len = str.length;
		 i = 0;
		 while (i < len) {
			 c = str.charCodeAt(i++);
			 switch (c >> 4) {
				 case 0:
				 case 1:
				 case 2:
				 case 3:
				 case 4:
				 case 5:
				 case 6:
				 case 7:
					out += str.charAt(i - 1);
					break;
				case 12:
				case 13:
					char2 = str.charCodeAt(i++);
					out += String.fromCharCode(((c & 0x1F) << 6)
						| (char2 & 0x3F));
					break;
				case 14:
					char2 = str.charCodeAt(i++);
					char3 = str.charCodeAt(i++);
					out += String.fromCharCode(((c & 0x0F) << 12)
						| ((char2 & 0x3F) << 6)
						| ((char3 & 0x3F) << 0));
					break;
			}
		}

		return out;
	} /* end of func Utf8To16 */


	/*
	 * urldecode function by Demon:
	 * http://demon.tw/programming/javascript-php-urldecode.html
	 */
	 function UrlDecode (encodedString) {
		 var output = encodedString;
		 var binVal, thisString;
		 var myregexp = /(%[^%]{2})/;

		while ((match = myregexp.exec(output)) != null
			&& match.length > 1 && match[1] != '') {
			binVal = parseInt(match[1].substr(1), 16);
			thisString = String.fromCharCode(binVal);
			output = output.replace(match[1], thisString);
		}

		/*output=Utf8To16(output);*/
		output = output.replace(/\\+/g, ' ');
		output = Utf8To16(output);
		return output;
	} /* end of func UrlDecode */


	/* Bookmarklet call */

	/*
	 * Find page url, on error page,
	 * you can only search it in page content,
	 * this various for different browser.
	 */
	var links = document.getElementsByTagName('a');
	var s = '';
	var i = -1;
	var j = 0;
	while ((-1 == i) && (3 > j)) {
		s = links[j++].getAttribute('href');
		i = s.search('url=http');
	}
/*
	var links = document.getElementsByTagName('a');
	var s = links[0].getAttribute('href');
	var s = window.location.href;
	var i = s.search('url=http');
*/
	if (-1 != i) {
		s = s.substr(s.search('url=http') + 4);
		s = UrlDecode(s);

		/* Remove useless tail by google */
		j = s.search('&ei=');
		if (-1 != j)
			s = s.substr(0, j);
		j = s.search('&usg=');
		if (-1 != j)
			s = s.substr(0, j);

		window.location.href = s;
	}
}
) ();
