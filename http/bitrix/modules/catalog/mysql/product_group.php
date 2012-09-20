<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/product_group.php");

/***********************************************************************/
/***********  CCatalogProductGroups  ***********************************/
/***********************************************************************/
class CCatalogProductGroups extends CAllCatalogProductGroups
{
	function Add($arFields)
	{
		global $DB;

		if (!CCatalogProductGroups::CheckFields("ADD", $arFields, 0))
			return False;

		$arInsert = $DB->PrepareInsert("b_catalog_product2group", $arFields);

		$strSql =
			"INSERT INTO b_catalog_product2group(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}
}
?>