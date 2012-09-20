<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(strlen($arResult["ERROR_MESSAGE"])<=0):?>

<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array("NO_HEAD"=>"1"),Array("MODE"=>"php"));
?>

<tbody>
<tr>
            
            <td class="C10_Left_Column" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="240">
              <tbody><tr>
                <td class="Ltop2" valign="top"><p>&nbsp;</p>
			<p><?=GetMessage("SPOD_ORDER_NO")?>&nbsp;<span class="Header">№ <?=$arResult["ID"]?></span></p>
                	<p><?=GetMessage("SPOD_FROM")?> <?=$arResult["DATE_INSERT"] ?></p>
			<p>&nbsp;</p>
			<p class="Header">Кампания наружной рекламы</p>
			<p><br>
                        <?echo GetMessage("SPOD_ORDER_STATUS")?> <b><?=$arResult["STATUS"]["NAME"]?></b><?=GetMessage("SPOD_ORDER_FROM")?><?=$arResult["DATE_STATUS"]?>)<br>
                        <?=GetMessage("P_ORDER_PRICE")?>
                        <?
                        echo "<b>".$arResult["PRICE_FORMATED"]."</b>";
						if (DoubleVal($arResult["SUM_PAID"]) > 0)
							echo "(".GetMessage("SPOD_ALREADY_PAID")."&nbsp;<b>".$arResult["SUM_PAID_FORMATED"]."</b>)";
						?>
			<p>
			<?= GetMessage("P_ORDER_CANCELED") ?>:
			<?echo (($arResult["CANCELED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
			if ($arOrder["CANCELED"] == "Y")
			{
				echo GetMessage("SPOD_ORDER_FROM").$arResult["DATE_CANCELED"].")";
				if (strlen($arResult["REASON_CANCELED"]) > 0)
					echo "<br />".$arResult["REASON_CANCELED"];
				}
				elseif ($arResult["CAN_CANCEL"]=="Y")
				{
					?>&nbsp;<a href="<?=$arResult["URL_TO_CANCEL"]?>"><?=GetMessage("SALE_CANCEL_ORDER")?></a><?
				}?>
			</p>
                </td>
              </tr>

            </tbody></table>              
            <p align="left">&nbsp;</p></td>
            <td colspan="3" class="C10_Right_Column Lleft" valign="top">
            
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td class="Ltop2" valign="top" width="240"><p>&nbsp;</p>
                 <?if (IntVal($arResult["USER_ID"])>0):?>
                  
                  <p><strong><?echo GetMessage("SPOD_ACCOUNT_DATA")?>:</strong></p>
                  <p><?=$arResult["USER_NAME"]?><br>
                    <?= GetMessage("SPOD_LOGIN") ?>: <?=$arResult["USER"]["LOGIN"]?><br>
                    <?echo GetMessage("SPOD_EMAIL")?>: <a href="mailto:<?=$arResult["USER"]["EMAIL"]?>"><?=$arResult["USER"]["EMAIL"]?></a></p>
                  <p>&nbsp;</p>



			<?if(!empty($arResult["ORDER_PROPS"])){
				foreach($arResult["ORDER_PROPS"] as $val){
					if ($val["SHOW_GROUP_NAME"] == "Y"){
						?>
							<p><b><?=$val["GROUP_NAME"];?></b></p>
						<?
					}
					?>
						<p><?echo $val["NAME"] ?>:
							<?
							if ($val["TYPE"] == "CHECKBOX")
							{
								if ($val["VALUE"] == "Y")
									echo GetMessage("SALE_YES");
								else
									echo GetMessage("SALE_NO");
							}
							else
								echo $val["VALUE"];
							?>
						</p>
					<?
				}
			}
			if (strlen($arResult["USER_DESCRIPTION"])>0)
			{
				?>
					<?=GetMessage("P_ORDER_USER_COMMENT")?>:
					<?=$arResult["USER_DESCRIPTION"]?>
				<?
			}?>


                  
                  <p>&nbsp;</p>

			<p><b><?=GetMessage("P_ORDER_PAYMENT")?></b></p>
			<p><?=GetMessage("P_ORDER_PAY_SYSTEM")?>:
					<?
					if (IntVal($arResult["PAY_SYSTEM_ID"]) > 0)
						echo $arResult["PAY_SYSTEM"]["NAME"];
					else
						echo GetMessage("SPOD_NONE");
					?></p>
				<p><?echo GetMessage("P_ORDER_PAYED") ?>:
				<?
					echo (($arResult["PAYED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
					if ($arResult["PAYED"] == "Y")
						echo GetMessage("SPOD_ORDER_FROM").$arResult["DATE_PAYED"].")";
					if ($arResult["CAN_REPAY"]=="Y")
					{
						if ($arResult["PAY_SYSTEM"]["PSA_NEW_WINDOW"] == "Y")
						{
							?>
							<a href="<?=$arResult["PAY_SYSTEM"]["PSA_ACTION_FILE"]?>" target="_blank"><?=GetMessage("SALE_REPEAT_PAY")?></a>
							<?
						}
						else
						{
							$ORDER_ID = $ID;
							include($arResult["PAY_SYSTEM"]["PSA_ACTION_FILE"]);
						}
					}?>
					</p>
				<p><?=GetMessage("P_ORDER_DELIVERY")?>:
					<?
					if (strpos($arResult["DELIVERY_ID"], ":") !== false || IntVal($arResult["DELIVERY_ID"]) > 0)
					{
						echo $arResult["DELIVERY"]["NAME"];
					}
					else
					{
						echo GetMessage("SPOD_NONE");
					}
					?></p>
                    <p>&nbsp;</p>                    
                    
                 <?endif;?>
                 </td>
                <td class="Ltop2" valign="top" width="501">
                 <p>&nbsp;</p>
                  <table cellpadding="5" cellspacing="0" width="500">
                    <thead>
                      <tr>
                        <td><strong><?= GetMessage("SPOD_NAME") ?></strong></td>
                        <td><strong><?= GetMessage("SPOD_PRICE") ?></strong></td>
                        <td><strong><?= GetMessage("SPOD_QUANTITY") ?></strong></td>
                      </tr>
                    </thead>
                    <tbody>
		<?
			foreach($arResult["BASKET"] as $val){
				$val["DETAIL_PAGE_URL"] = '';
		?>
				<tr>
					<td>
		<?
							if (strlen($val["DETAIL_PAGE_URL"])>0)
								echo "<a href=\"".$val["DETAIL_PAGE_URL"]."\">";
							echo '<b>'.htmlspecialcharsEx($val["NAME"]).'</b>';
							if (strlen($val["DETAIL_PAGE_URL"])>0)
								echo "</a>";

							if(!empty($val["PROPS"]))
							{
								foreach($val["PROPS"] as $vv) 
									echo "<p>".$vv["NAME"].": ".$vv["VALUE"]."</p>";
							}?>
					</td>
                                        <td><?=$val["PRICE_FORMATED"]?></td>
					<td><?=$val["QUANTITY"]?></td>
		<?
			}
		?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td><p><strong><?=GetMessage("SPOD_ITOG")?>:</strong></p></td>
                        <td nowrap><p><strong>
								<?
								if(DoubleVal($arResult["ORDER_WEIGHT"]) > 0)
									echo "<p>".$arResult["ORDER_WEIGHT_FORMATED"]."</p>";
								foreach($arResult["TAX_LIST"] as $val)
									echo "<p>".$val["VALUE_MONEY_FORMATED"]."</p>";
								if(DoubleVal($arResult["TAX_VALUE"]) > 0)
									echo "<p>".$arResult["TAX_VALUE_FORMATED"]."</p>";
								if(DoubleVal($arOrder["DISCOUNT_VALUE"]) > 0)
									echo "<p>".$arResult["DISCOUNT_VALUE_FORMATED"]."</p>";
								if(DoubleVal($arResult["PRICE_DELIVERY"]) > 0)
									echo "<p>".$arResult["PRICE_DELIVERY_FORMATED"]."</p>";
								?>
								<p><b><?=$arResult["PRICE_FORMATED"]?></b></p>                        
                        </strong></p></td>
                        <td>&nbsp;</td>
                      </tr>
                    </tfoot>
                  </table>                  
                  <p>&nbsp;</p></td>
              </tr>
            </tbody>
            </table>              
            </td>
            </td>
</tr>          
</tbody>

<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array("NO_HEAD"=>"1"),Array("MODE"=>"php"));
?>

<?else:?>
	<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?endif;?>
