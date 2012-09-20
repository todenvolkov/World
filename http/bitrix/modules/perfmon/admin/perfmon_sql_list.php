<?
define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if($RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_perfmon_sql_list";
$oSort = new CAdminSorting($sTableID, "NN", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_hit_id",
	"find_component_id",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"=HIT_ID" => ($find!="" && $find_type == "hit_id"? $find: $find_hit_id),
	"=COMPONENT_ID" => ($find!="" && $find_type == "component_id"? $find: $find_component_id),
);
foreach($arFilter as $key=>$value)
	if(!$value)
		unset($arFilter[$key]);

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => GetMessage("PERFMON_SQL_ID"),
		"sort" => "ID",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "HIT_ID",
		"content" => GetMessage("PERFMON_SQL_HIT_ID"),
		"sort" => "HIT_ID",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "NN",
		"content" => GetMessage("PERFMON_SQL_NN"),
		"sort" => "NN",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "QUERY_TIME",
		"content" => GetMessage("PERFMON_SQL_QUERY_TIME"),
		"sort" => "QUERY_TIME",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "MODULE_NAME",
		"content" => GetMessage("PERFMON_SQL_MODULE_NAME"),
		"sort" => "MODULE_NAME",
	),
	array(
		"id" => "COMPONENT_NAME",
		"content" => GetMessage("PERFMON_SQL_COMPONENT_NAME"),
		"sort" => "COMPONENT_NAME",
	),
	array(
		"id" => "SQL_TEXT",
		"content" => GetMessage("PERFMON_SQL_SQL_TEXT"),
		//"sort" => "SQL_TEXT",
		"default" => true,
	),
));

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if(!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
	$arSelectedFields = array(
		"ID",
		"HIT_ID",
		"NN",
		"QUERY_TIME",
		"SQL_TEXT",
	);

$cData = new CPerfomanceSQL;
$rsData = $cData->GetList($arSelectedFields, $arFilter, array($by => $order), false);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_SQL_PAGE")));

while($arRes = $rsData->NavNext(true, "f_")):
	$arRes["SQL_TEXT"] = CPerfomanceSQL::Format($arRes["SQL_TEXT"]);
	$row =& $lAdmin->AddRow($f_NAME, $arRes);

	if($_REQUEST["mode"] == "excel")
		$row->AddViewField("QUERY_TIME", number_format($f_QUERY_TIME, 6, ".", ""));
	else
		$row->AddViewField("QUERY_TIME", str_replace(" ", "&nbsp;", number_format($f_QUERY_TIME, 6, ".", " ")));

	if(class_exists("geshi") && $f_SQL_TEXT)
	{
		$obGeSHi = new GeSHi($arRes["SQL_TEXT"], 'sql');
		$row->AddViewField("SQL_TEXT", $obGeSHi->parse_code());
	}
	else
	{
		$row->AddViewField("SQL_TEXT", str_replace(
			array(" ", "\n"),
			array(" &nbsp;", "<br>"),
			htmlspecialchars($arRes["SQL_TEXT"])
		));
	}
	$arActions = Array();
	if($DBType == "mysql" || $DBType == "oracle")
	{
		$arActions[] = array(
			//"ICON"=>"delete",
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("PERFMON_SQL_EXPLAIN"),
			"ACTION" => 'jsUtils.OpenWindow(\'perfmon_explain.php?lang='.LANG.'&ID='.$f_ID.'\', 600, 500);',
		);
	}
	if(count($arActions))
		$row->AddActions($arActions);
endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$aContext = array(
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_SQL_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_hit_id" => GetMessage("PERFMON_SQL_HIT_ID"),
		"find_component_id" => GetMessage("PERFMON_SQL_COMPONENT_ID"),
	)
);
?>

<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("PERFMON_SQL_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>" title="<?=GetMessage("PERFMON_SQL_FIND")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("PERFMON_SQL_HIT_ID"),
				GetMessage("PERFMON_SQL_COMPONENT_ID"),
			),
			"reference_id" => array(
				"hit_id",
				"component_id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("PERFMON_SQL_HIT_ID")?></td>
	<td><input type="text" name="find_hit_id" size="47" value="<?echo htmlspecialchars($find_hit_id)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("PERFMON_SQL_COMPONENT_ID")?></td>
	<td><input type="text" name="find_component_id" size="47" value="<?echo htmlspecialchars($find_component_id)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
