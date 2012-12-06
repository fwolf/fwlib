..	-*- mode: rst -*-
..	-*- coding: utf-8 -*-


===========================================================================
Readme for Fwolflib
===========================================================================



Class
============================================================



benchmark.php::Benchmark
--------------------------------------------------


Benchmark code running time cost by set marker in code.



cache/
--------------------------------------------------


cache.php::Cache
	Base class for key - value like cache system.

cache-file.php::CacheFile
	K-v cache class using file storage.

cache-memcached.php::CacheMemcached
	K-v cache class using memcached storage.



cache.php::Cache (Deprecated)
--------------------------------------------------


Key - value like cache system, data store in filesystem.



curl.php::Curl
--------------------------------------------------

A class aimed to use curl function efficiency.
Useful for write webgame bot, or an information thief program.



curl-comm.php::CurlComm
--------------------------------------------------


Commucate with server via http using Curl.



dbbak2sql.php::DbBak2Sql
--------------------------------------------------


Backup db to standard sql files.



dbupdater.php:DbUpdater
--------------------------------------------------


Database mantance & update tools.



dict.php::Dict
--------------------------------------------------


Dict data management, mostly for db.



disp_listtable.php::DispListTable (Deprecated)
--------------------------------------------------


(Use ListTable instead)
列表类，以包含表头、分页、数据表格的方式显示各种列表。



doc-markdown.php::DocMarkDown
--------------------------------------------------


Easy write/display doc using Markdown markup language.



ebay.php::Ebay
--------------------------------------------------


eBay API caller class, only a few func done.



excel.php::Excel
--------------------------------------------------


Generate excel file, xml mode, need high version of MS Excel.



fwolflib.php::Fwolflib
--------------------------------------------------


Basic class of all. Log included.



list-table.php::ListTable
--------------------------------------------------


General table style display html to list data.



mail_parser.php::MailParser
--------------------------------------------------


Parse a maildir format mail file, got info, especially attachments.



mailsender.php::MailSender
--------------------------------------------------


Easy send mail using PHPMailer.



sql_generator.php::SqlGenerator
--------------------------------------------------


Tools class to generator compative sql parse, Adodb object needed.



todatauri.php::ToDataUri
--------------------------------------------------


Can save html all in 1 file.



validator.php::Validator
--------------------------------------------------


Validate form data, include web frontend and php backend.



ajax/
--------------------------------------------------

ajax-sel-div.php::AjaxSelDiv
	Select data using ajax div.



CSS
============================================================



filename.php is loader for php, return css file content.



default.css
--------------------------------------------------


Default normal css.



reset.css
--------------------------------------------------


Eric Meyer’s css reset.



Function
============================================================



array.php
--------------------------------------------------


ArrayAdd (&$ar_srce, $key, $val = 1)
	Add value to array by key, if key not exist, init with value.

ArrayEval ($s_eval, $ar = array())
	Eval string by replace tag with array value by index.

ArrayInsert (&$ar_srce, $idx, &$ar_ins, $i_pos = 1)
	Insert data to assigned position in srce array by assoc key.

ArrayRead ($ar, $key, $val_default = null)
	Read value from array.

ArraySort (&$ar_srce, $key, $b_asc = true, $joker = '')
	Sort array by one of its 2lv keys, and maintain assoc index.

FilterWildcard ($ar_srce, $rules)
	Filter an array by wildcard rules.



client.php
--------------------------------------------------


ClientIpFromHex ($hex)
	Get user ip from hex string.

ClientIpToHex ($ip)
	Convert user ip to hex string format.

GetBrowserType ()
	检查客户端的浏览器是NS还是IE(old)

GetClientIp ()
	Get ip of client.



config.php
--------------------------------------------------


GetCfg and SetCfg function.


GetCfg ($cfg)
	Return value of config string $cfg.

LimitServerId ($id)
	Limit program can only run on prefered server.

SetCfg ($cfg, $value)
	Set global config by config string $cfg using value $value.



crypt.php
--------------------------------------------------


MCryptSmplIvDecrypt/MCryptSmplIvEncrypt (...)
	Use mcrypt to de/encrypt, using a simple way to generate IV.



datetime.php
--------------------------------------------------


Date and time func.


SecToStr ($i_sec, $b_simple = true)
	Convert sec back to str describe.

StrToSec ($str)
	Convert str to seconds it means.

Strtotime1 ()
	Remove ':000' before original strtotime().



dbwrite.php
--------------------------------------------------


DbWrite ($db, $tbl, $data, $pkey)
	向数据库中写入数据，根据指定的主键或联合主键自动判断是insert还是update。



download.php
--------------------------------------------------


Download contents as a file.



ecl.php
--------------------------------------------------


Smart echo line, end with \n or <br /> according running mod



env.php
--------------------------------------------------


Runtime environment and server env variant.


ForceHttps ()
	Force page to be visit through https://.

IsCli ()
	Check if this program is running under cli mod, or is viewing in browser

NixOs ()
	判断当前主机是否nix操作系统



escape_color.php
--------------------------------------------------


Covert escape color to html code



filesystem.php
--------------------------------------------------


文件系统常用函数。


BaseName1 ($filename)
	Manual get basename instead of using pathinfo()

DelFile ($file_or_dir)
	Delete a dir or file completedly

DirName1 ($filename)
	Manual get dirname instead of using pathinfo()

DirSize ($path)
	Count size of a directory, recursive

FileExt1 ($filename)
	Manual get extension instead of using pathinfo()

FileName1 ($filename)
	Manual get filename instead of using pathinfo()

FileSize1 ($file)
	Count size of a file

GetFilenameToWrite ($s_file)
	Get/gen a filename to write as a new file.

ListDir ($dir)
	List files and file-information of a directory order by mtime asc.



formatbytesize.php
--------------------------------------------------


Convert variant byte size to human readable format string.



ini.php
--------------------------------------------------


IniGet ($filepath, $section = '', $item = '')
	Read ini file, return array of part of the value.
	Notice to retrieve global value, set $section to ' ' instead of ''.



request.php
--------------------------------------------------


与 GET 和 POST 参数及 http 请求有关的函数集。


GetGet ($var, $default)
	Get varient from $_GET

GetParam ($k = '', $v = '', $b_with_url = false)
	Get and return modified url param.

GetPost ($var, $default)
	Get varient from $_POST

GetSelfUrl ()
	Get self url which user visit, including GET parameters.

GetUrlPlan ($url = '')
	Get http/https from an url or self.



regex_match.php
--------------------------------------------------


RegexMatch($preg, $str = '', $csrts = true)
	Match content using preg, return result array or '' if non-match.



string.php
--------------------------------------------------


常用字符串函数集。


AddslashesRecursive ($srce)
	Addslashes for any data, recursive.

JsonEncodeHex ($val)
	Json encode with JSON_HEX_(TAG|AMP|APOS|QUOT) options.

JsonEncodeUnicode ($val, $option = 0)
	Json encode, simulate JSON_UNESCAPED_UNICODE option is on.

MatchWildcard ($str, $rule)
	Match a string with rule including wildcard.

Pin15To18 ($pin)
	Convert 15-digi pin to 18-digi.

SubstrIgnHtml ($str, $len, $marker, $start = 0, $encoding = 'utf-8')
	Get substr by display width, and ignore html tag's length.



url.php
--------------------------------------------------


处理url字符串，增加或设置/更改URL参数。



utf8_fix.php
--------------------------------------------------


Convert string like '_D0_D0_D0' to normal string



uuid.php
--------------------------------------------------


Uuid ($s_cus, $s_cus2)
	Generate an UUID.

UuidParse ($uuid)
	Get information from an UUID.

UuidSpeedTest ($num, $file)
	Test how many uuid can this program generate per second.



validate.php
--------------------------------------------------


ValidateIp ($ip)
	If an ip string given is valid address.

ValidateEmail ($email)
	Validate an email address.



JavaScript
============================================================



alert.js
--------------------------------------------------


JsAlert (msg, title, s_id, b_show_close, b_show_bg)
	Show msg using js/jQuery, with a float div.


common.js
--------------------------------------------------


通用 JS 函数集。



cookie.js
--------------------------------------------------


Cookie 操作 JS 函数集。



validate.js
--------------------------------------------------


数据效验 JS 函数集。
