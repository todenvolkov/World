<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
				</div>
			</div>
		</div>
		<div id="space-for-footer"></div>
	</div>

	<div id="footer-wrapper">
		<div id="footer">
			<div id="copyright"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/copyright.php"), false);?></div>

			<div id="footer-links">
			<?
			$APPLICATION->IncludeComponent("bitrix:menu", "bottom", array(
				"ROOT_MENU_TYPE" => "bottom",
				"MAX_LEVEL" => "1",
				),
				false
			);
			?>
			</div>
			<div id="footer-design"><?=GetMessage("FOOTER_DISIGN")?></div>
		</div>
	</div>
	<?
	if ($APPLICATION->GetProperty("CATALOG_COMPARE_LIST", false) == false && IsModuleInstalled('iblock'))
	{
		$arFilter = Array("TYPE"=>"catalog", "SITE_ID"=>SITE_ID);
		$obCache = new CPHPCache;
		if($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog/active"))
		{
			$arIBlocks = $obCache->GetVars();
		}
		elseif(CModule::IncludeModule("iblock") && $obCache->StartDataCache())
		{

			$arIBlocks = array();
			$dbRes = CIBlock::GetList(Array(), $arFilter);
			$dbRes = new CIBlockResult($dbRes);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache("/iblock/catalog/active");
				
				while($arIBlock = $dbRes->GetNext())
				{
					$CACHE_MANAGER->RegisterTag("iblock_id_".$arIBlock["ID"]);

					if($arIBlock["ACTIVE"] == "Y")
						$arIBlocks[$arIBlock["ID"]] = $arIBlock;
				}

				$CACHE_MANAGER->RegisterTag("iblock_id_new");
				$CACHE_MANAGER->EndTagCache();
			}
			else
			{
				while($arIBlock = $dbRes->GetNext())
				{
					if($arIBlock["ACTIVE"] == "Y")
						$arIBlocks[$arIBlock["ID"]] = $arIBlock;
				}
			}

			$obCache->EndDataCache($arIBlocks);
		}
		else
		{
			$arIBlocks = array();
		}

		if(count($arIBlocks) == 1)
		{
			foreach($arIBlocks as $iblock_id => $arIBlock)
				$APPLICATION->IncludeComponent(
					"bitrix:catalog.compare.list",
					"store",
					Array(
						"IBLOCK_ID" => $iblock_id,
						"COMPARE_URL" => $arIBlock["LIST_PAGE_URL"]."compare/"
					),
					false,
					Array("HIDE_ICONS" => "Y")
				);
		}
	}

	?>
</body>
</html>