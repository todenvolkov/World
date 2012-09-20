<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ('Y' == $arParams['SEPARATE_BUTTON'])
{
	?><div class="mea-cont"><?php
}
?><input class="mes-button" type="button" value="<?php echo ('' != $arParams['BUTTON_CAPTION'] ? $arParams['BUTTON_CAPTION'] : '...'); ?>" title="<?php echo ('' != $arParams['BUTTON_TITLE'] ? $arParams['BUTTON_TITLE'] : ''); ?>" onClick="<?php
	if ('Y' == $arParams['HIDDEN_WINDOW'])
	{	?><?php echo $arParams['CONTENT_URL']; ?><?php }
	else
	{  ?>jsUtils.OpenWindow('<?php echo $arParams['CONTENT_URL']; ?>', 800, 500);<?php }
?>"><?php	
if ('Y' == $arParams['SEPARATE_BUTTON'])
{
	?></div><?php
}