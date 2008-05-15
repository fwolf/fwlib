<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf, fwolf.aide@gmail.com
 * @since		2006-10-07
 * @version		$Id$
 */


/**
 * Manual get basename instead of using pathinfo()
 * @param	string	$filename
 * @return	string
 */
function BaseName1($filename)
{
	$i = strrpos($filename, '/');
	if (false === $i)
		return $filename;
	else
		return substr($filename, $i + 1);
} // end of func BaseName1


/**
 * Delete a dir or file completedly
 * 	When del a dir, del all dir and files under it also.
 * @param	string	$name
 */
function DelFile($name)
{
	//:Notice: Lost link file will got nothing using realpath, basename, dirname
	//	So trans in full path as $name all the time.
	if(!is_link($name))
		$name = realpath($name);
	if (is_dir($name) && !is_link($name))
	{
		foreach (scandir($name) as $file)
		{
			if (('.' == $file) || ('..' == $file))
				continue;
			DelFile($name . '/' . $file);
		}
		rmdir($name);
	}
	else
	{
		//echo "unlink($name)\n";
		//:Notice: Only del trash files is allowed.
		if (0 < stripos($name, 'trash'))
			unlink($name);
		else
			echo "Error!!! No trash files included!\n";
	}
} // end of func DelFile


/**
 * Manual get dirname instead of using pathinfo()
 * Result didn't include ending '/'
 * @param	string	$filename
 * @return	string
 */
function DirName1($filename)
{
	$i = strrpos($filename, '/');
	if (false === $i)
		return '';
	else
		return substr($filename, 0, $i);
} // end of func DirName1


/**
 * Count size of a directory, recursive
 * 	 This function also recursive executed automatic
 * @param	string	$path
 * @return	long
 */
function DirSize($path)
{
	$i = 0;
	$files = scandir($path);
	foreach ($files as $file)
	{
		if (('.' != $file) && ('..' != $file))
		{
			$pathfull = $path . '/' . $file;
			if (is_dir($pathfull))
			{
				$i += DirSize($pathfull);
			}
			else
				$i += FileSize1($pathfull);
		}
	}
	return $i;
}// end of function DirSize


/**
 * Manual get extension instead of using pathinfo()
 * @param	string	$filename
 * @return	string
 */
function FileExt1($filename)
{
	$i1 = strrpos($filename, '.');
	$i2 = strrpos($filename, '/');
	if ($i1 < $i2)
		return '';
	else
		return substr($filename, $i1 +1);
} // end of func FileExt1


/**
 * Manual get filename instead of using pathinfo()
 * @param	string	$filename
 * @return	string
 */
function FileName1($filename)
{
	$basename = Basename1($filename);
	$i = strrpos($basename, '.');
	if (false === $i)
		return $basename;
	else
		return substr($basename, 0, $i);
} // end of func FileName1


/**
 * Count size of a file
 * 	 This function is diffrence to php func filesize,
 *		It use stat & count blksize & blocks file used.
 *		11 = blksize, blocksize of filesystem IO *
 *		12 = blocks, number of blocks allocated
 * @param	string	$file
 * @return	long
 */
function FileSize1($file)
{
	if (is_link($file))
		$s = lstat($file);
	else
		$s = stat($file);
	if (-1 == $s['blksize'])
		$size = $s['size'];
	else
		$size = $s['blksize'] * $s['blocks'] / 8;
	return($size);
} // end of func FileSize1


/**
 * List files and file-information of a directory
 * 	By default, sort files by mtime asc
 *  Returned array is started from 1
 * param	string	$dir
 * return	array
 */
function ListDir($dir)
{
	//List files
	$dir = realpath($dir);
	if (empty($dir))
		return(false);
	if (!is_dir($dir))
		return(false);
	$dirfiles = scandir($dir);
	if (empty($dirfiles))
		return(false);
	
	$filename=array();
	$filemtime=array();
	// Parallel arrays (ignore the ".", "..")
	foreach ($dirfiles as $s)
	{
		if (('.' != $s) && ('..' != $s))
		{
			$filename[] = $s;
			$filemtime[] = filemtime($dir . '/' . $s);
		}
	}
	// Merge to an array and sort by mtime
	$ar_t = array_combine($filemtime, $filename);
	ksort($ar_t, SORT_NUMERIC);
	// Build result array, count file or dir size
	$i = 0;
	foreach ($ar_t as $key=>$value)
	{
		$i++;
		$files[$i]['name'] = $value;
		$files[$i]['mtime'] = $key;
		//file or dir's size
		//If not use pathfull, same-named file and '..' will be so bad
		$pathfull = $dir . '/' . $value;
		if (is_link($pathfull))
		{
			$files[$i]['size'] = FileSize1($pathfull);
		}
		elseif (is_dir($pathfull))
		{
			$files[$i]['size'] = DirSize($pathfull);
		}
		else
		{
			$files[$i]['size'] = FileSize1($pathfull);
		}
	}
	// Some maintainence
	unset($dirfiles, $filename, $filemtime, $ar_t);
	return($files);
} // end of func ListDir

?>