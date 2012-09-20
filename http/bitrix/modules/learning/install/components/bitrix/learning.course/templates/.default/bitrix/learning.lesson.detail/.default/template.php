<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["LESSON"])):?>

	<?php if ($arResult["LESSON"]["DETAIL_TEXT_TYPE"] == "file"):?>
		<iframe width="100%" height="95%" src="<?php echo $arResult["LESSON"]["LAUNCH"]?>" frameborder="0"></iframe>
	<?php else:?>
		<?if($arResult["LESSON"]["SELF_TEST_EXISTS"]):?>
			<a href="<?=$arResult["LESSON"]["SELF_TEST_URL"]?>" title="<?=GetMessage("LEARNING_PASS_SELF_TEST")?>">
				<div title="<?echo GetMessage("LEARNING_PASS_SELF_TEST")?>" class="learn-self-test-icon float-right"></div>
			</a>
		<?endif?>

		<?if($arResult["LESSON"]["DETAIL_PICTURE_ARRAY"] !== false):?>
			<?=ShowImage($arResult["LESSON"]["DETAIL_PICTURE_ARRAY"]["SRC"], 250, 250, "hspace='8' vspace='1' align='left' border='0'", "", true);?>
		<?endif?>

		<?=$arResult["LESSON"]["DETAIL_TEXT"]?>

		<?if($arResult["LESSON"]["SELF_TEST_EXISTS"]):?>
			<div class="float-clear"></div>
			<br /><div title="<?echo GetMessage("LEARNING_PASS_SELF_TEST")?>" class="learn-self-test-icon float-left"></div>&nbsp;<a href="<?=$arResult["LESSON"]["SELF_TEST_URL"]?>"><?=GetMessage("LEARNING_PASS_SELF_TEST")?></a><br />
		<?endif?>
	<?php endif?>

<?endif?>