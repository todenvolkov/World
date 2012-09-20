<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<?$APPLICATION->ShowHead();?>

<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/styles.css" />

<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/AC_RunActiveContent.js"></script>
<title><?$APPLICATION->ShowTitle()?></title>
</head>

<body>
<?
$TOP_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoGreen.png" width="177" height="127" border="0">';
$BOT_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoGreenF.png" width="220" height="51" border="0">';
$TOP_COLOR= '#005A66';
$current_page = $APPLICATION->GetCurPage();

if (strpos($current_page,'/catalog/')===0){
	$TOP_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoViolet.png" width="177" height="127" border="0">';
	$BOT_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoVioletF.png" width="219" height="51" border="0">';	
	$TOP_COLOR= '#AB0056';
}else if (strpos($current_page,'/print/')===0){
	$TOP_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoBrick.png" width="177" height="127" border="0">';
	$BOT_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoBrickF.png" width="218" height="50" border="0">';
	$TOP_COLOR= '#EA5E20';
}else if (strpos($current_page,'/personal/')===0){
	$TOP_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoPurple.png" width="177" height="127" border="0">';
	$BOT_LOGO = '<img src="'.SITE_TEMPLATE_PATH.'/images/LogoPurpleF.png" width="220" height="51" border="0">';	
	$TOP_COLOR= '#7A057A';

}


CModule::IncludeModule("sale");
$in_basket = CSaleBasket::GetList(false, array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"LID" => SITE_ID,"ORDER_ID" => "NULL","DELAY"=>"N","CAN_BUY"=>"Y"),false,false,array("ID" ))->SelectedRowsCount();
if ($in_basket>0){ $in_basket='('.$in_basket.')';}else{$in_basket='(0)';};

?>
<style type="text/css">
<!--
.active {
	color: <?=$TOP_COLOR;?>;
	font-style: italic;
	font-weight: bold;
}
-->
</style>


<div id="panel"><?$APPLICATION->ShowPanel();?></div>
<table width="930" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
<tr>
	<td width="20"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="10" height="10"></td>
	<td width="227"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="227" height="7"></td>
	<td width="227"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="227" height="7"></td>
	<td width="227"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="227" height="7"></td>
	<td width="227"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="227" height="7"></td>
	<td width="0"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="10" height="7"></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td rowspan="2"><span class="DivLogo"><a href="index.htm"><?=$TOP_LOGO;?></a></span></td>
	<td valign="top" class="C10">&nbsp;</td>
	<td valign="top" class="Lleft C10">
		<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "store", array("REGISTER_URL" => SITE_DIR."login/","PROFILE_URL" => SITE_DIR."personal/profile/","SHOW_ERRORS" => "N"),false,Array() );?>
		<br><br>
		<span class="Column">
			<a href="/personal/">Личный кабинет</a> <br>
			<a href="/personal/cart/">Корзина&nbsp;<span id="basket_num" class="active"><?=$in_basket?></span></a><br>
		</span>
	</td>
	<td valign="top" class="Lleft C10">
		<table width="230" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td nowrap class="Grd">Сообщений в блогах</td>
			<td align="right" nowrap class="Grd">1</td>
		</tr>
		<tr>
			<td nowrap class="Grd">Посетители</td>
			<td align="right" nowrap class="Grd">1 456782</td>
		</tr>
		<tr>
			<td nowrap class="Grd">Клиенты </td>
			<td align="right" nowrap class="Grd">123 45</td>
		</tr>
		<tr>
			<td nowrap class="Grd">Проекты</td>
			<td align="right" nowrap class="Grd">89</td>
		</tr>
		</table>
	</td>
	<td valign="top">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td colspan="4" align="center" class="Ltop2 Lbott">
		<?$APPLICATION->IncludeComponent("bitrix:menu", "mir_menu", Array(
	"ROOT_MENU_TYPE" => "top",	// Тип меню для первого уровня
	"MENU_CACHE_TYPE" => "Y",	// Тип кеширования
	"MENU_CACHE_TIME" => "36000000",	// Время кеширования (сек.)
	"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
	"MENU_CACHE_GET_VARS" => "",	// Значимые переменные запроса
	"MAX_LEVEL" => "1",	// Уровень вложенности меню
	"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
	"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
	),
	false
);?>
	</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td colspan="2">&nbsp;</td>
	<td align="left" valign="top">&nbsp;</td>
	<td align="left" valign="top">&nbsp;</td>
	<td align="left" valign="top">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td colspan="4">