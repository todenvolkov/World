<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<p>&nbsp;</p>
<p class="Header">Корзина. Отложенные товары</p>
<p>&nbsp;</p>

<?if(count($arResult["ITEMS"]["DelDelCanBuy"]) > 0):?>
	<table class="cart-items" width="100%" cellpadding="5" cellspacing="0" border="0">
	<thead>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-name"><?= GetMessage("SALE_NAME")?></td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price"><?= GetMessage("SALE_PRICE")?></td>
			<?endif;?>
			<td class="cart-item-actions">
				<?if (in_array("DELETE", $arParams["COLUMNS_LIST"]) || in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
					<?= GetMessage("SALE_ACTION")?>
				<?endif;?>
			</td>
		</tr>
	</thead>
	<tbody>
	<?
	$i=0;
	foreach($arResult["ITEMS"]["DelDelCanBuy"] as $arBasketItems)
	{
		$arBasketItems["DETAIL_PAGE_URL"] = '';
		$bgcolor = ($i%2==0)?' bgcolor="#F1F1F1"':'';
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
				<td class="cart-item-price"<?=$bgcolor?>><?=number_format($arBasketItems["PRICE"]*$arBasketItems["QUANTITY"],2,'.',' ')?> руб.</td>
			<?endif;?>
			<td class="cart-item-actions"<?=$bgcolor?>>
				<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
					<a class="cart-delete-item" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" title="<?=GetMessage("SALE_DELETE_PRD")?>"></a>
				<?endif;?>
				<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
					<a class="cart-shelve-item" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["add"])?>"><?=GetMessage("SALE_ADD_CART")?></a>
				<?endif;?>
			</td>
		</tr>
		<?
		$i++;
	}
	?>
	</tbody>
	</table>
<?else:
	echo ShowNote(GetMessage("SALE_NO_ACTIVE_PRD"));
endif;?>
