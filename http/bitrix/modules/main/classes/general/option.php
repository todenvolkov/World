<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

global $MAIN_OPTIONS;
$MAIN_OPTIONS = array();
class CAllOption
{
	function err_mess()
	{
		return "<br>Class: CAllOption<br>File: ".__FILE__;
	}

	function GetOptionString($module_id, $name, $def="", $site=false)
	{
		global $DB, $MAIN_OPTIONS;

		if($site===false)
			$site = SITE_ID;

		if($site == "")
			$site_id = '-';
		else
			$site_id = $site;

		if(CACHED_b_option===false)
		{
			if(!isset($MAIN_OPTIONS[$site_id][$module_id]))
			{
				$MAIN_OPTIONS[$site_id][$module_id] = array();
				$res = $DB->Query(
						"SELECT SITE_ID, NAME, VALUE ".
						"FROM b_option ".
						"WHERE (SITE_ID='".$DB->ForSql($site, 2)."' OR SITE_ID IS NULL)".
						"	AND MODULE_ID='".$DB->ForSql($module_id)."'"
						);

				while($ar = $res->Fetch())
					$MAIN_OPTIONS[strlen($ar["SITE_ID"])>0?$ar["SITE_ID"]:"-"][$module_id][$ar["NAME"]]=$ar["VALUE"];
			}
		}
		else
		{
			if(count($MAIN_OPTIONS)==0)
			{
				global $CACHE_MANAGER;
				if($CACHE_MANAGER->Read(CACHED_b_option, "b_option"))
				{
					$MAIN_OPTIONS = $CACHE_MANAGER->Get("b_option");
				}
				else
				{
					$res = $DB->Query("SELECT o.SITE_ID, o.MODULE_ID, o.NAME, o.VALUE FROM b_option o");
					while($ar = $res->Fetch())
						$MAIN_OPTIONS[strlen($ar["SITE_ID"])>0?$ar["SITE_ID"]:"-"][$ar["MODULE_ID"]][$ar["NAME"]]=$ar["VALUE"];
					$CACHE_MANAGER->Set("b_option", $MAIN_OPTIONS);
				}
			}
		}

		if(isset($MAIN_OPTIONS[$site_id][$module_id][$name]))
			return $MAIN_OPTIONS[$site_id][$module_id][$name];

		if($site_id!="-" && isset($MAIN_OPTIONS["-"][$module_id][$name]))
			return $MAIN_OPTIONS["-"][$module_id][$name];

		if(strlen($def)<=0 && file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_id."/default_option.php"))
		{
			$var = str_replace(".", "_", $module_id)."_default_option";
			global $$var;
			include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_id."/default_option.php");
			$arrDefault = $$var;
			if(is_array($arrDefault)) return $arrDefault[$name];
		}

		return $def;
	}

	function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		global $DB,$CACHE_MANAGER;
		if(CACHED_b_option!==false) $CACHE_MANAGER->Clean("b_option");

		if($site === false)
			$site = SITE_ID;

		$strSqlWhere = " SITE_ID".($site==""?" IS NULL":"='".$DB->ForSql($site, 2)."'")." ";

		$res = $DB->Query(
			"SELECT 'x' ".
			"FROM b_option ".
			"WHERE ".$strSqlWhere.
			"	AND MODULE_ID='".$DB->ForSql($module_id)."' ".
			"	AND NAME='".$DB->ForSql($name)."'"
			);

		if($res_array = $res->Fetch())
		{
			$DB->Query(
				"UPDATE b_option SET ".
				"	VALUE='".$DB->ForSql($value, 2000)."'".
				($desc!==false?", DESCRIPTION='".$DB->ForSql($desc, 255)."'":"")." ".
				"WHERE ".$strSqlWhere.
				"	AND MODULE_ID='".$DB->ForSql($module_id)."' ".
				"	AND NAME='".$DB->ForSql($name)."'"
				);
		}
		else
		{
			$DB->Query(
				"INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE, DESCRIPTION) ".
				"VALUES(".($site==""?"NULL":"'".$DB->ForSQL($site, 2)."'").", ".
				"'".$DB->ForSql($module_id, 50)."', '".$DB->ForSql($name, 50)."', ".
				"'".$DB->ForSql($value, 2000)."', '".$DB->ForSql($desc, 255)."') "
				);
		}

		if($site == "")
			$site = '-';

		global $MAIN_OPTIONS;
		$MAIN_OPTIONS[$site][$module_id][$name] = $value;

		$fname = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$module_id.'/option_triggers.php';
		if(file_exists($fname))
			include_once($fname);

		$events = GetModuleEvents("main", "OnAfterSetOption_".$name);
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($value));

		return true;
	}

	function RemoveOption($module_id, $name="", $site=false)
	{
		global $DB,$CACHE_MANAGER;
		if(CACHED_b_option!==false) $CACHE_MANAGER->Clean("b_option");

		$strSqlWhere = "";
		if(strlen($name)>0)
			$strSqlWhere = " AND NAME='".$DB->ForSql($name)."' ";

		if(strlen($site)>0)
			$strSqlWhere .= "	AND SITE_ID='".$DB->ForSql($site)."'";

		if($module_id == "main")
			$DB->Query("DELETE FROM b_option WHERE MODULE_ID='main' AND NAME NOT LIKE '~%' AND NAME<>'crc_code' AND NAME<>'admin_passwordh' AND NAME<>'server_uniq_id' AND NAME<>'PARAM_MAX_SITES' AND NAME<>'PARAM_MAX_USERS' ".$strSqlWhere);
		else
			$DB->Query("DELETE FROM b_option WHERE MODULE_ID='".$DB->ForSql($module_id)."' ".$strSqlWhere);

		global $MAIN_OPTIONS;

		if($site===false)
		{
			$arSites = array_keys($MAIN_OPTIONS);
			for($i=0; $i<count($arSites); $i++)
			{
				$site = $arSites[$i];
				if($name == "")
					unset($MAIN_OPTIONS[$site][$module_id]);
				else
					unset($MAIN_OPTIONS[$site][$module_id][$name]);
			}
		}
		else
		{
			if($name == "")
				unset($MAIN_OPTIONS[$site][$module_id]);
			else
				unset($MAIN_OPTIONS[$site][$module_id][$name]);
		}
	//echo "<br>Remove:<pre>";print_r($MAIN_OPTIONS);echo "</pre>";
	}

	function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return COption::GetOptionString($module_id, $name, $def, $site);
	}

	function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return COption::SetOptionString($module_id, $name, IntVal($value), $desc, $site);
	}
}

global $MAIN_PAGE_OPTIONS;
$MAIN_PAGE_OPTIONS = array();
class CAllPageOption
{
	function GetOptionString($module_id, $name, $def="", $site=false)
	{
		global $MAIN_PAGE_OPTIONS;

		if($site===false)
			$site = SITE_ID;

		if(isset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]))
			return $MAIN_PAGE_OPTIONS[$site][$module_id][$name];
		elseif(isset($MAIN_PAGE_OPTIONS["-"][$module_id][$name]))
			return $MAIN_PAGE_OPTIONS["-"][$module_id][$name];
		return $def;
	}

	function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		global $MAIN_PAGE_OPTIONS;

		if($site===false)
			$site = SITE_ID;
		if(strlen($site)<=0)
			$site = "-";

		$MAIN_PAGE_OPTIONS[$site][$module_id][$name] = $value;
		return true;
	}

	function RemoveOption($module_id, $name="", $site=false)
	{
		global $MAIN_PAGE_OPTIONS;

		if($site===false)
		{
			$arSites = array_keys($MAIN_PAGE_OPTIONS);
			for($i=0; $i<count($arSites); $i++)
			{
				$site = $arSites[$i];
				if($name == "")
					unset($MAIN_PAGE_OPTIONS[$site][$module_id]);
				else
					unset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
			}
		}
		else
		{
			if($name == "")
				unset($MAIN_PAGE_OPTIONS[$site][$module_id]);
			else
				unset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
		}
	}

	function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return CPageOption::GetOptionString($module_id, $name, $def, $site);
	}

	function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return CPageOption::SetOptionString($module_id, $name, IntVal($value), $desc, $site);
	}
}
?>