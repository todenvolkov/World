<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>
<?
//echo "<pre>"; print_r($arResult); echo "</pre>";
if (StrLen($arResult["ERROR_MESSAGE"])<=0)
{	
	$arUrlTempl = Array(
		"delete" => $APPLICATION->GetCurPage()."?action=delete&id=#ID#",
		"shelve" => $APPLICATION->GetCurPage()."?action=shelve&id=#ID#",
		"add" => $APPLICATION->GetCurPage()."?action=add&id=#ID#",
	);
	?>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form">
		<?
		//if ($arResult["ShowReady"]=="Y")
		if ($_REQUEST[delay]==""){
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");
		}else{
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delay.php");
		}
		//if ($arResult["ShowDelay"]=="Y")

		if ($_REQUEST["BasketMap"]){
			LocalRedirect('/catalog/?B=1');
		}

		?>
	</form>
	<?
}
else{
?>
<p>&nbsp;</p>
<p><b>
<?	
	ShowNote($arResult["ERROR_MESSAGE"]);
?>
</b></p>
<?
}
?>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
