<?php
/**
 * Example writing doc using fwolflib/doc
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-04-19
 * @version		$Id$
 */

require_once('fwolflib/doc/changelog.php');

// Use original fwolflib/doc
require_once('fwolflib/class/doc-markdown.php');
$dm = new DocMarkdown();
// Or
// Use customized project doc class
//require_once('fwolflib/func/env.php');
//define('P2R', P2r('../'));
//require_once(P2R . 'inc/doc-markdown.php');
//$dm = new DocMarkdownCbtms();

//$dm->aInfo = $ar_info;
// Or
$dm->aInfo = array_merge($dm->aInfo, $ar_info);
// Or
//$dm->aInfo['title']			= 'Changelog的写法';
//$dm->aInfo['author']		= 'Fwolf';
//$dm->aInfo['authormail']	= 'fwolf.aide+fwolflib.doc@gmail.com';
//$dm->aInfo['keywords']		= 'log, doc, change, changelog, rule';
//$dm->aInfo['description']	= '请在COMMIT的时候依照此规则。';

$dm->aBody = $ar_body;
// Or
//$dm->aBody[] = '';
//$dm->aBody[-1] = '';
//$dm->aBody[1] = '';

$dm->Display();
?>