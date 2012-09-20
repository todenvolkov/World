<?
if($_SERVER["REDIRECT_STATUS"]=="404")
	define("ERROR_404","Y");

$params = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?")+1);
parse_str($params, $_GET);
$GLOBALS += $_GET;
$HTTP_GET_VARS = $_GET;

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$APPLICATION->SetAdditionalCSS("/bitrix/themes/".ADMIN_THEME_ID."/404.css");
$APPLICATION->SetTitle(GetMessage("404_title"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<div class="error-404">
<table cellspacing="0" cellpadding="0" border="0" class="error-404">
	<tr class="top">
		<td class="left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr>
		<td class="left"><div class="empty"></div></td>
		<td class="content">
			<div class="title">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td><div class="icon"></div></td>
						<td><?echo GetMessage("404_header")?></td>
					</tr>
				</table>
			</div>
			<div class="description"><?echo GetMessage("404_message")?></div>
		</td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr class="bottom">
		<td class="left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
</table>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>