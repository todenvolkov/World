<?
IncludeModuleLangFile(__FILE__);

function tags_prepare($sText, $site_id = false)
{
	static $arEvents = false;
	if($arEvents === false)
	{
		$arEvents = array();
		$rsEvents = GetModuleEvents("search", "OnSearchGetTag");
		while($arEvent = $rsEvents->Fetch())
			$arEvents[] = $arEvent;
	}
	$arResult = array();
	$arTags = explode(",", $sText);
	foreach($arTags as $tag)
	{
		$tag = trim($tag);
		if(strlen($tag))
		{
			foreach($arEvents as $arEvent)
				$tag = ExecuteModuleEventEx($arEvent, array($tag));

			if(strlen($tag))
				$arResult[$tag] = $tag;
		}
	}
	return $arResult;
}

function TagsShowScript()
{
	static $bShown = false;
	if(!$bShown && ($_REQUEST["mode"] != 'excel'))
	{
		$bShown = true;
		$bOpera = (strpos($_SERVER["HTTP_USER_AGENT"], "Opera") !== false);
		echo "<script type=\"text/javascript\" src=\"/bitrix/js/search/tags.js".($bOpera? '':'?'.filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/search/tags.js'))."\"></script>";
	}
}

function InputTags($sName="", $sValue="", $arSites=array(), $sHTML="", $sId="")
{
	if(!$sId)
		$sId = GenerateUniqId($sName);
	TagsShowScript();
	$order = class_exists("cuseroptions")? CUserOptions::GetOption("search_tags", "order", "CNT"): "CNT";
	return '<input name="'.htmlspecialchars($sName).'" id="'.htmlspecialchars($sId).'" type="text" autocomplete="off" value="'.htmlspecialcharsex($sValue).'" onfocus="'.htmlspecialchars('window.oObject[this.id] = new JsTc(this, '.CUtil::PhpToJSObject($arSites).');').'" '.$sHTML.'/><input type="checkbox" id="ck_'.$sId.'" name="ck_'.htmlspecialchars($sName).'" '.($order=="NAME"? "checked": "").' title="'.GetMessage("SEARCH_TAGS_SORTING_TIP").'">';
}
?>