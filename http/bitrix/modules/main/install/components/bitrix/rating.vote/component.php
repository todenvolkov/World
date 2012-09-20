<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arAllowVote = CRatings::CheckAllowVote($arParams);

if ($arAllowVote['RESULT'] && $_POST['RATING_VOTE'] == 'Y'
	&& $arParams['ENTITY_TYPE_ID'] == $_POST['RATING_VOTE_TYPE_ID']
	&& $arParams['ENTITY_ID'] == $_POST['RATING_VOTE_ENTITY_ID'])
{
	$arAdd = array(
		"ENTITY_TYPE_ID" => $_REQUEST['RATING_VOTE_TYPE_ID'],
		"ENTITY_ID" 	 => intval($_REQUEST['RATING_VOTE_ENTITY_ID']),
		"VALUE" 		 => $_REQUEST['RATING_VOTE_ACTION'] == 'plus' ? 1 : -1,
		"USER_IP" 		 => $_SERVER['REMOTE_ADDR'],
		"USER_ID" 		 => $USER->GetId(), 	
	);
	CRatings::AddRatingVote($arAdd);
} 
else 
{
	$arResult['ENTITY_TYPE_ID']	= $arParams['ENTITY_TYPE_ID'];
	$arResult['ENTITY_ID'] 		= IntVal($arParams['ENTITY_ID']);
	$arResult["TOTAL_VALUE"] = floatval($arParams['TOTAL_VALUE']);
	$arResult["TOTAL_VOTES"] = IntVal($arParams['TOTAL_VOTES']);
	$arResult["TOTAL_POSITIVE_VOTES"] = IntVal($arParams['TOTAL_POSITIVE_VOTES']);
	$arResult["TOTAL_NEGATIVE_VOTES"] = IntVal($arParams['TOTAL_NEGATIVE_VOTES']);
	$arResult['USER_HAS_VOTED']	= in_array($arParams['USER_HAS_VOTED'], array('Y', 'N')) ? $arParams['USER_HAS_VOTED'] : 'N';
	$arResult['ALLOW_VOTE']	= $arAllowVote;
	
	if (!isset($arParams['TOTAL_VALUE']) || 
		!isset($arParams['TOTAL_VOTES']) ||
		!isset($arParams['TOTAL_POSITIVE_VOTES']) ||
		!isset($arParams['TOTAL_NEGATIVE_VOTES']) ||
		!isset($arParams['USER_HAS_VOTED']))
	{
		$arComponentVoteResult  = CRatings::GetRatingVoteResult($arResult['ENTITY_TYPE_ID'], $arResult['ENTITY_ID']);
		if (!empty($arComponentVoteResult))
		{
			$arResult['TOTAL_VALUE'] = $arComponentVoteResult['TOTAL_VALUE'];
			$arResult['TOTAL_VOTES'] = $arComponentVoteResult['TOTAL_VOTES'];
			$arResult['TOTAL_POSITIVE_VOTES'] = $arComponentVoteResult['TOTAL_POSITIVE_VOTES'];
			$arResult['TOTAL_NEGATIVE_VOTES'] = $arComponentVoteResult['TOTAL_NEGATIVE_VOTES'];
			$arResult['USER_HAS_VOTED'] = $arComponentVoteResult['USER_HAS_VOTED'];
		}
	}
	
	$arResult['VOTE_AVAILABLE'] = 'Y';
	if (!$arResult['ALLOW_VOTE']['RESULT'])
		$arResult['VOTE_AVAILABLE'] = 'N';

	$arResult['VOTE_RAND']	= time()+rand(0, 1000);
	$arResult['VOTE_TITLE'] = $arResult['TOTAL_VOTES'] == 0 ? GetMessage("RATING_COMPONENT_NO_VOTES") : sprintf(GetMessage("RATING_COMPONENT_DESC"), $arResult['TOTAL_VOTES'], $arResult['TOTAL_POSITIVE_VOTES'], $arResult['TOTAL_NEGATIVE_VOTES']);
	$arResult['VOTE_ID'] 	= $arResult['ENTITY_TYPE_ID'].'-'.$arResult['ENTITY_ID'].'-'.$arResult['VOTE_RAND'];	
			
	CUtil::InitJSCore(array('ajax'));
	
	if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
		$this->IncludeComponentTemplate();
	
	return $arResult;
}
?>