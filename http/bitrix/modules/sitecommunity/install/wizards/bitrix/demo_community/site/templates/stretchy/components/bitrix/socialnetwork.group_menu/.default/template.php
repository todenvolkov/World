<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->SetViewTarget("sidebar", 100);
?>
<div class="rounded-block">
	<div class="corner left-top"></div>
	<div class="corner right-top"></div>
	<div class="block-content">
		<div class="content-list user-sidebar">
			<div class="content-item">
				<div class="content-avatar">
					<a<?if ($arResult["CurrentUserPerms"]["UserCanViewGroup"]):?> href="<?=$arResult["Urls"]["View"]?>"<?endif;?><?if (strlen($arResult["Group"]["IMAGE_FILE"]["src"]) > 0):?> style="background:url('<?=$arResult["Group"]["IMAGE_FILE"]["src"]?>') no-repeat scroll center center transparent;"<?endif;?>></a>
				</div>			
				<div class="content-info">
					<div class="content-title"><a <?if ($arResult["CurrentUserPerms"]["UserCanViewGroup"]):?> href="<?=$arResult["Urls"]["View"]?>"<?endif;?>><?=$arResult["Group"]["NAME"]?></a></div>
					<?if($arResult["Group"]["CLOSED"] == "Y"):?>
						<div class="content-description"><?= GetMessage("SONET_UM_ARCHIVE_GROUP") ?></div>
					<?endif;?>
				</div>
			</div>
			<?
			if ($arResult["CurrentUserPerms"]["UserCanSpamGroup"]):
				?><div class="content-links">
					<a href="<?= $arResult["Urls"]["MessageToGroup"] ?>" onclick="window.open('<?= $arResult["Urls"]["MessageToGroup"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=750,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 750)/2-5)); return false;"><i class="sidebar-profile-icon sidebar-profile-message"></i><span><?=GetMessage("SONET_UM_SEND_MESSAGE")?></span></a>
				</div><?
				endif;
			?>
		</div>
		<div class="hr"></div>
		<ul class="mdash-list">
			<li class="<?if ($arParams["PAGE_ID"] == "group"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["View"]?>"><?=GetMessage("SONET_UM_GENERAL")?></a></li>
			<?
			foreach ($arResult["CanView"] as $key => $val)
			{
				if (!$val)
					continue;
				?><li class="<?if ($arParams["PAGE_ID"] == "group_".$key):?>selected<?endif?>"><a href="<?= $arResult["Urls"][$key] ?>"><?=$arResult["Title"][$key]?></a></li><?
			}
			?>
			<li class="<?if ($arParams["PAGE_ID"] == "group_users"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["GroupUsers"]?>"><?=GetMessage("SONET_UM_USERS")?></a></li>
		</ul>
	</div>
	<div class="corner left-bottom"></div>
	<div class="corner right-bottom"></div>
</div>
<?
$this->EndViewTarget();
?>