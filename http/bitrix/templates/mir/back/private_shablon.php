<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>
<tbody>
<div class="content-form login-form">
<div class="fields">
	<tr bgcolor="#FFFFFF">
		<td valign="top">
			<table width="230" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="Ltop2">
					</td>
				</tr>
			</table>
		</td>
		<td valign="top" class="Ltop2">
			
		</td>
		<td valign="top" class="Ltop2 C10">

		</td>
	</tr>

<script type="text/javascript">
<?
if (strlen($arResult["LAST_LOGIN"])>0)
{
?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<?
}
else
{
?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<?
}
?>
</script>

</div>
</div>
</tbody>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
