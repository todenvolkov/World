<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult['VOTE_AVAILABLE'] == 'Y')
{
	if ($arResult['USER_HAS_VOTED'] == 'N')
	{
		?>
			<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>" class="rating-vote">
				<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-result" class="rating-vote-result rating-vote-result-<?=($arResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus')?>" title="<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_TITLE']))?>"> <?=htmlspecialchars($arResult['TOTAL_VALUE'])?></span>
				<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-plus" class="rating-vote-plus"  onclick="RatingVoting('<?=CUtil::JSEscape(htmlspecialchars($arResult['ENTITY_TYPE_ID']))?>', '<?=CUtil::JSEscape(htmlspecialchars($arResult['ENTITY_ID']))?>',  '<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_RAND']))?>', 'plus');return false;" title="<?=GetMessage("RATING_COMPONENT_PLUS")?>" href="#plus"></a>&nbsp;<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-minus" class="rating-vote-minus" onclick="RatingVoting('<?=CUtil::JSEscape(htmlspecialchars($arResult['ENTITY_TYPE_ID']))?>', '<?=CUtil::JSEscape(htmlspecialchars($arResult['ENTITY_ID']))?>',  '<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_RAND']))?>', 'minus');return false;" title="<?=GetMessage("RATING_COMPONENT_MINUS")?>" href="#minus"></a>
			</span> 
		<?
	}
	else
	{
		?>
			<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>" class="rating-vote rating-vote-disabled">
				<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-result" class="rating-vote-result rating-vote-result-<?=($arResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus')?>" title="<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_TITLE']))?>"> <?=htmlspecialchars($arResult['TOTAL_VALUE'])?></span>
				<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-plus" class="rating-vote-plus"  onclick="return false;" title="<?=GetMessage('RATING_COMPONENT_HAS_VOTED')?>" href="#plus"></a>&nbsp;<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-minus" class="rating-vote-minus" onclick="return false;" title="<?=GetMessage('RATING_COMPONENT_HAS_VOTED')?>" href="#minus"></a>
			</span> 
		<?
	}
} 
else
{
	if ($arResult['ALLOW_VOTE']['ERROR_TYPE'] == 'COUNT_VOTE') 
	{
		?>
			<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>" class="rating-vote rating-vote-disabled">
				<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-result" class="rating-vote-result rating-vote-result-<?=($arResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus')?>" title="<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_TITLE']))?>"> <?=htmlspecialchars($arResult['TOTAL_VALUE'])?></span>
				<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-plus" class="rating-vote-plus"  onclick="return false;" title="<?=$arResult['ALLOW_VOTE']['ERROR_MSG']?>" href="#plus"></a>&nbsp;<a id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-minus" class="rating-vote-minus" onclick="return false;" title="<?=$arResult['ALLOW_VOTE']['ERROR_MSG']?>" href="#minus"></a>
			</span> 
		<?
	}
	else
	{
		?>
			<span class="rating-vote">
				<img src="/bitrix/components/bitrix/rating.vote/templates/.default/images/rate.gif" title="<?=$arResult['ALLOW_VOTE']['ERROR_MSG']?>">
				<span id="rating-vote-<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_ID']))?>-result" class="rating-vote-result rating-vote-result-<?=($arResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus')?>" title="<?=CUtil::JSEscape(htmlspecialchars($arResult['VOTE_TITLE']))?>"> <?=htmlspecialchars($arResult['TOTAL_VALUE'])?></span>
			</span> 
		<?
	}
}
?>