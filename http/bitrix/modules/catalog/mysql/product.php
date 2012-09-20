<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/product.php");

/***********************************************************************/
/***********  CCatalogProduct  *****************************************/
/***********************************************************************/
class CCatalogProduct extends CAllCatalogProduct
{
	function Add($arFields)
	{
		global $DB;

		$arFields["ID"] = IntVal($arFields["ID"]);
		if ($arFields["ID"]<=0)
			return false;

		$db_result = $DB->Query("SELECT 'x' FROM b_catalog_product WHERE ID = ".$arFields["ID"]." ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($db_result->Fetch())
		{
			CCatalogProduct::Update($arFields["ID"], $arFields);
		}
		else
		{
			if (!CCatalogProduct::CheckFields("ADD", $arFields, 0))
				return False;

			$arInsert = $DB->PrepareInsert("b_catalog_product", $arFields);

			$strSql =
				"INSERT INTO b_catalog_product(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$events = GetModuleEvents("sale", "OnProductAdd");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($arFields["ID"], $arFields));
		}

		return true;
	}

	function ParseQueryBuildField($field)
	{
		$field = strtoupper($field);
		if(substr($field, 0, 8)!=="CATALOG_")
			return false;

		$iNum = 0;
		$field = substr($field, 8);
		$p = strrpos($field, "_");
		if($p>0)
		{
			$iNum = IntVal(substr($field, $p+1));
			if($iNum>0)
				$field = substr($field, 0, $p);
		}
		return Array("FIELD"=>$field, "NUM"=>$iNum);
	}

	function GetQueryBuildArrays($arOrder, $arFilter, $arSelect)
	{
		global $DB, $USER;

		$sResSelect = "";
		$sResFrom = "";
		$sResWhere = "";
		$arResOrder = array();
		$arJoinGroup = Array();

		$arOrderTmp = Array();
		foreach ($arOrder as $key=>$val)
		{
			foreach ($val as $by=>$order)
			{
				if($arField = CCatalogProduct::ParseQueryBuildField($by))
				{
					$inum = $arField["NUM"];
					$by = $arField["FIELD"];

					$order = strtoupper($order);
					if ($order!="DESC") $order = "ASC";

					if ($by == "PRICE") $res = " CAT_P".$inum.".PRICE ".$order." ";
					elseif ($by == "CURRENCY") $res = " CAT_P".$inum.".CURRENCY ".$order." ";
					elseif ($by == "QUANTITY") { $arResOrder[$key] = " CAT_PR.QUANTITY ".$order." "; continue; }
					else $res = " CAT_P".$inum.".ID ".$order." ";

					if(!is_array($arOrderTmp[$inum]))
						$arOrderTmp[$inum] = Array();
					$arOrderTmp[$inum][$key] = $res;
					$arJoinGroup[] = $inum;
				}
			}
		}

		$arWhereTmp = Array();
		$arAddJoinOn = array();
		
		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i=0; $i<count($filter_keys); $i++)
		{
			$key = strtoupper($filter_keys[$i]);
			$val = $arFilter[$filter_keys[$i]];

			$res = CIBlock::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			if($arField = CCatalogProduct::ParseQueryBuildField($key))
			{
				$key = $arField["FIELD"];
				$inum = $arField["NUM"];

				$res = "";
				switch($key)
				{
				case "PRODUCT_ID":
					$res = CIBlock::FilterCreate("CAT_P".$inum.".PRODUCT_ID", $val, "number", $cOperationType);
					break;
				case "CATALOG_GROUP_ID":
					$res = CIBlock::FilterCreate("CAT_P".$inum.".CATALOG_GROUP_ID", $val, "number", $cOperationType);
					break;
				case "CURRENCY":
					$res = CIBlock::FilterCreate("CAT_P".$inum.".CURRENCY", $val, "string", $cOperationType);
					break;
				case "SHOP_QUANTITY":
					$res = ' 1=1 ';
					$arAddJoinOn[$inum] = 
						(($cOperationType=="N") ? " NOT " : " ").
						" ((CAT_P".$inum.".QUANTITY_FROM <= ".IntVal($val)." OR CAT_P".$inum.".QUANTITY_FROM IS NULL) AND (CAT_P".$inum.".QUANTITY_TO >= ".IntVal($val)." OR CAT_P".$inum.".QUANTITY_TO IS NULL)) ";
					break;
				case "PRICE":
					$res = CIBlock::FilterCreate("CAT_P".$inum.".PRICE", $val, "number", $cOperationType);
					break;
				case "QUANTITY":
					$res = CIBlock::FilterCreate("CAT_PR.QUANTITY", $val, "number", $cOperationType);
					break;
				case "AVAILABLE":
					$res = 
						(($cOperationType=="N") ? " NOT " : " ").
						" (CAT_PR.QUANTITY>0 OR CAT_PR.QUANTITY_TRACE<>'Y') ";
					break;
				}

				if($res!="")
				{
					if(!is_array($arWhereTmp[$inum]))
						$arWhereTmp[$inum] = Array();
					$arWhereTmp[$inum][] = $res;
					$arJoinGroup[] = $inum;
				}
			}
		}

		$strSubWhere = "";
		for ($i = 0; $i<count($arSelect); $i++)
		{
			$val = strtoupper($arSelect[$i]);
			$num = IntVal(substr($val, 14));
			if (substr($val, 0, 14)=="CATALOG_GROUP_" && $num>0)
				$strSubWhere .= ",".$num;
		}

		if(count($arJoinGroup)>0)
		{
			for($i=0; $i<count($arJoinGroup); $i++)
				$strSubWhere .= ",".IntVal($arJoinGroup[$i]);
		}

		if (strlen($strSubWhere) > 0)
		{
			$strCacheKey = "P";
			$strCacheKey .= "_".$USER->GetGroups();
			$strCacheKey .= "_".$strSubWhere;
			$strCacheKey .= "_".LANGUAGE_ID;

			$cacheTime = CATALOG_CACHE_DEFAULT_TIME;
			if (defined("CATALOG_CACHE_TIME"))
				$cacheTime = IntVal(CATALOG_CACHE_TIME);

			$GLOBALS["stackCacheManager"]->SetLength("catalog_GetQueryBuildArrays", 50);
			$GLOBALS["stackCacheManager"]->SetTTL("catalog_GetQueryBuildArrays", $cacheTime);
			if ($GLOBALS["stackCacheManager"]->Exist("catalog_GetQueryBuildArrays", $strCacheKey))
			{
				$arResult = $GLOBALS["stackCacheManager"]->Get("catalog_GetQueryBuildArrays", $strCacheKey);
			}
			else
			{
				$strSql = "SELECT CAT_CG.ID, CAT_CGL.NAME as CATALOG_GROUP_NAME, ".
					"	IF(CAT_CGG.ID IS NULL, 'N', 'Y') as CATALOG_CAN_ACCESS, ".
					"	IF(CAT_CGG1.ID IS NULL, 'N', 'Y') as CATALOG_CAN_BUY ".
					"FROM b_catalog_group CAT_CG ".
					"	LEFT JOIN b_catalog_group2group CAT_CGG ON (CAT_CG.ID = CAT_CGG.CATALOG_GROUP_ID AND CAT_CGG.GROUP_ID IN (".$USER->GetGroups().") AND CAT_CGG.BUY <> 'Y') ".
					"	LEFT JOIN b_catalog_group2group CAT_CGG1 ON (CAT_CG.ID = CAT_CGG1.CATALOG_GROUP_ID AND CAT_CGG1.GROUP_ID IN (".$USER->GetGroups().") AND CAT_CGG1.BUY = 'Y') ".
					"	LEFT JOIN b_catalog_group_lang CAT_CGL ON (CAT_CG.ID = CAT_CGL.CATALOG_GROUP_ID AND CAT_CGL.LID = '".LANGUAGE_ID."') ".
					($strSubWhere!="" ? " WHERE CAT_CG.ID IN (".substr($strSubWhere, 1).") " : "" ).
					"GROUP BY CAT_CG.ID ";
				$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$arResult = array();
				while ($arRes = $dbRes->Fetch())
					$arResult[] = $arRes;

				$GLOBALS["stackCacheManager"]->Set("catalog_GetQueryBuildArrays", $strCacheKey, $arResult);
			}

			$arCatGroups = array();

			foreach ($arResult as $key => $row)
			{
				$i = IntVal($row["ID"]);

				if(is_array($arWhereTmp[$i]))
				{
					foreach($arWhereTmp[$i] as $k=>$v)
						if(strlen($v)>0)
							$sResWhere .= " AND ".$v;
				}

				if(is_array($arOrderTmp[$i]))
				{
					foreach($arOrderTmp[$i] as $k=>$v)
						$arResOrder[$k] = $v;
				}

				$sResSelect .= ", CAT_P".$i.".ID as CATALOG_PRICE_ID_".$i.", ".
					" CAT_P".$i.".CATALOG_GROUP_ID as CATALOG_GROUP_ID_".$i.", ".
					" CAT_P".$i.".PRICE as CATALOG_PRICE_".$i.", ".
					" CAT_P".$i.".CURRENCY as CATALOG_CURRENCY_".$i.", ".
					" CAT_P".$i.".QUANTITY_FROM as CATALOG_QUANTITY_FROM_".$i.", ".
					" CAT_P".$i.".QUANTITY_TO as CATALOG_QUANTITY_TO_".$i.", ".
					" '".$DB->ForSql($row["CATALOG_GROUP_NAME"])."' as CATALOG_GROUP_NAME_".$i.", ".
					" '".$DB->ForSql($row["CATALOG_CAN_ACCESS"])."' as CATALOG_CAN_ACCESS_".$i.", ".
					" '".$DB->ForSql($row["CATALOG_CAN_BUY"])."' as CATALOG_CAN_BUY_".$i.", ".
					" CAT_P".$i.".EXTRA_ID as CATALOG_EXTRA_ID_".$i;

				$sResFrom .= " LEFT JOIN b_catalog_price CAT_P".$i." ON (CAT_P".$i.".PRODUCT_ID = BE.ID AND CAT_P".$i.".CATALOG_GROUP_ID = ".$row["ID"].") ";
				
				if (isset($arAddJoinOn[$i]))
					$sResFrom .= ' AND '.$arAddJoinOn[$i];
			}
		}

		$sResSelect .= ", CAT_PR.QUANTITY as CATALOG_QUANTITY, ".
			" CAT_PR.QUANTITY_TRACE as CATALOG_QUANTITY_TRACE, ".
			" CAT_PR.WEIGHT as CATALOG_WEIGHT, ".
			" CAT_VAT.RATE as CATALOG_VAT, ".
			" CAT_PR.VAT_INCLUDED as CATALOG_VAT_INCLUDED, ".
			" CAT_PR.PRICE_TYPE as CATALOG_PRICE_TYPE, ".
			" CAT_PR.RECUR_SCHEME_TYPE as CATALOG_RECUR_SCHEME_TYPE, ".
			" CAT_PR.RECUR_SCHEME_LENGTH as CATALOG_RECUR_SCHEME_LENGTH, ".
			" CAT_PR.TRIAL_PRICE_ID as CATALOG_TRIAL_PRICE_ID, ".
			" CAT_PR.WITHOUT_ORDER as CATALOG_WITHOUT_ORDER, ".
			" CAT_PR.SELECT_BEST_PRICE as CATALOG_SELECT_BEST_PRICE ";

		$sResFrom .= " LEFT JOIN b_catalog_product CAT_PR ON (CAT_PR.ID = BE.ID) ";
		$sResFrom .= " LEFT JOIN b_catalog_iblock CAT_IB ON ((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) AND CAT_IB.IBLOCK_ID = BE.IBLOCK_ID) ";
		$sResFrom .= " LEFT JOIN b_catalog_vat CAT_VAT ON (CAT_VAT.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID)) ";

		if (is_array($arWhereTmp[0]))
		{
			foreach($arWhereTmp[0] as $k=>$v)
				if(strlen($v)>0)
					$sResWhere .= " AND ".$v;
		}

		return array(
			"SELECT" => $sResSelect,
			"FROM" => $sResFrom,
			"WHERE" => $sResWhere,
			"ORDER" => $arResOrder
		);
	}

	// Do not use this function - it is depricated
	function GetListEx($arOrder=Array("SORT"=>"ASC"), $arFilter=Array())
	{
		return false;
		global $DB, $USER;

		$arSqlSearch = CIBlockElement::MkFilter($arFilter);
		$bSections = false;
		if($arSqlSearch["SECTION"]=="Y")
		{
			$bSections = true;
			unset($arSqlSearch["SECTION"]);
		}
		$strSqlSearch = "";
		for ($i=0; $i<count($arSqlSearch); $i++)
			$strSqlSearch .= " AND (".$arSqlSearch[$i].") ";

		$MAX_LOCK = intval(COption::GetOptionString("workflow", "MAX_LOCK_TIME", "60"));
		$uid = intval($USER->GetID());

		$db_groups = CCatalogGroup::GetList(array("SORT" => "ASC"));
		$strSelectPart = "";
		$strFromPart = "";
		$i = -1;
		while ($groups = $db_groups->Fetch())
		{
			$i++;
			$strSelectPart .= ", P".$i.".PRICE as PRICE".$i.", P".$i.".CURRENCY as CURRENCY".$i.", P".$i.".CATALOG_GROUP_ID as CATALOG_GROUP_ID".$i.", P".$i.".ID as PRICE_ID".$i." ";
			$strFromPart .= " LEFT JOIN b_catalog_price P".$i." ON (P".$i.".PRODUCT_ID = BE.ID AND P".$i.".CATALOG_GROUP_ID = ".$groups["ID"].") ";
		}
		$maxInd = $i;

		if (!$USER->IsAdmin())
		{
			$strSql = 
				"SELECT DISTINCT BE.*, ".
				"	".$DB->DateToCharFunction("BE.TIMESTAMP_X")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("BE.ACTIVE_FROM", "SHORT")." as ACTIVE_FROM, ".
				"	".$DB->DateToCharFunction("BE.ACTIVE_TO", "SHORT")." as ACTIVE_TO, ".
				"	".$DB->DateToCharFunction("BE.WF_DATE_LOCK")." as WF_DATE_LOCK, ".
				"	L.DIR as LANG_DIR, B.DETAIL_PAGE_URL, B.LIST_PAGE_URL, ".
				"	CAP.QUANTITY, CAP.QUANTITY_TRACE, CAP.WEIGHT, ".
				"	CAP.VAT_ID, CAP.VAT_INCLUDED, ".
				"	CAP.PRICE_TYPE, CAP.RECUR_SCHEME_TYPE, CAP.RECUR_SCHEME_LENGTH, CAP.TRIAL_PRICE_ID, ".
				"	CAP.WITHOUT_ORDER, CAP.SELECT_BEST_PRICE ".
				"	".$strSelectPart." ".
				"FROM b_iblock_element BE, b_lang L, ".
				($bSections?"b_iblock_section_element BSE,":"").
				"	b_iblock B ".
				"	LEFT JOIN b_iblock_group IBG ON IBG.IBLOCK_ID = B.ID ".
				"	LEFT JOIN b_catalog_product CAP ON BE.ID = CAP.ID ".
				"	".$strFromPart." ".
				"WHERE BE.IBLOCK_ID = B.ID ".
				"	AND B.LID = L.LID ".
				($bSections?"	AND BSE.IBLOCK_ELEMENT_ID = BE.ID ":"").
				"	AND IBG.GROUP_ID IN (".$USER->GetGroups().") ".
				"	".CIBlockElement::WF_GetSqlLimit("BE.", $SHOW_NEW)." ".
				"	AND IBG.PERMISSION>='".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
				"	AND (IBG.PERMISSION='X' OR B.ACTIVE='Y') ".
				"	".$strSqlSearch." ";
		}
		else
		{
			$strSql = 
				"SELECT BE.*, ".
				"	".$DB->DateToCharFunction("BE.TIMESTAMP_X")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("BE.ACTIVE_FROM", "SHORT")." as ACTIVE_FROM, ".
				"	".$DB->DateToCharFunction("BE.ACTIVE_TO", "SHORT")." as ACTIVE_TO, ".
				"	".$DB->DateToCharFunction("BE.WF_DATE_LOCK")." as WF_DATE_LOCK, ".
				"	L.DIR as LANG_DIR, B.DETAIL_PAGE_URL, B.LIST_PAGE_URL, ".
				"	CAP.QUANTITY, CAP.QUANTITY_TRACE, CAP.WEIGHT, ".
				"	CAP.VAT_ID, CAP.VAT_INCLUDED, ".
				"	CAP.PRICE_TYPE, CAP.RECUR_SCHEME_TYPE, CAP.RECUR_SCHEME_LENGTH, CAP.TRIAL_PRICE_ID, ".
				"	CAP.WITHOUT_ORDER, CAP.SELECT_BEST_PRICE ".
				"	".$strSelectPart." ".
				"FROM  b_iblock B, b_lang L, ".
				($bSections?"b_iblock_section_element BSE,":"").
				"	b_iblock_element BE ".
				"	LEFT JOIN b_catalog_product CAP ON BE.ID = CAP.ID ".
				"	".$strFromPart." ".
				"WHERE BE.IBLOCK_ID = B.ID ".
				($bSections?"	AND BSE.IBLOCK_ELEMENT_ID = BE.ID ":"").
				"	".CIBlockElement::WF_GetSqlLimit("BE.",$SHOW_NEW)." ".
				"	AND B.LID = L.LID ".
				"	".$strSqlSearch." ";
		}

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID")				$arSqlOrder[] = " BE.ID ".$order." ";
			elseif ($by == "SECTION")		$arSqlOrder[] = " BE.IBLOCK_SECTION_ID ".$order." ";
			elseif ($by == "NAME")			$arSqlOrder[] = " BE.NAME ".$order." ";
			elseif ($by == "STATUS")		$arSqlOrder[] = " BE.WF_STATUS_ID ".$order." ";
			elseif ($by == "MODIFIED_BY")	$arSqlOrder[] = " BE.MODIFIED_BY ".$order." ";
			elseif ($by == "ACTIVE")		$arSqlOrder[] = " BE.ACTIVE ".$order." ";
			elseif ($by == "ACTIVE_FROM")	$arSqlOrder[] = " BE.ACTIVE_FROM ".$order." ";
			elseif ($by == "ACTIVE_TO")	$arSqlOrder[] = " BE.ACTIVE_TO ".$order." ";
			elseif ($by == "SORT")			$arSqlOrder[] = " BE.SORT ".$order." ";
			elseif (substr($by, 0, 5) == "PRICE" && IntVal(substr($by, 5))<=$maxInd)
			{
				$indx = IntVal(substr($by, 5));
				$arSqlOrder[] = " P".$indx.".PRICE ".$order." ";
			}
			elseif (substr($by, 0, 8) == "CURRENCY" && IntVal(substr($by, 8))<=$maxInd)
			{
				$indx = IntVal(substr($by, 8));
				$arSqlOrder[] = " P".$indx.".CURRENCY ".$order." ";
			}
			else
			{
				$arSqlOrder[] = " BE.ID ".$order." ";
				$by = "ID";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}
		$strSql .= $strSqlOrder;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $res;
	}
	
	function GetVATInfo($PRODUCT_ID)
	{
		global $DB;
		
		$query = "
SELECT CAT_VAT.*, CAT_PR.VAT_INCLUDED
FROM b_catalog_product CAT_PR
LEFT JOIN b_iblock_element BE ON (BE.ID = CAT_PR.ID) 
LEFT JOIN b_catalog_iblock CAT_IB ON ((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) AND CAT_IB.IBLOCK_ID = BE.IBLOCK_ID) 
LEFT JOIN b_catalog_vat CAT_VAT ON (CAT_VAT.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID)) 
WHERE CAT_PR.ID = '".intval($PRODUCT_ID)."'
AND CAT_VAT.ACTIVE='Y'
";
		return $DB->Query($query);
	}
}
?>