<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr bgcolor="#FFFFFF">
<td colspan="3">
<table width="100%" border="0" cellpadding="5" cellspacing="0">
              <tr>
                <td colspan="7" nowrap><p class="Header">Полноцветная широкоформатная печать</p></td>
              </tr>
              <tr>
                <td height="40" align="right" nowrap class="Ltop2 Lbott"><p><strong>#</strong></p></td>
                <td nowrap class="Ltop2 Lbott" width="300"><strong>Экран</strong></td>
                <td nowrap class="Ltop2 Lbott" width="400"><strong>Название ролика</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Дата начала</strong></td>
                <td align="center" nowrap class="Ltop2 Lbott"><strong>Дата конца</strong></td>
                <td align="right" nowrap class="Ltop2 Lbott"><strong>Дней</strong></td>
                <td align="right" nowrap class="Ltop2 Lbott"><strong>Хронометраж</strong></td>
		<td align="right" nowrap class="Ltop2 Lbott"><strong>Руб.</strong></td>
              </tr>
<?

$tmp1 = CCatalog::GetList(array(),array());
while ($catalog = $tmp1->Fetch()){
	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		if ($section['DEPTH_LEVEL']==1){
		}else{
			$sec2_name[ $section["ID"] ] = $section["NAME"];
		}
	}
}


$i=0;
foreach($arResult["BASKET_ITEMS"] as $arBasketItems){

	if ($arBasketItems['CALLBACK_FUNC']!='add2basket_pscreen')  continue;

	$i++;
	$bgcolor = ($i%2==0)?"":" bgcolor=\"#EEEEEE\"";
?>
	<tr>
		<td align="right" nowrap <?=$bgcolor;?>><?=$i;?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][1]["VALUE"];?> / <?=$arBasketItems["PROPS"][0]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][2]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][3]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][4]["VALUE"];?></td>
		<td align="right" nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][5]["VALUE"];?></td>
		<td align="right" nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][6]["VALUE"];?> сек.</td>
		<td align="right" nowrap <?=$bgcolor;?>><?=number_format($arBasketItems["PRICE"],2,","," ");?></td>
	</tr>
<?
}
?>
              <tr>
                <td class="Ltop" colspan="8">&nbsp;</td>
              </tr>
</table>
</td>            
</tr>
