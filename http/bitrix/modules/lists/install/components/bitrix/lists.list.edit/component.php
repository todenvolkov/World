<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLLE_MODULE_NOT_INSTALLED"));
	return;
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	$arParams["~IBLOCK_ID"] > 0? $arParams["~IBLOCK_ID"]: false,
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLLE_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLLE_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLLE_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLLE_UNKNOWN_ERROR"));
		return;
	}
}
elseif($lists_perm < CListPermissions::IS_ADMIN)
{
	ShowError(GetMessage("CC_BLLE_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = $lists_perm >= CListPermissions::IS_ADMIN;

$bBizProc = IsModuleInstalled('bizproc');
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));

if($arIBlock)
{
	$arResult["~IBLOCK"] = $arIBlock;
	$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
	$arResult["IBLOCK_ID"] = $arIBlock["ID"];
}
else
{
	$arResult["IBLOCK"] = false;
	$arResult["IBLOCK_ID"] = false;
}

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_fields";
$arResult["FORM_ID"] = "lists_list_edit";

$arResult["~LISTS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array("0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialchars($arResult["~LISTS_URL"]);

$arResult["~LIST_URL"] = CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], "0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => ""));
$arResult["LIST_URL"] = htmlspecialchars($arResult["~LIST_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialchars($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_FIELDS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_FIELDS_URL"]
);
$arResult["LIST_FIELDS_URL"] = htmlspecialchars($arResult["~LIST_FIELDS_URL"]);

//Assume there was no error
$bVarsFromForm = false;

//Form submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	//When Save or Apply buttons was pressed
	if(isset($_POST["save"]) || isset($_POST["apply"]))
	{
		//Gather fields for update
		$arFields = array(
			"NAME" => $_POST["NAME"],
			"IBLOCK_TYPE_ID" => $arParams["~IBLOCK_TYPE_ID"],
			"SORT" => $_POST["SORT"],
			"WORKFLOW" => "N",
			"ELEMENTS_NAME" => $_POST["ELEMENTS_NAME"],
			"ELEMENT_NAME" => $_POST["ELEMENT_NAME"],
			"ELEMENT_ADD" => $_POST["ELEMENT_ADD"],
			"ELEMENT_EDIT" => $_POST["ELEMENT_EDIT"],
			"ELEMENT_DELETE" => $_POST["ELEMENT_DELETE"],
			"SECTIONS_NAME" => $_POST["SECTIONS_NAME"],
			"SECTION_NAME" => $_POST["SECTION_NAME"],
			"SECTION_ADD" => $_POST["SECTION_ADD"],
			"SECTION_EDIT" => $_POST["SECTION_EDIT"],
			"SECTION_DELETE" => $_POST["SECTION_DELETE"],
		);

		if($arParams["SOCNET_GROUP_ID"])
			$arFields["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];

		if($bBizProc)
			$arFields["BIZPROC"] = $_POST["BIZPROC"]=="Y"? "Y": "N";

		$arSites = array(SITE_ID);
		if($arIBlock)
		{
			$rsSite = CIBlock::GetSite($arIBlock["ID"]);
			while($ar = $rsSite->Fetch())
				$arSites[] = $ar["SITE_ID"];
		}
		$arFields["SITE_ID"] = $arSites;

		if(isset($_FILES["PICTURE"]))
			$arFields["PICTURE"] = $_FILES["PICTURE"];
		if(isset($_POST["PICTURE_del"]))
			$arFields["PICTURE"]["del"] = "Y";

		//Update existing or add new
		$ob = new CIBlock;
		if($arIBlock)
		{
			$res = $ob->Update($arIBlock["ID"], $arFields);
			if($res)
				$res = $arIBlock["ID"];
		}
		else
		{
			$res = $ob->Add($arFields);
			if($res)
			{
				$obList = new CList($res);
				$obList->AddField(array(
					"SORT" => 10,
					"NAME" => GetMessage("CC_BLLE_NAME_FIELD"),
					"IS_REQUIRED" => "Y",
					"MULTIPLE" => "N",
					"TYPE" => "NAME",
					"DEFAULT_VALUE" => "",
				));
				$obList->Save();
			}
		}

		if($res && $arParams["SOCNET_GROUP_ID"])
		{
			//Successfull update and social network
			CLists::SetSocnetPermission($res, $_POST["GROUPS"]);
		}

		if($res && !$arParams["SOCNET_GROUP_ID"])
		{
			//Successfull update set iblock permissions

			//Set iblock permissions
			$arIBlockPerms = CIBlock::GetGroupPermissions($res);
			$bChanged = false;

			//At first "filter" input
			$arNewPerms = array();
			if(is_array($_POST["GROUPS"]))
			{
				foreach($_POST["GROUPS"] as $arPerm)
				{
					if(
						isset($arPerm["GROUP"]) && $arPerm["GROUP"] > 0
						&& isset($arPerm["PERM"])
					)
					{
						if(strlen($arPerm["PERM"]) == 1 && strpos("DRUW", $arPerm["PERM"])!==false)
							$arNewPerms[intval($arPerm["GROUP"])] = $arPerm["PERM"];
						elseif(strlen($arPerm["PERM"]) == 0)
							$arNewPerms[intval($arPerm["GROUP"])] = false;
					}
				}
			}

			//Read changed permissions, but w/o administrative.
			foreach($arIBlockPerms as $group_id => $perm)
			{
				if($perm <= "W" && array_key_exists($group_id, $arNewPerms))
				{
					if($perm != $arNewPerms[$group_id])
					{
						$bChanged = true;
						if($arNewPerms[$group_id]!==false)
							$arIBlockPerms[$group_id] = $arNewPerms[$group_id];
						else
							unset($arIBlockPerms[$group_id]);
					}
					unset($arNewPerms[$group_id]);
				}
			}

			//Read added permissions
			foreach($arNewPerms as $group_id => $perm)
			{
				if($perm && !array_key_exists($group_id, $arIBlockPerms))
				{
					$bChanged = true;
					$arIBlockPerms[$group_id] = $perm;
				}
			}

			if($bChanged)
			{
				CIBlock::SetPermission($res, $arIBlockPerms);
			}
		}

		if($res)
		{
			//Clear components cache
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag("lists_list_".$res);
			$CACHE_MANAGER->ClearByTag("lists_list_any");
			$CACHE_MANAGER->CleanDir("menu");

			$tab_name = $arResult["FORM_ID"]."_active_tab";

			//And go to proper page
			if(isset($_POST["save"]))
				LocalRedirect($arResult["LISTS_URL"]);
			elseif($arIBlock)
				LocalRedirect(CHTTP::urlAddParams(
					$arResult["LIST_EDIT_URL"],
					array($tab_name => $_POST[$tab_name]),
					array("skip_empty" => true, "encode" => true)
				));
			else
				LocalRedirect(CHTTP::urlAddParams(str_replace(
					array("#list_id#", "#group_id#"),
					array($res, $arParams["SOCNET_GROUP_ID"]),
					$arParams["LIST_EDIT_URL"]
					),
					array($tab_name => $_POST[$tab_name]),
					array("skip_empty" => true, "encode" => true)
				));
		}
		else
		{
			ShowError($ob->LAST_ERROR);
			$bVarsFromForm = true;
		}
	}
	elseif(isset($_POST["action"]) && $_POST["action"]==="delete" && $arResult["IBLOCK_ID"])
	{
		if(CIBlock::Delete($arResult["IBLOCK_ID"]))
		{
			//Clear components cache
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag("lists_list_".$res);
			$CACHE_MANAGER->ClearByTag("lists_list_any");
			$CACHE_MANAGER->CleanDir("menu");
		}
		LocalRedirect($arResult["~LISTS_URL"]);
	}
	else
	{
		//Go to lists page
		LocalRedirect($arResult["~LISTS_URL"]);
	}
}

$data = array();

if($bVarsFromForm)
{//There was an error so display form values
	$data["ID"] = $arIBlock? $arIBlock["ID"]: "";
	$data["NAME"] = $_POST["NAME"];
	$data["SORT"] = $_POST["SORT"];
	if($bBizProc)
		$data["BIZPROC"] = $_POST["BIZPROC"];
	$data["PICTURE"] = $arIBlock? $arIBlock["PICTURE"]: "";
	$data["ELEMENTS_NAME"] = $_POST["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $_POST["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $_POST["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $_POST["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $_POST["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $_POST["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $_POST["SECTION_NAME"];
	$data["SECTION_ADD"] = $_POST["SECTION_ADD"];
	$data["SECTION_EDIT"] = $_POST["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $_POST["SECTION_DELETE"];
}
elseif($arIBlock)
{//Edit existing iblock
	$data["ID"] = $arIBlock["ID"];
	$data["NAME"] = $arIBlock["NAME"];
	$data["SORT"] = $arIBlock["SORT"];
	if($bBizProc)
		$data["BIZPROC"] = $arIBlock["BIZPROC"];
	$data["PICTURE"] = $arIBlock["PICTURE"];
	$data["ELEMENTS_NAME"] = $arIBlock["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $arIBlock["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $arIBlock["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $arIBlock["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $arIBlock["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $arIBlock["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $arIBlock["SECTION_NAME"];
	$data["SECTION_ADD"] = $arIBlock["SECTION_ADD"];
	$data["SECTION_EDIT"] = $arIBlock["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $arIBlock["SECTION_DELETE"];
}
else
{//New one
	$data["ID"] = "";
	$data["NAME"] = GetMessage("CC_BLLE_FIELD_NAME_DEFAULT");
	$data["SORT"] = 500;
	if($bBizProc)
		$data["BIZPROC"] = "Y";
	$data["PICTURE"] = "";
	$arMessages = CIBlock::GetMessages(0, $arParams["~IBLOCK_TYPE_ID"]);
	$data["ELEMENTS_NAME"] = $arMessages["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $arMessages["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $arMessages["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $arMessages["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $arMessages["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $arMessages["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $arMessages["SECTION_NAME"];
	$data["SECTION_ADD"] = $arMessages["SECTION_ADD"];
	$data["SECTION_EDIT"] = $arMessages["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $arMessages["SECTION_DELETE"];
}

$arResult["FORM_DATA"] = array();
foreach($data as $key => $value)
{
	$arResult["FORM_DATA"][$key] = htmlspecialchars($value);
	$arResult["FORM_DATA"]["~".$key] = $value;
}

if($arParams["SOCNET_GROUP_ID"])
{
	$arSocnetPerm = CLists::GetSocnetPermission($arResult["IBLOCK_ID"]);

	$arResult["IBLOCK_GROUPS"] = array();
	$arResult["GROUPS"] = array(
		//"A" => GetMessage("CC_BLLE_ROLE_GROUP_ADMIN"),
		"E" => GetMessage("CC_BLLE_ROLE_GROUP_MODERATOR"),
		"K" => GetMessage("CC_BLLE_ROLE_GROUP_MEMBER"),
		"L" => GetMessage("CC_BLLE_ROLE_GROUP_AUTHORIZED"),
		"N" => GetMessage("CC_BLLE_ROLE_GROUP_EVERYONE"),
		//"T" => GetMessage("CC_BLLE_ROLE_GROUP_BLACKLISTED"),
		//"Z" => Request
	);

	foreach($arSocnetPerm as $role => $permission)
	{
		if(isset($arResult["GROUPS"][$role]))
			$arResult["IBLOCK_GROUPS"][$role] = $permission;
	}
}
else
{
	$arIBlockPerms = CIBlock::GetGroupPermissions($arResult["IBLOCK_ID"]);
	$arListsPerm = CLists::GetPermission($arParams["~IBLOCK_TYPE_ID"]);

	$arResult["IBLOCK_GROUPS"] = array();
	$arResult["GROUPS"] = array();
	$rsGroups = CGroup::GetList($by="sort", $order="asc");
	while($ar = $rsGroups->Fetch())
	{
		if(
			!in_array($ar["ID"], $arListsPerm) //exclude groups which have permissions to create iblocks in this type
		)
		{
			if(array_key_exists($ar["ID"], $arIBlockPerms))
			{
				if($arIBlockPerms[$ar["ID"]] <= "W")
				{
					$arResult["GROUPS"][$ar["ID"]] = "[".$ar["ID"]."] ".htmlspecialchars($ar["NAME"]);
					$arResult["IBLOCK_GROUPS"][$ar["ID"]] = $arIBlockPerms[$ar["ID"]];
				}
			}
			else
			{
				$arResult["GROUPS"][$ar["ID"]] = "[".$ar["ID"]."] ".htmlspecialchars($ar["NAME"]);
			}
		}
	}
}

if($arIBlock["WORKFLOW"] == "Y")
	$arResult["RIGHTS"] = array(
		"D"=>GetMessage("CC_BLLE_ACCESS_D"),
		"R"=>GetMessage("CC_BLLE_ACCESS_R"),
		"U"=>GetMessage("CC_BLLE_ACCESS_U"),
		"W"=>GetMessage("CC_BLLE_ACCESS_W"),
		//"X"=>GetMessage("CC_BLLE_ACCESS_X"),
	);
elseif($arIBlock["BIZPROC"] == "Y")
	$arResult["RIGHTS"] = array(
		"D"=>GetMessage("CC_BLLE_ACCESS_D"),
		"R"=>GetMessage("CC_BLLE_ACCESS_R"),
		"U"=>GetMessage("CC_BLLE_ACCESS_U2"),
		"W"=>GetMessage("CC_BLLE_ACCESS_W"),
		//"X"=>GetMessage("CC_BLLE_ACCESS_X"),
	);
else
	$arResult["RIGHTS"] = array(
		"D"=>GetMessage("CC_BLLE_ACCESS_D"),
		"R"=>GetMessage("CC_BLLE_ACCESS_R"),
		"W"=>GetMessage("CC_BLLE_ACCESS_W"),
		//"X"=>GetMessage("CC_BLLE_ACCESS_X"),
	);

$this->IncludeComponentTemplate();

if($arIBlock)
	$APPLICATION->SetTitle(GetMessage("CC_BLLE_TITLE_EDIT", array("#NAME#" => $arResult["IBLOCK"]["NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("CC_BLLE_TITLE_NEW"));

if($arResult["IBLOCK_ID"])
	$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], $arResult["~LIST_URL"]);

$APPLICATION->AddChainItem(GetMessage("CC_BLLE_CHAIN_EDIT"), $arResult["~LIST_EDIT_URL"]);
?>