<?
IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"] = Array();

class CAllSalePersonType
{
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_person_type ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function CheckFields($ACTION, &$arFields)
	{
		global $DB, $USER;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGP_NO_NAME_TP"), "ERROR_NO_NAME");
			return false;
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGP_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if (!CSalePersonType::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("sale", "OnBeforePersonTypeUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEvent($arEvent, $ID, $arFields)===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_person_type", $arFields);
		$strSql = "UPDATE b_sale_person_type SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);

		$events = GetModuleEvents("sale", "OnPersonTypeUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID, $arFields);

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$db_orders = CSaleOrder::GetList(
				array("DATE_UPDATE" => "DESC"),
				array("PERSON_TYPE_ID" => $ID),
				false,
				array("nTopCount" => 1),
				array("ID")
			);
		if ($db_orders->Fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGP_ERROR_PERSON_HAS_ORDER").$ID, "ERROR_PERSON_HAS_ORDER");
			return False;
		}

		$db_events = GetModuleEvents("sale", "OnBeforePersonTypeDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEvent($arEvent, $ID)===false)
				return false;

		$events = GetModuleEvents("sale", "OnPersonTypeDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);

		$DB->Query("DELETE FROM b_sale_pay_system_action WHERE PERSON_TYPE_ID = ".$ID."", true);

		$db_orderProps = CSaleOrderProps::GetList(
				array("PROPS_GROUP_ID" => "ASC"),
				array("PERSON_TYPE_ID" => $ID)
			);
		while ($arOrderProps = $db_orderProps->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_order_props_variant WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_order_props_value WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_user_props_value WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
		}
		$DB->Query("DELETE FROM b_sale_order_props WHERE PERSON_TYPE_ID = ".$ID."", true);

		$db_orderUserProps = CSaleOrderUserProps::GetList(
				array("NAME" => "ASC"),
				array("PERSON_TYPE_ID" => $ID)
			);
		while ($arOrderUserProps = $db_orderUserProps->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_user_props_value WHERE USER_PROPS_ID = ".$arOrderUserProps["ID"]."", true);
		}
		$DB->Query("DELETE FROM b_sale_user_props WHERE PERSON_TYPE_ID = ".$ID."", true);

		$DB->Query("DELETE FROM b_sale_order_props_group WHERE PERSON_TYPE_ID = ".$ID."", true);

		unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);
		return $DB->Query("DELETE FROM b_sale_person_type WHERE ID = ".$ID."", true);
	}

	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = $DB->Query("SELECT 'x' FROM b_sale_person_type WHERE LID = '".$DB->ForSQL($lang, 2)."'");
		return ($r->Fetch() ? false : true);
	}

	function SelectBox($sFieldName, $sValue, $sDefaultValue = "", $bFullName = True, $JavaFunc = "", $sAddParams = "")
	{
		if (!isset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]) || !is_array($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]) || count($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"])<1)
		{
			unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);
			$l = CSalePersonType::GetList(($by="NAME"), ($order="ASC"));
			while ($l_res = $l->GetNext())
			{
				$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][] = $l_res;
			}
		}
		$s = '<select name="'.$sFieldName.'"';
		if (strlen($sAddParams)>0) $s .= ' '.$sAddParams.'';
		if (strlen($JavaFunc)>0) $s .= ' OnChange="'.$JavaFunc.'"';
		$s .= '>'."\n";
		$found = false;
		for ($i=0; $i<count($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]); $i++)
		{
			$found = (IntVal($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["ID"]) == IntVal($sValue));
			$s1 .= '<option value="'.$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["ID"].'"'.($found ? ' selected':'').'>'.(($bFullName)?("[".$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["ID"]."] ".$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["NAME"]." (".$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["LID"].")"):($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$i]["NAME"])).'</option>'."\n";
		}
		if (strlen($sDefaultValue)>0) 
			$s .= "<option value='' ".($found ? "" : "selected").">".htmlspecialchars($sDefaultValue)."</option>";
		return $s.$s1.'</select>';
	}
}
?>