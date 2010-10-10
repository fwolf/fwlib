<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2009-12-22
 */


require_once(dirname(__FILE__) . '/fwolflib.php');


/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2009-12-22
 * @link http://www.phplamp.org/2008/06/php-to-excel-clas/
 */
class Excel extends Fwolflib {

	/**
	 * Array of row in xml
	 * @var	array
	 */
	protected $aRow = array();

    /**
     * Footer of excel xml
     * @var string
     */
    protected $sFooter = "</Workbook>";

    /**
     * Header of excel xml
     * @var string
     */
    protected $sHeader = '<?xml version="1.0" encoding="utf-8"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:x="urn:schemas-microsoft-com:office:excel"
	xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:html="http://www.w3.org/TR/REC-html40">
	';

	/**
	 * Title of sheet
	 * @var	string
	 */
	public $sSheetTitle = 'Sheet1';


    /**
     * Set a single row(add mode)
     *
     * @param	array	$ar	1-dimensional array
     */
    protected function SetRow($ar) {
        $s_cell = '';

		if (!empty($ar)) {
			foreach ($ar as $v) {
				// Attention: data type
				if(is_numeric($v)) {
					$v = strval($v);
					// First letter is '0' ?
					if(0 == $v{0}) {
						$s_cells.= '<Cell><Data ss:Type="string">' . $v . '</Data></Cell>
						';
					} else {
						$s_cell .= '<Cell><Data ss:Type="number">' . $v . '</Data></Cell>
						';
					}
				} else {
					$s_cell .= '<Cell><Data ss:Type="string">' . $v . '</Data></Cell>
					';
				}
			}
			$this->aRow[] = "<Row>\n$s_cell</Row>\n";
		}
    } // end of func SetRow


    /**
     * Set data rows, multi row, clean mode
     *
     * @param	array	$ar	2-dimensional array
     */
    public function SetRows ($ar) {
        if (!empty($ar))
			foreach ($ar as $v)
				$this->SetRow($v);
    } // end of func SetRows


    /**
     * Set the worksheet title
     *
     * Checks the string for not allowed characters (:\/?*),
     * cuts it to maximum 31 characters and set the title. Damn
     * why are not-allowed chars nowhere to be found? Windows
     * help's no help...
     *
     * @param	string	$title
     */
    public function SetSheetTitle($title) {
        // Strip special chars
        $title = str_replace (
			array(':', '\\', '/', '?', '*'),
			'', $title);

        // Cut it to the allowed length
        $title = substr($title, 0, 31);

        $this->sSheetTitle = $title;
    } // end of func SetSheetTitle


    /**
     * Output the excel file
     *
     * @param	string	$fn	Filename without '.xls'
     */
    public function Output($fn) {
        // Set header
        header('Content-Type: application/vnd.ms-excel;  charset=utf-8');
        header('Content-Disposition: inline; filename="'
			. $fn . '.xls"');

        // Print
        echo ($this->sHeader);
        echo '<Worksheet ss:Name="'
			. $this->sSheetTitle
			. '">
			<Table>
		';
        echo '<Column ss:Index="1" ss:AutoFitWidth="0" ss:Width="110"/>
		';
        echo implode("\n", $this->aRow);
        echo '</Table>
			</Worksheet>
		';
        echo $this->sFooter;
    }

} // end of class Excel

/*
// Usage
// Need high version of microsoft office

$ar = array (
	array ('列1', '列2', '列3列3列3', '123456'),
);
$xls = new Excel;
$xls->SetRows($ar);
$xls->Output('test');
*/

?>
