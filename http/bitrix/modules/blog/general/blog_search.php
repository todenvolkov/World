<?
class CBlogSearch {
	function OnSearchReindex($NS=Array(), $oCallback=NULL, $callback_method="")
	{
		global $DB;
		$arResult = array();
		
		//CBlogSearch::Trace('OnSearchReindex', 'NS', $NS);
		if($NS["MODULE"]=="blog" && strlen($NS["ID"])>0)
		{
			$category = substr($NS["ID"], 0, 1);
			$id = intval(substr($NS["ID"], 1));
		}
		else
		{
			$category = 'B';//start with blogs
			$id = 0;//very first id
		}
		//CBlogSearch::Trace('OnSearchReindex', 'category+id', array("CATEGORY"=>$category,"ID"=>$id));
		
		//Reindex blogs
		if($category == 'B')
		{
			$strSql = "
				SELECT
					b.ID
					,bg.SITE_ID
					,b.REAL_URL
					,b.URL
					,".$DB->DateToCharFunction("b.DATE_UPDATE")." as DATE_UPDATE
					,b.NAME
					,b.DESCRIPTION
					,b.OWNER_ID
					,b.SOCNET_GROUP_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
				FROM
					b_blog b
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					b.ACTIVE = 'Y'
					AND b.SEARCH_INDEX = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND b.ID > ".$id."
				ORDER BY
					b.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				$arSite = array(
					$ar["SITE_ID"] => CBlog::PreparePath($ar["URL"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
				);
				//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
				$Result = Array(
					"ID"		=>"B".$ar["ID"],
					"LAST_MODIFIED"	=>$ar["DATE_UPDATE"],
					"TITLE"		=>$ar["NAME"],
					"BODY"		=>blogTextParser::killAllTags($ar["DESCRIPTION"]),
					"SITE_ID"	=>$arSite,
					"PARAM1"	=>"BLOG",
 					"PARAM2"	=>$ar["OWNER_ID"],
					"PERMISSIONS"	=>array(2),//public
					//TODO????"URL"		=>$DETAIL_URL,
					);
				if($ar["USE_SOCNET"] == "Y")
					unset($arResult["PERMISSIONS"]);
				//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
			//all blogs indexed so let's start index posts
			$category='P';
			$id=0;
		}
		if($category == 'P')
		{
			$strSql = "
				SELECT
					bp.ID
					,bg.SITE_ID
					,b.REAL_URL
					,b.URL
					,".$DB->DateToCharFunction("bp.DATE_PUBLISH")." as DATE_PUBLISH
					,bp.TITLE
					,bp.DETAIL_TEXT
					,bp.BLOG_ID
					,b.OWNER_ID
					,bp.CATEGORY_ID
					,b.SOCNET_GROUP_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
					,bp.PATH
				FROM
					b_blog_post bp
					INNER JOIN b_blog b ON (bp.BLOG_ID = b.ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					bp.DATE_PUBLISH <= ".$DB->CurrentTimeFunction()."
					AND bp.PUBLISH_STATUS = '".$DB->ForSQL(BLOG_PUBLISH_STATUS_PUBLISH)."'
					AND b.ACTIVE = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND bp.ID > ".$id."
					AND b.SEARCH_INDEX = 'Y'
				ORDER BY
					bp.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				//Check permissions
				$tag = "";
				$PostPerms = CBlogUserGroup::GetGroupPerms(1, $ar["BLOG_ID"], $ar["ID"], BLOG_PERMS_POST);
				if($PostPerms < BLOG_PERMS_READ)
					continue;
				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				if(strlen($ar["PATH"]) > 0)
				{
					$arSite = array(
						$ar["SITE_ID"] => str_replace("#post_id#", $ar["ID"], $ar["PATH"])
					);
				}
				else
				{
					$arSite = array(
						$ar["SITE_ID"] => CBlogPost::PreparePath($ar["URL"], $ar["ID"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
					);
				}

				if(strlen($ar["CATEGORY_ID"])>0)
				{
					$arC = explode(",", $ar["CATEGORY_ID"]);
					$tag = "";
					$arTag = Array();
					foreach($arC as $v)
					{
						$arCategory = CBlogCategory::GetByID($v);
						$arTag[] = $arCategory["NAME"];
					}
					$tag =  implode(",", $arTag);
				}				

				//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
				$Result = Array(
					"ID"		=>"P".$ar["ID"],
					"LAST_MODIFIED"	=>$ar["DATE_PUBLISH"],
					"TITLE"		=>$ar["TITLE"],
					"BODY"		=>blogTextParser::killAllTags($ar["DETAIL_TEXT"]),
					"SITE_ID"	=>$arSite,
					"PARAM1"	=>"POST",
 					"PARAM2"	=>$ar["BLOG_ID"],
					"PERMISSIONS"	=>array(2),//public
					"TAGS"		=> $tag,
					//TODO????"URL"		=>$DETAIL_URL,
					);
				if($ar["USE_SOCNET"] == "Y")
					unset($Result["PERMISSIONS"]);
				//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
			//all blog posts indexed so let's start index users
			$category='C';
			$id=0;
		}
		if($category == 'C')
		{
			$strSql = "
				SELECT
					bc.ID
					,bg.SITE_ID
					,bp.ID as POST_ID
					,b.URL
					,bp.TITLE as POST_TITLE
					,b.OWNER_ID
					,b.SOCNET_GROUP_ID
					,bc.TITLE
					,bc.POST_TEXT
					,bc.POST_ID
					,bc.BLOG_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
					,bc.PATH
					,".$DB->DateToCharFunction("bc.DATE_CREATE")." as DATE_CREATE
				FROM
					b_blog_comment bc
					INNER JOIN b_blog_post bp ON (bp.ID = bc.POST_ID)
					INNER JOIN b_blog b ON (bc.BLOG_ID = b.ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					bc.ID > ".$id." 
					".($NS["SITE_ID"]!=""?" AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND b.SEARCH_INDEX = 'Y'
				ORDER BY
					bc.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				//Check permissions
				$tag = "";
				$PostPerms = CBlogUserGroup::GetGroupPerms(1, $ar["BLOG_ID"], $ar["POST_ID"], BLOG_PERMS_POST);
				if($PostPerms < BLOG_PERMS_READ)
					continue;
				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				if(strlen($ar["PATH"]) > 0)
				{
					$arSite = array(
						$ar["SITE_ID"] => str_replace("#comment_id#", $ar["ID"], $ar["PATH"])
					);
				}
				else
				{
					$arSite = array(
						$ar["SITE_ID"] => CBlogPost::PreparePath($ar["URL"], $ar["POST_ID"], $ar["SITE_ID"], false, $ar["OWNER_ID"], $ar["SOCNET_GROUP_ID"]),
					);
				}

				$Result = array(
					"ID" => "C".$ar["ID"],
					"SITE_ID" => $arSite,
					"LAST_MODIFIED" => $ar["DATE_CREATE"],
					"PARAM1" => "COMMENT",
					"PARAM2" => $ar["BLOG_ID"]."|".$ar["POST_ID"],
					"PERMISSIONS" => array(2),
					"TITLE" => $ar["TITLE"],
					"BODY" => blogTextParser::killAllTags($ar["POST_TEXT"]),
				);
				if($ar["USE_SOCNET"] == "Y")
					unset($Result["PERMISSIONS"]);
				
				if(strlen($ar["TITLE"]) <= 0)
				{
					$Result["TITLE"] = $ar["POST_TITLE"];
				}

				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
			//all blog posts indexed so let's start index users
			$category='U';
			$id=0;
		}

		if($category == 'U')
		{
			$strSql = "
				SELECT
					bu.ID
					,bg.SITE_ID
					,".$DB->DateToCharFunction("bu.LAST_VISIT")." as LAST_VISIT
					,".$DB->DateToCharFunction("u.DATE_REGISTER")." as DATE_REGISTER
					,bu.ALIAS
					,bu.DESCRIPTION
					,bu.INTERESTS
					,u.NAME
					,u.LAST_NAME
					,u.LOGIN
					,bu.USER_ID
					,b.OWNER_ID
					,b.USE_SOCNET
					,b.SEARCH_INDEX
				FROM
					b_blog_user bu
					INNER JOIN b_user u  ON (u.ID = bu.USER_ID)
					INNER JOIN b_blog b ON (u.ID = b.OWNER_ID)
					INNER JOIN b_blog_group bg ON (b.GROUP_ID = bg.ID)
				WHERE
					b.ACTIVE = 'Y'
					".($NS["SITE_ID"]!=""?"AND bg.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
					AND bu.ID > ".$id."
					AND b.SEARCH_INDEX = 'Y'
				ORDER BY
					bu.ID
			";
			//CBlogSearch::Trace('OnSearchReindex', 'strSql', $strSql);
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				//Check permissions
				//$PostPerms = CBlogUserGroup::GetGroupPerms(1, $ar["BLOG_ID"], $ar["ID"], BLOG_PERMS_POST);
				//if($PostPerms < BLOG_PERMS_READ)
				//	continue;
				//CBlogSearch::Trace('OnSearchReindex', 'ar', $ar);
				$arSite = array(
					$ar["SITE_ID"] => CBlogUser::PreparePath($ar["USER_ID"], $ar["SITE_ID"]),
				);
				//CBlogSearch::Trace('OnSearchReindex', 'arSite', $arSite);
				$Result = Array(
					"ID"		=>"U".$ar["ID"],
					"LAST_MODIFIED"	=>$ar["LAST_VISIT"],
					"TITLE"		=>CBlogUser::GetUserName($ar["ALIAS"], $ar["NAME"], $ar["LAST_NAME"], $ar["LOGIN"]),
					"BODY"		=>blogTextParser::killAllTags($ar["DESCRIPTION"]." ".$ar["INTERESTS"]),
					"SITE_ID"	=>$arSite,
					"PARAM1"	=>"USER",
 					"PARAM2"	=>$ar["ID"],
					"PERMISSIONS"	=>array(2),//public
					//TODO????"URL"		=>$DETAIL_URL,
					);
				if(strlen($Result["LAST_MODIFIED"]) <= 0)
					$Result["LAST_MODIFIED"] = $ar["DATE_REGISTER"];
				if($ar["USE_SOCNET"] == "Y")
					unset($Result["PERMISSIONS"]);
				//CBlogSearch::Trace('OnSearchReindex', 'Result', $Result);
				if($oCallback)
				{
					$res = call_user_func(array($oCallback, $callback_method), $Result);
					if(!$res)
						return $Result["ID"];
				}
				else
				{
					$arResult[] = $Result;
				}
			}
		}
		if($oCallback)
			return false;
		return $arResult;
	}
	function Trace($method, $varname, $var)
	{
		return;
		ob_start();print_r($var);$m=ob_get_contents();ob_end_clean();
		$m=" CBlogSearch::$method:$varname:$m\n";$f=fopen("D:\\debug.log", "a");
		fwrite($f, time().$m);fclose($f);
	}
	
	function SetSoNetFeatureIndexSearch($ID, $arFields)
	{
		if(CModule::IncludeModule("socialnetwork"))
		{
			BXClearCache(True, "/".SITE_ID."/blog/sonet/");
			$feature = CSocNetFeatures::GetByID($ID);
			if($feature["FEATURE"] == "blog")
			{
				if(IntVal($feature["ENTITY_ID"]) > 0)
				{
					$bRights = false;
					$arFilter = Array("USE_SOCNET" => "Y");

					if($feature["ENTITY_TYPE"] == "U")
					{
						$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $feature["ENTITY_ID"], "blog", "view_post");
						if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
							$bRights = true;
					}
					else
					{
						$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $feature["ENTITY_ID"], "blog", "view_post");
						if ($featureOperationPerms == SONET_ROLES_ALL)
							$bRights = true;
					}

					$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID"));
					if($arBlog = $dbBlog->Fetch())
					{
						if($arFields["ACTIVE"] == "N")
						{
							CBlog::DeleteSocnetRead($arBlog["ID"]);
						}
						else
						{
							if($bRights)
								CBlog::AddSocnetRead($arBlog["ID"]);
							else
								CBlog::DeleteSocnetRead($arBlog["ID"]);
						}
					}
				}
			}
		}
	}
	
	function SetSoNetFeaturePermIndexSearch($ID, $arFields)
	{
		$featurePerm = CSocNetFeaturesPerms::GetByID($ID);
		if($featurePerm["OPERATION_ID"] == "view_post")
		{
			if(CModule::IncludeModule("socialnetwork"))
			{
				BXClearCache(True, "/".SITE_ID."/blog/sonet/");
				$feature = CSocNetFeatures::GetByID($featurePerm["FEATURE_ID"]);
				if($feature["FEATURE"] == "blog" && IntVal($feature["ENTITY_ID"]) > 0)
				{
					if($feature["ACTIVE"] == "Y" && (($feature["ENTITY_TYPE"] == "U" && $arFields["ROLE"] == "A") || ($feature["ENTITY_TYPE"] == "G" && $arFields["ROLE"] == "N")))
					{
						$arFilter = Array("USE_SOCNET" => "Y");
						if($feature["ENTITY_TYPE"] == "U")
							$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						else
							$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID"));
						if($arBlog = $dbBlog->Fetch())
							CBlog::AddSocnetRead($arBlog["ID"]);
					}
					else
					{
						$arFilter = Array("USE_SOCNET" => "Y");
						if($feature["ENTITY_TYPE"] == "U")
							$arFilter["OWNER_ID"] = $feature["ENTITY_ID"];
						else
							$arFilter["SOCNET_GROUP_ID"] = $feature["ENTITY_ID"];
						$dbBlog = CBlog::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), Array("ID"));
						if($arBlog = $dbBlog->Fetch())
							CBlog::DeleteSocnetRead($arBlog["ID"]);
					}
				}
			}
		}
	}
}
?>