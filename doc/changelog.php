<?php
/**
 * Changelog的写法
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-04-19
 * @version		$Id$
 */

// Config
$ar_info = array();
$ar_info['title']		= 'Changelog的写法';
$ar_info['author']		= 'Fwolf';
$ar_info['authormail']	= 'fwolf.aide+fwolflib.doc@gmail.com';
$ar_info['keywords']	= 'log, doc, change, changelog, rule';
$ar_info['description']	= '请在COMMIT的时候依照此规则。';

// Content
$ar_body = array();
$ar_body[] = '
Begin with these words, works better with GIT.
Compate with style started with 3 chars.

- Add something
- Bug [fix|found]: describe the bug or fix.
- Chg something
- Del something
- Enh some treatment
- New something
- Tmp for some cause


###### Old style, obsolete from 2009-03-31

- ADD: 
- BUG: 
- CHG: 
- DEL: 
- DEV: 
- ENH: 
- NEW: 
';

?>