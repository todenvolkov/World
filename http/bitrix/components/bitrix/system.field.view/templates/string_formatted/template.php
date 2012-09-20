<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
if ($arParams["arUserField"]["MULTIPLE"] == "Y")
{
	for($i = 0, $l = count($arParams['arUserField']["VALUE"]); $i < $l; $i++)
	{
		$val = $arParams["arUserField"]["VALUE"][$i];
		$name = str_replace("[]", "[".$i."]", $arParams["arUserField"]["FIELD_NAME"]);
		if ($val != "")
		{
			echo str_replace(
				array("#VALUE#"),
				array(CUserTypeStringFormatted::GetPublicViewHTML(
					array(
						"SETTINGS" => $arParams["arUserField"]["SETTINGS"]
					),
					array(
						"NAME" => $name,
						"VALUE" => $val
					)
				)
				),
				$arParams["arUserField"]["SETTINGS"]["PATTERN"]
			);
			echo "\n<br />\n";
		}
	}
}
else
{
	echo str_replace(
		array("#VALUE#"),
		array(CUserTypeStringFormatted::GetPublicViewHTML(
			array(
				"SETTINGS" => $arParams["arUserField"]["SETTINGS"]
			),
			array(
				"NAME" => $arParams["arUserField"]["FIELD_NAME"],
				"VALUE" => $arParams["arUserField"]["VALUE"]
			)
		)
		),
		$arParams["arUserField"]["SETTINGS"]["PATTERN"]
	);
}
?>