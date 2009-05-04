<?php
/**
 * 文件格式规范
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-05-04
 * @version		$Id$
 */

$ar_info = array();
$ar_info['title'] 		= '文件格式规范';
$ar_info['author']		= 'rxl';
$ar_info['authormail'] 	= 'gj3rxl@gmail.com';
$ar_info['keywords']	= 'work, file, format, doc, rules';
$ar_info['description']	= '对文件结构如何组织、如何命名、使用什么格式做出约定。';

$ar_body = array();
$ar_body[] = '

本文中用`$PROJ`表示项目根目录。


## 文件编码

全部使用utf-8编码，不带BOM。


## 文件格式

每行不超过78个字符，手工折行，参考：
[PBP代码布局(3)](http://tech.idv2.com/2008/03/11/pbp-code-layout-3/)。

使用TAB缩进，显示建议调整为1个TAB显示为4个空格的宽度。

PHP结束标记`?>`后面跟且只跟一个回车，多跟会在html中输出空行。
Geany的`File -> Ensure new line at file end`选项可以自动实现。


## 文件名命名规则

### 所有文件名一律使用小写字母、数字和“_-”的组合

* 用`-`分隔不同含义的内容，用`_`分隔一项内容的不同单词，例如：
	工程合同的编辑，文件名应该为bid_contract-edit.ext。
* 缩进原则上使用Tab，如果需要用空格缩进，使用4个空格。

### MVC派生类命名规则

Module类所在的文件采用`m-功能.php`格式的名称，`功能`的命名方式参见[通用命名规则](rule-naming.php)。

View类所在的文件采用`v-功能.php`格式的名称，`功能`的命名方式参见[通用命名规则](rule-naming.php)。

Controler类为各目录下固定名称的`index.php`。

### 其余文件

* 按照尽可能把相似功能排列在一起的原则来命名文件。
* 函数库统一用`func-xxx.php`的格式，如果函数比较多，可以建立目录`$PROJ/inc/func`，
同时在函数名中去掉`func-`部分。
* MVC基类采用`mvc-[controler|module|view].php`的格式。
* 各类规范和约定使用`rule-xxx.php`的格式。

### 文件名前缀省略说明

如果相同前缀的文件是存放在单独的文件夹中的话，前缀是可以省略的，但在现在习惯未形成之前，
到底是不是需要使用单独的文件夹存放还没有定论，所以带上前缀的适用性更强一些，尤其在目录内各类文件都有的情况下。


## 项目目录结构

由于所有目录、文件名均为英文，且不包含空格，所以下面的目录结构中，空格后面的中文是说明。

	$PROJ/
		|----dbdesign 数据库设计文档
			|----initsql 初始化数据，字典数据
		|----doc 系统文档、约定
		|----inc 全局包含文件、项目基类、函数库
			|----smarty 项目中继承Smarty类的基类
				|----configs Smarty的配置文件
				|----plugins Smarty的自定义插件目录
		|----tpl 模板目录
			|----default 默认模板
				|----images 模板中的图片
				|----js 模板中要使用到的javascript
			|----tpl2 模板2
		|----view 全局的页面显示类
		|----subsys1 某子系统一的目录
			|----module 子系统中的Module类(文件不多时可合并到上级目录中)
			|----view 子系统中的View类(文件不多时可合并到上级目录中)
		|----subsys2 某子系统二的目录

Cache目录的结构说明，此目录在项目源码目录之外。

	$config["cache.dir"]/
		|----dict		字典表缓存文件目录。
		|----tpl_c		Smarty编译后模板的存放目录
		|----tpl_cache	Smarty模板缓存目录

## 特殊文件说明

### 配置文件机制

`$PROJ/config.default.php`是项目设置的模板文件，随项目更新，纳入svn管理；
`$PROJ/config.php`是项目针对某个测试或生产环境的具体配置文件，只在本地维护，不纳入git/svn管理，
并添加到`$PROJ/`的`.gitignore`或`svn:ignore`列表中。

`$PROJ/config.php`生成方式，可以直接复制`$PROJ/config.default.php`的一份拷贝，
然后将其中的设置值按照环境进行调整，不需要调整的依然使用默认值即可。

在调用时，应用定义好`P2R`常量，然后直接调用`$PROJ/config.default.php`即可，
此文件会自动寻找、调用`$PROJ/config.php`。

### 上传文件的管理

* 上传文件在项目程序之外创建单独的目录存储，一般此目录要放在web root之外。
* 不同类型的文件存储在不同的子目录下。
* 一般文件按照上传时间，分别存储在`YYYYMM/`子目录下，数量特别多的还可以创建`YYYYMM/DD/`或其他的日期层次。
* 在不能保证文件名只包含英文的情况下，上传文件名更改为`UUID`，此UUID及相关文件数据表中的主键，文件真实名称记录在数据库中，下载时再取用。


## 页面注释

除html模板文件以外，其它文件开头都增加一段页面注释，说明本文件功能、作用、使用说明、需要注意的问题等信息，
格式如下(括号内为说明)：

	/**
	 * 页面功能简述
	 *
	 * @package		项目名称
	 * @subpackage	子系统名称(可省略)
	 * @copyright	Copyright 2009, 版权所有方
	 * @author		编写人 <编写人邮箱@domain.com>
	 * @since		YYYY-MM-DD (开始编写时间)
	 * @version		$Id$
	 */

函数和类的注释也参照页面注释格式来写，并且还要说明函数或类的作用、用法、依赖外部类或环境、需要注意的问题等信息。

页面注释、类注释中，标记和内容之间用空格连接，这样代码和文档中的缩进是一致的。

注意中间以`*`开头的行行首是要留一个空格的，即竖向的`*`是对齐的。

而在函数、变量的注释中，可以用TAB来缩进，因为注释的内容变化比较大，不像上面那样内容和格式比较固定。
';

?>
