<?
IncludeModuleLangFile(__FILE__);

//if ($APPLICATION->GetGroupRight("catalog") != "D")
//if ($USER->CanDoOperation('catalog_read') || $APPLICATION->GetGroupRight("catalog") != "D")
//{
	$bViewAll = $USER->CanDoOperation('catalog_read');

	global $DBType, $adminMenu;

	$expItems = array(); 
	$impItems = array();
	$page = $APPLICATION->GetCurPage();

	if(($bViewAll || $USER->CanDoOperation('catalog_export_edit') || $USER->CanDoOperation('catalog_export_exec')) && method_exists($adminMenu, "IsSectionActive"))
	{
		if($adminMenu->IsSectionActive("mnu_catalog_exp") || $page == "/bitrix/admin/cat_export_setup.php" || $page == "/bitrix/admin/cat_exec_exp.php")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog_export.php");
			$ce_db_res = CCatalogExport::GetList(array("NAME"=>"ASC", "ID"=>"ASC"), array("IN_MENU"=>"Y"));
			while ($ce_ar_res = $ce_db_res->Fetch())
			{
				$expItems[] = Array(
					"text" 	=> htmlspecialchars((strlen($ce_ar_res["NAME"])>0 ? $ce_ar_res["NAME"] : $ce_ar_res["FILE_NAME"])),
					"url" 	=>	"cat_exec_exp.php?ACT_FILE=".$ce_ar_res["FILE_NAME"]."&ACTION=EXPORT&PROFILE_ID=".$ce_ar_res["ID"]."&lang=".LANGUAGE_ID,
					"title"=>GetMessage("CAM_EXPORT_DESCR")." &quot;".htmlspecialchars($ce_ar_res["NAME"])."&quot;",
					"readonly" => !$USER->CanDoOperation('catalog_export_exec'),
				);
			}
		}
	}

	if(($bViewAll || $USER->CanDoOperation('catalog_import_edit') || $USER->CanDoOperation('catalog_import_exec')) && method_exists($adminMenu, "IsSectionActive"))
	{
		if($adminMenu->IsSectionActive("mnu_catalog_imp") || $page == "/bitrix/admin/cat_import_setup.php" || $page == "/bitrix/admin/cat_exec_imp.php")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog_import.php");
			$ce_db_res = CCatalogImport::GetList(array("NAME"=>"ASC", "ID"=>"ASC"), array("IN_MENU"=>"Y"));
			while ($ce_ar_res = $ce_db_res->Fetch())
			{
				$impItems[] = Array(
					"text"	=> htmlspecialchars((strlen($ce_ar_res["NAME"])>0 ? $ce_ar_res["NAME"] : $ce_ar_res["FILE_NAME"])),
					"url"	=> "cat_exec_imp.php?ACT_FILE=".$ce_ar_res["FILE_NAME"]."&ACTION=IMPORT&PROFILE_ID=".$ce_ar_res["ID"]."&lang=".LANGUAGE_ID,
					"title"=>GetMessage("CAM_IMPORT_DESCR")." \"".htmlspecialchars($ce_ar_res["NAME"])."\"",
					"readonly" => !$USER->CanDoOperation('catalog_import_exec'),
				);
			}
		}
	}

	$arSubItems = array();
	
	if ($bViewAll || $USER->CanDoOperation('catalog_discount'))
	{
		$arSubItems[] = array(
			"text" => GetMessage("CM_DISCOUNTS"),
			"url" => "cat_discount_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_discount_edit.php"),
			"title" => GetMessage("CM_DISCOUNTS_ALT"),
			"readonly" => !$USER->CanDoOperation('catalog_discount'),
		);
		
		$arSubItems[] = array(
			"text" => GetMessage("CM_COUPONS"),
			"url" => "cat_discount_coupon.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_discount_coupon_edit.php"),
			"title" => GetMessage("CM_COUPONS_ALT"),
			"readonly" => !$USER->CanDoOperation('catalog_discount'),
		);
	
		$arSubItems[] = array(
			"text" => GetMessage("EXTRA"),
			"url" => "cat_extra.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_extra_edit.php"),
			"title" => GetMessage("EXTRA_ALT"),
			"readonly" => !$USER->CanDoOperation('catalog_price'),
		);
	}
	
	if ($bViewAll || $USER->CanDoOperation('catalog_group'))
	{
		$arSubItems[] = array(
			"text" => GetMessage("GROUP"),
			"url" => "cat_group_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_group_edit.php"),
			"title" => GetMessage("GROUP_ALT"),
			"readonly" => !$USER->CanDoOperation('catalog_group'),
		);
	}
	
	if ($bViewAll || $USER->CanDoOperation('catalog_vat'))
	{
		$arSubItems[] = array(
			"text" => GetMessage("VAT"),
			"url" => "cat_vat_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("cat_vat_edit.php"),
			"title" => GetMessage("VAT_ALT"),
			"readonly" => !$USER->CanDoOperation('catalog_vat'),
		);
	}
	
	if ($bViewAll || $USER->CanDoOperation('catalog_export_edit') || $USER->CanDoOperation('catalog_export_exec'))
	{
		$arSubItems[] = array(
			"text" => GetMessage("SETUP_UNLOAD_DATA"),
			"url" => "cat_export_setup.php?lang=".LANGUAGE_ID,
			"more_url" => array(
				"cat_export_setup_report.php",
			),
			"title" => GetMessage("SETUP_UNLOAD_DATA_ALT"),
			"dynamic"=>true,
			"module_id"=>"catalog",
			"items_id"=>"mnu_catalog_exp",
			"readonly" => !$USER->CanDoOperation('catalog_export_edit') && !$USER->CanDoOperation('catalog_export_exec'),
			"items"=>$expItems
		);
	}
	
	if ($bViewAll || $USER->CanDoOperation('catalog_import_edit') || $USER->CanDoOperation('catalog_import_exec'))
	{
		$arSubItems[] = array(
				"text" => GetMessage("SETUP_LOAD_DATA"),
				"url" => "cat_import_setup.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("SETUP_LOAD_DATA_ALT"),
				"dynamic"=>true,
				"module_id"=>"catalog",
				"items_id"=>"mnu_catalog_imp",
				"readonly" => !$USER->CanDoOperation('catalog_import_edit') && !$USER->CanDoOperation('catalog_import_exec'),
				"items"=>$impItems
			);
	}
	
	if (count($arSubItems) > 0)
	{
		$aMenu = array(
			"parent_menu" => "global_menu_store",
			"section" => "catalog",
			"sort" => 200,
			"text" => GetMessage("CATALOG_CONTROL"),
			"title" => GetMessage("CATALOG_MNU_TITLE"),
			"url" => "cat_index.php?lang=".LANGUAGE_ID,
			"icon" => "catalog_menu_icon",
			"page_icon" => "catalog_page_icon",
			"items_id" => "mnu_catalog",
			"items" => $arSubItems,
		);
		return $aMenu;
	}
	else
		return false;
//}
//return false;
?>