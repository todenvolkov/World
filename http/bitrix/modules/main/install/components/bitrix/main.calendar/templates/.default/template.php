<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arParams['SILENT'] == 'Y') return;

$cnt = strlen($arParams['INPUT_NAME_FINISH']) > 0 ? 2 : 1;

for ($i = 0; $i < $cnt; $i++):
	if ($arParams['SHOW_INPUT'] == 'Y'):
?><input type="text" id="<?=$arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]?>" name="<?=$arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]?>" value="<?=$arParams['INPUT_VALUE'.($i == 1 ? '_FINISH' : '')]?>" <?=(Array_Key_Exists("~INPUT_ADDITIONAL_ATTR", $arParams)) ? $arParams["~INPUT_ADDITIONAL_ATTR"] : ""?>/><?
	endif;
?><a href="javascript:void(0);" title="<?=GetMessage('calend_title')?>"><img src="<?=$templateFolder?>/images/icon.gif" alt="<?=GetMessage('calend_title')?>" class="calendar-icon" onclick="jsCalendar.Show(this, '<?=$arParams['INPUT_NAME'.($i == 1 ? '_FINISH' : '')]?>', '<?=$arParams['INPUT_NAME']?>', '<?=$arParams['INPUT_NAME_FINISH']?>', <?=$arParams['SHOW_TIME'] == 'Y' ? 'true' : 'false'?>, '<?=(time()+date("Z"))?>','<?if ($arParams['FORM_NAME'] != ''){echo $arParams['FORM_NAME'];}?>', <?=$arParams['HIDE_TIMEBAR'] == 'Y' ? 'true' : 'false'?>);" onmouseover="this.className+=' calendar-icon-hover';" onmouseout="this.className = this.className.replace(/\s*calendar-icon-hover/ig, '');" border="0"/></a><?if ($cnt == 2 && $i == 0):?><span class="date-interval-hellip">&hellip;</span><?endif;?><?
endfor;
?>