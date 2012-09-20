<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["ITEMS"])):?>

<div class="learn-course-tree">
	<ul>

	<?$previousLevel = 0;foreach ($arResult["ITEMS"] as $arItem):?>

		<?if ($previousLevel && $arItem["DEPTH_LEVEL"] <= $previousLevel):?>
			<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"] + 1));?>
		<?endif?>

		<?if ($arItem["TYPE"] == "CH"):$previousLevel = $arItem["DEPTH_LEVEL"]?>
			<li<?if($arItem["CHAPTER_OPEN"] === false):?> class="close"<?elseif($arItem["SELECTED"] === true):?> class="selected"<?endif?>>
				<div class="chapter" onClick="JMenu.OpenChapter(this,'<?=$arItem["ID"]?>')"></div>
				<div class="item-text"><a href="<?=$arItem["URL"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["NAME"]?></a></div>
				<ul>
		<?elseif($arItem["TYPE"] == "LE"):?>
			<li>
				<div class="lesson"></div>
				<div class="item-text"><a href="<?=$arItem["URL"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["NAME"]?></a></div>
			</li>
		<?elseif($arItem["TYPE"] == "CD"):?>
			<li>
				<div class="course-detail"></div>
				<div class="item-text"><a href="<?=$arItem["URL"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["NAME"]?></a></div>
			</li>
		<?elseif($arItem["TYPE"] == "TL"):?>
			<li>
				<div class="test-list"></div>
				<div class="item-text"><a href="<?=$arItem["URL"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["NAME"]?></a></div>
			</li>
		<?endif?>

	<?endforeach?>

	</ul>
</div>

<script type="text/javascript">
	var JMenu = new JCMenu('<?=(array_key_exists("LEARN_MENU_".$arParams["COURSE_ID"],$_COOKIE ) ? CUtil::JSEscape($_COOKIE["LEARN_MENU_".$arParams["COURSE_ID"]]) :"")?>', '<?=$arParams["COURSE_ID"]?>');
</script>

<?endif?>