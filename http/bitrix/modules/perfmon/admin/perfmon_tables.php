<?
define("ADMIN_MODULE_NAME", "perfmon");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");
IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if($RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arEngines = array(
	"MYISAM" => array("NAME" => "MyISAM"),
	"INNODB" => array("NAME" => "InnoDB"),
);

$sTableID = "t_perfmon_all_tables";

$lAdmin = new CAdminList($sTableID);

if(($arTABLES = $lAdmin->GroupAction()) && $RIGHT>="W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CPerfomanceTableList::GetList();
		while($ar = $rsData->Fetch())
			$arTABLES[] = $ar["TABLE_NAME"];
	}

	foreach($arEngines as $id => $ar)
	{
		if($_REQUEST['action'] == "convert_to_".$id)
		{
			$_REQUEST["action"] = "convert";
			$_REQUEST["to"] = $id;
			break;
		}
	}

	$to = strtoupper($_REQUEST["to"]);

	foreach($arTABLES as $table_name)
	{
		if(strlen($table_name) <= 0 || !preg_match("/^[a-z0-9_]+\$/i", $table_name))
			continue;

		switch($_REQUEST['action'])
		{
		case "convert":
			$res = $DB->Query("show table status like '".$table_name."'", false);
			$arStatus = $res->Fetch();
			if($arStatus)
			{
				if($to != strtoupper($arStatus["Engine"]))
				{
					if($to == "MYISAM")
						$res = $DB->Query("alter table ".$table_name." type MyISAM", false);
					elseif($to == "INNODB")
						$res = $DB->Query("alter table ".$table_name." type InnoDB", false);
					else
						$res = true;

				}
				if(!$res)
				{
					$lAdmin->AddGroupError(GetMessage("PERFMON_TABLES_CONVERT_ERROR"), $table_name);
				}
			}
			break;
		}
	}
}

$lAdmin->BeginPrologContent();
echo "<h4>".GetMessage("PERFMON_TABLES_ALL")."</h4>\n";
$lAdmin->EndPrologContent();

$arHeaders = array();
$arHeaders[] = array(
	"id" => "TABLE_NAME",
	"content" => GetMessage("PERFMON_TABLES_NAME"),
	"default" => true,
);

if($DB->type == "MYSQL")
{
	$arHeaders[] = array(
		"id" => "ENGINE_TYPE",
		"content" => GetMessage("PERFMON_TABLES_ENGINE_TYPE"),
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeaders);

$arAllTables = array();
$rsData = CPerfomanceTableList::GetList();
while($ar = $rsData->Fetch())
	$arAllTables[] = $ar;

$rsData = new CDBResult;
$rsData->InitFromArray($arAllTables);
$rsData = new CAdminResult($rsData, $sTableID);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_TABLE_NAME, $arRes);
	$row->AddViewField("TABLE_NAME", '<a href="perfmon_table.php?lang='.LANGUAGE_ID.'&amp;table_name='.urlencode($f_TABLE_NAME).'">'.$f_TABLE_NAME.'</a>');

	$arActions = array();
	if($DB->type == "MYSQL")
	{
		foreach($arEngines as $id => $ar)
		{
			if(strtoupper($f_ENGINE_TYPE) != $id)
				$arActions[] = array(
					"ICON" => "edit",
					"DEFAULT" => false,
					"TEXT" => GetMessage("PERFMON_TABLES_ACTION_CONVERT", array("#ENGINE_TYPE#" => $ar["NAME"])),
					"ACTION" => $lAdmin->ActionDoGroup($f_TABLE_NAME, "convert", "to=".$id),
				);
		}
	}
	if(count($arActions))
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($DB->type == "MYSQL")
{
	$arGroupActions = array();
	foreach($arEngines as $id => $ar)
		$arGroupActions["convert_to_".$id] = GetMessage("PERFMON_TABLES_ACTION_CONVERT", array("#ENGINE_TYPE#" => $ar["NAME"]));

	$lAdmin->AddGroupActionTable($arGroupActions);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_TABLES_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$strLastTables = CUserOptions::GetOption("perfmon", "last_tables");
if(strlen($strLastTables) > 0)
{
	$arLastTables = explode(",", $strLastTables);
	if(count($arLastTables) > 0)
	{
		sort($arLastTables);

		foreach($arLastTables as $i => $table_name)
			$arLastTables[$i] = array("NAME" => '<a href="perfmon_table.php?lang='.LANGUAGE_ID.'&amp;table_name='.urlencode($table_name).'">'.$table_name.'</a>');

		$sTableID = "t_perfmon_recent_tables";

		$lAdmin2 = new CAdminList($sTableID);

		$lAdmin2->BeginPrologContent();
		echo "<h4>".GetMessage("PERFMON_TABLES_RECENTLY_BROWSED")."</h4>\n";
		$lAdmin2->EndPrologContent();

		$lAdmin2->AddHeaders(array(
			array(
				"id" => "NAME",
				"content" => GetMessage("PERFMON_TABLES_NAME"),
				"default" => true,
			),
		));

		$rsData = new CDBResult;
		$rsData->InitFromArray($arLastTables);
		$rsData = new CAdminResult($rsData, $sTableID);

		$j = 0;
		while($arRes = $rsData->NavNext(true, "f_"))
		{
			$row =& $lAdmin2->AddRow($j++, $arRes);
			foreach($arRes as $key => $value)
				$row->AddViewField($key, $value);
		}

		$lAdmin2->CheckListMode();
		$lAdmin2->DisplayList();

	}
}

$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
