<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ((defined('BX_PUBLIC_MODE')) && (1 == BX_PUBLIC_MODE))
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->GetFolder().'/style.css'; ?>">
<script type="text/javascript" src="/bitrix/js/main/ajax.js"></script>
<script type="text/javascript" src="/bitrix/js/main/utils.js"></script>
<script type="text/javascript" src="/bitrix/js/main/public_tools.js"></script>
<script type="text/javascript" src="<?php echo $this->__component->GetPath().'/script.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $this->GetFolder().'/script2.js'; ?>"></script>
<?php
}
else
{
	?><script type="text/javascript" src="<?php echo $this->GetFolder().'/script2.js'; ?>"></script><?
}

$control_id = $arParams['CONTROL_ID'];
$textarea_id = $arParams['INPUT_NAME_STRING'] ? $arParams['INPUT_NAME_STRING'] : 'visual_'.$control_id;

$arParams['MAX_HEIGHT'] = intval($arParams['MAX_HEIGHT']);
if (0 >= $arParams['MAX_HEIGHT']) $arParams['MAX_HEIGHT'] = 1000;
$arParams['MIN_HEIGHT'] = intval($arParams['MIN_HEIGHT']);
if (0 >= $arParams['MIN_HEIGHT']) $arParams['MIN_HEIGHT'] = 30;
$arParams['MAX_WIDTH'] = intval($arParams['MAX_WIDTH']);
if (0 > $arParams['MAX_WIDTH']) $arParams['MAX_WIDTH'] = 0;
if ((defined('BX_PUBLIC_MODE')) && (1 == BX_PUBLIC_MODE)) $arParams['MAX_WIDTH'] = 500;

$INPUT_VALUE = array();
if(isset($arParams['INPUT_VALUE_STRING']) && strlen($arParams['INPUT_VALUE_STRING']))
{
	$arTokens = preg_split('/(?<=])[\n;,]+/', $arParams['~INPUT_VALUE_STRING']);
	foreach($arTokens as $key => $token)
	{
		if(preg_match("/^(.*) \\[(\\d+)\\]/", $token, $match))
		{
			if (0 < intval($match[2]))
				$INPUT_VALUE[] = array(
					"ID" => intval($match[2]),
					"NAME" => $match[1],
				);
		}
	}
}
?>
<div class="mli-layout" id="layout_<?=$control_id?>">
	<input type="hidden" name="<?echo $arParams['~INPUT_NAME']; ?>" value="">
	<?if($arParams["MULTIPLE"]=="Y"):?>
	<textarea name="<?=$textarea_id?>" id="<?=$textarea_id?>" class="mli-field"><?if (isset($arParams['INPUT_VALUE_STRING'])) echo htmlspecialchars($arParams['INPUT_VALUE_STRING']);?></textarea>
	<?else:?>
	<input autocomplete="off" type="text" name="<?=$textarea_id?>" id="<?=$textarea_id?>" value="<?if (isset($arParams['INPUT_VALUE_STRING'])) echo htmlspecialchars($arParams['INPUT_VALUE_STRING']);?>" class="mli-field">
	<?endif?>
</div>
<?php
$arAjaxParams = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
);
if ('' != $arParams['BAN_SYM'])
{
	$arAjaxParams['BAN_SYM'] = $arParams['BAN_SYM'];
	$arAjaxParams['REP_SYM'] = $arParams['REP_SYM'];
}
?>
<script type="text/javascript">
var jsMLI_<?=$control_id?> = new JCMainLookupAdminSelector({
	'AJAX_PAGE' : '<?echo CUtil::JSEscape($this->GetFolder()."/ajax.php")?>',
	'AJAX_PARAMS' : <?echo CUtil::PhpToJsObject($arAjaxParams)?>,
	'CONTROL_ID': '<?echo CUtil::JSEscape($control_id)?>',
	'LAYOUT_ID': 'layout_<?echo CUtil::JSEscape($control_id)?>',
	'INPUT_NAME': '<?echo CUtil::JSEscape($arParams['~INPUT_NAME'])?>',
	<?if($arParams['INPUT_NAME_SUSPICIOUS']):?>
		'INPUT_NAME_SUSPICIOUS': '<?echo CUtil::JSEscape($arParams['INPUT_NAME_SUSPICIOUS'])?>',
	<?endif;?>
	'VALUE': <?echo CUtil::PhpToJsObject($INPUT_VALUE)?>,
	'VISUAL': {
		'ID': '<?=$textarea_id?>',
		'MAX_HEIGHT': <?php echo $arParams['MAX_HEIGHT']; ?>,
		'MIN_HEIGHT': <?php echo $arParams['MIN_HEIGHT']; ?>,
		<?php
		if (0 < $arParams['MAX_WIDTH'])
		{
		?>
		'MAX_WIDTH': <?php echo $arParams['MAX_WIDTH']?>,
		<?php
		}
		?>
		'START_TEXT': '<?echo CUtil::JSEscape($arParams['START_TEXT'])?>'
	}
});
<?php if ((defined('BX_PUBLIC_MODE')) && (1 == BX_PUBLIC_MODE))
{ ?>
jsMLI_<?=$control_id?>.Init();
<?php
}
?><?php 
if ('Y' == $arParams['RESET'])
{
?>jsMLI_<?=$control_id?>.Reset(true,false);<?php 
}
?>
</script>