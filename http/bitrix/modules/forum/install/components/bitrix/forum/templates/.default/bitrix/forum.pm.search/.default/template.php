<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html>
<head>
	<meta  http-equiv="Content-Type" content="text/html; charset='<?=$arResult["SITE_CHARSET"]?>'">
	<title><?=GetMessage("PM_TITLE")?></title>
	<?$APPLICATION->ShowHead()?>
	<style type=text/css>
		body{background-color:white;}
		div.forum-pmessage-search-user label{
			width:4em;}
	</style>
</head>
<body class="forum-popup-body">

<?
if ($arResult["SHOW_SELF_CLOSE"] == "Y"):
?>
<script type="text/javascript">
	<?if ($arResult["SHOW_MODE"] == "none"):?>
		window.parent.document.getElementById("div_USER_ID").innerHTML='<i><?=CUtil::JSEscape(GetMessage("PM_NOT_FINED"));?></i>';
	<?elseif ($arResult["SHOW_MODE"] == "light"):?>
		window.parent.document.getElementById("div_USER_ID").innerHTML='<?=CUtil::JSEscape(GetMessage("PM_IS_FINED"));?>';
	<?elseif ($arResult["SHOW_MODE"] == "full"):?>
		window.parent.document.getElementById("div_USER_ID").innerHTML='<?=Cutil::JSEscape("[<a href=\"".$arResult["profile_view"]."\">".$arResult["SHOW_NAME"]."</a>]")?>';
	<?else:?>
	opener.switcher='<?=CUtil::JSEscape($arResult["SHOW_NAME"])?>';
	var handler = opener.document.getElementById('USER_ID');
	if (handler)
		handler.value = '<?=$arResult["UID"]?>';
	handler = opener.document.getElementById('div_USER_ID');
	if (handler)
		handler.innerHTML = '[<a href="<?=CUtil::JSEscape($arResult["profile_view"])?>"><?=CUtil::JSEscape($arResult["SHOW_NAME"])?></a>]';
	handler = opener.document.getElementById('input_USER_ID');
	if (handler)
		handler.value = '<?=CUtil::JSEscape($arResult["SHOW_NAME"])?>';
	<?endif;?>
	self.close();
</script>
<?
die();
endif;
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<form action="<?=$APPLICATION->GetCurPageParam("", array(BX_AJAX_PARAM_ID))?>" method=GET>
	<input type="hidden" name="PAGE_NAME" value="pm_search" />
	<input type=hidden value="Y" name="do_search" />
	<?=bitrix_sessid_post()?>
	<?/*?><?=GetMessage("PM_SEARCH_PATTERN")?><?*/?>
	<div class="forum-filter-field forum-pmessage-search-user search-input">
		<label for="<?=$res["ID"]?>"><?=GetMessage("PM_SEARCH_INSERT")?>:</label>
		<span><input type="text" class="search-input" name="search_template" id="search_template" value="<?=$arResult["search_template"]?>" />
		<input type=submit value="<?=GetMessage("PM_SEARCH")?>" name="do_search1" class="inputbutton" /></span>
	</div>
<?/*?>	
	<div class="forum-filter-field forum-filter-footer">
			
			<input type=button value="<?=GetMessage("PM_CANCEL")?>" onclick='self.close();' class=inputbutton>
		<div class="forum-clear-float"></div>
	</div><?*/?>
	<div class="forum-clear-float"></div>
</form>
	</div>
</div>

<?
if ($arResult["SHOW_SEARCH_RESULT"] == "Y"):
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("PM_TITLE")?></span></div>
</div>
<?
?>
<div class="forum-info-box forum-users">
	<div class="forum-info-box-inner">
<?
if (!empty($arResult["SEARCH_RESULT"])):
$iStartNumber = (($arResult["NAV_RESULT"]->NavPageNomer-1)*$arResult["NAV_RESULT"]->NavPageSize);
$iStartNumber = ($iStartNumber > 0 ? $iStartNumber : 1);
?>
	<ol start="<?=$iStartNumber?>">
<?
	foreach ($arResult["SEARCH_RESULT"] as $res):
?>
	<li>
		<a href="<?=$res["link"]?>"><?=$res["SHOW_ABC"]?></a>
	</li>
<?
	endforeach;
?>
	</ol>
<?
else:
?>
	<?=GetMessage("PM_SEARCH_NOTHING")?>
<?
endif;
?>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
endif;
?>
</body>
</html>
