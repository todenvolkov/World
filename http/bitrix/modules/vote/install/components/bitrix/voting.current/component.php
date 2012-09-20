<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
if (!CModule::IncludeModule("vote")): 
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
endif;


/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = intVal($arParams["VOTE_ID"]);
	$arParams["CHANNEL_SID"] = trim($arParams["CHANNEL_SID"]);
/************** CACHE **********************************************/
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
$obCache = new CPHPCache;
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["CHANNEL_SID"]."/");
$cache_id = "vote_current_".serialize($arParams); //."_".$USER->GetGroups();
$cache_path = $cache_path_main;
if (!$obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path) || true)
{
	$arVote = array();
	$arChannel = array();
	if ($arParams["VOTE_ID"] > 0)
	{
		$db_res = CVote::GetList($by, $order, array("ID" => $arParams["VOTE_ID"], "ACTIVE" => "Y"), $is_filtered);
		if (!($db_res && $arVote = $db_res->Fetch())):
			return;
		else:
			$db_res = CVoteChannel::GetList($by, $order, array("ID" => $arVote["CHANNEL_ID"], "SITE" => SITE_ID, "ACTIVE" => "Y"), $is_filtered);
			if (!($db_res && $arChannel = $db_res->Fetch())):
				ShowError(GetMessage("VOTE_CHANNEL_NOT_FOUND"));
				return;
			endif;
		endif;
	}
	else
	{
		$obChannel = CVoteChannel::GetList($by, $order, array(
				"SID"=> $arParams["CHANNEL_SID"], "SID_EXACT_MATCH" => "Y", "SITE" => SITE_ID, "ACTIVE" => "Y"),
				$is_filtered);
		if (!$arChannel = $obChannel->Fetch()):
			ShowError(GetMessage("VOTE_CHANNEL_NOT_FOUND"));
			return;
		endif;
		//Get current vote
		$obVote = CVote::GetList($by , $order, array("CHANNEL_ID"=>$arChannel["ID"], "LAMP" => "green"), $is_filtered);
		if (!$arVote = $obVote->GetNext())
			return;
	}
	$arResult = array(
		"VOTE" => $arVote,
		"VOTE_ID" => $arVote["ID"],
		"VOTE_RESULT_TEMPLATE" => $APPLICATION->GetCurPageParam('', array('VOTE_SUCCESSFULL', 'VOTE_ID')),
		"ADDITIONAL_CACHE_ID" => "current_vote");
	$obCache->StartDataCache();
	$obCache->EndDataCache(array("arResult" => $arResult));
}
else
{
	$arVars = $obCache->GetVars();
	$arResult = $arVars["arResult"];
//	$this->SetTemplateCachedData($arVars["templateCachedData"]);
}

$permission = CVoteChannel::GetGroupPermission($arResult["VOTE"]["CHANNEL_ID"]);
if ($permission == 0)
	return false;
$voteUserID = intval($GLOBALS["APPLICATION"]->get_cookie("VOTE_USER_ID"));
$isUserVoted = CVote::UserAlreadyVote($arResult["VOTE_ID"], $voteUserID, $arResult["VOTE"]["UNIQUE_TYPE"], $arResult["VOTE"]["KEEP_IP_SEC"], $GLOBALS["USER"]->GetID());
$arResult["CAN_VOTE"] = (!$isUserVoted && $permission > 1 ? "Y" : "N");

global $VOTING_ID;
if ($GLOBALS["VOTING_OK"] == "Y" && ($VOTING_ID == $arParams['VOTE_ID']) && !empty($arParams["VOTE_RESULT_TEMPLATE"]))
{
	$strNavQueryString = DeleteParam(array("VOTE_ID", "VOTING_OK", "VOTE_SUCCESSFULL", "view_result"));
	$strNavQueryString = ($strNavQueryString <> "" ? "&".$strNavQueryString : $strNavQueryString);
	$url = CComponentEngine::MakePathFromTemplate($arParams["VOTE_RESULT_TEMPLATE"], array("VOTE_ID" => $arParams["VOTE_ID"]));
	$url = (strpos($url, "?") == false ? $url."?" : $url."&");
    LocalRedirect($url."VOTE_SUCCESSFULL=Y&VOTE_ID=".intval($_REQUEST['VOTE_ID']).$strNavQueryString);
}
elseif (($GLOBALS["VOTING_OK"] =="Y" && ($VOTING_ID == $arResult['VOTE_ID'])) || $GLOBALS["USER_ALREADY_VOTE"] =="Y" || $permission == 1 || $isUserVoted || 
	$_REQUEST["view_result"] == "Y" || ($_REQUEST["VOTE_SUCCESSFULL"] == "Y") && ($_REQUEST['VOTE_ID'] == $arResult['VOTE_ID']))
{
    $componentPage = "result";
}
else
{
    $componentPage = "form";
}

$this->IncludeComponentTemplate($componentPage);
?>
