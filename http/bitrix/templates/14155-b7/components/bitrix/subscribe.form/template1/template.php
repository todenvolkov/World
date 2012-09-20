<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="subscribe-form">
<form action="<?=$arResult["FORM_ACTION"]?>">
<table>
										<tr>
											<td width="240" height="5" style="background-image:url(<?=SITE_TEMPLATE_PATH?>/images/1_bg2.gif)"></td>
										</tr>
										<tr>
											<td width="240" height="106" style="background-image:url(<?=SITE_TEMPLATE_PATH?>/images/1_bg4.gif)">
												<br style="line-height:13px"><span class="head">Подписка на новости</span><br>

												<form action="" style="margin:0; padding:0 ">
												<div style="margin:2 0 0 23px ">
                                                    <?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
	<label for="sf_RUB_ID_<?=$itemValue["ID"]?>">
	<input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemValue["ID"]?>" value="<?=$itemValue["ID"]?>"<?if($itemValue["CHECKED"]) echo " checked"?> /> <?=$itemValue["NAME"]?>
	</label><br />
<?endforeach;?>									<br style="line-height:9px">
													<input name="sf_EMAIL" type="text" id="input1" value="<?=$arResult["EMAIL"]?>" title="<?=GetMessage("subscr_form_email_title")?>">
                                                    <input name="OK" type="image" id="submit" src="<?=SITE_TEMPLATE_PATH?>/images/1_k1.gif" alt="" value="<?=GetMessage("subscr_form_button")?>" ><br>
												</div>
												</form>
											</td>
										</tr>
										<tr>
											<td width="240" height="5" style="background-image:url(<?=SITE_TEMPLATE_PATH?>/images/1_bg3.gif)"></td>
										</tr>
										<tr>
											<td width="240" height="45"></td>
										</tr>
									</table>
</form>
</div>