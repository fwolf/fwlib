<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf, fwolf.aide@gmail.com
 * @since		2006-07-08
 * @version		$Id$
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * 向数据库中写入数据
 * 根据指定的主键或联合主键自动判断是insert还是update
 * 当然主键必须有值包含在数据中
 *
 * @param	object	$db		ADOdb数据库连接对象
 * @param	string	$tbl	写入数据表名
 * @param	array	$data	要写入的数据，数组array(column=>value,...)形式
 * @param	mixed	$pkey	主键，可以是字符串或数组（也可以是定位数据的其他条件，但要求键和值必须包含在data数组中
 * @return	mixed			出错返回错误信息，成功返回1(即处理的行数)
 */
function DbWrite($db, $tbl, $data, $pkey)
{
	//对要写入数据库的内容进行处理，主要是根据字段类型添加引号
	//:TODO:改用缓存机制
	$col = $db->MetaColumns($tbl);
	foreach ($data as $key => $val)
	{
		if (isset($col[strtoupper($key)]))
		{
			//根据字段类型添加引号
			if (!in_array($col[strtoupper($key)]->type, array('int', 'integer', 'tinyint', 'decimal', 'bolean', 'numeric')))
				$data[$key] = "'" . addslashes($val) . "'";
		}
		else
		{
			return("Column $key is not found in db schema.");
		}
	}
	//检查要写入的数据是否存在 insert or update?
	$sql = "select count(1) as c from $tbl where 1=1 ";
	if (is_array($pkey))
	{
		//多个值的定位
		//$s_pkey will be used again when actually write to db
		$s_pkey = '';
		foreach ($pkey as $key)
		{
			//检查键值是否被指定，如果没有被指定，中止操作
			if (isset($data[$key]))
				$s_pkey .= " and $key = $data[$key] ";
			else
			{
				return("Key $key has not assigned a value.");
			}
		}
		$sql .= $s_pkey;
	}
	else
	{
		//单个键值
		$s_pkey = " and $pkey = $data[$pkey] ";
		$sql .= $s_pkey;
	}
	$rs = $db->Execute($sql);
	$i = $rs->fields['c'];
	if (0 == $i)
	{
		//its insert
		$sql = "insert into $tbl (";
		//keys
		foreach ($data as $key=>$val)
		{
			$sql .= "$key, ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= ") values (";
		//values
		foreach ($data as $key=>$val)
		{
			$sql .= "$val, ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= ")";
	}
	elseif (1 == $i)
	{
		//its update
		$sql = "update $tbl set ";
		foreach ($data as $key=>$val)
		{
			$sql .= "$key = $val, ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2);
		$sql .= " where 1=1 ";
		//use pkey to locate data
		$sql .= $s_pkey;
	}
	else
	{
		//got too many match rows
		return("Got >1 rows by given pkey, which to update ?");
	}
	//finally, write to database
	//$db->debug = true;
	$rs = $db->Execute($sql);
	$i = $db->ErrorNo();
	if (0 == $i)
	{
		//no error
		//echo("1 row writed to database, no error.<br />\n");
		return 1;
	}
	else
	{
		//error occur
		return($db->ErrorMsg());
	}
} // end of function DbWrite

?>
