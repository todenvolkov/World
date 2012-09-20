<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript">
function requestURI(url) {
	document.getElementById('mm_'+url).innerHTML = '<img src="<?=SITE_TEMPLATE_PATH?>/images/ajax.gif" width="16" height="16" border="0">';
	url='/catalog/remove_basket.php?SALE_ID='+url;
	if (window.XMLHttpRequest) {
		c_request = new XMLHttpRequest();
		c_request.onreadystatechange = processRequestChange;
		c_request.open("GET", url, true);
		c_request.send(null);
	} else if (window.ActiveXObject) {
		c_request = new ActiveXObject("Microsoft.XMLHTTP");
		if (c_request) {
			c_request.onreadystatechange = processRequestChange;
			c_request.open("GET", url, true);
			c_request.send();
		}
	}
	return false;
}

function processRequestChange() {
 	if (c_request.readyState == 4) {
 		if (c_request.status == 200) {
 			//alert('ok');
 			window.top.location.href='';
		}
 	}
}
</script>
<style>
.over{
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  width: 330px;
}
</style>
<!--
<td align="center" nowrap<?=$bgcolor;?>><span id="mm_<?=$arBasketItems["ID"]?>"><a href="" onClick="javascript:return requestURI(<?=$arBasketItems["ID"]?>);" title="Удалить"><img src="<?=SITE_TEMPLATE_PATH?>/images/Del.png" width="12" height="12" border="0"></a></span></td>
-->
<tr bgcolor="#FFFFFF">
<td colspan="3">
<table width="100%" border="0" cellpadding="5" cellspacing="0">
              <tr>
                <td colspan="3" nowrap><p class="Header">Заказ</p>                </td>
                <td nowrap><!--Распечатать | Сохранить в файл PDF или есчо какой-нить.--></td>
                <td align="center" nowrap>&nbsp;</td>
                <td nowrap>&nbsp;</td>
                <td nowrap>&nbsp;</td>
                <td nowrap>&nbsp;</td>
                <td nowrap>&nbsp;</td>
                <td align="center" nowrap>&nbsp;</td>
                <td align="right" nowrap><strong>Итого:</strong></td>
                <td align="right" nowrap><strong><?=number_format($arResult["ORDER_PRICE"],2,","," ");?></strong></td>
              </tr>
              <tr>
                <td height="40" align="right" nowrap class="Ltop2 Lbott"><p><strong>#</strong></p></td>
                <td nowrap class="Ltop2 Lbott"><strong>Тип</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Формат</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Адрес </strong></td>
                <td align="center" nowrap class="Ltop2 Lbott"><strong>Сторона</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>РИМ</strong></td>
                <td nowrap class="Lbott Ltop2"><strong>Осв.</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Начало</strong></td>
                <td nowrap class="Ltop2 Lbott"><strong>Конец</strong></td>
                <td align="center" nowrap class="Ltop2 Lbott"><strong>Дней</strong></td>
                <td align="right" nowrap class="Ltop2 Lbott"><strong>Руб./День</strong></td>
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
	
	$mm = CIBlockElement::GetByID($arBasketItems["PRODUCT_ID"]);
	if($mmm = $mm->GetNextElement()){
		$item = $mmm->GetFields();
		$itemP = $mmm->GetProperties();
		$i++;
		$bgcolor = ($i%2==0)?"":" bgcolor=\"#EEEEEE\"";
		$itemP["OSV1"] = ($itemP["OSV1"]==1)?"Есть":"Нет";
?>
	<tr>
		<td align="right" nowrap <?=$bgcolor;?>><?=$i;?></td>
		<td nowrap <?=$bgcolor;?>><?=$sec2_name[$item["IBLOCK_SECTION_ID"]];?></td>
		<td nowrap <?=$bgcolor;?>><?=$itemP["SIZE1"]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><div class="over"><?=$arBasketItems["NAME"];?></div></td>
		<td align="center" nowrap <?=$bgcolor;?>><?=$itemP["SIDE1"]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>>SAF</td>
		<td nowrap <?=$bgcolor;?>><?=$itemP["OSV1"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][0]["VALUE"];?></td>
		<td nowrap <?=$bgcolor;?>><?=$arBasketItems["PROPS"][1]["VALUE"];?></td>
		<td align="center" nowrap <?=$bgcolor;?>><?=$arBasketItems["QUANTITY"];?></td>
		<td align="right" nowrap <?=$bgcolor;?>><?=number_format($arBasketItems["PRICE"],2,","," ");?></td>
		<td align="right" nowrap <?=$bgcolor;?>><?=number_format($arBasketItems["PRICE"]*$arBasketItems["QUANTITY"],2,","," ");?></td>
	</tr>
<?
	}
}
?>
              <tr>
                <td align="right" nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td align="center" nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td nowrap class="Ltop">&nbsp;</td>
                <td align="center" nowrap class="Ltop">&nbsp;</td>
                <td align="right" nowrap class="Ltop"><strong>Итого:</strong></td>
                <td align="right" nowrap class="Ltop"><strong><?=number_format($arResult["ORDER_PRICE"],2,","," ");?></strong></td>
              </tr>
</table>
</td>            
</tr>
