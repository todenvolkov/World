<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists("__array_stretch"))
{
	function __array_stretch($arGroup, $depth = 0)
	{
		$arResult = array();
		
		if (intVal($arGroup["ID"]) > 0)
		{
			$arResult["GROUP_".$arGroup["ID"]] = $arGroup;
			unset($arResult["GROUP_".$arGroup["ID"]]["GROUPS"]);
			unset($arResult["GROUP_".$arGroup["ID"]]["FORUM"]);
			$arResult["GROUP_".$arGroup["ID"]]["DEPTH"] = $depth; 
			$arResult["GROUP_".$arGroup["ID"]]["TYPE"] = "GROUP"; 
		}
		if (array_key_exists("FORUMS", $arGroup))
		{
			foreach ($arGroup["FORUMS"] as $res)
			{
				$arResult["FORUM_".$res["ID"]] = $res; 
				$arResult["FORUM_".$res["ID"]]["DEPTH"] = $depth; 
				$arResult["FORUM_".$res["ID"]]["TYPE"] = "FORUM"; 
			}
		}
				
		if (array_key_exists("GROUPS", $arGroup))
		{
			$depth++;
			foreach ($arGroup["GROUPS"] as $key => $val)
			{
				$res = __array_stretch($arGroup["GROUPS"][$key], $depth);
				$arResult = array_merge($arResult, $res);
			}
		}
		return $arResult;
	}
}
/********************************************************************
				Default params
********************************************************************/
$arResult["MAIN_PANEL"] = array(
	"SHOW" => "N", 
	"FORUMS_LIST" => ($arResult["SHOW_FORUMS_LIST"] == "Y" ? "Y" : "N"), 
	"FORUMS" => ($arParams["SHOW_ADD_MENU"] == "Y" && $arResult["IsAuthorized"] != "Y" && ($arResult["sSection"] == "INDEX" || $arResult["sSection"] == "LIST")) ? "Y" : "N");
$arResult["MAIN_PANEL"]["SHOW"] = (in_array("Y", $arResult["MAIN_PANEL"]) ? "Y" : "N");
$arResult["MANAGE_PANEL"] = array(
	"SHOW" => "N", 
	"SUBSCRIBE" => ($arResult["IsAuthorized"] == "Y" && in_array($arResult["sSection"], array("LIST", "READ")) ? "Y" : "N"), 
	"TOPICS" => ($arResult["UserPermission"] >= "Q" && in_array($arResult["sSection"], array("LIST", "READ")) ? "Y" : "N"), 
	"MESSAGES" => ($arResult["UserPermission"] >= "Q" && in_array($arResult["sSection"], array("MESSAGE_APPR", "READ")) ? "Y" : "N"), 
	"FORUMS" => ($arResult["IsAuthorized"] == "Y" && $arParams["SHOW_ADD_MENU"] == "Y" && in_array($arResult["sSection"], array("INDEX", "LIST")) ? "Y" : "N"));
$arResult["MANAGE_PANEL"]["SHOW"] = (in_array("Y", $arResult["MANAGE_PANEL"]) ? "Y" : "N");
$arResult["set_be_read"] = ForumAddPageParams(
	$arResult[strToLower($arResult["PAGE_NAME"])], 
	array("ACTION" => "SET_BE_READ"))."&amp;".bitrix_sessid_get();
/* POPUPS */
$arResult["popup"]["forums"] = array();
$arResult["popup"]["subscribe"] = array();
$arResult["popup"]["topics"] = array();
$arResult["popup"]["messages"] = array();

$arResult["GROUPS_FORUMS"] = __array_stretch($arResult["GROUPS_FORUMS"]);
/********************************************************************
				/Default params
********************************************************************/
?>