<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeAJAX();
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/popup/script.js"></script>', true);
$iIndex = rand();
?>
<script>
if (phpVars == null || typeof(phpVars) != "object")
{
	var phpVars = {
		'ADMIN_THEME_ID': '.default',
		'titlePrefix': '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '};
}
</script>
<form name="forum_form" id="forum_form_<?=$arResult["id"]?>" action="<?=$APPLICATION->GetCurPageParam()?>" method="get" class="forum-form">
	<?=bitrix_sessid_post()?>
<?
	foreach ($arResult["FIELDS"] as $key => $res):
		if ($res["TYPE"] == "HIDDEN"):
?>
	<input type="hidden" name="<?=$res["NAME"]?>" value="<?=$res["VALUE"]?>" />
<?
			unset($arResult["FIELDS"][$key]);
		endif;
	endforeach;

	$counter = 0;
	foreach ($arResult["FIELDS"] as $key => $res):
		$res["ID"] = (empty($res["ID"]) ? $res["NAME"]."_".$iIndex : $res["ID"]);
?>
	<div class="forum-filter-field <?=$res["CLASS"]?>">
		<label for="<?=$res["ID"]?>"><?=$res["TITLE"]?>:</label>
		<span>
<?
		
		if ($res["TYPE"] == "SELECT"):
			if (!empty($_REQUEST["del_filter"]))
				$res["ACTIVE"] = "";
?>
			<select name="<?=$res["NAME"]?>" class="<?=$res["CLASS"]?>" id="<?=$res["ID"]?>" <?=($res["MULTIPLE"] == "Y" ? "multiple='multiple' size='5'" : "")?>>
<?
			foreach ($res["VALUE"] as $key => $val) 
			{
				if ($val["TYPE"] == "OPTGROUP"):
?>
				<optgroup label="<?=str_replace(array(" ", "&amp;nbsp;") , "&nbsp;", $val["NAME"])?>" class="<?=$val["CLASS"]?>"></optgroup>
<?
				else:
?>
				<option value="<?=$key?>" <?=($res["ACTIVE"] == $key ? " selected='selected'" : "")?>><?=str_replace(
					array(" ", "&amp;nbsp;"), "&nbsp;", $val["NAME"])?></option>
<?
				endif;
			}
?>
			</select>
<?
		elseif ($res["TYPE"] == "PERIOD"):
			if (!empty($_REQUEST["del_filter"]))
			{
				$res["VALUE"] = "";
				$res["VALUE_TO"] = "";
			}
			?><?$APPLICATION->IncludeComponent("bitrix:main.calendar", "",
				array(
					"SHOW_INPUT" => "Y",
					"INPUT_NAME" => $res["NAME"], 
					"INPUT_NAME_FINISH" => $res["NAME_TO"],
					"INPUT_VALUE" => $res["VALUE"], 
					"INPUT_VALUE_FINISH" => $res["VALUE_TO"],
					"FORM_NAME" => "forum_form"),
				$component,
				array(
					"HIDE_ICONS" => "Y"));?><?
		elseif ($res["TYPE"] == "CHECKBOX"):
?>
			<input type="checkbox" name="<?=$res["NAME"]?>" id="<?=$res["ID"]?>" value="<?=$res["VALUE"]?>" class="<?=$res["CLASS"]?>" <?
				?><?=($res["ACTIVE"] == $res["VALUE"] ? " checked='checked' " : "")?> />
			<label for="<?=$res["ID"]?>"><?=$res["LABEL"]?></label>
<?
		else:
			if (!empty($_REQUEST["del_filter"]))
			{
				$res["VALUE"] = "";
			}
?>
			<input type="text" name="<?=$res["NAME"]?>" id="<?=$res["ID"]?>" value="<?=$res["VALUE"]?>" class="<?=$res["CLASS"]?>" />
<?
		endif;
?>
		</span>
		<div class="forum-clear-float"></div>
	</div>
<?
	endforeach;
?>
	<div class="forum-filter-field forum-filter-footer">
<?
	if (empty($arResult["BUTTONS"])):
?>
		<span class="forum-filter-first"><input type="submit" name="set_filter" value="<?=GetMessage("FORUM_BUTTON_FILTER")?>" /></span>
		<span><input type="submit" name="del_filter" value="<?=GetMessage("FORUM_BUTTON_RESET")?>" /></span>
<?
	else:
		$counter = 0; 
		foreach ($arResult["BUTTONS"] as $res):
		
?>
		<span class="<?=($counter == 0 ? "forum-filter-first" : "")?>">
			<input type="submit" name="<?=$res["NAME"]?>" value="<?=$res["VALUE"]?>" />
		</span>
<?
		endforeach;
	endif;
?>
		<div class="forum-clear-float"></div>
	</div>
</form><?