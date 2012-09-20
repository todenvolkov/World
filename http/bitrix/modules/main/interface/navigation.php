<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if($this->bPostNavigation)
	$nav_func_name = 'PostAdminList';
else
	$nav_func_name = 'GetAdminList';

$sQueryString = CUtil::JSUrlEscape($strNavQueryString);
?>
<div class="navigation">
<table cellpadding="0" cellspacing="0" border="0" class="navigation">
	<tr>
<?if($this->NavRecordCount>0):?>
<?if($this->NavPageNomer > 1):?>
		<td><a href="javascript:<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath.'?PAGEN_'.$this->NavNum.'=1'.'&amp;SIZEN_'.$this->NavNum.'='.$this->NavPageSize.$sQueryString;?>');"><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/first.gif" class="navfirst" alt="<?echo GetMessage("navigation_first")?>" title="<?echo GetMessage("navigation_first")?>" border="0"></a></td>
		<td><a href="javascript:<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer-1).'&amp;SIZEN_'.$this->NavNum.'='.$this->NavPageSize.$sQueryString;?>');"><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/prev.gif" class="navprev" alt="<?echo GetMessage("navigation_prev")?>" title="<?echo GetMessage("navigation_prev")?>" border="0"></a></td>
<?else:?>
		<td><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/first_dis.gif" class="navfirst" alt="" border="0"></td>
		<td><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/prev_dis.gif" class="navprev" alt="" border="0"></td>
<?endif;?>
		<td><?
$NavRecordGroup = $this->nStartPage;
while($NavRecordGroup <= $this->nEndPage):
	if($NavRecordGroup == $this->NavPageNomer)
		echo '<span class="current">&nbsp;'.$NavRecordGroup.'&nbsp;</span>';
	else
		echo('&nbsp;<a href="javascript:'.$this->table_id.'.'.$nav_func_name.'(\''.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.$NavRecordGroup.'&amp;SIZEN_'.$this->NavNum.'='.$this->NavPageSize.$sQueryString.'\');">'.$NavRecordGroup.'</a>&nbsp;');
	$NavRecordGroup++;
endwhile;
?></td>
<?if($this->NavPageNomer < $this->NavPageCount):?>
		<td><a href="javascript:<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer+1).'&amp;SIZEN_'.$this->NavNum.'='.$this->NavPageSize.$sQueryString;?>');"><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/next.gif" class="navnext" alt="<?echo GetMessage("navigation_next")?>" title="<?echo GetMessage("navigation_next")?>" border="0"></a></td>
		<td><a href="javascript:<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath.'?PAGEN_'.$this->NavNum.'='.$this->NavPageCount.'&amp;SIZEN_'.$this->NavNum.'='.$this->NavPageSize.$sQueryString;?>');"><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/last.gif" class="navlast" alt="<?echo GetMessage("navigation_last")?>" title="<?echo GetMessage("navigation_last")?>" border="0"></a></td>
<?else:?>
		<td><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/next_dis.gif" class="navnext" alt="" border="0"></td>
		<td><img src="/bitrix/themes/<?echo ADMIN_THEME_ID?>/images/nav/last_dis.gif" class="navlast" alt="" border="0"></td>
<?endif;?>
		<td>&nbsp;|&nbsp;</td>
<?
endif; //$this->NavRecordCount>0
?>
		<td><?echo GetMessage("navigation_records")?></td>
		<td>
		<select name="" onchange="
			var val = this[selectedIndex].value;
			if(val == '0')
				<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath."?PAGEN_".$this->NavNum."=1&amp;SHOWALL_".$this->NavNum."=1".CUtil::addslashes($strNavQueryString);?>');
			else
				<?echo $this->table_id?>.<?=$nav_func_name?>('<?echo $sUrlPath."?PAGEN_".$this->NavNum."=1&amp;SHOWALL_".$this->NavNum."=0"."&amp;SIZEN_".$this->NavNum."="?>'+val+'<?echo CUtil::addslashes($strNavQueryString);?>');
		">
<?
$aSizes = array(10, 20, 50, 100, 200, 500);
if($this->nInitialSize > 0 && !in_array($this->nInitialSize, $aSizes))
	array_unshift($aSizes, $this->nInitialSize);
$reqSize = intval($_REQUEST["SIZEN_".$this->NavNum]);
if($reqSize > 0 && !in_array($reqSize, $aSizes))
	array_unshift($aSizes, $reqSize);
foreach($aSizes as $size):
?>
			<option value="<?echo $size?>"<?if($this->NavPageSize == $size)echo " selected"?>><?echo $size?></option>
<?
endforeach;
if($this->bShowAll):
?>
			<option value="0"<?if($this->NavShowAll) echo " selected"?>><?echo GetMessage("navigation_records_all")?></option>
<?endif;?>
		</select></td>
<?if($this->NavRecordCount>0):?>
			<td class="navtext">
<?
echo $title." ".(($this->NavPageNomer-1)*$this->NavPageSize+1)." &ndash; ";
if($this->NavPageNomer <> $this->NavPageCount)
	echo($this->NavPageNomer * $this->NavPageSize);
else
	echo($this->NavRecordCount);
echo " ".GetMessage("navigation_records_of")." ".$this->NavRecordCount;
?></td>
<?endif?>
</tr>
</table>
</div>
