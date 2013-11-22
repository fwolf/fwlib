# PHP编码规范

## 命名规范

### 类、类方法命名

名字的首字母使用大写;词首使用大写字母作为词与词之间的分隔，其他的字母均使用小写;不使用下划线(`_`)。

### 类属性命名

类属性名首字母使用一个小写字母标示该属性的数据类型，如：

* $sParam    //字符串类型
* $aParam    //数组类型

依此类推。

### 常量

常量应该总是全部使用大写字母命名，少数特别必要的情况下，可使用下划线来分隔单词；
PHP 的内建值 TRUE、FALSE 和NULL必须全部采用大写字母书写。

### 变量、对象、函数名

变量名一律为小写格式，函数名首字母大写，命名基本以英文全单词为准则，单词之间一般使用大写字母进行分割；

变量命名只能使用项目中有据可查的英文缩写方式，例如可以使用$data而不可使用$data1、$data2这样容易产生混淆的形式，
应当使用$data_article、$data_user这样一目了然容易理解的形式；

可以合理的对过长的命名进行缩写，例如$bio($biography)，$tpp($threads_per_page)，
前提是英文中有这样既有的缩写形式，或字母符合英文缩写规范；


## 书写规范

### PHP代码标记

任何时候都要使用<?php ?>定义你的php代码，而不要简单地使用<?  ?>,这样可以保证PEAR的兼容性，也利于跨平台的移植。

### 缩进

每个缩进的单位约定是一个TAB，不使用空格。
本缩进规范适用于PHP、JavaScript中的函数、类、逻辑结构、循环等。

### 注释

注释是对于那些容易忘记作用的代码添加简短的介绍性内容。
为实现夸平台兼容性，请使用 C 样式的注释`/* */`和标准 C++ 注释`//`。

程序开发中难免留下一些临时代码和调试代码，此类代码必须添加注释，以免日后遗忘。
所有临时性、调试性、试验性的代码，必须添加统一的注释标记`// :DEBUG:`并后跟完整的注释信息，
这样可以方便在程序发布和最终调试前批量检查程序中是否还存在有疑问的代码。例如：

	$num = 1;
	$flag = TRUE; // :DEBUG: 这里不能确定是否需要对$flag进行赋值
	if(empty($flag)) {
		//Statements
	}

其他注释标记：

- :TEMP: 临时做法
- :TEST: 测试
- :THINK: 还需要进一步思考或探讨
- :TODO: 还有未完成的工作

### 控制结构

这里所说的控制结构包括: if for while switch foreach等。对于控制结构，在关键字（如if for ..）后面要空一个格，
然后再跟控制的圆括号，这样，不至于和函数调用混淆，此外，你应该尽量完整的使用花括号{}，即使从语法上来说是可选的。
这样可以防止你以后需添加新的代码行时产生逻辑上的疑惑或者错误。

switch结构中，通常当一个case块处理后，将跳过之后的case块处理，因此大多数情况下需要添加break。
break的位置视程序逻辑，与case同在一行，或新起一行均可，但同一switch体中，break的位置格式应当保持一致。
以下是符合上述规范的例子：

	if ($condition) {
		switch ($var) {
		case 1:
			echo "var is 1";
			break;
		case 2:
			echo "var is 2";
			break;
		default:
			echo "var is neither 1 or 2";
			break;
		}
	} else {
		statement;
	}


### 运算符、小括号、空格、关键词和函数

每个运算符与两边参与运算的值或表达式中间要有一个空格；

左括号"(" 应和函数名紧贴在一起，除此以外应当使用空格将"("同前面内容分开；
右括号")"除后面是")"或者"."以外，其他一律用空格隔开它们；

除字符串中特意需要，一般情况下，在程序以及HTML中不出现两个连续的空格；

任何情况下，PHP程序中不能出现空白的带有TAB或空格的行，即：这类空白行应当不包含任何TAB或空格。
同时，任何程序行尾也不能出现多余的TAB或空格。多数编辑器具有自动去除行尾空格的功能。
Greay的`Files -> Strip trailing spaces and tabs`选项能实现此功能。

每段较大的程序体，上、下应当加入空白行，两个程序块之间只使用1个空行，谨慎使用多行。
程序块划分尽量合理，过大或者过小的分割都会影响他人对代码的阅读和理解。
一般可以以较大函数定义、逻辑结构、功能结构来进行划分。少于15行的程序块，可不加上下空白行；

说明或显示部分中，内容如含有中文、数字、英文单词混杂，应当在数字或者英文单词的前后加入空格。


根据上述原则，以下举例说明正确的书写格式：

	$result = (($a + 1) * 3 / 2 + $num)) . "Test";

	if ($flag) {
		// Statements
		// More than 15 lines
	}

	Showmessage("");


## 函数

### 函数定义

函数定义中的左小括号，与函数名紧挨，中间无需空格；
开始的左大括号一般在函数定义行的行尾。

具有默认值的参数应该位于参数列表的后面；（系统默认必须如此）并且总是尽量返回有意义的函数值。

函数调用与定义的时候参数与参数之间加入一个空格，
必须仔细检查并切实杜绝函数起始缩进位置与结束缩进位置不同的现象。

例如，符合标准的定义：

	function Authcode($string, $operation, $key = "") {
		if ($flag) {
			// Statement
		}
		// Other statment
	}

### 函数调用

对于函数调用，函数名和左括号( 之间不应该有空格，对于函数参数，在分隔的逗号和下一个参数之间要有相同的空格分离，即用`, `间隔；
最后一个参数和右括号之间不要有空格。下面是一个标准的函数调用；

	$result = foo($param1, $param2, $param3);

不规范的写法：

	$result=foo ($param1,$param2,$param3);
	$result=foo( $param1,$param2, $param3 );


此外，如果要将函数的返回结果赋值，那么在等号和所赋值的变量之间要有空格，同时，如果是一系列相关的赋值语句，你添加适当的空格，使它们对齐，就象这样：

	$result1 = $foo($param1, $param2, $param3);
	$var2    = $foo($param3);
	$var3    = $foo($param4, $param5);

## 其他

无论什么时候，当需要无条件包含进一个class文件，使用 requre_once;
当你需要条件包含进一个class文件，你必须使用 include_once;
这样可以保证你要包含的文件只会包含一次，并且这2个语句共用同一个文件列表，所以你无须担心二者会混淆，
一旦 require_once 包含了一个文件，include_once不会再重复包含相同的文件，反之亦然。

常量定义与包含文件放在程序块的头部，单次输出使用require_once()，多次输出使用require();