<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/general/blog_post.php");

class CBlogPost extends CAllBlogPost
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

		if (!CBlogPost::CheckFields("ADD", $arFields))
			return false;
		elseif(!$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("BLOG_POST", 0, $arFields))
			return false;
			
		$db_events = GetModuleEvents("blog", "OnBeforePostAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array(&$arFields))===false)
				return false;

		CFile::SaveForDB($arFields, "ATTACH_IMG", "blog/".$arFields["URL"]);

		$arInsert = $DB->PrepareInsert("b_blog_post", $arFields);

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
				"INSERT INTO b_blog_post(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());
			
			$GLOBALS["USER_FIELD_MANAGER"]->Update("BLOG_POST", $ID, $arFields);
		}

		if ($ID)
		{
			$arPost = CBlogPost::GetByID($ID);
			CBlog::SetStat($arPost["BLOG_ID"]);

			CBlogPost::SetPostPerms($ID, $arFields["PERMS_POST"], BLOG_PERMS_POST);
			CBlogPost::SetPostPerms($ID, $arFields["PERMS_COMMENT"], BLOG_PERMS_COMMENT);
			
			$db_events = GetModuleEvents("blog", "OnPostAdd");
			while ($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
				
			if (CModule::IncludeModule("search"))
			{
				if ($arPost["DATE_PUBLISHED"] == "Y"
					&& $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
					&& CBlogUserGroup::GetGroupPerms(1, $arPost["BLOG_ID"], $ID, BLOG_PERMS_POST) >= BLOG_PERMS_READ)
				{
					$tag = "";
					$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
					if($arBlog["SEARCH_INDEX"] == "Y")
					{
						$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);

						if(strlen($arFields["PATH"]) > 0)
						{
							$arFields["PATH"] = str_replace("#post_id#", $ID, $arFields["PATH"]);
							$arPostSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
						}
						else
						{
							$arPostSite = array(
								$arGroup["SITE_ID"] => CBlogPost::PreparePath(
										$arBlog["URL"],
										$arPost["ID"],
										$arGroup["SITE_ID"],
										false,
										$arBlog["OWNER_ID"],
										$arBlog["SOCNET_GROUP_ID"]
									)
							);						
						}
						
						if(strlen($arPost["CATEGORY_ID"])>0)
						{
							$arC = explode(",", $arPost["CATEGORY_ID"]);
							$arTag = Array();
							foreach($arC as $v)
							{
								$arCategory = CBlogCategory::GetByID($v);
								$arTag[] = $arCategory["NAME"];
							}
							$tag =  implode(",", $arTag);
						}

						$arSearchIndex = array(
							"SITE_ID" => $arPostSite,
							"LAST_MODIFIED" => $arPost["DATE_PUBLISH"],
							"PARAM1" => "POST",
							"PARAM2" => $arPost["BLOG_ID"],
							"PARAM3" => $arPost["ID"],
							"PERMISSIONS" => array(2),
							"TITLE" => $arPost["TITLE"],
							"BODY" => blogTextParser::killAllTags($arPost["DETAIL_TEXT"]),
							"TAGS" => $tag,
						);
						if($arBlog["USE_SOCNET"] == "Y")
							unset($arSearchIndex["PERMISSIONS"]);

						CSearch::Index("blog", "P".$ID, $arSearchIndex);
					}
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
			$arFields["PATH"] = str_replace("#post_id#", $ID, $arFields["PATH"]);
		
		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}
		

		if (!CBlogPost::CheckFields("UPDATE", $arFields, $ID))
			return false;
		elseif(!$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("BLOG_POST", $ID, $arFields))
			return false;

		$db_events = GetModuleEvents("blog", "OnBeforePostUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, &$arFields))===false)
				return false;

		$arOldPost = CBlogPost::GetByID($ID);
		$filePath = $arOldPost["URL"];
		if (isset($arFields["URL"]) && strlen($arFields["URL"]) > 0)
			$filePath = $arFields["URL"];

		if(is_array($arFields["ATTACH_IMG"]))
			CFile::SaveForDB($arFields, "ATTACH_IMG", "blog/".$filePath);

		$strUpdate = $DB->PrepareUpdate("b_blog_post", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate) > 0)
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if (strlen($strUpdate) > 0)
		{
			$oldPostPerms = CBlogUserGroup::GetGroupPerms(1, $arOldPost["BLOG_ID"], $ID, BLOG_PERMS_POST);

			$strSql =
				"UPDATE b_blog_post SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			unset($GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID]);
			
			$GLOBALS["USER_FIELD_MANAGER"]->Update("BLOG_POST", $ID, $arFields);
		}
		else
		{
			$ID = False;
		}

		if ($ID)
		{
			$arNewPost = CBlogPost::GetByID($ID);
			CBlog::SetStat($arNewPost["BLOG_ID"]);
			if ($arNewPost["BLOG_ID"] != $arOldPost["BLOG_ID"])
				CBlog::SetStat($arOldPost["BLOG_ID"]);

			if (is_set($arFields, "PERMS_POST"))
				CBlogPost::SetPostPerms($ID, $arFields["PERMS_POST"], BLOG_PERMS_POST);
			if (is_set($arFields, "PERMS_COMMENT"))
				CBlogPost::SetPostPerms($ID, $arFields["PERMS_COMMENT"], BLOG_PERMS_COMMENT);
				
			$db_events = GetModuleEvents("blog", "OnPostUpdate");
			while ($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, Array($ID, &$arFields));
			
			if (CModule::IncludeModule("search"))
			{
				$newPostPerms = CBlogUserGroup::GetGroupPerms(1, $arNewPost["BLOG_ID"], $ID, BLOG_PERMS_POST);
				$arBlog = CBlog::GetByID($arNewPost["BLOG_ID"]);
				
				if (
					$arOldPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && 
					$oldPostPerms >= BLOG_PERMS_READ 
					&& (
						$arNewPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH || 
						$newPostPerms < BLOG_PERMS_READ
						)
					|| $arBlog["SEARCH_INDEX"] != "Y"
					)
				{
					CSearch::Index("blog", "P".$ID,
						array(
							"TITLE" => "",
							"BODY" => ""
						)
					);
					CSearch::DeleteIndex("blog", false, "COMMENT", $arBlog["ID"]."|".$ID);
				}
				elseif (
					$arNewPost["DATE_PUBLISHED"] == "Y"
					&& $arNewPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
					&& $newPostPerms >= BLOG_PERMS_READ
					&& $arBlog["SEARCH_INDEX"] == "Y"
					)
				{
					$tag = "";
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
					if(strlen($arFields["PATH"]) > 0)
					{
						$arPostSite = array($arGroup["SITE_ID"] => $arFields["PATH"]);
					}
					elseif(strlen($arNewPost["PATH"]) > 0)
					{
						$arNewPost["PATH"] = str_replace("#post_id#", $ID, $arNewPost["PATH"]);
						$arPostSite = array($arGroup["SITE_ID"] => $arNewPost["PATH"]);
					}
					else
					{
						$arPostSite = array(
							$arGroup["SITE_ID"] => CBlogPost::PreparePath(
									$arBlog["URL"],
									$arNewPost["ID"],
									$arGroup["SITE_ID"],
									false,
									$arBlog["OWNER_ID"],
									$arBlog["SOCNET_GROUP_ID"]
								)
						);
					}

					if(strlen($arNewPost["CATEGORY_ID"])>0)
					{
						$arC = explode(",", $arNewPost["CATEGORY_ID"]);
						$arTag = Array();
						foreach($arC as $v)
						{
							$arCategory = CBlogCategory::GetByID($v);
							$arTag[] = $arCategory["NAME"];
						}
						$tag =  implode(",", $arTag);
					}

					$arSearchIndex = array(
						"SITE_ID" => $arPostSite,
						"LAST_MODIFIED" => $arNewPost["DATE_PUBLISH"],
						"PARAM1" => "POST",
						"PARAM2" => $arNewPost["BLOG_ID"],
						"PARAM3" => $arNewPost["ID"],
						"PERMISSIONS" => array(2),
						"TITLE" => $arNewPost["TITLE"],
						"BODY" => blogTextParser::killAllTags($arNewPost["DETAIL_TEXT"]),
						"TAGS" => $tag,
					);
					if($arBlog["USE_SOCNET"] == "Y")
						unset($arSearchIndex["PERMISSIONS"]);
					CSearch::Index("blog", "P".$ID, $arSearchIndex, True);
					
					if($arOldPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $arNewPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH) //index comments
					{
						$arParamsComment = Array(
							"BLOG_ID" => $arBlog["ID"],
							"POST_ID" => $ID,
							"SITE_ID" => $arGroup["SITE_ID"],
							"PATH" => $arPostSite[$arGroup["SITE_ID"]]."?commentId=#comment_id###comment_id#",
							"BLOG_URL" => $arBlog["URL"],
							"OWNER_ID" => $arBlog["OWNER_ID"],
							"SOCNET_GROUP_ID" => $arBlog["SOCNET_GROUP_ID"],
							"USE_SOCNET" => $arBlog["USE_SOCNET"],
						);
						CBlogComment::_IndexPostComments($arParamsComment);
					}
				}
			}
		}

		return $ID;
	}

	//*************** SELECT *********************/
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		if (isset($GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID]) && is_array($GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID]) && is_set($GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT P.*, IF(P.DATE_PUBLISH <= NOW(), 'Y', 'N') as DATE_PUBLISHED, ".
				"	".$DB->DateToCharFunction("P.DATE_CREATE", "FULL")." as DATE_CREATE, ".
				"	".$DB->DateToCharFunction("P.DATE_PUBLISH", "FULL")." as DATE_PUBLISH ".
				"FROM b_blog_post P ".
				"WHERE P.ID = ".$ID."";
			$dbResult = $DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_POST"]["BLOG_POST_CACHE_".$ID] = $arResult;
				return $arResult;
			}
		}

		return False;
	}

	function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB, $USER_FIELD_MANAGER;
		
		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("BLOG_POST", "P.ID");
		$obUserFieldsSql->SetSelect($arSelectFields);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		if (isset($arFilter["DATE_PUBLISH_DAY"]) && isset($arFilter["DATE_PUBLISH_MONTH"]) && isset($arFilter["DATE_PUBLISH_YEAR"]))
		{
			if (strlen($arFilter["DATE_PUBLISH_YEAR"]) == 2)
				$arFilter["DATE_PUBLISH_YEAR"] = "20".$arFilter["DATE_PUBLISH_YEAR"];
			$date1 = mktime(0, 0, 0, $arFilter["DATE_PUBLISH_MONTH"], $arFilter["DATE_PUBLISH_DAY"], $arFilter["DATE_PUBLISH_YEAR"]);
			$date2 = mktime(0, 0, 0, $arFilter["DATE_PUBLISH_MONTH"], $arFilter["DATE_PUBLISH_DAY"] + 1, $arFilter["DATE_PUBLISH_YEAR"]);
			$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($date1, "SHORT", SITE_ID);
			$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($date2, "SHORT", SITE_ID);

			unset($arFilter["DATE_PUBLISH_DAY"]);
			unset($arFilter["DATE_PUBLISH_MONTH"]);
			unset($arFilter["DATE_PUBLISH_YEAR"]);
		}
		elseif (isset($arFilter["DATE_PUBLISH_MONTH"]) && isset($arFilter["DATE_PUBLISH_YEAR"]))
		{
			if (strlen($arFilter["DATE_PUBLISH_YEAR"]) == 2)
				$arFilter["DATE_PUBLISH_YEAR"] = "20".$arFilter["DATE_PUBLISH_YEAR"];
			$date1 = mktime(0, 0, 0, $arFilter["DATE_PUBLISH_MONTH"], 1, $arFilter["DATE_PUBLISH_YEAR"]);
			$date2 = mktime(0, 0, 0, $arFilter["DATE_PUBLISH_MONTH"] + 1, 1, $arFilter["DATE_PUBLISH_YEAR"]);
			$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($date1, "SHORT", SITE_ID);
			$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($date2, "SHORT", SITE_ID);

			unset($arFilter["DATE_PUBLISH_MONTH"]);
			unset($arFilter["DATE_PUBLISH_YEAR"]);
		}
		elseif (isset($arFilter["DATE_PUBLISH_YEAR"]))
		{
			if (strlen($arFilter["DATE_PUBLISH_YEAR"]) == 2)
				$arFilter["DATE_PUBLISH_YEAR"] = "20".$arFilter["DATE_PUBLISH_YEAR"];
			$date1 = mktime(0, 0, 0, 1, 1, $arFilter["DATE_PUBLISH_YEAR"]);
			$date2 = mktime(0, 0, 0, 1, 1, $arFilter["DATE_PUBLISH_YEAR"] + 1);
			$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($date1, "SHORT", SITE_ID);
			$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($date2, "SHORT", SITE_ID);

			unset($arFilter["DATE_PUBLISH_YEAR"]);
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_CREATE", "DATE_PUBLISH", "KEYWORDS", "PUBLISH_STATUS", "ATRIBUTE", "ATTACH_IMG", "ENABLE_TRACKBACK", "ENABLE_COMMENTS", "VIEWS", "NUM_COMMENTS", "CODE");
		if(in_array("*", $arSelectFields))
			$arSelectFields = array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_CREATE", "DATE_PUBLISH", "KEYWORDS", "PUBLISH_STATUS", "ATRIBUTE", "ATTACH_IMG", "ENABLE_TRACKBACK", "ENABLE_COMMENTS", "NUM_COMMENTS", "NUM_TRACKBACKS", "VIEWS", "FAVORITE_SORT", "CATEGORY_ID", "PERMS", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "AUTHOR_EMAIL", "AUTHOR", "BLOG_USER_ALIAS", "BLOG_USER_AVATAR", "BLOG_URL", "BLOG_OWNER_ID", "BLOG_ACTIVE", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_SOCNET_GROUP_ID", "BLOG_ENABLE_RSS", "BLOG_USE_SOCNET", "CODE");
		
		if((array_key_exists("BLOG_GROUP_SITE_ID", $arFilter) || in_array("BLOG_GROUP_SITE_ID", $arSelectFields)) && !in_array("BLOG_URL", $arSelectFields))
			$arSelectFields[] = "BLOG_URL";
			
		// FIELDS -->
		$arFields = array(
			"ID" => array("FIELD" => "P.ID", "TYPE" => "int"),
			"TITLE" => array("FIELD" => "P.TITLE", "TYPE" => "string"),
			"CODE" => array("FIELD" => "P.CODE", "TYPE" => "string"),
			"BLOG_ID" => array("FIELD" => "P.BLOG_ID", "TYPE" => "int"),
			"AUTHOR_ID" => array("FIELD" => "P.AUTHOR_ID", "TYPE" => "int"),
			"PREVIEW_TEXT" => array("FIELD" => "P.PREVIEW_TEXT", "TYPE" => "string"),
			"PREVIEW_TEXT_TYPE" => array("FIELD" => "P.PREVIEW_TEXT_TYPE", "TYPE" => "string"),
			"DETAIL_TEXT" => array("FIELD" => "P.DETAIL_TEXT", "TYPE" => "string"),
			"DETAIL_TEXT_TYPE" => array("FIELD" => "P.DETAIL_TEXT_TYPE", "TYPE" => "string"),
			"DATE_CREATE" => array("FIELD" => "P.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_PUBLISH" => array("FIELD" => "P.DATE_PUBLISH", "TYPE" => "datetime"),
			"KEYWORDS" => array("FIELD" => "P.KEYWORDS", "TYPE" => "string"),
			"PUBLISH_STATUS" => array("FIELD" => "P.PUBLISH_STATUS", "TYPE" => "string"),
			"ATRIBUTE" => array("FIELD" => "P.ATRIBUTE", "TYPE" => "string"),
			"ATTACH_IMG" => array("FIELD" => "P.ATTACH_IMG", "TYPE" => "int"),
			"ENABLE_TRACKBACK" => array("FIELD" => "P.ENABLE_TRACKBACK", "TYPE" => "string"),
			"ENABLE_COMMENTS" => array("FIELD" => "P.ENABLE_COMMENTS", "TYPE" => "string"),
			"NUM_COMMENTS" => array("FIELD" => "P.NUM_COMMENTS", "TYPE" => "int"),
			"NUM_TRACKBACKS" => array("FIELD" => "P.NUM_TRACKBACKS", "TYPE" => "int"),
			"VIEWS" => array("FIELD" => "P.VIEWS", "TYPE" => "int"),
			"FAVORITE_SORT" => array("FIELD" => "P.FAVORITE_SORT", "TYPE" => "int"),
			"CATEGORY_ID" => array("FIELD" => "P.CATEGORY_ID", "TYPE" => "string"),
			"CATEGORY_ID_F" => array("FIELD" => "PC.CATEGORY_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_blog_post_category PC ON (PC.POST_ID = P.ID AND P.BLOG_ID = PC.BLOG_ID)"),
			"PATH" => array("FIELD" => "P.PATH", "TYPE" => "string"),

			"PERMS" => array(),

			"AUTHOR_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (P.AUTHOR_ID = U.ID)"),
			"AUTHOR_NAME" => array("FIELD" => "UN.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user UN ON (P.AUTHOR_ID = UN.ID)"),
			"AUTHOR_LAST_NAME" => array("FIELD" => "ULN.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user ULN ON (P.AUTHOR_ID = ULN.ID)"),
			"AUTHOR_SECOND_NAME" => array("FIELD" => "USN.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user USN ON (P.AUTHOR_ID = USN.ID)"),
			"AUTHOR_EMAIL" => array("FIELD" => "UE.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user UE ON (P.AUTHOR_ID = UE.ID)"),
			"AUTHOR" => array("FIELD" => "UAA.LOGIN, UAA.NAME, UAA.LAST_NAME, UAA.EMAIL, UAA.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "LEFT JOIN b_user UAA ON (P.AUTHOR_ID = UAA.ID)"),

			"CATEGORY_NAME" => array("FIELD" => "PCN.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_blog_category PCN ON (P.BLOG_ID = PCN.BLOG_ID AND P.CATEGORY_ID = PCN.ID)"),

			"BLOG_USER_ALIAS" => array("FIELD" => "BU.ALIAS", "TYPE" => "string", "FROM" => "LEFT JOIN b_blog_user BU ON (P.AUTHOR_ID = BU.USER_ID)"),
			"BLOG_USER_AVATAR" => array("FIELD" => "BUA.AVATAR", "TYPE" => "int", "FROM" => "LEFT JOIN b_blog_user BUA ON (P.AUTHOR_ID = BUA.USER_ID)"),
			
			"BLOG_URL" => array("FIELD" => "B.URL", "TYPE" => "string", "FROM" => "INNER JOIN b_blog B ON (P.BLOG_ID = B.ID)"),
			"BLOG_OWNER_ID" => array("FIELD" => "BO.OWNER_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BO ON (P.BLOG_ID = BO.ID)"),
			"BLOG_ACTIVE" => array("FIELD" => "BA.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BA ON (P.BLOG_ID = BA.ID)"),
			"BLOG_GROUP_ID" => array("FIELD" => "BGI.GROUP_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog BGI ON (P.BLOG_ID = BGI.ID)"),
			"BLOG_GROUP_SITE_ID" => array("FIELD" => "BG.SITE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_group BG ON (B.GROUP_ID = BG.ID)"),
			"BLOG_SOCNET_GROUP_ID" => array("FIELD" => "BS.SOCNET_GROUP_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BS ON (P.BLOG_ID = BS.ID)"),
			"BLOG_ENABLE_RSS" => array("FIELD" => "BR.ENABLE_RSS", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BR ON (P.BLOG_ID = BR.ID)"),
			"BLOG_USE_SOCNET" => array("FIELD" => "BUS.USE_SOCNET", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BUS ON (P.BLOG_ID = BUS.ID)"),
			"BLOG_NAME" => array("FIELD" => "BN.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_blog BN ON (P.BLOG_ID = BN.ID)"),
			
			"SOCNET_BLOG_READ" => array("FIELD" => "BSR.BLOG_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_blog_socnet BSR ON (P.BLOG_ID = BSR.BLOG_ID)"),
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
											ON (P.BLOG_ID = BUGP".$val.".BLOG_ID 
												AND P.ID = BUGP".$val.".POST_ID 
												AND BUGP".$val.".USER_GROUP_ID = ".$val." 
												AND BUGP".$val.".PERMS_TYPE = '".BLOG_PERMS_POST."')"
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
										ON (P.BLOG_ID = BUGP.BLOG_ID 
											AND P.ID = BUGP.POST_ID 
											AND BUGP.USER_GROUP_ID = ".$arFilter["GROUP_CHECK_PERMS"]." 
											AND BUGP.PERMS_TYPE = '".BLOG_PERMS_POST."')"
						);
					$arSelectFields[] = "POST_PERM_".$arFilter["GROUP_CHECK_PERMS"];
				}
			}
			unset($arFilter["GROUP_CHECK_PERMS"]);
		}
		
		// <-- FIELDS
		$bNeedDistinct = false;
		$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
		if ($blogModulePermissions < "W")
		{	
			if(!CBlog::IsBlogOwner($arFilter["BLOG_ID"], $GLOBALS["USER"]->GetID()))
			{
				$arUserGroups = CBlogUser::GetUserGroups(($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : 0), IntVal($arFilter["BLOG_ID"]), "Y", BLOG_BY_USER_ID);
				$strUserGroups = "0";
				for ($i = 0; $i < count($arUserGroups); $i++)
					$strUserGroups .= ",".IntVal($arUserGroups[$i]);

				$arFields["PERMS"] = array("FIELD" => "UGP.PERMS", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_user_group_perms UGP ON (P.ID = UGP.POST_ID AND P.BLOG_ID = UGP.BLOG_ID AND UGP.USER_GROUP_ID IN (".$strUserGroups.") AND UGP.PERMS_TYPE = '".BLOG_PERMS_POST."')");
				$bNeedDistinct = true;
			}
			else
				$arFields["PERMS"] = array("FIELD" => "'W'", "TYPE" => "string");
		}
		else
		{
			$arFields["PERMS"] = array("FIELD" => "'W'", "TYPE" => "string");
		}

		$arSqls = CBlog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $obUserFieldsSql);

		if($bNeedDistinct)
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);
		else
			$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		$r = $obUserFieldsSql->GetFilter();
		if(strlen($r)>0)
			$strSqlUFFilter = " (".$r.") ";

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
					$obUserFieldsSql->GetSelect()." ".
				"FROM b_blog_post P ".
				"	".$arSqls["FROM"]." ".
					$obUserFieldsSql->GetJoin("P.ID")." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." "; 
			if(strlen($arSqls["WHERE"]) > 0 && strlen($strSqlUFFilter) > 0)
				$strSql .= " AND ".$strSqlUFFilter." ";
			elseif(strlen($arSqls["WHERE"]) <= 0 && strlen($strSqlUFFilter) > 0)
				$strSql .= " WHERE ".$strSqlUFFilter." ";
			
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}
	
//	$strSqlOrder = " ORDER BY ".strtoupper($s);

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
				$obUserFieldsSql->GetSelect()." ".
			"FROM b_blog_post P ".
			"	".$arSqls["FROM"]." ".
				$obUserFieldsSql->GetJoin("P.ID")." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." "; 
		if(strlen($arSqls["WHERE"]) > 0 && strlen($strSqlUFFilter) > 0)
			$strSql .= " AND ".$strSqlUFFilter." ";
		elseif(strlen($arSqls["WHERE"]) <= 0 && strlen($strSqlUFFilter) > 0)
			$strSql .= " WHERE ".$strSqlUFFilter." ";

		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT(DISTINCT P.ID) as CNT ".
				"FROM b_blog_post P ".
				"	".$arSqls["FROM"]." ".
					$obUserFieldsSql->GetJoin("P.ID")." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if(strlen($arSqls["WHERE"]) > 0 && strlen($strSqlUFFilter) > 0)
				$strSql_tmp .= " AND ".$strSqlUFFilter." ";
			elseif(strlen($arSqls["WHERE"]) <= 0 && strlen($strSqlUFFilter) > 0)
				$strSql_tmp .= " WHERE ".$strSqlUFFilter." ";

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

			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("BLOG_POST"));
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".$arNavStartParams["nTopCount"];

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$dbRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields("BLOG_POST"));
		}
		//echo "!4!=".htmlspecialchars($strSql)."<br>";
		
		return $dbRes;
	}

	function GetListCalendar($blogID, $year = false, $month = false, $day = false)
	{
		global $DB;

		$blogID = IntVal($blogID);

		if ($year)
			if (strlen($year) == 2)
				$year = "20".$year;

		if ($year && $month && $day)
		{
			$date1 = mktime(0, 0, 0, $month, $day, $year);
			$date2 = mktime(0, 0, 0, $month, $day + 1, $year);
		}
		elseif ($month && $year)
		{
			$date1 = mktime(0, 0, 0, $month, 1, $year);
			$date2 = mktime(0, 0, 0, $month + 1, 1, $year);
		}
		elseif ($year)
		{
			$date1 = mktime(0, 0, 0, 1, 1, $year);
			$date2 = mktime(0, 0, 0, 1, 1, $year + 1);
		}
		$datePublishFrom = ConvertTimeStamp($date1, "SHORT", SITE_ID);
		$datePublishTo = ConvertTimeStamp($date2, "SHORT", SITE_ID);

		$arUserGroups = CBlogUser::GetUserGroups(($GLOBALS["USER"]->IsAuthorized() ? $GLOBALS["USER"]->GetID() : 0), $arFilter["BLOG_ID"], "Y", BLOG_BY_USER_ID);
		$strUserGroups = "0";
		for ($i = 0; $i < count($arUserGroups); $i++)
			$strUserGroups .= ",".IntVal($arUserGroups[$i]);

		$strFromPerms =
			"	LEFT JOIN b_blog_user_group_perms UGP ".
			"		ON (P.ID = UGP.POST_ID ".
			"			AND P.BLOG_ID = UGP.BLOG_ID ".
			"			AND UGP.USER_GROUP_ID IN (".$strUserGroups.") ".
			"			AND UGP.PERMS_TYPE = '".$DB->ForSql(BLOG_PERMS_POST)."') ";
		$strWherePerms = " AND (UGP.PERMS > 'D') ";

		$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
		if ($blogModulePermissions >= "W")
		{
			$strFromPerms = "";
			$strWherePerms = "";
		}

		$strSql = 
			"SELECT DATE_FORMAT(P.DATE_PUBLISH, '%Y-%m-%d') as DATE_PUBLISH1, COUNT(P.ID) as CNT ".
			"FROM b_blog_post P ".$strFromPerms." ".
			"WHERE P.BLOG_ID = ".$blogID." ".
			"	AND P.DATE_PUBLISH >= ".$DB->CharToDateFunction($DB->ForSql($datePublishFrom), "SHORT")." ".
			"	AND P.DATE_PUBLISH < ".$DB->CharToDateFunction($DB->ForSql($datePublishTo), "SHORT")." ".
			"	AND P.PUBLISH_STATUS = '".$DB->ForSql(BLOG_PUBLISH_STATUS_PUBLISH)."' ".
			"	".$strWherePerms." ".
			"GROUP BY DATE_PUBLISH1 ".
			"ORDER BY DATE_PUBLISH1 ";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arResult = array();
		while ($arRes = $dbRes->Fetch())
		{
			$arDate = explode("-", $arRes["DATE_PUBLISH1"]);
			$arResult[] = array(
				"YEAR" => $arDate[0],
				"MONTH" => $arDate[1],
				"DAY" => $arDate[2],
				"DATE" => ConvertTimeStamp(mktime(0, 0, 0, $arDate[1], $arDate[2], $arDate[0]), "SHORT", LANG)
			);
		}

		return $arResult;
	}
}
?>