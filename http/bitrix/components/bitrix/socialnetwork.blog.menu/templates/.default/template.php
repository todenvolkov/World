<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
?>
<?
if (strlen($arResult["urlToNewPost"])>0 || (strlen($arResult["urlToDraft"])>0 && IntVal($arResult["CntToDraft"]) > 0) || (strlen($arResult["urlToModeration"])>0 && IntVal($arResult["CntToModerate"]) > 0))
{
	?>
	<div class="blog-menu-box">
		<?
		if (strlen($arResult["urlToNewPost"]) > 0)
		{
			?><span class="blog-menu-post"><a href="<?=$arResult["urlToNewPost"]?>" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_ADD_MESSAGE")?></a></span><?
		}
		if (strlen($arResult["urlToDraft"]) > 0 && IntVal($arResult["CntToDraft"]) > 0)
		{
			?>
			<span class="blog-vert-separator">|</span>
			<span class="blog-menu-post"><a href="<?=$arResult["urlToDraft"]?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_DRAFT_MESSAGES")?><?if(IntVal($arResult["CntToDraft"]) > 0) echo " (".$arResult["CntToDraft"].")"?></a></span>
			<?
		}
		if (strlen($arResult["urlToModeration"]) > 0 && IntVal($arResult["CntToModerate"]) > 0)
		{
			?>
			<span class="blog-vert-separator">|</span>
			<span class="blog-menu-post"><a href="<?=$arResult["urlToModeration"]?>"  title="<?=GetMessage("BLOG_MENU_MODERATION_MESSAGES_TITLE")?>"><?=GetMessage("BLOG_MENU_MODERATION_MESSAGES")?><?if(IntVal($arResult["CntToModerate"]) > 0) echo " (".$arResult["CntToModerate"].")"?></a></span>
			<?
		}
		?>
	</div>
	<?
}
?>