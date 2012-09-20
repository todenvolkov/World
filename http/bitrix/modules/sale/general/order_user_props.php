<?
IncludeModuleLangFile(__FILE__);

class CAllSaleOrderUserProps
{
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_user_props ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $USER;

		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && IntVal($arFields["PERSON_TYPE_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_EMPTY_PERS_TYPE"), "ERROR_NO_PERSON_TYPE_ID");
			return false;
		}

		if (false && !$USER->IsAuthorized())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_UNAUTH"), "ERROR_NO_AUTH");
			return false;
		}

		if (!is_set($arFields, "USER_ID"))
			$arFields["USER_ID"] = IntVal($USER->GetID());

		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && IntVal($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOUP_NO_USER_ID"), "ERROR_NO_PERSON_TYPE_ID");
			return false;
		}

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if (!CSaleOrderUserProps::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_user_props", $arFields);

		$strSql =
			"UPDATE b_sale_user_props SET ".
			"	".$strUpdate.", ".
			"	DATE_UPDATE = ".$DB->GetNowFunction()." ".
			"WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	function ClearEmpty()
	{
		global $DB;
		$strSql = 
			"SELECT UP.ID ".
			"FROM b_sale_user_props UP ".
			"	LEFT JOIN b_sale_user_props_value UPV ON (UP.ID = UPV.USER_PROPS_ID) ".
			"WHERE UPV.ID IS NULL ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($res = $db_res->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_user_props WHERE ID = ".$res["ID"]."");
		}
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		$DB->Query("DELETE FROM b_sale_user_props_value WHERE USER_PROPS_ID = ".$ID."", true);
		return $DB->Query("DELETE FROM b_sale_user_props WHERE ID = ".$ID."", true);
	}

	function OnUserDelete($ID)
	{
		$ID = IntVal($ID);
		$db_res = CSaleOrderUserProps::GetList(($b="ID"), ($o="ASC"), Array("USER_ID"=>$ID));
		while ($ar_res = $db_res->Fetch())
		{
			CSaleOrderUserProps::Delete(IntVal($ar_res["ID"]));
		}
		return True;
	}
}
?>