<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($_REQUEST["filter_canceled"] == "Y" && $_REQUEST["filter_history"] == "Y")
	$ptitle = "Заказы отмененные";
elseif($_REQUEST["filter_status"] == "F" && $_REQUEST["filter_history"] == "Y")
	$ptitle = "Заказы выполненные";
elseif($_REQUEST["filter_history"] == "Y")
	$ptitle = "Все заказы";
else
	$ptitle = "Активные заказы";
?>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>
<td valign="top" class="Ltop2" width="250" colspan="2">
<p>&nbsp;</p>
<p class="Header"><?=$ptitle?></p>
<p>&nbsp;</p>
<table class="cart-items" width="100%" cellpadding="0" cellspacing="0" border="0">
<tbody>
	<?
	$bNoOrder = true;
	$i=0;
	foreach($arResult["ORDERS"] as $key => $val)
	{
		$bNoOrder = false;
		$cclass = ($i==0)?'':'class="Ltop2"';
	?>
					<tr>
						<td <?=$cclass?> width="240"><p>&nbsp;</p>
							<p><?=GetMessage("STPOL_ORDER_NO")?><span class="Header"> №<?=$val["ORDER"]["ID"] ?></span>&nbsp;</p>
							<p>Кампания наружной рекламы</p>
							<p>от&nbsp;<?=$val["ORDER"]["DATE_INSERT"]; ?></p>
							<p>&nbsp;</p>
							<p><a title="<?echo GetMessage("STPOL_DETAIL")?>" href="<?=$val["ORDER"]["URL_TO_DETAIL"] ?>"><?echo GetMessage("STPOL_DETAIL")?></a></p>
							<p>&nbsp;</p>
							<?if ($val["ORDER"]["CAN_CANCEL"] == "Y"):?>
							<p><a title="<?= GetMessage("STPOL_CANCEL") ?>" href="<?=$val["ORDER"]["URL_TO_CANCEL"]?>"><?= GetMessage("STPOL_CANCEL") ?></a></p>
							<?endif;?>
							<p><a title="<?= GetMessage("STPOL_REORDER") ?>" href="<?=$val["ORDER"]["URL_TO_COPY"]?>"><?= GetMessage("STPOL_REORDER1") ?></a></p>
							<p>&nbsp;</p>
							<p><label><?echo GetMessage("STPOL_SUM")?></label> <span><?=$val["ORDER"]["FORMATED_PRICE"]?></span></p>
							<p><label><?=GetMessage("STPOL_PAYED")?></label> <span><?echo (($val["ORDER"]["PAYED"]=="Y") ? GetMessage("STPOL_Y") : GetMessage("STPOL_N"));?></span></p>
								<?if(IntVal($val["ORDER"]["PAY_SYSTEM_ID"])>0)
									echo "<p><label>".GetMessage("P_PAY_SYS")."</label> <span>".$arResult["INFO"]["PAY_SYSTEM"][$val["ORDER"]["PAY_SYSTEM_ID"]]["NAME"]."</span></p>"?>
							<div><strong></strong><strong></strong><strong></strong><div></div></div>
							<p>&nbsp;</p>
				


						</td>
						<td <?=$cclass?> valign="top" width="501"><p>&nbsp;</p>
							<ol>
									<?
									foreach($val["BASKET_ITEMS"] as $vvval)
									{
										$vvval["DETAIL_PAGE_URL"] = '';
										?>
										<li>												
											<?
											if (strlen($vvval["DETAIL_PAGE_URL"]) > 0)
												echo "<a href=\"".$vvval["DETAIL_PAGE_URL"]."\">";
											echo $vvval["NAME"];
											if (strlen($vvval["DETAIL_PAGE_URL"]) > 0)
												echo "</a>";
											if($vvval["QUANTITY"] > 1)
												echo " &mdash; ".$vvval["QUANTITY"].GetMessage("STPOL_SHT");
											?>
										</li>
										<?
									}
									?>
							</ol>
						</td>
					</tr>			
<?
		$i++;
	}
	if ($bNoOrder)
	{
		echo '<tr><td><td valign="top" colspan="2"><p>&nbsp;</p>';
		echo ShowNote(GetMessage("STPOL_NO_ORDERS_NEW"));
		echo '</td></tr>';
	}
?>
</tbody>
</table>
	 
<?if(strlen($arResult["NAV_STRING"]) > 0):?>
	<div class="navigation"><?=$arResult["NAV_STRING"]?></p>
<?endif?>

</td>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
