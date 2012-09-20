<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Р—Р°РґР°Р№С‚Рµ РІРѕРїСЂРѕСЃ");
?>
 
<p>РЈРІР°Р¶Р°РµРјС‹Рµ РїРѕРєСѓРїР°С‚РµР»Рё!</p>
 
<p>РџСЂРµР¶РґРµ С‡РµРј Р·Р°РґР°С‚СЊ СЃРІРѕР№ РІРѕРїСЂРѕСЃ, РѕР±СЂР°С‚РёС‚Рµ РІРЅРёРјР°РЅРёРµ РЅР° СЂР°Р·РґРµР» <a href="../faq/">РџРѕРјРѕС‰СЊ РїРѕРєСѓРїР°С‚РµР»СЋ</a>. Р’РѕР·РјРѕР¶РЅРѕ, С‚Р°Рј СѓР¶Рµ РµСЃС‚СЊ РёСЃС‡РµСЂРїС‹РІР°СЋС‰Р°СЏ РёРЅС„РѕСЂРјР°С†РёСЏ РїРѕ СЂРµС€РµРЅРёСЋ РІР°С€РµР№ РїСЂРѕР±Р»РµРјС‹.</p>

<?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback",
	"",
	Array(
		"USE_CAPTCHA" => "Y",
		"OK_TEXT" => "РЎРїР°СЃРёР±Рѕ, РІР°С€Рµ СЃРѕРѕР±С‰РµРЅРёРµ РїСЂРёРЅСЏС‚Рѕ.",
		"EMAIL_TO" => "sale@temp.t5.ru",
		"REQUIRED_FIELDS" => array(),
		"EVENT_MESSAGE_ID" => array()
	),
false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>