<? 
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
InitSorting();
$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
						  Функции
****************************************************************************/

function Support_GetUserInfo($USER_ID, &$login, &$name)
{
	static $arrUsers;
	$login = "";
	$name = "";
	if (intval($USER_ID)>0)
	{
		if (is_array($arrUsers) && in_array($USER_ID, array_keys($arrUsers)))
		{
			$login = $arrUsers[$USER_ID]["LOGIN"];
			$name = $arrUsers[$USER_ID]["NAME"];
		}
		else
		{
			$rsUser = CUser::GetByID($USER_ID);
			$arUser = $rsUser->Fetch();
			$login = htmlspecialchars($arUser["LOGIN"]);
			$name = htmlspecialchars($arUser["NAME"]." ".$arUser["LAST_NAME"]);
			$arrUsers[$USER_ID] = array("LOGIN" => $login, "NAME" => $name);
		}
	}
}

/***************************************************************************
						   Обработка GET | POST
****************************************************************************/
if (
	!isset($find_type) ||
	strlen($find_type) < 0 ||
	!in_array($find_type, Array("C","K","S","M","F","SR", "D"))
	)
	$find_type = "C";


$sTableID = "t_dict_list_" . strtolower($find_type);
$oSort = new CAdminSorting($sTableID, "SORT", "asc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка


$oFilter = new CAdminFilter(
	$sTableID."_filter_id", 
	array(
		GetMessage("SUP_F_ID"),
		GetMessage("SUP_F_SITE"),
		//GetMessage("SUP_F_TYPE"),
		GetMessage("SUP_F_NAME"),
		GetMessage("SUP_F_DESCR"),
		GetMessage("SUP_F_SID"),
		GetMessage("SUP_F_RESPONSIBLE"),
		GetMessage("SUP_F_DEFAULT"),
	)
);


$arFilterFields = Array(
	"find",
	"find_type_ex",

	"find_id",
	"find_id_exact_match",
	"find_site", 
	//"find_type",
	"find_name",
	"find_name_exact_match",
	"find_descr",
	"find_descr_exact_match",
	"find_sid",
	"find_sid_exact_match",
	"find_responsible",
	"find_responsible_exact_match",
	"find_responsible_id",
	"find_default"
	);

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра


InitBVar($find_id_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_sid_exact_match);
InitBVar($find_responsible_exact_match);



$arFilter = Array(
	"ID"						=> $find_id,
	"ID_EXACT_MATCH"			=> $find_id_exact_match,
	"SITE"						=> $find_site,	
	"TYPE"						=> $find_type,
	"NAME"						=> ($find!="" && $find_type_ex == "name"? $find: $find_name),
	"NAME_EXACT_MATCH"			=> $find_name_exact_match,
	"DESCR"						=> ($find!="" && $find_type_ex == "descr"? $find: $find_descr),
	"DESCR_EXACT_MATCH"			=> $find_descr_exact_match,
	"SID"						=> $find_sid,
	"SID_EXACT_MATCH"			=> $find_sid_exact_match,
	"RESPONSIBLE_ID"			=> $find_responsible_id,
	"RESPONSIBLE"				=> $find_responsible,
	"RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match,
	"DEFAULT"					=> $find_default
	);



if ($bAdmin=="Y" && $lAdmin->EditAction()) //если идет сохранение со списка
{

	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$arFields["C_SORT"] = intval($arFields["C_SORT"]);

		if (strlen(trim($arFields["NAME"]))>0)
		{
			$arUpdate = array(
					'C_SORT' => $arFields['C_SORT'],
					'NAME' => $arFields['NAME']
					);

			if (!CTicketDictionary::Update($ID, $arUpdate))
			{
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SUP_ERROR_SAVE")), $ID);

			}
		}
		else
		{
			$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SUP_FORGOT_NAME")), $ID);
		}
	}
}


if($bAdmin=="Y" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CTicketDictionary::GetList($by, $order, $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				CTicketDictionary::Delete($ID);
			break;
		}
	}
}
// если была нажата кнопка "Сохранить изменения"

if ($find_type=="C" || 
	$find_type=="K" || 
	$find_type=="SR" || 
	$find_type=="NOT_REF" || 
	strlen($find_type)<=0) $show_responsible_column = "Y";


$rsData = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SUP_PAGES")));

//$tdic = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
$APPLICATION->SetTitle(GetMessage("SUP_TICKETS_DIC_TITLE"));



$arHeaders = Array();
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "s_id");
$arHeaders[] = Array("id"=>"C_SORT", "content"=>GetMessage("SUP_SORT"), "default"=>true, "sort" => "s_c_sort");
$arHeaders[] =  Array("id"=>"C_SITE", "content"=>GetMessage("SUP_SITE"), "default"=>true,);
$arHeaders[] =  Array("id"=>"NAME", "content"=>GetMessage("SUP_NAME"), "default"=>true,"sort" => "s_name");
if ($show_responsible_column=="Y")
	$arHeaders[] =  Array("id"=>"RESPONSIBLE_USER_ID", "content"=>GetMessage("SUP_RESPONSIBLE"), "default"=>true,"sort" => "s_responsible");

$lAdmin->AddHeaders($arHeaders);

// построение списка
while($arRes = $rsData->NavNext(true, "f_"))
{

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	//$row->AddViewField("ID", $f_ID);
	$row->AddInputField("C_SORT", Array("size"=>"3"));

	$arrSITE =  CTicketDictionary::GetSiteArray($f_ID);reset($arrSITE);
	$str_SITE = "";
	if (is_array($arrSITE))
		foreach($arrSITE as $sid)
			$str_SITE .= ($str_SITE == "" ? "" : " / ").'<a title="'.GetMessage("MAIN_ADMIN_MENU_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.$sid.'&lang='.LANG.'">'.$sid.'</a>';

	$row->AddViewField("C_SITE", $str_SITE);

	$row->AddInputField("NAME", Array("size"=>"35"));
	//$row->AddViewField("NAME", TxtToHTML(htmlspecialcharsback($f_NAME)));


	$str = "&nbsp;";
	if (intval($f_RESPONSIBLE_USER_ID)>0):

	if (strlen($f_RESPONSIBLE_LOGIN)>0)
	{
		$str = '[<a title="'.GetMessage("SUP_USER_PROFILE").'" href="/bitrix/admin/user_edit.php?lang='.LANG.'&ID='.$f_RESPONSIBLE_USER_ID.'">'.$f_RESPONSIBLE_USER_ID.'</a>] ('.$f_RESPONSIBLE_LOGIN.') '.$f_RESPONSIBLE_NAME;
	}

	else
	{
		Support_GetUserInfo($f_RESPONSIBLE_USER_ID, $RESPONSIBLE_LOGIN, $RESPONSIBLE_NAME);

		$str = '[<a title="'.GetMessage("SUP_USER_PROFILE").'" href="/bitrix/admin/user_edit.php?lang='.LANG.'&ID='.$f_RESPONSIBLE_USER_ID.'">'.$f_RESPONSIBLE_USER_ID.'</a>] ('.$RESPONSIBLE_LOGIN.') '.$RESPONSIBLE_NAME;
	}
	endif;

	$row->AddViewField("RESPONSIBLE_USER_ID", $str);

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("SUP_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("ticket_dict_edit.php?find_type=".$f_C_TYPE."&ID=".$f_ID."&lang=".LANG)
	);

	if ($bAdmin=="Y")
	{
		$arActions[] = array("SEPARATOR" => true);

		$arActions[] = array(
			"ICON" => "delete",
			"TEXT"	=> GetMessage("SUP_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage('SUP_DELETE_TDIC_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "find_type=".$f_C_TYPE),
		);
	}

	$row->AddActions($arActions);

}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($bAdmin=="Y")
{
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

$aContext = array(
	array(
		"ICON"=> "btn_new",
		"TEXT"=> GetMessage("SUP_ADD"),
		//"LINK"=>"ticket_dict_edit.php?lang=".LANG."&find_type=".htmlspecialchars($find_type),
		"TITLE"=>GetMessage("SUP_ADD"),
		"MENU" => Array(
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_CATEGORY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=C';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_CRITICALITY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=K';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_STATUS"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=S';"
			),
			Array(
				"TEXT"	=> GetMessage("SUP_ADD_MARK"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=M';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_FUA"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=F';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_SOURCE"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=SR';"
			),

			Array(
				"TEXT"	=> GetMessage("SUP_ADD_DIFFICULTY"),
				"ACTION"	=> "window.location='/bitrix/admin/ticket_dict_edit.php?lang=".LANG."&find_type=D';"
			),

		)
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" id="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$oFilter->Begin();?>

<tr>
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>">
		<select name="find_type_ex">
			<option value="name"<?if($find_type_ex=="name") echo " selected"?>><?=GetMessage("SUP_F_NAME")?></option>
			<option value="descr"<?if($find_type_ex=="descr") echo " selected"?>><?=GetMessage("SUP_F_DESCR")?></option>
		</select>
	</td>
</tr>

<tr>
	<td><?=GetMessage("SUP_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?=htmlspecialchars($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top"> 
	<td valign="top"><?=GetMessage("SUP_F_SITE")?>:<br><img src="/bitrix/images/support/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	$ref = array();
	$ref_id = array();
	$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
	while ($ar = $rs->Fetch()) 
	{
		$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
		$ref_id[] = $ar["ID"];
	}
	echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
	?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?=htmlspecialchars($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_DESCR")?>:</td>
	<td><input type="text" name="find_descr" size="47" value="<?=htmlspecialchars($find_descr)?>"><?=InputType("checkbox", "find_descr_exact_match", "Y", $find_descr_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td><?=GetMessage("SUP_F_SID")?>:</td>
	<td><input type="text" name="find_sid" size="47" value="<?=htmlspecialchars($find_sid)?>"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr> 
	<td nowrap valign="top"><?=GetMessage("SUP_F_RESPONSIBLE")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicket::GetSupportTeamList();
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_responsible_id", $arr, htmlspecialchars($find_responsible_id), GetMessage("SUP_ALL"));
		?><br><input type="text" name="find_responsible" size="47" value="<?=htmlspecialchars($find_responsible)?>"><?=InputType("checkbox", "find_responsible_exact_match", "Y", $find_responsible_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("SUP_F_DEFAULT")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("SUP_YES"), GetMessage("SUP_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_default", $arr, htmlspecialchars($find_default), GetMessage("SUP_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?find_type=".$find_type, "form"=>"form1"));
$oFilter->End();
?>
</form>


<?$lAdmin->DisplayList();?>

<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>