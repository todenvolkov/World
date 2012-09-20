<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/common.css" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/CSS.css" />
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/script.js"></script>
<title><?$APPLICATION->ShowTitle()?></title>

<!--[if lt IE 7]>
<style type="text/css">
	#compare {bottom:-1px; }
	div.catalog-admin-links { right: -1px; }
	div.catalog-item-card .item-desc-overlay {background-image:none;}
</style>
<![endif]-->

<!--[if IE]>
<style type="text/css">
	#fancybox-loading.fancybox-ie div	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_loading.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-close		{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_close.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-title-over	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_over.png', sizingMethod='scale'); zoom: 1; }
	.fancybox-ie #fancybox-title-left	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_left.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-title-main	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_main.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-title-right	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_right.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-left-ico		{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_nav_left.png', sizingMethod='scale'); }
	.fancybox-ie #fancybox-right-ico	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_nav_right.png', sizingMethod='scale'); }
	.fancybox-ie .fancy-bg { background: transparent !important; }
	.fancybox-ie #fancy-bg-n	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_n.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-ne	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_ne.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-e	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_e.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-se	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_se.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-s	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_s.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-sw	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_sw.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-w	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_w.png', sizingMethod='scale'); }
	.fancybox-ie #fancy-bg-nw	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_nw.png', sizingMethod='scale'); }
</style>
<![endif]-->

<style type="text/css">
<!--
.active {
	color: #005A66;
	font-style: italic;
	font-weight: bold;
}
-->
</style>
</head>
<body>

<table width="1000" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
 <tr> 
  <td width="20"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="10" height="10"></td>
  <td width="250"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="250" height="7"></td>
  <td width="250"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="250" height="7"></td>
  <td width="250"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="250" height="7"></td>
  <td width="250"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="250" height="7"></td>
  <td width="0"><img src="<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif" width="10" height="7"></td>
 </tr>
 <tr> 
  <td>&nbsp;</td>
  <td rowspan="2" class="Lbott"><span class="DivLogo"><a href="index.htm"><img src="<?=SITE_TEMPLATE_PATH?>/images/LogoViolet.png" width="177" height="127" border="0"></a></span></td>
  <td valign="top" class="C10">&nbsp;</td>
  <td valign="top" class="Lleft C10"><span class="Column Grd">Карта <br>
   Заказ рекламы<br>
   Мой заказ</span></td>
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
  <td class="Lbott">&nbsp;</td>
  <td class="Lbott">&nbsp;</td>
  <td class="Lbott">&nbsp;</td>
  <td>&nbsp;</td>
 </tr>
 <tr> 
  <td>&nbsp;</td>
  <td colspan="4" align="center" class="Ltop Lbott"> 
   <table width="90%" border="0" cellspacing="0" cellpadding="0">
	<tr> 
	 <td height="37" align="center" nowrap class="Menu"><strong><a href="Main.htm">Главная</a></strong></td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu"><strong class="active">OutDoor-Карта</strong></td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Печать</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Дизайн</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Производство</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Заказать</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Блог</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Менеджеры</td>
	 <td align="center" nowrap class="Menu">|</td>
	 <td align="center" nowrap class="Menu">Клиентам</td>
	</tr>
   </table>
  </td>
  <td>&nbsp;</td>
 </tr>
 <tr> 
  <td>&nbsp;</td>
  <td height="50" colspan="4" rowspan="2"> 
   <p>