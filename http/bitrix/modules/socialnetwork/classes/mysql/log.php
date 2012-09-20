<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log.php");

class CSocNetLog extends CAllSocNetLog
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields, $bSendEvent = true)
	{
		global $DB;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSocNetLog::CheckFields("ADD", $arFields))
			return false;

		if (
			array_key_exists("ENTITY_TYPE", $arFields) 
			&& $arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& array_key_exists("ENTITY_ID", $arFields) && intval($arFields["ENTITY_ID"]) > 0 
			&& CSocNetEventUserView::IsEntityEmpty($arFields["ENTITY_TYPE"], $arFields["ENTITY_ID"])
		)
			CSocNetEventUserView::SetGroup($arFields["ENTITY_ID"], true);

		if (
			array_key_exists("ENTITY_TYPE", $arFields) 
			&& $arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& array_key_exists("ENTITY_ID", $arFields) && intval($arFields["ENTITY_ID"]) > 0 
			&& CSocNetEventUserView::IsEntityEmpty($arFields["ENTITY_TYPE"], $arFields["ENTITY_ID"])
		)
			CSocNetEventUserView::SetUser($arFields["ENTITY_ID"], false, false, true);
			
		$arInsert = $DB->PrepareInsert("b_sonet_log", $arFields);

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
				"INSERT INTO b_sonet_log(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());

			if ($ID > 0 && $bSendEvent)
				CSocNetLog::SendEvent($ID, "SONET_NEW_EVENT");
		}

		return $ID;
	}

	
	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSocNetLog::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_log", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate) > 0)
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_log SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else
		{
			$ID = False;
		}

		return $ID;
	}

	function ClearOld($days = 7)
	{
		global $DB;

		$days = IntVal($days);
		if ($days <= 0)
			$days = 7;

		$bSuccess = $DB->Query("DELETE FROM b_sonet_log WHERE LOG_DATE < DATE_SUB(NOW(), INTERVAL ".$days." DAY)", true);

		return $bSuccess;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arParams = array())
	{
 		global $DB, $arSocNetAllowedEntityTypes;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array(
				"ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "CALLBACK_FUNC", "EXTERNAL_ID", "SITE_ID", "PARAMS", 
				"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_INITIATE_PERMS", "GROUP_VISIBLE", "GROUP_OPENED", 
				"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", 
				"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN"
			);

		static $arFields = array(
			"ID" => Array("FIELD" => "L.ID", "TYPE" => "int"),
			"TMP_ID" => Array("FIELD" => "L.TMP_ID", "TYPE" => "int"),
			"ENTITY_TYPE" => Array("FIELD" => "L.ENTITY_TYPE", "TYPE" => "string"),
			"ENTITY_ID" => Array("FIELD" => "L.ENTITY_ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "L.USER_ID", "TYPE" => "int"),
			"EVENT_ID" => Array("FIELD" => "L.EVENT_ID", "TYPE" => "string"),
			"LOG_DATE" => Array("FIELD" => "L.LOG_DATE", "TYPE" => "datetime"),
			"TITLE_TEMPLATE" => Array("FIELD" => "L.TITLE_TEMPLATE", "TYPE" => "string"),
			"TITLE" => Array("FIELD" => "L.TITLE", "TYPE" => "string"),
			"MESSAGE" => Array("FIELD" => "L.MESSAGE", "TYPE" => "string"),
			"TEXT_MESSAGE" => Array("FIELD" => "L.TEXT_MESSAGE", "TYPE" => "string"),
			"URL" => Array("FIELD" => "L.URL", "TYPE" => "string"),
			"MODULE_ID" => Array("FIELD" => "L.MODULE_ID", "TYPE" => "string"),
			"CALLBACK_FUNC" => Array("FIELD" => "L.CALLBACK_FUNC", "TYPE" => "string"),
			"EXTERNAL_ID" => Array("FIELD" => "L.EXTERNAL_ID", "TYPE" => "string"),
			"SITE_ID" => Array("FIELD" => "L.SITE_ID", "TYPE" => "string"),
			"PARAMS" => Array("FIELD" => "L.PARAMS", "TYPE" => "string"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"GROUP_OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string", "FROM" => "LEFT JOIN b_sonet_group G ON (L.ENTITY_TYPE = 'G' AND L.ENTITY_ID = G.ID)"),
			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (L.ENTITY_TYPE = 'U' AND L.ENTITY_ID = U.ID)"),
			"CREATED_BY_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
			"CREATED_BY_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U1 ON L.USER_ID = U1.ID"),
		);

		if (array_key_exists("EVENT_ID", $arFilter))
		{
			if (!is_array($arFilter["EVENT_ID"]))
				$arFilter["EVENT_ID"] = array($arFilter["EVENT_ID"]);
				
			$arTmp = array();
			foreach ($arFilter["EVENT_ID"] as $event_id)
			{
				$bFound = false;
				foreach ($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
				{
					if (
						$event_id_tmp == $event_id
						&& array_key_exists("FULL_SET", $arEventTmp)
						&& is_array($arEventTmp["FULL_SET"])
					)
					{
						$bFound = true;
						$arTmp = array_merge($arTmp, $arEventTmp["FULL_SET"]);
						break;
					}
				}				
				
				if (!$bFound)
				{
					foreach ($GLOBALS["arSocNetFeaturesSettings"] as $feature_id => $arFeature)
					{
						if (
							array_key_exists("subscribe_events", $arFeature)
							&& array_key_exists($event_id, $arFeature["subscribe_events"])
							&& array_key_exists("FULL_SET", $arFeature["subscribe_events"][$event_id])
							&& is_array($arFeature["subscribe_events"][$event_id]["FULL_SET"])
						)
						{
							$bFound = true;
							$arTmp = array_merge($arTmp, $arFeature["subscribe_events"][$event_id]["FULL_SET"]);
							break;
						}
					}
				}
				if (!$bFound)
					$arTmp[] = $event_id;
			}
			$arFilter["EVENT_ID"] = $arTmp;
		}

		if (
			array_key_exists("USER_ID", $arFilter) 
			&& !array_key_exists("ENTITY_TYPE", $arFilter)
		)
		{
			$arCBFilterEntityType = array();
			foreach($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"] as $entity_type_tmp => $arEntityTypeTmp)
				if (
					array_key_exists("USE_CB_FILTER", $arEntityTypeTmp)
					&& $arEntityTypeTmp["USE_CB_FILTER"] == "Y"
				)
					$arCBFilterEntityType[] = $entity_type_tmp;
			
			if (is_array($arCBFilterEntityType) && count($arCBFilterEntityType) > 0)
				$arFilter["ENTITY_TYPE"] = $arCBFilterEntityType;
		}

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["RIGHTS"] = "";
		if (!empty($arParams) && array_key_exists("USER_ID", $arParams))
		{
			if (
				array_key_exists("ENTITY_TYPE", $arFilter) && strlen($arFilter["ENTITY_TYPE"]) > 0 
				&& array_key_exists("ENTITY_ID", $arFilter) && intval($arFilter["ENTITY_ID"]) > 0 
				&& $arFilter["ENTITY_TYPE"] == SONET_ENTITY_GROUP 
				&& CSocNetEventUserView::IsEntityEmpty($arFilter["ENTITY_TYPE"], $arFilter["ENTITY_ID"]))
					CSocNetEventUserView::SetGroup($arFilter["ENTITY_ID"], true);
			
			switch ($arParams["USER_ID"])
			{
				case "A":
					break;
				case false:
					$arSqls["RIGHTS"] = CSocNetEventUserView::CheckPermissions("L", false);
					break;
				default:
					$arSqls["RIGHTS"] = CSocNetEventUserView::CheckPermissions("L", intval($arParams["USER_ID"]));
					break;
			}
			
			if ($arParams["USE_SUBSCRIBE"] == "Y" && intval($arParams["SUBSCRIBE_USER_ID"]) > 0)
				$arSqls["SUBSCRIBE"] = CSocNetLogEvents::GetSQL(
										$arParams["SUBSCRIBE_USER_ID"], 
										$arParams["MY_ENTITIES"], 
										$arParams["TRANSPORT"],
										$arParams["VISIBLE"]										
									);
		}
	
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_log L ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["RIGHTS"]) > 0)
				$strSql .= $arSqls["RIGHTS"]." ";
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

		if (
			is_array($arParams) 
			&& array_key_exists("MIN_ID_JOIN", $arParams)
			&& $arParams["MIN_ID_JOIN"]			
		)
			$strMinIDJoin = 
				"INNER JOIN 
				(
					SELECT MIN(ID) AS ID, TMP_ID
					FROM b_sonet_log L ".
					(strlen($arSqls["SUBSCRIBE"]) > 0 ? "WHERE (".$arSqls["SUBSCRIBE"].") " : "").
					"GROUP BY TMP_ID
				) L1 
				ON L1.ID = L.ID ";
		else
			$strMinIDJoin = "";

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_log L ".
			$strMinIDJoin.
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["RIGHTS"]) > 0)
			$strSql .= $arSqls["RIGHTS"]." ";
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
				"FROM b_sonet_log L ".
				$strMinIDJoin.
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["RIGHTS"]) > 0)
				$strSql_tmp .= $arSqls["RIGHTS"]." ";
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
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
	
}
?>