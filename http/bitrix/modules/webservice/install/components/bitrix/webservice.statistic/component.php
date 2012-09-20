<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("webservice"))
	return;
if(!CModule::IncludeModule("statistic"))
	return;

class CStatisticWS extends IWebService
{
	function CheckAuth()
	{
		$statRight = $GLOBALS["APPLICATION"]->GetGroupRight("statistic");
		if ($statRight == "D")
		{
			$GLOBALS["USER"]->RequiredHTTPAuthBasic();
			return new CSOAPFault('Server Error', 'Unable to authorize user.');
		}

		return False;
	}

	function UsersOnline()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$dbresult = CUserOnline::GetList($guest_count, $session_count, Array("s_session_time"=>"desc"));
		$result = Array("GUEST_COUNT"=>$guest_count, "SESSIONS"=>Array());
		$i=0;
		while ($ar = $dbresult->Fetch())
		{
			$strTmp = "";
			$rsUser = CUser::GetByID($ar["LAST_USER_ID"]);
			if ($ar1 = $rsUser->Fetch())
				$strTmp = "[".$ar1["ID"]."] ".$ar1["NAME"]." ".$ar1["LAST_NAME"]." (".$ar1["LOGIN"].") ";
			else
				$strTmp = "[".$ar["LAST_USER_ID"]."]";
			$ar["USER_NAME"] = $strTmp;
			$result["SESSIONS"][($i++).':SESSION'] = $ar;
		}

		return $result;
	}

	function GetCommonValues()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$result = CTraffic::GetCommonValues(array(),true);
		$result["ONLINE_LIST"] = CStatisticWS::UsersOnline();

		return $result;
	}

	function GetAdv()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CAdv::GetList($a_by, $a_order, array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""), $is_filtered, "", $arrGROUP_DAYS, $v);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["REFERER1"]."/".$arAdv["REFERER2"],
				"today" => $arAdv["SESSIONS_TODAY"],
				"yesterday" => $arAdv["SESSIONS_YESTERDAY"],
				"bef_yesterday" => $arAdv["SESSIONS_BEF_YESTERDAY"],
				"all" => $arAdv["SESSIONS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetEvents()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$e_by = "s_stat";
		$e_order = "desc";
		$dbAdv = CStatEventType::GetList($e_by, $e_order, array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""), $is_filtered);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["EVENT"],
				"today" => $arAdv["TODAY_COUNTER"],
				"yesterday" => $arAdv["YESTERDAY_COUNTER"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_COUNTER"],
				"all" => $arAdv["TOTAL_COUNTER"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetPhrases()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CTraffic::GetPhraseList($s_by, $s_order, array(), $is_filtered, false);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => "0",
				"name" => TruncateText($arAdv["PHRASE"], 50),
				"today" => $arAdv["TODAY_PHRASES"],
				"yesterday" => $arAdv["YESTERDAY_PHRASES"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_PHRASES"],
				"all" => $arAdv["TOTAL_PHRASES"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetRefSites()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$dbAdv = CTraffic::GetRefererList($by, $order, array(), $is_filtered, false);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => "0",
				"name" => $arAdv["SITE_NAME"],
				"today" => $arAdv["TODAY_REFERERS"],
				"yesterday" => $arAdv["YESTERDAY_REFERERS"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_REFERERS"],
				"all" => $arAdv["TOTAL_REFERERS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetSearchers()
	{
		if (($r = CStatisticWS::CheckAuth()) !== False)
			return $r;

		$arResult = array();

		$f_by = "s_stat";
		$f_order = "desc";
		$dbAdv = CSearcher::GetList($f_by, $f_order, array("DATE1_PERIOD" => "", "DATE2_PERIOD" => ""), $is_filtered);
		$i = 0;
		while ($arAdv = $dbAdv->Fetch())
		{
			$i++;
			$arResult[$i.':top'] = array(
				"id" => $arAdv["ID"],
				"name" => $arAdv["NAME"],
				"today" => $arAdv["TODAY_HITS"],
				"yesterday" => $arAdv["YESTERDAY_HITS"],
				"bef_yesterday" => $arAdv["B_YESTERDAY_HITS"],
				"all" => $arAdv["TOTAL_HITS"]
			);
			if ($i >= COption::GetOptionInt("statistic", "STAT_LIST_TOP_SIZE", 10))
				break;
		}

		return $arResult;
	}

	function GetWebServiceDesc()
	{
		$wsdesc = new CWebServiceDesc();
		$wsdesc->wsname = "bitrix.webservice.statistic";
		$wsdesc->wsclassname = "CStatisticWS";
		$wsdesc->wsdlauto = true;
		$wsdesc->wsendpoint = CWebService::GetDefaultEndpoint();
		$wsdesc->wstargetns = CWebService::GetDefaultTargetNS();

		$wsdesc->classTypes = array();
		$wsdesc->structTypes["Session"] =
			array(
				"ID" => array("varType" => "integer"),
				"ADV_ID" => array("varType" => "integer"),
				"REFERER1" => array("varType" => "string"),
				"REFERER2" => array("varType" => "string"),
				"REFERER3" => array("varType" => "string"),
				"ADV_BACK" => array("varType" => "string"),
				"LAST_SITE_ID" => array("varType" => "string"),
				"URL_LAST" => array("varType" => "string"),
				"URL_LAST_404" => array("varType" => "string"),
				"IP_LAST" => array("varType" => "string"),
				"HITS" => array("varType" => "integer"),
				"USER_AUTH" => array("varType" => "string"),
				"STOP_LIST_ID" => array("varType" => "integer"),
				"GUEST_ID" => array("varType" => "integer"),
				"FAVORITES" => array("varType" => "string"),
				"LAST_USER_ID" => array("varType" => "string"),
				"SESSION_TIME" => array("varType" => "string"),
				"DATE_LAST" => array("varType" => "string"),
				"NEW_GUEST" => array("varType" => "string"),
				"FIRST_URL_FROM" => array("varType" => "string"),
				"FIRST_SITE_ID" => array("varType" => "string"),
				"URL_FROM" => array("varType" => "string"),
				"COUNTRY_ID" => array("varType" => "string"),
				"COUNTRY_NAME" => array("varType" => "string"),
			);

		$wsdesc->structTypes["Top"] =
			array(
				"id" => array("varType" => "int"),
				"name" => array("varType" => "string"),
				"today" => array("varType" => "integer"),
				"yesterday" => array("varType" => "integer"),
				"bef_yesterday" => array("varType" => "integer"),
				"all" => array("varType" => "integer"),
			);

		$wsdesc->structTypes["UsersOnlineList"] = Array(
			"GUEST_COUNT"  => array("varType" => "integer"),
			"SESSION_COUNT"  => array("varType" => "integer"),
			"SESSIONS" => array("varType" => "ArrayOfSession", "arrType"=>"Session")
			);

		$wsdesc->structTypes["CommonValues"] =
			array(
				"TOTAL_HITS" => array("varType" => "integer"),
				"TODAY_HITS" => array("varType" => "integer"),
				"YESTERDAY_HITS" => array("varType" => "integer"),
				"B_YESTERDAY_HITS" => array("varType" => "integer"),
				"TOTAL_SESSIONS" => array("varType" => "integer"),
				"TODAY_SESSIONS" => array("varType" => "integer"),
				"YESTERDAY_SESSIONS" => array("varType" => "integer"),
				"B_YESTERDAY_SESSIONS" => array("varType" => "integer"),
				"TOTAL_EVENTS" => array("varType" => "integer"),
				"TODAY_EVENTS" => array("varType" => "integer"),
				"YESTERDAY_EVENTS" => array("varType" => "integer"),
				"B_YESTERDAY_EVENTS" => array("varType" => "integer"),
				"TOTAL_HOSTS" => array("varType" => "integer"),
				"TODAY_HOSTS" => array("varType" => "integer"),
				"YESTERDAY_HOSTS" => array("varType" => "integer"),
				"B_YESTERDAY_HOSTS" => array("varType" => "integer"),
				"TOTAL_GUESTS" => array("varType" => "integer"),
				"TODAY_GUESTS" => array("varType" => "integer"),
				"YESTERDAY_GUESTS" => array("varType" => "integer"),
				"B_YESTERDAY_GUESTS" => array("varType" => "integer"),
				"TODAY_NEW_GUESTS" => array("varType" => "integer"),
				"YESTERDAY_NEW_GUESTS" => array("varType" => "integer"),
				"B_YESTERDAY_NEW_GUESTS" => array("varType" => "integer"),
				"TOTAL_FAVORITES" => array("varType" => "integer"),
				"TODAY_FAVORITES" => array("varType" => "integer"),
				"YESTERDAY_FAVORITES" => array("varType" => "integer"),
				"B_YESTERDAY_FAVORITES" => array("varType" => "integer"),
				"ONLINE_GUESTS" => array("varType" => "integer"),
				"ONLINE_LIST" => array("varType" => "UsersOnlineList"),
			);


		$wsdesc->structTypes["Tops"] = Array(
			"TOP_LIST" => array("varType" => "ArrayOfTop", "arrType"=>"Top")
			);

		$wsdesc->classes = array(
			"CStatisticWS" => array(
				"UsersOnline" => array(
					"type"		=> "public",
					"name"		=> "UsersOnline",
					"input"		=> array(),
					"output"	=> array(
						"user" => array("varType" => "UsersOnlineList")
					),
				),
				"GetCommonValues" => array(
					"type"		=> "public",
					"name"		=> "GetCommonValues",
					"input"		=> array(),
					"output"	=> array(
						"user" => array("varType" => "CommonValues")
					),
				),
				"GetAdv" => array(
					"type"		=> "public",
					"name"		=> "GetAdv",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "Tops")
					),
				),
				"GetEvents" => array(
					"type"		=> "public",
					"name"		=> "GetEvents",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "Tops")
					),
				),
				"GetPhrases" => array(
					"type"		=> "public",
					"name"		=> "GetPhrases",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "Tops")
					),
				),
				"GetRefSites" => array(
					"type"		=> "public",
					"name"		=> "GetRefSites",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "Tops")
					),
				),
				"GetSearchers" => array(
					"type"		=> "public",
					"name"		=> "GetSearchers",
					"input"		=> array(),
					"output"	=> array(
						"adv" => array("varType" => "Tops")
					),
				),
			),
		);
		return $wsdesc;
	}
}

$arParams["WEBSERVICE_NAME"] = "bitrix.webservice.statistic";
$arParams["WEBSERVICE_CLASS"] = "CStatisticWS";
$arParams["WEBSERVICE_MODULE"] = "";

$APPLICATION->IncludeComponent(
	"bitrix:webservice.server",
	"",
	$arParams
);

die();
?>