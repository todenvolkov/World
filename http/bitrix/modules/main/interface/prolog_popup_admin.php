<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?echo $APPLICATION->GetTitle()?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<?
if(!is_object($adminPage))
	$adminPage = new CAdminPage();
$APPLICATION->AddBufferContent(array($adminPage, "ShowCSS"));
echo $adminPage->ShowScript();
$APPLICATION->ShowHeadScripts();
$APPLICATION->ShowHeadStrings();
?>
<script type="text/javascript">
function PopupOnKeyPress(e)
{
	if(!e) e = window.event
	if(!e) return;
	if(e.keyCode == 27)
		window.close();
}
jsUtils.addEvent(window, "keypress", PopupOnKeyPress);
</script>
</head>
<body class="body-popup" onkeypress="PopupOnKeyPress();">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td>