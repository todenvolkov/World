<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$rnd = rand();

$arGadgetParams["CNT"] = IntVal($arGadgetParams["CNT"]);

if($arGadgetParams["CNT"]>50)
	$arGadgetParams["CNT"] = 0;

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/rssreader/styles.css');

include_once(dirname(__FILE__).'/include.php');

$cache = new CPageCache();
if($arGadgetParams["CACHE_TIME"]>0 && !$cache->StartDataCache($arGadgetParams["CACHE_TIME"], 'c'.$arGadgetParams["RSS_URL"].'-'.$arGadgetParams["CNT"], "gdrss"))
	return;
?>
<?
if($arGadgetParams["RSS_URL"]=="")
{
	?>
	<div class="gdrsserror">
		<?echo GetMessage("GD_RSS_READER_NEW_RSS")?>
	</div>
	<?
	$cache->EndDataCache();
	return;
}

$rss = gdGetRss($arGadgetParams["RSS_URL"]);
if($rss):
	$rss->title = strip_tags($rss->title);
?>
<script>
function ShowHide<?=$rnd?>(id)
{
	var d = document.getElementById(id);
	if(d.style.display == 'none')
		d.style.display = 'block';
	else
		d.style.display = 'none';
}
</script>
<div class="gdrsstitle">
<?if($arGadgetParams["SHOW_URL"]=="Y"):?>
	<a href="<?=htmlspecialchars($rss->link)?>"><?=htmlspecialcharsEx($rss->title)?></a>
<?else:?>
	<?=htmlspecialcharsEx($rss->title)?>
<?endif?>
</div>
<div class="gdrssitems">
<?
$cnt = 0;
foreach($rss->items as $item):
	$cnt++;
	if($arGadgetParams["CNT"]>0 && $arGadgetParams["CNT"]<$cnt)
		break;
	$item["DESCRIPTION"] = strip_tags($item["DESCRIPTION"]);
	$item["TITLE"] = strip_tags($item["TITLE"]);
?>
<div class="gdrssitem">
	<div class="gdrssitemtitle">&raquo; <a href="javascript:void(0)" onclick="ShowHide<?=$rnd?>('z<?=$cnt.md5($item["TITLE"])?><?=$rnd?>')"><?=htmlspecialcharsEx($item["TITLE"])?></a></div>
	<div class="gdrssitemdetail" id="z<?=$cnt.md5($item["TITLE"])?><?=$rnd?>" style="display:none">
		<div class="gdrssitemdate"><?=htmlspecialcharsEx($item["PUBDATE"])?></div>
		<div class="gdrssitemdesc"><?=htmlspecialcharsEx($item["DESCRIPTION"])?> <?if($arGadgetParams["SHOW_URL"]=="Y"):?><a href="<?=htmlspecialchars($item["LINK"])?>"><?echo GetMessage("GD_RSS_READER_RSS_MORE")?></a></div><?endif?>
	</div>
</div>
<?endforeach;?>
</div>
<?else:?>
<div class="gdrsserror">
<?echo GetMessage("GD_RSS_READER_RSS_ERROR")?>
</div>
<?endif?>
<?$cache->EndDataCache();?>
