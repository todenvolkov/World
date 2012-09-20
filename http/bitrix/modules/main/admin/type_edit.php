<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/mail_events/messagetype_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$arFields = array();
$arParams = array("ACTION" => "ADD");
$strError = "";
$bVarsFromForm = false;
$message = null;
$arLangs = array();
// **************************************************************************
$db_res = CLanguage::GetList(($by="sort"), ($order="asc"));
if ($db_res && $res = $db_res->GetNext())
{
	do 
	{
		$arParams["LANGUAGE"][$res["LID"]] = $res;
		$arLangs[$res["LID"]] = true;
	} while ($res = $db_res->GetNext());
}
// **************************************************************************
if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0) && $isAdmin && check_bitrix_sessid())
{
	$_POST["EVENT_NAME"] = trim($_POST["EVENT_NAME"]);

	$res = array();
	$DB->StartTransaction();
	if($_POST["EVENT_NAME"] <> '')
	{
		$db_res = CEventType::GetListEx(array(), array("EVENT_NAME" => $_POST["EVENT_NAME"]), array("type" => "full"));
		if(!($db_res) || !($res = $db_res->Fetch()))
		{
			$res["EVENT_NAME"] = $_POST["EVENT_NAME"];
		}
	}
	
	foreach ($arParams["LANGUAGE"] as $idLang => $arLang)
	{
		$arType = array(
			"ID" => $_POST["FIELDS"][$idLang]["ID"],
			"SORT" => $_POST["FIELDS"][$idLang]["SORT"],
			"NAME" => $_POST["FIELDS"][$idLang]["NAME"],
			"DESCRIPTION" => $_POST["FIELDS"][$idLang]["DESCRIPTION"],
			"LID" => $idLang,
			"EVENT_NAME" => $res["EVENT_NAME"],
		);
		if ((CAdminList::IsUpdated($idLang)) && ($_REQUEST[$idLang] == "Y"))
		{
			if ((intVal($arType["ID"]) > 0 && (!CEventType::Update(array("ID" => $arType["ID"]), $arType))) || 
				((intVal($arType["ID"]) <= 0) && !CEventType::Add($arType)))
			{
				$bVarsFromForm = true;
			}
		}
		if ($_REQUEST[$idLang] != "Y")
		{
			unset($arLangs[$idLang]);
			
			if (intVal($arType["ID"]) > 0)
			{
				if (!CEventType::Delete(array("ID" => $arType["ID"])))
					$bVarsFromForm = true;
			}
		}
		if ($bVarsFromForm)
			break;
	}
	
	if (empty($arLangs))
	{
		$arMsg = array();
		if (empty($res["EVENT_NAME"]))
			$arMsg[] = array("id" => "EVENT_NAME_EMPTY", "text" => GetMessage("EVENT_NAME_EMPTY"));
		$arMsg[] = array("id" => "LID_EMPTY", "text" => GetMessage("ERROR_LANG_EMPTY"));
		
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$bVarsFromForm = true;
	}
	if ($bVarsFromForm)
	{
		$DB->Rollback();
	}
	else 
	{
		$DB->Commit();
		if (strlen($save) > 0)
			LocalRedirect(BX_ROOT."/admin/type_admin.php?lang=".LANGUAGE_ID);
		else
			LocalRedirect(BX_ROOT."/admin/type_edit.php?EVENT_NAME=".$res["EVENT_NAME"]."&lang=".LANGUAGE_ID);
	}
}
if ($bVarsFromForm && ($e = $APPLICATION->GetException()))
	$message = new CAdminMessage(GetMessage("MAIN_ERROR_SAVING"), $e);
// **************************************************************************
$arParams["EVENT_NAME"] = $_REQUEST["EVENT_NAME"];
// **************************************************************************
if (!empty($arParams["EVENT_NAME"]))
{
	$db_res = CEventType::GetListEx(array(), array("EVENT_NAME" => $arParams["EVENT_NAME"]), array("type" => "full"));
	if ($db_res && $res=$db_res->Fetch())
	{
		$arParams["DATA"] = $res;
		if (is_array($res["TYPE"]))
		{
			foreach ($res["TYPE"] as $r)
				$arParams["DATA"][$r["LID"]] = $r;
		}
		$arParams["ACTION"] = "UPDATE";
		$arParams["DATA_OLD"] = $arParams["DATA"];
	}
}
// **************************************************************************
// **************************************************************************
$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("EVENT_NAME_TITLE"), "ICON" => "mail", "TITLE" => GetMessage("EVENT_NAME_DESCR")));
if ($arParams["ACTION"] == "UPDATE")
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("TEMPLATES_TITLE"), "ICON" => "mail", "TITLE" => GetMessage("TEMPLATES_DESCR"));
	
$tabControl = new CAdminTabControl("tabControl", $aTabs);
// **************************************************************************
if ($bVarsFromForm)
{
	foreach ($_REQUEST["FIELDS"] as $k => $v)
	{
		if ($_REQUEST[$k] == "Y")
			$arParams["DATA"][$k] = $_REQUEST["FIELDS"][$k];
	}
}
// **************************************************************************
if ($arParams["ACTION"]=="ADD")
{
	$APPLICATION->SetTitle(GetMessage("NEW_TITLE"));
	$context = new CAdminContextMenu(
		array(
			array(
				"TEXT"	=> GetMessage("RECORD_LIST"),
				"LINK"	=> "/bitrix/admin/type_admin.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
				"ICON"	=> "btn_list"
			), 
		)
	);
}
else
{
	$APPLICATION->SetTitle(str_replace("#TYPE#", $arParams["EVENT_NAME"], GetMessage("EDIT_TITLE")));
	$context = new CAdminContextMenu(
		array(
			array(
				"TEXT"	=> GetMessage("RECORD_LIST"),
				"LINK"	=> "/bitrix/admin/type_admin.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
				"ICON"	=> "btn_list"
			), 
			array(
				"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
				"LINK"	=> "/bitrix/admin/type_edit.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
				"ICON"	=> "btn_new"
			),
			array(
				"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
				"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/type_admin.php?ID=".urlencode($arParams["EVENT_NAME"])."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
				"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
				"ICON"	=> "btn_delete"
			),
			array("NEWBAR"=>true),
			array(
				"TEXT"	=> GetMessage("TEMPLATE_LIST"),
				"LINK"	=> "/bitrix/admin/message_admin.php?find_event_type=".urlencode($arParams["EVENT_NAME"])."&amp;lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("TEMPLATE_LIST_TITLE"),
				"ICON"	=> "btn_list"
			),
			array(
				"TEXT"	=> GetMessage("MAIN_NEW_TEMPLATE"),
				"LINK"	=> "/bitrix/admin/message_edit.php?EVENT_NAME=".urlencode($arParams["EVENT_NAME"])."&type=this&lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("MAIN_NEW_TEMPLATE_TITLE"),
				"ICON"	=> "btn_new"
			),
		)
	);
}
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$context->Show();
if($message)
	echo $message->Show();
$arParams["EVENT_NAME"] = htmlspecialcharsEx($arParams["EVENT_NAME"]);
?>
<style>
.visible{display:auto;}
.hidden{display:none;}
</style>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<?=$tabControl->Begin();?>
<?=$tabControl->BeginNextTab();?>
<tr>
	<td width="40%"><span class="required">*</span> <label for="EVENT_NAME"><?=GetMessage('EVENT_NAME')?>:</label></td>
	<td width="0%">
	<?if ($arParams["ACTION"] == "ADD"):?>
		<input type="text" name="EVENT_NAME" value="<?=$arParams["EVENT_NAME"]?>">
	<?else:?>
		<input type="hidden" name="EVENT_NAME" value="<?=$arParams["EVENT_NAME"]?>"> 
		<?=$arParams["EVENT_NAME"]?>
	<?endif;?>
	</td>
</tr>
<?foreach ($arParams["LANGUAGE"] as $idLang => $arLang):
	$strTbodyClass = empty($arParams["DATA"][$idLang]) ? "hidden" : "visible";?>
<tr class="heading">
	<td style="text-align:right;"><label for="<?=$idLang?>" >[<?=$arLang["ID"]?>] <?=$arLang["NAME"]?></label></td>
	<td style="text-align:left;"><input type="checkbox" id="<?=$idLang?>" name="<?=$idLang?>" onclick="checkThisTBody(this, '<?=$idLang?>')"<?=($strTbodyClass == "visible" ? " checked" : "")?> value="Y"><input type="hidden" name="LID"></td>
</tr>
<tbody id="lang<?=$idLang?>" class="<?=$strTbodyClass?>">
<?if ($arParams["DATA"][$arLang["ID"]]["ID"] > 0):?>
<tr valign="top"><td>ID:</td><td>
<?=htmlspecialcharsEx($arParams["DATA"][$arLang["ID"]]["ID"])?>
<input type="hidden" name="FIELDS[<?=$arLang["ID"]?>][ID]" value="<?=htmlspecialcharsEx($arParams["DATA"][$arLang["ID"]]["ID"])?>">
</td></tr>
<?endif;?>
<tr valign="top">
	<td><?=GetMessage("EVENT_SORT_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][SORT]" value="<?=htmlspecialcharsEx($arParams["DATA_OLD"][$arLang["ID"]]["SORT"])?>">
		<input type="text" name="FIELDS[<?=$arLang["ID"]?>][SORT]" value="<?=(intVal($arParams["DATA"][$arLang["ID"]]["SORT"]) ? $arParams["DATA"][$arLang["ID"]]["SORT"] : "150")?>">
	</td>
</tr>
<tr valign="top">
	<td><?=GetMessage("EVENT_NAME_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][NAME]" value="<?=htmlspecialcharsEx($arParams["DATA_OLD"][$arLang["ID"]]["NAME"])?>">
		<input type="text" name="FIELDS[<?=$arLang["ID"]?>][NAME]" value="<?=htmlspecialcharsEx($arParams["DATA"][$arLang["ID"]]["NAME"])?>" style="width:100%;">
	</td>
</tr>
<tr valign="top">
	<td><?=GetMessage("EVENT_DESCR_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][DESCRIPTION]" value="<?=htmlspecialcharsEx($arParams["DATA_OLD"][$arLang["ID"]]["DESCRIPTION"])?>">
		<textarea name="FIELDS[<?=$arLang["ID"]?>][DESCRIPTION]" style="width:100%;" rows="10"><?=htmlspecialcharsEx($arParams["DATA"][$arLang["ID"]]["DESCRIPTION"])?></textarea>
	</td>
</tr>
</tbody>
<?endforeach;?>
<?$tabControl->BeginNextTab();
if (is_array($arParams["DATA"]["TEMPLATES"])):
	foreach ($arParams["DATA"]["TEMPLATES"] as $k => $v):
?><tr valign="top" valign="top">
	<td width="40%">[<a href="/bitrix/admin/message_edit.php?ID=<?=$v["ID"]?>"><?=$v["ID"]?></a>]<?=(strLen(trim($v["SUBJECT"])) > 0 ? " " : "").htmlspecialcharsEx($v["SUBJECT"])?>:</td>
	<td width="60%"><?
	$arLID = Array();
	$db_LID = CEventMessage::GetLang($v["ID"]);
	while($arrLID = $db_LID->Fetch())
		$arLID[] = $arrLID["LID"];
	?><?=CLang::SelectBoxMulti("LID_READONLY", $arLID);?></td>
</tr><?
	endforeach;
endif;
$tabControl->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"type_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>

<script language="JavaScript">

result = document.forms['form1'].elements['LID_READONLY[]'];
if (result)
{
	for (var i=0; i < result.length; i++)
	{
		result[i].disabled = "disabled";
	}
}

function checkThisTBody(checkBox, id)
{
	if (checkBox && id)
	{
		 if (checkBox.checked)
		 	document.getElementById('lang'+id).className = "visible";
		 else
	 		document.getElementById('lang'+id).className = "hidden";
	}
	return;
}
</script>
<?
$tabControl->ShowWarnings(
	"form1", $message, 
	array(
		"EVENT_NAME_EMPTY" => "EVENT_NAME", 
		"LID_EMPTY" => "LID",
		"EVENT_NAME_EXIST" => "EVENT_NAME", 
		"EVENT_ID_EMPTY" => "EVENT_NAME", 
		"EVENT_NAME_EXIST" => "EVENT_NAME"));
?>
<?echo BeginNote();?>
<span class="required">*</span> - <?=GetMessage("REQUIRED_FIELDS")?><br />
<?=GetMessage("LANG_FIELDS")?><br />
<?echo EndNote();?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
