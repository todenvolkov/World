<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	//get saved columns and sorting from user settings
	$aOptions = CUserOptions::GetOption("main.interface.grid", $_REQUEST["GRID_ID"], array());
	
	if(!is_array($aOptions["views"]))
		$aOptions["views"] = array();
	if(!is_array($aOptions["filters"]))
		$aOptions["filters"] = array();
	if(!array_key_exists("default", $aOptions["views"]))
		$aOptions["views"]["default"] = array("columns"=>"");
	if($aOptions["current_view"] == '' || !array_key_exists($aOptions["current_view"], $aOptions["views"]))
		$aOptions["current_view"] = "default";
	
	if($_REQUEST["action"] == "showcolumns")
	{
		$aColsTmp = explode(",", $_REQUEST["columns"]);
		$aCols = array();
		foreach($aColsTmp as $col)
			if(($col = trim($col)) <> "")
				$aCols[] = $col;
		$aOptions["views"][$aOptions["current_view"]]["columns"] = implode(",", $aCols);
	}
	elseif($_REQUEST["action"] == "settheme")
	{
		$aOptions["theme"] = $_REQUEST["theme"];
	}
	elseif($_REQUEST["action"] == "savesettings")
	{
		CUtil::decodeURIComponent($_POST);
		$aOptions["views"][$_POST['view_id']] = array(
			"name"=>$_POST["name"],
			"columns"=>$_POST["columns"],
			"sort_by"=>$_POST["sort_by"],
			"sort_order"=>$_POST["sort_order"],
			"page_size"=>$_POST["page_size"],
			"saved_filter"=>$_POST["saved_filter"],
		);
	}
	elseif($_REQUEST["action"] == "delview")
	{
		unset($aOptions["views"][$_REQUEST['view_id']]);
	}
	elseif($_REQUEST["action"] == "setview")
	{
		if(!array_key_exists($_REQUEST["view_id"], $aOptions["views"]))
			$_REQUEST["view_id"] = "default";
		$aOptions["current_view"] = $_REQUEST["view_id"];
	}
	elseif($_REQUEST["action"] == "filterrows")
	{
		$aColsTmp = explode(",", $_REQUEST["rows"]);
		$aCols = array();
		foreach($aColsTmp as $col)
			if(($col = trim($col)) <> "")
				$aCols[] = $col;
		$aOptions["filter_rows"] = implode(",", $aCols);
	}
	elseif($_REQUEST["action"] == "savefilter")
	{
		CUtil::decodeURIComponent($_POST);
		$aOptions["filters"][$_POST['filter_id']] = array(
			"name"=>$_POST["name"],
			"fields"=>$_POST['fields'],
		);
	}
	elseif($_REQUEST["action"] == "delfilter")
	{
		unset($aOptions["filters"][$_REQUEST['filter_id']]);
	}
	elseif($_REQUEST["action"] == "filterswitch")
	{
		$aOptions["filter_shown"] = ($_REQUEST["show"] == "Y"? "Y":"N");
	}

	CUserOptions::SetOption("main.interface.grid", $_REQUEST["GRID_ID"], $aOptions);
}
echo "OK";
?>