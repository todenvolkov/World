<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//Initialize dropdown lists
$arrFilterDropdown = array("no"=>GetMessage("SEARCH_NO_LIMIT"), "main" => "[main] ".GetMessage("SEARCH_STATIC"));
if(IsModuleInstalled("forum"))
{
	$arrFilterDropdown["forum"] = "[forum] ".GetMessage("SEARCH_FORUM");
}
if(CModule::IncludeModule("iblock"))
{
	$rsType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			$arrFilterDropdown["iblock_".$arr["ID"]] = "[iblock_".$arr["ID"]."] ".$ar["NAME"];
	}
}
if(IsModuleInstalled("blog"))
{
	$arrFilterDropdown["blog"] = "[blog] ".GetMessage("SEARCH_BLOG");
}
if(IsModuleInstalled("socialnetwork"))
{
	$arrFilterDropdown["socialnetwork"] = "[socialnetwork] ".GetMessage("SEARCH_SOCIALNETWORK");
	$arrFilterDropdown["socialnetwork_user"] = "[socialnetwork_user] ".GetMessage("SEARCH_SOCIALNETWORK_USER");
}
if(IsModuleInstalled("intranet"))
{
	$arrFilterDropdown["intranet"] = "[intranet] ".GetMessage("SEARCH_INTRANET_USERS");
}

$arrFILTER=array();
if(is_array($arCurrentValues["arrFILTER"]))
{
	foreach($arCurrentValues["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			//array_pop($arCurrentValues["arrFILTER"]);
		}
		elseif($strFILTER=="forum" && CModule::IncludeModule("forum"))
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsForum = CForumNew::GetList();
			while($arForum=$rsForum->Fetch())
				$arrFILTER[$strFILTER][$arForum["ID"]]=$arForum["NAME"];
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsIBlock = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>substr($strFILTER,7)));
			while($arIBlock=$rsIBlock->Fetch())
				$arrFILTER[$strFILTER][$arIBlock["ID"]]=$arIBlock["NAME"];
		}
		elseif($strFILTER=="blog" && CModule::IncludeModule("blog"))
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsBlog = CBlog::GetList();
			while($arBlog=$rsBlog->Fetch())
				$arrFILTER[$strFILTER][$arBlog["ID"]]=$arBlog["NAME"];
		}
		elseif($strFILTER=="socialnetwork" && CModule::IncludeModule("socialnetwork"))
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsGroup = CSocNetGroup::GetList(array("ID" => "DESC"), array(), false, false, array("ID", "NAME"));
			while($arGroup = $rsGroup->Fetch())
				$arrFILTER[$strFILTER][$arGroup["ID"]] = $arGroup["NAME"];
		}
	}
}

$arComponentParameters = array(

	"PARAMETERS" => array(
		"SORT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_SORT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array("NAME"=>GetMessage("SEARCH_NAME"), "CNT"=>GetMessage("SEARCH_CNT")),
			"DEFAULT" => "NAME",
		),
		"PAGE_ELEMENTS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => "150",
		),
		"PERIOD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"URL_SEARCH" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_URL_SEARCH"),
			"TYPE" => "STRING",
			"DEFAULT" => "/search/index.php",
		),
		"TAGS_INHERIT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_TAGS_INHERIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CHECK_DATES" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"arrFILTER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_WHERE_FILTER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arrFilterDropdown,
			"DEFAULT" => "all",
			"REFRESH" => "Y",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);

if(is_array($arCurrentValues["arrFILTER"]))
{
	foreach($arCurrentValues["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_URL"),
				"TYPE" => "STRING",
				"MULTIPLE" => "Y",
				"ADDITIONAL_VALUES"=>"Y",
				"DEFAULT" => "",
				);
		}
		elseif($strFILTER=="forum")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_FORUM"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_IBLOCK_TYPE1").$arrFilterDropdown[$strFILTER].GetMessage("SEARCH_IBLOCK_TYPE2"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif($strFILTER=="blog")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_BLOG"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif($strFILTER=="socialnetwork")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("CP_STC_SOCIALNETWORK_GROUPS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif($strFILTER=="socialnetwork_user")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("CP_STC_SOCIALNETWORK_USER"),
				"TYPE" => "STRING",
				"DEFAULT" => "all",
				);
		}
	}
}
?>
