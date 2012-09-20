<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');
IncludeModuleLangFile(__FILE__);

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
$USER_ID = array_key_exists('USER_ID', $_REQUEST) ? intval($_REQUEST['USER_ID']) : 0;
$GROUP_ID = array_key_exists('GROUP_ID', $_REQUEST) ? intval($_REQUEST['GROUP_ID']) : 0;

if (array_key_exists('back_url', $_REQUEST) && strlen($_REQUEST['back_url']) > 0)
{
	$back_url = $_REQUEST['back_url'];
}
else 
{
	if ($GROUP_ID > 0)
	{
		$back_url = '/bitrix/admin/ticket_usergroup_list.php?lang=' . LANGUAGE_ID . '&GROUP_ID=' . $GROUP_ID;
	}
	else 
	{
		$back_url = '/bitrix/admin/ticket_group_list.php?lang=' . LANGUAGE_ID;
	}
}

if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=='POST' && $bAdmin=='Y' && check_bitrix_sessid())
{
	$bOk = false;
	$new = false;
	if ($USER_ID > 0 && $GROUP_ID > 0 && array_key_exists('edit', $_POST) && $_POST['edit'] == 'Y')
	{
		$bOk = CSupportUserGroup::UpdateUserGroup($GROUP_ID, $USER_ID, array('CAN_VIEW_GROUP_MESSAGES' => $_POST['CAN_VIEW_GROUP_MESSAGES']), array('CAN_MAIL_GROUP_MESSAGES' => $_POST['CAN_MAIL_GROUP_MESSAGES']));
	}
	else 
	{
		$bOk = CSupportUserGroup::AddUserGroup($_POST);
		$new = true;
	}
	
	if ($bOk)
	{
		if (strlen($save)>0) LocalRedirect($back_url);
		elseif ($new) LocalRedirect($APPLICATION->GetCurPage() . '?GROUP_ID='.$GROUP_ID. '&USER_ID='.$USER_ID.'&lang='.LANGUAGE_ID);
	}
	else 
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage('SUP_GE_ERROR'), $e);
	}
}


$FMUTagName = 'USER_IDS';
$FMUFormID = 'form1';

$arGroup = false;
if ($GROUP_ID > 0)
{
	$rsGroup = CSupportUserGroup::GetList(false, array('GROUP_ID' => $GROUP_ID));
	$arGroup = $rsGroup->GetNext();
}

$arGroupUsers = array();

if ($arGroup)
{
	$rs_ug = CSupportUser2UserGroup::GetList(false, array('GROUP_ID' => $GROUP_ID));
	while ($ar_ug = $rs_ug->GetNext())
	{
		$arGroupUsers[] = array(
			'USER_ID' => $ar_ug['USER_ID'],
			'CAN_VIEW_GROUP_MESSAGES' => $ar_ug['CAN_VIEW_GROUP_MESSAGES'],
		    'CAN_MAIL_GROUP_MESSAGES' => $ar_ug['CAN_MAIL_GROUP_MESSAGES'],
			'USER_NAME' => '[<a title="'.GetMessage("MAIN_USER_PROFILE").'" href="user_edit.php?ID='.$ar_ug["USER_ID"].'&amp;lang='.LANG.'">'.$ar_ug["USER_ID"].'</a>] ('.$ar_ug["LOGIN"].') '.$ar_ug["FIRST_NAME"].' '.$ar_ug["LAST_NAME"],
		);
	}
}

$arGroupUsers[] = array('USER_ID' => '');
$arGroupUsers[] = array('USER_ID' => '');
$arGroupUsers[] = array('USER_ID' => '');
	
$APPLICATION->SetTitle(GetMessage('SUP_UGE_TITLE_ADD'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($arGroup):

if ($message)
	echo $message->Show();

$aTabs = array();
$aTabs[] = array(
	'DIV' => 'edit1',
	'TAB' => GetMessage('SUP_UGE_TAB1'),
	//'ICON'=>'ticket_dict_edit',
	'TITLE'=>GetMessage('SUP_UGE_TAB1_TITLE')
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<form name="<?=$FMUFormID?>" method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="back_url" value="<?=htmlspecialchars($back_url)?>">
<input type="hidden" name="GROUP_ID" value="<?=$arGroup['ID']?>">
<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
<tr valign="top"> 
	<td align="right" width="40%"><?=GetMessage('SUP_UGE_GROUP')?>:</td>
	<td width="60%"><?=$arGroup['NAME']?></td>
</tr>
<tr valign="top"> 
	<td align="right"><?=GetMessage('SUP_UGE_USER')?>:</td>
	<td>

		<table id="FMUtab">
		<tr>
			<td><?=GetMessage('SUP_UGE_USER')?></td>
			<td><?=GetMessage('SUP_UGE_USER_CAN_READ_GROUP_MESS')?></td>
			<td><?=GetMessage('SUP_UGE_USER_CAN_MAIL_GROUP_MESS')?></td>
		</tr>
		<?
		$i = 0;
		$UIDS = array();
		foreach ($arGroupUsers as $val)
		{
			$UIDS[$i] = '';
			if (strlen($val['USER_ID']) > 0)
			{
				$UIDS[$i] = intval($val['USER_ID']);
			}
		?>
		<tr>
		<td>
			<input type="text" id="<?=$FMUTagName?>[VALS][<?=$i?>]" name="<?=$FMUTagName?>[VALS][<?=$i?>]" value="<?=$UIDS[$i]?>" size="5">
			<iframe style="width:0px; height:0px; border:0px" src="javascript:''" name="FMUhiddenframe<?=$i?>" id="FMUhiddenframe<?=$i?>"></iframe>
			<input class="" type="button" name="FMUButton<?=$i?>" id="FMUButton<?=$i?>" OnClick="window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=<?=$FMUFormID?>&FC=<?=urlencode($FMUTagName.'[VALS]['.$i.']')?>', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" value="...">
			<span id="div_FMUdivUN<?=$i?>"><?=$val['USER_NAME']?></span>
		</td>
		<td><input type="checkbox" name="<?=$FMUTagName?>[CHECKS][<?=$i?>]"<?if (!empty($val['CAN_VIEW_GROUP_MESSAGES']) || strlen($val['USER_ID']) <= 0){?> checked<?}?> value="Y"></td>
		<td><input type="checkbox" name="<?=$FMUTagName?>[MAIL][<?=$i?>]"<?if (!empty($val['CAN_MAIL_GROUP_MESSAGES']) || strlen($val['USER_ID']) <= 0){?> checked<?}?> value="Y"></td>
		</tr>
		<?
			$i++;
		}
		?>
		
		<tr>
		<td colspan="2"><input type="button" value="<?=GetMessage('SUP_UGE_ADD_MORE_USERS')?>" onclick="window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&JSFUNC=usergroups', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));"></td>
		</tr>
		</table>
		
		<script type="text/javascript">
		
		var rowCounter = <?=intval($i)?>;
		var UIDS = new Array();
		<?foreach ($UIDS as $k => $v){?>
		UIDS[<?=$k?>] = '<?=$v?>';
		<?}?>
		
		function SUVUpdateUserNames()
		{
			var str;
			var div;
			for(i in UIDS)
			{
				//alert(document.<?echo $FMUFormID;?>["<?=$FMUTagName?>[VALS]["+String(i)+"]"].value);
				str = document.<?echo $FMUFormID;?>["<?=$FMUTagName?>[VALS]["+String(i)+"]"].value;
				if(str.length > 0)
				{
					if(String(UIDS[i]) != str)
					{
						div = document.getElementById('div_FMUdivUN'+String(i));
						div.innerHTML = '<i><?=GetMessage('MAIN_WAIT')?></i>';
						document.getElementById("FMUhiddenframe"+String(i)).src='/bitrix/admin/get_user.php?ID=' + str + '&strName=FMUdivUN'+String(i)+'&lang=<?=LANG?><?=(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")?>';
						UIDS[i] = str;
					}
				}
			}
			
			setTimeout(function(){SUVUpdateUserNames()},1000);
		}
		
		SUVUpdateUserNames();
		
		function SUVusergroups(USER_ID)
		{
			var oTbl=document.getElementById('FMUtab');
			
			var sRowCounter = String(rowCounter);
			
			var newRow = oTbl.insertRow(oTbl.rows.length - 1);
			var newCell1 = newRow.insertCell(-1);
			newCell1.innerHTML = '<input type="text" id="<?=$FMUTagName?>[VALS]['+sRowCounter+']" name="<?=$FMUTagName?>[VALS]['+sRowCounter+']" value="'+String(USER_ID)+'" size="5"> ' +
				'<iframe style="width:0px; height:0px; border:0px" src="javascript:\'\'" name="FMUhiddenframe'+sRowCounter+'" id="FMUhiddenframe'+sRowCounter+'"></iframe> ' +
				'<input class="" type="button" name="FMUButton'+sRowCounter+'" id="FMUButton'+sRowCounter+'" OnClick="window.open(\'/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=<?=$FMUFormID?>&FC=<?=urlencode($FMUTagName)?>%5BVALS%5D%5B'+sRowCounter+'%5D\', \'\', \'scrollbars=yes,resizable=yes,width=760,height=500,top=\'+Math.floor((screen.height - 560)/2-14)+\',left=\'+Math.floor((screen.width - 760)/2-5));" value="..."> ' + 
				'<span id="div_FMUdivUN'+sRowCounter+'"></span>';
			
			var newCell2 = newRow.insertCell(-1);
			newCell2.innerHTML = '<input type="checkbox" name="<?=$FMUTagName?>[CHECKS]['+sRowCounter+']" value="Y" checked>';
			var newCell3 = newRow.insertCell(-1);
			newCell3.innerHTML = '<input type="checkbox" name="<?=$FMUTagName?>[MAIL]['+sRowCounter+']" value="Y" checked>';
			
			
			UIDS[rowCounter] = '';
			rowCounter++;
		}
		
		</script>	
	
	</td>
</tr>
<?
$tabControl->Buttons(Array('back_url' => $back_url));
$tabControl->End();
?>
</form>

<?echo BeginNote();?>
<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?
else:

echo GetMessage('SUP_UGE_NO_GROUP');

endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>