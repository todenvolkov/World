<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!IsModuleInstalled("search"))
{
	ShowError(GetMessage("CC_BST_MODULE_NOT_INSTALLED"));
	return;
}

if(!isset($arParams["PAGE"]) || strlen($arParams["PAGE"])<=0)
	$arParams["PAGE"] = "#SITE_DIR#search/index.php";

$arResult["CATEGORIES"] = array();

$query = ltrim($_POST["q"]);
if(
	!empty($query)
	&& $_REQUEST["ajax_call"] === "y"
	&& (
		!isset($_REQUEST["INPUT_ID"])
		|| $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"]
	)
	&& CModule::IncludeModule("search")
)
{
	CUtil::decodeURIComponent($query);

	$arResult["alt_query"] = "";
	if($arParams["USE_LANGUAGE_GUESS"] !== "N")
	{
		$arLang = CSearchLanguage::GuessLanguage($query);
		if(is_array($arLang) && $arLang["from"] != $arLang["to"])
			$arResult["alt_query"] = CSearchLanguage::ConvertKeyboardLayout($query, $arLang["from"], $arLang["to"]);
	}

	$arResult["query"] = $query;
	$arResult["phrase"] = stemming_split($query, LANGUAGE_ID);

	$arParams["NUM_CATEGORIES"] = intval($arParams["NUM_CATEGORIES"]);
	if($arParams["NUM_CATEGORIES"] <= 0)
		$arParams["NUM_CATEGORIES"] = 1;

	$arParams["TOP_COUNT"] = intval($arParams["TOP_COUNT"]);
	if($arParams["TOP_COUNT"] <= 0)
		$arParams["TOP_COUNT"] = 5;

	$arOthersFilter = array("LOGIC"=>"OR");

	for($i = 0; $i < $arParams["NUM_CATEGORIES"]; $i++)
	{
		if(empty($arParams["CATEGORY_".$i."_TITLE"]))
			continue;

		$arResult["CATEGORIES"][$i] = array(
			"TITLE" => htmlspecialchars($arParams["CATEGORY_".$i."_TITLE"]),
			"ITEMS" => array()
		);

		$exFILTER = array(array("LOGIC"=>"OR"));
		if($arParams["CHECK_DATES"] === "Y")
			$exFILTER["CHECK_DATES"] = "Y";

		if(is_array($arParams["CATEGORY_".$i]))
		{
			foreach($arParams["CATEGORY_".$i] as $strFILTER)
			{
				$param_name = "CATEGORY_".$i."_".$strFILTER;
				if($strFILTER=="main")
				{
					if(!is_array($arParams[$param_name]))
						$arParams[$param_name]=array();
					$arURL=array();
					foreach($arParams[$param_name] as $strURL)
						if(strlen($strURL))
							$arURL[]=$strURL."%";
					if(count($arURL)<=0)
						$arURL[]="/%";
					$exFILTER[0][]=array(
						"MODULE_ID" => "main",
						"URL" => $arURL,
					);
				}
				elseif($strFILTER=="forum" && IsModuleInstalled("forum"))
				{
					if(!is_array($arParams[$param_name]))
						$arParams[$param_name]=array();
					$arForum=array();
					foreach($arParams[$param_name] as $strForum)
						if($strForum<>"all")
							$arForum[]=intval($strForum);
					if(count($arForum)>0)
						$exFILTER[0][]=array(
							"MODULE_ID" => "forum",
							"PARAM1" => $arForum,
						);
					else
						$exFILTER[0][]=array(
							"MODULE_ID" => "forum",
						);
				}
				elseif(strpos($strFILTER,"iblock_")===0)
				{
					if(!is_array($arParams[$param_name]))
						$arParams[$param_name]=array();
					$arIBlock=array();
					foreach($arParams[$param_name] as $strIBlock)
						if($strIBlock<>"all")
							$arIBlock[]=intval($strIBlock);
					if(count($arIBlock)>0)
						$exFILTER[0][]=array(
							"=MODULE_ID" => "iblock",
							"PARAM1" => substr($strFILTER, 7),
							"PARAM2" => $arIBlock,
						);
					else
						$exFILTER[0][]=array(
							"=MODULE_ID" => "iblock",
							"PARAM1" => substr($strFILTER, 7),
						);
				}
				elseif($strFILTER=="blog")
				{
					if(!is_array($arParams[$param_name]))
						$arParams[$param_name]=array();
					$arBlog=array();
					foreach($arParams[$param_name] as $strBlog)
						if($strBlog<>"all")
							$arBlog[]=intval($strBlog);
					if(count($arBlog)>0)
						$exFILTER[0][]=array(
							"MODULE_ID" => "blog",
							"PARAM1" => "POST",
							"PARAM2" => $arBlog,
						);
					else
						$exFILTER[0][]=array(
							"MODULE_ID" => "blog",
						);
				}
				elseif($strFILTER=="socialnetwork")
				{
					if(!is_array($arParams[$param_name]))
						$arParams[$param_name]=array();
					$arSCGroups=array();
					foreach($arParams[$param_name] as $strSCGroup)
						if($strSCGroup<>"all")
							$arSCGroups[] = intval($strSCGroup);

					if(count($arSCGroups)>0)
						$exFILTER[0][]=array(
							"PARAMS" => array("socnet_group" => $arSCGroups),
							"USE_TF_FILTER" => false,
						);
					else
						$exFILTER[0][]=array(
							"MODULE_ID" => "socialnetwork",
						);
				}
				elseif($strFILTER=="socialnetwork_user")
				{
					$intSCUser = intval($arParams[$param_name]);
					if($intSCUser > 0)
						$exFILTER[0][]=array(
							"PARAMS" => array("socnet_user" => $intSCUser),
							"USE_TF_FILTER" => false,
						);
					else
						$exFILTER[0][]=array(
							"MODULE_ID" => "socialnetwork",
						);
				}
				elseif($strFILTER=="intranet")
				{
					$exFILTER[0][]=array(
						"=MODULE_ID" => "intranet",
					);
				}
			}
		}

		$arOthersFilter[] = $exFILTER;

		$j = 0;
		$obTitle = new CSearchTitle;
		if($obTitle->Search($arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"], $arParams["TOP_COUNT"], $exFILTER))
		{
			while($ar = $obTitle->Fetch())
			{
				$j++;
				if($j > $arParams["TOP_COUNT"])
				{
					$params = array("q" => $arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"]);

					$url = CHTTP::urlAddParams(
						str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
						,$params
						,array("encode"=>true)
					).CSearchTitle::MakeFilterUrl("f", $exFILTER);

					$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
						"NAME" => GetMessage("CC_BST_MORE"),
						"URL" => htmlspecialcharsex($url),
					);
					break;
				}
				else
				{
					$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
						"NAME" => $ar["NAME"],
						"URL" => htmlspecialchars($ar["URL"]),
						"MODULE_ID" => $ar["MODULE_ID"],
						"PARAM1" => $ar["PARAM1"],
						"PARAM2" => $ar["PARAM2"],
						"ITEM_ID" => $ar["ITEM_ID"],
					);
				}
			}
		}
		/* This code adds not fixed keyboard link to the category
		if($arResult["alt_query"] != "")
		{
			$params = array(
				"q" => $arResult["query"],
				"spell" => 1,
			);

			$url = CHTTP::urlAddParams(
				str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
				,$params
				,array("encode"=>true)
			).CSearchTitle::MakeFilterUrl("f", $exFILTER);

			$arResult["CATEGORIES"][$i]["ITEMS"][] = array(
				"NAME" => GetMessage("CC_BST_QUERY_PROMPT", array("#query#"=>$arResult["query"])),
				"URL" => htmlspecialcharsex($url),
			);
		}
		*/
		if(!$j)
		{
			unset($arResult["CATEGORIES"][$i]);
		}
	}

	if($arParams["SHOW_OTHERS"] === "Y")
	{
		$arResult["CATEGORIES"]["others"] = array(
			"TITLE" => htmlspecialchars($arParams["CATEGORY_OTHERS_TITLE"]),
			"ITEMS" => array(),
		);

		$j = 0;
		$obTitle = new CSearchTitle;
		if($obTitle->Search($arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"], $arParams["TOP_COUNT"], $arOthersFilter, true))
		{
			while($ar = $obTitle->Fetch())
			{
				$j++;
				if($j > $arParams["TOP_COUNT"])
				{
					//it's really hard to make it working
					break;
				}
				else
				{
					$arResult["CATEGORIES"]["others"]["ITEMS"][] = array(
						"NAME" => $ar["NAME"],
						"URL" => htmlspecialchars($ar["URL"]),
						"MODULE_ID" => $ar["MODULE_ID"],
						"PARAM1" => $ar["PARAM1"],
						"PARAM2" => $ar["PARAM2"],
						"ITEM_ID" => $ar["ITEM_ID"],
					);
				}
			}
		}

		if(!$j)
		{
			unset($arResult["CATEGORIES"]["others"]);
		}

	}

	if(!empty($arResult["CATEGORIES"]))
	{
		$arResult["CATEGORIES"]["all"] = array(
			"TITLE" => "",
			"ITEMS" => array()
		);

		$params = array(
			"q" => $arResult["alt_query"]? $arResult["alt_query"]: $arResult["query"],
		);
		$url = CHTTP::urlAddParams(
			str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
			,$params
			,array("encode"=>true)
		);
		$arResult["CATEGORIES"]["all"]["ITEMS"][] = array(
			"NAME" => GetMessage("CC_BST_ALL_RESULTS"),
			"URL" => $url,
		);
		/*
		if($arResult["alt_query"] != "")
		{
			$params = array(
				"q" => $arResult["query"],
				"spell" => 1,
			);

			$url = CHTTP::urlAddParams(
				str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"])
				,$params
				,array("encode"=>true)
			);

			$arResult["CATEGORIES"]["all"]["ITEMS"][] = array(
				"NAME" => GetMessage("CC_BST_ALL_QUERY_PROMPT", array("#query#"=>$arResult["query"])),
				"URL" => htmlspecialcharsex($url),
			);
		}
		*/
	}
}

$arResult["FORM_ACTION"] = htmlspecialchars(str_replace("#SITE_DIR#", SITE_DIR, $arParams["PAGE"]));

if (
    $_REQUEST["ajax_call"] === "y"
    && (
		!isset($_REQUEST["INPUT_ID"])
		|| $_REQUEST["INPUT_ID"] == $arParams["INPUT_ID"]
    )
)
{
	$APPLICATION->RestartBuffer();

	if(!empty($query))
		$this->IncludeComponentTemplate('ajax');
	die();
}
else
{
	$APPLICATION->AddHeadScript($this->GetPath().'/script.js');
	CUtil::InitJSCore(array('ajax'));
	$this->IncludeComponentTemplate();
}
?>
