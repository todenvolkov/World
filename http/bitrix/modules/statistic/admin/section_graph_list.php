<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
IncludeModuleLangFile(__FILE__);

$is_dir = $_REQUEST["is_dir"] == "Y"? "Y": "N";

if (isset($set_default) && $set_default=="Y" &&
	strlen($find_hits)<=0 &&
	strlen($find_enter_points)<=0 &&
	strlen($find_exit_points)<=0)
{
	$find_hits = "Y";
	$find_enter_points = "Y";
	$find_exit_points = "Y";
}

if(isset($find_adv) && is_array($find_adv) && count($find_adv)>0)
	$find_adv_str = implode(" | ",$find_adv);
else
	$find_adv_str = "";

$arFilter = Array(
	"DATE1" => $date1,
	"DATE2" => $date2,
	"ADV" => $find_adv_str,
	"ADV_DATA_TYPE" => $adv_data_type,
	"IS_DIR" => ($is_dir=="Y"? "Y": "N"),
);
$days = 0;
$rs = CPage::GetDynamicList($section, $by, $order, $arFilter);
while($ar = $rs->Fetch())
{
	$days++;
	$SUM_COUNTER += intval($ar["COUNTER"]);
	$SUM_ENTER_COUNTER += intval($ar["ENTER_COUNTER"]);
	$SUM_EXIT_COUNTER += intval($ar["EXIT_COUNTER"]);
}

$strTitle = ($is_dir=="Y") ? GetMessage("STAT_TITLE_SECTION") : GetMessage("STAT_TITLE_PAGE");
$APPLICATION->SetTitle($strTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

if (strlen($find_adv_str)>0) :
	echo "<h2>".GetMessage("STAT_ADV_LIST")."</h2><p>";
	$rsAdv = CAdv::GetList($v1="s_dropdown",$v2="asc", Array("ID" => $str), $v3, "", $v4, $v5);
	while ($arAdv = $rsAdv->Fetch()) :
		echo "[".$arAdv["ID"]."]&nbsp;".$arAdv["REFERER1"]."&nbsp;/&nbsp;".$arAdv["REFERER2"]."<br>";
	endwhile;
	if ($find_adv_data_type!="B" && $find_adv_data_type!="S") $find_adv_data_type="P";
	$arr = array(
		"P" => GetMessage("STAT_ADV_NO_BACK"),
		"B" => GetMessage("STAT_ADV_BACK"),
		"S" => GetMessage("STAT_ADV_SUMMA")
		);
	echo "<img src=\"/bitrix/images/1.gif\" width=\"1\" height=\"5\" border=\"0\" alt=\"\"><br>(".$arr[$find_adv_data_type].")<br></p>";
endif;
$s = "";
$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");


if(isset($find_adv) && is_array($find_adv) && count($find_adv)>0)
{
	foreach($find_adv as $adv_id)
		$s .= "&adv[]=".$adv_id;
}

if (strlen($site_id)>0)
	$show_site_id = "[<a target=\"_blank\" href=\"".htmlspecialchars("/bitrix/admin/site_edit.php?LID=".urlencode($site_id)."&lang=".LANGUAGE_ID)."\">".htmlspecialchars($site_id)."</a>]&nbsp;";
else
	$show_site_id = "";
?>

<h2><?=$strTitle?></h2>
<p><?=$show_site_id?><?
	if ($public!="Y"):
		?><a target="_blank" href="<?=htmlspecialchars($section)?>" title="<?=GetMessage("STAT_GO_LINK")?>"><?=htmlspecialchars(TruncateText($section,100))?></a><?
	else:
		echo htmlspecialchars($section);
	endif;
?></p>

<?if ($days>=2):?>
<div class="graph">
<table border="0" cellspacing="0" cellpadding="0" class="graph">
	<tr>
		<td>
			<table border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td>
						<table cellpadding="5" cellspacing="0" border="0">
							<tr>
								<td><img width=<?=$width?> height=<?=$height?> src="/bitrix/admin/section_graph.php?lang=<?=LANGUAGE_ID?>&date1=<?echo urlencode($date1)?>&date2=<?echo urlencode($date2)?><?=$s?>&is_dir=<?=$is_dir?>&adv_data_type=<?echo $find_adv_data_type?>&width=<?echo intval($width)?>&height=<?echo intval($height)?>&section=<?echo urlencode($section)?>&find_hits=<?echo $find_hits?>&find_enter_points=<?echo $find_enter_points?>&find_exit_points=<?echo $find_exit_points?>"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top"><table border="0" cellspacing="1" cellpadding="2" width="0%" class="legend">
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><?=GetMessage("STAT_TOTAL")?></td>
						</tr>
						<?if ($find_enter_points=="Y"):?>
						<tr>
							<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["GREEN"]?>" width="45" height="2"></td>
							<td nowrap><?=GetMessage("STAT_ENTER_POINTS")?></td>
							<td align="right"><?=intval($SUM_ENTER_COUNTER)?></td>
						</tr>
						<?endif;?>
						<?if ($find_exit_points=="Y"):?>
						<tr>
							<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["BLUE"]?>" width="45" height="2"></td>
							<td nowrap><?=GetMessage("STAT_EXIT_POINTS")?></td>
							<td align="right"><?=intval($SUM_EXIT_COUNTER)?></td>
						</tr>
						<?endif;?>
						<?if ($find_hits=="Y"):?>
						<tr>
							<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["RED"]?>" width="45" height="2"></td>
							<td nowrap><?=GetMessage("STAT_HITS")?></td>
							<td align="right"><?=intval($SUM_COUNTER)?></td>
						</tr>
						<?endif;?>
					</table></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="section" value="<?=htmlspecialchars($section)?>">
	<input type="hidden" name="date1" value="<?=htmlspecialchars($date1)?>">
	<input type="hidden" name="date2" value="<?=htmlspecialchars($date2)?>">
	<input type="hidden" name="width" value="<?=$width?>">
	<input type="hidden" name="height" value="<?=$height?>">
	<tr>
		<td><table border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td><table cellpadding="3" cellspacing="1" border="0">
							<tr>
								<td align="center"><p><?echo InputType("checkbox","find_enter_points","Y",$find_enter_points,false); ?>&nbsp;<?=GetMessage("STAT_ENTER_POINTS"); ?></p></td>
								<td  align="center"><p><?echo InputType("checkbox","find_exit_points","Y",$find_exit_points,false); ?>&nbsp;<?=GetMessage("STAT_EXIT_POINTS"); ?></p></td>
								<td  align="center"><p><?echo InputType("checkbox","find_hits","Y",$find_hits,false);?>&nbsp;<?=GetMessage("STAT_HITS")?></p></td>
							</tr>
							<tr>
								<td  colspan="2" valign="center" width="0%" nowrap><img src="/bitrix/images/1.gif" width="1" height="3"><br><input type="submit" name="set_filter" value="<?echo GetMessage("STAT_CREATE_GRAPH")?>"><input type="hidden" name="set_filter" value="Y"><br><img src="/bitrix/images/1.gif" width="1" height="3"></td>

								<td><input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>"></td>

						</table></td>
				</tr>
			</table></td>
	</tr>
	</form>
</table>
<?
else:
	CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));
?>
<form><input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>"></form>
<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php")?>