<?php
/**
 * Db design rules
 *
 * @package		fwolflib
 * @subpackage	doc
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.doc@gmail.com>
 * @since		2009-04-20
 * @version		$Id$
 */

$ar_info = array();
$ar_info['title'] 		= 'Database design rules';
$ar_info['author']		= 'Fwolf';
$ar_info['authormail'] 	= 'fwolf.aide+fwolflib.doc@gmail.com';
$ar_info['keywords']	= 'doc, rules, db, database';
$ar_info['description']	= '数据库的设计规范';

$ar_body = array();
$ar_body[] = '
## 语法规则

### 命名规则

* 使用小写英文字母、0-9、_，长度一般不超过30，不以数字开头。
* 使用`_`来间隔名称中的不同单词。
* 外键名称格式：`fk_表名_序号(从1开始)`，如果再带关联表名和字段就太长了。
* 索引名称格式：`idx_表名_字段名(字段列表用_隔开)`开头。
* 视图名称均使用`v_`开头。
* SQL语句中的关键字全部使用大写。
* 尽可能使用time替代date。

注：Mysql的标识符名称中不能使用`-`，并且长度限制为64。


### 注释格式

* 优先使用/* */块注释
* 单行注释使用`-- `（注意后面有一空格），不使用“#”或“//”开头的单行注释


## 数据模型设计规则

### 字段的设计

#### 数据的标准应该尽量符合Third Normal Form（3NF）

#### 字段的数据类型的设计

* 数据字段的长度限制应当略大于实际的需要。
* 一项内容在不同表中的字段，数据类型要保持一致，比如某字段在一个表里是整数类型，那在另一个表里就不要使用字符型。
* 字符串除长度固定的情况使用CHAR以外，其它的一律归为VARCHAR 20、50、255三个长度，超出255则使用TEXT类型。
* 时间均采用DATETIME类型。

##### 数值类型容量计算(Mysql)

* TINYINT，默认TINYINT(4)，范围为-128~127，超出范围使用SMALLINT
* SMALLINT，范围为-32768~32767，超出范围使用INTEGER
* INTEGER，默认INT(11)，范围为-2147483648~2147483647，超出范围使用FLOAT
* 整数类型一般就不再带标识长度的(?)了，就用默认长度即可。
* FLOAT，范围为-3.402823466E+38~-1.175494351E-38
* DOUBLE，范围为-1.7976931348623157E+308~-2.2250738585072014E-308
* DECIMAL/NUMERIC，将数字以字符串形式保存的，他的值的每一位 (包括小数点) 占一个字节的存储空间，
因此这种类型耗费空间比较大。但是它的一个突出的优点是小数的位数固定，在运算中不会“失真”，比较精确，
所以比较适合用于“价格”、“金额”这样对精度要求不高但准确度要求非常高的字段。
	
##### 字符型字段的容量计算(Mysql)

* CHAR，限制最多255，一般用在长度固定的情况。
* VARCHAR，限制最多255，一般用在长度不固定的情况，超出范围使用TEXT。
* TEXT，限制最多65535，超出范围使用MEDIUMTEXT。
* MEDIUMTEXT，限制最多16777215。

如果数据库使用utf8字符集，每两个中文会占用3个字节，所以在计算字段容量的时候要注意，
实际能够容纳的中文字个数为字段长度/3（字段长度 * 2/3 /2）。

#### 在不影响使用的基础上，尽量使用整数类型，目的：查询加速，节约空间。

#### 开关型标记字段0/1含义的确定

统一标记数值的字典规则，用0代表初始状态，并定为默认值，大于0的整数代表系统正常处理的不同状态，
小于0的整数代表系统不正常/出错情况下的状态码，一般用到小于0的整数标记的情况应该比较少。

同时，按照c语言和其他大部分语言的规则，0代表假，大于0的值比如1代表真，这就要和字段含义结合起来了，
在设计上要求有些技巧。

比如一个标明有效的标记，记录创建时应该是有效的，而默认值0按照“假”的逻辑，结合字段含义，应该是“不有效”，就矛盾了。

所以，这个例子中的标记应当赋予“无效”的含义，默认值0代表“不无效”，就一致了。

结论：开关型标记（只有0/1两种状态）尤其要注意含义的设计，规则统一以后不容易出错。

开关型标记使用TINYINT类型，使用其他类型一是省不去太多空间，二是为了扩充方便。

#### **所有数值型字段必需设置默认值**

### 键

#### 主键规则

* 一般保存用户产生内容的数据表，使用UUID作为主键。
* 确实不会发生外联关系、且并发不会很多、数据却很多的情况下，可以考虑设置自增主键。
* 字典表使用其编号为主键，一般是VARCHAR类型。因为字典表的编码往往是具备一些含义的，并且基本不会变动。
同时字典表经常会作为检索条件和外键，UUID或自增主键的值不可预测、没有规律，会影响效能。

* 避免使用复合键（原因？）。
* 外键总是关联唯一的键字段。

一般情况下如果主表和附属表是一对一的关系，那么辅助表可以不用再设立单独的主键，而是使用主表的主键作为自己的主键。
但本项目中不打算这样用，因为一对一的关系存在改变为一对多关系的可能，所以辅助表也要单设主键。

#### 外键规则

一般应本着“有用数据不删除”的原则，即外键的`ON DELETE`应当设为`NO ACTION`，
而`ON UPDATE`可设为`CASCADE`来确保数据一致。

只有在确定主数据需要从库中抹去的情况才可以使用`ON DELETE CASCADE`，比如：

1. 比选公告和比选文件，过期且未审核的比选公告的确需要从库里删除，
同时还需要删除用户上传的比选文件（在着之前还要手工删除文件系统上的对应文件），
这种情况下可以使用外键规则`ON DELETE CASCADE`。
2. 地区会在很多表中作为外键，如果一个地区不再使用了，并不代表所有和这个地区关联的数据都要删除，
相反，他们要留在库中存档备查的，所以外键规则要设为`ON DELETE NO ACTION`。
同时，地区库中不应删除地区，而是将地区的flag标记为0/无管理部门。
好处：在地区编码按照国家行政区划编码调整时，能够自动变更所有的相关数据
(:THINK: 相关变动数据会很多，数据库能很好的处理么？)。

最后，尽可能不用外键，除非应用要求比较苛刻，因为会给调试带来困难。
可以在应用逻辑里用代码实现外键约束的效果，同时和尽可能不使用数据库高级特性的原则吻合。

### 索引

* 唯一键都通过创建unique索引方式定义，建为单列索引。
* 应用组合查询条件的，建立组合索引；多种组合查询条件的，可考虑建多个组合索引。
* 多个单列索引不能替代组合索引，它们在组合查询的时候几乎没有作用，因为mysql只会使用一个索引。
* 键值重复太多的不要创建索引。
* 组合查询时，应当按照组合索引的字段顺序来查询，最左匹配原则。


### 高级数据库对象

* 可以使用视图。
* 尽可能不使用存储过程。
* 禁止使用触发器。


## 表设计备注

### 上传文件信息表

首先由于文件本身没有独立的含义，一般都是跟随其他信息共同使用；
其次文件用在多个地方、具有多重作用的情况也基本没有，所以将每一类的文件设计为单独的表存储。

文件表中不仅要存储原始文件名，还要存储相对于此分类专用上传文件保存目录的路径，即以日期开头的路径名称。


## 数据库高级特性

### 不使用的特性

* UPDATE中的ON DUPLICATE KEY UPDATE，原因：程序应当判断当前的数据应如何处理，且结果状态不好判断，
数据库复制好像也传不过去（官方文档上别人说的）。


## 避免在命名中使用的保留字(Mysql)

但可以和其它词组合形成对象名称。

	action			add				aggregate		all				alter
	after			and				as				asc				avg
	avg_row_length	auto_increment
	
	between			bigint			bit				binary			blob
	bool			both			by
	
	cascade			case			char			character		change
	check			checksum		column			columns			comment
	constraint		create			cross			current_date	current_time
	current_timestamp
	
	data			database		databases		date			datetime
	day				day_hour		day_minute		day_second		dayofmonth
	dayofweek		dayofyear		dec				decimal			default
	delayed			delay_key_write	delete			desc			describe
	distinct		distinctrow		double			drop
	
	end				else			escape			escaped			enclosed
	enum			explain			exists
	
	fields			file			first			float			float4
	float8			flush			foreign			from			for
	full			function
	
	global			grant			grants			group
	
	having			heap			high_priority	hour			hour_minute
	hour_second		hosts
	
	identified		ignore			in				index			infile
	inner			insert			insert_id		int				integer
	interval		int1			int2			int3			int4
	int8			into			if				is				isam

	join
	
	key				keys			kill
	
	last_insert_id	leading			left			length			like
	lines			limit			load			local			lock
	logs			long			longblob		longtext		low_priority

	max				max_rows		match			mediumblob		mediumtext
	mediumint		middleint		min_rows		minute			minute_second
	modify			month			monthname		myisam
	
	natural			numeric			no				not				null

	on				optimize		option			optionally		or
	order			outer			outfile

	pack_keys		partial			password		precision		primary
	procedure		process			processlist		privileges

	read			real			references		reload			regexp
	rename			replace			restrict		returns			revoke
	rlike			row				rows

	second			select			set				show			shutdown
	smallint		soname			sql_big_tables	sql_big_selects	sql_low_priority_updates
	sql_log_off		sql_log_update	sql_select_limit				sql_small_result	
	sql_big_result	sql_warnings	straight_join	starting		status
	string

	table			tables			temporary		terminated		text
	then			time			timestamp		tinyblob		tinytext
	tinyint			trailing		to				type

	use				using			unique			unlock			unsigned
	update			usage
	
	values			varchar			variables		varying			varbinary

	with			write			when			where

	year			year_month
	
	zerofill		zone

';

?>