<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/common.css" />
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/jquery/jquery-1.4.2.min.js"></script>
<?$APPLICATION->ShowHead();?>
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/colors.css" />
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

<script type="text/javascript">if (document.documentElement) { document.documentElement.id = "js" }</script>
</head>
<body>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
	<div id="page-wrapper">
		<div id="header-wrapper">
			<div id="header">
				<table id="logo" cellspacing="0">
					<tr>
						<td id="logo-image"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_logo.php"), false);?></td>
						<td id="logo-text"><a href="<?=SITE_DIR?>" title="<?=GetMessage("HEADER_TO_MAIN_PAGE")?>"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_name.php"), false);?></a></td>
					</tr>
				</table>
				<table id="schedule">
					<tr>
						<td>
							<div class="telephone"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/telephone.php"), false);?></div>
							<div class="schedule"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/schedule.php"), false);?></div>
						</td>
					</tr>
				</table>
				<div id="top-menu">
				<?
				$APPLICATION->IncludeComponent('bitrix:menu', "horizontal", array(
					"ROOT_MENU_TYPE" => "top",
					"MENU_CACHE_TYPE" => "Y",
					"MENU_CACHE_TIME" => "36000000",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => array(),
					"MAX_LEVEL" => "1",
					"USE_EXT" => "N",
					"ALLOW_MULTI_SELECT" => "N"
					)
				);
				?>
				</div>

				<div id="user-links">
					<?$APPLICATION->IncludeFile(
						SITE_DIR."include/user_links.php",
						Array(),
						Array("MODE"=>"text")
					);?>
				</div>
				
				<div id="cart">
					<div class="block-content">
						<p class="auth">
							<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "store", array(
								"REGISTER_URL" => SITE_DIR."login/",
								"PROFILE_URL" => SITE_DIR."personal/profile/",
								"SHOW_ERRORS" => "N"
								),
								false,
								Array()
							);?>
						</p>
						<span id="cart_line">
							<?
							$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", ".default", array(
								"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
								"PATH_TO_PERSONAL" => SITE_DIR."personal/",
								"SHOW_PERSONAL_LINK" => "N"
								),
								false,
								Array('')
							);
							?>
						</span>
					</div>
					<div class="corners"><div class="corner left-bottom"></div><div class="corner right-bottom"></div></div>
				</div>
				<?$APPLICATION->ShowProperty("CATALOG_COMPARE_LIST", "");?>
			</div>
		</div>
	
	
		<div id="content-wrapper">
		
			<div id="breadcrumb-search">
			 <?$APPLICATION->IncludeComponent("bitrix:search.title", "store", array(
				"NUM_CATEGORIES" => "1",
				"TOP_COUNT" => "5",
				"CHECK_DATES" => "N",
				"SHOW_OTHERS" => "Y",
				"PAGE" => "#SITE_DIR#search/",
				"CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS") ,
				"CATEGORY_0" => array(
					0 => "iblock_catalog",
				),
				"CATEGORY_0_iblock_catalog" => array(
					0 => "all",
				),
				"CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
				"SHOW_INPUT" => "Y",
				"INPUT_ID" => "title-search-input",
				"CONTAINER_ID" => "search"
				),
				false
			);?>
			<?
			$APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default", array(
				"START_FROM" => "1",
				"PATH" => "",
				"SITE_ID" => "-"
				),
				false,
				Array('HIDE_ICONS' => 'Y')
			);
			?>
			</div>
			
			<div id="content"<?$APPLICATION->ShowProperty("TEMPLATE_SIDEBAR_MODE", "");?>>
				<div id="left-column">
					<?
					$APPLICATION->IncludeComponent("bitrix:menu", "tree", array(
						"ROOT_MENU_TYPE" => "left",
						"MENU_CACHE_TYPE" => "A",
						"MENU_CACHE_TIME" => "36000000",
						"MENU_CACHE_USE_GROUPS" => "Y",
						"MENU_CACHE_GET_VARS" => array(
						),
						"MAX_LEVEL" => "4",
						"CHILD_MENU_TYPE" => "left",
						"USE_EXT" => "Y",
						"DELAY" => "N",
						"ALLOW_MULTI_SELECT" => "N"
						),
						false
					);
					?>
				</div>
				<?$APPLICATION->IncludeComponent(
					"bitrix:main.include",
					"sidebar",
					Array(
						"AREA_FILE_SHOW" => "sect", 
						"AREA_FILE_SUFFIX" => "inc", 
						"AREA_FILE_RECURSIVE" => "N", 
						"EDIT_MODE" => "html", 
					),
					false,
					Array('HIDE_ICONS' => 'Y')
				);?>	
				
				<div id="workarea">
					<h1 id="pagetitle"><?$APPLICATION->ShowTitle(false);?><?$APPLICATION->ShowProperty("ADDITIONAL_TITLE", "");?></h1>