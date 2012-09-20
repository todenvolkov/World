<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="search-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<table border="0" cellspacing="0" cellpadding="2" align="left">
        <tr>
			<td align="left"><span style="font-size:13px;font-weight:bold;">Поиск на сайте:</span></td>
		</tr>
        <tr>
			<td align="left"><input style="width:170px" type="text" name="q" value="" size="15" maxlength="50" /></td>
		</tr>
		<tr>
			<td align="right"><input name="s" type="submit" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" /></td>
		</tr>
	</table>
</form>
</div>