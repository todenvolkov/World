<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "settings/userfield_edit.php");

IncludeModuleLangFile(__FILE__);

$RIGHTS = $USER_FIELD_MANAGER->GetRights(false, $ID);
if($RIGHTS<"W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

	$aTabs = array();
$aTabs[] = array(
	"DIV" => "edit1",
	"TAB" => GetMessage("USER_TYPE_TAB"),
	"ICON"=>"main_user_edit",
	"TITLE"=>GetMessage("USER_TYPE_TAB_TITLE"),
);

$obEnum = false;
if($ID>0)
{
	if($arUserField = CUserTypeEntity::GetByID($ID))
	{
		if($arType = $USER_FIELD_MANAGER->GetUserType($arUserField["USER_TYPE_ID"]))
		{
			if($arType["BASE_TYPE"]=="enum")
			{
				$obEnum = new CUserFieldEnum;
				$aTabs[] = array(
					"DIV" => "edit2",
					"TAB" => GetMessage("USER_TYPE_TAB2"),
					"ICON"=>"main_user_edit",
					"TITLE"=>GetMessage("USER_TYPE_TAB2_TITLE"),
				);
			}
		}
	}
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);// Id of the edited record
$message = null;
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && ($RIGHTS>="W") && check_bitrix_sessid())
{
	$arFields = Array(
		"ENTITY_ID" => $ENTITY_ID,
		"FIELD_NAME" => $FIELD_NAME,
		"USER_TYPE_ID" => $USER_TYPE_ID,
		"XML_ID" => $XML_ID,
		"SORT" => $SORT,
		"MULTIPLE" => $MULTIPLE,
		"MANDATORY" => $MANDATORY,
		"SHOW_FILTER" => $SHOW_FILTER,
		"SHOW_IN_LIST" => $SHOW_IN_LIST,
		"EDIT_IN_LIST" => $EDIT_IN_LIST,
		"IS_SEARCHABLE" => $IS_SEARCHABLE,
		"SETTINGS" => $SETTINGS,
		"EDIT_FORM_LABEL" => $EDIT_FORM_LABEL,
		"LIST_COLUMN_LABEL" => $LIST_COLUMN_LABEL,
		"LIST_FILTER_LABEL" => $LIST_FILTER_LABEL,
		"ERROR_MESSAGE" => $ERROR_MESSAGE,
		"HELP_MESSAGE" => $HELP_MESSAGE,
	);

	$obUserField  = new CUserTypeEntity;
	if($ID > 0)
		$res = $obUserField->Update($ID, $arFields);
	else
	{
		$ID = $obUserField->Add($arFields);
		$res = ($ID>0);
	}

	if(is_object($obEnum))
	{
		if(is_array($LIST))
		{
			foreach($LIST as $id => $value)
				if(is_array($value))
					$LIST[$id]["DEF"] = "N";
		}
		if(is_array($LIST["DEF"]))
		{
			foreach($LIST["DEF"] as $value)
				if(is_array($LIST[$value]))
					$LIST[$value]["DEF"] = "Y";
			unset($LIST["DEF"]);
		}
		$res = $obEnum->SetEnumValues($ID, $LIST);
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/userfield_edit.php?ID=".$ID."&lang=".LANG."&back_url=".urlencode($back_url)."&".$tabControl->ActiveTabParam());
		elseif($back_url)
			LocalRedirect($back_url);
		else
			LocalRedirect("/bitrix/admin/userfield_admin.php?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("USER_TYPE_SAVE_ERROR"), $e);
		$bVarsFromForm = true;
	}

}

if($ID>0)
{
	$arUserField = CUserTypeEntity::GetByID($ID);
	if(!$arUserField)
		$ID=0;
}
else
{
	$arUserField = array(
		"ENTITY_ID" => isset($_GET["ENTITY_ID"])? $_GET["ENTITY_ID"]: "",
		"FIELD_NAME" => isset($_GET["FIELD_NAME"])? $_GET["FIELD_NAME"]: "UF_",
		"USER_TYPE_ID" => isset($_GET["USER_TYPE_ID"])? $_GET["USER_TYPE_ID"]: "",
		"XML_ID" => "",
		"SORT" => 100,
		"MULTIPLE" => "N",
		"MANDATORY" => "N",
		"SHOW_FILTER" => "N",
		"SHOW_IN_LIST" => "Y",
		"EDIT_IN_LIST" => "Y",
		"IS_SEARCHABLE" => "N",
		"SETTINGS" => array(),
	);
}

if($bVarsFromForm)
{
	$ENTITY_ID = htmlspecialchars($_REQUEST["ENTITY_ID"]);
	$FIELD_NAME = htmlspecialchars($_REQUEST["FIELD_NAME"]);
	$USER_TYPE_ID = htmlspecialchars($_REQUEST["USER_TYPE_ID"]);
	$XML_ID = htmlspecialchars($_REQUEST["XML_ID"]);
	$SORT = htmlspecialchars($_REQUEST["SORT"]);
	$MULTIPLE = htmlspecialchars($_REQUEST["MULTIPLE"]);
	$MANDATORY = htmlspecialchars($_REQUEST["MANDATORY"]);
	$SHOW_FILTER = htmlspecialchars($_REQUEST["SHOW_FILTER"]);
	$SHOW_IN_LIST = htmlspecialchars($_REQUEST["SHOW_IN_LIST"]);
	$EDIT_IN_LIST = htmlspecialchars($_REQUEST["EDIT_IN_LIST"]);
	$IS_SEARCHABLE = htmlspecialchars($_REQUEST["IS_SEARCHABLE"]);
}
else
{
	$ENTITY_ID = htmlspecialchars($arUserField["ENTITY_ID"]);
	$FIELD_NAME = htmlspecialchars($arUserField["FIELD_NAME"]);
	$USER_TYPE_ID = htmlspecialchars($arUserField["USER_TYPE_ID"]);
	$XML_ID = htmlspecialchars($arUserField["XML_ID"]);
	$SORT = htmlspecialchars($arUserField["SORT"]);
	$MULTIPLE = htmlspecialchars($arUserField["MULTIPLE"]);
	$MANDATORY = htmlspecialchars($arUserField["MANDATORY"]);
	$SHOW_FILTER = htmlspecialchars($arUserField["SHOW_FILTER"]);
	$SHOW_IN_LIST = htmlspecialchars($arUserField["SHOW_IN_LIST"]);
	$EDIT_IN_LIST = htmlspecialchars($arUserField["EDIT_IN_LIST"]);
	$IS_SEARCHABLE = htmlspecialchars($arUserField["IS_SEARCHABLE"]);
}

$APPLICATION->SetTitle(($ID>0? GetMessage("USER_TYPE_EDIT_TITLE", array("#ID#"=>$ID)) : GetMessage("USER_TYPE_ADD_TITLE")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("USER_TYPE_LIST"),
		"TITLE"=>GetMessage("USER_TYPE_LIST_TITLE"),
		"LINK"=>"userfield_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"TITLE"=>GetMessage("USER_TYPE_ADD"),
		"LINK"=>"userfield_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("MAIN_DELETE"),
		"TITLE"=>GetMessage("USER_TYPE_DELETE"),
		"LINK"=>"javascript:if(confirm('".GetMessage("USER_TYPE_DELETE_CONF")."'))window.location='userfield_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($message)
	echo $message->Show();
?>
<script language="JavaScript">
<!--
function addNewRow(tableID)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);
	for(var i=0;i<6;i++)
	{
		var oCell = oRow.insertCell(i);
		var sHTML=tbl.rows[cnt-1].cells[i].innerHTML;
		var p = 0;
		while(true)
		{
			var s = sHTML.indexOf('[n',p);
			if(s<0)break;
			var e = sHTML.indexOf(']',s);
			if(e<0)break;
			var n = parseInt(sHTML.substr(s+2,e-s));
			sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
			p=s+1;
		}
		while(true)
		{
			var s = sHTML.indexOf('\"n',p);
			if(s<0)break;
			var e = sHTML.indexOf('\"',s+1);
			if(e<0)break;
			var n = parseInt(sHTML.substr(s+2,e-s));
			sHTML = sHTML.substr(0, s)+'\"n'+(++n)+'\"'+sHTML.substr(e+1);
			p=s+1;
		}
		oCell.innerHTML = sHTML;
	}
}
//-->
</script>
<form method="POST" Action="<?echo $APPLICATION->GetCurPage()."?lang=".urlencode(LANG)?>" ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<?if($ID):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
	<?endif?>
	<tr>
		<td width="40%"><span class="required">*</span><?=GetMessage("USERTYPE_USER_TYPE_ID")?>:</td>
		<td width="60%">
			<?
			if($ID > 0)
			{
				$arUserType = $USER_FIELD_MANAGER->GetUserType($USER_TYPE_ID);
				echo htmlspecialchars($arUserType["DESCRIPTION"]);
			}
			else
			{
				$arUserTypes = $USER_FIELD_MANAGER->GetUserType();
				$arr = array("reference"=>array(), "reference_id"=>array());
				foreach($arUserTypes as $arUserType)
				{
					$arr["reference"][] = $arUserType["DESCRIPTION"];
					$arr["reference_id"][] = $arUserType["USER_TYPE_ID"];
				}
				echo SelectBoxFromArray("USER_TYPE_ID", $arr, $USER_TYPE_ID, "", 'OnChange="window.location=\''.htmlspecialchars($APPLICATION->GetCurPageParam("", array("USER_TYPE_ID")).'&USER_TYPE_ID=').'\'+this.value"');
			}
			?>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("USERTYPE_ENTITY_ID")?>:</td>
		<td>
			<?if($ID>0 || ($ENTITY_ID!="" && !$message)):?>
				<?=$ENTITY_ID?>
				<input type="hidden" name="ENTITY_ID" value="<?=$ENTITY_ID?>">
			<?else:?>
				<input type="text" name="ENTITY_ID" value="<?=$ENTITY_ID?>" maxlength="20">
			<?endif?>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("USERTYPE_FIELD_NAME")?>:</td>
		<td>
			<?if($ID>0):?>
				<?=$FIELD_NAME?>
			<?else:?>
				<input type="text" name="FIELD_NAME" value="<?=$FIELD_NAME?>" maxlength="20">
			<?endif?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_XML_ID")?>:</td>
		<td><input type="text" name="XML_ID" value="<?=$XML_ID?>" maxlength="255"></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_SORT")?>:</td>
		<td><input type="text" name="SORT" value="<?=$SORT?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_MULTIPLE")?>:</td>
		<td>
			<?if($ID>0):?>
				<?=$MULTIPLE == "Y"? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?>
			<?else:?>
				<input type="checkbox" name="MULTIPLE" value="Y"<?if($MULTIPLE == "Y") echo " checked"?> >
			<?endif?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_MANDATORY")?>:</td>
		<td><input type="checkbox" name="MANDATORY" value="Y"<?if($MANDATORY == "Y") echo " checked"?> ></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_SHOW_FILTER")?>:</td>
		<td><?
			$arr = array(
				"reference" => array(
					GetMessage("USER_TYPE_FILTER_N"),
					GetMessage("USER_TYPE_FILTER_I"),
					GetMessage("USER_TYPE_FILTER_E"),
					GetMessage("USER_TYPE_FILTER_S"),
				),
				"reference_id" => array(
					"N",
					"I",
					"E",
					"S",
				),
			);
			echo SelectBoxFromArray("SHOW_FILTER", $arr, $SHOW_FILTER);
		?></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_SHOW_IN_LIST")?>:</td>
		<td><input type="checkbox" name="SHOW_IN_LIST" value="N"<?if($SHOW_IN_LIST == "N") echo " checked"?> ></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_EDIT_IN_LIST")?>:</td>
		<td><input type="checkbox" name="EDIT_IN_LIST" value="N"<?if($EDIT_IN_LIST == "N") echo " checked"?> ></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_IS_SEARCHABLE")?>:</td>
		<td><input type="checkbox" name="IS_SEARCHABLE" value="Y"<?if($IS_SEARCHABLE == "Y") echo " checked"?> ></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("USERTYPE_SETTINGS")?></td>
	</tr>
	<?if($ID > 0):
		echo $USER_FIELD_MANAGER->GetSettingsHTML($arUserField, $bVarsFromForm);
	else:
		$arUserType = $USER_FIELD_MANAGER->GetUserType($USER_TYPE_ID);
		if(!$arUserType)
			$arUserType = array_shift($arUserTypes);
		echo $USER_FIELD_MANAGER->GetSettingsHTML($arUserType["USER_TYPE_ID"], $bVarsFromForm);
	endif;?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("USERTYPE_LANG_SETTINGS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="6">
				<tr>
					<td align="center"><?echo GetMessage("USER_TYPE_LANG");?></td>
					<td align="center"><?echo GetMessage("USER_TYPE_EDIT_FORM_LABEL");?></td>
					<td align="center"><?echo GetMessage("USER_TYPE_LIST_COLUMN_LABEL");?></td>
					<td align="center"><?echo GetMessage("USER_TYPE_LIST_FILTER_LABEL");?></td>
					<td align="center"><?echo GetMessage("USER_TYPE_ERROR_MESSAGE");?></td>
					<td align="center"><?echo GetMessage("USER_TYPE_HELP_MESSAGE");?></td>
				</tr>
				<?
				$rsLanguage = CLanguage::GetList($by, $order, array());
				while($arLanguage = $rsLanguage->Fetch()):
				?>
				<tr>
					<td><?echo $arLanguage["NAME"]?>:</font></td>
					<td><input type="text" name="EDIT_FORM_LABEL[<?echo $arLanguage["LID"]?>]" size="20" maxlength="255" value="<?echo htmlspecialchars($bVarsFromForm? $EDIT_FORM_LABEL[$arLanguage["LID"]]: $arUserField["EDIT_FORM_LABEL"][$arLanguage["LID"]])?>"></td>
					<td><input type="text" name="LIST_COLUMN_LABEL[<?echo $arLanguage["LID"]?>]" size="20" maxlength="255" value="<?echo htmlspecialchars($bVarsFromForm? $LIST_COLUMN_LABEL[$arLanguage["LID"]]: $arUserField["LIST_COLUMN_LABEL"][$arLanguage["LID"]])?>"></td>
					<td><input type="text" name="LIST_FILTER_LABEL[<?echo $arLanguage["LID"]?>]" size="20" maxlength="255" value="<?echo htmlspecialchars($bVarsFromForm? $LIST_FILTER_LABEL[$arLanguage["LID"]]: $arUserField["LIST_FILTER_LABEL"][$arLanguage["LID"]])?>"></td>
					<td><input type="text" name="ERROR_MESSAGE[<?echo $arLanguage["LID"]?>]" size="20" maxlength="255" value="<?echo htmlspecialchars($bVarsFromForm? $ERROR_MESSAGE[$arLanguage["LID"]]: $arUserField["ERROR_MESSAGE"][$arLanguage["LID"]])?>"></td>
					<td><input type="text" name="HELP_MESSAGE[<?echo $arLanguage["LID"]?>]" size="20" maxlength="255" value="<?echo htmlspecialchars($bVarsFromForm? $HELP_MESSAGE[$arLanguage["LID"]]: $arUserField["HELP_MESSAGE"][$arLanguage["LID"]])?>"></td>
				</tr>
				<?endwhile?>
			</table>
		</td>
	</tr>
<?if(is_object($obEnum)):
	$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td><?=GetMessage("USER_TYPE_LIST_LABEL")?></td>
		<td>
	<table border="0" cellspacing="0" cellpadding="0" class="internal" id="list_table">
	<tr class="heading">
		<td><?=GetMessage("USER_TYPE_LIST_ID")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_XML_ID")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_VALUE")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_SORT")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_DEF")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_DEL")?></td>
	</tr>
<?if($MULTIPLE=="N"):?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><?=GetMessage("USER_TYPE_LIST_NO_DEF")?></td>
		<td>&nbsp;</td>
		<td><input type="radio" name="LIST[DEF][]" value="0"></td>
		<td>&nbsp;</td>
	</tr>
<?endif?>
<?
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => $ID));
	while($arEnum = $rsEnum->GetNext()):

		if($bVarsFromForm && is_array($_REQUEST['LIST'][$arEnum["ID"]]))
			foreach($_REQUEST['LIST'][$arEnum["ID"]] as $key=>$val)
				$arEnum[$key] = htmlspecialchars($val);
?>
	<tr>
		<td><?=$arEnum["ID"]?></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][XML_ID]" value="<?=$arEnum["XML_ID"]?>" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][VALUE]" value="<?=$arEnum["VALUE"]?>" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][SORT]" value="<?=$arEnum["SORT"]?>" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="<?=$arEnum["ID"]?>" <?=($arEnum["DEF"]=="Y"? "checked": "")?>></td>
		<td><input type="checkbox" name="LIST[<?=$arEnum["ID"]?>][DEL]" value="Y"<?if($arEnum["DEL"] == "Y") echo " checked"?>></td>
	</tr>
<?
	endwhile;
?>
<?
if($bVarsFromForm):
	$n = 0;
	foreach($_REQUEST['LIST'] as $key=>$val):
		if(strncmp($key, "n", 1)===0):
?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" name="LIST[n<?=$n?>][XML_ID]" value="<?=htmlspecialchars($val["XML_ID"])?>" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[n<?=$n?>][VALUE]" value="<?=htmlspecialchars($val["VALUE"])?>" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[n<?=$n?>][SORT]" value="<?=htmlspecialchars($val["SORT"])?>" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="n<?=$n?>"></td>
		<td><input type="checkbox" name="LIST[n<?=$n?>][DEL]" value="Y"<?if($val["DEL"] == "Y") echo " checked"?>></td>
	</tr>
<?
			$n++;
		endif;
	endforeach;
else:
?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" name="LIST[n0][XML_ID]" value="" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[n0][VALUE]" value="" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[n0][SORT]" value="500" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="n0"></td>
		<td><input type="checkbox" name="LIST[n0][DEL]" value="Y"></td>
	</tr>
<?
endif;
?>
	</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="button" value="<?=GetMessage("USER_TYPE_LIST_MORE")?>" OnClick="addNewRow('list_table')" ></td>
	</tr>
<?endif?>
<?
$tabControl->Buttons(
	array(
		"disabled"=>$RIGHTS<"W",
		"back_url"=>"userfield_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<input type="hidden" name="back_url" value="<?=htmlspecialchars($back_url)?>">
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>

<?echo BeginNote();?>
<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>