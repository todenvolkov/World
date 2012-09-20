<?
IncludeModuleLangFile(__FILE__);

class CAllExtra
{
	function GetByID($ID)
	{
		global $DB;
		$strSql = 
			"SELECT ID, NAME, PERCENTAGE ".
			"FROM b_catalog_extra ".
			"WHERE ID = ".IntVal($ID)." ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($res = $db_res->Fetch())
			return $res;
		return false;
	}


	function GetList(&$by, &$order)
	{
		global $DB;

		$strSql = 
			"SELECT ID, NAME, PERCENTAGE ".
			"FROM b_catalog_extra ";

		if (strtoupper($by) == "NAME") $strSqlOrder = " ORDER BY NAME ";
		elseif (strtoupper($by) == "PERCENTAGE") $strSqlOrder = " ORDER BY PERCENTAGE ";
		else
		{
			$strSqlOrder = " ORDER BY NAME "; 
			$by = "NAME";
		}

		if (strtoupper($order)=="DESC") 
			$strSqlOrder .= " DESC "; 
		else
			$order = "ASC"; 

		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}


	function SelectBox($sFieldName, $sValue, $sDefaultValue = "", $JavaChangeFunc = "", $sAdditionalParams = "")
	{
		if (!isset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]) || !is_array($GLOBALS["MAIN_EXTRA_LIST_CACHE"]) || count($GLOBALS["MAIN_EXTRA_LIST_CACHE"])<1)
		{
			unset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]);
			$l = CExtra::GetList(($by="NAME"), ($order="ASC"));
			while ($l_res = $l->Fetch())
			{
				$GLOBALS["MAIN_EXTRA_LIST_CACHE"][] = $l_res;
			}
		}
		$s = '<select name="'.$sFieldName.'"';
		if (strlen($JavaChangeFunc)>0)
			$s .= ' OnChange="'.$JavaChangeFunc.'"';
		if (strlen($sAdditionalParams)>0)
			$s .= ' '.$sAdditionalParams.' ';
		$s .= '>'."\n";
		$found = false;
		for ($i=0; $i<count($GLOBALS["MAIN_EXTRA_LIST_CACHE"]); $i++)
		{
			$found = (IntVal($GLOBALS["MAIN_EXTRA_LIST_CACHE"][$i]["ID"]) == IntVal($sValue));
			$s1 .= '<option value="'.$GLOBALS["MAIN_EXTRA_LIST_CACHE"][$i]["ID"].'"'.($found ? ' selected':'').'>'.htmlspecialchars($GLOBALS["MAIN_EXTRA_LIST_CACHE"][$i]["NAME"]).' ('.htmlspecialchars($GLOBALS["MAIN_EXTRA_LIST_CACHE"][$i]["PERCENTAGE"]).'%)</option>'."\n";
		}
		if (strlen($sDefaultValue)>0) 
			$s .= "<option value='' ".($found ? "" : "selected").">".htmlspecialchars($sDefaultValue)."</option>";
		return $s.$s1.'</select>';
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$arFields["PERCENTAGE"] = DoubleVal($arFields["PERCENTAGE"]);
		$arFields['NAME'] = trim($arFields['NAME']);
		
		if (strlen($arFields["NAME"])<=0)
		{
			$GLOBALS['APPLICATION']->ThrowException(GetMessage('CAT_EXTRA_ERROR_NONAME'));
			return false;
		}

		$strUpdate = $DB->PrepareUpdate("b_catalog_extra", $arFields);
		$strSql = "UPDATE b_catalog_extra SET ".$strUpdate." WHERE ID = '".intval($ID)."'";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($arFields["RECALCULATE"]=="Y")
		{
			CPrice::ReCalculate("EXTRA", $ID, $arFields["PERCENTAGE"]);
		}

		unset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]);
		return true;
	}


	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		$DB->Query("UPDATE b_catalog_price SET EXTRA_ID = NULL WHERE EXTRA_ID = ".$ID." ");
		unset($GLOBALS["MAIN_EXTRA_LIST_CACHE"]);
		return $DB->Query("DELETE FROM b_catalog_extra WHERE ID = ".$ID." ", true);
	}

}
?>