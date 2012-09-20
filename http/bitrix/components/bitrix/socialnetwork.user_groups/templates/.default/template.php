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
		</div>
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
	<div class="sonet-cntnr-user-groups">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th><?= GetMessage("SONET_C36_T_GROUPS") ?></th>
		</tr>
		<tr>
			<td>
				<?
				if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewgroups"])
				{
					if ($arResult["Groups"] && $arResult["Groups"]["List"])
					{
						?>
						<table width="100%" border="0" class="sonet-user-profile-friend-box">
						<?
						$ind = 0;
						foreach ($arResult["Groups"]["List"] as $group)
						{
							if ($ind % $arParams["COLUMNS_COUNT"] == 0)
								echo "<tr>";
							echo "<td align=\"center\" valign=\"top\"".(($arParams["PAGE"] == "group_request_group_search" && !$group["CAN_INVITE2GROUP"]) ? " class=\"sonet_unactive\"" : "").">";
							echo $group["GROUP_PHOTO_IMG"];
							echo "<br>";
							echo "<a href=\"".$group["GROUP_URL"]."\">".$group["GROUP_NAME"]."</a><br>";
							echo $group["GROUP_DESCRIPTION"];
							if ($arParams["PAGE"] == "group_request_group_search")
							{
								if ($group["CAN_INVITE2GROUP"])
									echo "<br /><a href=\"".$group["GROUP_REQUEST_USER_URL"]."\"><b>".GetMessage("SONET_C36_T_INVITE")."</b></a>";
								else
									echo "<br /><b>".GetMessage("SONET_C36_T_INVITE_NOT")."</b>";
							}
							echo "</td>";
							$ind++;
							if ($ind % $arParams["COLUMNS_COUNT"] == 0)
								echo "</tr>";
						}
						?>
						</table>
						<?
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
			</td>
		</tr>
	</table>
	</div>
	<?if (StrLen($arResult["NAV_STRING"]) > 0):?>
		<br><?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<?
}
?>