<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "VOTE_NEW",
		"NAME" => GetMessage("VOTE_NEW_NAME"),
		"DESCRIPTION" => GetMessage("VOTE_NEW_DESC"),
	));

	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "VOTE_NEW",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
			"BCC" => "",
			"SUBJECT" => GetMessage("VOTE_NEW_SUBJECT"),
			"MESSAGE" => GetMessage("VOTE_NEW_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>