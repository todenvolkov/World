<?
class CSocNetSearch
{
	var $_params;
	var $_user_id;
	var $_group_id;

	/*
	arParams
		PATH_TO_GROUP_BLOG
		PATH_TO_USER_BLOG

		FORUM_ID
		PATH_TO_GROUP_FORUM_MESSAGE
		PATH_TO_USER_FORUM_MESSAGE

		PHOTO_GROUP_IBLOCK_ID
		PATH_TO_GROUP_PHOTO_ELEMENT
		PHOTO_USER_IBLOCK_ID
		PATH_TO_USER_PHOTO_ELEMENT

		CALENDAR_GROUP_IBLOCK_ID
		PATH_TO_GROUP_CALENDAR_ELEMENT

		TASK_IBLOCK_ID
		PATH_TO_GROUP_TASK_ELEMENT
		PATH_TO_USER_TASK_ELEMENT

		FILES_PROPERTY_CODE
		FILES_FORUM_ID
		FILES_GROUP_IBLOCK_ID
		PATH_TO_GROUP_FILES_ELEMENT
		FILES_USER_IBLOCK_ID
		PATH_TO_USER_FILES_ELEMENT
	*/
	function __construct($user_id, $group_id, $arParams)
	{
		$this->CSocNetSearch($user_id, $group_id, $arParams);
	}

	function CSocNetSearch($user_id, $group_id, $arParams)
	{
		$this->_user_id = intval($user_id);
		$this->_group_id = intval($group_id);
		$this->_params = $arParams;
	}

	function OnUserRelationsChange($user_id)
	{
		if(CModule::IncludeModule('search'))
			CSearchUser::DeleteByUserID($user_id);
	}

	function SetFeaturePermissions($entity_type, $entity_id, $feature, $operation, $new_perm)
	{
		if(substr($operation, 0, 4) == "view")//This kind of extremely dangerous optimization
		{
			if(CModule::IncludeModule('search'))
			{
				global $arSonetFeaturesPermsCache;
				unset($arSonetFeaturesPermsCache[$entity_type."_".$entity_id]);

				$arGroups = CSocNetSearch::GetSearchGroups($entity_type, $entity_id, $feature, $operation);
				$arParams = CSocNetSearch::GetSearchParams($entity_type, $entity_id, $feature, $operation);

				CSearch::ChangePermission(false, $arGroups, false, false, false, false, $arParams);
			}
		}
	}

	function GetSearchParams($entity_type, $entity_id, $feature, $operation)
	{
		return array(
			"feature_id" => "S".$entity_type."_".$entity_id."_".$feature."_".$operation,
			($entity_type == "G"? "socnet_group": "socnet_user") => $entity_id,
		);

	}

	function GetSearchGroups($entity_type, $entity_id, $feature, $operation)
	{
		$arResult = array();

		if($entity_type == "G")
		{
			$prefix = "SG_".$entity_id."_";
			$letter = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $entity_id, $feature, $operation);
			switch($letter)
			{
				case "N"://All
					$arResult[] = 'G2';
					break;
				case "L"://Authorized
					$arResult[] = 'AU';
					break;
				case "K"://Group members includes moderators and admins
					$arResult[] = $prefix.'K';
				case "E"://Moderators includes admins
					$arResult[] = $prefix.'E';
				case "A"://Admins
					$arResult[] = $prefix.'A';
					break;
			}
		}
		else
		{
			$prefix = "SU_".$entity_id."_";
			$letter = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $entity_id, $feature, $operation);
			switch($letter)
			{
				case "A"://All
					$arResult[] = 'G2';
					break;
				case "C"://Authorized
					$arResult[] = 'AU';
					break;
				case "E"://Friends of friends (has no rights yet) so it counts as
				case "M"://Friends
					$arResult[] = $prefix.'M';
				case "Z"://Personal
					$arResult[] = $prefix.'Z';
					break;
			}
		}

		return $arResult;
	}

	function OnSearchReindex($NS = Array(), $oCallback = NULL, $callback_method = "")
	{
		global $DB;
		$arResult = array();

		if ($NS["MODULE"]=="socialnetwork" && strlen($NS["ID"]) > 0)
			$id = intval($NS["ID"]);
		else
			$id = 0;//very first id

		$strSql = "
			SELECT
				g.ID
				,g.SITE_ID
				,".$DB->DateToCharFunction("g.DATE_UPDATE")." as DATE_UPDATE
				,g.NAME
				,g.DESCRIPTION
				,g.SUBJECT_ID
				,g.KEYWORDS
				,g.VISIBLE
			FROM
				b_sonet_group g
			WHERE
				g.ACTIVE = 'Y'
				".($NS["SITE_ID"]!=""?"AND g.SITE_ID='".$DB->ForSQL($NS["SITE_ID"])."'":"")."
				AND g.ID > ".$id."
			ORDER BY
				g.ID
		";

		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($ar = $rs->Fetch())
		{
			$arSite = array(
				$ar["SITE_ID"] => str_replace("#group_id#", $ar["ID"], COption::GetOptionString("socialnetwork", "group_path_template", "/workgroups/group/#group_id#/", $ar["SITE_ID"])),
			);

			$Result = Array(
				"ID" => "G".$ar["ID"],
				"LAST_MODIFIED" => $ar["DATE_UPDATE"],
				"TITLE" => $ar["NAME"],
				"BODY" => CSocNetTextParser::killAllTags($ar["DESCRIPTION"]),
				"SITE_ID" => $arSite,
				"PARAM1" => $ar["SUBJECT_ID"],
				"PARAM2" => $ar["ID"],
				"PARAM3" => "GROUP",
				"PERMISSIONS" => (
					$ar["VISIBLE"] == "Y"?
						array('G2')://public
						array(
							'SG_'.$ar["ID"].'_A',//admins
							'SG_'.$ar["ID"].'_E',//moderators
							'SG_'.$ar["ID"].'_K',//members
						)
				),
				"PARAMS" =>array(
					"socnet_group" => $ar["ID"],
				),
				"TAGS" => $ar["KEYWORDS"],
			);

			if($oCallback)
			{
				$res = call_user_func(array(&$oCallback, $callback_method), $Result);
				if(!$res)
					return $Result["ID"];
			}
			else
			{
				$arResult[] = $Result;
			}
		}

		if ($oCallback)
			return false;

		return $arResult;
	}

	function OnSearchPrepareFilter($strSearchContentAlias, $field, $val)
	{
		if(defined("BX_COMP_MANAGED_CACHE") && in_array($field, array("SOCIAL_NETWORK_USER", "SOCIAL_NETWORK_GROUP")))
		{
			$tag_val = (is_array($val) ? serialize($val) : $val);
			$tag_field = ($field == "SOCIAL_NETWORK_GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER);
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_search_".$tag_field."_".$tag_val);
		}
	}

	function OnSearchCheckPermissions($FIELD)
	{
		global $DB, $USER;
		$user_id = intval($USER->GetID());
		$arResult = array();

		if($user_id > 0)
		{
			$arResult[] = "SU_".$user_id."_Z";
			$rsFriends = CSocNetUserRelations::GetList(
				array(),
				array(
					"USER_ID" => $user_id,
					"RELATION" => SONET_RELATIONS_FRIEND
				),
				false,
				false,
				array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY")
			);
			while($arFriend = $rsFriends->Fetch())
			{
				if($arFriend["FIRST_USER_ID"] != $user_id)
					$arResult[] = "SU_".$arFriend["FIRST_USER_ID"]."_M";
				if($arFriend["SECOND_USER_ID"] != $user_id)
					$arResult[] = "SU_".$arFriend["SECOND_USER_ID"]."_M";
			}
		}

		$rsGroups = CSocNetUserToGroup::GetList(
			array(),
			array("USER_ID" => $user_id),
			false,
			false,
			array("GROUP_ID", "ROLE")
		);
		while($arGroup = $rsGroups->Fetch())
			$arResult[] = "SG_".$arGroup["GROUP_ID"]."_".$arGroup["ROLE"];

		return $arResult;
	}

	function BeforeIndexForum($arFields, $entity_type, $entity_id, $feature, $operation, $path_template)
	{
		global $USER;

		$SECTION_ID = "";
		$ELEMENT_ID = intval($_REQUEST["ELEMENT_ID"]);
		if($ELEMENT_ID > 0 && CModule::IncludeModule('iblock'))
		{
			$rsSections = CIBlockElement::GetElementGroups($ELEMENT_ID, true);
			$arSection = $rsSections->Fetch();
			if($arSection)
				$SECTION_ID = $arSection["ID"];
		}

		foreach($arFields["LID"] as $site_id => $url)
		{
			$arFields["URL"] = $arFields["LID"][$site_id] = str_replace(
				array(
					"#user_id#",
					"#group_id#",
					"#topic_id#",
					"#message_id#",
					"#action#",
					"#user_alias#",
					"#section_id#",
					"#element_id#",
					"#task_id#",
				),
				array(
					($this->_user_id > 0 ? $this->_user_id : $USER->GetID()),
					$this->_group_id,
					$arFields["PARAM2"],
					$arFields["ITEM_ID"],
					"view",
					($entity_type=="G"? "group_": "user_").$entity_id,
					$SECTION_ID,
					$ELEMENT_ID,
					$ELEMENT_ID,
				),
				$path_template
			);
		}

		$arFields["PERMISSIONS"] = $this->GetSearchGroups(
			$entity_type,
			$entity_id,
			$feature,
			$operation
		);

		$arFields["PARAMS"] = $this->GetSearchParams(
			$entity_type,
			$entity_id,
			$feature,
			$operation
		);

		return $arFields;
	}

	function Url($url, $params, $ancor)
	{
		$url_params = array();
		$p = strpos($url, "?");
		if($p !== false)
		{
			$ar = explode("&", substr($rl, $p+1));
			foreach($ar as $str)
			{
				list($name, $value) = explode("=", $str, 2);
				$url_params[$name] = $name."=".$value;
			}
			$url = substr($url, 0, $p);
		}

		foreach($params as $name => $value)
			$url_params[$name] = $name."=".$value;

		if(count($url_params))
			return $url."?".implode("&", $url_params).(strlen($ancor)? "#".$ancor: "");
		else
			return $url.(strlen($ancor)? "#".$ancor: "");
	}

	function BeforeIndex($arFields)
	{
		global $USER;

		//Check if we in right context
		if(!is_object($this) || !is_array($this->_params))
			return $arFields;

		if(isset($arFields["REINDEX_FLAG"]))
			return $arFields;

		//This was group modification
		if($this->_group_id)
		{
			if($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"G", $this->_group_id,
					"forum", "view",
					$this->_params["PATH_TO_GROUP_FORUM_MESSAGE"]
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["FILES_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"G", $this->_group_id,
					"files", "view",
					$this->Url($this->_params["PATH_TO_GROUP_FILES_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["TASK_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"G", $this->_group_id,
					"tasks", "view",
					$this->Url($this->_params["PATH_TO_GROUP_TASK_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["PHOTO_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"G", $this->_group_id,
					"photo", "view",
					$this->Url($this->_params["PATH_TO_GROUP_PHOTO_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif(
				$arFields["MODULE_ID"] == "blog"
				&& $arFields["PARAM1"] == "POST"
			)
			{
				$arFields["PERMISSIONS"] = $this->GetSearchGroups(
					"G",
					$this->_group_id,
					'blog',
					'view_post'
				);
				$arFields["PARAMS"] = $this->GetSearchParams(
					"G",
					$this->_group_id,
					'blog',
					'view_post'
				);
			}
			elseif(
				$arFields["MODULE_ID"] == "blog"
				&& $arFields["PARAM1"] == "COMMENT"
			)
			{
				$arFields["PERMISSIONS"] = $this->GetSearchGroups(
					"G",
					$this->_group_id,
					'blog',
					'view_comment'
				);
				$arFields["PARAMS"] = $this->GetSearchParams(
					"G",
					$this->_group_id,
					'blog',
					'view_comment'
				);
			}
		}
		elseif($this->_user_id)
		{
			if($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"U", $this->_user_id,
					"forum", "view",
					$this->_params["PATH_TO_USER_FORUM_MESSAGE"]
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["FILES_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"U", $this->_user_id,
					"files", "view",
					$this->Url($this->_params["PATH_TO_USER_FILES_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["TASK_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"U", $this->_user_id,
					"tasks", "view_all",
					$this->Url($this->_params["PATH_TO_USER_TASK_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif($arFields["MODULE_ID"] == "forum" && intval($arFields["PARAM1"]) == intval($this->_params["PHOTO_FORUM_ID"]))
			{
				$arFields = $this->BeforeIndexForum($arFields,
					"U", $this->_user_id,
					"photo", "view",
					$this->Url($this->_params["PATH_TO_USER_PHOTO_ELEMENT"], array("MID"=>"#message_id#"), "message#message_id#")
				);
			}
			elseif(
				$arFields["MODULE_ID"] == "blog"
				&& $arFields["PARAM1"] == "POST"
			)
			{
				$arFields["PERMISSIONS"] = $this->GetSearchGroups(
					"U",
					$this->_user_id,
					'blog',
					'view_post'
				);
				$arFields["PARAMS"] = $this->GetSearchParams(
					"U",
					$this->_user_id,
					'blog',
					'view_post'
				);
			}
			elseif(
				$arFields["MODULE_ID"] == "blog"
				&& $arFields["PARAM1"] == "COMMENT"
			)
			{
				$arFields["PERMISSIONS"] = $this->GetSearchGroups(
					"U",
					$this->_user_id,
					'blog',
					'view_comment'
				);
				$arFields["PARAMS"] = $this->GetSearchParams(
					"U",
					$this->_user_id,
					'blog',
					'view_comment'
				);
			}
		}

		return $arFields;
	}

	function IndexIBlockElement($arFields, $entity_id, $entity_type, $feature, $operation, $path_template, $arFieldList)
	{
		$ID = intval($arFields["ID"]);
		$IBLOCK_ID = intval($arFields["IBLOCK_ID"]);
		$arItem = array();

		if($entity_type == "G")
			$url = str_replace(
				array("#group_id#", "#user_alias#", "#section_id#", "#element_id#", "#action#", "#task_id#"),
				array($entity_id, "group_".$entity_id, $arFields["IBLOCK_SECTION"], $arFields["ID"], "view", $arFields["ID"]),
				$path_template
			);
		else
			$url = str_replace(
				array("#user_id#", "#user_alias#", "#section_id#", "#element_id#", "#action#", "#task_id#"),
				array($entity_id, "user_".$entity_id, $arFields["IBLOCK_SECTION"], $arFields["ID"], "view", $arFields["ID"]),
				$path_template
			);

		$body = "";
		foreach($arFieldList as $field)
		{
			if($field == "PREVIEW_TEXT" || $field == "DETAIL_TEXT")
				$body .= ($arFields[$field."_TYPE"]=="html"? HTMLToTxt($arFields[$field]): $arFields[$field])."\n\r";
//			elseif($field == "PROPERTY_FORUM_TOPIC_ID")
//			{
//				$topic_id = intval($ar["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
//				if($topic_id)
//					$this->UpdateForumTopicIndex($topic_id, $entity_type, $entity_id, $url."#message#message_id#");
//			}
			else
				$body .= $arFields[$field]."\n\r";
		}

		CSearch::Index("socialnetwork", $ID, $ar = array(
			"LAST_MODIFIED" => ConvertTimeStamp(time(), "FULL"),
			"TITLE" => $arFields["NAME"],
			"BODY" => $body,
			"SITE_ID" => array(SITE_ID => $url),
			"PARAM1" => CIBlock::GetArrayByID($IBLOCK_ID, "IBLOCK_TYPE_ID"),
			"PARAM2" => $IBLOCK_ID,
			"PARAM3" => $feature,
			"TAGS" => $arFields["TAGS"],
			"PERMISSIONS" => $this->GetSearchGroups(
				$entity_type,
				$entity_id,
				$feature,
				$operation
			),
			"PARAMS" => $this->GetSearchParams(
				$entity_type,
				$entity_id,
				$feature,
				$operation
			),
		), true);

		if(defined("BX_COMP_MANAGED_CACHE"))
			$GLOBALS["CACHE_MANAGER"]->ClearByTag("sonet_search_".$entity_type."_".$entity_id);


	}

	function IBlockElementUpdate(&$arFields)
	{

		//Do not index workflow history
		$WF_PARENT_ELEMENT_ID = intval($arFields["WF_PARENT_ELEMENT_ID"]);
		if($WF_PARENT_ELEMENT_ID > 0 && $WF_PARENT_ELEMENT_ID != intval($arFields["ID"]))
			return;

		if(!CModule::IncludeModule('search'))
			return;

		//And do not index wf drafts
		$rsElement = CIBlockElement::GetList(
			array(),
			array("=ID"=>$arFields["ID"]),
			false,
			false,
			array(
				"ID",
				"NAME",
				"WF_PARENT_ELEMENT_ID",
				"WF_STATUS_ID",
			)
		);
		$dbElement = $rsElement->Fetch();
		if(!$dbElement)
			return;

		if(!isset($arFields["NAME"]))
			$arFields["NAME"] = $dbElement["NAME"];

		switch(intval($arFields["IBLOCK_ID"]))
		{

		case intval($this->_params["PHOTO_GROUP_IBLOCK_ID"]):
			$path_template = trim($this->_params["PATH_TO_GROUP_PHOTO_ELEMENT"]);
 			if(strlen($path_template))
				$this->IndexIBlockElement($arFields, $this->_group_id, "G", "photo", "view", $path_template, array("PREVIEW_TEXT"));
			break;

		case intval($this->_params["PHOTO_USER_IBLOCK_ID"]):
			$path_template = trim($this->_params["PATH_TO_USER_PHOTO_ELEMENT"]);
 			if(strlen($path_template))
				$this->IndexIBlockElement($arFields, $this->_user_id, "U", "photo", "view", $path_template, array("PREVIEW_TEXT"));
			break;

		case intval($this->_params["CALENDAR_GROUP_IBLOCK_ID"]):
			$path_template = trim($this->_params["PATH_TO_GROUP_CALENDAR_ELEMENT"]);
 			if(strlen($path_template))
				$this->IndexIBlockElement($arFields, $this->_group_id, "G", "calendar", "view", $path_template, array("DETAIL_TEXT"));
			break;

		case intval($this->_params["TASK_IBLOCK_ID"]):
			if(is_array($arFields["IBLOCK_SECTION"]))
			{
				foreach($arFields["IBLOCK_SECTION"] as $section_id)
					break;
			}
			else
			{
				$section_id = $arFields["IBLOCK_SECTION"];
			}
			$section_id = intval($section_id);

			if($section_id)
			{
				$rsPath = CIBlockSection::GetNavChain($arFields["IBLOCK_ID"], $section_id);
				$arSection = $rsPath->Fetch();
				if($arSection)
				{
					if($arSection["EXTERNAL_ID"]=="users_tasks")
					{
						$rsAssigned = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE"=>"TASKASSIGNEDTO", "EMPTY"=>"N"));
						$arAssigned = $rsAssigned->Fetch();
						$path_template = trim($this->_params["PATH_TO_USER_TASK_ELEMENT"]);
						if($arAssigned && strlen($path_template))
							$this->IndexIBlockElement($arFields, $arAssigned["VALUE"], "U", "tasks", "view_all", $path_template, array("DETAIL_TEXT"));
					}
					elseif(intval($arSection["EXTERNAL_ID"]) > 0)
					{
						$path_template = trim($this->_params["PATH_TO_GROUP_TASK_ELEMENT"]);
						if(strlen($path_template))
							$this->IndexIBlockElement($arFields, intval($arSection["EXTERNAL_ID"]), "G", "tasks", "view", $path_template, array("DETAIL_TEXT"));
					}
				}
			}
			break;

		case intval($this->_params["FILES_GROUP_IBLOCK_ID"]):
			$path_template = trim($this->_params["PATH_TO_GROUP_FILES_ELEMENT"]);
 			if(strlen($path_template))
			{
				$property = strtoupper(trim($this->_params["FILES_PROPERTY_CODE"]));
				if(strlen($property) <= 0)
					$property = "FILE";

				$rsFile = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE"=>$property, "EMPTY"=>"N"));
				$arFile = $rsFile->Fetch();
				if($arFile)
				{
					$arFile = CIBlockElement::__GetFileContent($arFile["VALUE"]);
					if(is_array($arFile))
					{
						$arFields["FILE_CONTENT"] = $arFile["CONTENT"];
						if(strlen($arFields["TAGS"]))
							$arFields["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
						else
							$arFields["TAGS"] = $arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
					}
				}
				$this->IndexIBlockElement($arFields, $this->_group_id, "G", "files", "view", $path_template, array("FILE_CONTENT", "DETAIL_TEXT"));
			}
			break;

		case intval($this->_params["FILES_USER_IBLOCK_ID"]):
			$path_template = trim($this->_params["PATH_TO_USER_FILES_ELEMENT"]);
 			if(strlen($path_template))
			{
				$property = strtoupper(trim($this->_params["FILES_PROPERTY_CODE"]));
				if(strlen($property) <= 0)
					$property = "FILE";

				$rsFile = CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE"=>$property, "EMPTY"=>"N"));
				$arFile = $rsFile->Fetch();
				if($arFile)
				{
					$arFile = CIBlockElement::__GetFileContent($arFile["VALUE"]);
					if(is_array($arFile))
					{
						$arFields["FILE_CONTENT"] = $arFile["CONTENT"];
						if(strlen($arFields["TAGS"]))
							$arFields["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
						else
							$arFields["TAGS"] = $arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
					}
				}
				$this->IndexIBlockElement($arFields, $this->_user_id, "U", "files", "view", $path_template, array("FILE_CONTENT", "DETAIL_TEXT"));
			}
			break;
		}
	}

	function IBlockElementDelete($zr)
	{
		if(CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("socialnetwork", IntVal($zr["ID"]));
		}
	}

	function OnBeforeIndexUpdate($ID, $arFields)
	{
	}

	function OnAfterIndexAdd($ID, $arFields)
	{
	}
}
?>