<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2007, Fwolf
 * @author		Fwolf <fwolf.aide@gmail.com>
 * @since		2007-04-29
 * @version		$Id$
 */

/**
 * Download content as a file
 * @param	string	$content	Content to download
 * @param	string	$filename	Download file name
 * @param	string	$mime		Mime type of file
 */
function Download($content, $filename, $mime = 'application/force-download')
{
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

	// Write content to file & read in pieces to download
	list($usec, $sec) = explode(" ", microtime());
	$usec = substr(strval($usec), 2);
	$ft_name = '/tmp/' . $sec . '.' . $usec;
	file_put_contents($ft_name, $content);

	// Read temp file & output
	$size = filesize($ft_name);
	$fp = fopen($ft_name, "rb");
	//fseek($fp,$range);
	// Start buffered download
	//reset time limit for big files
	set_time_limit(0);
	while(!feof($fp))
	{
		print(fread($fp, 1024 * 8));
		//flush();   这个是多余的函数,加上会使压缩包下载不完整
		//ob_flush();  这个也是多余的函数,加上会使压缩包下载不完整
	}
	
	fclose($fp);
	//unlink($ft_name);
	exit;
}
?>