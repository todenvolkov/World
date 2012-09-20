<?
IncludeModuleLangFile(__FILE__);

class CAllSaleTax
{
	function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && strlen($arFields["LID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGT_EMPTY_SITE"), "ERROR_NO_LID");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGT_EMPTY_NAME"), "ERROR_NO_NAME");
			return false;
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGT_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if ((is_set($arFields, "CODE") || $ACTION=="ADD") && strlen($arFields["CODE"])<=0)
			$arFields["CODE"] = false;

		return true;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);

		if (!CSaleTax::CheckFields("UPDATE", $arFields)) return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_tax", $arFields);
		$strSql = "UPDATE b_sale_tax SET ".
			"	TIMESTAMP_X = ".$DB->GetNowFunction().", ".
			"	".$strUpdate." ".
			"WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$db_taxrates = CSaleTaxRate::GetList(Array(), Array("TAX_ID"=>$ID));
		while ($ar_taxrates = $db_taxrates->Fetch())
		{
			CSaleTaxRate::Delete($ar_taxrates["ID"]);
		}

		$DB->Query("DELETE FROM b_sale_tax_exempt2group WHERE TAX_ID = ".$ID."", true);
		return $DB->Query("DELETE FROM b_sale_tax WHERE ID = ".$ID."", true);
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql =
			"SELECT ID, LID, NAME, CODE, DESCRIPTION, ".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
			"FROM b_sale_tax ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetList($arOrder=Array("NAME"=>"ASC"), $arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i=0; $i<count($filter_keys); $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch (strtoupper($key))
			{
				case "ID":
					$arSqlSearch[] = "T.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "LID":
					$arSqlSearch[] = "T.LID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "CODE":
					$arSqlSearch[] = "T.CODE ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = 
			"SELECT T.ID, T.LID, T.NAME, T.CODE, T.DESCRIPTION, ".$DB->DateToCharFunction("T.TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
			"FROM b_sale_tax T ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if ($order!="ASC")
				$order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " T.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " T.LID ".$order." ";
			elseif ($by == "CODE") $arSqlOrder[] = " T.CODE ".$order." ";
			elseif ($by == "TIMESTAMP_X") $arSqlOrder[] = " T.TIMESTAMP_X ".$order." ";
			else
			{
				$arSqlOrder[] = " T.NAME ".$order." ";
				$by = "NAME";
			}
		}
		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<count($arSqlOrder); $i++)			
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}
		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetExemptList($arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			$arSqlSearch_tmp = array();

			for ($j = 0; $j < count($vals); $j++)
			{
				$val = $vals[$j];

				switch (strtoupper($key))
				{
					case "GROUP_ID":
						$arSqlSearch_tmp[] = "TE2G.GROUP_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
						break;
					case "TAX_ID":
						$arSqlSearch_tmp[] = "TE2G.TAX_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
						break;
				}
			}

			$strSqlSearch_tmp = "";
			for ($j = 0; $j < count($arSqlSearch_tmp); $j++)
			{
				if ($j > 0)
					$strSqlSearch_tmp .= ($bInvert ? " AND " : " OR ");
				$strSqlSearch_tmp .= "(".$arSqlSearch_tmp[$j].")";
			}

			if ($strSqlSearch_tmp != "")
				$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
		}

		$strSqlSearch = "";
		for ($i = 0; $i < count($arSqlSearch); $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = 
			"SELECT TE2G.GROUP_ID, TE2G.TAX_ID ".
			"FROM b_sale_tax_exempt2group TE2G ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$strSql .= $strSqlOrder;
		//echo "!1!=".$strSql.";<br>";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function AddExempt($arFields)
	{
		global $DB;

		$arFields["GROUP_ID"] = IntVal($arFields["GROUP_ID"]);
		$arFields["TAX_ID"] = IntVal($arFields["TAX_ID"]);

		if ($arFields["GROUP_ID"]<=0 || $arFields["TAX_ID"]<=0)
			return False;

		$strSql =
			"INSERT INTO b_sale_tax_exempt2group(GROUP_ID, TAX_ID) ".
			"VALUES(".$arFields["GROUP_ID"].", ".$arFields["TAX_ID"].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return True;
	}

	function DeleteExempt($arFields)
	{
		global $DB;

		$strSql = "DELETE FROM b_sale_tax_exempt2group WHERE ";

		$arSqlSearch = Array();

		if (!is_array($arFields))
			return False;
		else
			$filter_keys = array_keys($arFields);

		for ($i=0; $i<count($filter_keys); $i++)
		{
			$val = $arFields[$filter_keys[$i]];
			if (IntVal($val)<=0) continue;
			$key = $filter_keys[$i];
			$arSqlSearch[] = " ".$key." = ".IntVal($val)." ";
		}

		if (count($arSqlSearch)<=0)
			return False;

		$strSqlSearch = "";
		for ($i=0; $i<count($arSqlSearch); $i++)
		{
			if ($i==0) $strSqlSearch .= " ";
			else $strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql .= $strSqlSearch;

		return $DB->Query($strSql, true);
	}
}
?>