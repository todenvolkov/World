<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<p>&nbsp;</p>
<p class="Header">Корзина. Активные товары</p>
<p>&nbsp;</p>

<?if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):?>
	<table class="cart-items" width="100%" cellpadding="5" cellspacing="0" border="0">
	<thead>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-name"><strong><?= GetMessage("SALE_NAME")?></strong></td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price"><strong><?= GetMessage("SALE_PRICE")?></strong></td>
			<?endif;?>
			<td class="cart-item-actions">
				<?if (in_array("DELETE", $arParams["COLUMNS_LIST"]) || in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
					<strong><?= GetMessage("SALE_ACTION")?></strong>
				<?endif;?>
			</td>
		</tr>
	</thead>
	<tbody>
	<?
	$i=0;
	foreach($arResult["ITEMS"]["AnDelCanBuy"] as $arBasketItems)
	{
		$arBasketItems["DETAIL_PAGE_URL"] = '';
		$bgcolor = ($i%2==0)?' bgcolor="#F1F1F1"':'';
		//$arBasketItems["PRICE_FORMATED"]
		?>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-name"<?=$bgcolor?>><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?><a href="<?=$arBasketItems["DETAIL_PAGE_URL"] ?>"><?
				endif;
				?><b><?=$arBasketItems["NAME"] ?></b><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?></a><?
				endif;?>
				<?if (in_array("PROPS", $arParams["COLUMNS_LIST"]))
				{
					foreach($arBasketItems["PROPS"] as $val)
					{
						if (preg_match("/FILE\d+/",$val["CODE"])){
							$val["VALUE"] = '<a href="'.$val["VALUE"].'" target="_blank">ссылка</a>';
						}
						if (preg_match("/FILE_ID\d+/",$val["CODE"]) || $val["CODE"]=="COMMENT1"){
							continue;
						}
						echo "<br />".$val["NAME"].": ".$val["VALUE"];
					}
				}?>
				</td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price"<?=$bgcolor?> nowrap><?=number_format($arBasketItems["PRICE"]*$arBasketItems["QUANTITY"],2,'.',' ')?> руб.</td>
			<?endif;?>
			<td class="cart-item-actions"<?=$bgcolor?> nowrap>
				<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
					<a class="cart-delete-item" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>"><?=GetMessage("SALE_DELETE_PRD")?></a>&nbsp;&nbsp;&nbsp;
				<?endif;?>
				<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
					<a class="cart-shelve-item" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["shelve"])?>"><?=GetMessage("SALE_OTLOG")?></a>
				<?endif;?>
			</td>
		</tr>
		<?
		$i++;
	}
	?>
	</tbody>
	<tfoot>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-name" align="right">
					<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
						<p><?echo GetMessage("SALE_ALL_WEIGHT")?>:</p>
					<?endif;?>
					<?if (doubleval($arResult["DISCOUNT_PRICE"]) > 0)
					{
						?><p><?echo GetMessage("SALE_CONTENT_DISCOUNT")?><?
						if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0)
							echo " (".$arResult["DISCOUNT_PERCENT_FORMATED"].")";?>:</p><?
					}?>
					<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
						<p><?echo GetMessage('SALE_VAT_INCLUDED')?></p>
					<?endif;?>
					<p><b><?= GetMessage("SALE_ITOGO")?>:</b></p>
				</td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price" nowrap>
					<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
						<p><?=$arResult["allWeight_FORMATED"]?></p>
					<?endif;?>
					<?if (doubleval($arResult["DISCOUNT_PRICE"]) > 0):?>
						<p><?=$arResult["DISCOUNT_PRICE_FORMATED"]?></p>
					<?endif;?>
					<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
						<p><?=$arResult["allVATSum_FORMATED"]?></p>
					<?endif;?>
					<p><b><?=$arResult["allSum_FORMATED"]?></b></p>
				</td>
			<?endif;?>
			<?if (in_array("DELETE", $arParams["COLUMNS_LIST"]) || in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-actions">&nbsp;</td>
			<?endif;?>
		</tr>
	</tfoot>
	</table>
	<br>	
	<div class="cart-ordering">
		<?if ($arParams["HIDE_COUPON"] != "Y"):?>
			<div class="cart-code">
				<input <?if(empty($arResult["COUPON"])):?>onclick="if (this.value=='<?=GetMessage("SALE_COUPON_VAL")?>')this.value=''" onblur="if (this.value=='')this.value='<?=GetMessage("SALE_COUPON_VAL")?>'"<?endif;?> value="<?if(!empty($arResult["COUPON"])):?><?=$arResult["COUPON"]?><?else:?><?=GetMessage("SALE_COUPON_VAL")?><?endif;?>" name="COUPON">
			</div>
		<?endif;?>
		<div class="cart-buttons" align="right">
			<input type="submit" value="Показать на карте" name="BasketMap">&nbsp;&nbsp;
			<input type="submit" value="<?echo GetMessage("SALE_UPDATE")?>" name="BasketRefresh">&nbsp;&nbsp;
			<input type="submit" value="<?echo GetMessage("SALE_ORDER")?>" name="BasketOrder"  id="basketOrderButton2">
		</div>
	</div>
<?else:
	echo ShowNote(GetMessage("SALE_NO_ACTIVE_PRD"));
endif;?>
