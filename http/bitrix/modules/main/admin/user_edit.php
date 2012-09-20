<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog_user.php");
define("HELP_FILE", "users/user_edit.php");
$strRedirect_admin = BX_ROOT."/admin/user_admin.php?lang=".LANG;
$strRedirect = BX_ROOT."/admin/user_edit.php?lang=".LANG;

ClearVars();

if (!($USER->CanDoOperation('view_own_profile') || $USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$PROPERTY_ID = "USER";

if($USER->CanDoOperation('edit_own_profile') && !($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users')))
{
	$ID = $USER->GetID();
	if (intval($ID)<=0) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	$COPY_ID = 0;
}
IncludeModuleLangFile(__FILE__);

/***************************************************************************
					Обработка GET | POST
****************************************************************************/
$ID=IntVal($ID);
$COPY_ID=intval($COPY_ID);

$uid = $USER->GetID();

if($COPY_ID<=0)
{
	$arUserGroups = CUser::GetUserGroup($ID);
}
else
{
	$arUserGroups = array();
	$ID = $COPY_ID;
}

$selfEdit = ($USER->CanDoOperation('edit_own_profile') && $ID == $uid);

if($USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users'))
{
	$arUserSubordinateGroups = Array(2);
	$arUserGroups_u = CUser::GetUserGroup($uid);
	for ($j = 0,$len = count($arUserGroups_u); $j < $len; $j++)
	{
		$arSubordinateGroups = CGroup::GetSubordinateGroups($arUserGroups_u[$j]);
		$arUserSubordinateGroups = array_merge ($arUserSubordinateGroups, $arSubordinateGroups);
	}
	$arUserSubordinateGroups = array_unique($arUserSubordinateGroups);

	if (count(array_diff($arUserGroups, $arUserSubordinateGroups)) > 0 && !$selfEdit)
		LocalRedirect(BX_ROOT."/admin/user_admin.php?lang=".LANG);
}

$editable = ($USER->IsAdmin() ||
	$selfEdit ||
	($USER->CanDoOperation('edit_subordinate_users') && !in_array(1, $arUserGroups)) ||
	($USER->CanDoOperation('edit_all_users') && !in_array(1, $arUserGroups))
);


$canSelfEdit = true;
if($ID==$uid && !($USER->CanDoOperation('edit_php') || ($USER->CanDoOperation('edit_all_users') && $USER->CanDoOperation('edit_groups'))))
	$canSelfEdit = false;

$showGroupTabs = (($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')) && $canSelfEdit);

$strError="";
$message=null;

$aTabs = Array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("MAIN_USER_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAIN_USER_TAB1_TITLE"));

if($showGroupTabs)
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("GROUPS"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAIN_USER_TAB2_TITLE"));
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("USER_PERSONAL_INFO"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("USER_PERSONAL_INFO"));
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("MAIN_USER_TAB4"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("USER_WORK_INFO"));
$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("USER_RATING_INFO"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("USER_RATING_INFO"));

$i = 1;
$db_opt_res = CModule::GetList();
while ($opt_res = $db_opt_res->Fetch())
{
	$mdir = $opt_res["ID"];
	if (file_exists($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir) && is_dir($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir))
	{
		$ofile = $DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir."/options_user_settings.php";
		if(file_exists($ofile))
		{
			include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$mdir."/lang/", "/options_user_settings.php"));
			$aTabs[] = array("DIV" => "edit".($i+5), "TAB" => GetMessage($mdir."_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage($mdir."_TAB_TITLE"));
			$i++;
		}
	}
}

if(($editable && $ID!=$USER->GetID()) || $USER->IsAdmin())
	$aTabs[] = array("DIV" => "edit".($i+5), "TAB" => GetMessage("MAIN_USER_TAB5"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("USER_ADMIN_NOTES"));

//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields($PROPERTY_ID)) > 0) ||
	($USER_FIELD_MANAGER->GetRights($PROPERTY_ID) >= "W")
)
{
	$aTabs[] = $USER_FIELD_MANAGER->EditFormTab($PROPERTY_ID);
}

$tabControl = new CAdminForm("user_edit", $aTabs);

$strError="";
$res=true;
if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0 || $Update=="Y") && $editable && check_bitrix_sessid())
{
	$user = new CUser;

	if ($ID=="1" && $COPY_ID<=0)
		$ACTIVE = "Y";

	$arPERSONAL_PHOTO = $HTTP_POST_FILES["PERSONAL_PHOTO"];
	$arWORK_LOGO = $HTTP_POST_FILES["WORK_LOGO"];

	$arUser = false;
	if($ID>0)
	{
		$dbUser = CUser::GetById($ID);
		$arUser = $dbUser->Fetch();
	}

	if($arUser)
	{
		$arPERSONAL_PHOTO["old_file"] = $arUser["PERSONAL_PHOTO"];
		$arPERSONAL_PHOTO["del"] = $PERSONAL_PHOTO_del;

		$arWORK_LOGO["old_file"] = $arUser["WORK_LOGO"];
		$arWORK_LOGO["del"] = $WORK_LOGO_del;
	}

	$arFields = Array(
		"NAME"					=> $NAME,
		"LAST_NAME"				=> $LAST_NAME,
		"SECOND_NAME"			=> $SECOND_NAME,
		"EMAIL"					=> $EMAIL,
		"LOGIN"					=> $LOGIN,
		"PERSONAL_PROFESSION"	=> $PERSONAL_PROFESSION,
		"PERSONAL_WWW"			=> $PERSONAL_WWW,
		"PERSONAL_ICQ"			=> $PERSONAL_ICQ,
		"PERSONAL_GENDER"		=> $PERSONAL_GENDER,
		"PERSONAL_BIRTHDAY"		=> $PERSONAL_BIRTHDAY,
		"PERSONAL_PHOTO"		=> $arPERSONAL_PHOTO,
		"PERSONAL_PHONE"		=> $PERSONAL_PHONE,
		"PERSONAL_FAX"			=> $PERSONAL_FAX,
		"PERSONAL_MOBILE"		=> $PERSONAL_MOBILE,
		"PERSONAL_PAGER"		=> $PERSONAL_PAGER,
		"PERSONAL_STREET"		=> $PERSONAL_STREET,
		"PERSONAL_MAILBOX"		=> $PERSONAL_MAILBOX,
		"PERSONAL_CITY"			=> $PERSONAL_CITY,
		"PERSONAL_STATE"		=> $PERSONAL_STATE,
		"PERSONAL_ZIP"			=> $PERSONAL_ZIP,
		"PERSONAL_COUNTRY"		=> $PERSONAL_COUNTRY,
		"PERSONAL_NOTES"		=> $PERSONAL_NOTES,
		"WORK_COMPANY"			=> $WORK_COMPANY,
		"WORK_DEPARTMENT"		=> $WORK_DEPARTMENT,
		"WORK_POSITION"			=> $WORK_POSITION,
		"WORK_WWW"				=> $WORK_WWW,
		"WORK_PHONE"			=> $WORK_PHONE,
		"WORK_FAX"				=> $WORK_FAX,
		"WORK_PAGER"			=> $WORK_PAGER,
		"WORK_STREET"			=> $WORK_STREET,
		"WORK_MAILBOX"			=> $WORK_MAILBOX,
		"WORK_CITY"				=> $WORK_CITY,
		"WORK_STATE"			=> $WORK_STATE,
		"WORK_ZIP"				=> $WORK_ZIP,
		"WORK_COUNTRY"			=> $WORK_COUNTRY,
		"WORK_PROFILE"			=> $WORK_PROFILE,
		"WORK_LOGO"				=> $arWORK_LOGO,
		"WORK_NOTES"			=> $WORK_NOTES
		);

	if($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users'))
	{
		if(strlen($LID)>0)
			$arFields["LID"] = $LID;

		if(is_set($_REQUEST, 'EXTERNAL_AUTH_ID'))
			$arFields['EXTERNAL_AUTH_ID'] = $EXTERNAL_AUTH_ID;

		$arFields["ACTIVE"]=$ACTIVE;

		if($showGroupTabs && isset($_REQUEST["GROUP_ID_NUMBER"]))
		{
			$GROUP_ID_NUMBER = IntVal($GROUP_ID_NUMBER);
			$GROUP_ID = array();
			$ind = -1;
			for ($i = 0; $i <= $GROUP_ID_NUMBER; $i++)
			{
				if (${"GROUP_ID_ACT_".$i} == "Y")
				{
					$gr_id = IntVal(${"GROUP_ID_".$i});

					if($gr_id == 1 && !$USER->IsAdmin())
						continue;

					if ($USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users') && !in_array($gr_id, $arUserSubordinateGroups))
						continue;

					$ind++;
					$GROUP_ID[$ind]["GROUP_ID"] = $gr_id;
					$GROUP_ID[$ind]["DATE_ACTIVE_FROM"] = ${"GROUP_ID_FROM_".$i};
					$GROUP_ID[$ind]["DATE_ACTIVE_TO"] = ${"GROUP_ID_TO_".$i};
				}
			}

			if ($ID == "1" && $COPY_ID<=0)
			{
				$ind++;
				$GROUP_ID[$ind]["GROUP_ID"] = 1;
				$GROUP_ID[$ind]["DATE_ACTIVE_FROM"] = false;
				$GROUP_ID[$ind]["DATE_ACTIVE_TO"] = false;
			}

			$arFields["GROUP_ID"]=$GROUP_ID;
		}

		if (($editable && $ID!=$USER->GetID()) || $USER->IsAdmin())
			$arFields["ADMIN_NOTES"]=$ADMIN_NOTES;
		
		if ($USER->CanDoOperation('edit_ratings') && ($selfEdit || $ID!=$USER->GetID()) && is_array($_POST['RATING_BONUS'])) 
		{
			foreach ($_POST['RATING_BONUS'] as $ratingId => $ratingBonus)
			{				
				$arParam = array(
					'RATING_ID' => $ratingId,
					'ENTITY_ID' => $ID,
					'BONUS' => $ratingBonus,
				);
				CRatings::UpdateRatingUserBonus($arParam);
			}
		}
	}

	if(strlen($NEW_PASSWORD)>0)
	{
		$arFields["PASSWORD"]=$NEW_PASSWORD;
		$arFields["CONFIRM_PASSWORD"]=$NEW_PASSWORD_CONFIRM;
	}


	$USER_FIELD_MANAGER->EditFormAddFields($PROPERTY_ID, $arFields);
	if($ID>0 && $COPY_ID<=0)
	{
		$res = $user->Update($ID, $arFields, true);
	}
	elseif($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users'))
	{
		$ID = $user->Add($arFields);
		$res = ($ID>0);
		$new="Y";
	}

	$strError .= $user->LAST_ERROR;
	if ($GLOBALS['APPLICATION']->GetException())
	{
		$err = $GLOBALS['APPLICATION']->GetException();
		$strError .= $err->GetString();
		$GLOBALS['APPLICATION']->ResetException();
	}

	if(strlen($strError)<=0 && $ID>0)
	{
		if(is_array($profile_module_id) && count($profile_module_id)>0)
		{
			$db_opt_res = CModule::GetList();
			while ($opt_res = $db_opt_res->Fetch())
			{
				if (in_array($opt_res["ID"],$profile_module_id))
				{
					$mdir = $opt_res["ID"];
					if (file_exists($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir) && is_dir($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir))
					{
						$ofile = $DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir."/options_user_settings_set.php";
						if (file_exists($ofile))
						{
							$MODULE_RIGHT = $APPLICATION->GetGroupRight($mdir);
							if ($MODULE_RIGHT>="R")
							{
								include($ofile);
								$res = $res && ${$mdir."_res"};
								if (!${$mdir."_res"}) $strError .= ${$mdir."WarningTmp"};
							}
						}
					}
				}
			}
		}


		if((strlen($strError) <= 0) && $res)
		{
			if($user_info_event=="Y")
			{
				$arMess = false;
				$res_site = CSite::GetByID($LID);
				if($res_site_arr = $res_site->Fetch())
					$arMess = IncludeModuleLangFile(__FILE__, $res_site_arr["LANGUAGE_ID"], true);

				if($new=="Y")
					$user->SendUserInfo($ID, $LID, ($arMess !== false? $arMess["ACCOUNT_INSERT"]:GetMessage("ACCOUNT_INSERT")), true);
				else
					$user->SendUserInfo($ID, $LID, ($arMess !== false? $arMess["ACCOUNT_UPDATE"]:GetMessage("ACCOUNT_UPDATE")), true);
			}

			if($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users') || ($USER->CanDoOperation('edit_own_profile') && $ID==$uid))
			{
				if(strlen($save)>0)
					LocalRedirect($strRedirect_admin);
				elseif(strlen($apply)>0)
					LocalRedirect($strRedirect."&ID=".$ID."&".$tabControl->ActiveTabParam());
			}
			elseif($new=="Y")
				LocalRedirect($strRedirect."&ID=".$ID."&".$tabControl->ActiveTabParam());
		}
	}
}

$str_GROUP_ID = array();

$user = CUser::GetByID($ID);
if(!$user->ExtractFields("str_"))
{
	$ID=0;
	$str_ACTIVE="Y";
}
else
{
	$dbUserGroup = CUser::GetUserGroupList($ID);
	while ($arUserGroup = $dbUserGroup->Fetch())
	{
		$str_GROUP_ID[IntVal($arUserGroup["GROUP_ID"])]["DATE_ACTIVE_FROM"] = $arUserGroup["DATE_ACTIVE_FROM"];
		$str_GROUP_ID[IntVal($arUserGroup["GROUP_ID"])]["DATE_ACTIVE_TO"] = $arUserGroup["DATE_ACTIVE_TO"];
	}
}

if($COPY_ID > 0)
{
	$str_PERSONAL_PHOTO = "";
	$str_WORK_LOGO = "";
}

if((strlen($strError) > 0) || !$res)
{
	$save_PERSONAL_PHOTO = $str_PERSONAL_PHOTO;
	$save_WORK_LOGO = $str_WORK_LOGO;

	$DB->InitTableVarsForEdit("b_user", "", "str_");

	$str_PERSONAL_PHOTO = $save_PERSONAL_PHOTO;
	$str_WORK_LOGO = $save_WORK_LOGO;

	$GROUP_ID_NUMBER = IntVal($GROUP_ID_NUMBER);
	$str_GROUP_ID = array();
	for ($i = 0; $i <= $GROUP_ID_NUMBER; $i++)
	{
		if (${"GROUP_ID_ACT_".$i} == "Y")
		{
			$str_GROUP_ID[IntVal(${"GROUP_ID_".$i})]["DATE_ACTIVE_FROM"] = ${"GROUP_ID_FROM_".$i};
			$str_GROUP_ID[IntVal(${"GROUP_ID_".$i})]["DATE_ACTIVE_TO"] = ${"GROUP_ID_TO_".$i};
		}
	}
}


if($ID>0 && $COPY_ID<=0)
	$APPLICATION->SetTitle(GetMessage("EDIT_USER_TITLE", array("#ID#"=>$ID)));
else
	$APPLICATION->SetTitle(GetMessage("NEW_USER_TITLE"));

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array();
if($USER->CanDoOperation('view_all_users'))
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("RECORD_LIST"),
		"LINK"	=> "/bitrix/admin/user_admin.php?lang=".LANGUAGE_ID."&set_default=Y",
		"ICON"	=> "btn_list",
		"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
	);
}

if($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users'))
{
	if ($ID>0 && $COPY_ID<=0)
	{
		$aMenu[] = array("SEPARATOR"=>"Y");
		$aMenu[] = array(
			"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
			"LINK"	=> "/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID,
			"ICON"	=> "btn_new",
			"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
		);
		$aMenu[] = array(
			"TEXT"	=> GetMessage("MAIN_COPY_RECORD"),
			"LINK"	=> "/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID.htmlspecialchars("&COPY_ID=").$ID,
			"ICON"	=> "btn_copy",
			"TITLE"	=> GetMessage("MAIN_COPY_RECORD_TITLE"),
		);

		if ($ID!=1)
		{
			$aMenu[] = array(
				"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
				"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/user_admin.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
				"ICON"	=> "btn_delete",
				"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
			);
		}
	}
}

if(!empty($aMenu))
	$aMenu[] = array("SEPARATOR"=>"Y");
$link = DeleteParam(array("mode"));
$link = $GLOBALS["APPLICATION"]->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");
$aMenu[] = array(
	"TEXT"=>GetMessage("user_edit_form_settings"),
	"TITLE"=>GetMessage("user_edit_form_settings_title"),
	"LINK"=>"javascript:".$tabControl->GetName().".ShowSettings('".htmlspecialchars(CUtil::addslashes($link))."')",
	"ICON"=>"btn_settings",
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($e = $APPLICATION->GetException())
	$message = new CAdminMessage(GetMessage("MAIN_ERROR_SAVING"), $e);
if($message)
	echo $message->Show();
if(strlen($strError)>0)
{
	$e = new CAdminException(array(array('text' => $strError)));
	$message = new CAdminMessage(GetMessage("MAIN_ERROR_SAVING"), $e);
	echo $message->Show();
	//echo CAdminMessage::ShowMessage(Array("MESSAGE"=>$strError, "HTML"=>true, "TYPE"=>"ERROR"));
}

//We have to explicitly call calendar and editor functions because
//first output may be discarded by form settings
$tabControl->BeginPrologContent();
if(method_exists($USER_FIELD_MANAGER, 'showscript'))
	echo $USER_FIELD_MANAGER->ShowScript();
echo CAdminCalendar::ShowScript();
$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
?>
<?=bitrix_sessid_post()?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="COPY_ID" value=<?echo $COPY_ID?>>
<?
$tabControl->EndEpilogContent();

if($ID <= 0)
{
	$users_cnt = CUser::GetActiveUsersCount();
	$limitUsersCount = IntVal(COption::GetOptionInt("main", "PARAM_MAX_USERS", 0));
}

$tabControl->Begin(array(
	"FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".IntVal($ID)."&lang=".LANG,
	"FORM_ATTRIBUTES" => ($ID <= 0 && $limitUsersCount > 0 && $limitUsersCount <= $users_cnt? 'onsubmit="alert(\''.GetMessage("USER_EDIT_WARNING_MAX").'\')"':''),
));

$tabControl->BeginNextFormTab();

$tabControl->AddViewField("LAST_UPDATE", GetMessage('LAST_UPDATE'), ($ID>0 && $COPY_ID<=0? $str_TIMESTAMP_X:''));
$tabControl->AddViewField("LAST_LOGIN", GetMessage('LAST_LOGIN'), ($ID>0 && $COPY_ID<=0? $str_LAST_LOGIN:''));

if(($ID!='1' || $COPY_ID>0) && ($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('view_own_profile'))):
	$tabControl->BeginCustomField("ACTIVE", GetMessage('ACTIVE'));
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
		<?if($canSelfEdit):?>
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y") echo " checked"?>>
		<?else:?>
			<input type="checkbox" <?if($str_ACTIVE=="Y") echo " checked"?> disabled>
			<input type="hidden" name="ACTIVE" value="<?=$str_ACTIVE;?>">
		<?endif;?>
	</tr>
<?
	$tabControl->EndCustomField("ACTIVE", '<input type="hidden" name="ACTIVE" value="'.$str_ACTIVE.'">');
endif;

$tabControl->AddEditField("NAME", GetMessage('NAME'), false, array("size"=>30, "maxlength"=>50), $str_NAME);
$tabControl->AddEditField("LAST_NAME", GetMessage('LAST_NAME'), false, array("size"=>30, "maxlength"=>50), $str_LAST_NAME);
$tabControl->AddEditField("SECOND_NAME", GetMessage('SECOND_NAME'), false, array("size"=>30, "maxlength"=>50), $str_SECOND_NAME);
$tabControl->AddEditField("EMAIL", GetMessage('EMAIL'), true, array("size"=>30, "maxlength"=>50), $str_EMAIL);
$tabControl->AddEditField("LOGIN", GetMessage('LOGIN'), true, array("size"=>30, "maxlength"=>50), $str_LOGIN);

$tabControl->BeginCustomField("PASSWORD", GetMessage('NEW_PASSWORD_REQ'), true);
?>
	<tr>
		<td><?if($ID<=0 || $COPY_ID>0):?><span class="required">*</span><?endif?><?echo GetMessage('NEW_PASSWORD_REQ')?><sup><span class="required">1</span></sup>:</td>
		<td><input type="password" name="NEW_PASSWORD" size="30" maxlength="50" value="<? echo htmlspecialchars($NEW_PASSWORD) ?>" autocomplete="off"></td>
	</tr>
	<tr>
		<td><?if($ID<=0 || $COPY_ID>0):?><span class="required">*</span><?endif?><?echo GetMessage('NEW_PASSWORD_CONFIRM')?></td>
		<td><input type="password" name="NEW_PASSWORD_CONFIRM" size="30" maxlength="50" value="<? echo htmlspecialchars($NEW_PASSWORD_CONFIRM) ?>" autocomplete="off"></td>
	</tr>
<?
$tabControl->EndCustomField("PASSWORD");
?>
<?if($USER->CanDoOperation('view_all_users')):?>
<?
	$rExtAuth = CUser::GetExternalAuthList();
	if($arExtAuth = $rExtAuth->GetNext()):

		$tabControl->BeginCustomField("EXTERNAL_AUTH_ID", GetMessage('MAIN_USERED_AUTH_TYPE'));

		$bNewAuthID = (strlen($str_EXTERNAL_AUTH_ID)>0);
?>
		<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
			<select name="EXTERNAL_AUTH_ID"<?if(!$canSelfEdit) echo " disabled"?>>
				<option value=""><?echo GetMessage("MAIN_USERED_AUTH_INT")?></option>
				<?do{?>
				<option value="<?=$arExtAuth['ID']?>"<?
				if($str_EXTERNAL_AUTH_ID==$arExtAuth['ID'])
				{
					echo ' selected';
					$bNewAuthID = false;
				}
				?>><?=$arExtAuth['NAME']?></option>
				<?}while($arExtAuth = $rExtAuth->GetNext());?>
				<?if($bNewAuthID):?>
					<option value="<?=$str_EXTERNAL_AUTH_ID?>" selected><?=$str_EXTERNAL_AUTH_ID?></option>
				<?endif;?>
			</select>
		</td>
		</tr>
<?
		$tabControl->EndCustomField("EXTERNAL_AUTH_ID", '<input type="hidden" name="EXTERNAL_AUTH_ID" value="'.$str_EXTERNAL_AUTH_ID.'">');

	endif;
endif;
?>
<?
if($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')):
	$tabControl->BeginCustomField("LID", GetMessage("MAIN_DEFAULT_SITE"));
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<?if(!$canSelfEdit) $dis = " disabled"?>
		<td><?=CSite::SelectBox("LID", $str_LID, "", "",$dis);?></td>
	</tr>
<?
	$tabControl->EndCustomField("LID", '<input type="hidden" name="LID" value="'.$str_LID.'">');

	$tabControl->AddCheckBoxField("user_info_event", GetMessage('INFO_FOR_USER'), false, "Y", ($user_info_event=="Y"), (!$canSelfEdit? array("disabled"):array()));
endif;
?>
<?
if($showGroupTabs):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("GROUP_ID", GetMessage("user_edit_form_groups"));
?>
	<tr valign="top">
		<td colspan="2" align="center"><table border="0" cellpadding="0" cellspacing="0" align="center">
			<tr>
				<td width="0%" nowrap colspan="2" align="center">&nbsp;</td>
				<td width="0%" nowrap colspan="2" style="padding-left:10px"><?=GetMessage('TBL_GROUP_DATE')?> (<?=FORMAT_DATETIME?>)</td>
				<td nowrap align="center">&nbsp;</td>
				<td nowrap align="center">&nbsp;</td>
			</tr>
			<script language="JavaScript" type="text/javascript">
			function CatGroupsActivate(obj, id)
			{
				var ed = eval("document.<?=$tabControl->GetFormName()?>.GROUP_ID_FROM_" + id);
				var ed1 = eval("document.<?=$tabControl->GetFormName()?>.GROUP_ID_TO_" + id);
				ed.disabled = !obj.checked;
				ed1.disabled = !obj.checked;
			}
			</script>
			<?
			$ind = -1;
			$dbGroups = CGroup::GetList(($b = "c_sort"), ($o = "asc"), Array("ANONYMOUS" => "N"));
			while ($arGroups = $dbGroups->Fetch())
			{
				if (!$USER->CanDoOperation('edit_all_users') && $USER->CanDoOperation('edit_subordinate_users') && !in_array($arGroups["ID"], $arUserSubordinateGroups) || $arGroups["ID"] == 2)
					continue;
				if($arGroups["ID"]==1 && !$USER->IsAdmin())
					continue;
				$ind++;
				?>
				<tr>
					<td width="0%" nowrap><input type="hidden" name="GROUP_ID_<?=$ind?>" value="<?=$arGroups["ID"]?>"><input type="checkbox" name="GROUP_ID_ACT_<?=$ind?>" id="GROUP_ID_ACT_ID_<?=$ind?>" value="Y"<?
						if (array_key_exists($arGroups["ID"], $str_GROUP_ID))
							echo " checked";
						?> OnChange="CatGroupsActivate(this, <?=$ind?>)"></td>
					<td width="0%" nowrap><label for="GROUP_ID_ACT_ID_<?= $ind ?>"><?=htmlspecialchars($arGroups["NAME"])?> [<a href="/bitrix/admin/group_edit.php?ID=<?=$arGroups["ID"]?>&lang=<?=LANGUAGE_ID?>" title="<?=GetMessage("MAIN_VIEW_GROUP")?>"><?echo intval($arGroups["ID"])?></a>]</label></td>
					<td width="0%" nowrap align="center" style="padding-right:10px; padding-left:10px">
						<?= GetMessage('USER_GROUP_DATE_FROM')?>
						<?= CalendarDate("GROUP_ID_FROM_".$ind, (array_key_exists($arGroups["ID"], $str_GROUP_ID) ? htmlspecialchars($str_GROUP_ID[$arGroups["ID"]]["DATE_ACTIVE_FROM"]) : ""), $tabControl->GetFormName(), "10", ((array_key_exists($arGroups["ID"], $str_GROUP_ID)) ? " " : " disabled"))?>
					</td>
					<td width="0%" nowrap align="center">
						<?= GetMessage('USER_GROUP_DATE_TO')?>
						<?= CalendarDate("GROUP_ID_TO_".$ind, (array_key_exists($arGroups["ID"], $str_GROUP_ID) ? htmlspecialchars($str_GROUP_ID[$arGroups["ID"]]["DATE_ACTIVE_TO"]) : ""), $tabControl->GetFormName(), "10", ((array_key_exists($arGroups["ID"], $str_GROUP_ID)) ? " " : " disabled"))?>
					</td>
				</tr>
				<?
			}
			?>
		</table><input type="hidden" name="GROUP_ID_NUMBER" value="<?= $ind ?>"></td>
	</tr>
<?
	$tabControl->EndCustomField("GROUP_ID");
endif;
?>
<?
$tabControl->BeginNextFormTab();

$tabControl->AddEditField("PERSONAL_PROFESSION", GetMessage('USER_PROFESSION'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_PROFESSION);
$tabControl->AddEditField("PERSONAL_WWW", GetMessage('USER_WWW'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_WWW);
$tabControl->AddEditField("PERSONAL_ICQ", GetMessage('USER_ICQ'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_ICQ);
$tabControl->AddDropDownField("PERSONAL_GENDER", GetMessage('USER_GENDER'), false, array(""=>GetMessage("USER_DONT_KNOW"), "M"=>GetMessage("USER_MALE"), "F"=>GetMessage("USER_FEMALE")), $str_PERSONAL_GENDER);
$tabControl->AddCalendarField("PERSONAL_BIRTHDAY", GetMessage("USER_BIRTHDAY_DT")." (".CLang::GetDateFormat("SHORT")."):", $str_PERSONAL_BIRTHDAY);
$tabControl->AddFileField("PERSONAL_PHOTO", GetMessage("USER_PHOTO"), $str_PERSONAL_PHOTO, array("iMaxW"=>150, "iMaxH"=>150));

$tabControl->AddSection("USER_PHONES", GetMessage("USER_PHONES"));
$tabControl->AddEditField("PERSONAL_PHONE", GetMessage('USER_PHONE'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_PHONE);
$tabControl->AddEditField("PERSONAL_FAX", GetMessage('USER_FAX'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_FAX);
$tabControl->AddEditField("PERSONAL_MOBILE", GetMessage('USER_MOBILE'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_MOBILE);
$tabControl->AddEditField("PERSONAL_PAGER", GetMessage('USER_PAGER'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_PAGER);

$tabControl->AddSection("USER_POST_ADDRESS", GetMessage("USER_POST_ADDRESS"));
$tabControl->BeginCustomField("PERSONAL_COUNTRY", GetMessage('USER_COUNTRY'));
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td><?echo SelectBoxFromArray("PERSONAL_COUNTRY", GetCountryArray(), $str_PERSONAL_COUNTRY, GetMessage("USER_DONT_KNOW"));?></td>
	</tr>
<?
$tabControl->EndCustomField("PERSONAL_COUNTRY", '<input type="hidden" name="PERSONAL_COUNTRY" value="'.$str_PERSONAL_COUNTRY.'">');
$tabControl->AddEditField("PERSONAL_STATE", GetMessage('USER_STATE'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_STATE);
$tabControl->AddEditField("PERSONAL_CITY", GetMessage('USER_CITY'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_CITY);
$tabControl->AddEditField("PERSONAL_ZIP", GetMessage('USER_ZIP'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_ZIP);
$tabControl->AddTextField("PERSONAL_STREET", GetMessage("USER_STREET"), $str_PERSONAL_STREET, array("cols"=>40, "rows"=>3));
$tabControl->AddEditField("PERSONAL_MAILBOX", GetMessage('USER_MAILBOX'), false, array("size"=>30, "maxlength"=>255), $str_PERSONAL_MAILBOX);
$tabControl->AddTextField("PERSONAL_NOTES", GetMessage("USER_NOTES"), $str_PERSONAL_NOTES, array("cols"=>40, "rows"=>5));

$tabControl->BeginNextFormTab();

$tabControl->AddEditField("WORK_COMPANY", GetMessage('USER_COMPANY'), false, array("size"=>30, "maxlength"=>255), $str_WORK_COMPANY);
$tabControl->AddEditField("WORK_WWW", GetMessage('USER_WWW'), false, array("size"=>30, "maxlength"=>255), $str_WORK_WWW);
$tabControl->AddEditField("WORK_DEPARTMENT", GetMessage('USER_DEPARTMENT'), false, array("size"=>30, "maxlength"=>255), $str_WORK_DEPARTMENT);
$tabControl->AddEditField("WORK_POSITION", GetMessage('USER_POSITION'), false, array("size"=>30, "maxlength"=>255), $str_WORK_POSITION);
$tabControl->AddTextField("WORK_PROFILE", GetMessage("USER_WORK_PROFILE"), $str_WORK_PROFILE, array("cols"=>40, "rows"=>5));
$tabControl->AddFileField("WORK_LOGO", GetMessage("USER_LOGO"), $str_WORK_LOGO, array("iMaxW"=>150, "iMaxH"=>150));

$tabControl->AddSection("USER_WORK_PHONES", GetMessage("USER_PHONES"));
$tabControl->AddEditField("WORK_PHONE", GetMessage('USER_PHONE'), false, array("size"=>30, "maxlength"=>255), $str_WORK_PHONE);
$tabControl->AddEditField("WORK_FAX", GetMessage('USER_FAX'), false, array("size"=>30, "maxlength"=>255), $str_WORK_FAX);
$tabControl->AddEditField("WORK_PAGER", GetMessage('USER_PAGER'), false, array("size"=>30, "maxlength"=>255), $str_WORK_PAGER);

$tabControl->AddSection("USER_WORK_POST_ADDRESS", GetMessage("USER_POST_ADDRESS"));
$tabControl->BeginCustomField("WORK_COUNTRY", GetMessage('USER_COUNTRY'));
?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td><?echo SelectBoxFromArray("WORK_COUNTRY", GetCountryArray(), $str_WORK_COUNTRY, GetMessage("USER_DONT_KNOW"));?></td>
	</tr>
<?
$tabControl->EndCustomField("WORK_COUNTRY", '<input type="hidden" name="WORK_COUNTRY" value="'.$str_WORK_COUNTRY.'">');
$tabControl->AddEditField("WORK_STATE", GetMessage('USER_STATE'), false, array("size"=>30, "maxlength"=>255), $str_WORK_STATE);
$tabControl->AddEditField("WORK_CITY", GetMessage('USER_CITY'), false, array("size"=>30, "maxlength"=>255), $str_WORK_CITY);
$tabControl->AddEditField("WORK_ZIP", GetMessage('USER_ZIP'), false, array("size"=>30, "maxlength"=>255), $str_WORK_ZIP);
$tabControl->AddTextField("WORK_STREET", GetMessage("USER_STREET"), $str_WORK_STREET, array("cols"=>40, "rows"=>3));
$tabControl->AddEditField("WORK_MAILBOX", GetMessage('USER_MAILBOX'), false, array("size"=>30, "maxlength"=>255), $str_WORK_MAILBOX);
$tabControl->AddTextField("WORK_NOTES", GetMessage("USER_NOTES"), $str_WORK_NOTES, array("cols"=>40, "rows"=>5));

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("RATING_BOX", '', false);
?>
	<tr>
		<td width="100%" colspan="100%">
		<?
		$i = 1;
		$aTabs2 = Array();
		$arRatings = array();
		$rsRatings = CRatings::GetList(array('ID' => 'ASC'), array('ACTIVE' => 'Y', 'ENTITY_ID' => 'USER'));
		while ($arRatingsTmp = $rsRatings->GetNext())
		{
			if ($arRatingsTmp['AUTHORITY'] == 'Y')
				$arRatingsTmp['NAME'] = '<span class="required">[A]</span> '.$arRatingsTmp['NAME'];
				
			$aTabs2[] = Array("DIV"=>"rating_".$i, "TAB" => $arRatingsTmp['NAME'], "TITLE" => GetMessage('RATING_TAB_INFO'));
			$arRatings[$arRatingsTmp['ID']] = $arRatingsTmp;
			$i++;
		}

		$ratingWeightType 	 = COption::GetOptionString("main", "rating_weight_type", "auto");	
		$authorityRatingId	 = CRatings::GetAuthorityRating();
		$arAuthorityUserProp = CRatings::GetRatingUserProp($authorityRatingId, $ID);
			
		$viewTabControl = new CAdminViewTabControl("tabControlRating", $aTabs2);
		$viewTabControl->Begin();

		foreach($arRatings as $ratingId => $arRating)
		{
			$arRatingResult = CRatings::GetRatingResult($ratingId, $ID);
			$arRatingUserProp = CRatings::GetRatingUserProp($ratingId, $ID);
			$viewTabControl->BeginNextTab();
			?>
				<table cellspacing="7" cellpadding="0" border="0" width="100%" class="edit-table">
			<?	if ($USER->CanDoOperation('edit_ratings') && ($selfEdit || $ID!=$uid)): ?>
				<tr>
					<td class="field-name" width="40%"><?=GetMessage('RATING_BONUS')?><sup><span class="required">2</span></sup>:</td>
					<td><?=InputType('text', "RATING_BONUS[$ratingId]", floatval($arRatingUserProp['BONUS']), false, false, '', 'size="5" maxlength="11"')?> <?=($ratingWeightType == 'auto'? 'x '.GetMessage('RATING_NORM_VOTE_WEIGHT'): '')?></td>
				</tr>
			<? endif; ?>
				<tr>
					<td class="field-name" width="40%"><?=GetMessage('RATING_POSITION')?>:</td>
					<td>
					<?$APPLICATION->IncludeComponent(
						"bitrix:rating.result", "",
						Array(
							"RESULT_TYPE" 			=> 'POSITION',
							"SHOW_RATING_NAME"		=> 'N',
							"RATING_ID" 			=> $arRatingResult['RATING_ID'],
							"ENTITY_ID" 			=> $arRatingResult['ENTITY_ID'],
							"CURRENT_POSITION" 		=> $arRatingResult['CURRENT_POSITION'],
							"PREVIOUS_POSITION" 	=> $arRatingResult['PREVIOUS_POSITION'],
						),
						null,
						array("HIDE_ICONS" => "Y")
					);?>
					</td>
				</tr>
				<tr>
					<td class="field-name" width="40%"><?=GetMessage('RATING_CURRENT_VALUE')?>:</td>
					<td><?=floatval($arRatingResult['CURRENT_VALUE']);?></td>
				</tr>
				<tr>
					<td class="field-name" width="40%"><?=GetMessage('RATING_PREVIOUS_VALUE')?>:</td>
					<td><?=floatval($arRatingResult['PREVIOUS_VALUE']);?></td>
				</tr>
				<?
					if ($arRating['AUTHORITY'] == 'Y')
					{
						if ($ratingWeightType == 'auto')
						{
							$voteWeight		 = COption::GetOptionString("main", "rating_vote_weight", 1);
						    $voteWeightUser	 = round(floatval($arAuthorityUserProp['VOTE_WEIGHT']/$voteWeight), 4);
							$communitySize	 = COption::GetOptionString("main", "rating_community_size", 1);	
							$communityAuthority = COption::GetOptionString("main", "rating_community_authority", 1);
							$normVoteCount	 = floor(floatval($arRatingResult['CURRENT_VALUE'])/$voteWeight);
							$sRatingAuthorityWeight = COption::GetOptionString("main", "rating_authority_weight_formula", 'Y');			
							if ($sRatingAuthorityWeight == 'Y')
								$voteWeightAuthority = $communityAuthority > 0? round($communitySize*$voteWeightUser/$communityAuthority,4): 0;
							else 
								$voteWeightAuthority = 1;
							?>
							<tr>
								<td class="field-name" width="40%"><?=GetMessage('RATING_VOTE_NORM_VOTE')?>:</td>
								<td><?=$normVoteCount?></td>
							</tr>
							<?
						}
						else
						{
							$voteWeightAuthority = round(floatval($arAuthorityUserProp['VOTE_WEIGHT']), 4);
						}
						?>
						<tr>
							<td class="field-name" width="40%"><?=GetMessage('RATING_VOTE_WEIGHT')?>:</td>
							<td><?=round(floatval($arAuthorityUserProp['VOTE_WEIGHT']), 4)?></td>
						</tr>
						<tr>
							<td class="field-name" width="40%"><?=GetMessage('RATING_VOTE_WEIGHT_AUTHORITY')?>:</td>
							<td><?=$voteWeightAuthority?></td>
						</tr>
						<tr>
							<td class="field-name" width="40%"><?=GetMessage('RATING_VOTE_AUTHORITY_COUNT')?>:</td>
							<td><?=floatval($arRatingUserProp['VOTE_COUNT']);?></td>
						</tr>
						<?
					}					
					?>
				</table>
			<?
		}
		$viewTabControl->End();
		?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("RATING_BOX");

$db_opt_res = CModule::GetList();
while ($opt_res = $db_opt_res->Fetch())
{
	$mdir = $opt_res["ID"];
	if (file_exists($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir) && is_dir($DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir))
	{
		$ofile = $DOCUMENT_ROOT.BX_ROOT."/modules/".$mdir."/options_user_settings.php";
		if (file_exists($ofile))
		{
			$tabControl->BeginNextFormTab();
			$tabControl->BeginCustomField("MODULE_TAB_".$mdir, GetMessage($mdir."_TAB"));
			include($ofile);
			$tabControl->EndCustomField("MODULE_TAB_".$mdir);
		}
	}
}

if (($editable && $ID!=$USER->GetID()) || $USER->IsAdmin()):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("ADMIN_NOTES", GetMessage("USER_ADMIN_NOTES"));
?>
	<tr valign="top">
		<td align="center" colspan="2"><textarea name="ADMIN_NOTES" cols="50" rows="10" style="width:100%;"><?echo $str_ADMIN_NOTES?></textarea></td>
	</tr>
<?
	$tabControl->EndCustomField("ADMIN_NOTES", '<input type="hidden" name="ADMIN_NOTES" value="'.$str_ADMIN_NOTES.'">');
endif;

//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields($PROPERTY_ID)) > 0) ||
	($USER_FIELD_MANAGER->GetRights($PROPERTY_ID) >= "W")
)
{
	$tabControl->BeginNextFormTab();
	$tabControl->ShowUserFields($PROPERTY_ID, $ID, (strlen($strError) > 0) || !$res);
}

$tabControl->Buttons(array("disabled" => (!$editable), "back_url"=>"user_admin.php?lang=".LANGUAGE_ID));

$tabControl->Show();

$tabControl->ShowWarnings($tabControl->GetName(), $message);
?>

<?if(!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):?>
<?echo BeginNote();?>
<span class="required">1</span> <?$GROUP_POLICY = CUser::GetGroupPolicy($ID);echo $GROUP_POLICY["PASSWORD_REQUIREMENTS"];?><br>
<span class="required">2</span> <?echo GetMessage("RATING_BONUS_NOTICE")?><br>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>
<?endif;?>
<?
require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/epilog_admin.php");
?>