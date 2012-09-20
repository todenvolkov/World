<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

IncludeModuleLangFile(__FILE__);
class CMenu
{
	var $type = "left";
	var $arMenu = Array();
	var $bMenuCalc = false;
	var $MenuDir = "";
	var $MenuExtDir = "";
	var $MenuTemplate = "";
	var $template = "";
	var $LAST_ERROR = "";
	var $debug = null;

	function CMenu($type="left")
	{
		$this->type=$type;
	}

	function Init($InitDir, $bMenuExt=false, $template=false, $onlyCurrentDir=false)
	{
		global $USER;
		if($_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y" && ($USER->IsAdmin() || $_SESSION["SHOW_SQL_STAT"]=="Y"))
		{
			$this->debug = new CDebugInfo();
			$this->debug->Start();
		}

		$aMenuLinks = Array();
		$bFounded = false;
		if($template === false)
			UnSet($sMenuTemplate);
		else
			$sMenuTemplate = $template;

		$InitDir = str_replace("\\", "/", $InitDir);
		$Dir = $InitDir;

		$site_dir = false;
		if(defined("SITE_DIR") && SITE_DIR <> '')
		{
			$site_dir = SITE_DIR;
		}
		elseif(array_key_exists("site", $_REQUEST) && $_REQUEST["site"] <> '')
		{
			$rsSites = CSite::GetByID($_REQUEST["site"]);
			if($arSite = $rsSites->Fetch())
				$site_dir = $arSite["DIR"];
		}

		while(strlen($Dir)>0)
		{
			if($site_dir !== false && (strlen(trim($Dir, "/")) < strlen(trim($site_dir, "/"))))
				break;

			$Dir = rtrim($Dir, "/");
			$menu_file_name=$_SERVER["DOCUMENT_ROOT"].$Dir."/.".$this->type.".menu.php";
			if(file_exists($menu_file_name))
			{
				include($menu_file_name);
				$this->MenuDir = $Dir."/";
				$this->arMenu = $aMenuLinks;
				$this->template = $sMenuTemplate;
				$bFounded = true;
				break;
			}

			if($Dir == "")
				break;

			$pos = strrpos($Dir, "/");
			if($pos===false || $onlyCurrentDir == true)
				break;

			$Dir = substr($Dir, 0, $pos+1);
		}

		if($bMenuExt)
		{
			$Dir = $InitDir;
			while(strlen($Dir)>0)
			{
				$Dir = rtrim($Dir, "/");
				$menu_file_name=$_SERVER["DOCUMENT_ROOT"].$Dir."/.".$this->type.".menu_ext.php";

				if(file_exists($menu_file_name))
				{
					include($menu_file_name);
					if(!$bFounded)
						$this->MenuDir = $Dir."/";

					$this->MenuExtDir = $Dir."/";
					$this->arMenu = $aMenuLinks;
					$this->template = $sMenuTemplate;
					$bFounded = true;
					break;
				}

				if($Dir == "")
					break;

				$pos = strrpos($Dir, "/");
				if($pos===false || $onlyCurrentDir == true)
					break;

				$Dir = substr($Dir, 0, $pos+1);
			}
		}

		return $bFounded;
	}

	function RecalcMenu($bMultiSelect=false)
	{
		if($this->bMenuCalc!==false)
			return true;
		global $USER, $DB, $APPLICATION;
		$result = Array();

		$cur_page = $APPLICATION->GetCurPage(true);
		$cur_page_no_index = $APPLICATION->GetCurPage(false);
		$cur_dir = $APPLICATION->GetCurDir();
		$APPLICATION->_menu_recalc_counter++;

		$this->bMenuCalc = true;

		if(strlen($this->template)>0 && file_exists($GLOBALS["DOCUMENT_ROOT"].$this->template))
			$this->MenuTemplate = $GLOBALS["DOCUMENT_ROOT"].$this->template;
		else
		{
			if(defined("SITE_TEMPLATE_ID") && file_exists($GLOBALS["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$this->type.".menu_template.php"))
			{
				$this->template = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$this->type.".menu_template.php";
				$this->MenuTemplate = $GLOBALS["DOCUMENT_ROOT"].$this->template;
			}
			elseif(file_exists($GLOBALS["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".LANG."/".$this->type.".menu_template.php"))
			{
				$this->template = BX_PERSONAL_ROOT."/php_interface/".LANG."/".$this->type.".menu_template.php";
				$this->MenuTemplate = $GLOBALS["DOCUMENT_ROOT"].$this->template;
			}
			else
			{
				$this->template = BX_PERSONAL_ROOT."/templates/.default/".$this->type.".menu_template.php";
				$this->MenuTemplate = $GLOBALS["DOCUMENT_ROOT"].$this->template;
			}
		}

		if(!file_exists($this->MenuTemplate))
		{
			$this->LAST_ERROR = "Template ".$this->MenuTemplate." is not found.";
			return false;
		}

		$arMenuCache = false;
		$bCached = false;
		$bCacheIsAllowed = CACHED_menu!==false && !$USER->IsAuthorized() && !$this->MenuExtDir;
		if($bCacheIsAllowed)
		{
			$cache_id = $GLOBALS["DOCUMENT_ROOT"].",".$this->MenuDir.",".$this->MenuExtDir.",".$this->type;
			if($GLOBALS["CACHE_MANAGER"]->Read(CACHED_menu, $cache_id, "menu"))
			{
				$arMenuCache = $GLOBALS["CACHE_MANAGER"]->Get($cache_id);
				$bCached = true;
			}
		}

		$arUserRights = $GLOBALS["USER"]->GetUserGroupArray();
		$ITEM_INDEX = -1;

		$cur_selected = -1;
		$cur_selected_len = -1;

		foreach($this->arMenu as $iMenuItem=>$MenuItem)
		{
			$TEXT = $MenuItem[0];

			if($bCached)
				$LINK = $arMenuCache[$iMenuItem]["LINK"];
			else
			{
				if(!preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $MenuItem[1]))
					$LINK = Rel2Abs($this->MenuDir, $MenuItem[1]); //если ссылка меню относительная преобразуем ее в абсолютную (считаем ее относительно папки где лежит меню)
				else
					$LINK = $MenuItem[1];
				$arMenuCache[$iMenuItem]["LINK"] = $LINK;
			}

			$bSkipMenuItem = false;
			$ADDITIONAL_LINKS = $MenuItem[2];
			$PARAMS = $MenuItem[3];
			if(count($MenuItem)>4)
			{
				$CONDITION = $MenuItem[4];
				if(strlen($CONDITION)>0 && (!eval("return ".$CONDITION.";")))
					$bSkipMenuItem = true;
			}

			if(!$bSkipMenuItem)
				$ITEM_INDEX++;

			if(($pos = strpos($LINK, "?"))!==false)
				$ITEM_TYPE = "U";
			elseif(substr($LINK, -1)=="/")
				$ITEM_TYPE = "D";
			else
				$ITEM_TYPE = "P";

			$SELECTED = false;

			if($bCached)
			{
				$all_links = $arMenuCache[$iMenuItem]["LINKS"];
				if(!is_array($all_links))
					$all_links = array();
			}
			else
			{
				$all_links = array();
				if(is_array($ADDITIONAL_LINKS))
				{
					foreach($ADDITIONAL_LINKS as $link)
					{
						$tested_link = trim(Rel2Abs($this->MenuDir, $link));
						if(strlen($tested_link)>0)
							$all_links[] = $tested_link;
					}
				}
				$all_links[] = $LINK;
				$arMenuCache[$iMenuItem]["LINKS"] = $all_links;
			}

			if(preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $MenuItem[1]))
				$PERMISSION = "Z";
			else
			{
				if(!$bSkipMenuItem)
				{
					foreach($all_links as $tested_link)
					{
						if($tested_link == '')
							continue;
						//"/admin/"
						//"/admin/index.php"
						//"/admin/index.php?module=mail"
						//$tested_link = trim(Rel2Abs($this->MenuDir, $tested_link));
						if(strpos($cur_page, $tested_link) === 0 || strpos($cur_page_no_index, $tested_link) === 0) 
						{
							$SELECTED = true;
							break;
						}

						if(($pos = strpos($tested_link, "?")) !== false) 
						{
							if(($s = substr($tested_link, 0, $pos)) == $cur_page || $s == $cur_page_no_index)
							{
								$params = explode("&", substr($tested_link, $pos+1));
								$bOK = true;
								foreach($params as $param)
								{
									$eqpos = strpos($param, "=");
									$varvalue = "";
									if($eqpos === false)
									{
										$varname = $param;
									}
									elseif($eqpos == 0)
									{
										continue;
									}
									else
									{
										$varname = substr($param, 0, $eqpos);
										$varvalue = urldecode(substr($param, $eqpos+1));
									}

									$globvarvalue = (isset($GLOBALS[$varname])? $GLOBALS[$varname] : "");
									if($globvarvalue != $varvalue)
									{
										$bOK = false;
										break;
									}
								} 

								if($bOK)
								{
									$SELECTED = true;
									break;
								}
							}//if(substr($tested_link, 0, $pos)==$cur_page)
						} //if(($pos = strpos($tested_link, "?"))!==false)
					} //foreach($all_links as $tested_link)
				}

				if($bCached)
					$PERMISSION = $arMenuCache[$iMenuItem]["PERM"];
				else
					$arMenuCache[$iMenuItem]["PERM"] = $PERMISSION = $APPLICATION->GetFileAccessPermission(GetPagePath($LINK), $arUserRights);
			}

			if($SELECTED && !$bMultiSelect)
			{
				$new_len = strlen($tested_link);
				if($new_len > $cur_selected_len)
				{
					if($cur_selected !== -1)
						$result[$cur_selected]['SELECTED'] = false;

					$cur_selected = count($result);
					$cur_selected_len = $new_len;
				}
				else
				{
					$SELECTED = false;
				}
			}

			if(!$bSkipMenuItem)
			{
				$r = Array(
					"TEXT" => $TEXT,
					"LINK" => $LINK,
					"SELECTED" => $SELECTED,
					"PERMISSION" => $PERMISSION,
					"ADDITIONAL_LINKS" => $ADDITIONAL_LINKS,
					"ITEM_TYPE" => $ITEM_TYPE,
					"ITEM_INDEX" => $ITEM_INDEX,
					"PARAMS" => $PARAMS
					);

				$result[] = $r;
			}
		}

		$this->arMenu = $result;

		if($bCacheIsAllowed && !$bCached)
			$GLOBALS["CACHE_MANAGER"]->Set($cache_id, $arMenuCache);

		return true;
	}

	function GetMenuHtmlEx()
	{
		global $USER, $DB, $APPLICATION; // must be!

		if(!$this->RecalcMenu())
			return false;

		// $arMENU - копия массива меню
		// $arMENU_LINK - ссылка на массив меню

		$sMenu = "";
		$MENU_ITEMS = &$this->arMenu;
		$arMENU_LINK = &$this->arMenu;
		$arMENU = $this->arMenu;
		include($this->MenuTemplate);
		$result = $sMenu;

		$bShowButtons = false;
		$sMenuFile = $this->MenuDir.".".$this->type.".menu.php";
		if($APPLICATION->GetShowIncludeAreas())
		{
			$menu_perm = $APPLICATION->GetFileAccessPermission($sMenuFile);
			$templ_perm = $APPLICATION->GetFileAccessPermission($this->template);
			$arIcons = Array();
			if($menu_perm>="W")
				$arIcons[] = Array(
					"URL"=>"/bitrix/admin/fileman_menu_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"])."&path=".urlencode($this->MenuDir)."&name=".$this->type,
					"ICON"=>"menu-edit",
					"TITLE"=>GetMessage("MAIN_MENU_EDIT")
				);
			if($templ_perm>="W" && $USER->IsAdmin())
				$arIcons[] = Array(
					"URL"=>"/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"])."&full_src=Y&path=".urlencode($this->template),
					"ICON"=>"menu-template",
					"TITLE"=>GetMessage("MAIN_MENU_TEMPLATE_EDIT")
				);
			if(count($arIcons) > 0)
			{
				$result = $APPLICATION->IncludeStringBefore().$result;
				$bShowButtons = true;
			}
		}

		if($this->debug)
			$result .= $this->debug->Output($sMenuFile, $sMenuFile);

		if($bShowButtons)
			$result .= $APPLICATION->IncludeStringAfter($arIcons);

		return $result;
	}


	function GetMenuHtml()
	{
		global $USER, $DB, $APPLICATION; // must be!

		if(!$this->RecalcMenu())
			return false;

		// $arMENU - копия массива меню
		// $arMENU_LINK - ссылка на массив меню

		$MENU_ITEMS = &$this->arMenu;
		$arMENU_LINK = &$this->arMenu;
		$arMENU = $this->arMenu;
		for($i=0; $i<count($this->arMenu); $i++)
		{
			$m = $this->arMenu[$i];
			$sMenuBody = "";
			$sMenuProlog = "";
			$sMenuEpilog = "";
			extract($m, EXTR_OVERWRITE);

			// $TEXT - текст меню
			// $LINK - ссылка меню
			// $SELECTED - выбрано ли меню в данный момент
			// $PERMISSION - доступ на страницу указанную в $LINKS
			// $ADDITIONAL_LINKS - дополнительные ссылки для подсветки меню
			// $ITEM_TYPE - "D" - если $LINKS заканчивается на "/" (директория), "P" - иначе (страница)
			// $ITEM_INDEX - порядковый номер пункта меню
			// $PARAMS - параметры пунктов меню

			include($this->MenuTemplate);
			if($ITEM_INDEX==0)
				$sMenuPrologTmp = $sMenuProlog;
			$result.= $sMenuBody;
		}

		$result = $sMenuPrologTmp.$result.$sMenuEpilog;

		$bShowButtons = false;
		$sMenuFile = $this->MenuDir.".".$this->type.".menu.php";
		if($APPLICATION->GetShowIncludeAreas())
		{
			$menu_perm = $APPLICATION->GetFileAccessPermission($sMenuFile);
			$templ_perm = $APPLICATION->GetFileAccessPermission($this->template);
			$arIcons = Array();
			if($menu_perm>="W")
				$arIcons[] = Array(
					"URL"=>"/bitrix/admin/fileman_menu_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"])."&path=".urlencode($this->MenuDir)."&name=".$this->type,
					"ICON"=>"menu-edit",
					"TITLE"=>GetMessage("MAIN_MENU_EDIT")
				);

			if($templ_perm>="W" && $USER->IsAdmin())
				$arIcons[] = Array(
					"URL"=>"/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"])."&full_src=Y&path=".urlencode($this->template),
					"ICON"=>"menu-template",
					"TITLE"=>GetMessage("MAIN_MENU_TEMPLATE_EDIT")
				);
			if(count($arIcons) > 0)
			{
				$result = $APPLICATION->IncludeStringBefore().$result;
				$bShowButtons = true;
			}
		}

		if($this->debug)
			$result .= $this->debug->Output($sMenuFile, $sMenuFile);

		if($bShowButtons)
			$result .= $APPLICATION->IncludeStringAfter($arIcons);

		return $result;
	}
}

class CMenuCustom
{
	var $arItems = Array();

	function AddItem($type="left", $arItem=array())
	{
		if (count($arItem) <= 0)
			return;

		if (!array_key_exists("TEXT", $arItem) || strlen(trim($arItem["TEXT"])) <= 0)
			return;

		if (!array_key_exists("LINK", $arItem) || strlen(trim($arItem["LINK"])) <= 0)
			$arItem["LINK"] = "";

		if (!array_key_exists("SELECTED", $arItem))
			$arItem["SELECTED"] = false;

		if (!array_key_exists("PERMISSION", $arItem))
			$arItem["PERMISSION"] = "R";

		if (!array_key_exists("DEPTH_LEVEL", $arItem))
			$arItem["DEPTH_LEVEL"] = 1;

		if (!array_key_exists("IS_PARENT", $arItem))
			$arItem["IS_PARENT"] = false;

		$this->arItems[$type][] = array(
						"TEXT" => $arItem["TEXT"],
						"LINK" => $arItem["LINK"],
						"SELECTED" => $arItem["SELECTED"],
						"PERMISSION" => $arItem["PERMISSION"],
						"DEPTH_LEVEL" => $arItem["DEPTH_LEVEL"],
						"IS_PARENT" => $arItem["IS_PARENT"],
					);
	}


	function GetItems($type="left")
	{
		if (array_key_exists($type, $this->arItems))
			return $this->arItems[$type];
		else
			return false;
	}

}

global $BX_MENU_CUSTOM;
$BX_MENU_CUSTOM = new CMenuCustom;

?>