<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

$arParams["SET_NAVCHAIN"] = ($arParams["SET_NAVCHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

	if (!$arGroup || !is_array($arGroup) || $arGroup["ACTIVE"] != "Y" || $arGroup["SITE_ID"] != SITE_ID)
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
	}
	else
	{
		$arResult["Group"] = $arGroup;

		$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());

		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $GLOBALS["USER"]->GetID()));
		$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));

		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C9_TITLE"));

		if ($arParams["SET_NAVCHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C9_TITLE"));
		}

		if (!$arResult["CurrentUserPerms"]["UserCanModifyGroup"])
		{
			$arResult["FatalError"] = GetMessage("SONET_C9_NO_PERMS").". ";
		}
		else
		{
			$arResult["ShowForm"] = "Input";
			if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
			{
				$errorMessage = "";

				if (strlen($errorMessage) <= 0)
				{
					if (!CSocNetGroup::Delete($arResult["Group"]["ID"]))
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if (strlen($errorMessage) > 0)
					$arResult["ErrorMessage"] = $errorMessage;
				else
					$arResult["ShowForm"] = "Confirm";
			}
		}
	}
}
$this->IncludeComponentTemplate();
?>