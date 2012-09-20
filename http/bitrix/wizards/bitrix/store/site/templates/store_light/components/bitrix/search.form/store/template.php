<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="search" class="form-box">
	<form action="<?=$arResult["FORM_ACTION"]?>">
		<div class="form-textbox">
			<div class="form-textbox-border"><input type="text" name="q" maxlength="50" /></div>
		</div>
		<div class="form-button">
			<input type="submit" name="s" onfocus="this.blur();" value="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>" id="search-submit-button">
		</div>
	</form>
</div>