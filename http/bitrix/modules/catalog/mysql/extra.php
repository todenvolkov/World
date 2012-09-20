<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/extra.php");

class CExtra extends CAllExtra
{
	function Add($arFields)
	{
		global $DB;

		$arFields_i = array(
			'NAME' => trim($arFields['NAME']),
			'PERCENTAGE' => DoubleVal($arFields["PERCENTAGE"]),
		);

		if (isset($arFields['ID']))
			$arFields_i['ID'] = $arFields['ID'];

		if (strlen($arFields_i["NAME"])<=0)
		{
			$GLOBALS['APPLICATION']->ThrowException(GetMessage('CAT_EXTRA_ERROR_NONAME'));
			return false;
		}
		
		foreach ($arFields_i as $key => $value)
			$arFields_i[$key] = "'".$DB->ForSql($arFields_i[$key])."'";
		
		$res = $DB->Insert('b_catalog_extra', $arFields_i, "File: ". __FILE__ ."<br>Line: ". __LINE__ );

		unset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]);
		return $res;
	}
}
?>