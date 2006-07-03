<?php
/**
* @package      MaGod
* @copyright    Copyright 2003, Fwolf
* @author       Fwolf <fwolf001@tom.com>
*/

require_once('MaGod/MaGod.php');

/**
* SQL语句生成类
* 虽然用ADOdb能够完成大部分工作，但这个类是作为生成SQL的辅助工具用的
*
* @package    MaGod
* @copyright  Copyright 2003, Fwolf
* @author     Fwolf <fwolf001@tom.com>
* @since      2003-08-25 09:48:31
* @access     public
* @version    $Id$
*/

class SqlGenerator
{
    var $classname = "SqlGenerator";

    /**
	 * SQL语句中的SELECT部分
	 * @access	private
	 */
	var $mSelect = array();

    /**
	 * SQL语句中的INSERT部分
	 * @access	private
	 */
    var $mInsert;

    /**
	 * SQL语句中的UPDATE部分
	 * @access	private
	 */
	var $mUpdate;

    /**
	 * SQL语句中的DELETE部分
	 * @access	private
	 */
    var $mDelete;

    /**
	 * SQL语句中的INTO部分
	 * @access	private
	 */
    var $mInto;

    /**
     * 数据库查询时是否过滤掉重复记录（SQL的DISTINCT关键字）
	 * 如果不为空，将在SELECT子句中使用，如select count(distinct $mSelectDistinct) ...，select distinct col1, col2 ...。
     * @var string
     * @access  public
     */
    var $mSelectDistinct = '';

    /**
	 * SQL语句中的SET部分
	 * @access	private
	 */
    var $mSet = array();

    /**
	 * SQL语句中的FROM部分
	 * @access	private
	 */
    var $mFrom = array();

    /**
	 * SQL语句中的WHERE部分
	 * @access	private
	 */
    var $mWhere = array();

	/**
	 * SQL语句中附加的WHERE部分，将会完整的添加在WHERE子句部分
	 * @access	private
	 */
	var $mWhereAdd = '';

    /**
	 * SQL语句中的GROUP BY部分
	 * @access	private
	 */
    var $mGroupby;

    /**
	 * SQL语句中的HAVING部分
	 * @access	private
	 */
    var $mHaving;

    /**
	 * SQL语句中的ORDER BY部分
	 * @access	private
	 */
    var $mOrderby = array();

    /**
	 * SQL语句中的LIMIT部分
	 * @access	private
	 */
    var $mLimit;


	/**
	 * 重置已经设定的参数
	 *
	 * @access	public
	 * @param	string	$part	重设哪一部分
	 * @see		gsql()
	 */
	function Clear($part = '')
	{
	    if (empty($part) )
	    {
	        $this->gsql('reset');
	    }
		elseif ('select' == $part)
		{
		    $this->mSelect = array();
		}
		elseif ('update' == $part)
		{
            $this->mUpdate = ''; 
		}
		elseif ('insert' == $part)
		{
            $this->mInsert = ''; 
		}
		elseif ('delete' == $part)
		{
            $this->mDelete = ''; 
		}
		elseif ('into' == $part)
		{
            $this->mInto = '';   
		}
		elseif ('set' == $part)
		{
            $this->mSet = array();
		}
		elseif ('from' == $part)
		{
            $this->mFrom = array();   
		}
		elseif ('where' == $part)
		{
            $this->mWhere = array();  
		}
		elseif ('groupby' == $part)
		{
            $this->mGroupby = '';
		}
		elseif ('having' == $part)
		{
            $this->mHaving = ''; 
		}
		elseif ('orderby' == $part)
		{
            $this->mOrderby = array();
		}
		elseif ('limit' == $part)
		{
            $this->mLimit = '';  
		}
	} // end of function Clear


	/**
	 * 设置DELETE SQL的目标表
	 * 本来应该用Delete的，但Delete与系统关键字可能冲突，因此使用简写。
	 * @param	string	$tablename
	 * @access	public
	 */
	function Del($tablename)
	{
		$this->mDelete = $tablename;
	} // end of function Delete


	/**
	 * 设置FROM SQL的目标表
	 * @param	string	$tablename
	 * @access	public
	 */
	function From($tablename)
	{
	    $this->mFrom = array($tablename);
	} // end of function From


	/**
	 * 增设FROM SQL的目标表
	 * @param	string	$tablename
	 * @access	public
	 */
	function FromAdd($tablename)
	{
	    array_push($this->mFrom, $tablename);
	} // end of function FromAdd


	/**
	 * 设置GROUP BY SQL的内容
	 * @param	string	$groupby
	 * @access	public
	 */
	function Groupby($groupby)
	{
	    $this->mGroupby = $groupby;
	} // end of function Groupby


	/**
	 * 设置INSERT SQL的目标表
	 * @param	string	$tablename
	 * @access	public
	 */
	function Insert($tablename)
	{
	    $this->mInsert = $tablename;
	} // end of function Insert


	/**
	 * 设置LIMIT SQL的内容
	 * @param	string	$limit
	 * @access	public
	 */
	function Limit($limit)
	{
	    $this->mLimit = $limit;
	} // end of function Limit


	/**
	 * 设置ORDER BY SQL的内容
	 * @param	string	$orderby
	 * @access	public
	 */
	function Orderby($orderby)
	{
	    $this->mOrderby = array($orderby);
	} // end of function Orderby


	/**
	 * 增设ORDER BY SQL的内容
	 * @param	string	$orderby
	 * @access	public
	 */
	function OrderbyAdd($orderby)
	{
	    array_push($this->mOrderby, $orderby);
	} // end of function OrderbyAdd


	/**
	 * 设置SELECT SQL的目标字段
	 * @param	string	$column
	 * @access	public
	 */
	function Select($column)
	{
	    $this->mSelect = array($column);
	} // end of function Select


	/**
	 * 增设SELECT SQL的目标字段
	 * @param	string	$column
	 * @access	public
	 */
	function SelectAdd($column)
	{
	    array_push($this->mSelect, $column);
	} // end of function SelectAdd


	/**
	 * 设置SET SQL的值对
	 * @param	string	$val1
	 * @param	string	$val2
	 * @access	public
	 */
	function Set($val1, $val2)
	{
	    $this->mSet = array($val1 => $val2);
	} // end of function Set


	/**
	 * 增设SET SQL的值对
	 * @param	string	$val1
	 * @param	string	$val2
	 * @access	public
	 */
	function SetAdd($val1, $val2)
	{
	    $this->mSet[$val1] = $val2;
	} // end of function SetAdd


	/**
	 * 生成最终的SQL语句
	 * @param	string	$mode	是哪一种SQL语句
	 * @access	public
	 */
	function Sql($mode = 'auto')
	{
		if (('auto' == $mode) || empty($mode))
		{
			//自动判断
			if (count($this->mSelect) > 0)
			{
				return($this->Sql('select'));
			}
			elseif (strlen($this->mInsert) > 0)
			{
				return($this->Sql('insert'));
			}
			elseif (strlen($this->mUpdate) > 0)
			{
				return($this->Sql('update'));
			}
			elseif (strlen($this->mDelete) > 0)
			{
				return($this->Sql('delete'));
			}
		}
		elseif ('select' == $mode)
		{
			//SELECT语句
			$sql = '';
			if (count($this->mSelect) > 0)
			{
				$sql .= 'select ';
                if (!empty($this->mSelectDistinct))
                {
                    $sql .= ' distinct ';
                }
				$s_t = '';
				foreach ($this->mSelect as $key=>$val)
				{
					$s_t .= ', ' . $val;
				}
				$sql .= substr($s_t, 2);
			}
			if (count($this->mFrom) > 0)
			{
				$sql .= ' from ';
				$s_t = '';
				foreach ($this->mFrom as $key=>$val)
				{
					$s_t .= ', ' . $val;
				}
				$sql .= substr($s_t, 2);
			}
			if (count($this->mWhere) > 0)
			{
				$sql .= ' where ';
				$s_t = '';
				foreach ($this->mWhere as $key=>$val)
				{
					$s_t .= ' and ' . $val;
				}
				$sql .= substr($s_t, 5);
				$sql .= $this->mWhereAdd;
			}
			elseif ( !empty($this->mWhereAdd) )
			{
				$sql .= ' where ' . $this->mWhereAdd;
			}
			if (strlen($this->mGroupby) > 0)
			{
				$sql .= ' group by ' . $this->mGroupby . ' ';
			}
			if (strlen($this->mHaving) > 0)
			{
				$sql .= ' having ' . $this->mHaving . ' ';
			}
			if (count($this->mOrderby) > 0)
			{
				$sql .= ' order by ';
				$s_t = '';
				foreach ($this->mOrderby as $key=>$val)
				{
					$s_t .= ', ' . $val;
				}
				$sql .= substr($s_t, 2);
			}
			if (strlen($this->mLimit) > 0)
			{
				$sql .= ' limit ' . $this->mLimit . ' ';
			}
			return($sql);
		}
		elseif ('insert' == $mode)
		{
			//INSERT语句
			$sql = '';
			if (strlen($this->mInsert) > 0)
			{
				$sql .= 'insert into ' . $this->mInsert . ' ';
			}
//			if (count($this->mSet) > 0)
//			{
//				$sql .= ' set ';
//				$s_t = '';
//				foreach ($this->mSet as $key=>$val)
//				{
//					$s_t .= ', ' . $key . ' = ' . $val . ' ';
//				}
//				$sql .= substr($s_t, 2);
//			}
            $col = '';
            $values = '';
			if (count($this->mSet) > 0)
			{
				foreach ($this->mSet as $key=>$val)
				{
                    $col .= ', ' . $key;
                    $values .= ', ' . $val;
				}
				$col = substr($col, 2);
                $values = substr($values, 2);
			}
            $sql .= '(' . $col . ') VALUES (' . $values . ')';
			return($sql);
		}
		elseif ('update' == $mode)
		{
			//UPDATE语句
			$sql = '';
			if (strlen($this->mUpdate) > 0)
			{
				$sql .= 'update ' . $this->mUpdate . ' ';
			}
			if (count($this->mSet) > 0)
			{
				$sql .= ' set ';
				$s_t = '';
				foreach ($this->mSet as $key=>$val)
				{
					$s_t .= ', ' . $key . ' = ' . $val . ' ';
				}
				$sql .= substr($s_t, 2);
			}
			if (count($this->mWhere) > 0)
			{
				$sql .= ' where ';
				$s_t = '';
				foreach ($this->mWhere as $key=>$val)
				{
					$s_t .= ' and ' . $val;
				}
				$sql .= substr($s_t, 5);
				$sql .= $this->mWhereAdd;
			}
			elseif ( !empty($this->mWhereAdd) )
			{
				$sql .= ' where ' . $this->mWhereAdd;
			}
			return($sql);
		}
		elseif ('delete' == $mode)
		{
			//DELETE语句
			$sql = '';
			if (strlen($this->mDelete) > 0)
			{
				$sql .= 'delete from ' . $this->mDelete . ' ';      //mysql use "delete from"
			}
			if (count($this->mWhere) > 0)
			{
				$sql .= ' where ';
				$s_t = '';
				foreach ($this->mWhere as $key=>$val)
				{
					$s_t .= ' and ' . $val;
				}
				$sql .= substr($s_t, 5);
				$sql .= $this->mWhereAdd;
			}
			elseif ( !empty($this->mWhereAdd) )
			{
				$sql .= ' where ' . $this->mWhereAdd;
			}
			return($sql);
		}
	} // end of function Sql


	/**
	 * 设置UPDATE SQL的目标表
	 * @param	string	$tablename
	 * @access	public
	 */
	function Update($tablename)
	{
	    $this->mUpdate = $tablename;
	} // end of function Update
	

	/**
	 * 设置WHERE SQL的内容
	 * @param	string	$where
	 * @access	public
	 */
	function Where($where)
	{
	    $this->mWhere = array($where);
	} // end of function Where


	/**
	 * 增设WHERE SQL的内容
	 * @param	string	$where
	 * @access	public
	 */
	function WhereAdd($where)
	{
	    array_push($this->mWhere, $where);
	} // end of function WhereAdd


	/**
	 * SQL语句生成函数
	 * @access	public
	 * @param	string	$part	指定SQL的那一部分或者生成整个SQL语句
	 * @param	string	$value	指定部分的值
	 * @param	string	$value2	指定部分值2，主要用于SET等SQL子句
	 * @return	string
	 */
    function gsql($part = 'sqlstr', $value = 'auto', $value2 = '')
    {
        if ($part == '')
        {
            return(0);
        }
        elseif ($part == 'reset')
        {
            //所有变量复原，在每一次使用前最好使用一次
            $this->mSelect = array(); 
            $this->mInsert = ''; 
            $this->mUpdate = ''; 
            $this->mDelete = ''; 
            $this->mInto = '';   
            $this->mSet = array();
            $this->mFrom = array();   
            $this->mWhere = array();
			$this->mWhereAdd = '';
            $this->mGroupby = '';
            $this->mHaving = ''; 
            $this->mOrderby = array();
            $this->mLimit = '';  
        }
        elseif ($part == 'select')
        {
            $this->mSelect = array($value);
        }
        elseif ($part == 'selectadd')
        {
            array_push($this->mSelect, $value);
        }
        elseif ($part == 'insert')
        {
            $this->mInsert = $value;
        }
        elseif ($part == 'update')
        {
            $this->mUpdate = $value;
        }
        elseif ($part == 'delete')
        {
            $this->mDelete = $value;
        }
        elseif ($part == 'set')
        {
            $this->mSet = array($value => $value2);
        }
        elseif ($part == 'setadd')
        {
            //array_push($this->mSet, array($value => $value2));
            $this->mSet[$value] = $value2;
        }
        elseif ($part == 'from')
        {
            $this->mFrom = array($value);
        }
        elseif ($part == 'fromadd')
        {
            array_push($this->mFrom, $value);
        }
        elseif ($part == 'where')
        {
            $this->mWhere = array($value);
        }
        elseif ($part == 'whereadd')
        {
            array_push($this->mWhere, $value);
        }
        elseif ($part == 'groupby')
        {
            $this->mGroupby = $value;
        }
        elseif ($part == 'orderby')
        {
            $this->mOrderby = array($value);
        }
        elseif ($part == 'orderbyadd')
        {
            array_push($this->mOrderby, $value);
        }
        elseif ($part == 'limit')
        {
            $this->mLimit = $value;
        }
        //输出生成后的SQL语句
        elseif ($part == 'sqlstr')
        {
            if (($value == 'auto') || empty($value))
            {
                //自动判断
                if (count($this->mSelect) > 0)
                {
                    return($this->gsql($part, 'select'));
                }
                elseif (strlen($this->mInsert) > 0)
                {
                    return($this->gsql($part, 'insert'));
                }
                elseif (strlen($this->mUpdate) > 0)
                {
                    return($this->gsql($part, 'update'));
                }
                elseif (strlen($this->mDelete) > 0)
                {
                    return($this->gsql($part, 'delete'));
                }
            }
            elseif ($value == 'select')
            {
                //SELECT语句
                $sql = '';
                if (count($this->mSelect) > 0)
                {
                    $sql .= 'select ';
					if (!empty($this->mSelectDistinct))
					{
						$sql .= ' distinct ';
					}
                    $s_t = '';
                    foreach ($this->mSelect as $key=>$val)
                    {
                        $s_t .= ', ' . $val;
                    }
                    $sql .= substr($s_t, 2);
                }
                if (count($this->mFrom) > 0)
                {
                    $sql .= ' from ';
                    $s_t = '';
                    foreach ($this->mFrom as $key=>$val)
                    {
                        $s_t .= ', ' . $val;
                    }
                    $sql .= substr($s_t, 2);
                }
                if (count($this->mWhere) > 0)
                {
                    $sql .= ' where ';
                    $s_t = '';
                    foreach ($this->mWhere as $key=>$val)
                    {
                        $s_t .= ' and ' . $val;
                    }
                    $sql .= substr($s_t, 5);
					$sql .= $this->mWhereAdd;
                }
				elseif ( !empty($this->mWhereAdd) )
				{
				    $sql .= ' where ' . $this->mWhereAdd;
				}
                if (strlen($this->mGroupby) > 0)
                {
                    $sql .= ' group by ' . $this->mGroupby . ' ';
                }
                if (strlen($this->mHaving) > 0)
                {
                    $sql .= ' having ' . $this->mHaving . ' ';
                }
                if (count($this->mOrderby) > 0)
                {
                    $sql .= ' order by ';
                    $s_t = '';
                    foreach ($this->mOrderby as $key=>$val)
                    {
                        $s_t .= ', ' . $val;
                    }
                    $sql .= substr($s_t, 2);
                }
                if (strlen($this->mLimit) > 0)
                {
                    $sql .= ' limit ' . $this->mLimit . ' ';
                }
                return($sql);
            }
            elseif ($value == 'insert')
            {
                //INSERT语句
                $sql = '';
                if (strlen($this->mInsert) > 0)
                {
                    $sql .= 'insert into ' . $this->mInsert . ' ';
                }
                $col = '';
                $values = '';
                if (count($this->mSet) > 0)
                {
                    foreach ($this->mSet as $key=>$val)
                    {
                        $col .= ', ' . $key;
                        $values .= ', ' . $val;
                    }
                    $col = substr($col, 2);
                    $values = substr($values, 2);
                }
                $sql .= '(' . $col . ') VALUES (' . $values . ')';
//                if (count($this->mSet) > 0)
//                {
//                    $sql .= ' set ';
//                    $s_t = '';
//                    foreach ($this->mSet as $key=>$val)
//                    {
//                        $s_t .= ', ' . $key . ' = ' . $val . ' ';
//                    }
//                    $sql .= substr($s_t, 2);
//                }
                return($sql);
            }
            elseif ($value == 'update')
            {
                //UPDATE语句
                $sql = '';
                if (strlen($this->mUpdate) > 0)
                {
                    $sql .= 'update ' . $this->mUpdate . ' ';
                }
                if (count($this->mSet) > 0)
                {
                    $sql .= ' set ';
                    $s_t = '';
                    foreach ($this->mSet as $key=>$val)
                    {
                        $s_t .= ', ' . $key . ' = ' . $val . ' ';
                    }
                    $sql .= substr($s_t, 2);
                }
                if (count($this->mWhere) > 0)
                {
                    $sql .= ' where ';
                    $s_t = '';
                    foreach ($this->mWhere as $key=>$val)
                    {
                        $s_t .= ' and ' . $val;
                    }
                    $sql .= substr($s_t, 5);
					$sql .= $this->mWhereAdd;
                }
				elseif ( !empty($this->mWhereAdd) )
				{
				    $sql .= ' where ' . $this->mWhereAdd;
				}
                return($sql);
            }
            elseif ($value == 'delete')
            {
                //DELETE语句
                $sql = '';
                if (strlen($this->mDelete) > 0)
                {
                    $sql .= 'delete from ' . $this->mDelete . ' ';      //mysql use "delete from"
                }
                if (count($this->mWhere) > 0)
                {
                    $sql .= ' where ';
                    $s_t = '';
                    foreach ($this->mWhere as $key=>$val)
                    {
                        $s_t .= ' and ' . $val;
                    }
                    $sql .= substr($s_t, 5);
					$sql .= $this->mWhereAdd;
                }
				elseif ( !empty($this->mWhereAdd) )
				{
				    $sql .= ' where ' . $this->mWhereAdd;
				}
                return($sql);
            }
        }
    } // end of function gsql


	 /**
	  * 设置附加的SQL WHERE子句
	  * @param	string	$sql
	  * @see $mWhereAdd
	  * @access	public
	  */
	 function SetSqlWhereAdd($sql = '')
	 {
	     if ( !empty($sql) )
	     {
             //不能以'and '结尾
             if ( 'and ' == substr($sql, -4) )
             {
                 $sql = substr($sql, 0, strlen($sql) - 4);
             }
	         $this->mWhereAdd = $sql;
	     }
	 } // end of function SetSqlWhereAdd


	/**
	 * 处理SQL语句的WHERE子句
	 *
	 * @param	string	$sql	要处理的SQL语句
	 * @param	string	$method	处理方式
	 * @param	string	$sqlExt	作为数据的SQL语句
	 * @return	string
	 * @access	public
	 */
	function SqlWhere($sql, $method, $sqlExt)
	{
	    if ('add' == $method)
	    {
	        if (!empty($sqlExt))
	        {
				//增加一个WHERE子句
				//到PHP5才能使用stripos函数
				if (!stristr($sql, 'where'))
				{
					//$sql .= ' where 1=1 ';
                    $sql .= ' where ' . $sqlExt;
				}
                else
                {
    				$sql .= ' and ' . $sqlExt;
                }
	        }
	    }
        //不能以'and '结尾
        if ( 'and ' == substr($sql, -4) )
        {
            $sql = substr($sql, 0, strlen($sql) - 4);
        }
		return($sql);
	} // end of function SqlWhere


} // end of class SqlGenerator
?>