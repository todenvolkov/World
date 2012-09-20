<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arToolbar = array();
if($arResult["IBLOCK_ID"])
{
	$arToolbar[] = array(
		"TEXT"=>GetMessage("CT_BLLE_TOOLBAR_FIELDS"),
		"TITLE"=>GetMessage("CT_BLLE_TOOLBAR_FIELDS_TITLE"),
		"LINK"=>$arResult["LIST_FIELDS_URL"],
		"ICON"=>"btn-view-fields",
	);
	$arToolbar[] = array(
		"TEXT"=>GetMessage("CT_BLLE_TOOLBAR_DELETE"),
		"TITLE"=>GetMessage("CT_BLLE_TOOLBAR_DELETE_TITLE"),
		"LINK"=>"javascript:jsDelete('".CUtil::JSEscape("form_".$arResult["FORM_ID"])."', '".GetMessage("CT_BLLE_TOOLBAR_DELETE_WARNING")."')",
		"ICON"=>"btn-delete-list",
	);
	$arToolbar[] = array(
		"SEPARATOR"=>"Y",
	);
	$arToolbar[] = array(
		"TEXT"=>$arResult["IBLOCK"]["ELEMENTS_NAME"],
		"TITLE"=>GetMessage("CT_BLLE_TOOLBAR_LIST_TITLE"),
		"LINK"=>$arResult["LIST_URL"],
		"ICON"=>"btn-view-elements",
	);
}

if(count($arToolbar))
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS"=>$arToolbar,
		),
		$component, array("HIDE_ICONS" => "Y")
	);
}

if($arParams["SOCNET_GROUP_ID"])
{
	$rights_fields = array();
	foreach($arResult["IBLOCK_GROUPS"] as $role => $perm)
	{
		$rights_fields[] = array(
			"id"=>"GROUPS[".$role."]",
			"name"=>$arResult["GROUPS"][$role],
			"type"=>"list",
			"value"=>$perm,
			"items"=>$arResult["RIGHTS"],
		);
	}
}
else
{
	$i = 0;
	$rights_html = '';
	foreach($arResult["IBLOCK_GROUPS"] as $group_id => $perm)
	{
		$rights_html .= '<tr><td>';

		$rights_html .= '<select name="GROUPS[n'.$i.'][GROUP]" size="1">';
		$rights_html .= '<option value="">'.GetMessage("CT_BLLE_NO_VALUE").'</option>';
		foreach($arResult["GROUPS"] as $id => $name)
			$rights_html .= '<option value="'.$id.'" '.($id==$group_id? 'selected': '').'>'.$name.'</option>';
		$rights_html .= '</select>';

		$rights_html .= '<select name="GROUPS[n'.$i.'][PERM]" size="1">';
		$rights_html .= '<option value="">'.GetMessage("CT_BLLE_NO_VALUE").'</option>';
		foreach($arResult["RIGHTS"] as $id => $name)
			$rights_html .= '<option value="'.$id.'" '.($id==$perm? 'selected': '').'>'.$name.'</option>';
		$rights_html .= '</select>';

		$rights_html .= '</td></tr>';

		$i++;
	}


	$rights_html .= '<tr><td>';

	$rights_html .= '<select name="GROUPS[n'.$i.'][GROUP]" size="1">';
	$rights_html .= '<option value="" selected>'.GetMessage("CT_BLLE_NO_VALUE").'</option>';
	foreach($arResult["GROUPS"] as $id => $name)
		$rights_html .= '<option value="'.$id.'">'.$name.'</option>';
	$rights_html .= '</select>';

	$rights_html .= '<select name="GROUPS[n'.$i.'][PERM]" size="1">';
	foreach($arResult["RIGHTS"] as $id => $name)
		$rights_html .= '<option value="'.$id.'">'.$name.'</option>';
	$rights_html .= '</select>';

	$rights_html .= '</td></tr>';


	$rights_html .= '<tr><td>';
	$rights_html .= '<input type="button" onclick="addNewTableRow(\'tab3_edit_table\', 1, /GROUPS\[(n)([0-9]*)\]/g, 2)" value="'.GetMessage("CT_BLLE_ADD_BUTTON").'">';
	$rights_html .= '</td></tr>';

	$rights_fields = array(
		array(
			"id"=>"RIGHTS",
			"name"=>GetMessage("CT_BLLE_ACCESS_RIGHTS"),
			"type"=>"custom",
			"colspan"=>true,
			"value"=>$rights_html,
		),
	);
}
$custom_html = '<input type="hidden" name="action" id="action" value="">';

$arTab1 = array(
	"id" => "tab1",
	"name" => GetMessage("CT_BLLE_TAB_EDIT"),
	"title" => GetMessage("CT_BLLE_TAB_EDIT_TITLE"),
	"icon" => "",
	"fields" => array(
		array("id"=>"NAME", "name"=>GetMessage("CT_BLLE_FIELD_NAME"), "required"=>true),
		array("id"=>"SORT", "name"=>GetMessage("CT_BLLE_FIELD_SORT"), "params"=>array("size"=>5)),
		array("id"=>"PICTURE", "name"=>GetMessage("CT_BLLE_FIELD_PICTURE"), "type"=>"file"),
	),
);
if(isset($arResult["FORM_DATA"]["BIZPROC"]))
	$arTab1["fields"][] = array(
		"id" => "BIZPROC",
		"name" => GetMessage("CT_BLLE_FIELD_BIZPROC"),
		"type"=>"checkbox",
	);

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.form",
	"",
	array(
		"FORM_ID"=>$arResult["FORM_ID"],
		"TABS"=>array(
			$arTab1,
			array("id"=>"tab2", "name"=>GetMessage("CT_BLLE_TAB_MESSAGES"), "title"=>GetMessage("CT_BLLE_TAB_MESSAGES_TITLE"), "icon"=>"", "fields"=>array(
				array("id"=>"ELEMENTS_NAME", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENTS_NAME")),
				array("id"=>"ELEMENT_NAME", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_NAME")),
				array("id"=>"ELEMENT_ADD", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_ADD")),
				array("id"=>"ELEMENT_EDIT", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_EDIT")),
				array("id"=>"ELEMENT_DELETE", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_DELETE")),
				array("id"=>"SECTIONS_NAME", "name"=>GetMessage("CT_BLLE_FIELD_SECTIONS_NAME")),
				array("id"=>"SECTION_NAME", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_NAME")),
				array("id"=>"SECTION_ADD", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_ADD")),
				array("id"=>"SECTION_EDIT", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_EDIT")),
				array("id"=>"SECTION_DELETE", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_DELETE")),
			)),
			array(
				"id"=>"tab3",
				"name"=>GetMessage("CT_BLLE_TAB_ACCESS"),
				"title"=>GetMessage("CT_BLLE_TAB_ACCESS_TITLE"),
				"icon"=>"",
				"fields"=>$rights_fields,
			),
		),
		"BUTTONS"=>array("back_url"=>$arResult["~LISTS_URL"], "custom_html"=>$custom_html),
		"DATA"=>$arResult["FORM_DATA"],
		"SHOW_SETTINGS"=>"N",
		"THEME_GRID_ID"=>$arResult["GRID_ID"],
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>