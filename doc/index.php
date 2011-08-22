<?php
/**
 * Index and displayer for fwolflib/doc
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-04-19
 * @version		$Id$
 */

require_once('fwolflib/class/doc-markdown.php');
require_once('fwolflib/func/filesystem.php');

// Get file list
$ar_exclude = array(
	'changelog-syntax.txt', 'example.php', 'index.php'
);
$s_path = substr(__FILE__, 0, strrpos(__FILE__, '/') + 1);
$ar = ListDir($s_path);
$ar_file = array();
$ar_finfo = array();
foreach ($ar as $file) {
	if (!in_array($file['name'], $ar_exclude)) {
		$ar_file[] = $file['name'];
		$ar_finfo[$file['name']]['mtime'] = date('Y-m-d H:i:s', $file['mtime']);
		$ar_finfo[$file['name']]['size'] = filesize($s_path . $file['name']);
	}
}
asort($ar_file);
//$ar_file = array(
//	'changelog.php',
//);
//$ar_finfo = array();

// If $_GET['f'] exists and in $ar_file, change to doc display
if (isset($_GET['f'])) {
	$file = trim($_GET['f']);
	if (in_array($file, $ar_file)) {
		$s = substr(__FILE__, 0, strrpos(__FILE__, '/') + 1) . $file;
		if (is_readable($s)) {
			// Display it !
			$dm = new DocMarkdown();
			require($s);
			$dm->SetInfoFwolflib();
			$dm->aInfo = array_merge($dm->aInfo, $ar_info);
			$dm->aBody = $ar_body;
			$s = $dm->GetOutput();
			// Hack 'view=sourcecode'
			$s = str_replace('?view=sourcecode',
				'?f=' . urlencode($file) . '&view=sourcecode', $s
			);
			echo $s;
			exit(0);
		}
	}
}

// Else, display file index

// Get title in file
foreach ($ar_file as $file) {
	$s = substr(__FILE__, 0, strrpos(__FILE__, '/') + 1) . $file;
	if (is_readable($s)) {
//		require($s);
//		if (isset($ar_info['title']))
//			$ar_finfo[$file]['title'] = $ar_info['title'];
//		unset($ar_info);
//		unset($ar_body);
		// Read file and find 'title' set
		$str = file_get_contents($s);
		$ar = array();
		$i = preg_match('/[^\/]\$[\w_>-]+\[\'title\'\][\s]*=[\s]*(.*);/'
			, $str, $ar);
		if (0 < $i)
			$ar_finfo[$file]['title'] = trim($ar[1], '\'"');
		unset($str);
	}
}

// Print file info with link, using DocMarkdown
$dm = new DocMarkdown();

$dm->aInfo['title']			= 'Doc in fwolflib';
$dm->aInfo['author']		= 'Fwolf';
$dm->aInfo['authormail']	= 'fwolf.aide+fwolflib.doc@gmail.com';
$dm->aInfo['keywords']		= 'doc, index, rule, fwolflib';
$dm->aInfo['description']	= 'Can use or extend in project。';

$s_body = "<div class='single_line' markdown='1'>\n";
$s_body .= 'Name | Title | Last modified | Size';
$s_body .= "\n----|----|----|----\n";
foreach ($ar_file as $file) {
	$s_body .= "<a href='?f=" . urlencode($file) . "'>$file</a> | ";
	if (isset($ar_finfo[$file]['title']))
		$s_body .= $ar_finfo[$file]['title'] . ' | ';
	else
		$s_body .= ' | ';
	$s_body .= $ar_finfo[$file]['mtime'] . ' | ';
	$s_body .= $ar_finfo[$file]['size'] . ' | ';
	$s_body .= "\n";
}
$s_body .= "</div>\n";
$dm->aBody[] = $s_body;

$dm->Display();
?>
