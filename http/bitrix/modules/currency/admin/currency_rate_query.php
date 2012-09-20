<?
define("STOP_STATISTICS", "Y");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/include.php");
$CURRENCY_RIGHT = $APPLICATION->GetGroupRight("currency");
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/lang/", "/currencies_rates.php"));

if ($CURRENCY_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$RATE = $RATE_CNT = "";
$strError = "";
if(!check_bitrix_sessid())
	$strError = GetMessage("ERROR_SESSID");
if ($DATE_RATE == "" || !$DB->IsDate($DATE_RATE) || strlen($CURRENCY) < 0)
	$strError = GetMessage("ERROR_DATE_RATE");

if (strlen($strError) <= 0):

$QUERY_STR = "date_req=".$DB->FormatDate($DATE_RATE, CLang::GetDateFormat("SHORT", $lang), "D.M.Y");
$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);
if (strlen($strQueryText)<=0)
{
	if (intval($errno)>0 || strlen($errstr)>0)
		$strError = GetMessage("ERROR_QUERY_RATE");
	else
		$strError = GetMessage("ERROR_EMPTY_ANSWER");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

	$charset = "windows-1251";
	if (preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $strQueryText, $matches))
	{
		$charset = Trim($matches[1]);
	}
	$strQueryText = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $strQueryText);
	$strQueryText = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $strQueryText);
	$strQueryText = $APPLICATION->ConvertCharset($strQueryText, $charset, SITE_CHARSET);

	$objXML = new CDataXML();
	$res = $objXML->LoadString($strQueryText);
	if($res !== false)
		$arData = $objXML->GetArray();
	else
		$arData = false;

	if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"])>0)
	{
		for ($j1 = 0; $j1<count($arData["ValCurs"]["#"]["Valute"]); $j1++)
		{
			if ($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"]==$CURRENCY)
			{
				$RATE_CNT = IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]);
				$arCurrValue = str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"]);
				$RATE = DoubleVal($arCurrValue);
				break;
			}
		}
	}
}

endif;
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");?>

<?if (strlen($strError) > 0):?>
document.getElementById('cyrrency_query_error_div').innerHTML = '<span class="required"><?=$strError;?></span>';
<?else:?>
document.forms['form1'].elements['RATE'].value = '<?=$RATE?>';
document.forms['form1'].elements['RATE_CNT'].value = '<?=$RATE_CNT?>';
<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");?>