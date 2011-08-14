<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2004-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/regex_match.php');
require_once(FWOLFLIB . 'func/string.php');


/**
 * Parse a mail format message
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2004-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2007-08-05
 * @version		$Id$
 */
class MailParser extends Fwolflib{

	/**
	 * Error message
	 * @var string
	 */
	public $mErrorMsg = '';

	/**
	 * Error number
	 * 0: no error
	 * @var int
	 */
	public $mErrorNo = 0;

	/**
	 * Whole mail message
	 * @var string
	 */
	protected $mMsg = '';

	/**
	 * Attachment count named by this class
	 * Attachment already got name is not counted here.
	 * @var int
	 */
	public $mMsgAttachmentNamedCount = 0;

	/**
	 * Body part of mail
	 * @var string
	 */
	protected $mMsgBody = '';

	/**
	 * Contents in mail body
	 * @var array
	 */
	public $mMsgBodyContent = '';

	/**
	 * Header part of mail
	 * @var string
	 */
	protected $mMsgHeader = '';

	/**
	 * Delivered-To: in mail header
	 * @var string
	 */
	public $mMsgHeaderDeliveredTo = '';

	/**
	 * From: in mail header
	 * @var string
	 */
	public $mMsgHeaderFrom = '';

	/**
	 * Message-ID: in mail header
	 * @var string
	 */
	public $mMsgHeaderMessageId = '';

	/**
	 * Subject: in mail header
	 * @var string
	 */
	public $mMsgHeaderSubject = '';

	/**
	 * To: in mail header
	 * @var string
	 */
	public $mMsgHeaderTo = '';

	/**
	 * X-Sender: in mail header
	 * @var string
	 */
	public $mMsgHeaderXSender = '';

	/**
	 * Simple info about mail message
	 * @var array
	 */
	public $mMsgInfo = array();


	/**
	 * Constructor
	 * @param	string	$msg	Mail message
	 */
	public function __construct($msg = '') {
		if (!empty($msg)) {
			$this->mMsg = $msg;
			$this->Parse();
		}
	} // end of func __construct


	/**
	 * Name an un-named attachment
	 * Generate from microtime
	 * @param	string	$mime	Mime type of attachment
	 */
	protected function NameAttachment($mime) {
		// Get name without extension
		//list($msec, $sec) = explode(' ', microtime());
		//$s_name = $sec . substr($msec, 1);
		// These name seems not belong ONE mail, change to another way ...

		// Get name by datetime & md5sum(messageid) & attachment number
		$this->mMsgAttachmentNamedCount ++;
		// Design to name up to 99 attachments, adds '0' before name if attachments count below 10
		$s_name = date('Ymd') . '_' . substr(md5($this->mMsgHeaderMessageId), 0, 8) . '_' . ((1 == strlen(strval($this->mMsgAttachmentNamedCount)))?'0':'') . strval($this->mMsgAttachmentNamedCount);

		// Get extension from mime type
		$ar = array(
			'application/java-archive' => '.jar',
			'application/java-serialized-object' => '.ser',
			'application/java-vm' => '.class',
			'application/msaccess' => '.mdb',
			'application/msword' => '.doc',
			'application/ogg' => '.ogg',
			'application/pdf' => '.pdf',
			'application/pgp-signature' => '.pgp',
			'application/postscript' => '.ps',
			'application/rar' => '.rar',
			'application/rdf+xml' => '.rdf',
			'application/rss+xml' => '.rss',
			'application/rtf' => '.rtf',
			'application/xml' => '.xml',
			'application/zip' => '.zip',
			'application/vnd.google-earth.kml+xml' => '.kml',
			'application/vnd.google-earth.kmz' => '.kmz',
			'application/vnd.mozilla.xul+xml' => '.xul',
			'application/vnd.ms-excel' => '.xls',
			'application/vnd.ms-powerpoint' => '.ppt',
			'application/vnd.oasis.opendocument.chart' => '.odc',
			'application/vnd.oasis.opendocument.database' => '.odb',
			'application/vnd.oasis.opendocument.formula' => '.odf',
			'application/vnd.oasis.opendocument.graphics' => '.odg',
			'application/vnd.oasis.opendocument.graphics-template' => '.otg',
			'application/vnd.oasis.opendocument.image' => '.odi',
			'application/vnd.oasis.opendocument.presentation' => '.odp',
			'application/vnd.oasis.opendocument.presentation-template' => '.otp',
			'application/vnd.oasis.opendocument.spreadsheet' => '.ods',
			'application/vnd.oasis.opendocument.spreadsheet-template' => '.ots',
			'application/vnd.oasis.opendocument.text' => '.odt',
			'application/vnd.oasis.opendocument.text-master' => '.odm',
			'application/vnd.oasis.opendocument.text-template' => '.ott',
			'application/vnd.oasis.opendocument.text-web' => '.oth',
			'application/vnd.visio' => '.vsd',
			'application/x-7z-compressed' => '.7z',
			'application/x-bittorrent' => '.torrent',
			'application/x-cab' => '.cab',
			'application/x-debian-package' => '.deb',
			'application/x-flac' => '.flac',
			'application/x-freemind' => '.mm',
			'application/x-gtar' => '.tgz',
			'application/x-httpd-php' => '.php',
			'application/x-httpd-php-source' => '.phps',
			'application/x-iso9660-image' => '.iso',
			'application/x-javascript' => '.js',
			'application/x-latex' => '.latex',
			'application/x-lha' => '.lha',
			'application/x-lzh' => '.lzh',
			'application/x-msdos-program' => '.exe',
			'application/x-msi' => '.msi',
			'application/x-object' => '.o',
			'application/x-redhat-package-manager' => '.rpm',
			'application/x-sh' => '.sh',
			'application/x-shockwave-flash' => '.swf',
			'application/x-tar' => '.tar',
			'application/x-tcl' => '.tcl',
			'application/x-xfig' => '.fig',
			'application/x-xpinstall' => '.xpi',
			'audio/midi' => '.mid',
			'audio/mpeg' => '.mpga',
			'audio/x-aiff' => '.aif',
			'audio/x-ms-wma' => '.wma',
			'audio/x-pn-realaudio' => '.ra',
			'audio/x-realaudio' => '.ra',
			'audio/x-wav' => '.wav',
			'image/gif' => '.gif',
			'image/jpeg' => '.jpg',
			'image/pcx' => '.pcx',
			'image/png' => '.png',
			'image/svg+xml' => '.svg',
			'image/tiff' => '.tif',
			'image/x-icon' => '.ico',
			'image/x-ms-bmp' => '.bmp',
			'image/x-photoshop' => '.psd',
			'message/rfc822' => '.eml',
			'text/calendar' => '.ics',
			'text/css' => '.css',
			'text/csv' => '.csv',
			'text/html' => '.html',
			'text/plain' => '.txt',
			'text/tab-separated-values' => '.tsv',
			'text/x-c++hdr' => '.hpp',
			'text/x-c++src' => '.cpp',
			'text/x-chdr' => '.h',
			'text/x-csrc' => '.c',
			'text/x-diff' => '.diff',
			'text/x-java' => '.java',
			'text/x-pascal' => '.pas',
			'text/x-perl' => '.pl',
			'text/x-python' => '.py',
			'text/x-sh' => '.sh',
			'text/x-tcl' => '.tcl',
			'text/x-tex' => '.tex',
			'text/x-vcalendar' => '.vcs',
			'text/x-vcard' => '.vcf',
			'video/3gpp' => '.3gp',
			'video/fli' => '.fli',
			'video/mpeg' => '.mpg',
			'video/mp4' => '.mp4',
			'video/quicktime' => '.mov',
			'video/x-ms-asf' => '.asf',
			'video/x-ms-wmv' => '.wmv',
			'video/x-msvideo' => '.avi',
			'x-world/x-vrml' => '.vrml'
			);
		if (isset($ar[$mime]))
			$s_ext = $ar[$mime];
		else
			$s_ext = '';

		return $s_name . $s_ext;
	} // end of func NameAttachment


	/**
	 * Parse mail message
	 * @param	string	$msg	Mail message, If given, will reset all vars and start a new parse process. On default, deal $mMsg will not cause a reset.
	 * @see $mMsg
	 */
	public function Parse($msg = '') {
		// Reset all when $msg is given
		if (!empty($msg)) {
			$this->Reset();
			$this->mMsg = $msg;
		}

		// Msg length
		$this->mMsgInfo['msg_length'] = strlen($this->mMsg);
		$this->mMsg = trim($this->mMsg);
		$this->mMsgInfo['msg_length_trimmed'] = strlen($this->mMsg);

		// Split header & body, find FIRST empty line
		if (0 == preg_match('/\n{2}/m', $this->mMsg, $matches, PREG_OFFSET_CAPTURE)) {
			// No empty line, what's wrong ?
			$this->mErrorNo = 1;
			$this->mErrorMsg = 'Didn\'t find empty line which split header & body.';
			return ;
		} else {
			// Got the split position
			$i = $matches[0][1];
			$this->mMsgHeader = substr($this->mMsg, 0, $i);
			$this->mMsgBody = substr($this->mMsg, $i + 1);

			$this->mMsgInfo['header_length'] = strlen($this->mMsgHeader);
			$this->mMsgInfo['body_length'] = strlen($this->mMsgBody);
		}

		// Parse header & body
		$this->ParseHeader();
		$this->ParseBody();
	} // end of func Parse


	/**
	 * Parse body part of mail
	 * @see $mMsgBody
	 */
	protected function ParseBody() {
		// Find first boundary
		// Content-Type: multipart/mixed; boundary="----=_NextPart_000_0018_01C74EFC.64789E20"
		$b = RegexMatch('/boundary=("?)([^"]+?)\1[;\s]?/', $this->mMsgHeader);
		if (is_array($b))
			$b = $b[1];
		$this->ParseBodyContent($this->mMsgBody, $b);

	} // end of func ParseBody


	/**
	 * Parse content of mail body, recursive
	 * @param	string	$c	Content of mail, with boundary or inline
	 * @param	string	$b	boundary, empty str means inline
	 * @see $mMsgBodyContent
	 */
	protected function ParseBodyContent($c, $b) {
		if (empty($b)) {
			// Inline
			$c = trim($c);
			// Some mail client add '--' after close boundary, remove it
			if (2 == strlen($c) && '--' == $c)
				$c = '';
			if (!empty($c))
				$this->mMsgBodyContent[] = $this->ParseDecode($c);
		} else {
			// Split msg with boundary

			// Confirm boundary first, some mail client will modify boundary slightly(add several '-' before it, or change it's content)
			// Content-Type: multipart/mixed; boundary="----=_NextPart_000_0018_01C74EFC.64789E20"
			// ------=_NextPart_000_0018_01C74EFC.64789E20

			// Seems message is splitted by "--boundary"
			// Content-Type: multipart/mixed; boundary="K8nIJk4ghYZn606h"
			/*
			$bnew = RegexMatch("/\n?(--$b)/", $c);
			if (!empty($bnew)) {
				$b = $bnew[0];
			}
			*/
			$b = "--$b";

			// Using new boundary, find every part
			//echo memory_get_usage() . "$b<br />\n";
			$ar = explode($b, $c);
			//echo memory_get_usage() . "$b<br />\n";
			if (!empty($ar)) {
				foreach ($ar as $part) {
					// Parse every part
					// Un-standard boundary declare:
					//	boundary=Apple-Mail-10-288581275
					//	I used a regex recall '\1'
					// Also +? to refuse '贪婪' of regex
					$b_part = RegexMatch('/boundary=("?)([^"]+?)\1[;\s]+/', $part);
					//print_r($b_part);
					// If multi boundary found, choose the firse one.
					// Then choose value \2
					if (isset($b_part[1]) && is_array($b_part[1]))
						$b_part = $b_part[0][1];
					elseif (is_array($b_part))
						$b_part = $b_part[1];

					// Remove 'boundary=...' from part, or it will find 'new' boundary recurrently.
					if (!empty($b_part)) {
						$part = str_replace("boundary=\"$b_part\"", '', $part);
						$part = str_replace("boundary=$b_part", '', $part);
					}
					$this->ParseBodyContent(trim($part), $b_part);
				}
			}
		}
	} // end of func ParseBodyContent


	/**
	 * Decode parts of mail body, usually find by ParseBodyContent	 *
	 * @param	string	$c	Parts string
	 * @return	array
	 * @see ParseBodyContent()
	 */
	protected function ParseDecode($c) {
		/*
		Content-Type: image/jpeg
		Content-Type: multipart/alternative;
		Content-Type: text/plain; charset=iso-8859-1
		Content-Type: text/plain; charset=ISO-8859-1; format=flowed
		Content-Transfer-Encoding: base64
		Content-Transfer-Encoding: quoted-printable
		Content-Transfer-Encoding: 7bit
		Content-Disposition: inline;
		filename="ma_Jusko_Attack_of_the_Targa.jpg"
		Content-Type: application/pgp-signature; name="signature.asc"
		Content-Disposition: attachment; filename="DbExchange.tgz"
		Content-Disposition: attachment; filename*=utf-8''20071010-%E7%9B%91%E7%AE
		Content-ID: <3561580184000005@web36903.mail.mud.yahoo.com>
		*/
		$c = trim($c);

		// Find "header" part, identify by "two \n"
		//$s_h = substr($c, 0, strpos($c, "\n\n"));
		//if (empty($s_h)) {

		// Find "header" part, identify by 'Content'
		if (!(('Content' == substr($c, 0, 7)) || ('content' == substr($c, 0, 7)))) {
			// No content define, output directly
			$rs = array('type' => 'text/plain',
				'content' => $c,
				'charset' => '',
				'encoding' => '',
				'filename' => ''
				);
		} else {
			// Read the "header" defination, set the rs options and parse body string
			$i = strpos($c, "\n\n");
			if (false === $i) {
				// Special situation, eg: only have 1 line (header define):
				// Content-Type: multipart/alternative;
				$s_header = $c;
				$s_body = '';
			} else {
				$s_header = substr($c, 0, $i);
				$s_body = substr($c, $i +1);
			}

			// Prepare default value
			$rs = array();
			$rs['type'] = '';
			$rs['content'] = '';
			$rs['charset'] = '';
			$rs['encoding'] = '';
			$rs['filename'] = '';

			// Is there a Content-Type define ?
			$s_t = RegexMatch('/Content-Type: ([\w\d\/\-\+\.]+)[;\s]/i', $s_header);
			if (!empty($s_t)) {
				$rs['type'] = $s_t;
				// I don't know is this way right, but this will got an empty content, works correctly.
				// Multi-part container
				if ('multipart/alternative' == $s_t)
					return $rs;
			}

			// Charset ?
			$s_t = RegexMatch('/charset=([\w\d\-]+)[;\s]/i', $s_header);
			if (!empty($s_t)) {
				$rs['charset'] = $s_t;
				// Convert content to utf-8 encoding
				if ('utf-8' != strtolower($s_t))
					$s_body = mb_convert_encoding($s_body, 'utf-8', $s_t);
			}

			// quoted-printable encoding ? its format like '=0D=0A'
			if (0 < substr_count($s_header, 'quoted-printable')) {
				$s_body = quoted_printable_decode($s_body);
				$rs['encoding'] = 'quoted-printable';
			}

			// Base64 encoding ?
			if (0 < substr_count($s_header, 'base64')) {
				$s_body = base64_decode($s_body);
				$rs['encoding'] = 'base64';
			}

			// Content-Disposition:, means this is an attachment
			if (0 < substr_count($s_header, 'Content-Disposition:')
				|| 0 < substr_count($s_header, 'Content-ID:')
				|| 0 < substr_count($s_header, 'attachment')
				|| 0 < substr_count($s_header, 'name=')
				|| 'image/jpeg' == $rs['type']
				|| 'image/gif' == $rs['type']
				//|| 0 < substr_count($s_header, 'filename')
				) {
				// Find the filename or name it, if filename is empty, this is not an attachment
				/*
		Content-Type: application/pgp-signature; name="signature.asc"
		Content-Disposition: attachment; filename="DbExchange.tgz"
		Content-Disposition: attachment; filename*=utf-8''20071010-%E7%9B%91%E7%AE
		Content-ID: <3561580184000005@web36903.mail.mud.yahoo.com>
				*/
				$s_t = RegexMatch('/name="([^"]*)"/i', $s_header);
				if (empty($s_t)) {
					$s_t = RegexMatch('/filename*=([^\s]*)\s+/i', $s_header);
				}
				// Avoid multi name define
				while (is_array($s_t)) {
					$s_t = $s_t[0];
				}
				// Still can't get filename
				if (empty($s_t)) {
					// Name it ...
					$rs['filename'] = $this->NameAttachment($rs['type']);
				} else {
					// Set the filename
					//$rs['filename'] = imap_utf8($s_t);
					$rs['filename'] = Rfc2047Decode($s_t);
				}

				// Some bad mail client didn't set the attach mime right
				// Content-Type: application/octet-stream;
				//	name="Dave_Nitsche_036.jpg"
				if ('.jpg' == strtolower(substr($rs['filename'], strlen($rs['filename']) - 4)))
					$rs['type'] = 'image/jpeg';
				if ('.gif' == strtolower(substr($rs['filename'], strlen($rs['filename']) - 4)))
					$rs['type'] = 'image/gif';
				if ('.png' == strtolower(substr($rs['filename'], strlen($rs['filename']) - 4))) {
					$rs['type'] = 'image/png';
				}

			} else {
				// Not an attachment
				$rs['filename'] = '';
			}

			// 7bit, 8bit, inline need no change to $s_body

			// Set $s_body
			$rs['content'] = $s_body;
		}

		return $rs;
	} // end of func ParseDecode


	/**
	 * Parse header part of mail
	 * @see $mMsgHeader
	 */
	protected function ParseHeader() {
		// Delivered-To: fwolf.ssaint@gmail.com
		$this->mMsgHeaderDeliveredTo = RegexMatch('/^Delivered-To: (.*)/m', $this->mMsgHeader);
		// From: "Sammy Benjamin" <sammynatural@gmail.com>
		$this->mMsgHeaderFrom = RegexMatch('/^From: (.*)/m', $this->mMsgHeader);
		// Message-ID: <061c01c74f26$c1df0ac0$d6422241@psasquatch>
		$this->mMsgHeaderMessageId = RegexMatch('/^Message-ID: <(.*)>/m', $this->mMsgHeader);
		if (empty($this->mMsgHeaderMessageId)) {
			// Fake a message-id
			$this->mMsgHeaderMessageId = md5($this->mMsgHeader);
		}
		// Subject:
		//$this->mMsgHeaderSubject = imap_utf8(RegexMatch('/^Subject: (.*)/m', $this->mMsgHeader));
		$this->mMsgHeaderSubject = Rfc2047Decode(RegexMatch('/^Subject: (.*)/m', $this->mMsgHeader));
		// To: <Undisclosed-Recipient:;@gmail-pop.l.google.com>
		$this->mMsgHeaderTo = RegexMatch('/^To: (.*)/m', $this->mMsgHeader);
		// X-Sender: sammynatural@gmail.com
		$this->mMsgHeaderXSender = RegexMatch('/^X-Sender: (.*)/m', $this->mMsgHeader);
	} // end of func ParseHeader


	/**
	 * Reset all vars, prepare to a new parse process.
	 */
	public function Reset() {
		//:TODO: reset all data-vars, include $this->mMsg
		$this->mErrorMsg = '';
		$this->mErrorNo = 0;
		$this->mMsg = '';
		$this->mMsgAttachmentCount = 0;
		$this->mMsgBody = '';
		$this->mMsgBodyContent = array();
		$this->mMsgHeader = '';
		$this->mMsgHeaderDeliveredTo = '';
		$this->mMsgHeaderFrom = '';
		$this->mMsgHeaderMessageId = '';
		$this->mMsgHeaderSubject = '';
		$this->mMsgHeaderTo = '';
		$this->mMsgHeaderXSender = '';
		$this->mMsgInfo = array();
	} // end of func Reset

} // end of class MailParser

/*
// Test:
require_once('fwolflib/func/ecl.php');
//$mailtext = file_get_contents('1171559981.7971_1.wf:2,');
$mailtext = file_get_contents('1171559410.7880_1.wf:2,');

$mp=new MailParser($mailtext);

ecl("Message length: " . number_format($mp->mMsgInfo['msg_length']));
ecl("Message length trimmed: " . $mp->mMsgInfo['msg_length_trimmed']);
ecl("Header length: " . $mp->mMsgInfo['header_length']);
ecl("Body length: " . $mp->mMsgInfo['body_length']);
ecl("Delivered-To: " . htmlentities($mp->mMsgHeaderDeliveredTo));
ecl("From: " . htmlentities($mp->mMsgHeaderFrom));
ecl("Message-ID: <" . htmlentities($mp->mMsgHeaderMessageId) . ">");
ecl("Subject: " . htmlentities($mp->mMsgHeaderSubject));
ecl("To: " . htmlentities($mp->mMsgHeaderTo));
ecl("X-Sender: " . htmlentities($mp->mMsgHeaderXSender));

ecl("Body content part count: " . count($mp->mMsgBodyContent));
// :DEBUG:
foreach ($mp->mMsgBodyContent as $val) {
	echo "----------\nContent type: {$val['type']} <br />\n";
	echo "	Length: " . strlen($val['content']) . "<br />\n";
	echo "	Charset: " . $val['charset'] . "<br />\n";
	echo "	Encoding: " . $val['encoding'] . "<br />\n";
	echo "	Filename: " . $val['filename'] . "<br />\n";
}
// Output mail message & attachment
ecl("===================================================");
if (0 < count($mp->mMsgBodyContent)) {
	foreach ($mp->mMsgBodyContent as $val) {
		if (empty($val['filename'])) {
			// Common message
			$c = $val['content'];
			if ('text/plain' == $val['type'])
				$c = nl2br($c);
			echo $c;
		} else {
			// Attachment
			ecl($val['filename']);
			if ('image/' == substr($val['type'], 0, 6))
				ecl('<img src="data:' . $val['type'] . ';base64,' . base64_encode($val['content']) . '"/>');
		}
	}
}

if (0 != $mp->mErrorNo) {
	ecl($mp->mErrorMsg);
}

*/

//ecl("to: " . htmlentities($mp->to));
//ecl("subject: " . htmlentities($mp->subject));
//ecl("recieved: " . htmlentities($mp->received));

class parseMail {
	var $from="";
	var $to="";
	var $subject="";
	var $received="";
	var $date="";
	var $message_id="";
	var $content_type="";
	var $part =array();

	// decode a mail header
	function parseMail($text="") {
		$start=0;
		$lastheader="";
		while (true) {
			$end=strpos($text,"\n",$start);
			$line=substr($text,$start,$end-$start);
			$start=$end+1;
			if ($line=="") break; // end of headers!
			if (substr($line,0,1)=="\t") {
				$$last.="\n".$line;
			}
			if (preg_match("/^(From:)\s*(.*)$/",$line,$matches)) {
				$last="from";
				$$last=$matches[2];
			}
			if (preg_match("/^(Received:)\s*(.*)$/",$line,$matches)) {
				$last="received";
				$$last=$matches[2];
			}
			if (preg_match("/^(To:)\s*(.*)$/",$line,$matches)) {
				$last="to";
				$$last=$matches[2];
			}
			if (preg_match("/^(Subject:)\s*(.*)$/",$line,$matches)) {
				$last="subject";
				$$last=$matches[2];
			}
			if (preg_match("/^(Date:)\s*(.*)$/",$line,$matches)) {
				$last="date";
				$$last=$matches[2];
			}
			if (preg_match("/^(Content-Type:)\s*(.*)$/",$line,$matches)) {
				$last="content_type";
				$$last=$matches[2];
			}
			if (preg_match("/^(Message-Id:)\s*(.*)$/",$line,$matches)) {
				$last="message_id";
				$$last=$matches[2];
			}
		}
		$this->from=$from;
		$this->received=$received;
		$this->to=$to;
		$this->subject=$subject;
		$this->date=$date;
		$this->content_type=$content_type;
		$this->message_id=$message_id;

		if (preg_match("/^multipart\/mixed;/",$content_type)) {
			$b=strpos($content_type,"boundary=");
			$boundary=substr($content_type,$b+strlen("boundary="));
			$boundary=substr($boundary,1,strlen($boundary)-2);
			$this->multipartSplit($boundary,substr($text,$start));

		} else {
			$this->part[0]['Content-Type']=$content_type;
			$this->part[0]['content']=substr($text,$start);
		}
	}
	// decode a multipart header
	function multipartHeaders($partid,$mailbody) {
		$text=substr($mailbody,$this->part[$partid]['start'],
		             $this->part[$partid]['ende']-$this->part[$partid]['start']);

		$start=0;
		$lastheader="";
		while (true) {
			$end=strpos($text,"\n",$start);
			$line=substr($text,$start,$end-$start);
			$start=$end+1;
			if ($line=="") break; // end of headers!
			if (substr($line,0,1)=="\t") {
				$$last.="\n".$line;
			}
			if (preg_match("/^(Content-Type:)\s*(.*)$/",$line,$matches)) {
				$last="c_t";
				$$last=$matches[2];
			}
			if (preg_match("/^(Content-Transfer-Encoding:)\s*(.*)$/",$line,$matches)) {
				$last="c_t_e";
				$$last=$matches[2];
			}
			if (preg_match("/^(Content-Description:)\s*(.*)$/",$line,$matches)) {
				$last="c_desc";
				$$last=$matches[2];
			}
			if (preg_match("/^(Content-Disposition:)\s*(.*)$/",$line,$matches)) {
				$last="c_disp";
				$$last=$matches[2];
			}
		}
		if ($c_t_e=="base64") {
			$this->part[$partid]['content']=base64_decode(substr($text,$start));
			$c_t_e="8bit";
		} else {
			$this->part[$partid]['content']=substr($text,$start);
		}
		$this->part[$partid]['Content-Type']=$c_t;
		$this->part[$partid]['Content-Transfer-Encoding']=$c_t_e;
		$this->part[$partid]['Content-Description']=$c_desc;
		$this->part[$partid]['Content-Disposition']=$c_disp;
		unset($this->part[$partid]['start']);
		unset($this->part[$partid]['ende']);
	}
	// we have a multipart message body
    // split the parts
	function multipartSplit($boundary,$text) {
		$start=0;
		$b_len=strlen("--".$boundary);
		$partcount=0;
		while (true) { // should have an emergency exit...
			$end=strpos($text,"--".$boundary,$start);
			if (substr($text,$end+$b_len,1)=="\n") {
				// '\n' => part boundary
				$this->part[$partcount]['start']=$end+$b_len+1;
				if ($partcount) {
					$this->part[$partcount-1]['ende']=$end-1;
					$this->multipartHeaders($partcount-1,$text);
				}
				$start=$end+$b_len+1;
				$partcount++;
			} else {
				// '--' => end boundary
				$this->part[$partcount-1]['ende']=$end-1;
				$this->multipartHeaders($partcount-1,$text);
				break;
			}
		}
	}
}

?>
