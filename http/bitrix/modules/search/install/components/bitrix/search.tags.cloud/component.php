<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!function_exists("__sortName"))
{
	function __sortName($this, $next)
	{
		if ($this["NAME_HTML"] != $next["NAME_HTML"])
		{
			$arSort = array($this["NAME_HTML"], $next["NAME_HTML"]);
			sort($arSort);
			if ($arSort[0] != $this["NAME_HTML"])
				return 1;
		}
		elseif (intVal($this["CNT"]) < intVal($next["CNT"]))
		{
			return 1;
		}
		return -1;
	}
}

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["SORT"] = ($arParams["SORT"] == "CNT" ? "CNT" : "NAME");
$arParams["SORT_BY"] = ($arParams["SORT_BY"] == "ASC" ? "ASC" : "DESC");
$arParams["PAGE_ELEMENTS"] = ((intVal($arParams["PAGE_ELEMENTS"]) > 0) ? intVal($arParams["PAGE_ELEMENTS"]) : 1000);
$arParams["PERIOD"] = intVal($arParams["PERIOD"]);
$arParams["CHECK_DATES"] = ($arParams["CHECK_DATES"]=="Y" ? true : false);
$arParams["~TAGS"] = (empty($arParams["~TAGS"]) ? $_REQUEST["tags"] : $arParams["~TAGS"]);
$arParams["~TAGS"] = trim($arParams["~TAGS"]);
$arParams["TAGS"] = htmlspecialcharsex($arParams["~TAGS"]);
$arParams["SEARCH"] = (empty($arParams["SEARCH"]) ? $_REQUEST["q"] : $arParams["~SEARCH"]);
$arParams["~SEARCH"] = trim($arParams["SEARCH"]);
$arParams["SEARCH"] = htmlspecialchars($arParams["~SEARCH"]);

if (!empty($arParams["URL_SEARCH"]))
{
	$arResult["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["URL_SEARCH"], array("TAGS" => "tags=#TAGS#"));
	if (strpos($arResult["~URL"], "#TAGS#") === false)
	{
		if (strpos($arResult["~URL"], "?") === false)
			$arResult["~URL"] .= "?";
		else
			$arResult["~URL"] .= "&";
		$arResult["~URL"] .= "tags=#TAGS#";
	}
}
else
{
	$arResult["~URL"] = $APPLICATION->GetCurPageParam("tags=#TAGS#", array("tags"));
}
$arResult["URL"] = htmlspecialchars($arResult["~URL"]);

$exFILTER = array("LIMIT" => $arParams["PAGE_ELEMENTS"]);
if(is_array($arParams["arrFILTER"]))
{
	foreach($arParams["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			if(!is_array($arParams["arrFILTER_main"]))
				$arParams["arrFILTER_main"]=array();
			$arURL=array();
			foreach($arParams["arrFILTER_main"] as $strURL)
				$arURL[]=$strURL."%";
			if(count($arURL)<=0)
				$arURL[]="/%";
			$exFILTER[]=array(
				"MODULE_ID" => "main",
				"URL" => $arURL,
			);
		}
		elseif($strFILTER=="forum")
		{
			if(!is_array($arParams["arrFILTER_forum"]))
				$arParams["arrFILTER_forum"] = array();
			$arForum=array();
			foreach($arParams["arrFILTER_forum"] as $strForum)
				if($strForum<>"all")
					$arForum[]=intval($strForum);
			if(count($arForum)>0)
				$exFILTER[]=array(
					"MODULE_ID" => "forum",
					"PARAM1" => $arForum,
				);
			else
				$exFILTER[]=array(
					"MODULE_ID" => "forum",
				);
		}
		elseif((strpos($strFILTER, "iblock_") === 0))
		{
			if(!is_array($arParams["arrFILTER_".$strFILTER]))
				$arParams["arrFILTER_".$strFILTER] = array();
			$arIBlock=array();
			foreach($arParams["arrFILTER_".$strFILTER] as $strIBlock)
				if($strIBlock<>"all")
					$arIBlock[]=intval($strIBlock);
			if(count($arIBlock)>0)
				$exFILTER[]=array(
					"MODULE_ID" => "iblock",
					"PARAM1" => substr($strFILTER, 7),
					"PARAM2" => $arIBlock,
				);
			else
				$exFILTER[]=array(
					"MODULE_ID" => "iblock",
					"PARAM1" => substr($strFILTER, 7),
				);
		}
		elseif($strFILTER=="blog")
		{
			if(!is_array($arParams["arrFILTER_blog"]))
				$arParams["arrFILTER_blog"] = array();
			$arBlog=array();
			foreach($arParams["arrFILTER_blog"] as $strBlog)
				if($strBlog<>"all")
					$arBlog[]=intval($strBlog);
			if(count($arBlog)>0)
				$exFILTER[]=array(
					"MODULE_ID" => "blog",
					"PARAM1" => "POST",
					"PARAM2" => $arBlog,
				);
			else
				$exFILTER[]=array(
					"MODULE_ID" => "blog",
				);
		}
		elseif($strFILTER=="socialnetwork")
		{
			if(!is_array($arParams["arrFILTER_socialnetwork"]))
				$arParams["arrFILTER_socialnetwork"]=array();
			$arSCGroups=array();
			foreach($arParams["arrFILTER_socialnetwork"] as $strSCGroup)
				if($strSCGroup<>"all")
					$arSCGroups[] = intval($strSCGroup);

			if(count($arSCGroups)>0)
				$exFILTER[]=array(
					"PARAMS" => array("socnet_group" => $arSCGroups),
					"USE_TF_FILTER" => false,
				);
			else
				$exFILTER[]=array(
					"MODULE_ID" => "socialnetwork",
				);
		}
		elseif($strFILTER=="socialnetwork_user")
		{
			$intSCUser = intval($arParams["arrFILTER_socialnetwork_user"]);
			if($intSCUser > 0)
				$exFILTER[]=array(
					"PARAMS" => array("socnet_user" => $intSCUser),
					"USE_TF_FILTER" => false,
				);
			else
				$exFILTER[]=array(
					"MODULE_ID" => "socialnetwork",
				);
		}
		elseif($strFILTER=="intranet")
		{
			$exFILTER[]=array(
				"MODULE_ID" => "intranet",
			);
		}
	}
}

if (!empty($arParams["~TAGS"]) || !empty($arParams["~SEARCH"]))
{
	$arParams["CACHE_TIME"] = 0;
}

if ($this->StartResultCache(false, array($USER->GetGroups())))
{

	if(!CModule::IncludeModule("search"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("BSF_C_MODULE_NOT_INSTALLED"));
		return;
	}

	$arFilter = array(
		"SITE_ID" => SITE_ID,
		"QUERY" => $arParams["~SEARCH"],
		"TAGS" => $arParams["~TAGS"] ? $arParams["~TAGS"] : "",
	);
	if ($arParams["PERIOD"] > 0)
		$arFilter["DATE_CHANGE"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), time()-($arParams["PERIOD"]*24*3600));
	if($arParams["CHECK_DATES"])
		$arFilter["CHECK_DATES"]="Y";

	$obSearch = new CSearch();
	$obSearch->Search($arFilter, array("CNT" => "DESC"), $exFILTER, true);

	$arResult["ERROR_CODE"] = $obSearch->errorno;
	$arResult["ERROR_TEXT"] = $obSearch->error;

	$arResult["DATE"] = array();
	$arResult["SEARCH"] = array();
	if($obSearch->errorno==0)
	{
		$res = $obSearch->GetNext();
		if(!$res && ($arParams["RESTART"] == "Y") && $obSearch->Query->bStemming)
		{
			$exFILTER["STEMMING"] = false;
			$obSearch = new CSearch();
			$obSearch->Search($arFilter, array("CNT" => "DESC"), $exFILTER, true);

			$arResult["ERROR_CODE"] = $obSearch->errorno;
			$arResult["ERROR_TEXT"] = $obSearch->error;

			if($obSearch->errorno == 0)
			{
				$res = $obSearch->GetNext();
			}
		}

		if($res)
		{
			$arResult["CNT_MIN"] = $res["CNT"];
			$arResult["CNT_MAX"] = $res["CNT"];
			$res["TIME"] = MakeTimeStamp($res["FULL_DATE_CHANGE"]);
			$arResult["TIME_MIN"] = $res["TIME"];
			$arResult["TIME_MAX"] = $res["TIME"];

			$arTags = array();
			if (($arParams["TAGS_INHERIT"] != "N") && (strlen($arParams["TAGS"]) > 0))
			{
				$tmp = explode(",", $arParams["~TAGS"]);
				foreach($tmp as $tag)
				{
					$tag = trim($tag);
					if(strlen($tag) > 0)
						$arTags[$tag] = $tag;
				}
			}

			do
			{
				$arResult["CNT_ALL"] += $res["CNT"];
				if ($arResult["CNT_MIN"] > $res["CNT"])
					$arResult["CNT_MIN"] = $res["CNT"];
				elseif ($arResult["CNT_MAX"] < $res["CNT"])
					$arResult["CNT_MAX"] = $res["CNT"];

				$res["TIME"] = MakeTimeStamp($res["FULL_DATE_CHANGE"]);

				if ($arResult["TIME_MIN"] > $res["TIME"])
					$arResult["TIME_MIN"] = $res["TIME"];
				elseif ($arResult["TIME_MAX"] < $res["TIME"])
					$arResult["TIME_MAX"] = $res["TIME"];

				$tags = $res["~NAME"];
				if (count($arTags) > 0)
				{
					if(array_key_exists($tags, $arTags))
						$tags = implode(",", $arTags);
					else
						$tags .= ",".implode(",", $arTags);
				}

				$res["URL"] = str_replace("#TAGS#", urlencode($tags), $arResult["URL"]);

				$res["NAME_HTML"] = ToLower($res["NAME"]);

				$arResult["SEARCH"][] = $res;
				$arResult["CNT"][$res["NAME"]] = $res["CNT"];
				$arResult["DATE"][$res["NAME"]] = $res["TIME"];
			} while ($res = $obSearch->getNext());
		}
	}
	if ($arParams["SORT"] != "CNT")
		uasort($arResult["SEARCH"], "__sortName");

	$arResult["TAGS_CHAIN"] = array();
	if ($arParams["~TAGS"])
	{
		$res = array_unique(explode(",", $arParams["~TAGS"]));
		$url = array();
		foreach ($res as $key => $tags)
		{
			$tags = trim($tags);
			if (!empty($tags))
			{
				$url_without = $res;
				unset($url_without[$key]);
				$url[$tags] = $tags;
				$result = array(
					"TAG_NAME" => htmlspecialcharsex($tags),
					"TAG_PATH" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url)), array("tags")),
					"TAG_WITHOUT" => $APPLICATION->GetCurPageParam((count($url_without) > 0 ? "tags=".urlencode(implode(",", $url_without)) : ""), array("tags")),
				);
				$arResult["TAGS_CHAIN"][] = $result;
			}
		}
	}
	$this->IncludeComponentTemplate();
}
?>