<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="wiki-post">
<?

if(!empty($arResult["FATAL_MESSAGE"]))
{
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
}
else 
{
	?><div id="wiki-post-content"><?=$arResult['ELEMENT']['DETAIL_TEXT'];?></div><?
}
?>
</div>