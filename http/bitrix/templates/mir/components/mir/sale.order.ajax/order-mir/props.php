<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
function PrintPropsForm($errors_array,$arSource=Array())
{
	if (!empty($arSource))
	{
		foreach($arSource as $arProperties)
		{
			if ($arProperties["TYPE"] == "LOCATION" && count($arProperties["VARIANTS"]) == 1)
			{
				$arC = array_values($arProperties["VARIANTS"]);
				?>
				<tr style="display:none;"><td colspan="2" style="display:none;">
				<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="<?=$arC[0]["ID"]?>">
				</td></tr>
				<?
			}
			else
			{
				?>
				<tr>
					<td valign="top" align="left" width="240">
						<?=$arProperties["NAME"]?>:
						<?if($arProperties["REQUIED_FORMATED"]=="Y")
						{
							?> <span class="active">*</span><?
						}
						?>
					</td>
					<td width="501">
						<?
						if($arProperties["TYPE"] == "CHECKBOX")
						{
							?>
							
							<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="">
							<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?>>
							<?
						}
						elseif($arProperties["TYPE"] == "TEXT")
						{
							?>
							<input type="text" maxlength="250" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" style="width:300px;">
							<?
						}
						elseif($arProperties["TYPE"] == "SELECT")
						{
							?>
							<select name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>" style="width:300px;">
							<?
							foreach($arProperties["VARIANTS"] as $arVariants)
							{
								?>
								<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
								<?
							}
							?>
							</select>
							<?
						}
						elseif ($arProperties["TYPE"] == "MULTISELECT")
						{
							?>
							<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>" style="width:300px;">
							<?
							foreach($arProperties["VARIANTS"] as $arVariants)
							{
								?>
								<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
								<?
							}
							?>
							</select>
							<?
						}
						elseif ($arProperties["TYPE"] == "TEXTAREA")
						{
							?>
							<textarea rows="<?=$arProperties["SIZE2"]?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" style="width:300px;"><?=$arProperties["VALUE"]?></textarea>
							<?
						}
						elseif ($arProperties["TYPE"] == "LOCATION")
						{
							$value = 0;
							foreach ($arProperties["VARIANTS"] as $arVariant) 
							{
								if ($arVariant["SELECTED"] == "Y") 
								{
									$value = $arVariant["ID"]; 
									break;
								}
							}

							$GLOBALS["APPLICATION"]->IncludeComponent(
								'bitrix:sale.ajax.locations', 
								'', 
								array(
									"AJAX_CALL" => "N", 
									"COUNTRY_INPUT_NAME" => "COUNTRY_".$arProperties["FIELD_NAME"],
									"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
									"CITY_OUT_LOCATION" => "Y",
									"LOCATION_VALUE" => $value,
									"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
								),
								null,
								array('HIDE_ICONS' => 'Y')
							);
						}
						elseif ($arProperties["TYPE"] == "RADIO")
						{
							foreach($arProperties["VARIANTS"] as $arVariants)
							{
								?>
								<input type="radio" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>" value="<?=$arVariants["VALUE"]?>"<?if($arVariants["CHECKED"] == "Y") echo " checked";?>> <label for="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"><?=$arVariants["NAME"]?></label><br />
								<?
							}
						}

						if (strlen($arProperties["DESCRIPTION"]) > 0)
						{
							?><br /><small><?echo $arProperties["DESCRIPTION"] ?></small><?
						}
						?>
						<span id="<?=$arProperties["NAME"]?>"><?=$errors_array[$arProperties["NAME"]]?></span>
					</td>
				</tr>
				<?
			}
		}

		return true;
	}
	return false;
}
?>
<tr bgcolor="#FFFFFF">
	<td class="C10_Left_Column" valign="top">
		<table width="240" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="Ltop2">
					<p>&nbsp;</p>
					<p class="Header">Контактные данные</p>
				</td>
			</tr>
		</table>
	</td>
	<td class="C10_Right_Column Lleft" valign="top" width="750" colspan="2">
		<table width="100%" border="0" cellspacing="0" cellpadding="5">
                <td class="Ltop2" width="260"><p>&nbsp;</p></td>
                <td class="Ltop2" width="501"><p>&nbsp;</p></td>
              

<?              
		PrintPropsForm($errors_array,$arResult["ORDER_PROP"]["USER_PROPS_Y"]);
		PrintPropsForm($errors_array,$arResult["ORDER_PROP"]["USER_PROPS_N"]);
?>              
		</table>              
	</td>
</tr>
