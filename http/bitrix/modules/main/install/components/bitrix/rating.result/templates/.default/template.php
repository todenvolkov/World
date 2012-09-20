<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult['RESULT_TYPE'] == 'VALUE'):?>
	<?=($arResult['SHOW_RATING_NAME'] == 'Y'? htmlspecialchars($arResult['RATING_NAME']).':': '')?> 
	<span title="<?=CUtil::JSEscape(htmlspecialchars($arResult['RATING_NAME']))?>: <?=CUtil::JSEscape(htmlspecialchars($arResult['CURRENT_VALUE']))?> (<?=GetMessage('RATING_COMPONENT_PROGRESS')?> <?=CUtil::JSEscape(htmlspecialchars($arResult['PROGRESS_VALUE']))?>)">
	<?=(isset($arResult['LINK'])? '<a href="'.$arResult['LINK'].'">': '')?>
		<?=htmlspecialchars($arResult['ROUND_CURRENT_VALUE'])?>
	<?=(isset($arResult['LINK'])? '</a>': '')?>
	</span>
<?endif;
if ($arResult['RESULT_TYPE'] == 'POSITION'):
	$strRatingProgressPositon = '';
	if ($arResult['PROGRESS_POSITION_DIRECT'] == 'up')
		$strRatingProgressPositon = '<span class="rating_result_up">'.$arResult['PROGRESS_POSITION'].'</span>';
	else if ($arResult['PROGRESS_POSITION_DIRECT'] == 'down')
		$strRatingProgressPositon = '<span class="rating_result_down">'.$arResult['PROGRESS_POSITION'].'</span>';
?>
	<?=($arResult['SHOW_RATING_NAME'] == 'Y'? htmlspecialchars($arResult['RATING_NAME']).':': '')?> <span title="<?=GetMessage('RATING_COMPONENT_CURRENT_POSITION');?> <?=CUtil::JSEscape(htmlspecialchars($arResult['CURRENT_POSITION']))?> (<?=GetMessage('RATING_COMPONENT_PREVIOUS_POSITION');?> <?=CUtil::JSEscape(htmlspecialchars($arResult['PREVIOUS_POSITION']))?>)"><?=$arResult['CURRENT_POSITION']?> <?=$strRatingProgressPositon?></span>
<?endif;?>