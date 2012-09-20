<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(!empty($arResult))
{
	if(!empty($arResult["CATEGORY"]))
	{
		?>
		<ul>
		<li class="blog-tags">
			<h3 class="blog-sidebar-title"><?=GetMessage("BLOG_BLOG_BLOGINFO_CAT")?></h3>
			<noindex>
			<ul>
			<?
			foreach($arResult["CATEGORY"] as $arCategory)
			{
				?>
				<li>
				<?
				if($arCategory["SELECTED"]=="Y")
					echo "<b>";
				?>
				<a href="<?=$arCategory["urlToCategory"]?>" title="<?GetMessage("BLOG_BLOG_BLOGINFO_CAT_VIEW")?>" rel="nofollow"><?=$arCategory["NAME"]?></a>
				<?
				if($arCategory["SELECTED"]=="Y")
					echo "</b>";
				?>
				</li>
				<?
			}
			?>
			</ul>
			</noindex>
		</li>
		</ul>
		<?
	}
}
?>	
