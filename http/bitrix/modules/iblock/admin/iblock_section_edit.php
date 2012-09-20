<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$strWarning="";
$bVarsFromForm = false;
$message = false;
$ID=IntVal($ID);
$IBLOCK_SECTION_ID=IntVal($IBLOCK_SECTION_ID);

$IBLOCK_ID = IntVal($IBLOCK_ID);

$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
	$bBadBlock=($BlockPerm < "W");
else
	$bBadBlock=true;

if(!$bBadBlock)
{
	$arIBTYPE = CIBlockType::GetByIDLang((strlen($type)>0?$type:$arIBlock['IBLOCK_TYPE_ID']), LANG);
	if($arIBTYPE===false)
		$bBadBlock = true;
	else
		$type = $arIBlock['IBLOCK_TYPE_ID'];
}

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if($bBadBlock):
	?>
	<?echo ShowError(GetMessage("IBSEC_E_BAD_IBLOCK"));?>
	<a href="iblock_admin.php?lang=<?=LANG?>&amp;type=<?=urlencode($type)?>"><?echo GetMessage("IBSEC_E_BACK_TO_ADMIN")?></a>
	<?
	endif;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if(!$arIBlock["SECTION_NAME"])
	$arIBlock["SECTION_NAME"] = $arIBTYPE["SECTION_NAME"]? $arIBTYPE["SECTION_NAME"]: GetMessage("IBLOCK_SECTION");

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => $arIBlock["SECTION_NAME"], "ICON"=>"iblock_section", "TITLE"=> htmlspecialchars($ID > 0 ? $arIBlock["SECTION_EDIT"] : $arIBlock["SECTION_ADD"]));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("IBSEC_E_TAB2"), "ICON"=>"iblock_section", "TITLE"=>GetMessage("IBSEC_E_TAB2_TITLE"));

//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION")) > 0) ||
	($USER_FIELD_MANAGER->GetRights("IBLOCK_".$IBLOCK_ID."_SECTION") >= "W")
)
{
	$aTabs[] = $USER_FIELD_MANAGER->EditFormTab("IBLOCK_".$IBLOCK_ID."._SECTION");
}

$tabControl = new CAdminForm("form_section_".$IBLOCK_ID, $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$DB->StartTransaction();
	$bs = new CIBlockSection;

	if(array_key_exists("PICTURE", $_FILES))
		$arPICTURE = $_FILES["PICTURE"];
	elseif(isset($_REQUEST["PICTURE"])  && is_file($_SERVER["DOCUMENT_ROOT"].$_REQUEST["PICTURE"]))
	{
		$arPICTURE = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$_REQUEST["PICTURE"]);
		$arPICTURE["COPY_FILE"] = "Y";
	}
	else
		$arPICTURE = array();

	$arPICTURE["del"] = ${"PICTURE_del"};

	if(array_key_exists("DETAIL_PICTURE", $_FILES))
		$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
	elseif(isset($_REQUEST["DETAIL_PICTURE"]))
	{
		$arDETAIL_PICTURE = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$_REQUEST["DETAIL_PICTURE"]);
		$arDETAIL_PICTURE["COPY_FILE"] = "Y";
	}
	else
		$arDETAIL_PICTURE = array();

	$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};

	$arFields = Array(
		"ACTIVE"=>$ACTIVE,
		"IBLOCK_SECTION_ID"=>$IBLOCK_SECTION_ID,
		"IBLOCK_ID"=>$IBLOCK_ID,
		"NAME"=>$NAME,
		"SORT"=>$SORT,
		"CODE"=>$_POST["CODE"],
		"PICTURE"=>$arPICTURE,
		"DETAIL_PICTURE"=>$arDETAIL_PICTURE,
		"DESCRIPTION"=>$DESCRIPTION,
		"DESCRIPTION_TYPE"=>$DESCRIPTION_TYPE
		);

	$USER_FIELD_MANAGER->EditFormAddFields("IBLOCK_".$IBLOCK_ID."_SECTION", $arFields);

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if($ID>0)
	{
		$res = $bs->Update($ID, $arFields, true, true, true);
	}
	else
	{
		$ID = $bs->Add($arFields, true, true, true);
		$res = ($ID>0);
	}

	if(!$res)
	{
		$strWarning .= $bs->LAST_ERROR;
		$bVarsFromForm = true;
		$DB->Rollback();
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("admin_lib_error"), $e);
	}
	else
	{
		CIBlockSection::ReSort($IBLOCK_ID);
		$DB->Commit();
		if(strlen($apply)<=0)
		{
			if(strlen($return_url)>0)
			{
				if(strpos($return_url, "#")!==false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("ID" => $ID), false, array("SECTION_PAGE_URL"));
					$arSection = $rsSection->Fetch();
					if($arSection)
						$return_url = CIBlock::ReplaceDetailUrl($return_url, $arSection, true, "S");
				}
				LocalRedirect($return_url);
			}
			else
			{
				LocalRedirect("/bitrix/admin/".CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section))));
			}
		}

		LocalRedirect("/bitrix/admin/iblock_section_edit.php?lang=". LANG."&type=".urlencode($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."&".$tabControl->ActiveTabParam().'&ID='.$ID.(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):""));
	}
}

ClearVars("str_");
$str_ACTIVE="Y";
$str_NAME = htmlspecialchars($arIBlock["FIELDS"]["SECTION_NAME"]["DEFAULT_VALUE"]);
$str_DESCRIPTION_TYPE = $arIBlock["FIELDS"]["SECTION_DESCRIPTION_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
$str_DESCRIPTION = htmlspecialchars($arIBlock["FIELDS"]["SECTION_DESCRIPTION"]["DEFAULT_VALUE"]);
$str_SORT="500";
$str_IBLOCK_SECTION_ID = $IBLOCK_SECTION_ID;

$result = CIBlockSection::GetByID($ID);
$arSection = $result->ExtractFields("str_");
if(!$arSection)
	$ID=0;

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_iblock_section", "", "str_");

if($ID>0)
	$APPLICATION->SetTitle(GetMessage("IBSEC_E_EDIT_TITLE", array("#IBLOCK_NAME#"=>$arIBlock["NAME"], "#SECTION_TITLE#"=>$arIBTYPE["SECTION_NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("IBSEC_E_NEW_TITLE", array("#IBLOCK_NAME#"=>$arIBlock["NAME"], "#SECTION_TITLE#"=>$arIBTYPE["SECTION_NAME"])));


$adminChain->AddItem(array(
	"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
	"LINK" => htmlspecialchars(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0))),
));
if($find_section_section > 0)
{
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, IntVal($find_section_section));
	while($ar_nav = $nav->GetNext())
	{
		$last_nav = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"]));
		$adminChain->AddItem(array(
			"TEXT" => $ar_nav["NAME"],
			"LINK" => htmlspecialchars($last_nav),
		));
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>htmlspecialchars($arIBlock["SECTIONS_NAME"]),
		"LINK"=>CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section))),
		"ICON"=>"btn_list",
	)
);

if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>htmlspecialchars($arIBlock["SECTION_ADD"]),
		"LINK"=>"iblock_section_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section)."&IBLOCK_SECTION_ID=".htmlspecialchars($IBLOCK_SECTION_ID>0?$IBLOCK_SECTION_ID:$find_section_section),
		"ICON"=>"btn_new",
	);

	$urlDelete = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section), 'action'=>'delete'));
	$urlDelete .= '&'.bitrix_sessid_get();
	$urlDelete .= '&ID[]='.(preg_match('/^iblock_list_admin\.php/', $urlDelete)? "S": "").$ID;

	$aMenu[] = array(
		"TEXT"=>htmlspecialchars($arIBlock["SECTION_DELETE"]),
		"LINK"=>"javascript:if(confirm('".GetMessage("IBSEC_E_CONFIRM_DEL_MESSAGE")."'))window.location='".CUtil::JSEscape($urlDelete)."'",
		"ICON"=>"btn_delete",
	);
}

$link = DeleteParam(array("mode"));
$link = $GLOBALS["APPLICATION"]->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");
$aMenu[] = array(
	"TEXT"=>GetMessage("admin_lib_context_sett"),
	"TITLE"=>GetMessage("admin_lib_context_sett_title"),
	"LINK"=>"javascript:".$tabControl->GetName().".ShowSettings('".htmlspecialchars(CUtil::addslashes($link))."')",
	"ICON"=>"btn_settings",
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($strWarning)
	CAdminMessage::ShowOldStyleError($strWarning."<br>");
elseif($message)
	echo $message->Show();
?>

<?
//We have to explicitly call calendar and editor functions because
//first output may be discarded by form settings
$bFileman = CModule::IncludeModule("fileman");

$arTranslit = $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"];
$bLinked = !$ID && $_POST["linked_state"]!=='N';

$tabControl->BeginPrologContent();
if(method_exists($USER_FIELD_MANAGER, 'showscript'))
	echo $USER_FIELD_MANAGER->ShowScript();
echo CAdminCalendar::ShowScript();
if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && $bFileman)
{
	//TODO:This dirty hack will be replaced by special method like calendar do
	echo '<div style="display:none">';
	CFileMan::AddHTMLEditorFrame(
		"SOME_TEXT",
		"",
		"SOME_TEXT_TYPE",
		"text",
		array(
			'height' => 450,
			'width' => '100%'
		),
		"N",
		0,
		"",
		"",
		$arIBlock["LID"]
	);
	echo '</div>';
}
if($bFileman)
	CMedialibTabControl::ShowScript();

if($arTranslit["TRANSLITERATION"] == "Y")
{
	CUtil::InitJSCore(array('translit'));
	?>
	<script>
		var linked=<?if($bLinked) echo 'true'; else echo 'false';?>;
		function set_linked()
		{
			linked=!linked;

			var name_link = document.getElementById('name_link');
			if(name_link)
			{
				if(linked)
					name_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					name_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var code_link = document.getElementById('code_link');
			if(code_link)
			{
				if(linked)
					code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var linked_state = document.getElementById('linked_state');
			if(linked_state)
			{
				if(linked)
					linked_state.value='Y';
				else
					linked_state.value='N';
			}
		}
		var oldValue = '';
		function transliterate()
		{
			if(linked)
			{
				var from = document.getElementById('NAME');
				var to = document.getElementById('CODE');
				if(from && to && oldValue != from.value)
				{
					BX.translit(from.value, {
						'max_len' : <?echo intval($arTranslit['TRANS_LEN'])?>,
						'change_case' : '<?echo $arTranslit['TRANS_CASE']?>',
						'replace_space' : '<?echo $arTranslit['TRANS_SPACE']?>',
						'replace_other' : '<?echo $arTranslit['TRANS_OTHER']?>',
						'delete_repeat_replace' : <?echo $arTranslit['TRANS_EAT'] == 'Y'? 'true': 'false'?>,
						'use_google' : <?echo $arTranslit['USE_GOOGLE'] == 'Y'? 'true': 'false'?>,
						'callback' : function(result){to.value = result; setTimeout('transliterate()', 250);}
					});
					oldValue = from.value;
				}
				else
				{
					setTimeout('transliterate()', 250);
				}
			}
			else
			{
				setTimeout('transliterate()', 250);
			}
		}
		transliterate();
	</script>
	<?
}

$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
?>
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="linked_state" id="linked_state" value="<?if($bLinked) echo 'Y'; else echo 'N';?>">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($return_url)>0):?>
	<input type="hidden" name="return_url" value="<?=htmlspecialchars($return_url)?>">
<?endif?>
<?
$tabControl->EndEpilogContent();

$tabControl->Begin(array(
	"FORM_ACTION" => "/bitrix/admin/iblock_section_edit.php?type=".urlencode($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section),
));
$tabControl->BeginNextFormTab();
?>
	<?$tabControl->BeginCustomField("ID", "ID:");?>
<?if($ID>0):?>
	<tr>
		<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%"><?echo $str_ID?></td>
	</tr>
<?endif;?>
	<?$tabControl->EndCustomField("ID", '');?>

	<?$tabControl->BeginCustomField("DATE_CREATE", GetMessage("IBSEC_E_CREATED"));?>
<?if($ID>0):?>
		<?if(strlen($str_DATE_CREATE) > 0):?>
			<tr>
				<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
				<td width="60%"><?echo $str_DATE_CREATE?><?
				if(intval($str_CREATED_BY)>0):
					?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_CREATED_BY?>"><?echo $str_CREATED_BY?></a>]<?
					$rsUser = CUser::GetByID($str_CREATED_BY);
					$arUser = $rsUser->GetNext();
					if($arUser):
						echo "&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
					endif;
				endif;
				?></td>
			</tr>
		<?endif;?>
<?endif;?>
	<?$tabControl->EndCustomField("DATE_CREATE", '');?>

	<?$tabControl->BeginCustomField("TIMESTAMP_X", GetMessage("IBSEC_E_LAST_UPDATE"));?>
<?if($ID>0):?>
	<tr>
		<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%"><?echo $str_TIMESTAMP_X?><?
		if(intval($str_MODIFIED_BY)>0):
			?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]<?
			if(intval($str_CREATED_BY) != intval($str_MODIFIED_BY))
			{
				$rsUser = CUser::GetByID($str_MODIFIED_BY);
				$arUser = $rsUser->GetNext();
			}
			if($arUser):
				echo "&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
			endif;
		endif?></td>
	</tr>
<?endif;?>
	<?$tabControl->EndCustomField("TIMESTAMP_X", '');?>

<?
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("IBSEC_E_ACTIVE"), false, "Y", $str_ACTIVE=="Y");

$tabControl->BeginCustomField("IBLOCK_SECTION_ID", GetMessage("IBSEC_E_PARENT_SECTION").":");?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
		<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));?>
		<select name="IBLOCK_SECTION_ID" >
			<option value="0"><?echo GetMessage("IBLOCK_UPPER_LEVEL")?></option>
		<?
			while($a = $l->Fetch()):
				?><option value="<?echo intval($a["ID"])?>"<?if($str_IBLOCK_SECTION_ID==$a["ID"])echo " selected"?>><?echo str_repeat(".", $a["DEPTH_LEVEL"])?><?echo htmlspecialchars($a["NAME"])?></option><?
			endwhile;
		?>
		</select>
		</td>
	</tr>
<?
$tabControl->EndCustomField("IBLOCK_SECTION_ID", '<input type="hidden" id="IBLOCK_SECTION_ID" name="IBLOCK_SECTION_ID" value="'.$str_IBLOCK_SECTION_ID.'">');

if($arTranslit["TRANSLITERATION"] == "Y")
{
	$tabControl->BeginCustomField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true);
	?>
		<tr id="tr_NAME">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>
				<input type="text" size="50" name="NAME" id="NAME" maxlength="255" value="<?echo $str_NAME?>"><image id="name_link" title="<?echo GetMessage("IBSEC_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("NAME",
		'<input type="hidden" name="NAME" id="NAME" value="'.$str_NAME.'">'
	);

	$tabControl->BeginCustomField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["SECTION_CODE"]["IS_REQUIRED"] === "Y");
	?>
		<tr id="tr_CODE">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>

				<input type="text" size="50" name="CODE" id="CODE" maxlength="255" value="<?echo $str_CODE?>"><image id="code_link" title="<?echo GetMessage("IBSEC_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("CODE",
		'<input type="hidden" name="CODE" id="CODE" value="'.$str_CODE.'">'
	);
}
else
{
	$tabControl->AddEditField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true, array("size" => 50, "maxlength" => 255), $str_NAME);
}

$tabControl->BeginCustomField("PICTURE", GetMessage("IBSEC_E_PICTURE"), $arIBlock["FIELDS"]["SECTION_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("PICTURE", $_REQUEST) && $arSection)
	$str_PICTURE = intval($arSection["PICTURE"]);
?>
	<tr>
		<td valign="top" ><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td valign="top">
			<?if($bFileman):
				echo CMedialib::InputFile(
					"PICTURE", $str_PICTURE,
					array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
					"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
					array(), //file
					array(), //server
					array(), //media lib
					false, //descr
					array(), //delete
					false //scale
				);
			else:?>
				<?echo CFile::InputFile("PICTURE", 20, $str_PICTURE);?><br>
				<?echo CFile::ShowImage($str_PICTURE, "border=0", "", 200, 200, true)?>
			<?endif;?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("PICTURE", '');

$tabControl->BeginCustomField("DESCRIPTION", GetMessage("IBSEC_E_DESCRIPTION"), $arIBlock["FIELDS"]["SECTION_DESCRIPTION"]["IS_REQUIRED"] === "Y");
?>
	<tr  class="heading">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>

	<?if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && $bFileman):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame("DESCRIPTION", $str_DESCRIPTION, "DESCRIPTION_TYPE", $str_DESCRIPTION_TYPE, 300, "N", 0, "", "", $arIBlock["LID"]);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td  ><?echo GetMessage("IBSEC_E_DESC_TYPE")?></td>
		<td >
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_text" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>> <label for="DESCRIPTION_TYPE_text"><?echo GetMessage("IBSEC_E_DESC_TYPE_TEXT")?></label> /
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_html" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>> <label for="DESCRIPTION_TYPE_html"><?echo GetMessage("IBSEC_E_DESC_TYPE_HTML")?></label>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="15"  name="DESCRIPTION" style="width:100%"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif;
$tabControl->EndCustomField("DESCRIPTION",
	'<input type="hidden" name="DESCRIPTION" value="'.$str_DESCRIPTION.'">'.
	'<input type="hidden" name="DESCRIPTION_TYPE" value="'.$str_DESCRIPTION_TYPE.'">'
);

$tabControl->BeginNextFormTab();

$tabControl->AddEditField("SORT", GetMessage("IBLOCK_FIELD_SORT").":", false, array("size" => 7, "maxlength" => 10), $str_SORT);


if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y")
{
	$tabControl->AddEditField(
		"XML_ID",
		GetMessage("IBLOCK_FIELD_XML_ID").":",
		$arIBlock["FIELDS"]["SECTION_XML_ID"]["IS_REQUIRED"] === "Y",
		array("size" => 20, "maxlength" => 255),
		$str_XML_ID
	);
}

if($arTranslit["TRANSLITERATION"] != "Y")
{
	$tabControl->AddEditField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["SECTION_CODE"]["IS_REQUIRED"] === "Y", array("size" => 20, "maxlength" => 255), $str_CODE);
}

$tabControl->BeginCustomField("DETAIL_PICTURE", GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").":", $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("DETAIL_PICTURE", $_REQUEST) && $arSection)
	$str_DETAIL_PICTURE = intval($arSection["DETAIL_PICTURE"]);
?>
	<tr>
		<td valign="top" ><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td valign="top">
			<?if($bFileman):
				echo CMedialib::InputFile(
					"DETAIL_PICTURE", $str_DETAIL_PICTURE,
					array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
					"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
					array(), //file
					array(), //server
					array(), //media lib
					false, //descr
					array(), //delete
					false //scale
				);
			else:?>
				<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE);?><br>
				<?echo CFile::ShowImage($str_DETAIL_PICTURE, "border=0", "", 200, 200, true)?>
			<?endif;?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("DETAIL_PICTURE", '');

//Add user fields tab only when there is fields defined or user has rights for adding new field
$entity_id = "IBLOCK_".$IBLOCK_ID."_SECTION";
if(
	(count($USER_FIELD_MANAGER->GetUserFields($entity_id)) > 0) ||
	($USER_FIELD_MANAGER->GetRights($entity_id) >= "W")
)
{
	$tabControl->BeginNextFormTab();

	if($USER_FIELD_MANAGER->GetRights($entity_id) >= "W")
	{
		$tabControl->BeginCustomField("USER_FIELDS_ADD", GetMessage("IBSEC_E_USER_FIELDS_ADD_HREF"));
		?>
			<tr colspan="2">
				<td align="left">
					<a href="/bitrix/admin/userfield_edit.php?lang=<?echo LANG?>&amp;ENTITY_ID=<?echo urlencode($entity_id)?>&amp;back_url=<?echo urlencode($APPLICATION->GetCurPageParam()."&tabControl_active_tab=user_fields_tab")?>"><?echo $tabControl->GetCustomLabelHTML()?></a>
				</td>
			</tr>
		<?
		$tabControl->EndCustomField("USER_FIELDS_ADD", '');
	}

	$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entity_id, $ID, LANGUAGE_ID);
	foreach($arUserFields as $FIELD_NAME => $arUserField)
	{
		$arUserField["VALUE_ID"] = intval($ID);
		$strLabel = $arUserField["EDIT_FORM_LABEL"]? $arUserField["EDIT_FORM_LABEL"]: $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = $strLabel;

		$tabControl->BeginCustomField($FIELD_NAME, $strLabel, $arUserField["MANDATORY"]=="Y");
			echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);


		$form_value = $GLOBALS[$FIELD_NAME];
		if(!$bVarsFromForm)
			$form_value = $arUserField["VALUE"];
		elseif($arUserField["USER_TYPE"]["BASE_TYPE"]=="file")
			$form_value = $GLOBALS[$arUserField["FIELD_NAME"]."_old_id"];

		$hidden = "";
		if(is_array($form_value))
		{
			foreach($form_value as $value)
				$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'[]" value="'.htmlspecialchars($value).'">';
		}
		else
		{
			$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'" value="'.htmlspecialchars($form_value).'">';
		}
		$tabControl->EndCustomField($FIELD_NAME, $hidden);
	}
}
?>
<?
if(strlen($return_url)>0)
	$bu = $return_url;
else
	$bu = "/bitrix/admin/".CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section)));

$tabControl->Buttons(array("disabled"=>false, "back_url"=>$bu));

$tabControl->Show();

$tabControl->ShowWarnings($tabControl->GetName(), $message);

if($BlockPerm >= "X" && (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1))
{
	echo
		BeginNote(),
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_section_edit.php?ID=".$ID."&lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"")).'">',
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
