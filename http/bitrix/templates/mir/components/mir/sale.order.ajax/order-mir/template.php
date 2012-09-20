<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="order_form_div">
<NOSCRIPT>
 <div class="errortext"><?=GetMessage("SOA_NO_JS")?></div>
</NOSCRIPT>
<div class="order-checkout" id="order_form">
<?

if(!$USER->IsAuthorized() && $arParams["ALLOW_AUTO_REGISTER"] == "N")
{
	if(!empty($arResult["ERROR"]))
	{
		$errors_array = '<div class="errortext"><ul>';
		foreach($arResult["ERROR"] as $v)
			$errors_array.="<li>".$v."</li>";
		$errors_array.="</ul></div>";
	}
	elseif(!empty($arResult["OK_MESSAGE"]))
	{
		foreach($arResult["OK_MESSAGE"] as $v)
			echo ShowNote($v);
	}

	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/auth.php");
}
else
{
	if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y")
	{
		if(strlen($arResult["REDIRECT_URL"]) > 0)
		{
			?>
			<script>
			<!--
			//top.location.replace = '<?=CUtil::JSEscape($arResult["REDIRECT_URL"])?>';
			window.top.location.href='<?=CUtil::JSEscape($arResult["REDIRECT_URL"])?>';
			//setInterval("window.top.location.href='<?=CUtil::JSEscape($arResult["REDIRECT_URL"])?>';",2000);
			//-->
			</script>
			<?
			die();
		}
		else
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/confirm.php");
	}
	else
	{
		$FORM_NAME = 'ORDERFORM_'.RandString(5);
		if(!empty($arResult["ERROR"]) && $arResult["USER_VALS"]["FINAL_STEP"] == "Y")
		{
			if(!empty($arResult["ERROR"]))
			{
				foreach($arResult["ERROR"] as $v){
					$zz = split('"',$v);
					//$errors_array[$zz[1]] = '<strong class="active">'.$v.'</strong>';
					$errors_array[$zz[1]] = '<strong class="active"> - Заполните поле</strong>';
				}
			}
			?>
			<script>
			top.location.hash = '#order_form';
			</script>
			<?
		}
		?>
		
		<script>
		<!--
		function submitForm(val)
		{
			if(val != 'Y') 
				document.getElementById('confirmorder').value = 'N';
			
			var orderForm = document.getElementById('ORDER_FORM_ID_NEW');
			jsAjaxUtil.InsertFormDataToNode(orderForm, 'order_form_div', false);
			orderForm.submit();
			return true;
		}
		//-->
		</script>
		<div style="display:none;">
			<div id="order_form_id">
				<?
				if(count($arResult["PERSON_TYPE"]) > 1)
				{
					?>
					<div class="order-item">
						<div class="order-title">
							<b class="r2"></b><b class="r1"></b><b class="r0"></b>
							<div class="order-title-inner">
								<span><?=GetMessage("SOA_TEMPL_PERSON_TYPE")?></span>
							</div>
						</div>
						<div class="order-info">
							<table width="100%" cellpadding="0" cellspacing="6">
								<tbody>
								<?
								foreach($arResult["PERSON_TYPE"] as $v)
								{
									?>
									<tr>
										<td valign="top" width="0%"><input type="radio" id="PERSON_TYPE_<?= $v["ID"] ?>" name="PERSON_TYPE" value="<?= $v["ID"] ?>"<?if ($v["CHECKED"]=="Y") echo " checked=\"checked\"";?> onclick="submitForm()"></td>
										<td valign="top" width="100%"><label for="PERSON_TYPE_<?= $v["ID"] ?>"><?= $v["NAME"] ?></label></td>
									</tr>
									<?
								}
								?>
							</tbody></table>
							<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$arResult["USER_VALS"]["PERSON_TYPE_ID"]?>">
						</div>
					</div>
					<?
				}
				else
				{
					if(IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"]) > 0)
					{
						?>
						<input type="hidden" name="PERSON_TYPE" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>">
						<input type="hidden" name="PERSON_TYPE_OLD" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>">
						<?
					}
					else
					{
						foreach($arResult["PERSON_TYPE"] as $v)
						{
							?>
							<input type="hidden" id="PERSON_TYPE" name="PERSON_TYPE" value="<?=$v["ID"]?>">11
							<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$v["ID"]?>">
							<?
						}
					}
				}
				
				$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array("INDEX"=>"10"),Array("MODE"=>"php"));
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");
				//include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");
				//include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");

				$i=0;
				foreach($arResult["BASKET_ITEMS"] as $arBasketItems){
					if ($arBasketItems['CALLBACK_FUNC']=='CatalogBasketCallback') $cc['outcoor']++;
	 				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pprint') $cc['pprint']++;
	 				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pdesign') $cc['pdesign']++;
	 				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pscreen') $cc['pscreen']++;
	 				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pmake') $cc['pmake']++;
	 			}

	 			if ($cc['outcoor']>0)
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary_outdoor.php");
				if ($cc['pprint']>0)
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary_pprint.php");
				if ($cc['pdesign']>0)
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary_pdesign.php");
				if ($cc['pscreen']>0)
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary_pscreen.php");
				if ($cc['pmake']>0)
					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary_pmake.php");

				?>
				<tr bgcolor="#FFFFFF">
					<td colspan="3">
						<table width="100%" border="0" cellpadding="5" cellspacing="0"><tr><td align="right">
						<strong>Итого:</strong>&nbsp;&nbsp;&nbsp;
						<strong><?=number_format($arResult["ORDER_PRICE"],2,","," ");?></strong>
						<br><br>
						</td></tr></table>
					</td>
				</tr>

				<?

				$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_footer.php',Array(),Array("MODE"=>"php"));
				?>
				<div align="right"><br>
					<input type="hidden" name="confirmorder" id="confirmorder" value="Y">
					<div class="order-buttons">
					<input type="button" name="submitbuttonb" onclick="window.top.location.href='/personal/cart/';" value="Вернуться в корзину">&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="button" name="submitbutton" onclick="submitForm('Y');" value="<?=GetMessage("SOA_TEMPL_BUTTON")?>">
					</div>
				</div>
			</div>
		</div>
		
		<div id="form_new"></div>
		<script>
		<!--
		var newform = document.createElement("FORM");
		newform.method = "POST";
		newform.action = "";
		newform.name = "<?=$FORM_NAME?>";
		newform.id = "ORDER_FORM_ID_NEW";
		var im = document.getElementById('order_form_id');
		document.getElementById("form_new").appendChild(newform);
		newform.appendChild(im);
		//-->
		</script>
		
		<?
	}
}
?>
</div>
</div>