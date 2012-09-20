<?
IncludeModuleLangFile(__FILE__);
$GLOBALS["BLOG_COMMENT"] = Array();

class CAllBlogComment
{
	/*************** ADD, UPDATE, DELETE *****************/
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB;
		if ((is_set($arFields, "BLOG_ID") || $ACTION=="ADD") && IntVal($arFields["BLOG_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_BLOG_ID"), "EMPTY_BLOG_ID");
			return false;
		}
		elseif (is_set($arFields, "BLOG_ID"))
		{
			$arResult = CBlog::GetByID($arFields["BLOG_ID"]);
			if (!$arResult)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["BLOG_ID"], GetMessage("BLG_GCM_ERROR_NO_BLOG")), "ERROR_NO_BLOG");
				return false;
			}
		}

		if ((is_set($arFields, "POST_ID") || $ACTION=="ADD") && IntVal($arFields["POST_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_POST_ID"), "EMPTY_POST_ID");
			return false;
		}
		elseif (is_set($arFields, "POST_ID"))
		{
			$arResult = CBlogPost::GetByID($arFields["POST_ID"]);
			if (!$arResult)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["POST_ID"], GetMessage("BLG_GCM_ERROR_NO_POST")), "ERROR_NO_POST");
				return false;
			}
		}

		if (is_set($arFields, "PARENT_ID") && $arFields["PARENT_ID"])
		{
			$arResult = CBlogComment::GetByID($arFields["PARENT_ID"]);
			if (!$arResult)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PARENT_ID"], GetMessage("BLG_GCM_ERROR_NO_COMMENT")), "ERROR_NO_COMMENT");
				return false;
			}
		}

		if (is_set($arFields, "AUTHOR_ID"))
		{
			if (IntVal($arFields["AUTHOR_ID"]) <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_AUTHOR_ID"), "EMPTY_AUTHOR_ID");
				return false;
			}
			else
			{
				$dbResult = CUser::GetByID($arFields["AUTHOR_ID"]);
				if (!$dbResult->Fetch())
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_ERROR_NO_AUTHOR_ID"), "ERROR_NO_AUTHOR_ID");
					return false;
				}
			}
		}
		else
		{
			if ((is_set($arFields, "AUTHOR_NAME") || $ACTION=="ADD") && strlen($arFields["AUTHOR_NAME"]) <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_AUTHOR_NAME"), "EMPTY_AUTHOR_NAME");
				return false;
			}
		}

		if (is_set($arFields, "AUTHOR_EMAIL") && strlen($arFields["AUTHOR_EMAIL"]) > 0)
		{
			if (!check_email($arFields["AUTHOR_EMAIL"]))
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_ERROR_AUTHOR_EMAIL"), "ERROR_AUTHOR_EMAIL");
				return false;
			}
		}

		if ((is_set($arFields, "DATE_CREATE") || $ACTION=="ADD") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_ERROR_DATE_CREATE"), "ERROR_DATE_CREATE");
			return false;
		}

		if ((is_set($arFields, "POST_TEXT") || $ACTION=="ADD") && strlen($arFields["POST_TEXT"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_POST_TEXT"), "EMPTY_POST_TEXT");
			return false;
		}

		/*
		if ((is_set($arFields, "TITLE") || $ACTION=="ADD") && strlen($arFields["TITLE"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GCM_EMPTY_TITLE"), "EMPTY_TITLE");
			return false;
		}
		*/

		return True;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		$arResult = CBlogComment::GetByID($ID);
		
		$db_events = GetModuleEvents("blog", "OnBeforeCommentDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
				return false;
				
		if ($arResult)
		{
			$DB->Query(
				"UPDATE b_blog_comment SET ".
				"	PARENT_ID = ".((IntVal($arResult["PARENT_ID"]) > 0) ? IntVal($arResult["PARENT_ID"]) : "null")." ".
				"WHERE PARENT_ID = ".$ID." ".
				"	AND BLOG_ID = ".IntVal($arResult["BLOG_ID"])." ".
				"	AND POST_ID = ".IntVal($arResult["POST_ID"])." ",
				true
			);

			if($arResult["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
				CBlogPost::Update($arResult["POST_ID"], array("=NUM_COMMENTS" => "NUM_COMMENTS - 1"));
		}

		unset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]);
		
		$events = GetModuleEvents("blog", "OnCommentDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID));

		if (CModule::IncludeModule("search"))
		{
			CSearch::Index("blog", "C".$ID,
				array(
					"TITLE" => "",
					"BODY" => ""
				)
			);
		}
				
		return $DB->Query("DELETE FROM b_blog_comment WHERE ID = ".$ID."", true);
	}

	//*************** SELECT *********************/
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		if (isset($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]) && is_array($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID]) && is_set($GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT C.ID, C.BLOG_ID, C.POST_ID, C.PARENT_ID, C.AUTHOR_ID, C.AUTHOR_NAME, ".
				"	C.AUTHOR_EMAIL, C.AUTHOR_IP, C.AUTHOR_IP1, C.TITLE, C.POST_TEXT, ".
				"	".$DB->DateToCharFunction("C.DATE_CREATE", "FULL")." as DATE_CREATE, ".
				"	C.PUBLISH_STATUS, C.PATH ".
				"FROM b_blog_comment C ".
				"WHERE C.ID = ".$ID."";
			$dbResult = $DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_COMMENT"]["BLOG_COMMENT_CACHE_".$ID] = $arResult;
				return $arResult;
			}
		}

		return False;
	}
	
	function BuildRSS($postID, $blogID, $type = "RSS2.0", $numPosts = 10, $arPathTemplate = Array())
	{
		$blogID = IntVal($blogID);
		$postID = IntVal($postID);
		if($blogID <= 0)
			return false;
		if($postID <= 0)
			return false;
		$numPosts = IntVal($numPosts);
		$type = strtolower(preg_replace("/[^a-zA-Z0-9.]/is", "", $type));
		if ($type != "rss.92" && $type != "atom.03")
			$type = "rss2.0";

		$rssText = False;

		$arBlog = CBlog::GetByID($blogID);
		if ($arBlog
			&& $arBlog["ACTIVE"] == "Y"
			&& $arBlog["ENABLE_RSS"] == "Y")
		{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
				$arPost = CBlogPost::GetByID($postID);
				if(!empty($arPost) && $arPost["BLOG_ID"] == $arBlog["ID"] && $arPost["ENABLE_COMMENTS"] == "Y")
				{
					$now = date("r");
					$nowISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);

					$serverName = "";
					$charset = "";
					$language = "";
					$dbSite = CSite::GetList(($b = "sort"), ($o = "asc"), array("LID" => SITE_ID));
					if ($arSite = $dbSite->Fetch())
					{
						$serverName = $arSite["SERVER_NAME"];
						$charset = $arSite["CHARSET"];
						$language = $arSite["LANGUAGE_ID"];
					}

					if (strlen($serverName) <= 0)
					{
						if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "");
					}

					if (strlen($charset) <= 0)
					{
						if (defined("SITE_CHARSET") && strlen(SITE_CHARSET) > 0)
							$charset = SITE_CHARSET;
						else
							$charset = "windows-1251";
					}

					if(strlen($arPathTemplate["PATH_TO_BLOG"])>0)
						$blogURL = htmlspecialchars("http://".$serverName.CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"])));
					else
						$blogURL = htmlspecialchars("http://".$serverName.CBlog::PreparePath($arBlog["URL"], $arGroup["SITE_ID"]));
						
					if(strlen($arPathTemplate["PATH_TO_POST"])>0)
						$url = htmlspecialchars("http://".$serverName.CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arPathTemplate["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"])));
					else
						$url = htmlspecialchars("http://".$serverName.CBlogPost::PreparePath($arBlog["URL"], $arPost["ID"], $arGroup["SITE_ID"]));
						
					$blogName = GetMessage("BLG_GCM_RSS_TITLE", Array("#BLOG_NAME#" => htmlspecialcharsEx($arBlog["NAME"]), "#POST_TITLE#" => htmlspecialcharsEx($arPost["TITLE"])));

					$rssText = "";
					if ($type == "rss.92")
					{
						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<rss version=\".92\">\n";
						$rssText .= " <channel>\n";
						$rssText .= "	<title>".$blogName."</title>\n";
						$rssText .= "	<description>".$blogName."</description>\n";
						$rssText .= "	<link>".$url."</link>\n";
						$rssText .= "	<language>".$language."</language>\n";
						$rssText .= "	<docs>http://backend.userland.com/rss092</docs>\n";
						$rssText .= "\n";
					}
					elseif ($type == "rss2.0")
					{
						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<rss version=\"2.0\">\n";
						$rssText .= " <channel>\n";
						$rssText .= "	<title>".$blogName."</title>\n";
						$rssText .= "	<description>".$blogName."</description>\n";
						//$rssText .= "	<guid>".$url."</guid>\n";
						$rssText .= "	<link>".$url."</link>\n";
						$rssText .= "	<language>".$language."</language>\n";
						$rssText .= "	<docs>http://backend.userland.com/rss2</docs>\n";
						$rssText .= "	<pubDate>".$now."</pubDate>\n";
						$rssText .= "\n";
					}
					elseif ($type == "atom.03")
					{
						$atomID = "tag:".htmlspecialchars($serverName).",".date("Y-m-d").":".$postID;

						$rssText .= "<"."?xml version=\"1.0\" encoding=\"".$charset."\"?".">\n\n";
						$rssText .= "<feed version=\"0.3\" xmlns=\"http://purl.org/atom/ns#\" xml:lang=\"".$language."\">\n";
						$rssText .= "  <title>".$blogName."</title>\n";
						$rssText .= "  <tagline>".$url."</tagline>\n";
						$rssText .= "  <id>".$atomID."</id>\n";
						$rssText .= "  <link rel=\"alternate\" type=\"text/html\" href=\"".$url."\" />\n";
						$rssText .= "  <modified>".$nowISO."</modified>\n";
						
						$BlogUser = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID); 
						$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
						$arUser = $dbUser->Fetch();
						$authorP = htmlspecialcharsex(CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]));
						if(strLen($arPathTemplate["PATH_TO_USER"])>0)
							$authorURLP = htmlspecialchars("http://".$serverName.CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_USER"], array("user_id"=>$arPost["AUTHOR_ID"])));
						else
							$authorURLP = "http://".$serverName.CBlogUser::PreparePath($arPost["AUTHOR_ID"], $arGroup["SITE_ID"]);

						$rssText .= "  <author>\n";
						$rssText .= "  		<name>".$authorP."</name>\n";
						$rssText .= "  		<uri>".$authorURLP."</uri>\n";
						$rssText .= "  </author>\n";
						
						$rssText .= "\n";
					}
					
					$user_id = $GLOBALS["USER"]->GetID();
					if($bSoNet)
					{
						$blogOwnerID = $arBlog["OWNER_ID"];
						$arResult["PostPerm"] = BLOG_PERMS_DENY;
						if(IntVal($arBlog["SOCNET_GROUP_ID"]) > 0)
						{
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arBlog["SOCNET_GROUP_ID"], "blog", "view_post"))
								$postPerm = BLOG_PERMS_READ;
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arBlog["SOCNET_GROUP_ID"], "blog", "write_post"))
								$postPerm = BLOG_PERMS_WRITE;
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arBlog["SOCNET_GROUP_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $GLOBALS["APPLICATION"]->GetGroupRight("blog") >= "W")
								$postPerm = BLOG_PERMS_FULL;
						}
						else
						{
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "view_post"))
								$postPerm = BLOG_PERMS_READ;
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "write_post"))
								$postPerm = BLOG_PERMS_WRITE;
							if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $GLOBALS["APPLICATION"]->GetGroupRight("blog") >= "W")
								$postPerm = BLOG_PERMS_FULL;
						}
					}
					else
					{
						$postPerm = CBlogPost::GetBlogUserCommentPerms($postID, IntVal($user_id));
					}

					if($postPerm >= BLOG_PERMS_READ)
					{
						$parser = new blogTextParser();

						$dbComments = CBlogComment::GetList(
							array("DATE_CREATE" => "DESC"),
							array(
								"BLOG_ID" => $blogID,
								"POST_ID" => $postID,
								"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
							),
							false,
							array("nTopCount" => $numPosts),
							array("ID", "TITLE", "DATE_CREATE", "POST_TEXT", "AUTHOR_EMAIL", "AUTHOR_ID", "AUTHOR_NAME", "USER_LOGIN", "USER_LAST_NAME", "USER_NAME", "BLOG_USER_ALIAS")
						);

						while ($arComments = $dbComments->Fetch())
						{
							$arDate = ParseDateTime($arComments["DATE_CREATE"], CSite::GetDateFormat("FULL", $arGroup["SITE_ID"]));
							$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));

							if(strpos($url, "?") !== false)
								$url1 = $url."&amp;";
							else
								$url1 = $url."?";
							$url1 .= "commentId=".$arComments["ID"]."#".$arComments["ID"];

							
							$authorURL = "";
							if(IntVal($arComments["AUTHOR_ID"]) > 0)
							{
								$author = CBlogUser::GetUserName($arComments["BLOG_USER_ALIAS"], $arComments["USER_NAME"], $arComments["USER_LAST_NAME"], $arComments["USER_LOGIN"]);
								if(strLen($arPathTemplate["PATH_TO_USER"])>0)
									$authorURL = htmlspecialchars("http://".$serverName.CComponentEngine::MakePathFromTemplate($arPathTemplate["PATH_TO_USER"], array("user_id"=>$arComments["AUTHOR_ID"])));
								else
									$authorURL = htmlspecialchars("http://".$serverName.CBlogUser::PreparePath($arComments["AUTHOR_ID"], $arGroup["SITE_ID"]));
							}
							else
								$author = $arComments["AUTHOR_NAME"];
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N");
							if($arPathTemplate["NO_URL_IN_COMMENTS"] == "L" || (IntVal($arComments["AUTHOR_ID"]) <= 0  && $arPathTemplate["NO_URL_IN_COMMENTS"] == "A"))
								$arAllow["CUT_ANCHOR"] = "Y";
								
							if($arPathTemplate["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && IntVal($arComments["AUTHOR_ID"]) > 0)
							{
								$authorityRatingId = CRatings::GetAuthorityRating();
								$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComments["AUTHOR_ID"]);
								if($arRatingResult["CURRENT_VALUE"] < $arPathTemplate["NO_URL_IN_COMMENTS_AUTHORITY"])
									$arAllow["CUT_ANCHOR"] = "Y";
							}

							$text = $parser->convert_to_rss($arComments["POST_TEXT"], false, $arAllow);

							$title = GetMessage("BLG_GCM_COMMENT_TITLE", Array("#POST_TITLE#" => htmlspecialcharsEx($arPost["TITLE"]), "#COMMENT_AUTHOR#" => htmlspecialcharsEx($author)));
							/*$title = str_replace(
								array("&", "<", ">", "\""),
								array("&amp;", "&lt;", "&gt;", "&quot;"),
								$title);
							*/
							//$text1 = HTMLToTxt($text, "", Array("\&nbsp;"), 60);
							$text = "<![CDATA[".$text."]]>";


							if ($type == "rss.92")
							{
								$rssText .= "    <item>\n";
								$rssText .= "      <title>".$title."</title>\n";
								$rssText .= "      <description>".$text."</description>\n";
								$rssText .= "      <link>".$url1."</link>\n";
								$rssText .= "    </item>\n";
								$rssText .= "\n";
							}
							elseif ($type == "rss2.0")
							{
								$rssText .= "    <item>\n";
								$rssText .= "      <title>".$title."</title>\n";
								$rssText .= "      <description>".$text."</description>\n";
								$rssText .= "      <link>".$url1."</link>\n";
								$rssText .= "      <guid>".$url1."</guid>\n";
								$rssText .= "      <pubDate>".$date."</pubDate>\n";
								$rssText .= "    </item>\n";
								$rssText .= "\n";
							}
							elseif ($type == "atom.03")
							{
								$atomID = "tag:".htmlspecialchars($serverName).":".$arBlog["URL"]."/".$arPost["ID"];

								$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
								$dateISO = date("Y-m-d\TH:i:s", $timeISO).substr(date("O", $timeISO), 0, 3).":".substr(date("O", $timeISO), -2, 2);

								$rssText .= "<entry>\n";
								$rssText .= "  <title type=\"text/html\">".$title."</title>\n";
								$rssText .= "  <link rel=\"alternate\" type=\"text/html\" href=\"".$url1."\"/>\n";
								$rssText .= "  <issued>".$dateISO."</issued>\n";
								$rssText .= "  <modified>".$nowISO."</modified>\n";
								$rssText .= "  <id>".$atomID."</id>\n";
								$rssText .= "  <content type=\"text/html\" mode=\"escaped\" xml:lang=\"".$language."\" xml:base=\"".$blogURL."\">\n";
								$rssText .= $text."\n";
								$rssText .= "  </content>\n";
								$rssText .= "  <author>\n";
								$rssText .= "    <name>".htmlspecialcharsex($author)."</name>\n";
								if(strlen($authorURL) > 0)
									$rssText .= "    <uri>".$authorURL."</uri>\n";
								$rssText .= "  </author>\n";
								$rssText .= "</entry>\n";
								$rssText .= "\n";
							}
						}
					}

					if ($type == "rss.92")
						$rssText .= "  </channel>\n</rss>";
					elseif ($type == "rss2.0")
						$rssText .= "  </channel>\n</rss>";
					elseif ($type == "atom.03")
						$rssText .= "\n\n</feed>";
				}
			}
		}

		return $rssText;
	}
	function _IndexPostComments($arParams = Array())
	{
		if(IntVal($arParams["BLOG_ID"]) <= 0 || IntVal($arParams["POST_ID"]) <= 0 || !CModule::IncludeModule("search"))
			return false;

		$dbComment = CBlogComment::GetList(Array(), Array("BLOG_ID" => $arParams["BLOG_ID"], "POST_ID" => $arParams["POST_ID"], "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH), false, false, Array("ID", "POST_ID", "BLOG_ID", "PUBLISH_STATUS", "PATH", "DATE_CREATE", "POST_TEXT", "TITLE", ));
		while($arComment = $dbComment->Fetch())
		{
			if(strlen($arComment["PATH"]) > 0)
				$arComment["PATH"] = str_replace("#comment_id#", $arComment["ID"], $arComment["PATH"]);
			elseif(strlen($arParams["PATH"]) > 0)
				$arComment["PATH"] = str_replace("#comment_id#", $arComment["ID"], $arParams["PATH"]);
			else
			{
				$arComment["PATH"] = CBlogPost::PreparePath(
							$arParams["BLOG_URL"],
							$arComment["POST_ID"],
							$arParams["SITE_ID"],
							false,
							$arParams["OWNER_ID"],
							$arParams["SOCNET_GROUP_ID"]
						);
			}
			
			$arSearchIndex = array(
				"SITE_ID" => array($arParams["SITE_ID"] => $arComment["PATH"]),
				"LAST_MODIFIED" => $arComment["DATE_CREATE"],
				"PARAM1" => "COMMENT",
				"PARAM2" => $arComment["BLOG_ID"]."|".$arComment["POST_ID"],
				"PERMISSIONS" => array(2),
				"TITLE" => $arComment["TITLE"],
				"BODY" => blogTextParser::killAllTags($arComment["POST_TEXT"]),
			);
			if($arParams["USE_SOCNET"] == "Y")
				unset($arSearchIndex["PERMISSIONS"]);
			if(strlen($arComment["TITLE"]) <= 0)
			{
				$arSearchIndex["TITLE"] = substr($arSearchIndex["BODY"], 0, 100);
			}

			CSearch::Index("blog", "C".$arComment["ID"], $arSearchIndex, True);
		}
	}
}
?>