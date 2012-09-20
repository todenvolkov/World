<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arrDropdown=array(
	"no"=>GetMessage("CP_BST_NO_LIMIT"),
	"main" => "[main] ".GetMessage("CP_BST_STATIC"),
);
if(IsModuleInstalled("forum"))
{
	$arrDropdown["forum"] = "[forum] ".GetMessage("CP_BST_FORUM");
}
if(CModule::IncludeModule("iblock"))
{
	$rsType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			$arrDropdown["iblock_".$arr["ID"]] = "[iblock_".$arr["ID"]."] ".$ar["~NAME"];
	}
}
if(IsModuleInstalled("blog"))
{
	$arrDropdown["blog"] = "[blog] ".GetMessage("CP_BST_BLOG");
}
if(IsModuleInstalled("socialnetwork"))
{
	$arrDropdown["socialnetwork"] = "[socialnetwork] ".GetMessage("CP_BST_SOCIALNETWORK");
	$arrDropdown["socialnetwork_user"] = "[socialnetwork_user] ".GetMessage("CP_BST_SOCIALNETWORK_USER");
}
if(IsModuleInstalled("intranet"))
{
	$arrDropdown["intranet"] = "[intranet] ".GetMessage("CP_BST_INTRANET_USERS");
}

$arrFILTER = array();

$NUM_CATEGORIES = intval($arCurrentValues["NUM_CATEGORIES"]);
if($NUM_CATEGORIES <= 0)
	$NUM_CATEGORIES = 1;

for($i = 0; $i < $NUM_CATEGORIES; $i++)
{
	if(is_array($arCurrentValues["CATEGORY_".$i]))
	{
		foreach($arCurrentValues["CATEGORY_".$i] as $strFILTER)
		{
			if($strFILTER=="main")
			{
			}
			elseif($strFILTER=="forum" && CModule::IncludeModule("forum"))
			{
				$arrFILTER[$strFILTER]["all"]=GetMessage("CP_BST_ALL");
				$rsForum = CForumNew::GetList();
				while($arForum=$rsForum->Fetch())
					$arrFILTER[$strFILTER][$arForum["ID"]]=$arForum["NAME"];
			}
			elseif(strpos($strFILTER,"iblock_")===0)
			{
				$arrFILTER[$strFILTER]["all"]=GetMessage("CP_BST_ALL");
				$rsIBlock = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>substr($strFILTER,7)));
				while($arIBlock=$rsIBlock->Fetch())
					$arrFILTER[$strFILTER][$arIBlock["ID"]]=$arIBlock["NAME"];
			}
			elseif($strFILTER=="blog" && CModule::IncludeModule("blog"))
			{
				$arrFILTER[$strFILTER]["all"]=GetMessage("CP_BST_ALL");
				$rsBlog = CBlog::GetList();
				while($arBlog=$rsBlog->Fetch())
					$arrFILTER[$strFILTER][$arBlog["ID"]]=$arBlog["NAME"];
			}
			elseif($strFILTER=="socialnetwork" && CModule::IncludeModule("socialnetwork"))
			{
				$arrFILTER[$strFILTER]["all"]=GetMessage("CP_BST_ALL");
				$rsGroup = CSocNetGroup::GetList(array("ID" => "DESC"), array(), false, false, array("ID", "NAME"));
				while($arGroup = $rsGroup->Fetch())
					$arrFILTER[$strFILTER][$arGroup["ID"]] = $arGroup["NAME"];
			}
		}
	}
}

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"PAGE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("CP_BST_FORM_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "#SITE_DIR#search/index.php",
		),
		"NUM_CATEGORIES" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BST_NUM_CATEGORIES"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
			"REFRESH" => "Y",
		),
		"TOP_COUNT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BST_TOP_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
			"REFRESH" => "Y",
		),
		"USE_LANGUAGE_GUESS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BST_USE_LANGUAGE_GUESS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CHECK_DATES" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BST_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SHOW_OTHERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BST_SHOW_OTHERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
	),
);

$NUM_CATEGORIES = intval($arCurrentValues["NUM_CATEGORIES"]);
if($NUM_CATEGORIES <= 0)
	$NUM_CATEGORIES = 1;

for($i = 0; $i < $NUM_CATEGORIES; $i++)
{
	$arComponentParameters["GROUPS"]["CATEGORY_".$i] = array(
		"NAME" => GetMessage("CP_BST_NUM_CATEGORY", array("#NUM#" => $i+1))
	);
	$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_TITLE"] = array(
		"PARENT" => "CATEGORY_".$i,
		"NAME" => GetMessage("CP_BST_CATEGORY_TITLE"),
		"TYPE" => "STRING",
	);
	$arComponentParameters["PARAMETERS"]["CATEGORY_".$i] = array(
		"PARENT" => "CATEGORY_".$i,
		"NAME" => GetMessage("CP_BST_WHERE_FILTER"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arrDropdown,
		"DEFAULT" => "all",
		"REFRESH" => "Y",
	);

	if(is_array($arCurrentValues["CATEGORY_".$i]))
	{
		foreach($arCurrentValues["CATEGORY_".$i] as $strFILTER)
		{
			if($strFILTER=="main")
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_URL"),
					"TYPE" => "STRING",
					"MULTIPLE" => "Y",
					"ADDITIONAL_VALUES"=>"Y",
					"DEFAULT" => "",
				);
			}
			elseif($strFILTER=="forum")
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_FORUM"),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"VALUES" => $arrFILTER[$strFILTER],
					"ADDITIONAL_VALUES"=>"N",
					"DEFAULT" => "all",
				);
			}
			elseif(strpos($strFILTER,"iblock_")===0)
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_IBLOCK_TYPE", array("#IBLOCK_TYPE_ID#" => substr($strFILTER, 7))),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"VALUES" => $arrFILTER[$strFILTER],
					"ADDITIONAL_VALUES"=>"N",
					"DEFAULT" => "all",
				);
			}
			elseif($strFILTER=="blog")
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_BLOG"),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"VALUES" => $arrFILTER[$strFILTER],
					"ADDITIONAL_VALUES"=>"N",
					"DEFAULT" => "all",
				);
			}
			elseif($strFILTER=="socialnetwork")
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_SOCIALNETWORK_GROUPS"),
					"TYPE" => "LIST",
					"MULTIPLE" => "Y",
					"VALUES" => $arrFILTER[$strFILTER],
					"ADDITIONAL_VALUES"=>"N",
					"DEFAULT" => "all",
				);
			}
			elseif($strFILTER=="socialnetwork_user")
			{
				$arComponentParameters["PARAMETERS"]["CATEGORY_".$i."_".$strFILTER]=array(
					"PARENT" => "CATEGORY_".$i,
					"NAME" => GetMessage("CP_BST_SOCIALNETWORK_USER"),
					"TYPE" => "STRING",
					"DEFAULT" => "",
				);
			}
		}
	}

}

if($arCurrentValues["SHOW_OTHERS"] === "Y")
{
	$arComponentParameters["GROUPS"]["OTHERS_CATEGORY"] = array(
		"NAME" => GetMessage("CP_BST_OTHERS_CATEGORY")
	);
	$arComponentParameters["PARAMETERS"]["CATEGORY_OTHERS_TITLE"] = array(
		"PARENT" => "OTHERS_CATEGORY",
		"NAME" => GetMessage("CP_BST_CATEGORY_TITLE"),
		"TYPE" => "STRING",
	);
}

?>
