<?
IncludeModuleLangFile(__FILE__);
$aMenu = Array();
if ($APPLICATION->GetGroupRight("sale")!="D")
{
	$aMenu = array(
		array(
			"parent_menu" => "global_menu_store",
			"sort" => 100,
			"text" => GetMessage("SALE_ORDERS"),
			"title" => GetMessage("SALE_ORDERS_DESCR"),
			"icon" => "sale_menu_icon_orders",
			"page_icon" => "sale_page_icon_orders",
			"url" => "sale_order.php?lang=".LANGUAGE_ID,
			"more_url" => array(
				"sale_order_detail.php",
				"sale_order_edit.php",
				"sale_order_print.php"
			),
		),
		array(
			"parent_menu" => "global_menu_store",
			"sort" => 110,
			"text" => GetMessage("SM_RENEW"),
			"title" => GetMessage("SM_RENEW_ALT"),
			"icon" => "sale_menu_icon_recurring",
			"page_icon" => "sale_page_icon_recurring",
			"url" => "sale_recurring_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("sale_recurring_edit.php"),
		),
		array(
			"parent_menu" => "global_menu_store",
			"sort" => 120,
			"text" => GetMessage("sale_menu_accounts"),
			"title" => GetMessage("sale_menu_accounts_title"),
			"icon" => "sale_menu_icon_buyers",
			"page_icon" => "sale_page_icon_buyers",
			"url" => "sale_account_index.php?lang=".LANGUAGE_ID,
			"items_id" => "menu_sale_buyers",
			"items" => array(
				array(
					"text" => GetMessage("SM_ACCOUNTS"),
					"url" => "sale_account_admin.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_account_edit.php"),
					"title" => GetMessage("SM_ACCOUNTS_ALT")
				),
				array(
					"text" => GetMessage("SM_TRANSACT"),
					"url" => "sale_transact_admin.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_transact_edit.php"),
					"title" => GetMessage("SM_TRANSACT")
				),
				array(
					"text" => GetMessage("SM_CCARDS"),
					"url" => "sale_ccards_admin.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_ccards_edit.php"),
					"title" => GetMessage("SM_CCARDS")
				),
			)
		),
		array(
			"parent_menu" => "global_menu_store",
			"sort" => 125,
			"text" => GetMessage("SM1_AFFILIATES"),
			"title" => GetMessage("SM1_SHOP_AFFILIATES"),
			"icon" => "sale_menu_icon_buyers",
			"page_icon" => "sale_page_icon_buyers",
			"url" => "sale_affiliate.php?lang=".LANGUAGE_ID,
			"items_id" => "menu_sale_affiliates",
			"items" => array(
				array(
					"text" => GetMessage("SM1_AFFILIATES_CALC"),
					"url" => "sale_affiliate_calc.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("SM1_AFFILIATES_CALC_ALT")
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES"),
					"url" => "sale_affiliate.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_edit.php"),
					"title" => GetMessage("SM1_SHOP_AFFILIATES")
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_TRAN"),
					"url" => "sale_affiliate_transact.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("SM1_AFFILIATES_TRAN_ALT")
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_PLAN"),
					"url" => "sale_affiliate_plan.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_plan_edit.php"),
					"title" => GetMessage("SM1_AFFILIATES_PLAN_ALT")
				),
				array(
					"text" => GetMessage("SM1_AFFILIATES_TIER"),
					"url" => "sale_affiliate_tier.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_affiliate_tier_edit.php"),
					"title" => GetMessage("SM1_AFFILIATES_TIER_ALT")
				),
			)
		),
		array(
			"parent_menu" => "global_menu_store",
			"sort" => 126,
			"text" => GetMessage("SM1_STATISTIC"),
			"title" => GetMessage("SM1_SHOP_STATISTIC"),
			"icon" => "sale_menu_icon_statistic",
			"page_icon" => "sale_page_icon_statistic",
			"url" => "sale_stat.php?lang=".LANGUAGE_ID."&set_default=Y",
			"items_id" => "menu_sale_stat",
			"items" => array(
				array(
					"text" => GetMessage("SM1_STAT"),
					"url" => "sale_stat.php?lang=".LANGUAGE_ID."&set_default=Y",
					"more_url" => array(),
					"title" => GetMessage("SM1_STAT_ALT")
				),
				array(
					"text" => GetMessage("SM1_STAT_PRODUCTS"),
					"url" => "sale_stat_products.php?lang=".LANGUAGE_ID."&set_default=Y",
					"more_url" => array(),
					"title" => GetMessage("SM1_STAT_PRODUCTS_ALT")
				),
				array(
					"text" => GetMessage("SM1_STAT_GRAPH"),
					"url" => "sale_stat_graph_index.php?lang=".LANGUAGE_ID."&set_default=Y",
					"title" => GetMessage("SM1_STAT_GRAPH_DESCR"),
					"items_id" => "menu_sale_stat_graph",
					"items" => array(
						array(
							"text" => GetMessage("SM1_STAT_GRAPH_QUANTITY"),
							"url" => "sale_stat_graph_index.php?lang=".LANGUAGE_ID."&set_default=Y",
							"title" => GetMessage("SM1_STAT_GRAPH_QUANTITY_DESCR")
						),
						array(
							"text" => GetMessage("SM1_STAT_GRAPH_MONEY"),
							"url" => "sale_stat_graph_money.php?lang=".LANGUAGE_ID."&set_default=Y",
							"title" => GetMessage("SM1_STAT_GRAPH_MONEY_DESCR")
						),
						/*
						array(
							"text" => GetMessage("SM1_STAT_GRAPH_STATUS"),
							"url" => "sale_stat_graph_status.php?lang=".LANGUAGE_ID."&set_default=Y",
							"title" => GetMessage("SM1_STAT_GRAPH_STATUS_DESCR")
						),
						*/
					),
				),

			),
		),

		array(
			"parent_menu" => "global_menu_store",
			"sort" => 130,
			"text" => GetMessage("SM_SETTINGS"),
			"url"  => "sale_settings.php?lang=".LANGUAGE_ID,
			"title"=> GetMessage("SM_SETTINGS"),
			"icon" => "sale_menu_icon",
			"page_icon" => "sale_page_icon",
			"items_id" => "menu_sale_settings",
			"items" => array(
				array(
					"text" => GetMessage("SM_DISCOUNT"),
					"url" => "sale_discount.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_discount_edit.php"),
					"title" => GetMessage("SALE_DISCOUNT_DESCR")
				),
				array(
					"text" => GetMessage("SALE_DELIVERY"),
					"url" => "sale_delivery_index.php?lang=".LANGUAGE_ID,
					"title" => GetMessage("SALE_DELIVERY_DESCR"),
					"items_id" => "menu_sale_delivery",
					"items" => array(
						array(
							"text" => GetMessage("SALE_DELIVERY_OLD"),
							"url" => "sale_delivery.php?lang=".LANGUAGE_ID,
							"page_icon" => "sale_page_icon",
							"more_url" => array("sale_delivery_edit.php"),
							"title" => GetMessage("SALE_DELIVERY_OLD_DESCR")
						),
						array(
							"text" => GetMessage("SALE_DELIVERY_HANDLERS"),
							"url" => "sale_delivery_handlers.php?lang=".LANGUAGE_ID,
							"page_icon" => "sale_page_icon",
							"more_url" => array("sale_delivery_handler_edit.php"),
							"title" => GetMessage("SALE_DELIVERY_HANDLERS_DESCR")
						),
					),
				),
				array(
					"text" => GetMessage("SALE_PAY_SYS"),
					"url" => "sale_pay_system.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_pay_system_edit.php"),
					"title" => GetMessage("SALE_PAY_SYS_DESCR")
				),
				array(
					"text" => GetMessage("SALE_PERSON_TYPE"),
					"url" => "sale_person_type.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_person_type_edit.php"),
					"title" => GetMessage("SALE_PERSON_TYPE_DESCR")
				),
				array(
					"text" => GetMessage("SALE_STATUS"),
					"url" => "sale_status.php?lang=".LANGUAGE_ID,
					"more_url" => array("sale_status_edit.php"),
					"title" => GetMessage("SALE_STATUS_DESCR")
				),
				array(
					"text" => GetMessage("SALE_ORDER_PROPS"),
					"title" => GetMessage("SALE_ORDER_PROPS_DESCR"),
					"url" => "sale_order_props.php?lang=".LANGUAGE_ID,
					"items_id" => "menu_sale_properties",
					"items"=>array(
						array(
							"text" => GetMessage("sale_menu_properties"),
							"url" => "sale_order_props.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_order_props_edit.php"),
							"title" => GetMessage("sale_menu_properties_title")
						),
						array(
							"text" => GetMessage("SALE_ORDER_PROPS_GR"),
							"url" => "sale_order_props_group.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_order_props_group_edit.php"),
							"title" => GetMessage("SALE_ORDER_PROPS_GR_DESCR")
						),
						/*
						array(
							"text" => GetMessage("sale_menu_uf_properties"),
							"url" => "sale_order_uf_props.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_order_uf_props_edit.php"),
							"title" => GetMessage("sale_menu_uf_properties_title")
						),
						*/
					),
				),
				array(
					"text" => GetMessage("SALE_LOCATION"),
					"title" => GetMessage("SALE_LOCATION_DESCR"),
					"url" => "sale_location_admin.php?lang=".LANGUAGE_ID,
					"items_id" => "menu_sale_locations",
					"items"=>array(
						array(
							"text" => GetMessage("sale_menu_locations"),
							"url" => "sale_location_admin.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_location_edit.php"),
							"title" => GetMessage("sale_menu_locations_title")
						),
						array(
							"text" => GetMessage("SALE_LOCATION_GROUPS"),
							"url" => "sale_location_group_admin.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_location_group_edit.php"),
							"title" => GetMessage("SALE_LOCATION_GROUPS_DESCR")
						),
						array(
							"text" => GetMessage("SALE_LOCATION_IMPORT"),
							//"url" => "javascript:WizardWindow.Open('bitrix:sale.locations', '".bitrix_sessid()."')",
							"url" => "sale_location_import.php?lang=".LANGUAGE_ID,
							"title" => GetMessage("SALE_LOCATION_IMPORT_DESCR")
						),
					),
				),
				array(
					"text" => GetMessage("SALE_TAX"),
					"title" => GetMessage("SALE_TAX_DESCR"),
					"url" => "sale_tax.php?lang=".LANGUAGE_ID,
					"items_id" => "menu_sale_taxes",
					"items"=>array(
						array(
							"text" => GetMessage("sale_menu_taxes"),
							"url" => "sale_tax.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_tax_edit.php"),
							"title" => GetMessage("sale_menu_taxes_title")
						),
						array(
							"text" => GetMessage("SALE_TAX_RATE"),
							"url" => "sale_tax_rate.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_tax_rate_edit.php"),
							"title" => GetMessage("SALE_TAX_RATE_DESCR")
						),
						array(
							"text" => GetMessage("SALE_TAX_EX"),
							"url" => "sale_tax_exempt.php?lang=".LANGUAGE_ID,
							"more_url" => array("sale_tax_exempt_edit.php"),
							"title" => GetMessage("SALE_TAX_EX_DESCR")
						)
					),
				),
				array(
					"text" => GetMessage("MAIN_MENU_1C_INTEGRATION"),
					"url" => "1c_admin.php?lang=".LANGUAGE_ID,
					"title" => GetMessage("MAIN_MENU_1C_INTEGRATION_TITLE"),
					"more_url" => array("1c_admin.php"),
				),
				array(
					"text" => GetMessage("MAIN_MENU_REPORT_EDIT"),
					"url" => "sale_report_edit.php?lang=".LANGUAGE_ID,
					"title" => GetMessage("MAIN_MENU_REPORT_EDIT_TITLE"),
					"more_url" => array("sale_report_edit.php"),
				),

			)
		),
	);
	return $aMenu;
}
return $false;
?>
