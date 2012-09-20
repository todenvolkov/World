<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

if(!check_bitrix_sessid())
	die();

IncludeModuleLangFile(__FILE__);

$aUserOpt = CUserOptions::GetOption("global", "settings", array());

function __GetSubmenu($menu)
{
	global $aUserOpt;
	
	$aPopup = array();
	foreach($menu as $item)
	{
		if(!is_array($item))
			continue;

		$aItem = array(
			"TEXT"=>$item["text"],
			"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? $item["title"] : ''),
			"ICON"=>$item["icon"],
		);
		if($item["url"] <> "")
		{
			$link = $item["url"];
			if(strpos($link, "/bitrix/admin/") !== 0)
				$link = "/bitrix/admin/".$link;
			$aItem["ACTION"] = "jsStartMenu.OpenURL(this, arguments, '".
				CUtil::JSEscape($link)."'".
				($_REQUEST["back_url_pub"]<>''? ", '".CUtil::JSEscape($_REQUEST["back_url_pub"])."'":"").");";
		}

		if(is_array($item["items"]) && count($item["items"])>0)
		{
			$aItem["MENU"] = __GetSubmenu($item["items"]);
			if($item["url"] <> "" && $aUserOpt['start_menu_title'] <> 'N')
				$aItem["TITLE"] .= ' '.GetMessage("get_start_menu_dbl");
		}
		elseif($item["dynamic"] == true)
		{
			$aItem["MENU"] = array(
				array(
					"TEXT"=>GetMessage("get_start_menu_loading"),
					"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? GetMessage("get_start_menu_loading_title") : ''),
					"ICON"=>"loading",
					"AUTOHIDE"=>false,
				)
			);
			if($item["url"] <> "" && $aUserOpt['start_menu_title'] <> 'N')
				$aItem["TITLE"] .= ' '.GetMessage("get_start_menu_dbl");

			$aItem["ONMENUPOPUP"] = "jsStartMenu.OpenDynMenu(menu, '".
				CUtil::JSEscape($item["module_id"])."', '".
				CUtil::JSEscape($item["items_id"])."'".
				($_REQUEST["back_url_pub"]<>''? ", '".CUtil::JSEscape($_REQUEST["back_url_pub"])."'":"").");";
		}
		
		$aPopup[] = $aItem;
	}
	return $aPopup;
}

function __FindSubmenu($menu, $items_id)
{
	foreach($menu as $item)
	{
		if(is_array($item["items"]) && count($item["items"])>0)
		{
			if($item["items_id"] == $items_id)
				return $item["items"];
			elseif(($m = __FindSubmenu($item["items"], $items_id)) !== false)
				return $m;
		}
	}
	return false;
}

if($_REQUEST["mode"] == "save_recent")
{
	if($_REQUEST["url"] <> "")
	{
		$nLinks = 5;
		if($aUserOpt["start_menu_links"] <> "")
			$nLinks = intval($aUserOpt["start_menu_links"]);
		
		$aRecent = CUserOptions::GetOption("start_menu", "recent", array());

		CUtil::decodeURIComponent($_REQUEST["text"]);
		CUtil::decodeURIComponent($_REQUEST["title"]);
		$aLink = array("url"=>$_REQUEST["url"], "text"=>$_REQUEST["text"], "title"=>$_REQUEST["title"], "icon"=>$_REQUEST["icon"]);

		if(($pos = array_search($aLink, $aRecent)) !== false)
			unset($aRecent[$pos]);
		array_unshift($aRecent, $aLink);
		$aRecent = array_slice($aRecent, 0, $nLinks);

		CUserOptions::SetOption("start_menu", "recent", $aRecent);
	}
	echo "OK";
}
elseif($_REQUEST["mode"] == "dynamic")
{
	//admin menu - dynamic sections
	$adminMenu->AddOpenedSections($_REQUEST["admin_mnu_menu_id"]);
	$adminMenu->Init(array($_REQUEST["admin_mnu_module_id"]));

	$aSubmenu = __FindSubmenu($adminMenu->aGlobalMenu, $_REQUEST["admin_mnu_menu_id"]);
		
	if(!is_array($aSubmenu) || empty($aSubmenu))
		$aSubmenu = array(array("text"=>GetMessage("get_start_menu_no_data")));

	//generate JavaScript array for popup menu
	echo "menuItems={'items':".CAdminPopup::PhpToJavaScript(__GetSubmenu($aSubmenu))."}";
}
else
{
	//admin menu - all static sections
	$adminPage->Init();
	$adminMenu->Init($adminPage->aModules);

	$aPopup = array();
	foreach($adminMenu->aGlobalMenu as $menu)
	{
		$aPopup[] = array(
			"TEXT"=>$menu["text"], 
			"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? $menu["title"].' '.GetMessage("get_start_menu_dbl"):''), 
			"ICON"=>$menu["icon"], 
			"ACTION"=>"jsUtils.Redirect(arguments, '".CUtil::addslashes('/bitrix/admin/'.$menu['url'])."');",
			"MENU"=>__GetSubmenu($menu["items"])
		);
	}
	
	//favorites
	if($USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('view_other_settings'))
	{
		$aFav = array(
			array(
				"TEXT"=>GetMessage("get_start_menu_add_fav"),
				"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? GetMessage("get_start_menu_add_fav_title"):''),
				"ACTION"=>"jsUtils.Redirect(arguments, '".BX_ROOT."/admin/favorite_edit.php?lang=".CUtil::addslashes(LANGUAGE_ID).
					"&name='+encodeURIComponent(document.title)+'".
					"&addurl='+encodeURIComponent(window.location.href)+'".
					"&encoded=Y'".
					($_REQUEST["back_url_pub"]<>''? "+'&back_url_pub=".CUtil::JSEscape(urlencode($_REQUEST["back_url_pub"]))."'":"").");"
			),
			array(
				"TEXT"=>GetMessage("get_start_menu_org_fav"),
				"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? GetMessage("get_start_menu_org_fav_title"):''),
				"ACTION"=>"jsUtils.Redirect(arguments, '".BX_ROOT."/admin/favorite_list.php?lang=".CUtil::addslashes(LANGUAGE_ID)."'".
					($_REQUEST["back_url_pub"]<>''? "+'&back_url_pub=".CUtil::JSEscape(urlencode($_REQUEST["back_url_pub"]))."'":"").");"
			),
		);
		
		$db_fav = CFavorites::GetList(array("COMMON"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("MENU_FOR_USER"=>$USER->GetID(), "LANGUAGE_ID"=>LANGUAGE_ID));
		$prevCommon = "";
		while($db_fav_arr = $db_fav->Fetch())
		{
			if($db_fav_arr["COMMON"] == "Y" && $db_fav_arr["MODULE_ID"] <> "" && $APPLICATION->GetGroupRight($db_fav_arr["MODULE_ID"]) < "R")
				continue;
			if($db_fav_arr["COMMON"] <> $prevCommon)
			{
				$aFav[] = array("SEPARATOR"=>true);
				$prevCommon = $db_fav_arr["COMMON"];
			}
		
			$sTitle = '';
			if($aUserOpt['start_menu_title'] <> 'N') 
			{
				$sTitle = $db_fav_arr["COMMENTS"];
				$sTitle = (strlen($sTitle)>100? substr($sTitle, 0, 100)."..." : $sTitle);
				$sTitle = str_replace("\r\n", "\n", $sTitle);
				$sTitle = str_replace("\r", "\n", $sTitle);
				$sTitle = str_replace("\n", " ", $sTitle);
			}
		
			$aFav[] = array(
				"TEXT"=>htmlspecialchars($db_fav_arr["NAME"]),
				"TITLE"=>htmlspecialchars($sTitle),
				"ICON"=>"favorites",
				"ACTION"=>"jsUtils.Redirect(arguments, '".CUtil::JSEscape(htmlspecialchars($db_fav_arr["URL"]))."');",
			);
		}
		$aPopup[] = array("SEPARATOR"=>true);
		$aPopup[] = array(
			"TEXT"=>GetMessage("get_start_menu_fav"),
			"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? GetMessage("get_start_menu_fav_title"):''),
			"ICON"=>"favorites",
			"MENU"=>$aFav,
		);
	}
	
	//recent urls
	$aRecent = CUserOptions::GetOption("start_menu", "recent", array());
	if(!empty($aRecent))
	{
		$aPopup[] = array("SEPARATOR"=>true);

		$nLinks = 5;
		if($aUserOpt["start_menu_links"] <> "")
			$nLinks = intval($aUserOpt["start_menu_links"]);

		$i = 0;
		foreach($aRecent as $recent)
		{
			$i++;
			if($i > $nLinks)
				break;
			$aPopup[] = array(
				"TEXT"=>htmlspecialchars($recent["text"]),
				"TITLE"=>($aUserOpt['start_menu_title'] <> 'N'? htmlspecialchars($recent["title"]):''),
				"ICON"=>htmlspecialchars($recent["icon"]),
				"ACTION"=>"jsStartMenu.OpenURL(this, arguments, '".CUtil::JSEscape($recent["url"])."'".($_REQUEST["back_url_pub"]<>''? ", '".CUtil::JSEscape($_REQUEST["back_url_pub"])."'":"").");",
			);
		}
	}

	//styles of icons from modules
	$sCss = '';
	foreach($adminPage->aModules as $module)
	{
		$fname = $_SERVER["DOCUMENT_ROOT"].ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/start_menu/'.$module.'/'.$module.'.css';
		if(file_exists($fname))
		{
			if($handle = fopen($fname, "r"))
			{
				$contents = fread($handle, filesize($fname));
				fclose($handle);
				$contents = preg_replace(
					"/(background-image\\s*:\\s*url\\s*\\(\\s*)([a-z].*?)(\\))/si", 
					"\\1".ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/start_menu/'.$module.'/'."\\2\\3", 
					$contents);
				$sCss .= $contents."\n";
			}
		}
	}

	if(empty($aPopup))
		$aPopup[] = array("TEXT"=>GetMessage("get_start_menu_no_data"));

	//generate JavaScript array for popup menu
	echo "menuItems={'items':".CAdminPopup::PhpToJavaScript($aPopup).", 'styles':'".CUtil::JSEscape($sCss)."'}";

} //$_REQUEST["mode"] == "dynamic"

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>