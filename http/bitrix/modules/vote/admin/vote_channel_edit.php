<? 
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");

ClearVars();


$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE", "vote_channel_list.php");

$arrSites = array();
$rs = CSite::GetList(($by="sort"), ($order="asc"));
while ($ar = $rs->Fetch()) $arrSites[$ar["ID"]] = $ar;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("VOTE_PROP"), "ICON" => "main_channel_edit", "TITLE" => GetMessage("VOTE_GRP_PROP")),
	array("DIV" => "edit2", "TAB" => GetMessage("VOTE_ACCESS"), "ICON" => "main_channel_edit", "TITLE" => GetMessage("VOTE_RIGHTS")),

);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;
/********************************************************************
				Functions
********************************************************************/
function CheckFields() 
{
	global $DB, $TITLE, $SYMBOLIC_NAME, $err_mess, $ID;
	$str = "";
	$SYMBOLIC_NAME = trim($SYMBOLIC_NAME);
	if (strlen(trim($TITLE))<=0) 
		$aMsg[] = array("id"=>"TITLE", "text"=>GetMessage("VOTE_FORGOT_TITLE"));

    $arrSite=$_REQUEST[arrSITE];
    if ((!is_array($arrSite)) || count($arrSite)<=0)
        $aMsg[] = array("id"=>"arrSITE", "text"=>GetMessage("VOTE_FORGOT_SITE"));

	if (strlen($SYMBOLIC_NAME)<=0) 
		$aMsg[] = array("id"=>"SYMBOLIC_NAME", "text"=>GetMessage("VOTE_FORGOT_SYMBOLIC_NAME"));
	else
	{
		if (preg_match("/[^a-z_0-9]/is", $SYMBOLIC_NAME, $matches))
			$aMsg[] = array("id"=>"SYMBOLIC_NAME", "text"=>GetMessage("VOTE_INCORRECT_SYMBOLIC_NAME")); 
		else
		{
			if (is_array($arrSite) && count($arrSite)>0)
			{
				$arFilter = array(
					"ID"				=> "~$ID", 
					"SITE"				=> $arrSite,
					"ACTIVE"			=> "Y",
					"SID"				=> $SYMBOLIC_NAME, 
					"SID_EXACT_MATCH"	=> "Y"
					);
				$a = CVoteChannel::GetList($v1, $v2, $arFilter, $v3);
				if ($ar = $a->Fetch()) 
					$aMsg[] = array("id"=>"SYMBOLIC_NAME", "text"=>str_replace("#ID#", $ar["ID"], GetMessage("VOTE_SYMBOLIC_NAME_ALREADY_IN_USE")));
            }
		}
	}
	if(!empty($aMsg))
	{
		$e = new CAdminException($aMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

/********************************************************************
				Actions 
********************************************************************/
$ID = intval($ID);
if ($TEMPLATE=="NOT_REF") $TEMPLATE="";
if ($RESULT_TEMPLATE=="NOT_REF") $RESULT_TEMPLATE="";

$w = CGroup::GetList($by = "dropdown", $order = "asc", Array("ADMIN"=>"N"));
$arGroups = array();
while ($wr=$w->Fetch()) $arGroups[] = array("ID"=>$wr["ID"], "NAME"=>"[<a class=\"tablebodylink\" href=\"/bitrix/admin/group_edit.php?ID=".intval($wr["ID"])."&lang=".LANGUAGE_ID."\">".intval($wr["ID"])."</a>] ".htmlspecialchars($wr["NAME"]));
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	if (CheckFields())
	{
		$DB->PrepareFields("b_vote_channel");
		InitBVar($str_ACTIVE);
		InitBVar($str_VOTE_SINGLE);
		InitBVar($str_USE_CAPTCHA);
		if (is_array($arrSITE))
		{
			reset($arrSITE);
			list($k, $FIRST_SITE_ID) = each($arrSITE);
		}		
		$arFields = array(
			"TIMESTAMP_X"		=> $DB->GetNowFunction(),
			"C_SORT"			=> "'".$str_C_SORT."'",
			"FIRST_SITE_ID"		=> "'".$DB->ForSql($FIRST_SITE_ID,2)."'",
			"ACTIVE"			=> "'".$str_ACTIVE."'",
			"VOTE_SINGLE"		=> "'".$str_VOTE_SINGLE."'",
			"USE_CAPTCHA"		=> "'".$str_USE_CAPTCHA."'",
			"TITLE"				=> "'".$str_TITLE."'",
			"SYMBOLIC_NAME"		=> "upper('".$str_SYMBOLIC_NAME."')"
			);

        global $CACHE_MANAGER;
		if (VOTE_CACHE_TIME!==false) $CACHE_MANAGER->CleanDir("b_vote_channel");
        if ($ID>0) {
            $DB->Update("b_vote_channel",$arFields,"WHERE ID='".$ID."'",$err_mess.__LINE__);
        }
        else 
        {
            $ID = $DB->Insert("b_vote_channel",$arFields, $err_mess.__LINE__);
            $new = "Y";
        }
		if (strlen($strError)<=0) 
		{
            if (defined("BX_COMP_MANAGED_CACHE"))
                $CACHE_MANAGER->ClearByTag("vote_form_channel_".$ID);
			if (VOTE_CACHE_TIME!==false) $CACHE_MANAGER->Clean("b_vote_channel_2_site");

            $DB->Query("DELETE FROM b_vote_channel_2_site WHERE CHANNEL_ID='".$ID."'", false, $err_mess.__LINE__);
            if (is_array($arrSITE))
            {
                reset($arrSITE);
                foreach($arrSITE as $sid)
                {
                    $strSql = "INSERT INTO b_vote_channel_2_site (CHANNEL_ID, SITE_ID) VALUES ($ID, '".$DB->ForSql($sid,2)."')";
                    $DB->Query($strSql, false, $err_mess.__LINE__);
                }				
            }

			reset($arGroups);

			if (VOTE_CACHE_TIME!==false) $CACHE_MANAGER->CleanDir("b_vote_perm");

			$DB->Query("DELETE FROM b_vote_channel_2_group WHERE CHANNEL_ID='$ID'", false, $err_mess.__LINE__);
			while (list(,$arr)=each($arGroups))
			{
				$perm = intval(${"PERMISSION_".$arr["ID"]});
				if ($perm>0)
				{
					$arFields = array(
						"CHANNEL_ID"	=> "'".intval($ID)."'",
						"GROUP_ID"		=> "'".intval($arr["ID"])."'",
						"PERMISSION"	=> "'".$perm."'"
					);
					$DB->Insert("b_vote_channel_2_group",$arFields, $err_mess.__LINE__);
				}
			}
			if (strlen($save)>0) LocalRedirect("vote_channel_list.php?lang=".LANGUAGE_ID);
			else LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam());
		}
	}
	else
	{
		$str_SYMBOLIC_NAME=htmlspecialchars($SYMBOLIC_NAME);
		$str_TITLE=htmlspecialchars($TITLE);
		if($e = $APPLICATION->GetException()) $message = new CAdminMessage(GetMessage("VOTE_GOT_ERROR"), $e);
	}
}

$channel = CVoteChannel::GetByID($ID);
if (!($channel && $channel->ExtractFields()))
{
	$ID=0; 
	$str_ACTIVE = "Y";
	$str_C_SORT = "100";
	$str_VOTE_SINGLE = "Y";
	$str_USE_CAPTCHA= "N";
}
else
{
	$arrSITE =  CVoteChannel::GetSiteArray($ID);
}
if (strlen($strError)>0) $DB->InitTableVarsForEdit("b_vote_channel", "", "str_");

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("VOTE_EDIT_RECORD")) : GetMessage("VOTE_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/********************************************************************
				Form
********************************************************************/
$aMenu = array();
$aMenu[] = array(
	"TEXT"	=> GetMessage("VOTE_LIST"),
	"TITLE" => GetMessage("VOTE_RECORDS_LIST"),
	"LINK"	=> "/bitrix/admin/vote_channel_list.php?lang=".LANGUAGE_ID,
	"ICON" => "btn_list"
);


if ($ID>0)
{
	if ($VOTE_RIGHT=="W")
	{
        $aMenu[] = array(
            "TEXT"	=> GetMessage("VOTE_CREATE"),
            "TITLE"	=> GetMessage("VOTE_CREATE_NEW_RECORD"),
            "LINK"	=> "/bitrix/admin/vote_channel_edit.php?lang=".LANGUAGE_ID,
            "ICON" => "btn_new");

		$aMenu[] = array(
			"TEXT"	=> GetMessage("VOTE_DELETE"), 
			"TITLE"	=> GetMessage("VOTE_DELETE_RECORD"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_channel_list.php?action=delete&ID=".$ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
			"ICON" => "btn_delete"
			);
	}

	$aMenu[] = array(
		"NEWBAR" => "Y"
		);

		
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_VOTES").($str_VOTES?" [".$str_VOTES."]":""),
		"TITLE"	=> GetMessage("VOTE_VOTES_TITLE"),
		"LINK"	=> "/bitrix/admin/vote_list.php?lang=".LANGUAGE_ID."&find_channel_id=$ID&set_filter=Y",
		"ICON" => "btn_list"
		);

	if ($VOTE_RIGHT=="W")
	{
		$aMenu[] = array(
			"TEXT"	=> GetMessage("VOTE_CREATE_VOTE"),
			"TITLE"	=> GetMessage("VOTE_VOTE_ADD"),
			"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&CHANNEL_ID=$ID",
			"ICON" => "btn_new"
		);
	}	
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message) echo $message->Show();

?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="post_form">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<? if (strlen($str_TIMESTAMP_X)>0 && $str_TIMESTAMP_X!="00.00.0000 00:00:00") : ?>
	<tr>
		<td><?=GetMessage("VOTE_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td width="40%"><?=GetMessage("VOTE_ACTIVE")?></td>
		<td width="60%"><?=InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false)?></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("VOTE_VOTE_SINGLE")?></td>
		<td width="60%"><?=InputType("checkbox","VOTE_SINGLE","Y",$str_VOTE_SINGLE,false)?></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("VOTE_USE_CAPTCHA")?></td>
		<td width="60%"><?=InputType("checkbox","USE_CAPTCHA","Y",$str_USE_CAPTCHA,false)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_SORTING")?></td>
		<td><input type="text" name="C_SORT" size="5" value="<?=$str_C_SORT?>"></td>
	</tr>
	<tr valign="top"> 
		<td><span class="required">*</span><?=GetMessage("VOTE_SITE")?></td>
		<td><?
		reset($arrSites);
		while(list($sid, $arrS) = each($arrSites)):
			$checked = ((is_array($arrSITE) && in_array($sid, $arrSITE)) || ($ID<=0 && $def_site_id==$sid)) ? "checked" : "";
			?>
			<input type="checkbox" name="arrSITE[]" value="<?=htmlspecialcharsex($sid)?>" id="<?=htmlspecialcharsex($sid)?>" class="typecheckbox" <?=$checked?> <?=$disabled?>> 
			<label for="<?=htmlspecialchars($sid)?>"><?echo '[<a title="'.GetMessage("VOTE_SITE_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.htmlspecialchars($sid).'&lang='.LANGUAGE_ID.'">'.htmlspecialchars($sid).'</a>]&nbsp;'.htmlspecialcharsex($arrS["NAME"])?></label>
			<br>
			<?
		endwhile;
		?></td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("VOTE_SYMBOLIC_NAME")?></td>
		<td><input type="text" name="SYMBOLIC_NAME" size="20" maxlength="255" value="<?=$str_SYMBOLIC_NAME?>"></td>
	</tr>
	<tr>
		<td><span class="required">*</span><?=GetMessage("VOTE_TITLE")?></td>
		<td><input type="text" name="TITLE" size="60" maxlength="255" value="<?=$str_TITLE?>"></td>
	</tr>
<?
//********************
//Permissions Tab
//********************
$tabControl->BeginNextTab();
?>
	<?
	reset($arGroups);

	$arPerm = CVoteChannel::GetArrayGroupPermission($ID);

	$arr = array("reference_id" => array(0,1,2), "reference" => array(GetMessage("VOTE_DENIED"), GetMessage("VOTE_READ"), GetMessage("VOTE_WRITE")));
	while (list(,$group)=each($arGroups)) :
	?>
	<tr>
		<td width=40%><?=$group["NAME"].":"?></td>
		<td><?
		if ($ID>0) 
			//$perm = CAllVoteChannel::GetGroupPermission($ID, array($group["ID"]),"Y");
			$perm = (array_key_exists($group["ID"], $arPerm) ? $arPerm[$group["ID"]] : 0);
		else $perm = 2;
		echo SelectBoxFromArray("PERMISSION_".$group["ID"], $arr, $perm);
		?></td>
	</tr>
	<?endwhile;?>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($VOTE_RIGHT<"W"), "back_url"=>"vote_channel_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>


</form>
<?
$tabControl->ShowWarnings("post_form", $message);
?>
<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); 
?>
