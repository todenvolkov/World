<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	?>
	<div id="wiki-post">
	<div id="wiki-post-content">
	<form action="<?=POST_FORM_ACTION_URI?>" method="get">
	<?
	if (!empty($_SERVER['QUERY_STRING'])):
	$arGetParams = explode('&', $_SERVER['QUERY_STRING']);
	foreach($arGetParams as $strParam)
	{
		$arGetParam = explode('=', $strParam);
		?> <input type="hidden" value="<?=$arGetParam[1]?>" name="<?=$arGetParam[0]?>" /><? 
	}
	endif;
	?>
	<input type="text" name="q" value="<?=$arResult['QUERY']?>" size="40" />
	&nbsp;<input type="submit" value="<?=GetMessage('SEARCH_GO')?>" />
	</form>         
	<? 
	if (empty($arResult['CATEGORIES'])):    
		ShowNote(GetMessage('SEARCH_CORRECT_AND_CONTINUE'));        
	else:    
	?>
	<?=$arResult['DB_LIST']->NavPrint(GetMessage('NAV_TITLE'));?>
	<br/>
	<? 
	foreach($arResult['CATEGORIES'] as $arCat)  
	{
		?>
		<a href="<?=$arCat['LINK']?>" title="<?=$arCat['NAME']?>" class="<?=($arCat['IS_RED'] == 'Y' ? 'wiki_red' : '')?>"><?=$arCat['NAME']?></a> (<?=$arCat['CNT']?>)<br/>
		<? 
	}       
	?>   
	<?=$arResult['DB_LIST']->NavPrint(GetMessage('NAV_TITLE'));?> 
	<? endif;?>       
	</div>
	</div>
<? 
endif;
?>