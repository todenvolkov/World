<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
CModule::IncludeModule('support');

IncludeModuleLangFile(__FILE__);

$bDemo = CTicket::IsDemo();
$bAdmin = CTicket::IsAdmin();

if(!$bAdmin && !$bDemo)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$EDIT_URL = '/bitrix/admin/ticket_usergroup_edit.php';
	
$sTableID = 't_groupusers_list';
$oSort = new CAdminSorting($sTableID, 'LOGIN', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID.'_filter_id', 
	array(
		GetMessage('SUP_UGL_FLT_USER'),
		GetMessage('SUP_UGL_FLT_LOGIN'),
		GetMessage('SUP_UGL_FLT_CAN_VIEW_GROUP_MESSAGES'),
		GetMessage('SUP_UGL_FLT_IS_TEAM_GROUP'),
	)
);

$arFilterFields = Array(
	'FIND_GROUP_ID',
	'FIND_USER_ID',
	'FIND_LOGIN',
	'FIND_LOGIN_EXACT_MATCH',
	'FIND_CAN_VIEW_GROUP_MESSAGES',
	'FIND_IS_TEAM_GROUP',
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
foreach($arFilterFields as $key)
{
	if (strpos($key, '_EXACT_MATCH') !== false) continue;
	
	if (array_key_exists($key . '_EXACT_MATCH', $_REQUEST) && $_REQUEST[$key . '_EXACT_MATCH'] == 'Y')
	{
		$op = '=';
	}
	else 
	{
		$op = '%';
	}
	
	if (array_key_exists($key, $_REQUEST) && strlen($_REQUEST[$key]) > 0)
	{
		if (in_array($key . '_EXACT_MATCH', $arFilterFields))
		{
			$arFilter[$op . substr($key, 5)] = $_REQUEST[$key];
		}
		else 
		{
			$arFilter[substr($key, 5)] = $_REQUEST[$key];
		}
	}
}
//file_put_contents(__FILE__.'.php', var_export($arFilter, true));

if ($lAdmin->EditAction()) //если идет сохранение со списка
{
	foreach($FIELDS as $ID => $arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		list($tGROUP_ID, $tUSER_ID) = explode('_', $ID);
		
		if (!CSupportUserGroup::UpdateUserGroup($tGROUP_ID, $tUSER_ID, $arFields))
		{
			$ex = $APPLICATION->GetException();
			$lAdmin->AddUpdateError($ex->GetString(), $ID);
		}
	}
}


if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CSupportUser2UserGroup::GetList(array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		
		list($tGROUP_ID, $tUSER_ID) = explode('_', $ID);

		switch($_REQUEST['action'])
		{
			case 'delete':
				@set_time_limit(0);
				CSupportUser2UserGroup::Delete($tGROUP_ID, $tUSER_ID);
			break;
		}
	}
}


$rsData = CSupportUser2UserGroup::GetList(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);

$lAdmin->NavText($rsData->GetNavPrint(GetMessage('SUP_UGL_PAGES')));

$arHeaders = Array();
$arHeaders[] = Array('id'=>'GROUP_ID', 'content'=>GetMessage('SUP_UGL_GROUP_ID'), 'default'=>true, 'sort' => 'GROUP_ID');
$arHeaders[] = Array('id'=>'USER_ID', 'content'=>GetMessage('SUP_UGL_USER_ID'), 'default'=>true, 'sort' => 'USER_ID');
$arHeaders[] = Array('id'=>'CAN_VIEW_GROUP_MESSAGES', 'content'=>GetMessage('SUP_UGL_CAN_VIEW_GROUP_MESSAGES'), 'default'=>true, 'sort' => 'CAN_VIEW_GROUP_MESSAGES');
$arHeaders[] = Array('id'=>'GROUP_NAME', 'content'=>GetMessage('SUP_UGL_GROUP_NAME'), 'default'=>true, 'sort' => 'GROUP_NAME');
$arHeaders[] = Array('id'=>'LOGIN', 'content'=>GetMessage('SUP_UGL_LOGIN'), 'default'=>true, 'sort' => 'LOGIN');
$arHeaders[] = Array('id'=>'FIRST_NAME', 'content'=>GetMessage('SUP_UGL_FIRST_NAME'), 'default'=>false, 'sort' => 'FIRST_NAME');
$arHeaders[] = Array('id'=>'LAST_NAME', 'content'=>GetMessage('SUP_UGL_LAST_NAME'), 'default'=>false, 'sort' => 'LAST_NAME');
$arHeaders[] = Array('id'=>'IS_TEAM_GROUP', 'content'=>GetMessage('SUP_UGL_IS_TEAM_GROUP'), 'default'=>false, 'sort' => 'IS_TEAM_GROUP');

$lAdmin->AddHeaders($arHeaders);

while ($arUserGroup = $rsData->GetNext())
{
	$ROW_ID = $arUserGroup['GROUP_ID'] . '_' . $arUserGroup['USER_ID'];
	$row =& $lAdmin->AddRow($ROW_ID, $arUserGroup);
	
	$row->AddCheckField('CAN_VIEW_GROUP_MESSAGES');
	$row->AddCheckField('IS_TEAM_GROUP', false);
	
	$arActions = Array();
	
	$arActions[] = array(
		'ICON'=>'edit',
		'DEFAULT' => 'Y',
		'TEXT'=>GetMessage('SUP_UGL_EDIT'),
		'ACTION'=>$lAdmin->ActionRedirect($EDIT_URL.'?lang='.LANGUAGE_ID.'&USER_ID='.$arUserGroup['USER_ID'].'&GROUP_ID='.$arUserGroup['GROUP_ID'].'&back_url='.urlencode($APPLICATION->GetCurPageParam('', array('mode', 'table_id', 'ID', 'action'))))
	);
	
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array(
		'ICON' => 'delete',
		'TEXT'	=> GetMessage('SUP_UGL_DELETE'),
		'ACTION'=>'if(confirm(\''.GetMessage('SUP_UGL_DELETE_CONFIRMATION').'\')) '.$lAdmin->ActionDoGroup($ROW_ID, 'delete'),
	);
	
	
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array('title'=>GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value'=>$rsData->SelectedRowsCount()),
		array('counter'=>true, 'title'=>GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value'=>'0'),
	)
);

$lAdmin->AddGroupActionTable(Array(
	'delete'=>GetMessage('MAIN_ADMIN_LIST_DELETE'),
	)
);

$aContext = array(
	array(
		'ICON'=> 'btn_new',
		'TEXT'=> GetMessage('SUP_UGL_ADD'),
		'LINK'=>$EDIT_URL.'?lang='.LANG.(array_key_exists('GROUP_ID', $arFilter) ? '&GROUP_ID='.$arFilter['GROUP_ID']:'').'&back_url='.urlencode($APPLICATION->GetCurPageParam('', array('mode', 'table_id'))),
		'TITLE'=>GetMessage('SUP_UGL_ADD_TITLE')
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('SUP_UGL_TITLE'));
	
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?><form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?"><?
$filter->Begin();
$FUI_VALUE = array_key_exists('FIND_USER_ID', $_REQUEST) && intval($_REQUEST['FIND_USER_ID']) > 0 ? intval($_REQUEST['FIND_USER_ID']) : '';
?>
<tr>
	<td><?=GetMessage('SUP_UGL_FLT_GROUP')?></td>
	<td><select name="FIND_GROUP_ID"><option value=""><?=GetMessage('MAIN_ALL')?></option>
	<?
	$rs = CSupportUserGroup::GetList(array('NAME' => 'ASC'));
	while ($arr = $rs->GetNext())
	{
		?><option value="<?=$arr['ID']?>"<?if (array_key_exists('FIND_GROUP_ID', $_REQUEST) && $arr['ID'] == $_REQUEST['FIND_GROUP_ID']){?> selected<?}?>><?=$arr['NAME']?> [<?=$arr['ID']?>]</option><?
	}
	?>
	</select></td>
</tr>
<tr>
	<td><?=GetMessage('SUP_UGL_FLT_USER')?></td>
	<td><?=FindUserID('FIND_USER_ID', $FUI_VALUE, '', "form1")?></td>
</tr>
<tr>
	<td><?=GetMessage('SUP_UGL_FLT_LOGIN')?></td>
	<td><input type="text" name="FIND_LOGIN" size="47" value="<?=htmlspecialchars($_REQUEST['FIND_LOGIN'])?>"><?=InputType("checkbox", "FIND_LOGIN_EXACT_MATCH", "Y", $_REQUEST['FIND_LOGIN_EXACT_MATCH'], false, "", "title='".GetMessage('SUP_UGL_EXACT_MATCH')."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage('SUP_UGL_FLT_CAN_VIEW_GROUP_MESSAGES')?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("FIND_CAN_VIEW_GROUP_MESSAGES", $arr, htmlspecialchars($FIND_CAN_VIEW_GROUP_MESSAGES), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?=GetMessage('SUP_UGL_FLT_IS_TEAM_GROUP')?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("FIND_IS_TEAM_GROUP", $arr, htmlspecialchars($FIND_IS_TEAM_GROUP), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?></form><?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>