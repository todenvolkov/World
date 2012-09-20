<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/general/blog_comment.php");

class CBlogComment extends CAllBlogComment
{
	/*************** ADD, UPDATE, DELETE *****************/
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

		if (!CBlogComment::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("blog", "OnBeforeCommentAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array(&$arFields))===false)
				return false;

		$arInsert = $DB->PrepareInsert("b_blog_comment", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($arInsert[0]) > 0)
				$arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if (strlen($arInsert[1]) > 0)
				$arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$ID = False;
		if (strlen($arInsert[0]) > 0)
		{
			$strSql =
				"INSERT INTO b_blog_comment(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());
		}

		if ($ID)
		{
			$arComment = CBlogComment::GetByID($ID);
			if($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
				CBlogPost::Update($arComment["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS + 1"));
		}
		
		$db_events = GetModuleEvents("blog", "OnCommentAdd");
		while ($arEvent = $db_events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
		
		if (CModule::IncludeModule("search"))
		{
			if (CBlogUserGroup::GetGroupPerms(1, $arComment["BLOG_ID"], $arComment["POST_ID"], BLOG_PERMS_POST) >= BLOG_PERMS_READ)
			{
				$arBlog = CBlog::GetByID($arComment["BLOG_ID"]);
				if($arBlog["SEARCH_INDEX"] == "Y" && $arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
				{
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
					if(strlen($arFields["PATH"]) > 0)
					{
						$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);
						$arCommentSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
					}
					else
					{
						$arCommentSite = array(
							$arGroup["SITE_ID"] => CBlogPost::PreparePath(
									$arBlog["URL"],
									$arComment["POST_ID"],
									$arGroup["SITE_ID"],
									false,
									$arBlog["OWNER_ID"],
									$arBlog["SOCNET_GROUP_ID"]
								)
						);
					}
					
					$arSearchIndex = array(
						"SITE_ID" => $arCommentSite,
						"LAST_MODIFIED" => $arComment["DATE_CREATE"],
						"PARAM1" => "COMMENT",
						"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
						"PERMISSIONS" => array(2),
						"TITLE" => $arComment["TITLE"],
						"BODY" => blogTextParser::killAllTags($arComment["POST_TEXT"]),
						"INDEX_TITLE" => false,
					);
					if($arBlog["USE_SOCNET"] == "Y")
						unset($arSearchIndex["PERMISSIONS"]);
					if(strlen($arComment["TITLE"]) <= 0)
					{
						//$arPost = CBlogPost::GetByID($arComment["POST_ID"]);
						$arSearchIndex["TITLE"] = substr($arSearchIndex["BODY"], 0, 100);
					}


					CSearch::Index("blog", "C".$ID, $arSearchIndex);
				}
			}
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		
		if(strlen($arFields["PATH"]) > 0)
			$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CBlogComment::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("blog", "OnBeforeCommentUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, &$arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_blog_comment", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate) > 0)
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if (strlen($strUpdate) > 0)
		{
			if(is_set($arFields["PUBLISH_STATUS"]) && strlen($arFields["PUBLISH_STATUS"]) > 0)
			{
				$arComment = CBlogComment::GetByID($ID);
				if($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
					CBlogPost::Update($arComment["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS - 1"));
				elseif($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
					CBlogPost::Update($arComment["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS + 1"));
			}
			
			$strSql =
				"UPDATE b_blog_comment SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
			unset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]);
			
			$db_events = GetModuleEvents("blog", "OnCommentUpdate");
			while ($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
			
			if (CModule::IncludeModule("search"))
			{
				$arComment = CBlogComment::GetByID($ID);
				$newPostPerms = CBlogUserGroup::GetGroupPerms(1, $arComment["BLOG_ID"], $arComment["POST_ID"], BLOG_PERMS_POST);
				$arBlog = CBlog::GetByID($arComment["BLOG_ID"]);
				
				if ($arBlog["SEARCH_INDEX"] != "Y" || $arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
				{
					CSearch::Index("blog", "C".$ID,
						array(
							"TITLE" => "",
							"BODY" => ""
						)
					);
				}
				else
				{
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);

					if(strlen($arFields["PATH"]) > 0)
					{
						$arFields["PATH"] = str_replace("#comment_id#", $ID, $arFields["PATH"]);
						$arPostSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
					}
					elseif(strlen($arComment["PATH"]) > 0)
					{
						$arComment["PATH"] = str_replace("#post_id#", $ID, $arComment["PATH"]);
						$arPostSite = array($arGroup["SITE_ID"] => $arComment["PATH"]);
					}
					else
					{
						$arPostSite = array(
							$arGroup["SITE_ID"] => CBlogPost::PreparePath(
									$arBlog["URL"],
									$arComment["POST_ID"],
									$arGroup["SITE_ID"],
									false,
									$arBlog["OWNER_ID"],
									$arBlog["SOCNET_GROUP_ID"]
								)
						);
					}

					$arSearchIndex = array(
						"SITE_ID" => $arPostSite,
						"LAST_MODIFIED" => $arComment["DATE_CREATE"],
						"PARAM1" => "COMMENT",
						"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
						"PERMISSIONS" => array(2),
						"TITLE" => $arComment["TITLE"],
						"BODY" => blogTextParser::killAllTags($arComment["POST_TEXT"]),
					);
					if($arBlog["USE_SOCNET"] == "Y")
						unset($arSearchIndex["PERMISSIONS"]);
					if(strlen($arComment["TITLE"]) <= 0)
					{
						//$arPost = CBlogPost::GetByID($arComment["POST_ID"]);
						$arSearchIndex["TITLE"] = substr($arSearchIndex["BODY"], 0, 100);
					}

					CSearch::Index("blog", "C".$ID, $arSearchIndex, True);
				}
			}

			return $ID;
		}

		return False;
	}

	//*************** SELECT *********************/
	function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT");
		if(in_array("*", $arSelectFields))
			$arSelectFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "DATE_CREATE", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL", "USER", "BLOG_USER_ALIAS", "BLOG_USER_AVATAR", "BLOG_URL", "BLOG_OWNER_ID", "BLOG_SOCNET_GROUP_ID", "BLOG_ACTIVE", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_USE_SOCNET", "PERMS", "PUBLISH_STATUS");
		if((array_key_exists("BLOG_GROUP_SITE_ID", $arFilter) || in_array("BLOG_GROUP_SITE_ID", $arSelectFields)) && !in_array("BLOG_URL", $arSelectFields))
			$arSelectFields[] = "BLOG_URL";

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "C.ID", "TYPE" => "int"),
				"BLOG_ID" => array("FIELD" => "C.BLOG_ID", "TYPE" => "int"),
				"POST_ID" => array("FIELD" => "C.POST_ID", "TYPE" => "int"),
				"PARENT_ID" => array("FIELD" => "C.PARENT_ID", "TYPE" => "int"),
				"AUTHOR_ID" => array("FIELD" => "C.AUTHOR_ID", "TYPE" => "int"),
				"AUTHOR_NAME" => array("FIELD" => "C.AUTHOR_NAME", "TYPE" => "string"),
				"AUTHOR_EMAIL" => array("FIELD" => "C.AUTHOR_EMAIL", "TYPE" => "string"),
				"AUTHOR_IP" => array("FIELD" => "C.AUTHOR_IP", "TYPE" => "string"),
				"AUTHOR_IP1" => array("FIELD" => "C.AUTHOR_IP1", "TYPE" => "string"),
				"TITLE" => array("FIELD" => "C.TITLE", "TYPE" => "string"),
				"POST_TEXT" => array("FIELD" => "C.POST_TEXT", "TYPE" => "string"),
				"DATE_CREATE" => array("FIELD" => "C.DATE_CREATE", "TYPE" => "datetime"),
				"PATH" => array("FIELD" => "C.PATH", "TYPE" => "string"),

				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				"USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (C.AUTHOR_ID = U.ID)"),
				
				"BLOG_USER_ALIAS" => array("FIELD" => "BU.ALIAS", "TYPE" => "string", "FROM" => "LEFT JOIN b_blog_user BU ON (C.AUTHOR_ID = BU.USER_ID)"),
				"BLOG_USER_AVATAR" => array("FIELD" => "BUA.AVATAR", "TYPE" => "int", "FROM" => "LEFT JOIN b_blog_user BUA ON (C.AUTHOR_ID = BUA.USER_ID)"),
				
				"BLOG_URL" => array("FIELD" => "B.URL", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (C.BLOG_ID = B.ID)"),
				"BLOG_OWNER_ID" => array("FIELD" => "BO.OWNER_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BO ON (C.BLOG_ID = BO.ID)"),
				"BLOG_SOCNET_GROUP_ID" => array("FIELD" => "BS.SOCNET_GROUP_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BS ON (C.BLOG_ID = BS.ID)"),
				"BLOG_ACTIVE" => array("FIELD" => "BA.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BA ON (C.BLOG_ID = BA.ID)"),
				"BLOG_GROUP_ID" => array("FIELD" => "BGI.GROUP_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog BGI ON (C.BLOG_ID = BGI.ID)"),
				"BLOG_GROUP_SITE_ID" => array("FIELD" => "BG.SITE_ID", "TYPE" => "string", "FROM" => "
						INNER JOIN b_blog BGS ON (C.BLOG_ID = BGS.ID)
						INNER JOIN b_blog_group BG ON (BGS.GROUP_ID = BG.ID)"),
				"BLOG_USE_SOCNET" => array("FIELD" => "BUS.USE_SOCNET", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BUS ON (C.BLOG_ID = BUS.ID)"),
				"BLOG_NAME" => array("FIELD" => "BN.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BN ON (C.BLOG_ID = BN.ID)"),
				"PERMS" => Array(),
				
				"SOCNET_BLOG_READ" => array("FIELD" => "BSR.BLOG_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog_socnet BSR ON (C.BLOG_ID = BSR.BLOG_ID)"),
				"BLOG_POST_PUBLISH_STATUS" => array("FIELD" => "BP.PUBLISH_STATUS", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
				"PUBLISH_STATUS" => array("FIELD" => "C.PUBLISH_STATUS", "TYPE" => "string"),
				"POST_CODE" => array("FIELD" => "BP.CODE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
				"POST_TITLE" => array("FIELD" => "BP.TITLE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post BP ON (C.POST_ID = BP.ID)"),
			);
				
		if(isset($arFilter["GROUP_CHECK_PERMS"]))
		{
			if(is_array($arFilter["GROUP_CHECK_PERMS"]))
			{
				foreach($arFilter["GROUP_CHECK_PERMS"] as $val)
				{
					if(IntVal($val)>0)
					{
						$arFields["POST_PERM_".$val] = Array(
								"FIELD" => "BUGP".$val.".PERMS", 
								"TYPE" => "string", 
								"FROM" => "LEFT JOIN b_blog_user_group_perms BUGP".$val." 
											ON (C.BLOG_ID = BUGP".$val.".BLOG_ID 
												AND C.POST_ID = BUGP".$val.".POST_ID 
												AND BUGP".$val.".USER_GROUP_ID = ".$val." 
												AND BUGP".$val.".PERMS_TYPE = '".BLOG_PERMS_COMMENT."')"
							);
						$arSelectFields[] = "POST_PERM_".$val;
					}
				}
			}
			else
			{
				if(IntVal($arFilter["GROUP_CHECK_PERMS"])>0)
				{
					$arFields["POST_PERM_".$arFilter["GROUP_CHECK_PERMS"]] = Array(
							"FIELD" => "BUGP.PERMS", 
							"TYPE" => "string", 
							"FROM" => "LEFT JOIN b_blog_user_group_perms BUGP 
										ON (C.BLOG_ID = BUGP.BLOG_ID 
											AND C.POST_ID = BUGP.POST_ID 
											AND BUGP.USER_GROUP_ID = ".$arFilter["GROUP_CHECK_PERMS"]." 
											AND BUGP.PERMS_TYPE = '".BLOG_PERMS_COMMENT."')"
						);
					$arSelectFields[] = "POST_PERM_".$arFilter["GROUP_CHECK_PERMS"];
				}
			}
			unset($arFilter["GROUP_CHECK_PERMS"]);
		}

		$bNeedDistinct = false;
		$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
		if ($blogModulePermissions < "W")
		{	
			$arUserGroups = CBlogUser::GetUserGroups(($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : 0), 0, "Y", BLOG_BY_USER_ID);
			$strUserGroups = "0";
			for ($i = 0; $i < count($arUserGroups); $i++)
				$strUserGroups .= ",".IntVal($arUserGroups[$i]);

			$arFields["PERMS"] = array("FIELD" => "UGP.PERMS", "TYPE" => "char", "FROM" => "INNER JOIN b_blog_user_group_perms UGP ON (C.POST_ID = UGP.POST_ID AND C.BLOG_ID = UGP.BLOG_ID AND UGP.USER_GROUP_ID IN (".$strUserGroups.") AND UGP.PERMS_TYPE = '".BLOG_PERMS_COMMENT."')");
			$bNeedDistinct = true;
		}		
		else
		{
			$arFields["PERMS"] = array("FIELD" => "'W'", "TYPE" => "string");
		}


		$arSqls = CBlog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		if($bNeedDistinct)
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);
		else
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_blog_comment C ".
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
			"FROM b_blog_comment C ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_blog_comment C ".
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
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialchars($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".$arNavStartParams["nTopCount"];

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return $dbRes;
	}
}
?>