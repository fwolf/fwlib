..  -*- mode: rst -*-
..  -*- coding: utf-8 -*-


============================================================
文件格式和命名规则
============================================================



文件格式
==================================================


全部使用utf-8编码，不带BOM。回车格式为Unix(LF)。

一般每行不超过78个字符，手工折行。
中文可以在标点符号处折行。



通用文件命名规则
==================================================


-   一般文件名尽量使用小写，用 `-` 分割单词。
-   类定义文件可用类名称作为文件名，区分大小写。
-   语言或框架对文件名有约定的，从其约定。

具备相似功能的文件，尽可能采用一致的命名方法，比如相同的前缀。



Javascript 文件命名示例
==================================================


-   class.js
-   class-name.js
-   class-name.plugin-1.2.min.js
-   function.js
-   function-name.js
-   function.extended-1.2.js

Link:

-   `What is the javascript filename naming convention?
    <http://stackoverflow.com/questions/7273316>`_

-   `Google JavaScript Style Guide
    <http://google-styleguide.googlecode.com/svn/trunk/javascriptguide.xml>`_

-   `Principles of Writing Consistent, Idiomatic JavaScript
    <https://github.com/rwaldron/idiomatic.js/>`_

-   `JavaScript Style Guides And Beautifiers
    <http://addyosmani.com/blog/javascript-style-guides-and-beautifiers/>`_



常用在命名中的单词
==================================================


包括目录名。

+-----------+-------------------------------------+
|   单词    |   含义                              |
+===========+=====================================+
| css       | CSS 文件                            |
+-----------+-------------------------------------+
| doc       | 文档                                |
+-----------+-------------------------------------+
| img       | 图片或媒体文件                      |
+-----------+-------------------------------------+
| inc       | 包含文件                            |
+-----------+-------------------------------------+
| js        | Javascript 文件                     |
+-----------+-------------------------------------+
| tpl       | 模板                                |
+-----------+-------------------------------------+

