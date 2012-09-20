<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?//elements detail?>
<table cellpadding="0" cellspacing="0" class="data-table" width="100%">
	<tr>
		<th>
		<?
		//add edit element button
		if(isset($arResult['ITEM']['EDIT_BUTTON']))
			echo $arResult['ITEM']['EDIT_BUTTON'];
		?>
		<?=$arResult['ITEM']['NAME']?>
		</th>
	</tr>
	<tr>
		<td>
		<?=$arResult['ITEM']['PREVIEW_TEXT']?>
		<?=$arResult['ITEM']['DETAIL_TEXT']?>
		</td>
	</tr>
</table>