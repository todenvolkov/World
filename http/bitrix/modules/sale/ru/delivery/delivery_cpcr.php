<?
/**********************************************************************
Delivery handler for CPCR delivery service (http://www.cpcr.ru/)
It uses on-line calculator. Calculation only to Russia.
Files: 
cpcr/cities.php - cache of cpcr ids for cities
cpcr/locations.php - list of cpcr ids for countries.
**********************************************************************/

CModule::IncludeModule("sale");

IncludeModuleLangFile('/bitrix/modules/sale/delivery/delivery_cpcr.php');

define('DELIVERY_CPCR_WRITE_LOG', 0); // flag 'write to log'. use CDeliveryCPCR::__WriteToLog() for logging.
define('DELIVERY_CPCR_CACHE_LIFETIME', 2592000); // cache lifetime - 30 days (60*60*24*30)

define('DELIVERY_CPCR_CATEGORY_DEFAULT', 8); // default category for delivered goods

define('DELIVERY_CPCR_PRICE_TARIFF', 0.0025); // price koefficient - 0.25%

define('DELIVERY_CPCR_COUNTRY_DEFAULT', '209|0'); // default country - Russia
define('DELIVERY_CPCR_CITY_DEFAULT', '40|0'); // default city - Moscow

define('DELIVERY_CPCR_SERVER', 'cpcr.ru'); // server name to send data
define('DELIVERY_CPCR_SERVER_PORT', 80); // server port
define('DELIVERY_CPCR_SERVER_PAGE', '/components/tarifcalc2/tarifcalc.php?JsHttpRequest='); // server page url
define('DELIVERY_CPCR_SERVER_METHOD', 'POST'); // data send method

define('DELIVERY_CPCR_SERVER_POST_FROM_REGION', 'from_select_region'); // query variable name for "from" region id
define('DELIVERY_CPCR_SERVER_POST_FROM_COUNTRY', 'from_select_country'); // query variable name for "from" region id
define('DELIVERY_CPCR_SERVER_POST_FROM_CITY_NAME', 'from_Cities_name'); // query variable name for "from" city name
define('DELIVERY_CPCR_SERVER_POST_FROM_CITY', 'from_Cities_Id'); // query variable name for "from" city id
define('DELIVERY_CPCR_SERVER_POST_WEIGHT', 'Weight'); // query variable name for order weight
define('DELIVERY_CPCR_SERVER_POST_CATEGORY', 'Nature'); // query variable name for order goods category
define('DELIVERY_CPCR_SERVER_POST_PRICE', 'Amount'); // query variable name for order price
define('DELIVERY_CPCR_SERVER_POST_TO_COUNTRY', 'to_select_country'); // query variable name for "to" country id
define('DELIVERY_CPCR_SERVER_POST_TO_REGION', 'to_select_region'); // query variable name for "to" region id
define('DELIVERY_CPCR_SERVER_POST_TO_CITY_NAME', 'to_Cities_name'); // query variable name for "to" city name
define('DELIVERY_CPCR_SERVER_POST_TO_CITY', 'to_Cities_Id'); // query variable name for "to" city id
define('DELIVERY_CPCR_SERVER_POST_ADDITIONAL', 'Amount=0&AmountCheck=1&SMS=0&InHands=0&BeforeSignal=0&DuesOrder=0&PlatType=0&GabarythSum=60&GabarythB=0'); // additional POST data

define('DELIVERY_CPCR_VALUE_CHECK_STRING', '"Total"'); // first check string - to determine whether delivery price is in response
define(
	'DELIVERY_CPCR_VALUE_CHECK_REGEXP', 
	'/"(result[2]{0,1})": \[([^\]]*)\]/i'
); // second check string - regexp to parse final price from response

class CDeliveryCPCR
{
	function Init()
	{
		// fix a possible currency bug
		if ($arCurrency = CCurrency::GetByID('RUR'))
		{
			$base_currency = 'RUR';
		}
		else
		{
			$base_currency = 'RUB';
		}	
	
		return array(
			/* Basic description */
			"SID" => "cpcr", // unique string identifier
			"NAME" => GetMessage('SALE_DH_CPCR_NAME'), // handler public title
			"DESCRIPTION" => GetMessage('SALE_DH_CPCR_DESCRIPTION'), // handler public dedcription
			"DESCRIPTION_INNER" => GetMessage('SALE_DH_CPCR_DESCRIPTION_INNER'), // handler private description for admin panel
			"BASE_CURRENCY" => $base_currency, // handler base currency
			
			"HANDLER" => __FILE__, // handler path - don't change it if you do not surely know what you are doin
			
			/* Handler methods */
			"DBGETSETTINGS" => array("CDeliveryCPCR", "GetSettings"), // callback for method for conversion of string representation to handler settings
			"DBSETSETTINGS" => array("CDeliveryCPCR", "SetSettings"), // callback for method for conversion of handler settings to  string representation
			"GETCONFIG" => array("CDeliveryCPCR", "GetConfig"), // callback method to get handler settings list
			
			"COMPABILITY" => array("CDeliveryCPCR", "Compability"), // callback method to check whether handler is compatible with current order
			"CALCULATOR" => array("CDeliveryCPCR", "Calculate"), // callback method to calculate delivery price
			
			/* List of delivery profiles */
			"PROFILES" => array(
				"simple" => array(
					"TITLE" => GetMessage("SALE_DH_CPCR_SIMPLE_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_CPCR_SIMPLE_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(0, 35000),
					"RESTRICTIONS_SUM" => array(0),
				),
				"optima" => array(
					"TITLE" => GetMessage("SALE_DH_CPCR_OPTIMA_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_CPCR_OPTIMA_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(2000, 31500),
					"RESTRICTIONS_SUM" => array(0),
				),
				"econom" => array(
					"TITLE" => GetMessage("SALE_DH_CPCR_ECONOM_TITLE"),
					"DESCRIPTION" => GetMessage("SALE_DH_CPCR_ECONOM_DESCRIPTION"),
					
					"RESTRICTIONS_WEIGHT" => array(30000),
					"RESTRICTIONS_SUM" => array(0),
				),
			)
		);
	}
	
	function GetConfig()
	{
		$arConfig = array(
			"CONFIG_GROUPS" => array(
				"all" => GetMessage('SALE_DH_CPCR_CONFIG_TITLE'),
			),
			
			"CONFIG" => array(
				"category" => array(
					"TYPE" => "DROPDOWN",
					"DEFAULT" => DELIVERY_CPCR_CATEGORY_DEFAULT,
					"TITLE" => GetMessage('SALE_DH_CPCR_CONFIG_CATEGORY'),
					"GROUP" => "all",
					"VALUES" => array(),
				),
			),
		);
		
		for ($i = 1; $i < 9; $i++)
		{
			$arConfig["CONFIG"]["category"]["VALUES"][$i] = GetMessage('SALE_DH_CPCR_CONFIG_CATEGORY_'.$i);
		}
		
		return $arConfig; 
	}
	
	function GetSettings($strSettings)
	{
		return array(
			"category" => intval($strSettings)
		);
	}
	
	function SetSettings($arSettings)
	{
		$category = intval($arSettings["category"]);
		if ($category <= 0 || $category > 8) return DELIVERY_CPCR_CATEGORY_DEFAULT;
		else return $category;
	}
	
	function __GetLocation($location)
	{
		static $arCPCRCountries;
		static $arCPCRCity;
		
		$arLocation = CSaleLocation::GetByID($location);
		
		$arReturn = array();
		
		if (!is_array($arCPCRCountries))
		{
			require ("cpcr/locations.php");
		}
		
		foreach ($arCPCRCountries as $country_id => $country_title)
		{
			if (
				$country_title == $arLocation["COUNTRY_NAME_ORIG"]
				||
				$country_title == $arLocation["COUNTRY_SHORT_NAME"]
				||
				$country_title == $arLocation["COUNTRY_NAME_LANG"]
				||
				$country_title == $arLocation["COUNTRY_NAME"]
			)
			{
				$arReturn["COUNTRY"] = $country_id;
				break;
			}
		}
		
		$arReturn["CITY"] = $arLocation["CITY_NAME_LANG"];
		
		if (!is_array($arCPCRCity))
		{
			require ("cpcr/cities.php");
		}
		
		/*
		if (is_set($arCPCRCity, $arLocation["CITY_ID"]))
		{
			$arReturn["CITY_ID"] = $arCPCRCity[$arLocation["CITY_ID"]];
		}
		*/
		foreach ($arCPCRCity as $city_id => $city_title)
		{
			if (
				$city_title == $arLocation["CITY_NAME_ORIG"]
				||
				$city_title == $arLocation["CITY_SHORT_NAME"]
				||
				$city_title == $arLocation["CITY_NAME_LANG"]
				||
				$city_title == $arLocation["CITY_NAME"]
			)
			{
				$arReturn["CITY_ID"] = $city_id;
				break;
			}
		}
		
		$arReturn["ORIGINAL"] = array(
			"ID" => $arLocation["ID"],
			"COUNTRY_ID" => $arLocation["COUNTRY_ID"],
			"CITY_ID" => $arLocation["CITY_ID"],
		);
		
		return $arReturn;
	}
	
	function __GetPrice($arData)
	{
		$arResult = array();
		$arProfiles = array('SIMPLE' => 'СПСР - Экспресс', 'OPTIMA' => 'СПСР - Оптима', 'ECONOM' => 'СПСР - Эконом');
	
		foreach ($arData as $arEntry)
		{
			foreach ($arProfiles as $profile => $title)
			{
				if ($arEntry[0] == $title)
				{
					$arResult[ToLower($profile)] = array(
						'VALUE' => $arEntry[1],
						'TRANSIT' => $arEntry[5]
					);
					
					unset($arProfiles[$profile]);
					break;
				}
			}
			
			if (count($arProfiles) <= 0)
				break;
		}
		
		return $arResult;
	}
	
	function Calculate($profile, $arConfig, $arOrder, $STEP)
	{
		if ($STEP >= 3) 
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_CONNECT'),
			);

		$arOrder["WEIGHT"] = CSaleMeasure::Convert($arOrder["WEIGHT"], "G", "KG");
		if ($arOrder["WEIGHT"] <= 0) $arOrder["WEIGHT"] = 1; // weight must not be null - let it be 1 kg

		$arLocationFrom = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_TO"]);

		// caching is dependent from category, locations "from" & "to" and from weight interval
		$cache_id = "sale3|9.5.0|cpcr|".$arConfig["category"]['VALUE']."|".$arLocationFrom["ORIGINAL"]["COUNTRY_ID"]."|".$arLocationFrom["ORIGINAL"]["CITY_ID"]."|".$arLocationTo["ORIGINAL"]["COUNTRY_ID"]."|".$arLocationTo["ORIGINAL"]["CITY_ID"];
		
		if ($arOrder["WEIGHT"] <= 0.5) $cache_id .= "|0"; // first interval - up to 0.5 kg
		elseif ($arOrder["WEIGHT"] <= 1) $cache_id .= "|1"; //2nd interval - up to 1 kg
		else $cache_id .= "|".ceil($arOrder["WEIGHT"]); // other intervals - up to next natural number
	
		$obCache = new CPHPCache();
		
		if ($obCache->InitCache(DELIVERY_CPCR_CACHE_LIFETIME, $cache_id, "/"))
		{
			// cache found
			$vars = $obCache->GetVars();
			$arResult = $vars["RESULT"];
		}
		else
		{
			// format HTTP query request data
			$arQuery = array();
			
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_COUNTRY."=".urlencode($arLocationFrom["COUNTRY"]);
			
			if (is_set($arLocationFrom["CITY_ID"]))
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_CITY."=".urlencode($arLocationFrom["CITY_ID"]);
			else
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_FROM_CITY_NAME."=".urlencode($GLOBALS['APPLICATION']->ConvertCharset($arLocationFrom["CITY"], LANG_CHARSET, 'windows-1251'));

		
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_WEIGHT."=".urlencode($arOrder["WEIGHT"]);
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_CATEGORY."="."1";//urlencode($arConfig["category"]["VALUE"]);
			//$arQuery[] = DELIVERY_CPCR_SERVER_POST_PRICE."=".urlencode($arOrder["PRICE"]);
			// price coefficient will be added later - to make caching independent from price
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_PRICE."=0";
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_COUNTRY."=".urlencode($arLocationTo["COUNTRY"]);
		
			/*
			if (is_set($arLocationTo["REGION"]))
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_REGION."=".urlencode($arLocationTo["REGION"]);
			else
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_REGION."=".urlencode(DELIVERY_CPCR_CITY_DEFAULT);
			*/
		
			if (is_set($arLocationTo["CITY_ID"]))
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_CITY."=".urlencode($arLocationTo["CITY_ID"]);
			else
				$arQuery[] = DELIVERY_CPCR_SERVER_POST_TO_CITY_NAME."=".urlencode($GLOBALS['APPLICATION']->ConvertCharset($arLocationTo["CITY"], LANG_CHARSET, 'windows-1251'));
			
			CDeliveryCPCR::__Write2Log(print_r($arLocationTo, true));
		
			$arQuery[] = DELIVERY_CPCR_SERVER_POST_ADDITIONAL;
		
			//$query_string = $GLOBALS['APPLICATION']->ConvertCharset(implode("&", $arQuery), LANG_CHARSET, 'windows-1251');
			$query_string = implode("&", $arQuery);
		
			list($mtime, $time) = explode(' ', microtime());
			$query_page = DELIVERY_CPCR_SERVER_PAGE . $time . intval($mtime * 10000) . '-xml';
			
		// get data from server
			$data = QueryGetData(
				DELIVERY_CPCR_SERVER, 
				DELIVERY_CPCR_SERVER_PORT,
				$query_page,
				$query_string,
				$error_number = 0,
				$error_text = "",
				DELIVERY_CPCR_SERVER_METHOD,
				'',
				'' // Empty content-type because of CPCR inner bugs
			);
			
			$data = $GLOBALS["APPLICATION"]->ConvertCharset($data, 'windows-1251', LANG_CHARSET);
			
			CDeliveryCPCR::__Write2Log($query_page);
			CDeliveryCPCR::__Write2Log($query_string);
			CDeliveryCPCR::__Write2Log($error_number.": ".$error_text);
			CDeliveryCPCR::__Write2Log($data);
			
			if (strlen($data) <= 0)
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_CONNECT'),
				);
			}

			if ($arData = CUtil::JsObjectToPhp($data))
			{
				//print_r($arData);
			
				if (is_array($arData['js']) && is_array($arData['js']['result2']) && count($arData['js']['result2']) > 0)
				{
					$obCache->StartDataCache();
					$arResult = CDeliveryCPCR::__GetPrice($arData['js']['result2']);
					$obCache->EndDataCache(
						array(
							"RESULT" => $arResult
						)
					);
				}
				elseif (is_array($arData['js']) && is_array($arData['js']['error']))
				{
					return array(
						"RESULT" => "ERROR",
						"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_CONNECT').' ('.htmlspecialchars(strip_tags($arData['js']['error'])).')',
					);
				}
			}
		}
		
		//echo $profile."\r\n";
		//print_r($arResult);
		//die();
		
		if (is_array($arResult[$profile]))
		{
			$arResult[$profile]['RESULT'] = 'OK';
			
			// it's starnge but it seems that CPCR new calculator doesnt count insurance tax at all. so, temporarily comment this line. 
			// TODO: check this later
			//$arResult[$profile]['VALUE'] += $arOrder["PRICE"] * DELIVERY_CPCR_PRICE_TARIFF
			
			return $arResult[$profile];
		}
		else
		{
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_CPCR_ERROR_RESPONSE'),
			);
		}
	}
	
	function Compability($arOrder)
	{
		$arLocationFrom = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryCPCR::__GetLocation($arOrder["LOCATION_TO"]);
	
		// delivery only from russia and to russia
		if (
			$arLocationFrom["COUNTRY"] != DELIVERY_CPCR_COUNTRY_DEFAULT 
			|| 
			$arLocationTo["COUNTRY"] != DELIVERY_CPCR_COUNTRY_DEFAULT
		) 
			return array();
		else 
			return array("simple", "optima", "econom");
	} 
	
	function __Write2Log($data)
	{
		if (defined('DELIVERY_CPCR_WRITE_LOG') && DELIVERY_CPCR_WRITE_LOG === 1)
		{
			$fp = fopen(dirname(__FILE__)."/cpcr.log", "a");
			fwrite($fp, "\r\n==========================================\r\n");
			fwrite($fp, $data);
			fclose($fp);
		}
	}
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryCPCR', 'Init')); 
?>