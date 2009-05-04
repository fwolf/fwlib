<?php
/**
 * 通用命名规则
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-05-04
 * @version		$Id$
 */

$ar_info = array();
$ar_info['title'] 		= '通用命名规则';
$ar_info['author']		= 'Fwolf';
$ar_info['authormail'] 	= 'fwolf.aide+fwolflib.doc@gmail.com';
$ar_info['keywords']	= 'doc, rules, naming';
$ar_info['description']	= '';

$ar_body = array();
$ar_body[] = '
## 基本规则

### 适用场合

* 程序中的变量名、函数名、类名，以及各种常用参数的命名
* 数据库中的库名、表名、字段名、索引名等
* 目录名和文件名

### 全部使用英文、数字和-、_

除非极其特殊的情况，在名称中只使用大、小写英文字母和数字组合，
并用“`-`”间隔不同的含义，同一含义不同的部分用“`_`”连接。

### 一般不使用复数形式，除非只有复数形式能表达确切含义的情况

即单词的复数形式不仅代表了数量的变化，还含有含义上的变化，而我们用到的正式这个变化以后的含义，
在这种情况下，才使用单词的复数形式来进行命名。


## 名称组合规则

### 基本组合规则

组合的目的：

* 变量、方法按字母排序，一目了然，便于查找。
* 相似的处理，代码距离比较近，方便编辑。
* 在对方法进行组合、拆分的时候易于处理。

#### 一般按照`对象-动作`的方式来组合，比如：

* bagy_anc-list bagy_anc是比选公告/对象，list是列表显示/动作。
* bagy_cc bagy是招标代理/对象，cc是比选/动作，按规则应该是用`-`连接，
但因为“招标代理比选”本身形成了一个新的名词，`cc`成为了名词中的一部分，
`bagy_cc`还是一个子系统的名称，所以在这里用`_`连接。

如果是类方法则采用首字母大写，无`_-`的格式。

#### 在特殊的情况下，采用`动作-对象`的方式来组合，一般用于主体数量少或没有，
无法用`主体-动作`形式来表达的情况，比如：

* sync-db_schema 同步数据库结构
* sync-db_data 同步数据库数据

### 特定用法中的组合规则

#### PHP中的action参数使用`[对象]-[动作]`的组合方式。

比如：

* anc-list 公告列表
* anc-detail 公告详细
* pub-list 公示列表

#### PHP中的类函数命名采用`[分类][动作][对象]`的组合方式

其中分类和动作在很多情况下都是组合在一起的。比如：

* FlowAncAprv 流程处理-公告审核
* GenContentHeader 生成内容-header部分
* GetListAnc 得到公告列表（数据）
* 视图的显示使用`ViewNameDisp`。(:THINK: 其实使用`DispViewName`也有合适的道理，
但规则必须统一，所以先固定为这种用法。)

#### 类的命名

MVC基类的名称直接在Module, View, Controler上扩展，后接项目简称，比如ModuleProj, ViewProj,
ControlerProj。

其余MVC类的命名为：

* Controler的扩展类比较少，所在文件都是各子系统下的`index.php`，Controler这个单词又比较长，
所以Controler扩展类命名格式为`IdxSubsysname`，其中`Subsysname`为子系统名称。
* 项目根目录下的Controler基类不在任何一个子系统中，所以固定类名称为`IdxRoot`。
* Module类的扩展类名称格式为`ModuleFunc`，其中`Func`为类的功能功能。
* View类的扩展类名称格式为`ViewFunc`，其中`Func`为类的功能描述。

';

?>
