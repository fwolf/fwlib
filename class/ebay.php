<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-12-08
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/config.php');


/**
 * Ebay API
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2009-12-08
 */
class Ebay extends Fwolflib {
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
	 * Generate XML for func GetSellerTransactions
	 *
	 * @param	array	&$param
	 * @return	string
	 * @link	http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetSellerTransactions.html
	 */
	public function GenRequestGetSellerTransactions(&$param) {
		$s = $this->GenRequestHeader('GetSellerTransactions');

		// Common part
		$s .= $this->GenRequestCommon($param);

		$s .= $this->GenRequestFooter('GetSellerTransactions');
		return $s;
	} // end of func GenRequestGetSellerTransactions


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


	/**
	 * Parse result of GetOrders
	 *
	 * @param	string	$xml
	 * @return	array
	 */
	public function ParseGetOrders($xml) {
		if (empty($xml)) return array();

		$rs = simplexml_load_string($xml);
		if ('Success' == $rs->Ack) {
			$ar = array();
			if (0 < count($rs->OrderArray->Order)) {
				$i = 0;
				foreach ($rs->OrderArray->Order as $order) {
					// Unique id
					$id = strval($order->OrderID);
					$ar[$id]['OrderID'] = strval($order->OrderID);
					$ar[$id]['OrderStatus'] = strval($order->OrderStatus);
					$ar[$id]['BuyerUserID'] = strval($order->BuyerUserID);
					$ar[$id]['Total'] = strval($order->Total);
					$ar[$id]['Subtotal'] = strval($order->Subtotal);
					$ar[$id]['CreatedTime'] = strval($order->CreatedTime);
					$ar[$id]['CreatedTime'] = date('Y-m-d H:i:s O', strtotime($ar[$id]['CreatedTime']));

					// Shipping
					if (empty($order->ShippingServiceSelected))
						$ar[$id]['ShippingServiceCost'] = 0;
					else
						$ar[$id]['ShippingServiceCost'] = strval($order->ShippingServiceSelected->ShippingServiceCost);
					$ar[$id]['ShippingAddress'] =
						strval($order->ShippingAddress->Name) . "\n"
						. strval($order->ShippingAddress->Street1) . "\n"
						. strval($order->ShippingAddress->Street2) . "\n"
						. strval($order->ShippingAddress->PostalCode) . ' ' . strval($order->ShippingAddress->CityName) . "\n"
						. strval($order->ShippingAddress->StateOrProvince) . "\n"
						. strval($order->ShippingAddress->CountryName)
						;
					$ar[$id]['ShippingAddress'] = str_replace(
						"\n\n"
						, "\n"
						, $ar[$id]['ShippingAddress']);
//					$ar[$id]['ShippingAddressPostalCode'] =
//						strval($order->ShippingAddress->PostalCode);
					$ar[$id]['ShippingAddressPhone'] =
						strval($order->ShippingAddress->Phone);
					$ar[$id]['ShippingAddressPhone'] = str_replace('Invalid Request', '', $ar[$id]['ShippingAddressPhone']);

					// Transaction
					if (empty($order->TransactionArray))
						$ar[$id]['Transaction'] = array();
					else {
						$j = 0;
						foreach ($order->TransactionArray->Transaction as $trans) {
							$ar[$id]['Transaction'][$j] = array();
							$ar_t = &$ar[$id]['Transaction'][$j];
							$ar_t['ItemID'] = strval($trans->Item->ItemID);
							$ar_t['QuantityPurchased'] = strval($trans->QuantityPurchased);
							$ar_t['TransactionPrice'] = strval($trans->TransactionPrice);

							$j ++;
						}
					}

					$i ++;
				}
			}

			return $ar;
		} else {
			return array();
		}
	} // end of func ParseGetOrders


	/**
	 * Parse result of GetOrders
	 *
	 * @param	string	$xml
	 * @return	array
	 */
	public function ParseGetSellerTransactions($xml) {
		if (empty($xml)) return array();

		$rs = simplexml_load_string($xml);
		if ('Success' == $rs->Ack) {
			$ar = array();
			if (0 < count($rs->TransactionArray->Transaction)) {
				$i = 0;
				foreach ($rs->TransactionArray->Transaction as $trans) {
					// Unique id
					$id = strval($trans->TransactionID);
					$ar[$id]['TransactionID'] = strval($trans->TransactionID);
					$ar[$id]['TransactionPrice'] = strval($trans->TransactionPrice);
					$ar[$id]['BuyerUserID'] = strval($trans->Buyer->UserID);
					$ar[$id]['AmountPaid'] = strval($trans->AmountPaid);

					// Shipping
					if (empty($trans->ShippingServiceSelected))
						$ar[$id]['ShippingServiceCost'] = 0;
					else
						$ar[$id]['ShippingServiceCost'] = strval($trans->ShippingServiceSelected->ShippingServiceCost);
					$ar[$id]['ShippingAddress'] = $this->ParseShippingAddress($trans->Buyer->BuyerInfo->ShippingAddress);

					$ar[$id]['PaidTime'] = strval($trans->PaidTime);
					if (empty($ar[$id]['PaidTime']))
						// Not paided
						$ar[$id]['PaidTime'] = strval($trans->Status->CompleteStatus);
					else
						$ar[$id]['PaidTime'] = date('Y-m-d H:i:s O', strtotime($ar[$id]['PaidTime']));

					// Item, only 1 type, qnty => 1
					$ar[$id]['ItemID'] = strval($trans->Item->ItemID);
					$ar[$id]['ItemPrice'] = strval($trans->Item->SellingStatus->CurrentPrice);
					$ar[$id]['QuantityPurchased'] = strval($trans->QuantityPurchased);

					// Have not been multipled ?
					$ar[$id]['TransactionPrice'] = strval($trans->TransactionPrice);

					$i ++;
				}
			}

			return $ar;
		} else {
			return array();
		}
	} // end of func ParseGetSellerTransactions


	/**
	 * Parse shipping address
	 *
	 * @param	object	$addr	$trans/order->ShippingAddress
	 * @return	string
	 */
	protected function ParseShippingAddress($addr) {
		$s =
			strval($addr->Name) . "\n"
			. strval($addr->Street1) . "\n"
			. strval($addr->Street2) . "\n"
			. strval($addr->PostalCode) . ' ' . strval($addr->CityName) . "\n"
			. strval($addr->StateOrProvince) . "\n"
			. strval($addr->CountryName)
			;
		$s = str_replace("\n\n", "\n", $s);
		return $s;
	} // end of func ParseShippingAddress


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
