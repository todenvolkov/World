<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<style>
.over2{
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  width: 260px;
}
</style>

<tr bgcolor="#FFFFFF">
<td colspan="3">
<table width="100%" border="0" cellpadding="5" cellspacing="0">
              <tr>
                <td colspan="7" nowrap><p class="Header">Производство рекламных конструкций</p></td>
              </tr>
              <tr>
                <td height="40" align="right" nowrap class="Ltop2 Lbott"><p><strong>#</strong></p></td>
                <td nowrap class="Ltop2 Lbott"><strong>Тип конструкции</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Место установки</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Классность проекта</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Высота / Ширинаа</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Файлы</strong></td>
                <td align="center" nowrap class="Ltop2 Lbott"><strong>Комментарий</strong></td>
              </tr>
<?
$i=0;
foreach($arResult["BASKET_ITEMS"] as $arBasketItems){

	if ($arBasketItems['CALLBACK_FUNC']!='add2basket_pmake')  continue;
	
	$i++;
	$bgcolor = ($i%2==0)?"":" bgcolor=\"#EEEEEE\"";
	$filesa = array();
	for ($j=6;$j<sizeof($arBasketItems["PROPS"]);$j+=2){
		if ($arBasketItems["PROPS"][$j]["VALUE"]!="")
			$filesa[] = '<a href="'.$arBasketItems["PROPS"][$j]["VALUE"].'" target="_blank">F</a>';
	}
?>
	<tr>
		<td align="right" nowrap <?=$bgcolor;?>><?=$i;?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][0]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][1]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][2]["VALUE"];?></td>
		<td align="right" nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][3]["VALUE"];?> / <?=$arBasketItems["PROPS"][4]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=join(" ",$filesa);?></td>
		<td nowrap <?=$bgcolor;?>><div class="over2"><?=$arBasketItems["PROPS"][5]["VALUE"];?></div></td>
	</tr>
<?
}
?>
              <tr>
                <td class="Ltop" colspan="7">&nbsp;</td>
              </tr>
</table>
</td>            
</tr>
