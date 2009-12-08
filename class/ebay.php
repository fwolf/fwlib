<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-12-08
 */

require_once('fwolflib/func/config.php');

/**
 * Ebay API
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-12-08
 */
class Ebay
{
	/**
	 * Id and Token
	 * @var	array
	 */
	public $aToken = array();

	/**
	 * Compative level
	 * @var	int
	 */
	public $iCompatlevel = 0;

	/**
	 * Site ID
	 * @var	int
	 */
	public $iSiteid = 0;


	/**
	 * Constructor
	 *
	 * @param	string	$profile	Name of profile
	 */
	public function __construct($profile) {
		if (!empty($profile))
			$this->GetToken($profile);
	} // end of func __construct


	/**
	 * Do an API call
	 * @param	string	$func
	 * @param	string	$request
	 * @return	string
	 */
	function ApiCall($func, $request) {
		// Request if param array?
		if (is_array($request)) {
			$request = $this->{"GenRequest$func"}($request);
		}

		// Apply token to request
		$request = str_replace('{eBayAuthToken}', $this->aToken['usertoken'], $request);

		// Gen eBay requested header
		$header = array (
			'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->iCompatlevel,

			// Set the keys
			'X-EBAY-API-DEV-NAME: ' . $this->aToken['devid'],
			'X-EBAY-API-APP-NAME: ' . $this->aToken['appid'],
			'X-EBAY-API-CERT-NAME: ' . $this->aToken['certid'],

			// The name of the calling func
			'X-EBAY-API-CALL-NAME: ' . $func,

			// SiteID is the eBay site we called
			'X-EBAY-API-SITEID: ' . $this->iSiteid,
		);

		// Using curl now
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_URL, $this->aToken['serverurl']);

		// No SSL certificate
		curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, 0);

		// Set the headers
		curl_setopt($conn, CURLOPT_HTTPHEADER, $header);

		curl_setopt($conn, CURLOPT_POST, 1);

		// Request body
		curl_setopt($conn, CURLOPT_POSTFIELDS, $request);

		// Set it to return the transfer as a string from curl_exec
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);

		// Send the Request
		$response = curl_exec($conn);

		curl_close($conn);
		return $response;
	} // end of func ApiCall


	/**
	 * Generate XML for request, commmon style - mark pair
	 *
	 * @param	array	&$param
	 * @return	string
	 */
	protected function GenRequestCommon(&$param) {
		if (empty($param) || !is_array($param))
			return;

		$s = '';
		foreach ($param as $k => $v) {
			$s .= "	<$k>$v</$k>\n";
			unset($param[$k]);
		}

		return $s;
	} // end of func GenRequestCommon


	/**
	 * Generate XML for request, common footer part
	 *
	 * @param	string	$func
	 * @return	string
	 */
	protected function GenRequestFooter($func) {
		return "</{$func}Request>
";
	} // end of func GenRequestFooter


	/**
	 * Generate XML for func GetOrders
	 *
	 * @param	array	&$param
	 * @return	string
	 * @link	http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetOrders.html
	 */
	public function GenRequestGetOrders(&$param) {
		$s = $this->GenRequestHeader('GetOrders');

		// Special part
		if (isset($param['CreateTimeFrom'])) {
			$s .= "	<CreateTimeTo>" . date('Y-m-d H:i:s')
				. "</CreateTimeTo>\n";
			unset($param['CreateTimeTo']);
		}

		// Common part
		$s .= $this->GenRequestCommon($param);

		$s .= $this->GenRequestFooter('GetOrders');
		return $s;
	} // end of func GenRequestGetOrders


	/**
	 * Generate XML for request, common header part
	 *
	 * @param	string	$func
	 * @return	string
	 */
	protected function GenRequestHeader($func) {
		return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<{$func}Request xmlns=\"urn:ebay:apis:eBLBaseComponents\">
	<RequesterCredentials>
		<eBayAuthToken>{eBayAuthToken}</eBayAuthToken>
	</RequesterCredentials>
";
	} // end of func GenRequestHeader


	/**
	 * Get Token, 3 IDs from config by given profile
	 *
	 * Will read config using fwolflib/func/config.php
	 * 	::GetCfg('ebay.profiles.name');
	 * Will also retrieve compatlevel & siteid.
	 * @param	string	$profile
	 */
	public function GetToken($profile) {
		$this->aToken = GetCfg('ebay.profiles.' . GetCfg('ebay.profile'));
		$this->iCompatlevel = GetCfg('ebay.compatlevel');
		$this->iSiteid = GetCfg('ebay.siteid');
	} // end of func GetToken

/*
<?xml version="1.0" encoding="utf-8"?>
<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
	<RequesterCredentials>
		<eBayAuthToken>{eBayAuthToken}</eBayAuthToken>
	</RequesterCredentials>
	<CreateTimeFrom>2009-11-01 00:00:00</CreateTimeFrom>
	<CreateTimeTo>2009-12-31 24:00:00</CreateTimeTo>
	<OrderRole>Seller</OrderRole>
	<OrderStatus>Active</OrderStatus>
</GetOrdersRequest>
*/
} // end of class Ebay
?>
