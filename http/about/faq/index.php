<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("РџРѕРјРѕС‰СЊ РїРѕРєСѓРїР°С‚РµР»СЋ");
?><p>Р’ СЌС‚РѕРј СЂР°Р·РґРµР»Рµ Р’С‹ РјРѕР¶РµС‚Рµ РЅР°Р№С‚Рё РѕС‚РІРµС‚С‹ РЅР° РјРЅРѕРіРёРµ РІРѕРїСЂРѕСЃС‹, РєР°СЃР°СЋС‰РёРµСЃСЏ СЂР°Р±РѕС‚С‹ РЅР°С€РµРіРѕ СЃР°Р№С‚Р°. Р•СЃР»Рё Р’С‹ РЅРµ РЅР°С€Р»Рё РёРЅС‚РµСЂРµСЃСѓСЋС‰РµР№ Р’Р°СЃ РёРЅС„РѕСЂРјР°С†РёРё, С‚Рѕ РјРѕР¶РµС‚Рµ РѕС‚РїСЂР°РІРёС‚СЊ РЅР°Рј Р·Р°РїСЂРѕСЃ СЃ РїРѕРјРѕС‰СЊСЋ <a href="../contacts/">С„РѕСЂРјС‹ РѕР±СЂР°С‚РЅРѕР№ СЃРІСЏР·Рё</a>.</p>
 <?$APPLICATION->IncludeComponent("bitrix:support.faq.element.list", ".default", array(
		"IBLOCK_TYPE" => "services",
		"IBLOCK_ID" => "2",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"AJAX_MODE" => "N",
		"SECTION_ID" => "1",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>