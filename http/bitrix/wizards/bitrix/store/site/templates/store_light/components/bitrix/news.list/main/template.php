<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? if (count($arResult["ITEMS"]) < 1)
	return;
?>
<h2><a href="<?=SITE_DIR?>news/rss/" title="<?=GetMessage("SDNW_RSS")?>" class="rss-icon"></a><?=GetMessage("SDNW_TITLE")?></h2>
<dl class="block-list">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<dt><span><?=$arItem["DISPLAY_ACTIVE_FROM"]?></span> <a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a></dt>
	<dd><?=(strlen($arItem["PREVIEW_TEXT"])> 0 ? $arItem["PREVIEW_TEXT"] : '')?></dd>
<?endforeach;?>
</dl>

<a href="<?=SITE_DIR?>news/"><?=GetMessage("SDNW_ALLNEWS")?></a>
