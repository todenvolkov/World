<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	?>
	<?if ($arResult["CurrentUserPerms"]["IsCurrentUser"] && $arResult["ALLOW_CREATE_GROUP"]):?>
		<div class="sonet-add-group-button">
		<a class="sonet-add-group-button-left" href="<?= $arResult["Urls"]["GroupsAdd"] ?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-fill"><a href="<?= $arResult["Urls"]["GroupsAdd"] ?>" class="sonet-add-group-button-fill-text"><?= GetMessage("SONET_C36_T_CREATE") ?></a></div>
		<a class="sonet-add-group-button-right" href="<?= $arResult["Urls"]["GroupsAdd"] ?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-clear"></div>
		</div><br>
	<?endif;?>
	<?
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>
	<?if ($arResult["CanViewLog"] && $arParams["PAGE"] != "groups_list"):?>
		<a href="<?= $arResult["Urls"]["LogGroups"] ?>"><?= GetMessage("SONET_C33_T_UPDATES") ?></a><br /><br />
	<?endif;?>
	<?if (StrLen($arResult["NAV_STRING"]) > 0):?>
		<?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?
	if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewgroups"])
	{
		if ($arResult["Groups"] && $arResult["Groups"]["List"])
		{
			foreach ($arResult["Groups"]["List"] as $group)
			{
				?>
				<table width="100%" border="0" class="sonet-user-profile-friend-box">
				<tr>
				<td width="105" nowrap valign="top" align="center">
					<?=$group["GROUP_PHOTO_IMG"];?>
				</td>
				<td valign="top">
				
				<div class="content-sidebar">
				<div class="content-change"><?= GetMessage("SONET_C24_T_ACTIVITY") ?>: <?= $group["FULL"]["DATE_ACTIVITY_FORMATTED"]; ?></div>
				<?
				if (IntVal($group["FULL"]["NUMBER_OF_MEMBERS"]) > 0)
				{
					?>
					<div class="content-members">
					<?= GetMessage("SONET_C24_T_MEMBERS") ?>: <?= $group["FULL"]["NUMBER_OF_MEMBERS"] ?>
					</div>
					<?
				}
				?>
				</div>
				<a href="<?=$group["GROUP_URL"]?>"><b><?=$group["GROUP_NAME"]?></b></a><br>
				<?
					if (strlen($group["FULL"]["SUBJECT_NAME"]) > 0)
					{
						?>
						<div class="content-subject"><?= GetMessage("SONET_C24_T_SUBJ") ?>: <?=$group["FULL"]["SUBJECT_NAME"]?></div>
						<?
					}
					?>
				<br /><?=$group["GROUP_DESCRIPTION"]?>
				<?if ($arParams["PAGE"] == "group_request_group_search")
				{
					if ($group["CAN_INVITE2GROUP"])
						echo "<br /><a href=\"".$group["GROUP_REQUEST_USER_URL"]."\"><b>".GetMessage("SONET_C36_T_INVITE")."</b></a>";
					else
						echo "<br /><b>".GetMessage("SONET_C36_T_INVITE_NOT")."</b>";
				}?>
				</td>
				</tr>
				</table>
				<br />
				<?
			}
		}
		else
		{
			echo GetMessage("SONET_C36_T_NO_GROUPS");
		}
	}
	else
	{
		echo GetMessage("SONET_C36_T_GR_UNAVAIL");
	}
	?>

	<?if (StrLen($arResult["NAV_STRING"]) > 0):?>
		<br><?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?
}
?>