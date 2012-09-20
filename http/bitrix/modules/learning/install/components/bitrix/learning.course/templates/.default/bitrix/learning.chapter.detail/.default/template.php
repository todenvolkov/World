<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["CHAPTER"])):?>

	<?if($arResult["CHAPTER"]["DETAIL_PICTURE_ARRAY"] !== false):?>
		<?=ShowImage($arResult["CHAPTER"]["DETAIL_PICTURE_ARRAY"]["SRC"], 250, 250, "hspace='8' vspace='1' align='left' border='0'", "", true);?>
	<?endif?>



	<?if (strlen($arResult["CHAPTER"]["DETAIL_TEXT"])>0):?>
		<br /><?=$arResult["CHAPTER"]["DETAIL_TEXT"]?>
	<?endif;?>

	<br clear="all" />

	<?if (!empty($arResult["CONTENTS"])):?>
	<div class="learn-chapter-contents">
		<b><?echo GetMessage("LEARNING_CHAPTER_CONTENTS");?>:</b>
		<?foreach ($arResult["CONTENTS"] as $arContent):?>
			<?=str_repeat("<ul>", $arContent["DEPTH_LEVEL"]);?>
			<li><a href="<?=$arContent["URL"]?>"><?=$arContent["NAME"]?></a></li>
			<?=str_repeat("</ul>", $arContent["DEPTH_LEVEL"]);?>
		<?endforeach?>
	</div>
	<?endif?>

<?endif?>