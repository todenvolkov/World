<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>
<form name="form_screen">
<table width="100%" border="0" cellspacing="4" cellpadding="4">
<tr>
	<td>Экран:</td>
	<td><span id="screenheader"></span>&nbsp;</td>
</tr>
<tr>
	<td>Название видеоролика:</td>
	<td><input type="text" size="50" name="screenname" id="screenname" onChange="javascript:return screen_recount();"></td>
</tr>
<tr>
	<td>Период размещения с:</td>
	<td nowrap>
		<?$APPLICATION->IncludeComponent("bitrix:main.calendar","mir",Array("SHOW_INPUT" => "Y","FORM_NAME" => "form_screen","INPUT_NAME" => "screendate1","INPUT_NAME_FINISH" => "","INPUT_VALUE" => date("d.m.Y"),"INPUT_VALUE_FINISH" => "","SHOW_TIME" => "N","HIDE_TIMEBAR" => "Y"));?>
		по: <?$APPLICATION->IncludeComponent("bitrix:main.calendar","mir",Array("SHOW_INPUT" => "Y","FORM_NAME" => "form_screen","INPUT_NAME" => "screendate2","INPUT_NAME_FINISH" => "","INPUT_VALUE" => date("d.m.Y", time()+86400*6),"INPUT_VALUE_FINISH" => "","SHOW_TIME" => "N","HIDE_TIMEBAR" => "Y"));?>
		&nbsp;&nbsp;<b><span id="screendays">дней: 1</span></b>
	</td>
</tr>
<tr>
	<td>Количество выходов:</td>
	<td>
		<select name="screenkol" id="screenkol" onChange="javascript:return screen_recount();">
			<option value="1">Выход ролика в 1-м блоке из 4-х</option>
			<option value="2">Выход ролика через блок</option>
			<option value="3">Выход ролика в 3-х блоках из 4-х</option>
			<option value="4">Выход ролика в каждом блоке</option>
		</select>
	</td>
</tr>
<tr>
	<td>Хронометраж:</td>
	<td><input type="text" size="3" name="screenhron" id="screenhron" value="5" onChange="javascript:return screen_recount();"> сек.</td>
</tr>
<tr>
	<td valign="top">Комментарий:</td>
	<td><textarea name="screencomm" id="screencomm"  cols="40" rows="4"></textarea></td>
</tr>
<tr>
	<td><b>Полная стоимость:</td>
	<td><p class="Header"><span id="screencost">0.00</span></p></td>
</tr>
</table>
</form>
