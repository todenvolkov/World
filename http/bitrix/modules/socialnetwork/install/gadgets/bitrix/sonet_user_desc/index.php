<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

?><h4 class="bx-sonet-user-desc-username"><?=htmlspecialcharsback($arGadgetParams['USER_NAME'])?></h4><?
			
if ($arGadgetParams['CAN_VIEW_PROFILE']):

	?><table width="100%" cellspacing="2" cellpadding="2"><?
	if ($arGadgetParams['FIELDS_MAIN_SHOW'] == "Y"):
		foreach ($arGadgetParams['FIELDS_MAIN_DATA'] as $fieldName => $arUserField):
			if (StrLen($arUserField["VALUE"]) > 0):
				?><tr>
					<td width="50%"><?= $arUserField["NAME"] ?>:</td>
					<td width="50%"><?if (StrLen($arUserField["SEARCH"]) > 0):?><a href="<?= $arUserField["SEARCH"] ?>"><?endif;?><?= $arUserField["VALUE"] ?><?if (StrLen($arUserField["SEARCH"]) > 0):?></a><?endif;?></td>
				</tr><?
			endif;
		endforeach;
	endif;

	if ($arGadgetParams['PROPERTIES_MAIN_SHOW'] == "Y"):
		foreach ($arGadgetParams['PROPERTIES_MAIN_DATA'] as $fieldName => $arUserField):
			if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
				?><tr>
					<td width="50%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
					<td width="50%"><?
					$bInChain = ($fieldName == "UF_DEPARTMENT" ? "Y" : "N");
					$GLOBALS["APPLICATION"]->IncludeComponent(
						"bitrix:system.field.view", 
						$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
						array("arUserField" => $arUserField, "inChain" => $bInChain),
						null,
						array("HIDE_ICONS"=>"Y")
					);
					?></td>
				</tr><?
			endif;
		endforeach;
	endif;

	
	
	if (
		array_key_exists("RATING_MULTIPLE", $arGadgetParams)
		&& is_array($arGadgetParams["RATING_MULTIPLE"])
		&& count($arGadgetParams["RATING_MULTIPLE"]) > 0
	):
		foreach($arGadgetParams["RATING_MULTIPLE"] as $arRating):
			?>
			<tr>
				<td width="50%"><?=$arRating["NAME"]?>:</td>
				<td width="50%"><?=$arRating["VALUE"]?></td>
			</tr>
			<?
		endforeach;
		?>
		<tr>
			<td width="50%"><?=GetMessage("GD_SONET_USER_DESC_VOTE")?>:</td>
			<td width="50%">
				<?$APPLICATION->IncludeComponent("bitrix:rating.vote","",
					array(
						"ENTITY_TYPE_ID" => "USER",
						"ENTITY_ID" => $arParams["USER_ID"],
						"OWNER_ID" => $arParams["USER_ID"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);?>
			</td>
		</tr>
		<?
	elseif (strlen($arGadgetParams['RATING_NAME']) > 0):
		?>
		<tr>
			<td width="50%"><?=$arGadgetParams['RATING_NAME']?>:</td>
			<td width="50%"><?=$arGadgetParams['RATING_VALUE']?></td>
		</tr>
		<tr>
			<td width="50%"><?=GetMessage("GD_SONET_USER_DESC_VOTE")?>:</td>
			<td width="50%">
				<?$APPLICATION->IncludeComponent("bitrix:rating.vote","",
					array(
						"ENTITY_TYPE_ID" => "USER",
						"ENTITY_ID" => $arParams["USER_ID"],
						"OWNER_ID" => $arParams["USER_ID"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);?>
			</td>
		</tr>
		<?
	endif;
	
	?></table>

	<h4 class="bx-sonet-user-desc-contact"><?= GetMessage("GD_SONET_USER_DESC_CONTACT_TITLE") ?></h4>
	<table width="100%" cellspacing="2" cellpadding="2"><?
	if ($arGadgetParams['CAN_VIEW_CONTACTS']):
		$bContactsEmpty = true;
		if ($arGadgetParams['FIELDS_CONTACT_SHOW'] == "Y"):
			foreach ($arGadgetParams['FIELDS_CONTACT_DATA'] as $fieldName => $arUserField):
				if (StrLen($arUserField["VALUE"]) > 0):
					?><tr>
						<td width="50%"><?= $arUserField["NAME"] ?>:</td>
						<td width="50%"><?if (StrLen($arUserField["SEARCH"]) > 0):?><a href="<?= $arUserField["SEARCH"] ?>"><?endif;?><?= $arUserField["VALUE"] ?><?if (StrLen($arUserField["SEARCH"]) > 0):?></a><?endif;?></td>
					</tr><?
					$bContactsEmpty = false;
				endif;
			endforeach;
		endif;

		if ($arGadgetParams['PROPERTIES_CONTACT_SHOW'] == "Y"):
			foreach ($arGadgetParams['PROPERTIES_CONTACT_DATA'] as $fieldName => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
					?><tr>
						<td width="50%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
						<td width="50%"><?
						$bInChain = ($fieldName == "UF_DEPARTMENT" ? "Y" : "N");
						$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:system.field.view", 
							$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
							array("arUserField" => $arUserField, "inChain" => $bInChain),
							null,
							array("HIDE_ICONS"=>"Y")
						);
						?></td>
					</tr><?
					$bContactsEmpty = false;
				endif;
			endforeach;
		endif;
		if ($bContactsEmpty):
			?><tr>
				<td colspan="2"><?= GetMessage("GD_SONET_USER_DESC_CONTACT_UNSET") ?></td>
			</tr><?
		endif;

	else:
		?><tr>
			<td colspan="2"><?= GetMessage("GD_SONET_USER_DESC_CONTACT_UNAVAIL") ?></td>
		</tr><?
	endif;
	?></table><?
	
	if ($arGadgetParams['FIELDS_PERSONAL_SHOW'] == "Y" || $arGadgetParams['PROPERTIES_PERSONAL_SHOW'] == "Y"):
		?><h4 class="bx-sonet-user-desc-personal"><?= GetMessage("GD_SONET_USER_DESC_PERSONAL_TITLE") ?></h4>
		<table width="100%" cellspacing="2" cellpadding="2"><?
		$bNoPersonalInfo = true;
		if ($arGadgetParams['FIELDS_PERSONAL_SHOW'] == "Y"):
			foreach ($arGadgetParams['FIELDS_PERSONAL_DATA'] as $fieldName => $arUserField):
				if (StrLen($arUserField["VALUE"]) > 0):
					?><tr>
						<td width="50%"><?= $arUserField["NAME"] ?>:</td>
						<td width="50%"><?if (StrLen($arUserField["SEARCH"]) > 0):?><a href="<?= $arUserField["SEARCH"] ?>"><?endif;?><?= $arUserField["VALUE"] ?><?if (StrLen($arUserField["SEARCH"]) > 0):?></a><?endif;?></td>
					</tr><?
					$bNoPersonalInfo = false;
				endif;
			endforeach;
		endif;
		if ($arGadgetParams['PROPERTIES_PERSONAL_SHOW'] == "Y"):
			foreach ($arGadgetParams['PROPERTIES_PERSONAL_DATA'] as $fieldName => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
					?><tr>
						<td width="50%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
						<td width="50%">
						<?
						$bInChain = ($fieldName == "UF_DEPARTMENT" ? "Y" : "N");
						$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:system.field.view", 
							$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
							array("arUserField" => $arUserField, "inChain" => $bInChain),
							null,
							array("HIDE_ICONS"=>"Y")
						);
						?>
						</td>
					</tr><?
					$bNoPersonalInfo = false;
				endif;
			endforeach;
		endif;
		if ($bNoPersonalInfo):
			?><tr>
				<td colspan="2"><?= GetMessage("GD_SONET_USER_DESC_PERSONAL_UNAVAIL") ?></td>
			</tr><?
		endif;
		?></table><?
	endif;
endif;	
?>