<?php
/**
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2007-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2007-04-29
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/env.php');
require_once(FWOLFLIB . 'func/filesystem.php');


/**
 * Download content as a file
 * @param	string	$content	Content to download
 * @param	string	$filename	Download file name, send to client, not path on server.
 * @param	string	$mime		Mime type of file
 * @return	boolean
 */
function Download($content, $filename = '', $mime = 'application/force-download')
{
	list($usec, $sec) = explode(" ", microtime());
	$usec = substr(strval($usec), 2, 3);
	$tmpfilename = $sec . $usec;

	if (empty($filename)) {
		// Use timestamp as filename if not provide
		$filename = $tmpfilename;
	}

	if (NixOs()) {
		$filepath = '/tmp/';
	} else {
		$s_tmp = '';
		if (!empty($_ENV["TEMP"]))
			$s_tmp = $_ENV["TEMP"];
		if (empty($s_tmp) && !empty($_ENV["TMP"]))
			$s_tmp = $_ENV["TMP"];
		// Default, this should never accur
		if (empty($s_tmp))
			$s_tmp = 'c:/windows/temp/';
		// And check again
		if (!is_dir($s_tmp) || !is_writable($s_tmp))
			die('No temp dir to store file content which need to downloaded.');

		$filepath = $s_tmp;
	}
	// Add the ending '/' to tmp path
	if ('/' != substr($filepath, -1))
		$filepath .= '/';
	// Then got full path of tmp file
	$tmpfilename = $filepath . $tmpfilename;

	file_put_contents($tmpfilename, $content);
	$result = DownloadFile($tmpfilename, $filename, $mime);

	unlink($tmpfilename);
	return $result;
}


/**
 * Download a file
 * @param	string	$filepath	Full path to download file.
 * @param	string	$filename	Download file name, send to client, not path on server.
 * @param	string	$mime		Mime type of file
 * @return	boolean
 */
function DownloadFile($filepath, $filename = '', $mime = 'application/force-download')
{
	// Check and fix parameters
	if (!is_file($filepath) || !is_readable($filepath))
		return false;
	// If no client filename given, use original name
	if (empty($filename))
		$filename = BaseName1($filepath);

	// Begin writing headers
	header("Cache-Control:");
	header("Cache-Control: public");

	//Use the switch-generated Content-Type
	header("Content-Type: $mime");

	// workaround for IE filename bug with multiple periods / multiple dots in filename
	// that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
		// count is reference (&count) in str_replace, so can't use it.
		$filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
		//$iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);

	header("Content-Disposition: attachment; filename=\"$filename\"");
	//header("Content-Range: $from-$to fsize");  加上压缩包头信息不正确
	//header("Content-Length: $content_size");   加上压缩包头信息不正确

	header("Accept-Ranges: bytes");

	// Read temp file & output
	$size = filesize($filepath);
	$size_downloaded = 0;	// Avoid infinite loop
	$size_step = 1024 * 8;	// Control download speed

	$fp = fopen($filepath, "rb");
	//fseek($fp,$range);
	// Start buffered download
	//reset time limit for big files
	set_time_limit(0);
	while(!feof($fp) && ($size > $size_downloaded))
	{
		print(fread($fp, $size_step));
		$size_downloaded += $size_step;
		//flush();   这个是多余的函数,加上会使压缩包下载不完整
		//ob_flush();  这个也是多余的函数,加上会使压缩包下载不完整
	}

	fclose($fp);
	//unlink($ft_name);
	exit;
} // end of function DownloadFile
?>
