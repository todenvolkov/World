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

$sTableID = "tbl_autodetect_list";

$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

// action handlers
if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT=="W" && check_bitrix_sessid())
{
	$added_browsers = 0;
	$added_searchers = 0;
	$total_records = intval($total_records);

        if($_REQUEST['action_target'] == "selected")
        {
                $arID = Array();
                while(list($key)=each($mask))
                        $arID[] = $key;
        }

        foreach($arID as $ID)
        {
                if(strlen($ID)<=0)
                        continue;

		$sqlmask = $DB->ForSql($mask[$ID],255);
		$strSql = "
			SELECT
				count(ID) CNT
			FROM
				b_stat_browser
			WHERE
				upper('$sqlmask') like upper(USER_AGENT)
			and USER_AGENT is not null
			and ".$DB->Length("USER_AGENT").">0
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		if (intval($zr["CNT"])<=0)
		{
			$strSql = "
				SELECT
					count(ID) CNT
				FROM
					b_stat_searcher
				WHERE
					upper('$sqlmask') like ".$DB->Concat("'%'",USER_AGENT,"'%'")."
				and USER_AGENT is not null
				and ".$DB->Length("USER_AGENT").">0
				";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			$ar = $rs->Fetch();
			if (intval($ar["CNT"])<=0)
			{
				if ($s_type[$ID]=="b")
				{
					$mask[$ID] = TrimEx($mask[$ID],"%");
					$arFields = Array("USER_AGENT" => "'%".$DB->ForSql($mask[$ID],255)."%'");
					$DB->Insert("b_stat_browser",$arFields, $err_mess.__LINE__);
					$added_browsers++;
				}
				elseif ($s_type[$ID]=="s")
				{
					$arFields = array(
						"ACTIVE"			=> "'Y'",
						"SAVE_STATISTIC"	=> "'Y'",
						"NAME"				=> "'".$DB->ForSql($mask[$ID],255)."'",
						"USER_AGENT"		=> "'".$DB->ForSql($mask[$ID],255)."'");
					$DB->Insert("b_stat_searcher",$arFields, $err_mess.__LINE__);
					$added_searchers++;
				}
			}
		}
	}
	$lAdmin->BeginPrologContent();
	ShowNote(GetMessage("STAT_ADDED_SEARCHERS")." ".$added_searchers."<br>". GetMessage("STAT_ADDED_BROWSERS")." ".$added_browsers);
	$lAdmin->EndPrologContent();
}

$arrExactMatch = array(
	"USER_AGENT_EXACT_MATCH"	=> "find_user_agent_exact_match"
	);
$FilterArr = Array(
	"find_last",
	"find_user_agent",
	"find_counter1",
	"find_counter2");
$arFilterFields = array_merge($FilterArr, array_values($arrExactMatch));

$lAdmin->InitFilter($arFilterFields);

$arFilter = Array(
	"LAST"			=> $find_last,
	"USER_AGENT"		=> $find_user_agent,
	"COUNTER1"		=> $find_counter1,
	"COUNTER2"		=> $find_counter2
	);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));
$first = getmicrotime();
$etime = round((getmicrotime()-$first),5);

$rsData = CAutoDetect::GetList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_USER_AGENT_PAGES")));

$lAdmin->AddHeaders(array(
	array("id"=>"USER_AGENT", "content"=>GetMessage("STAT_USER_AGENT"), "sort"=>"", "default"=>true),
	array("id"=>"COUNTER", "content"=>GetMessage("STAT_SESSIONS"), "sort"=>"", "default"=>true, "align"=>"right"),
	array("id"=>"FAKE_MASK", "content"=>GetMessage("STAT_MASK"), "sort"=>"", "default"=>true),
	array("id"=>"FAKE_SRCH_S", "content"=>GetMessage("STAT_SEARCHER"), "align"=>"center", "default"=>true),
	array("id"=>"FAKE_SRCH_B", "content"=>GetMessage("STAT_BROWSER"), "align"=>"center", "default"=>true),
	)
);

$i=0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$f_ID = $i++;
        $row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("COUNTER","<a title=\"".GetMessage("STAT_SESS_LIST")."\" href=\"/bitrix/admin/session_list.php?lang=".LANGUAGE_ID."&find_user_agent=".urlencode("\"".str_replace(array("\\", "\'", "\""), "_", $f_USER_AGENT)."\"")."&set_filter=Y\">$f_COUNTER</a>");

	$txt="";
	$mask = preg_replace("/[0-9\\\'\"]/", "_", $f_USER_AGENT);
	if (!is_array($arrMask) || !in_array($mask, $arrMask))
	{
		if (strlen($mask)>3) $txt=$mask;
		$arrMask[] = $mask;
	}
	$row->AddViewField("FAKE_MASK", "<input type=\"text\" name=\"mask[$f_ID]\" value=\"$txt\" style=\"width:100%\"> ");

	$row->AddViewField("FAKE_SRCH_S", "<input type=\"radio\" name=\"s_type[$f_ID]\" value=\"s\" checked> ");
	$row->AddViewField("FAKE_SRCH_B", "<input type=\"radio\" name=\"s_type[$f_ID]\" value=\"b\"> ");
}

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);

$lAdmin->AddGroupActionTable(
	array(
		array(
		"value" =>"add",
		"name"=>GetMessage("STAT_ADD"),
		"type"=>"button",
		)
	),
	array("disable_action_target"=>true)
);

$lAdmin->CheckListMode();

############
$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
				HTML form
****************************************************************************/
?>
<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
        $sTableID."_filter",
        array(
		GetMessage("STAT_FL_DAY"),
		GetMessage("STAT_FL_SESS"),
        )
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("STAT_F_USER_AGENT")?></b></td>
	<td><input type="text" name="find_user_agent" size="28" value="<?echo htmlspecialchars($find_user_agent)?>"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_LAST_DAY")?></td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_last", $arr, htmlspecialchars($find_last), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_COUNTER")?></td>
	<td>
		<input type="text" name="find_counter1" size="10" value="<?echo htmlspecialchars($find_counter1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_counter2" size="10" value="<?echo htmlspecialchars($find_counter2)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
#############################################################
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
