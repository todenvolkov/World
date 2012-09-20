<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
$bEdit = ($_REQUEST['gdhtml']==$id) && ($_REQUEST['edit']=='true') && ($arParams["PERMISSION"]>"R");
if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['gdhtmlform']=='Y' && $_REQUEST['gdhtml']==$id)
{
	$arGadget["USERDATA"] = Array("content"=>$_POST["html_content"]);
	$arGadget["FORCE_REDIRECT"] = true;
}
$arData = $arGadget["USERDATA"];
$content = $arData["content"];
if(!$bEdit):
	?>
	<?=($content?$content:GetMessage("GD_HTML_AREA_NO_CONTENT"));?>
	<?if($arParams["PERMISSION"]>"R"):?><div class="gdhtmlareach" style="padding-top: 10px;"><a class="gdhtmlareachlink" href="?gdhtml=<?=$id?>&edit=true"><?echo GetMessage("GD_HTML_AREA_CHANGE_LINK")?></a></div><?endif?>
<?else:?>
<form action="?gdhtml=<?=$id?>" method="post" id="gdf<?=$id?>">
<?
$APPLICATION->IncludeComponent(
   "bitrix:fileman.light_editor",
   ".default",
   Array(
      "CONTENT" => $content,
      "INPUT_NAME" => "html_content",
      "WIDTH" => "100%",
      "HEIGHT" => "200px",
      "USE_FILE_DIALOGS" => "N",
      "RESIZABLE" => "Y",
      "AUTO_RESIZE" => "Y",
      "TOOLBAR_CONFIG" => array(
		'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat',
		'CreateLink', 'DeleteLink', 'Image', 'Video',
		'ForeColor', 'Justify',
		'InsertOrderedList', 'InsertUnorderedList',
		//'=|=', // new line
		'Outdent', 'Indent',
		/*'HeaderList', */'FontList', 'FontSizeList'
	)
   )
);?>
<input type="hidden" name="gdhtmlform" value="Y">
</form>
<script>
function gdhtmlsave()
{
	document.getElementById("gdf<?=$id?>").submit();
	return false;
}
</script>
<a href="javascript:void(0);" onclick="return gdhtmlsave();"><?echo GetMessage("GD_HTML_AREA_SAVE_LINK")?></a> | <a href="?"><?echo GetMessage("GD_HTML_AREA_CANCEL_LINK")?></a>
<?endif?>
