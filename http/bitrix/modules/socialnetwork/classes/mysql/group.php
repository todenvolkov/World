<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/group.php");

class CSocNetGroup extends CAllSocNetGroup
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields)
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

		if (!CSocNetGroup::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetGroupAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;

		CFile::SaveForDB($arFields, "IMAGE_ID", "socialnetwork");

		$arInsert = $DB->PrepareInsert("b_sonet_group", $arFields);

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
				"INSERT INTO b_sonet_group(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());

			$events = GetModuleEvents("socialnetwork", "OnSocNetGroupAdd");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if ($ID > 0)
			{
				$GLOBALS["USER_FIELD_MANAGER"]->Update("SONET_GROUP", $ID, $arFields);

				if (CModule::IncludeModule("search"))
				{
					$arGroupNew = CSocNetGroup::GetByID($ID);
					if ($arGroupNew)
					{
						if ($arGroupNew["ACTIVE"] == "Y")
						{
							$BODY = CSocNetTextParser::killAllTags($arGroupNew["~DESCRIPTION"]);
							$BODY .= $GLOBALS["USER_FIELD_MANAGER"]->OnSearchIndex("SONET_GROUP", $ID);

							$arSearchIndex = array(
								"SITE_ID" => array($arGroupNew["SITE_ID"] => str_replace("#group_id#", $ID, COption::GetOptionString("socialnetwork", "group_path_template", "/workgroups/group/#group_id#/", $arGroupNew["SITE_ID"]))),
								"LAST_MODIFIED" => $arGroupNew["DATE_ACTIVITY"],
								"PARAM1" => $arGroupNew["SUBJECT_ID"],
								"PARAM2" => $ID,
								"PARAM3" => "GROUP",
								"PERMISSIONS" => (
									$arGroupNew["VISIBLE"] == "Y"?
										array('G2')://public
										array(
											'SG_'.$ID.'_A',//admins
											'SG_'.$ID.'_E',//moderators
											'SG_'.$ID.'_K',//members
										)
								),
								"PARAMS" =>array(
									"socnet_group" => $ID,
								),
								"TITLE" => $arGroupNew["~NAME"],
								"BODY" => $BODY,
								"TAGS" => $arGroupNew["~KEYWORDS"],
							);

							CSearch::Index("socialnetwork", "G".$ID, $arSearchIndex, True);
						}
					}
				}
			}
		}

		return $ID;
	}

	function Update($ID, $arFields, $bAutoSubscribe = true)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arGroupOld = CSocNetGroup::GetByID($ID);
		if (!$arGroupOld)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_NO_GROUP"), "ERROR_NO_GROUP");
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

		if (!CSocNetGroup::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetGroupUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		CFile::SaveForDB($arFields, "IMAGE_ID", "socialnetwork");

		$strUpdate = $DB->PrepareUpdate("b_sonet_group", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate) > 0)
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_group SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			unset($GLOBALS["SONET_GROUP_CACHE"][$ID]);

			$GLOBALS["USER_FIELD_MANAGER"]->Update("SONET_GROUP", $ID, $arFields);

			$events = GetModuleEvents("socialnetwork", "OnSocNetGroupUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if (CModule::IncludeModule("search"))
			{
				$arGroupNew = CSocNetGroup::GetByID($ID);
				if ($arGroupNew)
				{
					if ($arGroupNew["ACTIVE"] == "N" && $arGroupOld["ACTIVE"] == "Y")
					{
						CSearch::DeleteIndex("socialnetwork", "G".$ID);
					}
					elseif ($arGroupNew["ACTIVE"] == "Y")
					{
						$BODY = CSocNetTextParser::killAllTags($arGroupNew["~DESCRIPTION"]);
						$BODY .= $GLOBALS["USER_FIELD_MANAGER"]->OnSearchIndex("SONET_GROUP", $ID);

						$arSearchIndex = array(
							"SITE_ID" => array($arGroupNew["SITE_ID"] => str_replace("#group_id#", $ID, COption::GetOptionString("socialnetwork", "group_path_template", "/workgroups/group/#group_id#/", $arGroupNew["SITE_ID"]))),
							"LAST_MODIFIED" => $arGroupNew["DATE_ACTIVITY"],
							"PARAM1" => $arGroupNew["SUBJECT_ID"],
							"PARAM2" => $ID,
							"PARAM3" => "GROUP",
							"PERMISSIONS" => (
								$arGroupNew["VISIBLE"] == "Y"?
									array('G2')://public
									array(
										'SG_'.$ID.'_A',//admins
										'SG_'.$ID.'_E',//moderators
										'SG_'.$ID.'_K',//members
									)
							),
							"PARAMS" =>array(
								"socnet_group" => $ID,
							),
							"TITLE" => $arGroupNew["~NAME"],
							"BODY" => $BODY,
							"TAGS" => $arGroupNew["~KEYWORDS"],
						);

						CSearch::Index("socialnetwork", "G".$ID, $arSearchIndex, True);
					}

					if ($arGroupNew["OPENED"] == "Y" && $arGroupOld["OPENED"] == "N")
					{
						$dbRequests = CSocNetUserToGroup::GetList(
							array(),
							array(
								"GROUP_ID" => $ID,
								"ROLE" => SONET_ROLES_REQUEST,
								"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER
							),
							false,
							false,
							array("ID")
						);
						if ($dbRequests)
						{
							$arIDs = array();
							while ($arRequests = $dbRequests->GetNext())
								$arIDs[] = $arRequests["ID"];

							CSocNetUserToGroup::ConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), $ID, $arIDs, $bAutoSubscribe);
						}
					}
				}
			}
		}
		elseif(!$GLOBALS["USER_FIELD_MANAGER"]->Update("SONET_GROUP", $ID, $arFields))
		{
			$ID = False;
		}

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
 		global $DB, $USER_FIELD_MANAGER;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "SITE_ID", "NAME", "DESCRIPTION", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "VISIBLE", "OPENED", "CLOSED", "SUBJECT_ID", "OWNER_ID", "KEYWORDS", "IMAGE_ID", "NUMBER_OF_MEMBERS", "INITIATE_PERMS", "SPAM_PERMS", "DATE_ACTIVITY", "SUBJECT_NAME");

		static $arFields = array(
			"ID" => Array("FIELD" => "G.ID", "TYPE" => "int"),
			"SITE_ID" => Array("FIELD" => "G.SITE_ID", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string"),
			"DESCRIPTION" => Array("FIELD" => "G.DESCRIPTION", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "G.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "G.DATE_UPDATE", "TYPE" => "datetime"),
			"DATE_ACTIVITY" => Array("FIELD" => "G.DATE_ACTIVITY", "TYPE" => "datetime"),
			"ACTIVE" => Array("FIELD" => "G.ACTIVE", "TYPE" => "string"),
			"VISIBLE" => Array("FIELD" => "G.VISIBLE", "TYPE" => "string"),
			"OPENED" => Array("FIELD" => "G.OPENED", "TYPE" => "string"),
			"CLOSED" => Array("FIELD" => "G.CLOSED", "TYPE" => "string"),
			"SUBJECT_ID" => Array("FIELD" => "G.SUBJECT_ID", "TYPE" => "int"),
			"OWNER_ID" => Array("FIELD" => "G.OWNER_ID", "TYPE" => "int"),
			"KEYWORDS" => Array("FIELD" => "G.KEYWORDS", "TYPE" => "string"),
			"IMAGE_ID" => Array("FIELD" => "G.IMAGE_ID", "TYPE" => "int"),
			"NUMBER_OF_MEMBERS" => Array("FIELD" => "G.NUMBER_OF_MEMBERS", "TYPE" => "int"),
			"INITIATE_PERMS" => Array("FIELD" => "G.INITIATE_PERMS", "TYPE" => "string"),
			"SPAM_PERMS" => Array("FIELD" => "G.SPAM_PERMS", "TYPE" => "string"),
 			"SUBJECT_NAME" => Array("FIELD" => "S.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group_subject S ON (G.SUBJECT_ID = S.ID)"),
			"OWNER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)"),
			"OWNER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (G.OWNER_ID = U.ID)")
		);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, array("ENTITY_ID" => "SONET_GROUP"));

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
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

		$checkPermissions = Array_Key_Exists("CHECK_PERMISSIONS", $arFilter);

		if ($checkPermissions)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ".
				"WHERE G.VISIBLE = 'Y' ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "AND ".$arSqls["WHERE"]." ";

			$strSql .= "UNION ".
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	INNER JOIN b_sonet_user2group UG ON (G.ID = UG.GROUP_ID AND UG.USER_ID = ".IntVal($arFilter["CHECK_PERMISSIONS"])." AND UG.ROLE <= '".$DB->ForSql(SONET_ROLES_USER, 1)."') ".
				"	".$arSqls["FROM"]." ".
				"WHERE G.VISIBLE = 'N' ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "AND ".$arSqls["WHERE"]." ";
			$strSql .= " ";

			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			if (strlen($arSqls["ORDERBY"]) > 0)
				$strSql .= "ORDER BY ".Str_Replace(array(" G.", " UG.", " S."), array(" ", " ", " "), " ".$arSqls["ORDERBY"])." ";
		}
		else
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
			if (strlen($arSqls["ORDERBY"]) > 0)
				$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		}

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_group G ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0 || $checkPermissions)
				$strSql_tmp .= "WHERE ".($checkPermissions ? "G.VISIBLE = 'Y'" : "1 = 1").(strlen($arSqls["WHERE"]) > 0 ? " AND " : "").$arSqls["WHERE"]." ";
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

			if ($checkPermissions)
			{
				$strSql_tmp =
					"SELECT COUNT('x') as CNT ".
					"FROM b_sonet_group G ".
					"	INNER JOIN b_sonet_user2group UG ON (G.ID = UG.GROUP_ID AND UG.USER_ID = ".IntVal($arFilter["CHECK_PERMISSIONS"])." AND UG.ROLE <= '".$DB->ForSql(SONET_ROLES_USER, 1)."') ".
					"	".$arSqls["FROM"]." ".
					"WHERE G.VISIBLE = 'N' ";
				if (strlen($arSqls["WHERE"]) > 0)
					$strSql_tmp .= "AND ".$arSqls["WHERE"]." ";
				if (strlen($arSqls["GROUPBY"]) > 0)
					$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

				//echo "!2.2!=".htmlspecialchars($strSql_tmp)."<br>";

				$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if (strlen($arSqls["GROUPBY"]) <= 0)
				{
					if ($arRes = $dbRes->Fetch())
						$cnt += $arRes["CNT"];
				}
				else
				{
					// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
					$cnt += $dbRes->SelectedRowsCount();
				}
			}

			$dbRes = new CDBResult();

			//echo "!2.3!=".htmlspecialchars($strSql)."<br>";

			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_GROUP"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("SONET_GROUP"));
		}

		return $dbRes;
	}
}
?>