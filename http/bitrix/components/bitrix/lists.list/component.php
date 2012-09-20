<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));
	return;
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	intval($arParams["~IBLOCK_ID"]),
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLL_UNKNOWN_ERROR"));
		return;
	}
}
elseif($lists_perm < CListPermissions::CAN_READ)
{
	ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = $lists_perm >= CListPermissions::IS_ADMIN;
$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
$arResult["IBLOCK_ID"] = $arIBlock["ID"];

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_list_elements_".$arResult["IBLOCK_ID"];

if(isset($_GET["list_section_id"]))
	$section_id = intval($_GET["list_section_id"]);
else
	$section_id = intval($arParams["~SECTION_ID"]);

$arResult["ANY_SECTION"] = isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0;
$arResult["SECTION"] = false;
$arResult["SECTION_ID"] = false;
$arResult["PARENT_SECTION_ID"] = false;
$arResult["SECTIONS"] = array();
$arResult["LIST_SECTIONS"] = array("0" => GetMessage("CC_BLL_UPPER_LEVEL"));
$arResult["SECTION_PATH"] = array();

$rsSections = CIBlockSection::GetList(
	array("left_margin" => "asc"), array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"GLOBAL_ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
));
while($arSection = $rsSections->GetNext())
{
	if($section_id && !$arResult["SECTION"])
	{
		while(count($arResult["SECTION_PATH"]) && $arSection["DEPTH_LEVEL"] <= $arResult["SECTION_PATH"][count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_pop($arResult["SECTION_PATH"]);

		if(!count($arResult["SECTION_PATH"])|| $arSection["DEPTH_LEVEL"] > $arResult["SECTION_PATH"][count($arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
			array_push($arResult["SECTION_PATH"], $arSection);
	}

	if($arSection["ID"] == $section_id)
	{
		$arResult["SECTION"] = $arSection;
		$arResult["SECTION_ID"] = $arSection["ID"];
		$arResult["PARENT_SECTION_ID"] = $arSection["IBLOCK_SECTION_ID"];
	}

	$arResult["SECTIONS"][$arSection["ID"]] = array(
		"ID" => $arSection["ID"],
		"NAME"=>$arSection["NAME"],
		"LIST_URL"=>str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arSection["IBLOCK_ID"], $arSection["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams['LIST_URL']
		),
	);

	$arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
}

foreach($arResult["SECTION_PATH"] as $i => $arSection)
{
	$arResult["SECTION_PATH"][$i] = array(
		"NAME" => htmlspecialcharsex($arSection["NAME"]),
		"URL" => str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($arIBlock["ID"], intval($arSection["ID"]), $arParams["SOCNET_GROUP_ID"]),
			$arParams["LIST_URL"]
		),
	);
}

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialchars($arResult["~LISTS_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialchars($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
$arResult["LIST_URL"] = htmlspecialchars($arResult["~LIST_URL"]);

$arResult["~LIST_SECTION_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_SECTIONS_URL"]
);
$arResult["LIST_SECTION_URL"] = htmlspecialchars($arResult["~LIST_SECTION_URL"]);

$arResult["~LIST_PARENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arResult["PARENT_SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
$arResult["LIST_PARENT_URL"] = htmlspecialchars($arResult["~LIST_PARENT_URL"]);

$arResult["~BIZPROC_WORKFLOW_ADMIN_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~BIZPROC_WORKFLOW_ADMIN_URL"]
);
$arResult["BIZPROC_WORKFLOW_ADMIN_URL"] = htmlspecialchars($arResult["~BIZPROC_WORKFLOW_ADMIN_URL"]);

$obList = new CList($arIBlock["ID"]);

//Form submitted
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& (
		isset($_POST["action_button_".$arResult["GRID_ID"]])
	)
	&& $arResult["IBLOCK_PERM"] >= "W"
)
{
	$obSection = new CIBlockSection;
	$obElement = new CIBlockElement;

	/*Build filter*/
	$arFilter = array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
	);

	if($_POST["action_all_rows_".$arResult["GRID_ID"]] == "Y")
	{
		if(!$arResult["ANY_SECTION"])
			$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];
	}
	else
	{
		$arFilter["=ID"] = $_POST["ID"];
	}

	/*Take action*/
	if($_POST["action_button_".$arResult["GRID_ID"]]  == "section")
	{

		$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
		while($arElement = $rsElements->Fetch())
			$obElement->SetElementSection($arElement["ID"], array($_POST["section_to_move"]));
	}
	elseif($_POST["action_button_".$arResult["GRID_ID"]] == "delete" && isset($_POST["ID"]) && is_array($_POST["ID"]))
	{
		$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
		while($arElement = $rsElements->Fetch())
			$obElement->Delete($arElement["ID"]);
	}

	if(!isset($_POST["AJAX_CALL"]))
		LocalRedirect($arResult["~LIST_URL"]);
}

$grid_options = new CGridOptions($arResult["GRID_ID"]);
$grid_columns = $grid_options->GetVisibleColumns();
$grid_sort = $grid_options->GetSorting(array("sort"=>array("name"=>"asc")));

if($arResult["IBLOCK_PERM"] >= "U" && $arResult["IBLOCK"]["BIZPROC"]=="Y" && CModule::IncludeModule('bizproc'))
{
	$arDocumentTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$arResult["IBLOCK_ID"]));
	$arResult["BIZPROC"] = "Y";
}
else
{
	$arDocumentTemplates = array();
	$arResult["BIZPROC"] = "N";
}

/* FIELDS */
$arResult["ELEMENTS_HEADERS"] = array();
$arSelect = array("ID", "IBLOCK_ID");
$arProperties = array();

$arResult["FIELDS"] = $arListFields = $obList->GetFields();
foreach($arListFields as $FIELD_ID => $arField)
{
	if(!count($grid_columns) || in_array($FIELD_ID, $grid_columns))
	{
		if(substr($FIELD_ID, 0, 9) == "PROPERTY_")
			$arProperties[] = $FIELD_ID;
		else
			$arSelect[] = $FIELD_ID;
	}

	if($FIELD_ID == "CREATED_BY")
		$arSelect[] = "CREATED_USER_NAME";

	if($FIELD_ID == "MODIFIED_BY")
		$arSelect[] = "USER_NAME";

	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => $FIELD_ID,
		"name" => htmlspecialcharsex($arField["NAME"]),
		"default" => true,
		"sort" => $arField["MULTIPLE"]=="Y"? "": $FIELD_ID,
	);
}

if(!count($grid_columns) || in_array("IBLOCK_SECTION_ID", $grid_columns))
{
	$arSelect[] = "IBLOCK_SECTION_ID";
}
$arResult["ELEMENTS_HEADERS"][] = array(
	"id" => "IBLOCK_SECTION_ID",
	"name" => GetMessage("CC_BLL_COLUMN_SECTION"),
	"default" => true,
	"sort" => false,
);

if(count($arDocumentTemplates) > 0)
{
	$arSelect[] = "CREATED_BY";
	$arResult["ELEMENTS_HEADERS"][] = array(
		"id" => "BIZPROC",
		"name" => GetMessage("CC_BLL_COLUMN_BIZPROC"),
		"default" => true,
		"sort" => false,
	);
}



/* FILTER */
$sections = '&nbsp;<select name="list_section_id" size="1">';
$sections .= '<option value="" '.($arResult["ANY_SECTION"]? 'selected': '').'>'.GetMessage("CC_BLL_ANY").'</option>';
foreach($arResult["LIST_SECTIONS"] as $id => $name)
{
	$sections .= '<option value="'.$id.'" '.(!$arResult["ANY_SECTION"] && $id == $arResult["SECTION_ID"]? 'selected': '').'>'.$name.'</option>';
}
$sections .= '</select>&nbsp;';

$arResult["FILTER"] = array(
	array(
		"id" => "list_section_id",
		"name" => GetMessage("CC_BLL_SECTION"),
		"type" => "custom",
		"value" => $sections,
		"filtered" => $arResult["SECTION_ID"] !== false,
	),
);
$i = 1;

$arFilterable = array();
$arCustomFilter = array();

foreach($arListFields as $FIELD_ID => $arField)
{
	if($arField["TYPE"] == "ACTIVE_FROM" || $arField["TYPE"] == "ACTIVE_TO")
	{
		$arResult["FILTER"][$i] = array(
			"id" => "DATE_".$FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "date",
		);
		$arFilterable["DATE_".$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "PREVIEW_PICTURE" || $arField["TYPE"] == "DETAIL_PICTURE")
	{
	}
	elseif(is_array($arField["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicFilterHTML", $arField["PROPERTY_USER_TYPE"]))
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "custom",
			"value" => call_user_func_array($arField["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"], array(
				$arField,
				array(
					"VALUE"=>$FIELD_ID,
					"FORM_NAME"=>"filter_".$arResult["GRID_ID"],
				),
			)),
		);
		$arFilterable[$FIELD_ID] = "";
		if(array_key_exists("AddFilterFields", $arField["PROPERTY_USER_TYPE"]))
			$arCustomFilter[$FIELD_ID] = array(
				"callback" => $arField["PROPERTY_USER_TYPE"]["AddFilterFields"],
				"filter" => &$arResult["FILTER"][$i],
			);
	}
	elseif($arField["TYPE"] == "F")
	{
	}
	elseif($arField["TYPE"] == "SORT" || $arField["TYPE"] == "N")
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "number",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "G")
	{
		$items = array();
		$prop_secs = CIBlockSection::GetList(array("left_margin" => "asc"), array("IBLOCK_ID" => $arField["LINK_IBLOCK_ID"]));
		while($ar_sec = $prop_secs->Fetch())
			$items[$ar_sec["ID"]] = str_repeat(". ", $ar_sec["DEPTH_LEVEL"]-1).$ar_sec["NAME"];

		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "list",
			"items" => $items,
			"params" => array("size"=>5, "multiple"=>"multiple"),
			"valign" => "top",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "E")
	{
		//Should be handled in template
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "E",
			"value" => $arField,
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "L")
	{
		$items = array();
		$prop_enums = CIBlockProperty::GetPropertyEnum($arField["ID"]);
		while($ar_enum = $prop_enums->Fetch())
			$items[$ar_enum["ID"]] = $ar_enum["VALUE"];

		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
			"type" => "list",
			"items" => $items,
			"params" => array("size"=>5, "multiple"=>"multiple"),
			"valign" => "top",
		);
		$arFilterable[$FIELD_ID] = "";
	}
	elseif($arField["TYPE"] == "S" || $arField["TYPE"] == "NAME")
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
		);
		$arFilterable[$FIELD_ID] = "?";
	}
	else
	{
		$arResult["FILTER"][$i] = array(
			"id" => $FIELD_ID,
			"name" => htmlspecialcharsex($arField["NAME"]),
		);
		$arFilterable[$FIELD_ID] = "";
	}

	$i++;
}

$arFilter = array();
$grid_filter = $grid_options->GetFilter($arResult["FILTER"]);
foreach($grid_filter as $key => $value)
{
	if(substr($key, -5) == "_from")
	{
		$op = ">=";
		$new_key = substr($key, 0, -5);
	}
	elseif(substr($key, -3) == "_to")
	{
		$op = "<=";
		$new_key = substr($key, 0, -3);
	}
	else
	{
		$op = "";
		$new_key = $key;
	}

	if(array_key_exists($new_key, $arFilterable))
	{
		if($op == "")
			$op = $arFilterable[$new_key];
		$arFilter[$op.$new_key] = $value;
	}
}

foreach($arCustomFilter as $FIELD_ID => $arCallback)
{
	call_user_func_array($arCallback["callback"], array(
		$arListFields[$FIELD_ID],
		array("VALUE" => $FIELD_ID),
		&$arFilter,
		&$filtered,
	));
	$arCallback["filter"]["filtered"] = $filtered;
}

$arFilter["IBLOCK_ID"] = $arIBlock["ID"];
$arFilter["CHECK_PERMISSIONS"] = ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y");
if(!$arResult["ANY_SECTION"])
	$arFilter["SECTION_ID"] = $arResult["SECTION_ID"];

$rsElements = CIBlockElement::GetList(
	$grid_sort["sort"], $arFilter, false, $grid_options->GetNavParams(), $arSelect
);

$arResult["ELEMENTS_ROWS"] = array();
while($obElement = $rsElements->GetNextElement())
{
	$data = $obElement->GetFields();
	$prop = false;

	$aCols = array();
	foreach($arProperties as $FIELD_ID)
	{
		if(!$prop)
			$prop = $obElement->GetProperties();

		$arField = $arResult["FIELDS"][$FIELD_ID];

		if($arField["CODE"])
			$data[$FIELD_ID] = $prop[$arField["CODE"]]["VALUE"];
		else
			$data[$FIELD_ID] = $prop[$arField["ID"]]["VALUE"];
	}

	if(isset($data["CREATED_BY"]))
		$data["CREATED_BY"] = "[".$data["CREATED_BY"]."] ".$data["CREATED_USER_NAME"];

	if(isset($data["MODIFIED_BY"]))
		$data["MODIFIED_BY"] = "[".$data["MODIFIED_BY"]."] ".$data["USER_NAME"];

	$arBPStart = array();
	foreach($arDocumentTemplates as $arWorkflowTemplate)
	{
		$url = CHTTP::urlAddParams(str_replace(
			array("#list_id#", "#section_id#", "#element_id#", "#workflow_template_id#", "#group_id#"),
			array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]), $arWorkflowTemplate["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams["BIZPROC_WORKFLOW_START_URL"]
		), array("workflow_template_id" => $arWorkflowTemplate["ID"]));
		$arBPStart[] = array(
			"TEXT" => $arWorkflowTemplate["NAME"],
			"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
		);
	}

	$url = str_replace(
		array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
		array($arIBlock["ID"], intval($arResult["SECTION_ID"]), intval($data["~ID"]), $arParams["SOCNET_GROUP_ID"]),
		$arParams["LIST_ELEMENT_URL"]
	);
	if($arResult["ANY_SECTION"])
		$url = CHTTP::urlAddParams($url, array("list_section_id" => ""));

	if($arResult["IBLOCK_PERM"] >= "W")
	{
		$aActions = array(
			array(
				"ICONCLASS" => "edit",
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_EDIT"),
				"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				"DEFAULT" => true,
			),
		);
		if(count($arBPStart))
		{
			$aActions[] = array(
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_START_BP"),
				"MENU" => $arBPStart,
			);
		}
		$aActions[] = array("SEPARATOR" => true);
		$aActions[] = array(
			"ICONCLASS" => "delete",
			"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE"),
			"ONCLICK" => "bxGrid_".$arResult["GRID_ID"].".DeleteItem('".$data["ID"]."', '".GetMessage("CC_BLL_ELEMENT_ACTION_MENU_DELETE_CONF")."')",
		);
	}
	elseif($arResult["IBLOCK_PERM"] >= "U")
	{
		$aActions = array(
			array(
				"ICONCLASS" => "view",
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_VIEW"),
				"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				"DEFAULT" => true,
			),
		);
		if(count($arBPStart))
		{
			$aActions[] = array(
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_START_BP"),
				"MENU" => $arBPStart,
			);
		}
	}
	else
	{
		$aActions = array(
			array(
				"ICONCLASS" => "view",
				"TEXT" => GetMessage("CC_BLL_ELEMENT_ACTION_MENU_VIEW"),
				"ONCLICK" =>"jsUtils.Redirect(arguments, '".CUtil::JSEscape($url)."')",
				"DEFAULT" => true,
			),
		);
	}

	$arResult["ELEMENTS_ROWS"][] = array(
		"id" => $data["ID"],
		"data" => $data,
		"actions" => $aActions,
		"columns" => $aCols,
	);
}

$rsElements->bShowAll = false;
$arResult["NAV_OBJECT"] = $rsElements;
$arResult["SORT"] = $grid_sort["sort"];

$arResult["LIST_NEW_ELEMENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
	array($arIBlock["ID"], intval($arResult["SECTION_ID"]), 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["LIST_ELEMENT_URL"]
);
if($arResult["ANY_SECTION"])
	$arResult["LIST_NEW_ELEMENT_URL"] = CHTTP::urlAddParams($arResult["LIST_NEW_ELEMENT_URL"], array("list_section_id" => ""));

$APPLICATION->SetTitle(GetMessage("CC_BLL_TITLE", array("#NAME#" => $arResult["IBLOCK"]["NAME"])));

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => "")));

foreach($arResult["SECTION_PATH"] as $arPath)
{
	$APPLICATION->AddChainItem($arPath["NAME"], $arPath["URL"]);
}

$this->IncludeComponentTemplate();

?>