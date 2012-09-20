<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog_import.php");

class CCatalogImport extends CAllCatalogImport
{
	function Add($arFields)
	{
		global $DB;

		//	"=LAST_USE" => $DB->GetNowFunction()
		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		$arFields["IS_EXPORT"] = "N";

		if (!CCatalogImport::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_export", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($arInsert[0])>0)
			{
				$arInsert[0] .= ", ";
				$arInsert[1] .= ", ";
			}
			$arInsert[0] .= $key;
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_catalog_export(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);

		//	"=LAST_USE" => $DB->GetNowFunction()
		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		$arFields["IS_EXPORT"] = "N";

		if (!CCatalogImport::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_export", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate)>0) $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql =
			"UPDATE b_catalog_export SET ".$strUpdate." WHERE ID = ".$ID." AND IS_EXPORT = 'N'";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}
}
?>