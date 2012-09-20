<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ('Y' == $arParams['MULTIPLE'])
{
	?><input class="mes-button" type="button" value="<?php echo ('' != $arParams['BUTTON_CAPTION'] ? $arParams['BUTTON_CAPTION'] : '...'); ?>" title="<?php echo ('' != $arParams['BUTTON_TITLE'] ? $arParams['BUTTON_TITLE'] : ''); ?>" onClick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<?php echo $arParams['LANG']; ?>&amp;IBLOCK_ID=<?php echo $arParams["IBLOCK_ID"]; ?>&amp;n=&amp;k=&amp;m=y&amp;lookup=<?php echo $arParams['ONSELECT']; ?>', 600, 500);"><?php
}
else
{
	?><input class="mes-button" type="button" value="<?php echo ('' != $arParams['BUTTON_CAPTION'] ? $arParams['BUTTON_CAPTION'] : '...'); ?>" title="<?php echo ('' != $arParams['BUTTON_TITLE'] ? $arParams['BUTTON_TITLE'] : ''); ?>" onClick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<?php echo $arParams['LANG']; ?>&amp;IBLOCK_ID=<?php echo $arParams["IBLOCK_ID"]; ?>&amp;n=&amp;k=&amp;lookup=<?php echo $arParams['ONSELECT']; ?>', 600, 500);"><?php
}