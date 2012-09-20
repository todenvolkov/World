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
}
if(IsModuleInstalled("intranet"))
{
	$arrFilterDropdown["intranet"] = "[intranet] ".GetMessage("SEARCH_INTRANET_USERS");
}

$arrFILTER=array();
if(!empty($arCurrentValues["arrFILTER"]))
{
	if (!is_array($arCurrentValues["arrFILTER"]))
		$arCurrentValues["arrFILTER"] = array($arCurrentValues["arrFILTER"]);
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
		"NAME" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "TAG",
		),
		"VALUE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"arrFILTER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_WHERE_FILTER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $arrFilterDropdown,
			"DEFAULT" => "all",
			"REFRESH" => "Y",
		),
		"SITE_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SEARCH_SITE_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => SITE_ID,
		)
	),
);

if(!empty($arCurrentValues["arrFILTER"]))
{
	if (!is_array($arCurrentValues["arrFILTER"]))
		$arCurrentValues["arrFILTER"] = array($arCurrentValues["arrFILTER"]);
	foreach($arCurrentValues["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_URL"),
				"TYPE" => "STRING",
				"MULTIPLE" => "N",
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
				"MULTIPLE" => "N",
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
				"MULTIPLE" => "N",
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
				"MULTIPLE" => "N",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif($strFILTER=="socialnetwork")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("CP_STI_SOCIALNETWORK_GROUPS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "N",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
	}
}
?>