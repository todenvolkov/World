<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$itemsCnt = count($arResult['ITEMS']);
$delUrlID = "";
?>
<div class="compare-list-result">
	<div class="inline-filter compare-filter">
		<label><?=GetMessage('CATALOG_CHARACTERISTICS_LABEL')?>:</label>&nbsp;
		<?
		if($arResult["DIFFERENT"]):
		?>
				<a href="<?=htmlspecialchars($APPLICATION->GetCurPageParam("DIFFERENT=N",array("DIFFERENT")))?>" rel="nofollow"><?=GetMessage("CATALOG_ALL_CHARACTERISTICS")?></a>&nbsp;<b><?=GetMessage("CATALOG_ONLY_DIFFERENT")?></b>
		<?
		else:
		?>
				<b><?=GetMessage("CATALOG_ALL_CHARACTERISTICS")?></b>&nbsp;<a href="<?=htmlspecialchars($APPLICATION->GetCurPageParam("DIFFERENT=Y",array("DIFFERENT")))?>" rel="nofollow"><?=GetMessage("CATALOG_ONLY_DIFFERENT")?></a>
		<?
		endif;
		?>
	</div>

<?
if(!empty($arResult["PROP_ROWS"]))
{
	?>
	<!--noindex-->
	<div class="compare-props">
		<table class="compare-props" cellspacing="0">
		<tbody>
			<?
			foreach($arResult["PROP_ROWS"] as $arProp)
			{
				?>
				<tr>
				<?
				foreach($arProp as $propCode)
				{
					?>
					<td>
					<?
					if(!empty($arResult["SHOW_PROPERTIES"][$propCode]))
					{
						$url = htmlspecialchars($APPLICATION->GetCurPageParam("action=DELETE_FEATURE&pr_code=".$propCode,array("pr_code","action")));
						?>
						<input type="checkbox" id="<?=$propCode?>" checked="checked" onclick="location.href='<?=CUtil::JSEscape($url)?>'"><a href="<?=$url?>" rel="nofollow"><label for="<?=$propCode?>"><?=$arResult["SHOW_PROPERTIES"][$propCode]["NAME"]?></label></a>
						<?
					}
					elseif(!empty($arResult["DELETED_PROPERTIES"][$propCode]))
					{
						$url = htmlspecialchars($APPLICATION->GetCurPageParam("action=ADD_FEATURE&pr_code=".$propCode,array("pr_code","action")));
						?>
						<input type="checkbox" id="<?=$propCode?>" onclick="location.href='<?=CUtil::JSEscape($url)?>'"><a href="<?=$url?>" rel="nofollow"><label for="<?=$propCode?>" class="unchecked"><?=$arResult["DELETED_PROPERTIES"][$propCode]["NAME"]?></label></a>
						<?
					}
					?>
					</td>
					<?
				}
				?>
				</tr>
				<?
			}
			?>
		</tbody>
		</table>
	</div>
	<!--/noindex-->
	<?
}
$i = 0;
?>
	<div class="compare-grid">
		<?if($itemsCnt > 4):?>
			<table class="compare-grid" cellspacing="0" style="width:<?=($itemsCnt*25 + 25)?>%; table-layout: fixed;">
		<?else:?>
			<table class="compare-grid" cellspacing="0">
				<col />
				<col span="<?=$itemsCnt?>" width="<?=round(100/$itemsCnt)?>%" />
		<?endif;?>
			<thead>
				<tr>
					<td class="compare-property"><?=GetMessage("CATALOG_NAME")?></td>
<?
foreach($arResult["ITEMS"] as $arElement):
$delUrlID .= "&ID[]=".$arElement["ID"];
?>
					<td><a class="compare-delete-item" href="<?=htmlspecialchars($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_RESULT&IBLOCK_ID=".$arParams['IBLOCK_ID']."&ID[]=".$arElement['ID'],array("action", "IBLOCK_ID", "ID")))?>" title="<?=GetMessage("CATALOG_REMOVE_PRODUCT")?>"></a><a href="<?=$arElement['DETAIL_PAGE_URL']?>"><?=$arElement['NAME']?></a></td>
<?
endforeach;
?>
				</tr>
			</thead>
			<tbody>
			

<?
$i++;
foreach($arResult["ITEMS"][0]["FIELDS"] as $code=>$field):
	if($code == "NAME")
		continue;
?>
				<tr<?if($i%2 == 0) echo ' class="alt"';?>>
					<td class="compare-property"><?=GetMessage("IBLOCK_FIELD_".$code)?></td>
<?
	foreach($arResult["ITEMS"] as $arElement):
?>
					<td>
<?
		switch($code):
			case "NAME":
?>
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement[$code]?></a>
<?
			break;
			case "PREVIEW_PICTURE":
			case "DETAIL_PICTURE":
				if(is_array($arElement["FIELDS"][$code])):
?>
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arElement["FIELDS"][$code]["PREVIEW_IMG"]["SRC"]?>" width="<?=$arElement["FIELDS"][$code]["PREVIEW_IMG"]["WIDTH"]?>" height="<?=$arElement["FIELDS"][$code]["PREVIEW_IMG"]["HEIGHT"]?>" alt="<?=$arElement["FIELDS"][$code]["ALT"]?>" /></a>
<?
				endif;
			break;
			default:
				echo $arElement["FIELDS"][$code];
			break;
		endswitch;
?>
					</td>
<?
	endforeach;
?>
				</tr>
<?
$i++;
endforeach;

foreach($arResult["ITEMS"][0]["PRICES"] as $code=>$arPrice):
	if($arPrice["CAN_ACCESS"]):
?>
				<tr<?if($i%2 == 0) echo ' class="alt"';?>>
					<td class="compare-property"><?=$arResult["PRICES"][$code]["TITLE"]?></td>
<?
		foreach($arResult["ITEMS"] as $arElement):
?>
					<td>
<?
			if($arElement["PRICES"][$code]["CAN_ACCESS"]):
				echo $arElement["PRICES"][$code]["PRINT_VALUE"];
			endif;
?>
					</td>
<?
		endforeach;
?>
				</tr>
<?
	$i++;
	endif;
endforeach;
foreach($arResult["SHOW_PROPERTIES"] as $code=>$arProperty):
	$arCompare = Array();
	foreach($arResult["ITEMS"] as $arElement)
	{
		$arPropertyValue = $arElement["DISPLAY_PROPERTIES"][$code]["VALUE"];
		if(is_array($arPropertyValue))
		{
			sort($arPropertyValue);
			$arPropertyValue = implode(" / ", $arPropertyValue);
		}
		$arCompare[] = $arPropertyValue;
	}
	$diff = (count(array_unique($arCompare)) > 1 ? true : false);
	if($diff || !$arResult["DIFFERENT"]):
?>
				<tr<?if($i%2 == 0) echo ' class="alt"';?>>
					<td class="compare-property"><?=$arProperty["NAME"]?></td>
<?
		foreach($arResult["ITEMS"] as $arElement):
			if($diff):
?>
					<td>
<?
				echo (
					is_array($arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])
					? implode("/ ", $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])
					: $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]
				);
?>
					</td>
<?
			else:
?>
					<td>
<?
				echo (
					is_array($arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])
					? implode("/ ", $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])
					: $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]
				);
?>
					</td>
<?
			endif;
		endforeach;
?>
				</tr>
<?
	$i++;
	endif;
endforeach;
?>
			</tbody>
		</table>
	</div>
<?
if(strlen($delUrlID) > 0)
{
	$delUrl = htmlspecialchars($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_RESULT&IBLOCK_ID=".$arParams['IBLOCK_ID'].$delUrlID,array("action", "IBLOCK_ID", "ID")));
	?><p><a href="<?=$delUrl?>"><?=GetMessage("CATALOG_DELETE_ALL")?></a></p><?
}
?>
</div>