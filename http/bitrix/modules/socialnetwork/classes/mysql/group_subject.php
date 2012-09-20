<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/group_subject.php");

class CSocNetGroupSubject extends CAllSocNetGroupSubject
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSocNetGroupSubject::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sonet_group_subject", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($arInsert[0]) > 0)
				$arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if (strlen($arInsert[1]) > 0)
				$arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$ID = false;
		if (strlen($arInsert[0]) > 0)
		{
			$strSql =
				"INSERT INTO b_sonet_group_subject(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());

			if (CACHED_b_sonet_group_subjects != false)
				$CACHE_MANAGER->CleanDir("b_sonet_group_subjects");
		}

		return $ID;
	}

	
	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	function GetList($arOrder = Array("SORT" => "ASC", "ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
 		global $DB, $CACHE_MANAGER;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "SITE_ID", "NAME", "SORT");

		$bShouldBeCached = false;
		$cacheId = "";
		if (CACHED_b_sonet_group_subjects != false)
		{
			if ($arSelectFields == false && $arNavStartParams == false && $arGroupBy == false)
			{
				$bFilterByID = array_key_exists("ID", $arFilter);
				$bFilterBySite = array_key_exists("SITE_ID", $arFilter);
				if (count($arFilter) == 1 && ($bFilterByID || $bFilterBySite))
				{
					$bShouldBeCached = true;
					$cacheId = "b_sonet_group_subjects".md5(serialize($arOrder));
					if ($CACHE_MANAGER->Read(CACHED_b_sonet_group_subjects, $cacheId, "b_sonet_group_subjects"))
					{
						$arResult = $CACHE_MANAGER->Get($cacheId);

						$arReturnValue = array();
						for ($i = 0; $i < count($arResult); $i++)
						{
							if ($bFilterByID && $arResult[$i]["ID"] == $arFilter["ID"])
								$arReturnValue[] = $arResult[$i];
							if ($bFilterBySite && $arResult[$i]["SITE_ID"] == $arFilter["SITE_ID"])
								$arReturnValue[] = $arResult[$i];
						}

						$res = new CDBResult;
						$res->InitFromArray($arResult);
						return $res;
					}
				}
			}
		}

		static $arFields = array(
			"ID" => Array("FIELD" => "S.ID", "TYPE" => "int"),
			"SITE_ID" => Array("FIELD" => "S.SITE_ID", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "S.NAME", "TYPE" => "string"),
			"SORT" => Array("FIELD" => "S.SORT", "TYPE" => "int"),
		);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group_subject S ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}


		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_group_subject S ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_group_subject S ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialchars($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialchars($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			//$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (CACHED_b_sonet_group_subjects == false || !$bShouldBeCached)
			{
				$dbRes = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
			else
			{
				$arResult = array();
				$dbRes = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				while ($ar = $dbRes->Fetch())
					$arResult[] = $ar;

				$CACHE_MANAGER->Set($cacheId, $arResult);

				$dbRes = new CDBResult;
				$dbRes->InitFromArray($arResult);
			}
		}

		return $dbRes;
	}
}
?>