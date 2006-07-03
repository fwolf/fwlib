/**
 * 数据效验JS函数集
 * 用法：
 *   包含common.js
 *   包含此文件
 * <script language="JavaScript" type="text/JavaScript" src="validate.js"></script>
 *   在页面中声明
 * <script language="JavaScript" type="text/JavaScript">
 * var errfound;
 * function Validate()
 * {
 * 	var Submitted=false;
 * 	//防止表单被多次提交
 * 	if (Submitted==true) {alert("请不要重复提交！");return false;}
 * 	errfound= false;
 * 	if (!errfound) ValidLength('yhmc',"用户名称",3); 
 * 	......
 * 	if (!errfound) {Submitted=true;}
 * 	return !errfound;
 * }
 * 然后：
 * <form action="***.asp" method="post" name="form1" onSubmit="return Validate();">
 *
 * @package    MaGod
 * @copyright  Copyright 2003-2004, Fwolf
 * @author     Fwolf <fwolf001@tom.com>
 * @since      2004-3-1 17:08:06
 * @access     public
 * @version    $Id$
 */

//<script language="JavaScript">

//检查用户的各种输入错误
//检查字符串最小长度 ValidLength(item,col,len)
//检查字符串最大长度 ValidLengthMax(item,col,len)   
//检查特殊字符 ValidSpecchar(item,col)  
//检查EMAIL的合法性 ValidEmail(item,col)     
//检查数字的合法性 ValidNum(item,col)  
//检查实数的合法性 ValidNumD(item,col)  
//检查日期的合法性 ValidDate(item,col)  
//检查是否合法IP地址 ValidIp(item,col)

  
/**
 * 显示出错信息
 * @param   string  name    出错对象名称
 * @param   string  title   出错对象描述
 * @param   string  text    错误信息
 * @access  private
 */
function ValidateFail(name, title, text)
{
    //abort if we already found an error
    if (errfound) return;
    alert(title+" 输入错误！\n\n"+text);
    GetElement(document, name).select();
    GetElement(document, name).focus();
    errfound = true;
} // end of function ValidateFail


/**
 * 效验是否合法的IP地址
 * @param   string  name    被检查对象的名称
 * @param   string  title	被检查对象的描述，如果为空，说明出错后不弹出提示信息
 * @access  public
 * @return  boolean
 */
function ValidateIp(name, title)
{
	//检查特殊字符和节数是否正确
	var pattern = /^(\d{1,3}\.){3}\d{1,3}$/;
	if (!pattern.exec(GetData(name, 'value')))
	{
		if (0<title.length)
		{
			ValidateFail(name, title, 'IP地址必须输入完全，并且不能包含除数字以外的其它字符（连接符“.”除外）\n正确的格式如：12.34.56.78');
			alert(title);
		}
		return false;
	}
	//检查每一节不能大于255
	ar = GetData(name, 'value').split('.');
	if (1>ar[0] || 254<ar[0])
	{
		if (0<title.length)
		{
			ValidateFail(name, title, 'IP地址的第一段，数值必须是1－254之间');
		}
		return false;
	}
	for (i=1;i<ar.length;i++)
	{
		if (0>ar[i] || 255<ar[i])
		{
			if (0<title.length)
			{
				ValidateFail(name, title, 'IP地址的第二、三、四段，数值必须是0－255之间');
			}
			return false;
		}
	}
	return true;
} // end of function ValidateIp


//========================== Old Functions ========================


function ValidLength(item,col,len)   // 检查字符串最小长度
 {
  if (item.value.length < len) error(item,col,"您输入的长度不够，请输入至少 "+len+" 位");
  }

function ValidLengthMax(item,col,len)   // 检查字符串最大长度
 {
  if (item.value.length > len) error(item,col,"您输入的字符太多了，至多能输入 "+len+" 个字符");
  }

function ValidSpecchar(item,col)  //检查特殊字符
 {
  //当前非法字符为：'（半角的单引号）
  if (item.value.length>0)
   {
    if (item.value.indexOf("'") != -1) error(item,col,"您输入了非法字符\n输入中不能含有'（半角的单引号）");
    }
  }

function ValidEmail(item,col)     // 检查EMAIL的合法性
 {
  if (item.value.length>0)
   {
    if (item.value.length < 6) error(item,col,"您输入了非法的EMAIL地址");
    if ((item.value.indexOf('@',0) == -1) || (item.value.indexOf('.',0) == -1) || (item.value.indexOf("'",0) != -1)) error(item,col,"您输入了非法的EMAIL地址\n或者EMAIL地址中含有非法字符");
    }
  }
  
function ValidNum(item,col)  //检查数字的合法性
 {
  if (item.value.length>0)
   {
    for (var i=0;i<item.value.length;i++)
     {
      var ch=item.value.substring(i,i+1);
      if ("0">ch || ch>"9") error(item,col,"您输入了非法的数字");
      }
    }
  }
  
function ValidNumD(item,col)  //检查实数的合法性
 {
  if (item.value.length>0)
   {
    var k=0;
    for (var i=0;i<item.value.length;i++)
     {
      var ch=item.value.substring(i,i+1);
      if (ch==".") 
       {
        k++;
        }
      else
       {
        if (("0">ch || ch>"9") && ch!=".") 
         {
          error(item,col,"您输入了非法的字符");
          return;
          }
        }
      }
    if (k>1) error(item,col,"您输入了 "+k+" 个小数点");
    if (k==1)
      if (item.value.substring(item.value.length-3,item.value.length-2) != ".")
        if (item.value.substring(item.value.length-2,item.value.length-1) != ".") 
          if (item.value.substring(item.value.length-1,item.value.length) != ".") error(item,col,"最多输入两位小数");
    }
  }

function ValidDate(item,col)  //检查日期的合法性
 {
  if (item.value.length>0)
   {
    var year=0,month=0,day=0,n=0,i;
    var yn=0,mn=0,dn=0;
    for (i=0;i<item.value.length;i++)
     {
      var ch=item.value.substring(i,i+1);
      if (ch=="-") 
       {
        n++;
        }
      else
       {
        if ("0"<=ch && ch<="9")
         {
          if (n==0) {year=year*10+parseInt(ch);yn++;}
          if (n==1) {month=month*10+parseInt(ch);mn++;}
          if (n==2) {day=day*10+parseInt(ch);dn++;}
          }
        else 
         {
          error(item,col,"请不要输入无关字符");
          }
        }
      }
    if (n!=2) error(item,col,"您输入了非法的日期\n\n正确的日期格式为YYYY-MM-DD，如2000-01-01为2000年1月1日\n年份必须输入4位");
    if (year<1800 || year>3000 || yn>4 || yn==0) error(item,col,"请输入1800～3000之间的年份");
    if (month>12 || month<1 || mn>2 || mn==0) error(item,col,"请输入1～12之间的月份");
    if (day>31 || day<1 || dn>2 || dn==0) error(item,col,"请输入1～31之间的日");
    }
  }
  
function ValidIp(item,col)  //检查是否合法IP地址
 {
  var i,n,st,j;
  n=0;j=0;
  for (i=0;i<item.value.length;i++)
   {
    st=item.value.substr(i,1);
    if (st==".") 
     {
      j++;
      if (j==1 && n==0) error(item,col,"IP输入不完整");
      if (0>n || n>255) error(item,col,"IP只能是0－255的数字");
      n=0;
      }
    else
     {
      if ("0">st || st>"9") error(item,col,"您输入了非法的数字");
      n=n*10+parseInt(st);
      }
    }
  if (j!=3) error(item,col,"IP输入不完整");
  if (0>n || n>255) error(item,col,"IP只能是0－255的数字");
  if (n==0) error(item,col,"IP输入不完整");
  }
  

function error(elem,col,text)  //显示出错信息
 {
  //abort if we already found an error
  if (errfound) return;
  alert(col+" 输入错误！\n\n"+text);
  elem.select();
  elem.focus();
  errfound = true;
  }
//</script>