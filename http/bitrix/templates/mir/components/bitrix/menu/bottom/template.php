<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult))
	return;


$qq = array();
foreach($arResult as $itemIdex => $arItem)
{
	$qq[] = '<tr><td class="footerText">'.(($arItem["SELECTED"]==1)?'<i>'.$arItem["TEXT"].'</i>':'<a href="'.$arItem["LINK"].'" class="f">'.$arItem["TEXT"].'</a>').'</td></tr>';
}

$maxq = (sizeof($qq)>5) ? array(0,5,5,sizeof($qq)) : array(0,sizeof($qq),0,0);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">
			<table width="50%" border="0" cellpadding="0" cellspacing="0"><?=implode("",array_slice($qq,$maxq[0],$maxq[1]));?></table>
		</td>
		<td valign="top">
			<table width="50%" border="0" cellpadding="0" cellspacing="0"><?=implode("",array_slice($qq,$maxq[2],$maxq[3]));?></table>
		</td>
	</tr>
</table>
