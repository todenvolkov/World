<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

global $MAIN_MODULE_EVENTS, $MAIN_MODULE_INCLUDED, $MAIN_MODULE_INCLUDED_EX, $MAIN_MODULE_EVENTS_INIT;
$MAIN_MODULE_INCLUDED = Array();
$MAIN_MODULE_INCLUDED_EX = Array();
$MAIN_MODULE_EVENTS = Array();
$MAIN_MODULE_EVENTS_INIT = false;

$GLOBALS["arBitrixModuleClasses"] = array();

define("MODULE_NOT_FOUND", 0);
define("MODULE_INSTALLED", 1);
define("MODULE_DEMO", 2);
define("MODULE_DEMO_EXPIRED", 3);


if (!function_exists("__autoload"))
{
	function __autoload($className)
	{
		CModule::RequireAutoloadClass($className);
	}
	define("NO_BITRIX_AUTOLOAD", False);
}
else
{
	define("NO_BITRIX_AUTOLOAD", True);
}

Class CModule
{
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_VERSION;
	var $MODULE_ID;
	var $MODULE_SORT=10000;
	var $SHOW_SUPER_ADMIN_GROUP_RIGHTS;

	function AddAutoloadClasses($module, $arParams = array())
	{
		if (!is_array($arParams) || count($arParams) <= 0)
			return False;

		$module = Trim($module);

		if (!minimumPHPVersion("5.0.0") || defined("NO_BITRIX_AUTOLOAD") && NO_BITRIX_AUTOLOAD)
		{
			foreach ($arParams as $key => $value)
				include_once($_SERVER["DOCUMENT_ROOT"].((StrLen($module) > 0) ? BX_ROOT."/modules/".$module."/" : "").$value);
		}
		else
		{
			foreach ($arParams as $key => $value)
				$GLOBALS["arBitrixModuleClasses"][strtolower($key)] = array(
					"module" => $module,
					"file" => $value
				);
		}
	}

	function AutoloadClassDefined($className)
	{
		$className = Trim($className);
		if (StrLen($className) <= 0)
			return False;

		$className = strtolower($className);

		return array_key_exists($className, $GLOBALS["arBitrixModuleClasses"]);
	}

	function RequireAutoloadClass($className)
	{
		$className = Trim($className);
		if (StrLen($className) <= 0)
			return False;

		$className = strtolower($className);

		if (array_key_exists($className, $GLOBALS["arBitrixModuleClasses"]))
		{
			require_once($_SERVER["DOCUMENT_ROOT"].((StrLen($GLOBALS["arBitrixModuleClasses"][$className]["module"]) > 0) ? BX_ROOT."/modules/".$GLOBALS["arBitrixModuleClasses"][$className]["module"]."/" : "").$GLOBALS["arBitrixModuleClasses"][$className]["file"]);
			return True;
		}

		return False;
	}


	function _GetCache()
	{
		global $DB,$CACHE_MANAGER;
		if($CACHE_MANAGER->Read(3600, "b_module"))
			$arModules = $CACHE_MANAGER->Get("b_module");
		else
		{
			$arModules = array();
			$rs = $DB->Query("SELECT m.* FROM b_module m ORDER BY m.ID");
			while($ar = $rs->Fetch())
				$arModules[$ar['ID']] = $ar;
			$CACHE_MANAGER->Set("b_module", $arModules);
		}
		return $arModules;
	}

	function _GetName($arEvent)
	{
		$strName = '';
		if(array_key_exists("CALLBACK", $arEvent))
		{
			if(is_array($arEvent["CALLBACK"]))
				$strName .= (is_object($arEvent["CALLBACK"][0]) ? get_class($arEvent["CALLBACK"][0]) : $arEvent["CALLBACK"][0]).'::'.$arEvent["CALLBACK"][1];
			else
				$strName .= $arEvent["CALLBACK"];
		}
		else
			$strName .= $arEvent["TO_CLASS"].'::'.$arEvent["TO_METHOD"];
		if(strlen($arEvent['TO_MODULE_ID'])>0)
			$strName .= ' ('.$arEvent['TO_MODULE_ID'].')';
		return $strName;
	}

	function InstallDB()
	{
		return false;
	}

	function UnInstallDB()
	{
	}

	function InstallEvents()
	{
	}

	function UnInstallEvents()
	{
	}

	function InstallFiles()
	{
	}

	function UnInstallFiles()
	{
	}

	function DoInstall()
	{
	}

	function IsInstalled()
	{
		$arModules = CModule::_GetCache();
		return array_key_exists($this->MODULE_ID, $arModules);
	}

	function DoUninstall()
	{
	}

	function Remove()
	{
		global $DB,$CACHE_MANAGER;
		$CACHE_MANAGER->Clean("b_module");
		$DB->Query("DELETE FROM b_module WHERE ID='".$this->MODULE_ID."'");
	}

	function Add()
	{
		global $DB, $CACHE_MANAGER, $MAIN_MODULE_INCLUDED, $MAIN_MODULE_INCLUDED_EX;
		$CACHE_MANAGER->Clean("b_module");
		$DB->Query(
			"INSERT INTO b_module(ID) ".
			"VALUES('".$this->MODULE_ID."')"
			);
		unset($MAIN_MODULE_INCLUDED[$this->MODULE_ID]);
		unset($MAIN_MODULE_INCLUDED_EX[$this->MODULE_ID]);
	}

	function GetList()
	{
		$result = new CDBResult;
		$result->InitFromArray(CModule::_GetCache());
		return $result;
	}

	function IncludeModule($module_name)
	{
		global $DB, $MAIN_MODULE_INCLUDED, $MESS;

		if(defined("SM_SAFE_MODE") && SM_SAFE_MODE===true)
		{
			if(!in_array($module_name, Array("main", "fileman")))
				return false;
		}

		if(is_set($MAIN_MODULE_INCLUDED, $module_name))
			return $MAIN_MODULE_INCLUDED[$module_name];

		$arModules = CModule::_GetCache();
		if(!array_key_exists($module_name, $arModules))
		{
			$MAIN_MODULE_INCLUDED[$module_name] = false;
			return false;
		}

		if(!file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_name."/include.php"))
		{
			$MAIN_MODULE_INCLUDED[$module_name] = false;
			return false;
		}

		//$oldval = ini_set("track_errors", "1");
		$aaa = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_name."/include.php");
		if ($aaa===false)
		{
			$MAIN_MODULE_INCLUDED[$module_name] = false;
			return false;
		}
		/*
		ini_set("track_errors", $oldval);
		if(strlen($php_errormsg)>0)
		{
			$MAIN_MODULE_INCLUDED[$module_name] = false;
			return false;
		}
        */

		$MAIN_MODULE_INCLUDED[$module_name] = true;
		return true;
	}

	function IncludeModuleEx($module_name)
	{
		global $MAIN_MODULE_INCLUDED_EX;

		if (is_set($MAIN_MODULE_INCLUDED_EX, $module_name))
			return $MAIN_MODULE_INCLUDED_EX[$module_name];

		$module_name_tmp = str_replace(".", "_", $module_name);

		if (CModule::IncludeModule($module_name))
		{
			if (defined($module_name_tmp."_DEMO") && constant($module_name_tmp."_DEMO") == "Y")
				$MAIN_MODULE_INCLUDED_EX[$module_name] = MODULE_DEMO;
			else
				$MAIN_MODULE_INCLUDED_EX[$module_name] = MODULE_INSTALLED;

			return $MAIN_MODULE_INCLUDED_EX[$module_name];
		}

		if (defined($module_name_tmp."_DEMO") && constant($module_name_tmp."_DEMO") == "Y")
		{
			$MAIN_MODULE_INCLUDED_EX[$module_name] = MODULE_DEMO_EXPIRED;
			return MODULE_DEMO_EXPIRED;
		}

		$MAIN_MODULE_INCLUDED_EX[$module_name] = MODULE_NOT_FOUND;
		return MODULE_NOT_FOUND;
	}

	function err_mess()
	{
		return "<br>Class: CModule;<br>File: ".__FILE__;
	}

	function GetDropDownList($strSqlOrder="ORDER BY ID")
	{
		global $DB;
		$err_mess = (CModule::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				ID as REFERENCE
			FROM
				b_module
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function CreateModuleObject($moduleId)
	{
		$moduleId = trim($moduleId);
		$moduleId = preg_replace("/[^a-zA-Z0-9_.]+/i", "", $moduleId);
		if (strlen($moduleId) <= 0)
			return false;

		$path = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$moduleId."/install/index.php";
		if (!file_exists($path))
			return false;

		include_once($path);

		$className = str_replace(".", "_", $moduleId);
		if (!class_exists($className))
			return false;

		return new $className;
	}
}

function RegisterModule($id)
{
	$m = new CModule;
	$m->MODULE_ID = $id;
	$m->Add();
}

function UnRegisterModule($id)
{
	global $DB;
	$DB->Query("DELETE FROM b_agent WHERE MODULE_ID='".$DB->ForSQL($id)."'");
	CMain::DelGroupRight($id);
	$m = new CModule;
	$m->MODULE_ID = $id;
	$m->Remove();
}

function AddEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $CALLBACK, $SORT=100, $FULL_PATH = false)
{
	global $MAIN_MODULE_EVENTS;

	$arEvent = array("FROM_MODULE_ID"=>$FROM_MODULE_ID, "MESSAGE_ID"=>$MESSAGE_ID, "CALLBACK"=>$CALLBACK, "SORT"=>$SORT, "FULL_PATH"=>$FULL_PATH);
	$arEvent['TO_NAME'] = CModule::_GetName($arEvent);

	$FROM_MODULE_ID = strtoupper($FROM_MODULE_ID);
	$MESSAGE_ID = strtoupper($MESSAGE_ID);

	if (!is_array($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID]))
		$MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID] = array();

	$iEventHandlerKey = count($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID]);

	$MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID][$iEventHandlerKey] = $arEvent;

	uasort($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID], create_function('$a, $b', 'if($a["SORT"] == $b["SORT"]) return 0; return ($a["SORT"] < $b["SORT"])? -1 : 1;'));

	return $iEventHandlerKey;
}

function RemoveEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $iEventHandlerKey)
{
	global $MAIN_MODULE_EVENTS;

	$FROM_MODULE_ID = strtoupper($FROM_MODULE_ID);
	$MESSAGE_ID = strtoupper($MESSAGE_ID);

	if(is_array($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID]))
	{
		if(isset($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID][$iEventHandlerKey]))
		{
			unset($MAIN_MODULE_EVENTS[$FROM_MODULE_ID][$MESSAGE_ID][$iEventHandlerKey]);
			return true;
		}
	}

	return false;
}

function GetModuleEvents($MODULE_ID, $MESSAGE_ID)
{
	global $DB, $MAIN_MODULE_EVENTS, $MAIN_MODULE_EVENTS_INIT;
	if($MAIN_MODULE_EVENTS_INIT === false)
	{
		global $CACHE_MANAGER;
		if($CACHE_MANAGER->Read(3600, "b_module_to_module"))
			$arEvents = $CACHE_MANAGER->Get("b_module_to_module");
		else
		{
			$arEvents = array();
			$rs = $DB->Query("
				SELECT
					*
				FROM
					b_module_to_module m2m
				INNER JOIN b_module m ON (m2m.TO_MODULE_ID = m.ID)
				ORDER BY SORT
			");
			while($ar = $rs->Fetch())
			{
				$ar['TO_NAME'] = CModule::_GetName($ar);
				$ar["~FROM_MODULE_ID"]=strtoupper($ar["FROM_MODULE_ID"]);
				$ar["~MESSAGE_ID"]=strtoupper($ar["MESSAGE_ID"]);
				if (strlen($ar["TO_METHOD_ARG"]) > 0)
					$ar["TO_METHOD_ARG"] = unserialize($ar["TO_METHOD_ARG"]);
				else
					$ar["TO_METHOD_ARG"] = array();
				$arEvents[] = $ar;
			}
			$CACHE_MANAGER->Set("b_module_to_module", $arEvents);
		}

		if(!is_array($arEvents))
			$arEvents = Array();

		$copy_MAIN_MODULE_EVENTS = $MAIN_MODULE_EVENTS;

		foreach($arEvents as $ar)
			$MAIN_MODULE_EVENTS[$ar["~FROM_MODULE_ID"]][$ar["~MESSAGE_ID"]][] = $ar;

		// need to re-sort because of AddEventHandler() calls
		$funcSort = create_function('$a, $b', 'if($a["SORT"] == $b["SORT"]) return 0; return ($a["SORT"] < $b["SORT"])? -1 : 1;');
		foreach(array_keys($copy_MAIN_MODULE_EVENTS) as $module)
			foreach(array_keys($copy_MAIN_MODULE_EVENTS[$module]) as $message)
				uasort($MAIN_MODULE_EVENTS[$module][$message], $funcSort);

		$MAIN_MODULE_EVENTS_INIT = true;
	}

	$MODULE_ID = strtoupper($MODULE_ID);
	$MESSAGE_ID = strtoupper($MESSAGE_ID);
	if(array_key_exists($MODULE_ID, $MAIN_MODULE_EVENTS) && array_key_exists($MESSAGE_ID, $MAIN_MODULE_EVENTS[$MODULE_ID]))
		$arrResult = $MAIN_MODULE_EVENTS[$MODULE_ID][$MESSAGE_ID];
	else
		$arrResult = Array();

	$resRS = new CDBResult;
	$resRS->InitFromArray($arrResult);

	return $resRS;
}

function ExecuteModuleEvent($arEvent, $param1=NULL, $param2=NULL, $param3=NULL, $param4=NULL, $param5=NULL, $param6=NULL, $param7=NULL, $param8=NULL, $param9=NULL, $param10=NULL)
{
	$CNT_PREDEF = 10;
	$r = true;
	if($arEvent["TO_MODULE_ID"]<>"" && $arEvent["TO_MODULE_ID"]<>"main")
	{
		if(!CModule::IncludeModule($arEvent["TO_MODULE_ID"]))
			return;
		$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$arEvent["TO_MODULE_ID"]."/include.php");
	}
	elseif($arEvent["TO_PATH"]<>"" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]))
		$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]);
	elseif($arEvent["FULL_PATH"]<>"" && file_exists($arEvent["FULL_PATH"]))
		$r = include_once($arEvent["FULL_PATH"]);

	if((strlen($arEvent["TO_CLASS"])<=0 || strlen($arEvent["TO_METHOD"])<=0) && !is_set($arEvent, "CALLBACK"))
		return $r;

	UnSet($resmod);

	$args = Array();
	if (is_array($arEvent["TO_METHOD_ARG"]) && count($arEvent["TO_METHOD_ARG"]) > 0)
	{
		foreach ($arEvent["TO_METHOD_ARG"] as $v)
			$args[] = $v;
	}

	for($i=1; $i<=$CNT_PREDEF; $i++)
	{
		if($i>func_num_args())
			break;
		$args[] = &${"param".$i};
	}

	for($i=$CNT_PREDEF+1; $i<func_num_args(); $i++)
		$args[] = func_get_arg($i);

	if(is_set($arEvent, "CALLBACK"))
	{
		$resmod = call_user_func_array($arEvent["CALLBACK"], $args);
	}
	else
	{
		//php bug: http://bugs.php.net/bug.php?id=47948
		class_exists($arEvent["TO_CLASS"]);
		$resmod = call_user_func_array(array($arEvent["TO_CLASS"], $arEvent["TO_METHOD"]), $args);
	}

	return $resmod;
}

function ExecuteModuleEventEx($arEvent, $arParams = array())
{
	$r = true;

	if($arEvent["TO_MODULE_ID"]<>"" && $arEvent["TO_MODULE_ID"]<>"main")
	{
		if(CModule::IncludeModule($arEvent["TO_MODULE_ID"]))
			$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$arEvent["TO_MODULE_ID"]."/include.php");
		else
			return;
	}
	elseif($arEvent["TO_PATH"]<>"" && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]))
	{
		$r = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]);
	}
	elseif($arEvent["FULL_PATH"]<>"" && file_exists($arEvent["FULL_PATH"]))
	{
		$r = include_once($arEvent["FULL_PATH"]);
	}

	if(array_key_exists("CALLBACK", $arEvent))
	{
		if(is_array($arEvent["TO_METHOD_ARG"]) && count($arEvent["TO_METHOD_ARG"]))
			$args = array_merge($arEvent["TO_METHOD_ARG"], $arParams);
		else
			$args = $arParams;

		return call_user_func_array($arEvent["CALLBACK"], $args);
	}
	elseif(strlen($arEvent["TO_CLASS"]) && strlen($arEvent["TO_METHOD"]))
	{
		if(is_array($arEvent["TO_METHOD_ARG"]) && count($arEvent["TO_METHOD_ARG"]))
			$args = array_merge($arEvent["TO_METHOD_ARG"], $arParams);
		else
			$args = $arParams;

		//php bug: http://bugs.php.net/bug.php?id=47948
		class_exists($arEvent["TO_CLASS"]);
		return call_user_func_array(array($arEvent["TO_CLASS"], $arEvent["TO_METHOD"]), $args);
	}
	else
	{
		return $r;
	}
}

function UnRegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $TO_PATH="", $TO_METHOD_ARG = array())
{
	global $DB,$CACHE_MANAGER;

	$TO_METHOD_ARG = ((!is_array($TO_METHOD_ARG) || is_array($TO_METHOD_ARG) && count($TO_METHOD_ARG) <= 0) ? "" : serialize($TO_METHOD_ARG));

	$strSql = "DELETE FROM b_module_to_module ".
			"WHERE FROM_MODULE_ID='".$DB->ForSql($FROM_MODULE_ID)."'".
			"	AND MESSAGE_ID='".$DB->ForSql($MESSAGE_ID)."' ".
			"	AND TO_MODULE_ID='".$DB->ForSql($TO_MODULE_ID)."' ".
			(strlen($TO_CLASS)>0?
				"	AND TO_CLASS='".$DB->ForSql($TO_CLASS)."' ":
				"	AND (TO_CLASS='' OR TO_CLASS IS NULL) ").
			(strlen($TO_METHOD)>0?
				"	AND TO_METHOD='".$DB->ForSql($TO_METHOD)."'":
				"	AND (TO_METHOD='' OR TO_METHOD IS NULL) ").
			(strlen($TO_PATH)>0?
				"	AND TO_PATH='".$DB->ForSql($TO_PATH)."'":
				"	AND (TO_PATH='' OR TO_PATH IS NULL) ").
			(strlen($TO_METHOD_ARG)>0?
				"	AND TO_METHOD_ARG='".$DB->ForSql($TO_METHOD_ARG)."'":
				"	AND (TO_METHOD_ARG='' OR TO_METHOD_ARG IS NULL) ");
	$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	$CACHE_MANAGER->Clean("b_module_to_module");
}

function RegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $SORT=100, $TO_PATH="", $TO_METHOD_ARG = array())
{
	global $DB,$CACHE_MANAGER;

	$TO_METHOD_ARG = ((!is_array($TO_METHOD_ARG) || is_array($TO_METHOD_ARG) && count($TO_METHOD_ARG) <= 0) ? "" : serialize($TO_METHOD_ARG));

	$r = $DB->Query(
		"SELECT 'x' ".
		"FROM b_module_to_module ".
		"WHERE FROM_MODULE_ID='".$DB->ForSql($FROM_MODULE_ID)."'".
		"	AND MESSAGE_ID='".$DB->ForSql($MESSAGE_ID)."' ".
		"	AND TO_MODULE_ID='".$DB->ForSql($TO_MODULE_ID)."' ".
		"	AND TO_CLASS='".$DB->ForSql($TO_CLASS)."' ".
		"	AND TO_METHOD='".$DB->ForSql($TO_METHOD)."'".
		(strlen($TO_PATH)<=0?
			"	AND (TO_PATH='' OR TO_PATH IS NULL)"
			:"	AND TO_PATH='".$DB->ForSql($TO_PATH)."'"
		).
		(strlen($TO_METHOD_ARG)<=0?
			"	AND (TO_METHOD_ARG='' OR TO_METHOD_ARG IS NULL)"
			:"	AND TO_METHOD_ARG='".$DB->ForSql($TO_METHOD_ARG)."'"
			)
	);

	if(!$r->Fetch())
	{
		$arFields = array(
			"SORT"			=> intval($SORT),
			"FROM_MODULE_ID"	=> "'".$DB->ForSql($FROM_MODULE_ID)."'",
			"MESSAGE_ID"		=> "'".$DB->ForSql($MESSAGE_ID)."'",
			"TO_MODULE_ID"		=> "'".$DB->ForSql($TO_MODULE_ID)."'",
			"TO_CLASS"		=> "'".$DB->ForSql($TO_CLASS)."'",
			"TO_METHOD"		=> "'".$DB->ForSql($TO_METHOD)."'",
			"TO_PATH"		=> "'".$DB->ForSql($TO_PATH)."'",
			"TO_METHOD_ARG"		=> "'".$DB->ForSql($TO_METHOD_ARG)."'",
			);
		$DB->Insert("b_module_to_module",$arFields, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
		$CACHE_MANAGER->Clean("b_module_to_module");
	}
}

function IsModuleInstalled($module_id)
{
	$m = new CModule;
	$m->MODULE_ID = $module_id;
	return $m->IsInstalled();
}

function GetModuleID($str)
{
	$arr = explode("/",$str);
	$i = array_search("modules",$arr);
	return $arr[$i+1];
}

/************************************
return TRUE if version1 >= version2
version1 = "XX.XX.XX"
version2 = "XX.XX.XX"
************************************/
function CheckVersion($version1, $version2)
{
	$arr1 = explode(".",$version1);
	$arr2 = explode(".",$version2);
	if (intval($arr2[0])>intval($arr1[0])) return false;
	elseif (intval($arr2[0])<intval($arr1[0])) return true;
	else
	{
		if (intval($arr2[1])>intval($arr1[1])) return false;
		elseif (intval($arr2[1])<intval($arr1[1])) return true;
		else
		{
			if (intval($arr2[2])>intval($arr1[2])) return false;
			elseif (intval($arr2[2])<intval($arr1[2])) return true;
			else return true;
		}
	}
}?>