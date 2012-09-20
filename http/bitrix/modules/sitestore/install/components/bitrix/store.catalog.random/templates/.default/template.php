<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(!empty($arResult['NAME']) > 0): ?>
<div class="content-block content-block-special">

	<h3><?=GetMessage("CR_TITLE")?></h3>

	<div class="special-product">
		<?if(is_array($arResult["PICTURE_PREVIEW"])):?>
			<div class="item-image"><a href="<?=$arResult["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arResult["PICTURE_PREVIEW"]["SRC"]?>" width="<?=$arResult["PICTURE_PREVIEW"]["WIDTH"]?>" height="<?=$arResult["PICTURE_PREVIEW"]["HEIGHT"]?>" alt="<?=$arResult['NAME']?>" title="<?=$arResult['NAME']?>" /></a></div>
		<?endif;?>

		<div class="item-name"><a href="<?=$arResult["DETAIL_PAGE_URL"]?>"><?=$arResult['NAME']?></a></div>
		
		<? if (strlen($arResult["DESCRIPTION"]) > 0):?>
		<div class="item-desc"><?=$arResult["DESCRIPTION"]?></div>
		<?endif?>

		<div class="item-price">
		<?if ($arResult['bDiscount']):?>
			<span><?=$arResult['PRICE']['DISCOUNT_PRICE_F']?></span> <s><?=$arResult['PRICE']['PRICE_F']?></s>
		<?else:?>
			<span><?=$arResult['PRICE']['PRICE_F']?></span>
		<?endif;?>
		</div>
	</div>

</div>
<?elseif($USER->IsAdmin()):?>
<div class="content-block content-block-special">
	<h3><?=GetMessage("CR_TITLE")?></h3>

	<?=GetMessage("CR_TITLE_NULL")?>
</div>
<?endif;?>
