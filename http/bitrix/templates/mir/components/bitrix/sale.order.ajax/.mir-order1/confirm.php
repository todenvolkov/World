<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array("INDEX"=>"10"),Array("MODE"=>"php")); ?>

<?
if (!empty($arResult["ORDER"]))
{
	?>
<tbody>
<tr bgcolor="#FFFFFF">
	<td valign="top" class="C10_Left_Column">
		<table width="240" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="Ltop2">
					<p>&nbsp;</p>
					<p class="Header"><?=GetMessage("SOA_TEMPL_ORDER_COMPLETE")?></p>
					<p>&nbsp;</p>
				</td>
			</tr>
		</table>
	</td>
	<td valign="top" class="C10_Right_Column Lleft" colspan="2" width="750">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td class="Ltop2" width="750" valign="top" colspan="2">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
						<td width="240">
						<?= GetMessage("SOA_TEMPL_ORDER_SUC", Array("#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"], "#ORDER_ID#" => $arResult["ORDER_ID"]))?><br /><br />
						</td>
						<td width="501" style="padding-left:25px;" valign="top">
						<p>&nbsp;</p>

<div class="notetext">

				
				<?= GetMessage("SOA_TEMPL_ORDER_SUC1", Array("#LINK#" => $arParams["PATH_TO_PERSONAL"])) ?>
	<?
	if (!empty($arResult["PAY_SYSTEM"]))
	{
		?>
		<br /><br />
		<?=GetMessage("SOA_TEMPL_PAY")?>: <?= $arResult["PAY_SYSTEM"]["NAME"] ?><br />
			<?
			if (strlen($arResult["PAY_SYSTEM"]["ACTION_FILE"]) > 0)
			{
						if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y")
						{
							?>
							<script language="JavaScript">
								window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?= $arResult["ORDER_ID"] ?>');
							</script>
							<?= GetMessage("SOA_TEMPL_PAY_LINK", Array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$arResult["ORDER_ID"])) ?>
							<?
						}
						else
						{
							if (strlen($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"])>0)
							{
								include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]);
							}
						}
			}
	}
?>
</div>
						</td>
						</tr>
					</table>

				</td>
			</tr>
		</table>
		<div class="cart-buttons" align="right">
			<input type="submit" value="Печать заказа" name="BasketMap" onClick="javascript:window.open('../print.php?ID=<?=$arResult["ORDER_ID"]?>','print');return false;">&nbsp;&nbsp;
		</div>
	</td>
</tr>
<?
}
else
{
	?>
<tbody>
<tr bgcolor="#FFFFFF">
	<td valign="top" class="C10_Left_Column" colspan="2">
		&nbsp;
	</td>
	<td valign="top" class="C10_Right_Column Lleft" colspan="2" width="750">
	<b><?=GetMessage("SOA_TEMPL_ERROR_ORDER")?></b><br />
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST", Array("#ORDER_ID#" => $arResult["ORDER_ID"]))?>
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST1")?>
	</td>
</tr>
</tbody>
	<?
}
?>

						
				
						

<!--
<tr>
<td class="C10_Left_Column" valign="top">
	<table border="0" cellpadding="0" cellspacing="0" width="240">
		<tbody>
			<tr>
				<td class="Ltop2">
					<p>&nbsp;</p>
					<p class="Header">Заказ сформирован</p>                  
				</td>
			</tr>

		</tbody>
	</table>              
	<p align="left">&nbsp;</p>
</td>
<td colspan="2" class="C10_Right_Column Lleft" valign="top">
	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tbody>
			<tr>
				<td class="Ltop2" valign="top" width="240"><p>&nbsp;</p>
					<p>Ваш заказ </p>
					<p class="Header"><strong>№17</strong> </p>
					<p>от         16.06.2011 22:06:54</p>
					<p> успешно создан.</p></td>
				<td class="Ltop2" valign="top" width="501">
					<p>&nbsp;</p>
					<p>Вы можете контролировать выполнение своего заказа в <a href="http://temp.t5.ru/personal/order/">Персональном разделе сайта</a>.         </p>
					<p>&nbsp;</p>
					<p>Обратите внимание, что для входа в этот раздел вам необходимо будет ввести         логин и пароль пользователя сайта. </p>
					<p>&nbsp;</p>
					<p>Успехов.</p>
				</td>
			</tr>
		</tbody>
	</table>
</td>
</tr>       
-->





<!--
<div class="notetext">
<?
if (!empty($arResult["ORDER"]))
{
	?>
	<b><?=GetMessage("SOA_TEMPL_ORDER_COMPLETE")?></b><br />
				<?= GetMessage("SOA_TEMPL_ORDER_SUC", Array("#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"], "#ORDER_ID#" => $arResult["ORDER_ID"]))?><br /><br />
				<?= GetMessage("SOA_TEMPL_ORDER_SUC1", Array("#LINK#" => $arParams["PATH_TO_PERSONAL"])) ?>
	<?
	if (!empty($arResult["PAY_SYSTEM"]))
	{
		?>
		<br /><br />
		<?=GetMessage("SOA_TEMPL_PAY")?>: <?= $arResult["PAY_SYSTEM"]["NAME"] ?><br />
			<?
			if (strlen($arResult["PAY_SYSTEM"]["ACTION_FILE"]) > 0)
			{
						if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y")
						{
							?>
							<script language="JavaScript">
								window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?= $arResult["ORDER_ID"] ?>');
							</script>
							<?= GetMessage("SOA_TEMPL_PAY_LINK", Array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$arResult["ORDER_ID"])) ?>
							<?
						}
						else
						{
							if (strlen($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"])>0)
							{
								include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]);
							}
						}
			}
	}
}
else
{
	?>
	<b><?=GetMessage("SOA_TEMPL_ERROR_ORDER")?></b><br />
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST", Array("#ORDER_ID#" => $arResult["ORDER_ID"]))?>
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST1")?>
	<?
}
?>
</div>
-->

<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_footer.php',Array(),Array("MODE"=>"php")); ?>
