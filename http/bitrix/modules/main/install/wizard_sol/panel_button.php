<?
IncludeModuleLangFile(__FILE__);
class CWizardSolPanel
{
	function ShowPanel()
	{
		if($GLOBALS["USER"]->IsAdmin())
		{
			$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/themes/.default/solpanel.css"); 

			if($_REQUEST['add_new_site_sol']=='sol' && check_bitrix_sessid())
			{
				$dbrSites = CSite::GetList($by, $ord);
				$arSitesID = Array();
				$arSitesPath = Array();
				$siteCnt = 0;
				while($arSite = $dbrSites->Fetch())
				{
					if($arSite["ACTIVE"]=="Y")
						$siteCnt++;

					$arSitesID[] = strtolower($arSite["ID"]);
					$arSitesPath[] = strtolower($arSite["PATH"]);
				}

				while(true)
				{
					$newSiteID = chr(rand(ord("a"), ord("z"))).chr(rand(ord("a"), ord("z")));
					if(!in_array($newSiteID, $arSitesID) && !in_array("/site".$newSiteID."/", $arSitesPath) && !file_exists($_SERVER['DOCUMENT_ROOT']."/site".$newSiteID))
						break;
				}
				
				$arFields = array(
					"LID"				=> $newSiteID, 
					"ACTIVE"			=> "Y",
					"SORT"				=> 100,
					"DEF"				=> "N",
					"NAME"				=> $newSiteID,
					"DIR"				=> "/site_".$newSiteID."/",
					"FORMAT_DATE"		=> FORMAT_DATE,
					"FORMAT_DATETIME"	=> FORMAT_DATETIME,
					"CHARSET"			=> SITE_CHARSET,
					"SITE_NAME"			=> $newSiteID,
					"SERVER_NAME"		=> $_SERVER["SERVER_NAME"],
					"EMAIL"				=> COption::GetOptionString("main", "email_from"),
					"LANGUAGE_ID"		=> LANGUAGE_ID,
					"DOC_ROOT"			=> "",
				);

				$obSite = new CSite;
				$result = $obSite->Add($arFields);
				if ($result)
				{
					CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/site_".$newSiteID."/");
					$indexContent = '<'.'?'.
						'define("WIZARD_DEFAULT_SITE_ID", "'.$newSiteID.'");'.
						'define("WIZARD_DEFAULT_TONLY", true);'.
						'define("PRE_LANGUAGE_ID","'.LANGUAGE_ID.'");'.
						'define("PRE_INSTALL_CHARSET","'.SITE_CHARSET.'");'.
						'include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/wizard.php");'.
						'?'.'>';

					$handler = fopen($_SERVER["DOCUMENT_ROOT"]."/site_".$newSiteID."/index.php","wb");
					fwrite($handler, $indexContent);
					fclose($handler);
					
					LocalRedirect("/site_".$newSiteID."/");
				}
				else 
				{
					echo $obSite->LAST_ERROR; 
				}
			}

			if($_REQUEST['delete_button_sol']=='sol' && check_bitrix_sessid())
			{
				UnRegisterModuleDependences('main', 'OnBeforeProlog', 'main', 'CWizardSolPanel', 'ShowPanel', '/modules/main/install/wizard_sol/panel_button.php');
				LocalRedirect($GLOBALS['APPLICATION']->GetCurPage());
			}

			$arMenu = Array(
				Array(		
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape(SITE_DIR)."?add_new_site_sol=sol&".bitrix_sessid_get()."')",
					"ICON" => "wizard",
					"TEXT" => "<b>".GetMessage("SOL_BUTTON_TEST_TEXT", Array("#BR#" => " "))."</b>",
					"TITLE" => GetMessage("SOL_BUTTON_TEST_TITLE"),
				),
			);

	 		$arMenu[] = Array(		
				"ACTION" => "if(confirm('".AddSlashes(GetMessage("SOL_BUTTON_CONFIRM_W2"))."')) jsUtils.Redirect([], '".CUtil::JSEscape(SITE_DIR)."?delete_button_sol=sol&".bitrix_sessid_get()."');",
				"ICON" => "wizard-clear",
				"TEXT" => GetMessage("SOL_BUTTON_DEL_TEXT"),
				"TITLE" => GetMessage("SOL_BUTTON_DEL_TITLE"),
			);

			$arSites = array();	
			$dbrSites = CSite::GetList($by, $ord, Array("ACTIVE"=>"Y"));
			while($arSite = $dbrSites->GetNext())
			{
		 		$arSites[] = Array(		
					"ACTION" => "jsUtils.Redirect([], '".CUtil::JSEscape($arSite["DIR"])."');",
					"ICON" => ($arSite["LID"]==SITE_ID? "checked":""),
					"TEXT" => $arSite["NAME"],
					"TITLE" => GetMessage("SOL_BUTTON_GOTOSITE")." ".$arSite["NAME"],
				);
			}
	 		$arMenu[] = Array("SEPARATOR"=>true);
	 		$arMenu[] = Array(		
				"TEXT" => GetMessage("SOL_BUTTON_GOTOSITE"),
				"MENU" => $arSites,
			);

			$GLOBALS["APPLICATION"]->AddPanelButton(array(
				"HREF" => SITE_DIR."?add_new_site_sol=sol&".bitrix_sessid_get(),
				"ID" => "solutions_wizard",
				"ICON" => "bx-panel-install-solution-icon",
                "TYPE" => "BIG",
				"ALT" => GetMessage("SOL_BUTTON_TEST_TITLE"),
				"TEXT" => GetMessage("SOL_BUTTON_TEST_TEXT"),
				"MAIN_SORT" => 2500,
				"SORT" => 20,
				"MENU" => $arMenu,
				'HINT' => array(
					'TITLE' => str_replace('#BR#', ' ', GetMessage("SOL_BUTTON_TEST_TEXT")),
					'TEXT' => GetMessage('SOL_BUTTON_TEST_TEXT_HINT')
				),
				'HINT_MENU' => array(
					'TITLE' => str_replace('#BR#', ' ', GetMessage("SOL_BUTTON_TEST_TEXT")),
					'TEXT' => GetMessage('SOL_BUTTON_TEST_MENU_HINT')
				)
			));
		}

	}
}
?>