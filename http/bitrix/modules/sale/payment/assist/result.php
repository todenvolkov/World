<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$assist_Shop_IDP = CSalePaySystemAction::GetParamValue("SHOP_IDP");
$assist_LOGIN = CSalePaySystemAction::GetParamValue("SHOP_LOGIN");
$assist_PASSWORD = CSalePaySystemAction::GetParamValue("SHOP_PASSWORD");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);

set_time_limit(0);

$sHost = "secure.assist.ru";
$sUrl = "/results/results.cfm";
$sVars = "SHOPORDERNUMBER=".$ORDER_ID."&SHOP_ID=".$assist_Shop_IDP."&LOGIN=".$assist_LOGIN."&PASSWORD=".$assist_PASSWORD."&FORMAT=4";
$aDesc = array(
	"AS000"=>GetMessage("SASP_AS000"),
	"AS001"=>GetMessage("SASP_AS001"),
	"AS010"=>GetMessage("SASP_AS010"),
	"AS011"=>GetMessage("SASP_AS011"),
	"AS020"=>GetMessage("SASP_AS020"),
	"AS100"=>GetMessage("SASP_AS100"),
	"AS101"=>GetMessage("SASP_AS101"),
	"AS102"=>GetMessage("SASP_AS102"),
	"AS104"=>GetMessage("SASP_AS104"),
	"AS105"=>GetMessage("SASP_AS105"),
	"AS106"=>GetMessage("SASP_AS106"),
	"AS107"=>GetMessage("SASP_AS107"),
	"AS108"=>GetMessage("SASP_AS108"),
	"AS109"=>GetMessage("SASP_AS109"),
	"AS200"=>GetMessage("SASP_AS200"),
	"AS300"=>GetMessage("SASP_AS300"),
	"AS400"=>GetMessage("SASP_AS400"),
	"AS998"=>GetMessage("SASP_AS998")
);

$sResult = QueryGetData($sHost, 80, $sUrl, $sVars, $errno, $errstr);

if ($sResult <> "")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

	$objXML = new CDataXML();
	$objXML->LoadString($sResult);
	$arResult = $objXML->GetArray();

	if (count($arResult)>0 && $arResult["assistresult"]["@"]["firstcode"] == "0")
	{
		$aRes = $arResult["assistresult"]["#"]["orders"][0]["#"]["order"];
		$nRes = count($aRes);
		if ($nRes > 0 && IntVal($aRes[0]["#"]["ordernumber"][0]["#"]) == $ORDER_ID)
		{
			$aSuccess = array("AS000", "AS001", "AS010", "AS011", "AS020");
			$nRec = $nRes-1;
			for ($i=0; $i<$nRes; $i++)
			{
				if (in_array($aRes[0]["#"]["response_code"][0]["#"], $aSuccess))
				{
					$nRec = $i;
					break;
				}
			}

			$aFields = $aRes[IntVal($nRec)]["#"];

			$arFields = array(
					"PS_STATUS" => (in_array($aFields["response_code"][0]["#"], $aSuccess)?"Y":"N"),
					"PS_STATUS_CODE" => $aFields["response_code"][0]["#"],
					"PS_STATUS_DESCRIPTION" => $aDesc[$aFields["response_code"][0]["#"]],
					"PS_STATUS_MESSAGE" => $aFields["recommendation"][0]["#"],
					"PS_SUM" => DoubleVal($aFields["total"][0]["#"]),
					"PS_CURRENCY" => $aFields["currency"][0]["#"],
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				);

			// You can uncomment this code if you want PAYED flag to be set automatically
			/*
			$arOrder = CSaleOrder::GetByID($ORDER_ID);

			if ($arOrder["PRICE"] == $arFields["PS_SUM"] && $arFields["PS_STATUS"] == "Y")
			{
				CSaleOrder::PayOrder($arOrder["ID"], "Y");
			}
			*/
			CSaleOrder::Update($ORDER_ID, $arFields);

			return true;
		}
	}
}

return false;
?>