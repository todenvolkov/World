<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult))
	return;


if (strpos($_SERVER["REQUEST_URI"],'/personal/')!==false){
	for ($i=0;$i<sizeof($arResult);$i++){
		if (strpos($arResult[$i]["LINK"],'/personal/')!==false){
			$arResult[$i]["SELECTED"] = 1;
		}else{
			$arResult[$i]["SELECTED"] = 0;
		}
	}
}


$lastSelectedItem = null;
$lastSelectedIndex = -1;

$qq = array();
foreach($arResult as $itemIdex => $arItem)
{
	$qq[] = '<td height="37" align="center" nowrap class="Menu">'.(($arItem["SELECTED"]==1)?'<strong class="active">'.$arItem["TEXT"].'</strong>':'<a href="'.$arItem["LINK"].'"><strong>'.$arItem["TEXT"].'</strong></a>').'</td>';
}
echo $ROOT_MENU_TYPE;
	
?>
<table width="90%" border="0" cellspacing="0" cellpadding="0" bordercolor="#FFCCCC">
	<tr> 
		<?=implode('<td align="center" nowrap class="Menu">|</td>',$qq);?>
	</tr>
</table>
