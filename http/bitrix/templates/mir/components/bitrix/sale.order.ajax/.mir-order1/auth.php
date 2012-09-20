<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
<!--
function ChangeGenerate(val)
{
	if(val)
	{
		document.getElementById("sof_choose_login").style.display='none';
	}
	else
	{
		document.getElementById("sof_choose_login").style.display='block';
		document.getElementById("NEW_GENERATE_N").checked = true;
	}

	try{document.order_reg_form.NEW_LOGIN.focus();}catch(e){}
}
//-->
</script>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>

<?if($errors_array!=""):?>
	<p>&nbsp;</p>
	<span class="active"><?echo $errors_array?></span>
<?endif;?>
<table border="0" cellspacing="0" cellpadding="0">    
	<tr>
		<td valign="top" width="250">
			<p>&nbsp;</p>
			<p class="Header">
				<?if($arResult["AUTH"]["new_user_registration"]=="Y"):?>
					<b><?echo GetMessage("STOF_2REG")?></b>
				<?endif;?>
			</p>
			<p class="Header">&nbsp;</p>
			<table border="0" cellspacing="0" cellpadding="0">
			<form method="post" action="<?= $arParams["PATH_TO_ORDER"] ?>" name="order_auth_form">
			<tbody>
				<tr>
					<td><?echo GetMessage("STOF_LOGIN_PROMT")?></td>
				</tr>

				<tr>
					<td nowrap><p>&nbsp;</p><?echo GetMessage("STOF_LOGIN")?> <span class="sof-req">*</span><br />
					<input type="text" name="USER_LOGIN" maxlength="30" size="30" value="<?=$arResult["AUTH"]["USER_LOGIN"]?>">&nbsp;&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td nowrap><p>&nbsp;</p><?echo GetMessage("STOF_PASSWORD")?> <span class="sof-req">*</span><br />
					<input type="password" name="USER_PASSWORD" maxlength="30" size="30">&nbsp;&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td nowrap><p>&nbsp;</p><a href="/personal/profile/?forgot_password=yes&back_url=<?= urlencode($arParams["PATH_TO_ORDER"]); ?>"><?echo GetMessage("STOF_FORGET_PASSWORD")?></a></td>
				</tr>
				<tr>
					<td nowrap align="center">
						<input type="submit" value="<?echo GetMessage("STOF_NEXT_STEP")?>">
						<input type="hidden" name="do_authorize" value="Y">
					</td>
				</tr>
			</tbody>
			</form>
			</table>
		</td>
		<td valign="top" width="500">
			<p>&nbsp;</p>
			<p class="Header">
				<?if($arResult["AUTH"]["new_user_registration"]=="Y"):?>
					<b><?echo GetMessage("STOF_2NEW")?></b>
				<?endif;?>          
			</p>
			<?if($arResult["AUTH"]["new_user_registration"]=="Y"):?>
				<form method="post" action="<?= $arParams["PATH_TO_ORDER"]?>" name="order_reg_form">
					<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td nowrap>
								<p>&nbsp;</p>
								<?echo GetMessage("STOF_NAME")?> <span class="sof-req">*</span><br />
								<input type="text" name="NEW_NAME" size="40" value="<?=$arResult["AUTH"]["NEW_NAME"]?>">&nbsp;&nbsp;&nbsp;
							</td>
						</tr>
						<tr>
							<td nowrap>
								<p>&nbsp;</p>
								<?echo GetMessage("STOF_LASTNAME")?> <span class="sof-req">*</span><br />
								<input type="text" name="NEW_LAST_NAME" size="40" value="<?=$arResult["AUTH"]["NEW_LAST_NAME"]?>">&nbsp;&nbsp;&nbsp;
							</td>
						</tr>
						<tr>
							<td nowrap>
								<p>&nbsp;</p>
								E-Mail <span class="sof-req">*</span><br />
								<input type="text" name="NEW_EMAIL" size="40" value="<?=$arResult["AUTH"]["NEW_EMAIL"]?>">&nbsp;&nbsp;&nbsp;
							</td>
						</tr>
						<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
						<tr>
							<td nowrap><p>&nbsp;</p><input type="radio" id="NEW_GENERATE_N" name="NEW_GENERATE" value="N" OnClick="ChangeGenerate(false)"<?if ($_POST["NEW_GENERATE"] == "N") echo " checked";?>> <label for="NEW_GENERATE_N"><?echo GetMessage("STOF_MY_PASSWORD")?></label></td>
						</tr>
						<?endif;?>
						<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
						<tr>
							<td>
								<p>&nbsp;</p>
								<div id="sof_choose_login">
									<table>
						<?endif;?>
										<tr>
											<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
											<td width="0%">&nbsp;&nbsp;&nbsp;</td>
											<?endif;?>
											<td><?echo GetMessage("STOF_LOGIN")?> <span class="sof-req">*</span><br />
												<input type="text" name="NEW_LOGIN" size="30" value="<?=$arResult["AUTH"]["NEW_LOGIN"]?>">
											</td>
										</tr>
										<tr>
											<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
											<td width="0%">&nbsp;&nbsp;&nbsp;</td>
											<?endif;?>
											<td>
												<?echo GetMessage("STOF_PASSWORD")?> <span class="sof-req">*</span><br />
												<input type="password" name="NEW_PASSWORD" size="30">
											</td>
										</tr>
										<tr>
											<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
											<td width="0%">&nbsp;&nbsp;&nbsp;</td>
											<?endif;?>
											<td>
												<?echo GetMessage("STOF_RE_PASSWORD")?> <span class="sof-req">*</span><br />
												<input type="password" name="NEW_PASSWORD_CONFIRM" size="30">
											</td>
										</tr>
						<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
									</table>
								</div>
							</td>
						</tr>
						<?endif;?>
						<?if($arResult["AUTH"]["new_user_registration_email_confirmation"] != "Y"):?>
						<tr>
							<td>
								<input type="radio" id="NEW_GENERATE_Y" name="NEW_GENERATE" value="Y" OnClick="ChangeGenerate(true)"<?if ($POST["NEW_GENERATE"] != "N") echo " checked";?>> <label for="NEW_GENERATE_Y"><?echo GetMessage("STOF_SYS_PASSWORD")?></label>
								<script language="JavaScript">
								<!--
								ChangeGenerate(<?= (($_POST["NEW_GENERATE"] != "N") ? "true" : "false") ?>);
								//-->
								</script>
							</td>
						</tr>
						<?endif;?>
						<?
						if($arResult["AUTH"]["captcha_registration"] == "Y") //CAPTCHA
						{
							?>
							<tr>
								<td><br /><b><?=GetMessage("CAPTCHA_REGF_TITLE")?></b></td>
							</tr>
							<tr>
								<td>
									<input type="hidden" name="captcha_sid" value="<?=$arResult["AUTH"]["capCode"]?>">
									<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["AUTH"]["capCode"]?>" width="180" height="40" alt="CAPTCHA">
								</td>
							</tr>
							<tr valign="middle">
								<td>
									<span class="sof-req">*</span><?=GetMessage("CAPTCHA_REGF_PROMT")?>:<br />
									<input type="text" name="captcha_word" size="30" maxlength="50" value="">
								</td>
							</tr>
							<?
						}
						?>
						<tr>
							<td align="center">
								<p>&nbsp;</p>
								<input type="submit" value="<?echo GetMessage("STOF_NEXT_STEP")?>">
								<input type="hidden" name="do_register" value="Y">
							</td>
						</tr>
					</table>
				</form>
			<?endif;?>
		</td>
	</tr>
</table>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
