<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-11-17
 */

/**
 * Program execute time benchmark toosl.
 *
 * Time is mesured by microtime.
 *
 * Reference:
 * http://pear.php.net/package/Benchmark/docs/latest/__filesource/fsource_Benchmark__Benchmark-1.2.7doctimer_example.php.html
 * http://www.mdsjack.bo.it/index.php?page=kwikemark
 * http://www.phpclasses.org/browse/package/2244.html
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-11-17
 */
class Benchmark{

	/**
	 * Define color group
	 *
	 * Seq: fast to slow
	 *
	 * @var array
	 */
	public $aColor = array(
		"#00FF00",
		"#CCFFCC",
		"#77FF77",
		"#FFCCCC",
		"#FF7777",
		"#FF0000"
	);

	/**
	 * Group data
	 *
	 * array(
	 * 	iGroup => array(
	 * 		desc
	 * 		time_start
	 * 		time_stop
	 * 	)
	 * )
	 * @var	array
	 */
	protected $aGroup = array();

	/**
	 * Marker data
	 *
	 * array(
	 * 	iGroup => array(
	 * 		iMark => array(
	 * 			desc
	 * 			time
	 * 			dur
	 * 			color
	 * 			pct
	 * 		)
	 * 	)
	 * )
	 * @var	array
	 */
	protected $aMark = array();

	/**
	 * Current mark group no.
	 *
	 * 1 group meas start->stop, another start will be group 2.
	 * @var	int
	 */
	protected $iGroup = 0;

	/**
	 * Seq of marker in group, start from 1
	 * @var	int
	 */
	protected $iMark = 1;


	/**
	 * Constructor
	 *
	 * @param	string	$options	Eg: autostart
	 */
	public function __construct($options = '') {
		// Auto start
		if (!(false === strpos($options, 'autostart'))) {
			$this->Start();
		}
	} // end of func construct


	/**
	 * Display benchmark result
	 *
	 * @param	string	$options
	 */
	public function Display($options = '') {
		echo $this->Result($options);
	} // end of func Display


	/**
	 * Format cell bg color
	 *
	 * Split max/min marker dur by color number, and put each mark in it's color
	 * @param	int	$i_group
	 */
	protected function FormatColor($i_group) {
		// Find max/min marker dur
		$dur_min = $this->aMark[$i_group][1]['dur'];
		$dur_max = $dur_min;
		foreach ($this->aMark[$i_group] as $i_mark => &$ar_mark) {
			if ($ar_mark['dur'] > $dur_max)
				$dur_max = $ar_mark['dur'];
			if ($ar_mark['dur'] < $dur_min)
				$dur_min = $ar_mark['dur'];
		}
		$dur = $dur_max - $dur_min;
		// Only 1 marker
		if (0 == $dur)
			$dur = $dur_max;

		// Amount of color
		$i_color = count($this->aColor);
		if (1 > $i_color) return;

		// Split dur
		$step = $dur / $i_color;
		$ar_dur = array();
		// 6 color need 7 bound value
		for ($i=0; $i<($i_color + 1); $i++)
			$ar_dur[$i] = $step * $i;

		// Compare, assign color
		foreach ($this->aMark[$i_group] as $i_mark => &$ar_mark) {
			for ($i=1; $i<($i_color + 1); $i++) {
				if (($ar_mark['dur'] - $dur_min) <= $ar_dur[$i]) {
					// 5.5 < 6, assign color[5]/color no.6
					$ar_mark['color'] = $this->aColor[$i - 1];

					// Compute dur percent
					$ar_mark['pct'] = round(100 * $ar_mark['dur'] / $this->aGroup[$i_group]['dur']);

					// Quit for
					$i = $i_color + 1;
				}
			}
		}
	} // end of func FormatColor


	/**
	 * Format time to output
	 *
	 * @param	float	$time
	 * @return	string
	 */
	protected function FormatTime($time) {
		// Split dur by '.' to make solid width
		$sec = floor($time);
		$usec = substr(strval(round($time - $sec, 3)), 2);
		$html = <<<EOF

<div style="float: left; width: 4em; text-align: right;">
	{$sec}
</div>
<div style="float: left;">.</div>
<div style="float: left; width: 3em; text-align: left;">
	{$usec}
</div>

EOF;
		return $html;
	} // end of func FormatTime


	/**
	 * Get current time, mesured by microsecond
	 *
	 * @return	float
	 */
	protected function GetTime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec) * 1000;
	} // end of func GetTime


	/**
	 * Set a marker
	 *
	 * @param	string	$desc	Marker description
	 * @param	string	$color	Specific color like '#FF0000' or 'red'
	 */
	public function Mark($desc = '', $color = '') {
		if (1 == $this->iMark)
			$this->aMark[$this->iGroup] = array();
		$ar = &$this->aMark[$this->iGroup][$this->iMark];

		if (empty($desc))
			$desc = "Group #{$this->iGroup}, Mark #{$this->iMark}";

		$ar['desc'] = $desc;
		$ar['time'] = $this->GetTime();
		if (1 == $this->iMark)
			$ar['dur'] = $ar['time'] - $this->aGroup[$this->iGroup]['time_start'];
		else
			$ar['dur'] = $ar['time'] - $this->aMark[$this->iGroup][$this->iMark - 1]['time'];
		if (!empty($color))
			$ar['color'] = $color;

		$this->iMark ++;
	} // end of func Mark


	/**
	 * Get html result
	 *
	 * @param	string	$options
	 * @return	string
	 */
	public function Result($options = '') {
		// Stop last group if it's not stopped
		if (!isset($this->aGroup[$this->iGroup]['time_stop'])
			&& isset($this->aGroup[$this->iGroup]['time_start']))
			$this->Stop();

		$html = '';

		if (0 <= $this->iGroup) {
			$html .= <<<EOF

<style type="text/css" media="screen, print">
<!--
	#fl-bm table, #fl-bm td {
		border: 1px solid #999;
		border-collapse: collapse;
		padding-left: 0.2em;
	}
	#fl-bm table caption, #fl-bm-m {
		margin-top: 0.5em;
	}
	#fl-bm tr.total {
		background-color: #E5E5E5;
	}
-->
</style>

EOF;
			$html .= "<div id='fl-bm'>\n";
			foreach ($this->aGroup as $i_group => $ar_group) {
				$this->FormatColor($i_group);

				// Stop will create mark, so no 0=mark
				$html .= "\t<table id='fl-bm-g{$i_group}'>\n";
				$html .= "\t\t<caption>{$ar_group['desc']}</caption>\n";

				// Th
				$html .= <<<EOF

<thead>
<tr>
	<th>Dur Time</th>
	<th>Mark Description</th>
	<th>%</th>
</tr>
</thead>

EOF;
				// Markers
				if (0 < count($this->aMark[$i_group])) {
					$html .= "<tbody>\n";
					foreach ($this->aMark[$i_group] as $i_mark => $ar_mark) {
						$time = $this->FormatTime($ar_mark['dur']);
						// Bg color
						if (!empty($ar_mark['color']))
							$color = ' style="background-color: ' . $ar_mark['color'] . ';"';
						else
							$color = '';
						$html .= <<<EOF

<tr>
	<td{$color}>{$time}</td>
	<td>{$ar_mark['desc']}</td>
	<td style="text-align: right">{$ar_mark['pct']}%</td>
</tr>

EOF;
					}
					$html .= "</tbody>\n";
				}

				// Stop has already set marker

				// Total
				$time = $this->FormatTime($ar_group['dur']);
				$html .= <<<EOF

<tr class="total">
	<td>{$time}</td>
	<td>Total</td>
	<td>-</td>
</tr>

EOF;

				$html .= "\t</table>\n";
			}

			// Memory usage
			if (function_exists('memory_get_usage')) {
				$memory = number_format(memory_get_usage());
				$html .= <<<EOF

<div id="fl-bm-m">
	Memory Usage: $memory
</div>

EOF;
			}

			$html .= "</div>\n";
		}

		return $html;
	} // end of func Result


	/**
	 * Start the timer
	 *
	 * @param	string	$desc	Group description
	 */
	public function Start($desc = '') {
		// Stop last group if it's not stopped
		if (!isset($this->aGroup[$this->iGroup]['time_stop'])
			&& isset($this->aGroup[$this->iGroup]['time_start']))
			$this->Stop();

		if (empty($desc))
			$desc = "Group #{$this->iGroup}";

		$this->aGroup[$this->iGroup]['time_start'] = $this->GetTime();
		$this->aGroup[$this->iGroup]['desc'] = $desc;
	} // end of func Start


	/**
	 * Stop the timer
	 */
	public function Stop() {
		$this->Mark('Stop');

		$time = $this->GetTime();
		$ar = &$this->aGroup[$this->iGroup];
		$ar['time_stop'] = $time;
		$ar['dur'] = $time - $ar['time_start'];

		$this->iGroup ++;
		$this->iMark = 1;
	} // end of func Stop


} // end of class Benchmark
?>
