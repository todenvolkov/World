<?if ($arResult['ERROR']) {
	ShowError($arResult['ERROR_TEXT']);
}?>
<form action="<?=$arResult['POST_URL']?>" method="POST">
<input type="hidden" name="savelogin" value="Y">
<table border="0">
<tr>
	<td><?=GetMessage('LIVEID_REC_LOGIN')?></td>
	<td><input type="text" name="LOGIN" value="<?=$arResult['LOGIN']?>"></td>
</tr>
<tr>
	<td><?=GetMessage('LIVEID_REC_EMAIL')?></td>
	<td><input type="text" name="EMAIL" value="<?=$arResult['EMAIL']?>"></td>
</tr>
</table>
<input type="submit" value="<?=GetMessage('LIVEID_REC_SUBMIT')?>">
</form>