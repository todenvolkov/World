<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$bBizproc = CModule::IncludeModule("bizproc");
$bWorkflow = CModule::IncludeModule("workflow");

if($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame')
	CFile::DisableJSFunction(true);

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
if($arIBTYPE===false)
	$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

$IBLOCK_ID = IntVal($IBLOCK_ID);
$BlockPerm = "D";

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
{
	$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
	if($bWorkflow && $arIBlock["WORKFLOW"] != "N"
		|| $bBizproc && $arIBlock["BIZPROC"] != "N")
		$bBadBlock=($BlockPerm<"U");
	else
		$bBadBlock=($BlockPerm<"W");
}
else
	$bBadBlock = true;

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if($bBadBlock):
	?>
	<?echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>
	<a href="iblock_admin.php?lang=<?echo LANG?>&amp;type=<?echo htmlspecialchars($type)?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?
	endif;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arIBlock["SITE_ID"] = array();
$rsSites = CIBlock::GetSite($IBLOCK_ID);
while($arSite = $rsSites->Fetch())
	$arIBlock["SITE_ID"] = $arSite["LID"];

$bWorkFlow = $bWorkflow && (CIBlock::GetArrayByID($IBLOCK_ID, "WORKFLOW") != "N");
$bBizproc = $bBizproc && (CIBlock::GetArrayByID($IBLOCK_ID, "BIZPROC") != "N");

$bCatalog = CModule::IncludeModule("catalog");
if($bCatalog)
{
	$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
	if(!$rs->Fetch())
	{
		$bCatalog = false;
	}
	else
	{
		if(!$USER->CanDoOperation('catalog_read') && !$USER->CanDoOperation('catalog_price'))
			$bCatalog = false;
	}
}

$dbrFProps = CIBlockProperty::GetList(
		Array(
			"SORT"=>"ASC",
			"NAME"=>"ASC"
		),
		Array(
			"ACTIVE"=>"Y",
			"IBLOCK_ID"=>$IBLOCK_ID
		)
	);

$arProps = Array();
while($arProp = $dbrFProps->GetNext())
{
	if(strlen($arProp["USER_TYPE"])>0)
		$arUserType = CIBlockProperty::GetUserType($arProp["USER_TYPE"]);
	else
		$arUserType = array();

	$arProp["PROPERTY_USER_TYPE"] = $arUserType;

	$arProps[] = $arProp;
}

$sTableID = "tbl_iblock_element_".md5($type.".".$IBLOCK_ID);
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
$lAdmin->bMultipart = true;

$arFilterFields = Array(
	"find_el",
	"find_el_type",
	"find_section_section",
	"find_el_subsections",
	"find_el_id_start",
	"find_el_id_end",
	"find_el_timestamp_from",
	"find_el_timestamp_to",
	"find_el_modified_user_id",
	"find_el_modified_by",
	"find_el_created_from",
	"find_el_created_to",
	"find_el_created_user_id",
	"find_el_created_by",
	"find_el_status_id",
	"find_el_status",
	"find_el_date_active_from_from",
	"find_el_date_active_from_to",
	"find_el_date_active_to_from",
	"find_el_date_active_to_to",
	"find_el_active",
	"find_el_name",
	"find_el_intext",
	"find_el_code",
	"find_el_external_id",
	"find_el_tags",
);

for($i=0; $i<count($arProps); $i++)
{
	if($arProps[$i]["FILTRABLE"]!="Y" || $arProps[$i]["PROPERTY_TYPE"]=="F")
		continue;
	$arFilterFields[] = "find_el_property_".$arProps[$i]["ID"];
}


if(strlen($del_filter)>0 || !isset($find_section_section))
	$find_section_section = -1;

//We have to handle current section in a special way
$section_id = intval($find_section_section);
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;
//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANG.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.intval($find_section_section);

$arFilter = Array(
	"IBLOCK_ID"		=> $IBLOCK_ID,
	"SECTION_ID"		=> $find_section_section,
	"MODIFIED_USER_ID"	=> $find_el_modified_user_id,
	"MODIFIED_BY"		=> $find_el_modified_by,
	"CREATED_USER_ID"	=> $find_el_created_user_id,
	"ACTIVE"		=> $find_el_active,
	"CODE"			=> $find_el_code,
	"EXTERNAL_ID"		=> $find_el_external_id,
	"?TAGS"			=> $find_el_tags,
	"?NAME"			=> ($find_el!='' && $find_el_type == "name"? $find_el: $find_el_name),
	"?SEARCHABLE_CONTENT"	=> ($find_el!='' && $find_el_type == "desc"? $find_el: $find_el_intext),
	"SHOW_NEW"		=> "Y",
);

if($bBizproc && ($BlockPerm < "W"))
	$arFilter["CHECK_BP_PERMISSIONS"] = "read";

for($i=0, $intPropCount = count($arProps); $i<$intPropCount ; $i++)
{
	if (('Y' == $arProps[$i]["FILTRABLE"]) && ('F' != $arProps[$i]["PROPERTY_TYPE"]))
	{
		if (true == array_key_exists("AddFilterFields", $arProps[$i]["PROPERTY_USER_TYPE"]))
		{
			call_user_func_array($arProps[$i]["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
				$arProps[$i],
				array("VALUE" => "find_el_property_".$arProps[$i]["ID"]),
				&$arFilter,
				&$filtered,
			));
		}
		else
		{
			$value = ${"find_el_property_".$arProps[$i]["ID"]};
			if(strlen($value) || is_array($value))
			{
				if($value === "NOT_REF")
					$value = false;
				$arFilter["?PROPERTY_".$arProps[$i]["ID"]] = $value;
			}			
		}
	}
}

if(IntVal($find_section_section)<0 || strlen($find_section_section)<=0)
	unset($arFilter["SECTION_ID"]);

elseif($find_el_subsections=="Y")
{
	if($arFilter["SECTION_ID"]==0)
		unset($arFilter["SECTION_ID"]);
	else
		$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
}

if(!empty($find_el_id_start))
	$arFilter[">=ID"] = $find_el_id_start;
if(!empty($find_el_id_end))
	$arFilter["<=ID"] = $find_el_id_end;
if(!empty($find_el_timestamp_from))
	$arFilter["DATE_MODIFY_FROM"] = $find_el_timestamp_from;
if(!empty($find_el_timestamp_to))
	$arFilter["DATE_MODIFY_TO"] = $find_el_timestamp_to;
if(!empty($find_el_created_from))
	$arFilter[">=DATE_CREATE"] = $find_el_created_from;
if(!empty($find_el_created_to))
	$arFilter["<=DATE_CREATE"] = $find_el_created_to;
if(!empty($find_el_created_by) && strlen($find_el_created_by)>0)
	$arFilter["CREATED_BY"] = $find_el_created_by;
if(!empty($find_el_status_id))
	$arFilter["WF_STATUS"] = substr($find_el_status_id,0,1);
if(!empty($find_el_status) && strlen($find_el_status)>0)
	$arFilter["WF_STATUS"] = $find_el_status;
if(!empty($find_el_date_active_from_from))
	$arFilter[">=DATE_ACTIVE_FROM"] = $find_el_date_active_from_from;
if(!empty($find_el_date_active_from_to))
	$arFilter["<=DATE_ACTIVE_FROM"] = $find_el_date_active_from_to;
if(!empty($find_el_date_active_to_from))
	$arFilter[">=DATE_ACTIVE_TO"] = $find_el_date_active_to_from;
if(!empty($find_el_date_active_to_to))
	$arFilter["<=DATE_ACTIVE_TO"] = $find_el_date_active_to_to;

if($lAdmin->EditAction())
{
	if(is_array($_FILES['FIELDS']))
		CAllFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);
	if(is_array($FIELDS_del))
		CAllFile::ConvertFilesToPost($FIELDS_del, $_POST['FIELDS'], "del");

	foreach($_POST['FIELDS'] as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$ID = IntVal($ID);

	   	$arRes = CIBlockElement::GetByID($ID);
	   	$arRes = $arRes->Fetch();
	   	if(!$arRes)
	   		continue;

		$WF_ID = $ID;
		if($bWorkFlow)
		{
			$WF_ID = CIBlockElement::WF_GetLast($ID);
			if($WF_ID!=$ID)
			{
				$rsData2 = CIBlockElement::GetByID($WF_ID);
				if($arRes = $rsData2->Fetch())
					$WF_ID = $arRes["ID"];
				else
					$WF_ID = $ID;
			}

			if($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
			{
				$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR1")." (ID:".$ID.")", $ID);
				continue;
			}
		}
		elseif ($bBizproc)
		{
			if (CIBlockDocument::IsDocumentLocked($ID, ""))
			{
				$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
				continue;
			}
		}

		$bPermissions = false;
		//delete and modify can:
		if($BlockPerm>='W') // only writers
			$bPermissions = true;
		elseif($bWorkFlow)
		{
			// change is under workflow find status and its permissions
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]);
			if($STATUS_PERMISSION>=2)
				$bPermissions = true;

			// status change  - check permissions
			if(isset($arFields["WF_STATUS_ID"]))
			{
				if(CIBlockElement::WF_GetStatusPermission($arFields["WF_STATUS_ID"])<1)
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR2")." (ID:".$ID.")", $ID);
					continue;
				}
			}
		}
		elseif ($bBizproc)
		{
			$bCanWrite = CIBlockDocument::CanUserOperateDocument(
				IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
				$USER->GetID(),
				$ID,
				array("IBlockId" => $IBLOCK_ID, "IBlockPermission" => $BlockPerm, "UserGroups" => $USER->GetUserGroupArray())
			);
			if ($bCanWrite)
			{
				$bPermissions = true;
			}
		}

		if(!$bPermissions)
		{
			$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$ID.")", $ID);
			continue;
		}

		if(!is_array($arFields["PROPERTY_VALUES"]))
			$arFields["PROPERTY_VALUES"] = Array();
		$bFieldProps = array();
		foreach($arFields as $k=>$v)
		{
			if(
				substr($k, 0, strlen("PROPERTY_")) == "PROPERTY_"
				&& $k != "PROPERTY_VALUES"
			)
			{
				$prop_id = substr($k, strlen("PROPERTY_"));
				$arFields["PROPERTY_VALUES"][$prop_id] = $v;
				unset($arFields[$k]);
				$bFieldProps[$prop_id]=true;
			}
		}
		if(count($bFieldProps) > 0)
		{
			//We have to read properties from database in order not to delete its values
			if(!$bWorkFlow)
			{
				$dbPropV = CIBlockElement::GetProperty($IBLOCK_ID, $ID, "sort", "asc", Array("ACTIVE"=>"Y"));
				while($arPropV = $dbPropV->Fetch())
				{
					if(!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
					{
						if(!array_key_exists($arPropV["ID"], $arFields["PROPERTY_VALUES"]))
							$arFields["PROPERTY_VALUES"][$arPropV["ID"]] = array();

						$arFields["PROPERTY_VALUES"][$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]] = array(
							"VALUE" => $arPropV["VALUE"],
							"DESCRIPTION" => $arPropV["DESCRIPTION"],
						);
					}
				}
			}
		}
		else
		{
			//We will not update property values
			unset($arFields["PROPERTY_VALUES"]);
		}

		//All not displayed required fields from DB
		foreach($arIBlock["FIELDS"] as $FIELD_ID => $field)
		{
			if(
				$field["IS_REQUIRED"] === "Y"
				&& !array_key_exists($FIELD_ID, $arFields)
				&& $FIELD_ID !== "DETAIL_PICTURE"
				&& $FIELD_ID !== "PREVIEW_PICTURE"
			)
				$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
		}
		if($arRes["IN_SECTIONS"] == "Y")
		{
			$arFields["IBLOCK_SECTION"] = array();
			$rsSections = CIBlockElement::GetElementGroups($arRes["ID"], true);
			while($arSection = $rsSections->Fetch())
				$arFields["IBLOCK_SECTION"][] = $arSection["ID"];
		}

		$arFields["MODIFIED_BY"]=$USER->GetID();
		$ib = new CIBlockElement;
		$DB->StartTransaction();

		if(!$ib->Update($ID, $arFields, true, true, true))
		{
			$lAdmin->AddUpdateError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ib->LAST_ERROR)), $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}

		if($bCatalog)
		{
			if($USER->CanDoOperation('catalog_price'))
			{
				$CATALOG_QUANTITY = $arFields["CATALOG_QUANTITY"];
				$CATALOG_QUANTITY_TRACE = $arFields["CATALOG_QUANTITY_TRACE"];

				if(!CCatalogProduct::GetByID($ID))
				{
					$arCatalogQuantity = Array("ID" => $ID);
					if(strlen($CATALOG_QUANTITY) > 0)
						$arCatalogQuantity["QUANTITY"] = $CATALOG_QUANTITY;
					if(strlen($CATALOG_QUANTITY_TRACE) > 0)
						$arCatalogQuantity["QUANTITY_TRACE"] = ($CATALOG_QUANTITY_TRACE == "Y") ? "Y" : "N";
					CCatalogProduct::Add($arCatalogQuantity);
				}
				else
				{
					$arCatalogQuantity = Array();
					if(strlen($CATALOG_QUANTITY) > 0)
						$arCatalogQuantity["QUANTITY"] = $CATALOG_QUANTITY;
					if(strlen($CATALOG_QUANTITY_TRACE) > 0)
						$arCatalogQuantity["QUANTITY_TRACE"] = ($CATALOG_QUANTITY_TRACE == "Y") ? "Y" : "N";
					if(!empty($arCatalogQuantity))
						CCatalogProduct::Update($ID, $arCatalogQuantity);
				}
			}
		}
	}

	if($bCatalog)
	{
		if($USER->CanDoOperation('catalog_price') && (isset($_POST["CATALOG_PRICE"]) || isset($_POST["CATALOG_CURRENCY"])))
		{
			$CATALOG_PRICE = $_POST["CATALOG_PRICE"];
			$CATALOG_CURRENCY = $_POST["CATALOG_CURRENCY"];
			$CATALOG_EXTRA = $_POST["CATALOG_EXTRA"];
			$CATALOG_PRICE_ID = $_POST["CATALOG_PRICE_ID"];
			$CATALOG_QUANTITY_FROM = $_POST["CATALOG_QUANTITY_FROM"];
			$CATALOG_QUANTITY_TO = $_POST["CATALOG_QUANTITY_TO"];
			$CATALOG_PRICE_old = $_POST["CATALOG_old_PRICE"];
			$CATALOG_CURRENCY_old = $_POST["CATALOG_old_CURRENCY"];

			$db_extras = CExtra::GetList(($by3="NAME"), ($order3="ASC"));
			while ($extras = $db_extras->Fetch())
				$arCatExtraUp[$extras["ID"]] = $extras["PERCENTAGE"];

			if(!CCatalogProduct::GetByID($ID))
				CCatalogProduct::Add(Array("ID" => $ID));

			foreach($CATALOG_PRICE as $elID => $arPrice)
			{
				//1 Find base price ID
				//2 If such a column is displayed then
				//	check if it is greater than 0
				//3 otherwise
				//	look up it's value in database and
				//	output an error if not found or found less or equal then zero
				$bError = false;
				$arBaseGroup = CCatalogGroup::GetBaseGroup();
				if (isset($arPrice[$arBaseGroup['ID']]))
				{
					if ($arPrice[$arBaseGroup['ID']] <= 0)
					{
						$bError = true;
						$lAdmin->AddUpdateError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
					}
				}
				else
				{
					$arBasePrice = CPrice::GetBasePrice(
						$elID,
						$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']],
						$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']]
					);

					if (!is_array($arBasePrice) || $arBasePrice['PRICE'] <= 0)
					{
						$bError = true;
						$lAdmin->AddGroupError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
					}
				}

				if($bError)
					continue;

				$arCurrency = $CATALOG_CURRENCY[$elID];

				$dbCatalogGroups = CCatalogGroup::GetList(
						array("SORT" => "ASC"),
						array("CAN_ACCESS" => "Y", "LID"=>LANGUAGE_ID)
					);
				while ($arCatalogGroup = $dbCatalogGroups->Fetch())
				{
					if(doubleval($arPrice[$arCatalogGroup["ID"]]) != doubleval($CATALOG_PRICE_old[$elID][$arCatalogGroup["ID"]])
						|| $arCurrency[$arCatalogGroup["ID"]] != $CATALOG_CURRENCY_old[$elID][$arCatalogGroup["ID"]])
					{
						if($arCatalogGroup["BASE"]=="Y") // if base price check extra for other prices
						{
							$arFields = Array(
								"PRODUCT_ID" => $elID,
								"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
								"PRICE" => DoubleVal($arPrice[$arCatalogGroup["ID"]]),
								"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
								"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
								"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]],
							);
							if($arFields["PRICE"] <=0 )
							{
								CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
							}
							elseif(IntVal($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]])>0)
							{
								CPrice::Update(IntVal($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]), $arFields);
							}
							elseif($arFields["PRICE"] > 0)
							{
								CPrice::Add($arFields);
							}

							$arPrFilter = array(
						                "PRODUCT_ID" => $elID,
							);
							if(DoubleVal($arPrice[$arCatalogGroup["ID"]])>0)
							{
								$arPrFilter["!CATALOG_GROUP_ID"] = $arCatalogGroup["ID"];
								$arPrFilter["+QUANTITY_FROM"] = "1";
								$arPrFilter["!EXTRA_ID"] = false;
							}
							$db_res = CPrice::GetList(
								array(),
								$arPrFilter,
								false,
								false,
								Array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "EXTRA_ID")
							);
							while($ar_res = $db_res->Fetch())
							{
								$arFields = Array(
									"PRICE" => DoubleVal($arPrice[$arCatalogGroup["ID"]])*(1+$arCatExtraUp[$ar_res["EXTRA_ID"]]/100) ,
									"EXTRA_ID" => $ar_res["EXTRA_ID"],
									"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
									"QUANTITY_FROM" => $ar_res["QUANTITY_FROM"],
									"QUANTITY_TO" => $ar_res["QUANTITY_TO"]
								);
								if($arFields["PRICE"] <= 0)
									CPrice::Delete($ar_res["ID"]);
								else
									CPrice::Update($ar_res["ID"], $arFields);
							}
						}
						elseif(!isset($CATALOG_EXTRA[$elID][$arCatalogGroup["ID"]]))
						{
							$arFields = Array(
								"PRODUCT_ID" => $elID,
								"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
								"PRICE" => DoubleVal($arPrice[$arCatalogGroup["ID"]]),
								"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
								"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
								"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]]
							);
							if($arFields["PRICE"] <= 0)
								CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
							elseif(IntVal($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]])>0)
								CPrice::Update(IntVal($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]), $arFields);
							elseif($arFields["PRICE"] > 0)
								CPrice::Add($arFields);
						}
					}
				}
			}
		}
	}
}

if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CIBlockElement::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

	   	$ID = IntVal($ID);
	   	$arRes = CIBlockElement::GetByID($ID);
	   	$arRes = $arRes->Fetch();
	   	if(!$arRes)
	   		continue;

		$WF_ID = $ID;
		if($bWorkFlow)
		{
			$WF_ID = CIBlockElement::WF_GetLast($ID);
			if($WF_ID!=$ID)
			{
				$rsData2 = CIBlockElement::GetByID($WF_ID);
				if($arRes = $rsData2->Fetch())
					$WF_ID = $arRes["ID"];
				else
					$WF_ID = $ID;
			}

			if($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
			{
				$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR1")." (ID:".$ID.")", $ID);
				continue;
			}
		}
		elseif ($bBizproc)
		{
			if (CIBlockDocument::IsDocumentLocked($ID, "") && !($_REQUEST['action']=='unlock' && CBPDocument::IsAdmin()))
			{
				$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
				continue;
			}
		}

		$bPermissions = false;
		//delete and modify can:
		if($BlockPerm>='W') // only writers
			$bPermissions = true;
		elseif($bWorkFlow)
		{
			//For delete action we have to check all statuses in element history
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"], $_REQUEST['action']=="delete"? $ID: false);
			if($STATUS_PERMISSION>=2)
				$bPermissions = true;
		}
		elseif ($bBizproc)
		{
			$bCanWrite = CIBlockDocument::CanUserOperateDocument(
				IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
				$USER->GetID(),
				$ID,
				array("IBlockId" => $IBLOCK_ID, "IBlockPermission" => $BlockPerm, "UserGroups" => $USER->GetUserGroupArray())
			);
			if ($bCanWrite)
				$bPermissions = true;
		}

		if(!$bPermissions)
		{
			$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$ID.")", $ID);
			continue;
		}

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$APPLICATION->ResetException();
			if(!CIBlockElement::Delete($ID))
			{
				$DB->Rollback();
				if($ex = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
				else
					$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR"), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "activate":
		case "deactivate":
			$ob = new CIBlockElement();
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields, true))
				$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $ID);
			break;
		case "section":
		case "add_section":
			$new_section = intval($_REQUEST["section_to_move"]);
			if(($new_section >= 0) && ($new_section != $section_id))
			{
				$obE = new CIBlockElement();

				$arSections = array();
				$rsSections = $obE->GetElementGroups($ID, true);
				while($ar = $rsSections->Fetch())
					$arSections[$ar["ID"]] = $ar["ID"];

				if($_REQUEST['action'] == "section")
					$arSections[$section_id] = $new_section;
				else
					$arSections[$new_section] = $new_section;

				if(!$obE->Update($ID, array("IBLOCK_SECTION" => $arSections)))
					$lAdmin->AddGroupError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obE->LAST_ERROR)), $TYPE.$ID);
			}
			break;
		case "lock":
			CIBlockElement::WF_Lock($ID);
			break;
		case "unlock":
			CIBlockElement::WF_UnLock($ID);
			break;
		}
	}

	if(isset($return_url) && strlen($return_url)>0)
		LocalRedirect($return_url);
}

$CAdminCalendar_ShowScript = CAdminCalendar::ShowScript();

$arHeader = Array();
$arHeader[] = array("id"=>"NAME", "content"=>GetMessage("IBLOCK_FIELD_NAME"), "sort"=>"name", "default"=>true);

$arHeader[] = array("id"=>"ACTIVE", "content"=>GetMessage("IBLOCK_FIELD_ACTIVE"), "sort"=>"active", "default"=>true, "align"=>"center");
$arHeader[] = array("id"=>"DATE_ACTIVE_FROM", "content"=>GetMessage("IBEL_A_ACTFROM"), "sort"=>"date_active_from");
$arHeader[] = array("id"=>"DATE_ACTIVE_TO", "content"=>GetMessage("IBEL_A_ACTTO"), "sort"=>"date_active_to");
$arHeader[] = array("id"=>"SORT", "content"=>GetMessage("IBLOCK_FIELD_SORT"), "sort"=>"sort", "default"=>true, "align"=>"right");
$arHeader[] = array("id"=>"TIMESTAMP_X", "content"=>GetMessage("IBLOCK_FIELD_TIMESTAMP_X"), "sort"=>"timestamp_x", "default"=>true);
$arHeader[] = array("id"=>"USER_NAME", "content"=>GetMessage("IBLOCK_FIELD_USER_NAME"), "sort"=>"modified_by");
$arHeader[] = array("id"=>"DATE_CREATE", "content"=>GetMessage("IBLOCK_EL_ADMIN_DCREATE"), "sort"=>"created");
$arHeader[] = array("id"=>"CREATED_USER_NAME", "content"=>GetMessage("IBLOCK_EL_ADMIN_WCREATE2"), "sort"=>"created_by");

$arHeader[] = array("id"=>"CODE", "content"=>GetMessage("IBEL_A_CODE"), "sort"=>"code");
$arHeader[] = array("id"=>"EXTERNAL_ID", "content"=>GetMessage("IBEL_A_EXTERNAL_ID"), "sort"=>"external_id");
$arHeader[] = array("id"=>"TAGS", "content"=>GetMessage("IBEL_A_TAGS"), "sort"=>"tags");

if($bWorkFlow)
{
	$arHeader[] = array("id"=>"WF_STATUS_ID", "content"=>GetMessage("IBLOCK_FIELD_STATUS"), "sort"=>"status", "default"=>true);
	$arHeader[] = array("id"=>"WF_NEW", "content"=>GetMessage("IBEL_A_EXTERNAL_WFNEW"), "sort"=>"");
	$arHeader[] = array("id"=>"LOCK_STATUS", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK"), "default"=>true);
	$arHeader[] = array("id"=>"LOCKED_USER_NAME", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK_BY"));
	$arHeader[] = array("id"=>"WF_DATE_LOCK", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK_WHEN"));
	$arHeader[] = array("id"=>"WF_COMMENTS", "content"=>GetMessage("IBEL_A_EXTERNAL_COM"));
}

$arHeader[] = array("id"=>"SHOW_COUNTER", "content"=>GetMessage("IBEL_A_EXTERNAL_SHOWS"), "sort"=>"show_counter", "align"=>"right");
$arHeader[] = array("id"=>"SHOW_COUNTER_START", "content"=>GetMessage("IBEL_A_EXTERNAL_SHOW_F"), "sort"=>"show_counter_start", "align"=>"right");
$arHeader[] = array("id"=>"PREVIEW_PICTURE", "content"=>GetMessage("IBEL_A_EXTERNAL_PREV_PIC"), "align"=>"right");
$arHeader[] = array("id"=>"PREVIEW_TEXT", "content"=>GetMessage("IBEL_A_EXTERNAL_PREV_TEXT"));
$arHeader[] = array("id"=>"DETAIL_PICTURE", "content"=>GetMessage("IBEL_A_EXTERNAL_DET_PIC"), "align"=>"center");
$arHeader[] = array("id"=>"DETAIL_TEXT", "content"=>GetMessage("IBEL_A_EXTERNAL_DET_TEXT"));
$arHeader[] = array("id"=>"ID", "content"=>'ID', "sort"=>"id", "default"=>true, "align"=>"right");


for($i=0; $i<count($arProps); $i++)
{
	$arFProps = $arProps[$i];
	$arHeader[] = array("id"=>"PROPERTY_".$arFProps['ID'], "content"=>$arFProps['NAME'], "align"=>($arFProps["PROPERTY_TYPE"]=='N'?"right":"left"), "sort" => ($arFProps["MULTIPLE"]!='Y'? "PROPERTY_".$arFProps['ID'] : ""));
}

$arWFStatus = Array();
if($bWorkFlow)
{
	$rsWF = CWorkflowStatus::GetDropDownList("Y");
	while($arWF = $rsWF->GetNext())
		$arWFStatus[$arWF["~REFERENCE_ID"]] = $arWF["~REFERENCE"];
}

if($bCatalog)
{
	if($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_price'))
	{
		$arCatGroup = Array();
		$dbCatalogGroups = CCatalogGroup::GetList(
				array("SORT" => "ASC"),
				array("CAN_ACCESS" => "Y", "LID"=>LANGUAGE_ID)
			);
		while ($arCatalogGroup = $dbCatalogGroups->Fetch())
		{
			$arHeader[] = array(
				"id" => "CATALOG_GROUP_".$arCatalogGroup["ID"],
				"content" => htmlspecialcharsex($arCatalogGroup["NAME_LANG"]),
				"align" => "right",
				"sort" => "CATALOG_PRICE_".$arCatalogGroup["ID"],
			);
			$arCatGroup[$arCatalogGroup["ID"]] = $arCatalogGroup;
		}
		$arCatExtra = Array();

		$db_extras = CExtra::GetList(($by3="NAME"), ($order3="ASC"));
		while ($extras = $db_extras->Fetch())
			$arCatExtra[] = $extras;
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY",
			"content" => GetMessage("IBEL_CATALOG_QUANTITY"),
			"align" => "right",
			"sort" => "CATALOG_QUANTITY",
		);
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY_TRACE",
			"content" => GetMessage("IBEL_CATALOG_QUANTITY_TRACE"),
			"align" => "right",
		);
	}
}

if ($bBizproc)
{
	$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID));
	foreach ($arWorkflowTemplates as $arTemplate)
	{
		$arHeader[] = array(
			"id" => "WF_".$arTemplate["ID"],
			"content" => $arTemplate["NAME"],
		);
	}
	$arHeader[] = array(
		"id" => "BIZPROC",
		"content" => GetMessage("IBEL_A_BP_H"),
	);
	$arHeader[] = array(
		"id" => "BP_PUBLISHED",
		"content" => GetMessage("IBLOCK_FIELD_BP_PUBLISHED"),
		"sort" => "status",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeader);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();

$arSelectedProps = Array();
foreach($arProps as $i => $arProperty)
{
	$k = array_search("PROPERTY_".$arProperty['ID'], $arSelectedFields);
	if($k!==false)
	{
		$arSelectedProps[] = $arProperty;
		if($arProperty["PROPERTY_TYPE"] == "L")
		{
			$arSelect[$arProperty['ID']] = Array();
			$rs = CIBlockProperty::GetPropertyEnum($arProperty['ID']);
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = $ar["VALUE"];
		}
		elseif($arProperty["PROPERTY_TYPE"] == "G")
		{
			$arSelect[$arProperty['ID']] = Array();
			$rs = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$arProperty["LINK_IBLOCK_ID"]));
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"];
		}
		unset($arSelectedFields[$k]);
	}
}

if(!in_array("ID", $arSelectedFields))
	$arSelectedFields[] = "ID";
if(!in_array("CREATED_BY", $arSelectedFields))
	$arSelectedFields[] = "CREATED_BY";

$arSelectedFields[] = "LANG_DIR";
$arSelectedFields[] = "LID";
$arSelectedFields[] = "WF_PARENT_ELEMENT_ID";

if(in_array("LOCKED_USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "WF_LOCKED_BY";
if(in_array("USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "MODIFIED_BY";
if(in_array("CREATED_USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "CREATED_BY";
if(in_array("PREVIEW_TEXT", $arSelectedFields))
	$arSelectedFields[] = "PREVIEW_TEXT_TYPE";
if(in_array("DETAIL_TEXT", $arSelectedFields))
	$arSelectedFields[] = "DETAIL_TEXT_TYPE";

$arSelectedFields[] = "LOCK_STATUS";
$arSelectedFields[] = "WF_NEW";
$arSelectedFields[] = "WF_STATUS_ID";
$arSelectedFields[] = "DETAIL_PAGE_URL";
$arSelectedFields[] = "SITE_ID";
$arSelectedFields[] = "CODE";
$arSelectedFields[] = "EXTERNAL_ID";

$arSelectedFieldsMap = array();
foreach($arSelectedFields as $field)
	$arSelectedFieldsMap[$field] = true;


if(is_array($arCatGroup))
{
	foreach($arCatGroup as $CatalogGroups)
	{
		if(in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
		{
			$arFilter["CATALOG_SHOP_QUANTITY_".$CatalogGroups["ID"]] = 1;
		}
	}
}

//We need javascript not in excel mode
if(($_REQUEST["mode"]=='list' || $_REQUEST["mode"]=='frame') && isset($arCatGroup))
{
	?><script language="JavaScript">
		top.arCatalogShowedGroups = new Array();
	<?
	if(is_array($arCatGroup))
	{
		$i=0;
		foreach($arCatGroup as $CatalogGroups)
		{
			if(in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
			{
				echo "top.arCatalogShowedGroups[".$i."]=".$CatalogGroups["ID"].";\n";
				$i++;
			}
		}
	}
	?>
		top.arExtra = new Array();
		top.arCatalogGroups = new Array();
		top.BaseIndex = "";
		<?
		$i=0;
		foreach($arCatExtra as $CatExtra)
		{
			echo "top.arExtra[".$CatExtra["ID"]."]=".$CatExtra["PERCENTAGE"].";\n";
			$i++;
		}
		$i=0;
		foreach($arCatGroup as $CatGroup)
		{
			if($CatGroup["BASE"]!="Y")
			{
				echo "top.arCatalogGroups[".$i."]=".$CatGroup["ID"].";\n";
				$i++;
			}
			else
				echo "top.BaseIndex=".$CatGroup["ID"].";\n";
		}
		?>
		top.ChangeBasePrice = function(id)
		{
			for(var i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				var pr = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]");
				if(pr.disabled)
				{
					var price = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.BaseIndex+"]").value;
					if(price > 0)
					{
						var extraId = document.getElementById("CATALOG_EXTRA["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]").value;
						var esum = parseFloat(price) * (1 + top.arExtra[extraId] / 100);
						var eps = 1.00/Math.pow(10, 6);
						esum = Math.round((esum+eps)*100)/100;
					}
					else
						var esum = "";

					pr.value = esum;
				}
			}
		}

		top.ChangeBaseCurrency = function(id)
		{
			var currency = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.BaseIndex+"]");
			for(var i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				var pr = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.arCatalogShowedGroups[i]+"]");
				if(pr.disabled)
				{
					pr.selectedIndex = currency.selectedIndex;
				}
			}
		}
	</script>
	<?
}

$wf_status_id = "";
if($bWorkFlow && (strpos($find_el_status_id, "-") !== false))
{
	$ar = explode("-", $find_el_status_id);
	$wf_status_id = $ar[1];
}

if($wf_status_id)
{
	$rsData = CIBlockElement::GetList(
		Array($by=>$order),
		$arFilter,
		false,
		false,
		$arSelectedFields
	);
	while($arElement = $rsData->Fetch())
	{
		if($wf_status_id!==false)
		{
			$LAST_ID = CIBlockElement::WF_GetLast($arElement['ID']);
			if($LAST_ID!=$arElement['ID'])
			{
				$rsData2 = CIBlockElement::GetList(
						Array(),
						Array(
							"ID"=>$LAST_ID,
							"SHOW_HISTORY"=>"Y"
							),
						false,
						Array("nTopCount"=>1),
						array("ID","WF_STATUS_ID")
					);
				if($arRes = $rsData2->Fetch())
				{
					if($arRes["WF_STATUS_ID"]!=$wf_status_id)
						continue;
				}
			}
			else
				continue;
		}
		$arResult[]=$arElement;
	}
	$rsData = new CDBResult;
	$rsData->InitFromArray($arResult);
	$rsData = new CAdminResult($rsData, $sTableID);
}
else
{
	$rsData = CIBlockElement::GetList(
		Array($by=>$order),
		$arFilter,
		false,
		Array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
		$arSelectedFields
	);
	$rsData->SetTableID($sTableID);
	$wf_status_id = false;
}

$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(htmlspecialchars($arIBlock["ELEMENTS_NAME"])));

function GetElementName($ID)
{
	$ID = IntVal($ID);
	static $cache = array();
	if(!array_key_exists($ID, $cache))
	{
		$rsElement = CIBlockElement::GetList(Array(), Array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false, array("ID","IBLOCK_ID","NAME"));
		$cache[$ID] = $rsElement->GetNext();
	}
	return $cache[$ID];
}
function GetIBlockTypeID($IBLOCK_ID)
{
	$IBLOCK_ID = IntVal($IBLOCK_ID);
	static $cache = array();
	if(!array_key_exists($IBLOCK_ID, $cache))
	{
		$rsIBlock = CIBlock::GetByID($IBLOCK_ID);
		if(!($cache[$ID] = $rsIBlock->GetNext()))
			$cache[$ID] = array("IBLOCK_TYPE_ID"=>"");
	}
	return $cache[$ID]["IBLOCK_TYPE_ID"];
}

while($arRes = $rsData->NavNext(true, "f_"))
{
	$arRes_orig = $arRes;
	// in workflow mode show latest changes
 	if($bWorkFlow)
	{
		$LAST_ID = CIBlockElement::WF_GetLast($arRes['ID']);
		if($LAST_ID!=$arRes['ID'])
		{
			$rsData2 = CIBlockElement::GetList(
					Array(),
					Array(
						"ID"=>$LAST_ID,
						"SHOW_HISTORY"=>"Y"
						),
					false,
					Array("nTopCount"=>1),
					$arSelectedFields
				);
			if(isset($arCatGroup))
			{
				$arRes_tmp = Array();
				foreach($arRes as $vv => $vval)
				{
					if(substr($vv, 0, 8) == "CATALOG_")
						$arRes_tmp[$vv] = $arRes[$vv];
				}
			}

			$arRes = $rsData2->NavNext(true, "f_");
			if(isset($arCatGroup))
				$arRes = array_merge($arRes, $arRes_tmp);

			$f_ID = $arRes_orig["ID"];
		}
		$lockStatus = $arRes_orig['LOCK_STATUS'];
	}
	elseif($bBizproc)
	{
		$lockStatus = CIBlockDocument::IsDocumentLocked($f_ID, "") ? "red" : "green";
	}
	else
	{
		$lockStatus = "";
	}

	$edit_url = 'iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl;
	$row =& $lAdmin->AddRow($f_ID, $arRes, $edit_url, GetMessage("IBEL_A_EDIT"));

	$row->AddViewField("ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBEL_A_EDIT_TITLE").'">'.$f_ID.'</a>');
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME", Array('size'=>'35'));
	$row->AddViewField("NAME", '<div class="iblock_menu_icon_elements"></div><a href="'.$edit_url.'" title="'.GetMessage("IBEL_A_EDIT_TITLE").'">'.$f_NAME.'</a>');
	$row->AddInputField("SORT", Array('size'=>'3'));
	$row->AddInputField("CODE");
	$row->AddInputField("EXTERNAL_ID");
	if(CModule::IncludeModule('search'))
	{
		$row->AddViewField("TAGS", $f_TAGS);
		$row->AddEditField("TAGS", InputTags("FIELDS[".$f_ID."][TAGS]", $arRes["TAGS"], $arIBlock["SITE_ID"]));
	}
	else
	{
		$row->AddInputField("TAGS");
	}
	$row->AddCalendarField("DATE_ACTIVE_FROM");
	$row->AddCalendarField("DATE_ACTIVE_TO");

	if($f_LOCKED_USER_NAME)
		$row->AddViewField("LOCKED_USER_NAME", '<a href="user_edit.php?lang='.LANG.'&ID='.$f_WF_LOCKED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_LOCKED_USER_NAME.'</a>');
	if($f_USER_NAME)
		$row->AddViewField("USER_NAME", '<a href="user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_USER_NAME.'</a>');
	$row->AddViewField("CREATED_USER_NAME", '<a href="user_edit.php?lang='.LANG.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_CREATED_USER_NAME.'</a>');

	if($arWFStatus)
	{
		$row->AddSelectField("WF_STATUS_ID", $arWFStatus);
		if($arRes_orig['WF_NEW']=='Y' || $arRes['WF_STATUS_ID']=='1')
			$row->AddViewField("WF_STATUS_ID", htmlspecialcharsex($arWFStatus[$arRes['WF_STATUS_ID']]));
		else
			$row->AddViewField("WF_STATUS_ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBEL_A_ED_TITLE").'">'.htmlspecialcharsex($arWFStatus[$arRes['WF_STATUS_ID']]).'</a> / <a href="'.'iblock_element_edit.php?ID='.$arRes_orig['ID'].($BlockPerm<'W'?'&view=Y':'').$sThisSectionUrl.'" title="'.GetMessage("IBEL_A_ED2_TITLE").'">'.htmlspecialcharsex($arWFStatus[$arRes_orig['WF_STATUS_ID']]).'</a>');
	}

	if(array_key_exists("PREVIEW_PICTURE", $arSelectedFieldsMap))
	{
		$row->AddViewField("PREVIEW_PICTURE", CFile::ShowFile($arRes['PREVIEW_PICTURE'], 100000, 50, 50, true));
		$row->AddEditField("PREVIEW_PICTURE", CFile::InputFile('FIELDS['.$f_ID.'][PREVIEW_PICTURE]', 30, $arRes['PREVIEW_PICTURE']).'<br clear="all">'.CFile::ShowFile($arRes['PREVIEW_PICTURE'], 100000, 50, 50, true));
	}
	if(array_key_exists("PREVIEW_TEXT", $arSelectedFieldsMap))
	{
		$row->AddViewField("PREVIEW_TEXT", ($arRes["PREVIEW_TEXT_TYPE"]=="text" ? htmlspecialcharsex($arRes["PREVIEW_TEXT"]) : HTMLToTxt($arRes["PREVIEW_TEXT"])));
		$sHTML = '<input type="radio" name="FIELDS['.$f_ID.'][PREVIEW_TEXT_TYPE]" value="text" id="'.$f_ID.'PREVIEWtext"';
		if($arRes["PREVIEW_TEXT_TYPE"]!="html")
			$sHTML .= ' checked';
		$sHTML .= '><label for="'.$f_ID.'PREVIEWtext">text</label> /';
		$sHTML .= '<input type="radio" name="FIELDS['.$f_ID.'][PREVIEW_TEXT_TYPE]" value="html" id="'.$f_ID.'PREVIEWhtml"';
		if($arRes["PREVIEW_TEXT_TYPE"]=="html")
			$sHTML .= ' checked';
		$sHTML .= '><label for="'.$f_ID.'PREVIEWhtml">html</label><br>';
		$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_ID.'][PREVIEW_TEXT]">'.htmlspecialcharsex($arRes["PREVIEW_TEXT"]).'</textarea>';
		$row->AddEditField("PREVIEW_TEXT", $sHTML);
	}
	if(array_key_exists("DETAIL_PICTURE", $arSelectedFieldsMap))
	{
		$row->AddViewField("DETAIL_PICTURE", CFile::ShowFile($arRes['DETAIL_PICTURE'], 100000, 50, 50, true));
		$row->AddEditField("DETAIL_PICTURE", CFile::InputFile('FIELDS['.$f_ID.'][DETAIL_PICTURE]', 30, $arRes['DETAIL_PICTURE']));
	}
	if(array_key_exists("DETAIL_TEXT", $arSelectedFieldsMap))
	{
		$row->AddViewField("DETAIL_TEXT", ($arRes["DETAIL_TEXT_TYPE"]=="text" ? htmlspecialcharsex($arRes["DETAIL_TEXT"]) : HTMLToTxt($arRes["DETAIL_TEXT"])));
		$sHTML = '<input type="radio" name="FIELDS['.$f_ID.'][DETAIL_TEXT_TYPE]" value="text" id="'.$f_ID.'DETAILtext"';
		if($arRes["DETAIL_TEXT_TYPE"]!="html")
			$sHTML .= ' checked';
		$sHTML .= '><label for="'.$f_ID.'DETAILtext">text</label> /';
		$sHTML .= '<input type="radio" name="FIELDS['.$f_ID.'][DETAIL_TEXT_TYPE]" value="html" id="'.$f_ID.'DETAILhtml"';
		if($arRes["DETAIL_TEXT_TYPE"]=="html")
			$sHTML .= ' checked';
		$sHTML .= '><label for="'.$f_ID.'DETAILhtml">html</label><br>';

		$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_ID.'][DETAIL_TEXT]">'.htmlspecialcharsex($arRes["DETAIL_TEXT"]).'</textarea>';
		$row->AddEditField("DETAIL_TEXT", $sHTML);
	}
	if($bWorkFlow || $bBizproc)
	{
		$lamp = "/bitrix/images/workflow/".$lockStatus.".gif";
		if($lockStatus=="green")
			$lamp_alt = GetMessage("IBLOCK_GREEN_ALT");
		elseif($lockStatus=="yellow")
			$lamp_alt = GetMessage("IBLOCK_YELLOW_ALT");
		else
			$lamp_alt = GetMessage("IBLOCK_RED_ALT");


		if($lockStatus=='red' && $arRes_orig['LOCKED_USER_NAME']!='')
			$row->AddViewField("LOCK_STATUS", '<table cellpadding="0" cellspacing="0" border="0"><tr><td><img hspace="4" src="'.$lamp.'" alt="'.htmlspecialchars($lamp_alt).'" title="'.htmlspecialchars($lamp_alt).'" /></td><td>'.$arRes_orig['LOCKED_USER_NAME'].$unlock.'</td></tr></table>');
		else
			$row->AddViewField("LOCK_STATUS", '<img src="'.$lamp.'" hspace="4" alt="'.htmlspecialchars($lamp_alt).'" title="'.htmlspecialchars($lamp_alt).'" />');
	}

	if($bBizproc)
		$row->AddCheckField("BP_PUBLISHED", false);

	$arProperties = array();
	if(count($arSelectedProps) > 0)
	{
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $arRes["ID"]);
		while($ar = $rsProperties->GetNext())
		{
			if(!array_key_exists($ar["ID"], $arProperties))
				$arProperties[$ar["ID"]] = array();
			$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar;
		}
	}

	foreach($arSelectedProps as $aProp)
	{
		$arViewHTML = array();
		$arEditHTML = array();
		if(strlen($aProp["USER_TYPE"])>0)
			$arUserType = CIBlockProperty::GetUserType($aProp["USER_TYPE"]);
		else
			$arUserType = array();
		$max_file_size_show=100000;

		$last_property_id = false;
		foreach($arProperties[$aProp["ID"]] as $prop_id => $prop)
		{
			$prop['PROPERTY_VALUE_ID'] = intval($prop['PROPERTY_VALUE_ID']);
			$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][VALUE]';
			$DESCR_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][DESCRIPTION]';
			//View part
			if(array_key_exists("GetAdminListViewHTML", $arUserType))
			{
				$arViewHTML[] = call_user_func_array($arUserType["GetAdminListViewHTML"],
					array(
						$prop,
						array(
							"VALUE" => $prop["~VALUE"],
							"DESCRIPTION" => $prop["~DESCRIPTION"]
						),
						array(
							"VALUE" => $VALUE_NAME,
							"DESCRIPTION" => $DESCR_NAME,
							"MODE"=>"iblock_element_admin",
							"FORM_NAME"=>"form_".$sTableID,
						),
					));
			}
			elseif($prop['PROPERTY_TYPE']=='N')
				$arViewHTML[] = $prop["VALUE"];
			elseif($prop['PROPERTY_TYPE']=='S')
				$arViewHTML[] = $prop["VALUE"];
			elseif($prop['PROPERTY_TYPE']=='L')
				$arViewHTML[] = $prop["VALUE_ENUM"];
			elseif($prop['PROPERTY_TYPE']=='F')
			{
				$arViewHTML[] = CFile::ShowFile($prop["VALUE"], 100000, 50, 50, true);
			}
			elseif($prop['PROPERTY_TYPE']=='G')
			{
				if(intval($prop["VALUE"])>0)
				{
					$rsSection = CIBlockSection::GetList(Array(), Array("ID" => $prop["VALUE"]));
					if($arSection = $rsSection->GetNext())
					{
						$arViewHTML[] = $arSection['NAME'].
						' [<a href="'.
						'iblock_section_edit.php?'.
						'type='.GetIBlockTypeID($arSection['IBLOCK_ID']).
						'&amp;IBLOCK_ID='.$arSection['IBLOCK_ID'].
						'&amp;ID='.$arSection['ID'].
						'&amp;lang='.$lang.
						'" title="'.GetMessage("IBEL_A_SEC_EDIT").'">'.$arSection['ID'].'</a>]';
					}
				}
			}
			elseif($prop['PROPERTY_TYPE']=='E')
			{
				if($t = GetElementName($prop["VALUE"]))
				{
					$arViewHTML[] = $t['NAME'].
					' [<a href="'.
					'iblock_element_edit.php'.
					'?WF=Y'.
					'&type='.GetIBlockTypeID($t['IBLOCK_ID']).
					'&amp;IBLOCK_ID='.$t['IBLOCK_ID'].
					'&amp;ID='.$t['ID'].
					'&amp;lang='.$lang.
					'" title="'.GetMessage("IBEL_A_EL_EDIT").'">'.$t['ID'].'</a>]';
				}
			}
			//Edit Part
			$bUserMultiple = $prop["MULTIPLE"] == "Y" &&  array_key_exists("GetPropertyFieldHtmlMulty", $arUserType);
			if($bUserMultiple)
			{
				if($last_property_id != $prop["ID"])
				{
					$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']';
					$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtmlMulty"], array(
						$prop,
						$arProperties[$prop["ID"]],
						array(
							"VALUE" => $VALUE_NAME,
							"MODE"=>"iblock_element_admin",
							"FORM_NAME"=>"form_".$sTableID,
						)
					));
				}
			}
			elseif(array_key_exists("GetPropertyFieldHtml", $arUserType))
			{
				$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$prop,
						array(
							"VALUE" => $prop["VALUE"],
							"DESCRIPTION" => $prop["DESCRIPTION"],
						),
						array(
							"VALUE" => $VALUE_NAME,
							"DESCRIPTION" => $DESCR_NAME,
							"MODE"=>"iblock_element_admin",
							"FORM_NAME"=>"form_".$sTableID,
						),
					));
			}
			elseif($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
			{
				if($prop["ROW_COUNT"] > 1)
					$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'">'.$prop["VALUE"].'</textarea>';
				else
					$html = '<input type="text" name="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="'.$prop["COL_COUNT"].'">';
				if($prop["WITH_DESCRIPTION"] == "Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").
						'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='L' && ($last_property_id!=$prop["ID"]))
			{
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
				$arValues = array();
				foreach($arProperties[$prop["ID"]] as $g_prop)
				{
					$g_prop = intval($g_prop["VALUE"]);
					if($g_prop > 0)
						$arValues[$g_prop] = $g_prop;
				}
				if($prop['LIST_TYPE']=='C')
				{
					if($prop['MULTIPLE'] == "Y" || count($arSelect[$prop['ID']]) == 1)
					{
						$html = '<input type="hidden" name="'.$VALUE_NAME.'" value="">';
						foreach($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<input type="checkbox" name="'.$VALUE_NAME.'" id="'.$prop["PROPERTY_VALUE_ID"]."_".$value.'" value="'.$value.'"';
							if(array_key_exists($value, $arValues))
								$html .= ' checked';
							$html .= '>&nbsp;<label for="'.$prop["PROPERTY_VALUE_ID"]."_".$value.'">'.$display.'</label><br>';
						}
					}
					else
					{
						$html = '<input type="radio" name="'.$VALUE_NAME.'" id="'.$prop["PROPERTY_VALUE_ID"].'_none" value=""';
						if(count($arValues) < 1)
							$html .= ' checked';
						$html .= '>&nbsp;<label for="'.$prop["PROPERTY_VALUE_ID"].'_none">'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</label><br>';
						foreach($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<input type="radio" name="'.$VALUE_NAME.'" id="'.$prop["PROPERTY_VALUE_ID"]."_".$value.'" value="'.$value.'"';
							if(array_key_exists($value, $arValues))
								$html .= ' checked';
							$html .= '>&nbsp;<label for="'.$prop["PROPERTY_VALUE_ID"]."_".$value.'">'.$display.'</label><br>';
						}
					}
				}
				else
				{
					$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
					$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
					foreach($arSelect[$prop['ID']] as $value => $display)
					{
						$html .= '<option value="'.$value.'"';
						if(array_key_exists($value, $arValues))
							$html .= ' selected';
						$html .= '>'.$display.'</option>'."\n";
					}
					$html .= "</select>\n";
				}
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='F')
			{
				$html = CFile::InputFile($VALUE_NAME, $prop["COL_COUNT"], $prop["VALUE"], false, 0, "").
					"<br>".
					CFile::ShowFile($prop["VALUE"], $max_file_size_show, 400, 400, true).
					"<br>";
				if($prop["WITH_DESCRIPTION"]=="Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
				else
					$html .= '<input type="hidden" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'">';
				$arEditHTML[] = $html;
			}
			elseif(($prop['PROPERTY_TYPE']=='G') && ($last_property_id!=$prop["ID"]))
			{
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
				$arValues = array();
				foreach($arProperties[$prop["ID"]] as $g_prop)
				{
					$g_prop = intval($g_prop["VALUE"]);
					if($g_prop > 0)
						$arValues[$g_prop] = $g_prop;
				}
				$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
				$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
				foreach($arSelect[$prop['ID']] as $value => $display)
				{
					$html .= '<option value="'.$value.'"';
					if(array_key_exists($value, $arValues))
						$html .= ' selected';
					$html .= '>'.$display.'</option>'."\n";
				}
				$html .= "</select>\n";
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='E')
			{
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].']';
				if($t = GetElementName($prop["VALUE"]))
				{
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).'\', 600, 500);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$t['NAME'].'</span>';
				}
				else
				{
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).'\', 600, 500);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
				}
			}
			$last_property_id = $prop['ID'];
		}
		$table_id = md5($f_ID.':'.$aProp['ID']);
		if($aProp["MULTIPLE"] == "Y")
		{
			$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][VALUE]';
			$DESCR_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][DESCRIPTION]';
			if(array_key_exists("GetPropertyFieldHtmlMulty", $arUserType))
			{
			}
			elseif(array_key_exists("GetPropertyFieldHtml", $arUserType))
			{
				$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$prop,
						array(
							"VALUE" => "",
							"DESCRIPTION" => "",
						),
						array(
							"VALUE" => $VALUE_NAME,
							"DESCRIPTION" => $DESCR_NAME,
							"MODE"=>"iblock_element_admin",
							"FORM_NAME"=>"form_".$sTableID,
						),
					));
			}
			elseif($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
			{
				if($prop["ROW_COUNT"] > 1)
					$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'"></textarea>';
				else
					$html = '<input type="text" name="'.$VALUE_NAME.'" value="" size="'.$prop["COL_COUNT"].'">';
				if($prop["WITH_DESCRIPTION"] == "Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='F')
			{
				$html = CFile::InputFile($VALUE_NAME, $prop["COL_COUNT"], "", false, 0, "").
					"<br>".
					CFile::ShowFile("", $max_file_size_show, 400, 400, true).
					"<br>";
				if($prop["WITH_DESCRIPTION"]=="Y")
					$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
				else
					$html .= '<input type="hidden" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'">';
				$arEditHTML[] = $html;
			}
			elseif($prop['PROPERTY_TYPE']=='E')
			{
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0]';
				$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
					'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).'\', 600, 500);">'.
					'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
			}

			if($prop["PROPERTY_TYPE"]!=="G" && $prop["PROPERTY_TYPE"]!=="L" && !$bUserMultiple)
				$arEditHTML[] = '<input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.$table_id.'\')">';
		}
		if(count($arViewHTML) > 0)
			$row->AddViewField("PROPERTY_".$aProp['ID'], implode(" / ", $arViewHTML)."&nbsp;");
		if(count($arEditHTML) > 0)
			$row->AddEditField("PROPERTY_".$aProp['ID'], '<table id="tb'.$table_id.'" border=0 cellpadding=0 cellspacing=0><tr><td nowrap>'.implode("</td></tr><tr><td nowrap>", $arEditHTML).'</td></tr></table>');
	}

	$row->AddInputField("CATALOG_QUANTITY");
	$row->AddCheckField("CATALOG_QUANTITY_TRACE");
	if(isset($arCatGroup))
	{
		foreach($arCatGroup as $CatGroup)
		{
			$price = "";
			$sHTML = "";
			$selectCur = "";
			if(CModule::IncludeModule("currency"))
			{
				$price = CurrencyFormat($arRes["CATALOG_PRICE_".$CatGroup["ID"]],$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]]);
				if($USER->CanDoOperation('catalog_price'))
				{
					$db_curr = CCurrency::GetList(($by1="sort"), ($order1="asc"));
					$selectCur = '<select name="CATALOG_CURRENCY['.$arRes["ID"].']['.$CatGroup["ID"].']" id="CATALOG_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']"';
					if(IntVal($arRes["CATALOG_EXTRA_ID_".$CatGroup["ID"]])>0)
						$selectCur .= ' disabled="disabled" readonly="readonly"';
					if($CatGroup["BASE"]=="Y")
						$selectCur .= ' OnChange="ChangeBaseCurrency('.$f_ID.')"';
					$selectCur .= '>';
					while ($curr = $db_curr->Fetch())
					{
						$selectCur .= '<option value="'.htmlspecialcharsex($curr["CURRENCY"]).'"';
						if($curr["CURRENCY"]==$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]])
							$selectCur .= ' selected';
						$selectCur .= '>'.htmlspecialcharsex($curr["CURRENCY"]).'</option>';
					}
					$selectCur .= '</select>';
				}
			}
			else
				$price = $arRes["CATALOG_PRICE_".$CatGroup["ID"]]." ".$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]];

				$row->AddViewField("CATALOG_GROUP_".$CatGroup["ID"], $price);
			if($USER->CanDoOperation('catalog_price'))
			{
				$sHTML = '<input type="text" size="5" id="CATALOG_PRICE['.$f_ID.']['.$CatGroup["ID"].']" name="CATALOG_PRICE['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_".$CatGroup["ID"]].'"';
				if($CatGroup["BASE"]=="Y")
					$sHTML .= ' OnChange="ChangeBasePrice('.$f_ID.')"';
				if(IntVal($arRes["CATALOG_EXTRA_ID_".$CatGroup["ID"]])>0)
					$sHTML .= ' disabled readonly';
				$sHTML .= '> '.$selectCur;
				if(IntVal($arRes["CATALOG_EXTRA_ID_".$CatGroup["ID"]])>0)
					$sHTML .= '<input type="hidden" id="CATALOG_EXTRA['.$f_ID.']['.$CatGroup["ID"].']" name="CATALOG_EXTRA['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_EXTRA_ID_".$CatGroup["ID"]].'">';

				$sHTML .= '<input type="hidden" name="CATALOG_old_PRICE['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_".$CatGroup["ID"]].'">';
				$sHTML .= '<input type="hidden" name="CATALOG_old_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]].'">';
				$sHTML .= '<input type="hidden" name="CATALOG_PRICE_ID['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_ID_".$CatGroup["ID"]].'">';
				$sHTML .= '<input type="hidden" name="CATALOG_QUANTITY_FROM['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_QUANTITY_FROM_".$CatGroup["ID"]].'">';
				$sHTML .= '<input type="hidden" name="CATALOG_QUANTITY_TO['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_QUANTITY_TO_".$CatGroup["ID"]].'">';

				$row->AddEditField("CATALOG_GROUP_".$CatGroup["ID"], $sHTML);
			}
		}
	}

	if ($bBizproc)
	{
		$arDocumentStates = CBPDocument::GetDocumentStates(
			array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID),
			array("iblock", "CIBlockDocument", $f_ID)
		);

		$arRes["CURENT_USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();
		if ($arRes["CREATED_BY"] == $GLOBALS["USER"]->GetID())
			$arRes["CURENT_USER_GROUPS"][] = "Author";

		$arStr = array();
		$arStr1 = array();
		foreach ($arDocumentStates as $kk => $vv)
		{
			$canViewWorkflow = CIBlockDocument::CanUserOperateDocument(
				IBLOCK_DOCUMENT_OPERATION_VIEW_WORKFLOW,
				$GLOBALS["USER"]->GetID(),
				$f_ID,
				array("IBlockPermission" => $BlockPerm, "AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates, "WorkflowId" => $kk)
			);
			if (!$canViewWorkflow)
				continue;

			$arStr1[$vv["TEMPLATE_ID"]] = $vv["TEMPLATE_NAME"];
			$arStr[$vv["TEMPLATE_ID"]] .= "<a href=\"bizproc_log.php?ID=".$kk."\">".(strlen($vv["STATE_TITLE"]) > 0 ? $vv["STATE_TITLE"] : $vv["STATE_NAME"])."</a><br />";

			if (strlen($vv["ID"]) > 0)
			{
				$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $vv["ID"]);
				foreach ($arTasks as $arTask)
				{
					$arStr[$vv["TEMPLATE_ID"]] .= GetMessage("IBEL_A_BP_TASK").":<br /><a href=\"bizproc_task.php?id=".$arTask["ID"]."\" title=\"".$arTask["DESCRIPTION"]."\">".$arTask["NAME"]."</a><br /><br />";
				}
			}
		}

		$str = "";
		foreach ($arStr as $k => $v)
		{
			$row->AddViewField("WF_".$k, $v);
			$str .= "<b>".(strlen($arStr1[$k]) > 0 ? $arStr1[$k] : GetMessage("IBEL_A_BP_PROC"))."</b>:<br />".$v."<br />";
		}

		$row->AddViewField("BIZPROC", $str);
	}

	$arActions = Array();


	if($bWorkFlow)
	{
		$STATUS_PERMISSION = 2;
		if($arRes["WF_STATUS_ID"]>1)
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]);

		$arUnLock = Array(
				"ICON"=>"unlock",
				"TEXT"=>GetMessage("IBEL_A_UNLOCK"),
				"TITLE"=>GetMessage("IBLOCK_UNLOCK_ALT"),
				"ACTION"=>"if(confirm('".GetMessage("IBLOCK_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($arRes_orig['ID'], "unlock", $sThisSectionUrl)
			);

		if($arRes_orig['LOCK_STATUS']=="red" && CWorkflow::IsAdmin())
		{
			$arActions[] = $arUnLock;
		}
		elseif($STATUS_PERMISSION>=2 || $BlockPerm>="W")
		{
			if($arRes_orig['LOCK_STATUS']=="yellow")
			{
				$arActions[] = $arUnLock;
				$arActions[] = array("SEPARATOR"=>true);
			}

			if($arRes_orig['WF_NEW']=="Y") // not published, under workflow
			{
				$arActions[] = array(
					"ICON"=>"edit",
					"TEXT"=>GetMessage("IBEL_A_CHANGE"),
					"DEFAULT"=>true,
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				$arActions[] = array(
					"ICON"=>"copy",
					"TEXT"=>GetMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl."&action=copy")
				);

				$arActions[] = array(
					"ICON"=>"history",
					"TEXT"=>GetMessage("IBEL_A_HIST"),
					"TITLE"=>GetMessage("IBLOCK_HISTORY_ALT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_history_list.php?ELEMENT_ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				if(strlen($f_DETAIL_PAGE_URL)>0)
				{
					$tmpVar = CIBlock::ReplaceDetailUrl($arRes_orig["DETAIL_PAGE_URL"], $arRes_orig, true, "E");
					$arActions[] = array(
						"ICON"=>"view",
						"TEXT"=>GetMessage("IBLOCK_EL_ADMIN_VIEW_WF"),
						"TITLE"=>GetMessage("IBEL_A_ORIG"),
						"ACTION"=>$lAdmin->ActionRedirect(htmlspecialchars($tmpVar).((strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y")
					);
				}

				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"ICON"=>"delete",
					"TEXT"=>GetMessage('MAIN_DELETE'),
					"TITLE"=>GetMessage("IBLOCK_DELETE_ALT"),
					"ACTION"=>"if(confirm('".GetMessage('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($arRes_orig['ID'], "delete", $sThisSectionUrl)
				);
			}
			elseif($arRes["WF_STATUS_ID"]>1) // published but changed
			{
				$arActions[] = array(
					"ICON"=>"edit",
					"DEFAULT"=>true,
					"TEXT"=>GetMessage("IBEL_A_CHANGE"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				$arActions[] = array(
					"ICON"=>"copy",
					"TEXT"=>GetMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl."&action=copy")
				);

				$arActions[] = array(
					"ICON"=>"history",
					"TEXT"=>GetMessage("IBEL_A_HIST"),
					"TITLE"=>GetMessage("IBLOCK_HISTORY_ALT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_history_list.php?ELEMENT_ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				if($BlockPerm>="W")
				{
					$arActions[] = array("SEPARATOR"=>true);

					if(strlen($f_DETAIL_PAGE_URL)>0)
					{
						$tmpVar = CIBlock::ReplaceDetailUrl($arRes_orig["DETAIL_PAGE_URL"], $arRes_orig, true, 'E');

						$arActions[] = array(
							"ICON"=>"view",
							"TEXT"=>GetMessage("IBLOCK_EL_ADMIN_VIEW"),
							"TITLE"=>GetMessage("IBEL_A_ORIG"),
							"ACTION"=>$lAdmin->ActionRedirect(htmlspecialchars($tmpVar))
						);

						$arActions[] = array(
							"ICON"=>"view",
							"TEXT"=>GetMessage("IBLOCK_EL_ADMIN_VIEW_WF"),
							"TITLE"=>GetMessage("IBEL_A_ORIG"),
							"ACTION"=>$lAdmin->ActionRedirect(htmlspecialchars($tmpVar).((strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y")
						);
					}

					$arActions[] = array(
						"ICON"=>"edit_orig",
						"TEXT"=>GetMessage("IBEL_A_ORIG_ED"),
						"TITLE"=>GetMessage("IBEL_A_ORIG_ED_TITLE"),
						"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?ID='.$arRes_orig['ID'].($BlockPerm<'W'?'&view=Y':'').$sThisSectionUrl)
					);

					$arActions[] = array(
						"ICON"=>"delete",
						"TEXT"=>GetMessage('MAIN_DELETE'),
						"TITLE"=>GetMessage("IBLOCK_DELETE_ALT"),
						"ACTION"=>"if(confirm('".GetMessage('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($arRes_orig['ID'], "delete", $sThisSectionUrl)
					);
				}
			}
			else //published
			{
				$arActions[] = array(
					"ICON"=>"edit",
					"DEFAULT"=>true,
					"TEXT"=>GetMessage("IBEL_A_CHANGE"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				$arActions[] = array(
					"ICON"=>"copy",
					"TEXT"=>GetMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$arRes_orig['ID'].$sThisSectionUrl."&action=copy")
				);

				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"ICON"=>"history",
					"TEXT"=>GetMessage("IBEL_A_HIST"),
					"TITLE"=>GetMessage("IBLOCK_HISTORY_ALT"),
					"ACTION"=>$lAdmin->ActionRedirect('iblock_history_list.php?ELEMENT_ID='.$arRes_orig['ID'].$sThisSectionUrl)
				);

				if(strlen($f_DETAIL_PAGE_URL) > 0)
				{
					$tmpVar = CIBlock::ReplaceDetailUrl($arRes_orig["DETAIL_PAGE_URL"], $arRes_orig, true, "E");

					$arActions[] = array(
						"ICON"=>"view",
						"TEXT"=>GetMessage("IBLOCK_EL_ADMIN_VIEW"),
						"TITLE"=>GetMessage("IBEL_A_ORIG"),
						"ACTION"=>$lAdmin->ActionRedirect(htmlspecialchars($tmpVar))
					);
				}

				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"ICON"=>"delete",
					"TEXT"=>GetMessage('MAIN_DELETE'),
					"TITLE"=>GetMessage("IBLOCK_DELETE_ALT"),
					"ACTION"=>"if(confirm('".GetMessage('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($arRes_orig['ID'], "delete", $sThisSectionUrl)
				);
			}
		} //if($STATUS_PERMISSION>=2)
	}
	elseif($bBizproc)
	{
		$bWritePermission = CIBlockDocument::CanUserOperateDocument(
			IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
			$USER->GetID(),
			$f_ID,
			array("IBlockId" => $IBLOCK_ID, "IBlockPermission" => $BlockPerm, "UserGroups" => $USER->GetUserGroupArray(), "AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates)
		);

		$bStartWorkflowPermission = CIBlockDocument::CanUserOperateDocument(
			IBLOCK_DOCUMENT_OPERATION_START_WORKFLOW,
			$USER->GetID(),
			$f_ID,
			array("IBlockId" => $IBLOCK_ID, "IBlockPermission" => $BlockPerm, "UserGroups" => $USER->GetUserGroupArray(), "AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates)
		);

		if ($bStartWorkflowPermission)
		{
			$arActions[] = array(
				"ICON" => "",
				"TEXT" => GetMessage("IBEL_A_BP_RUN"),
				"ACTION" => $lAdmin->ActionRedirect('iblock_start_bizproc.php?document_id='.$f_ID.'&document_type=iblock_'.$IBLOCK_ID.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array("mode", "table_id"))).''),
			);
		}

		if ($lockStatus == "red")
		{
			if (CBPDocument::IsAdmin())
			{
				$arActions[] = Array(
					"ICON" => "unlock",
					"TEXT" => GetMessage("IBEL_A_UNLOCK"),
					"TITLE" => GetMessage("IBEL_A_UNLOCK_ALT"),
					"ACTION" => "if(confirm('".GetMessage("IBEL_A_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "unlock", $sThisSectionUrl),
				);
			}
		}
		elseif ($bWritePermission)
		{
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => GetMessage("IBEL_A_CHANGE"),
				"DEFAULT" => true,
				"ACTION" => $lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$f_ID.$sThisSectionUrl),
			);

			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
				"ACTION" => $lAdmin->ActionRedirect('iblock_element_edit.php?WF=Y&ID='.$f_ID.$sThisSectionUrl."&action=copy"),
			);

			$arActions[] = array(
				"ICON" => "history",
				"TEXT" => GetMessage("IBEL_A_HIST"),
				"TITLE" => GetMessage("IBEL_A_HISTORY_ALT"),
				"ACTION" => $lAdmin->ActionRedirect('iblock_bizproc_history.php?document_id='.$f_ID.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array())).''),
			);

			$arActions[] = array("SEPARATOR"=>true);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage('MAIN_DELETE'),
				"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
				"ACTION" => "if(confirm('".GetMessage('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "delete", $sThisSectionUrl),
			);
		}
	}
	else
	{
		$arActions[] = array(
			"ICON"=>"edit",
			"TEXT"=>GetMessage("IBEL_A_CHANGE"),
			"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?ID='.$arRes_orig['ID'].$sThisSectionUrl)
		);

		$arActions[] = array(
			"ICON"=>"copy",
			"TEXT"=>GetMessage("IBEL_A_COPY_ELEMENT"),
			"ACTION"=>$lAdmin->ActionRedirect('iblock_element_edit.php?ID='.$arRes_orig['ID'].$sThisSectionUrl."&action=copy")
		);

		if(strlen($f_DETAIL_PAGE_URL) > 0)
		{
			$tmpVar = CIBlock::ReplaceDetailUrl($arRes_orig["DETAIL_PAGE_URL"], $arRes_orig, true, "E");
			$arActions[] = array(
				"ICON"=>"view",
				"TEXT"=>GetMessage("IBLOCK_EL_ADMIN_VIEW"),
				"TITLE"=>GetMessage("IBEL_A_ORIG"),
				"ACTION"=>$lAdmin->ActionRedirect(htmlspecialchars($tmpVar))
			);
		}

		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage('MAIN_DELETE'),
			"TITLE"=>GetMessage("IBLOCK_DELETE_ALT"),
			"ACTION"=>"if(confirm('".GetMessage('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($arRes_orig['ID'], "delete", $sThisSectionUrl)
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$arActions = array(
	"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
);

if($arIBTYPE["SECTIONS"] == "Y")
{
	$sections = '&nbsp;<select name="section_to_move" size="1">';
	$sections .= '<option value="-1">'.GetMessage("MAIN_NO").'</option>';
	$sections .= '<option value="0">'.GetMessage("IBLOCK_UPPER_LEVEL").'</option>';
	$rsSections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
	while($ar = $rsSections->GetNext())
	{
		$sections .= '<option value="'.$ar["ID"].'">'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
	}
	$sections .= '</select>&nbsp;';

	$arActions["section"] = GetMessage("IBEL_A_MOVE_TO_SECTION");
	$arActions["add_section"] = GetMessage("IBEL_A_ADD_TO_SECTION");
	$arActions["section_chooser"] = array("type" => "html", "value" => $sections);
}

if($bWorkFlow)
{
	$arActions["unlock"] = GetMessage("IBEL_A_UNLOCK_ACTION");
	$arActions["lock"] = GetMessage("IBEL_A_LOCK_ACTION");
}
elseif($bBizproc)
{
	$arActions["unlock"] = GetMessage("IBEL_A_UNLOCK_ACTION");
}
$lAdmin->AddGroupActionTable($arActions);

$chain = $lAdmin->CreateChain();
if($arIBTYPE["SECTIONS"]=="Y")
{
	$chain->AddItem(array(
		"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
		"LINK" => htmlspecialchars(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0))),
	));

	$sLastFolder = '';
	if($find_section_section > 0)
	{
		$sLastFolder = htmlspecialchars(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0)));
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
		while($ar_nav = $nav->GetNext())
		{
			$sLastFolder = htmlspecialchars(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"])));
			$chain->AddItem(array(
				"TEXT" => $ar_nav["NAME"],
				"LINK" => $sLastFolder,
			));
		}
	}
}

$lAdmin->ShowChain($chain);

$aContext = array(
	array(
		"ICON"=>"btn_new",
		"TEXT"=>htmlspecialchars($arIBlock["ELEMENT_ADD"]),
		"LINK"=>'iblock_element_edit.php?IBLOCK_SECTION_ID='.urlencode($find_section_section).$sThisSectionUrl,
		"TITLE"=>GetMessage("IBEL_A_ADDEL_TITLE")
	),
);

if(strlen($sLastFolder)>0)
{
	$aContext[] = Array(
		"ICON"=>"btn_up",
		"TEXT"=>GetMessage("IBEL_A_UP"),
		"LINK"=>$sLastFolder,
		"TITLE"=>GetMessage("IBEL_A_UP_TITLE")
		);
}

if($bBizproc && IsModuleInstalled("bizprocdesigner"))
{
	$bCanDoIt = CBPDocument::CanUserOperateDocumentType(
		IBLOCK_DOCUMENT_OPERATION_CREATE_WORKFLOW,
		$GLOBALS["USER"]->GetID(),
		array("iblock", "CIBlockDocument", "iblock_".$IBLOCK_ID)
		);

	if($bCanDoIt)
	{
		$aContext[] = array(
			"TEXT" => GetMessage("IBEL_BTN_BP"),
			"ICON" => "btn_bp",
			"LINK" => 'iblock_bizproc_workflow_admin.php?document_type=iblock_'.$IBLOCK_ID.'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($REQUEST_URI),
		);
	}
}


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENTS_NAME"]);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

echo $CAdminCalendar_ShowScript;
?>
<form method="GET" name="find_form" id="find_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array();
$arFindFields["IBEL_A_F_ID"] = GetMessage("IBEL_A_F_ID");

if($arIBTYPE["SECTIONS"]=="Y")
	$arFindFields["IBEL_A_F_PARENT"] = GetMessage("IBEL_A_F_PARENT");

$arFindFields["IBEL_A_F_MODIFIED_WHEN"] = GetMessage("IBEL_A_F_MODIFIED_WHEN");
$arFindFields["IBEL_A_F_MODIFIED_BY"] = GetMessage("IBEL_A_F_MODIFIED_BY");
$arFindFields["IBEL_A_F_CREATED_WHEN"] = GetMessage("IBEL_A_F_CREATED_WHEN");
$arFindFields["IBEL_A_F_CREATED_BY"] = GetMessage("IBEL_A_F_CREATED_BY");

if($bWorkFlow)
	$arFindFields["IBEL_A_F_STATUS"] = GetMessage("IBEL_A_F_STATUS");

$arFindFields["IBEL_A_F_ACTIVE_FROM"] = GetMessage("IBEL_A_ACTFROM");
$arFindFields["IBEL_A_F_ACTIVE_TO"] = GetMessage("IBEL_A_ACTTO");
$arFindFields["IBEL_A_F_ACT"] = GetMessage("IBEL_A_F_ACT");
$arFindFields["IBEL_A_F_NAME"] = GetMessage("IBEL_A_F_NAME");
$arFindFields["IBEL_A_F_DESC"] = GetMessage("IBEL_A_F_DESC");
$arFindFields["IBEL_A_CODE"] = GetMessage("IBEL_A_CODE");
$arFindFields["IBEL_A_EXTERNAL_ID"] = GetMessage("IBEL_A_EXTERNAL_ID");
$arFindFields["IBEL_A_TAGS"] = GetMessage("IBEL_A_TAGS");

foreach($arProps as $arProp)
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F")
		$arFindFields["IBEL_A_PROP_".$arProp["ID"]] = $arProp["NAME"];

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
$oFilter->Begin();
?>
	<tr>
		<td><b><?=GetMessage("MAIN_ADMIN_LIST_FILTER_1ST_NAME")?></b></td>
		<td><input type="text" name="find_el" title="<?=GetMessage("MAIN_ADMIN_LIST_FILTER_1ST")?>" value="<?echo htmlspecialcharsex($find_el)?>" size="30">
			<select name="find_el_type">
				<option value="name"<?if($find_el_type=="name") echo " selected"?>><?echo GetMessage("IBEL_A_F_NAME")?></option>
				<option value="desc"<?if($find_el_type=="desc") echo " selected"?>><?echo GetMessage("IBEL_A_F_DESC")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_FILTER_FROMTO_ID")?></td>
		<td nowrap>
			<input type="text" name="find_el_id_start" size="10" value="<?echo htmlspecialcharsex($find_el_id_start)?>">
			...
			<input type="text" name="find_el_id_end" size="10" value="<?echo htmlspecialcharsex($find_el_id_end)?>">
		</td>
	</tr>

	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td><?echo GetMessage("IBLOCK_FIELD_SECTION_ID")?>:</td>
		<td>
			<select name="find_section_section">
				<option value="-1"><?echo GetMessage("IBLOCK_VALUE_ANY")?></option>
				<option value="0"<?if($find_section_section=="0")echo" selected"?>><?echo GetMessage("IBLOCK_UPPER_LEVEL")?></option>
				<?
				$bsections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
				while($ar = $bsections->GetNext()):
					?><option value="<?echo $ar["ID"]?>"<?if($ar["ID"]==$find_section_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $ar["DEPTH_LEVEL"])?><?echo $ar["NAME"]?></option><?
				endwhile;
				?>
			</select><br>
			<input type="checkbox" name="find_el_subsections" value="Y"<?if($find_el_subsections=="Y")echo" checked"?>> <?echo GetMessage("IBLOCK_INCLUDING_SUBSECTIONS")?>
		</td>
	</tr>
	<?endif?>

	<tr>
		<td><?echo GetMessage("IBLOCK_FIELD_TIMESTAMP_X")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_el_timestamp_from", htmlspecialcharsex($find_el_timestamp_from), "find_el_timestamp_to", htmlspecialcharsex($find_el_timestamp_to), "find_form")?></font></td>
	</tr>

	<tr>
		<td><?=GetMessage("IBLOCK_FIELD_MODIFIED_BY")?>:</td>
		<td><input type="text" name="find_el_modified_user_id" value="<?echo htmlspecialcharsex($find_el_modified_user_id)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
			$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_el_modified_by">
		<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_el_modified_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsex($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_EL_ADMIN_DCREATE")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_el_created_from", htmlspecialcharsex($find_el_created_from), "find_el_created_to", htmlspecialcharsex($find_el_created_to), "find_form")?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_EL_ADMIN_WCREATE")?></td>
		<td><input type="text" name="find_el_created_user_id" value="<?echo htmlspecialcharsex($find_el_created_user_id)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
		$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_el_created_by">
		<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_el_created_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsex($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>

	<?if($bWorkFlow):?>
	<tr>
		<td><?=GetMessage("IBLOCK_FIELD_STATUS")?></td>
		<td><input type="text" name="find_el_status_id" value="<?echo htmlspecialcharsex($find_el_status_id)?>" size="3">
		<select name="find_el_status">
		<option value=""><?=GetMessage("IBLOCK_VALUE_ANY")?></option>
		<?
		$rs = CWorkflowStatus::GetDropDownList("Y");
		while($arRs = $rs->GetNext())
		{
			?><option value="<?=$arRs["REFERENCE_ID"]?>"<?if($find_el_status == $arRs["~REFERENCE_ID"])echo " selected"?>><?=$arRs["REFERENCE"]?></option><?
		}
		?>
		</select></td>
	</tr>
	<?endif?>

	<tr>
		<td><?echo GetMessage("IBEL_A_ACTFROM")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_el_date_active_from_from", htmlspecialcharsex($find_el_date_active_from_from), "find_el_date_active_from_to", htmlspecialcharsex($find_el_date_active_from_to), "find_form")?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBEL_A_ACTTO")." (".CLang::GetDateFormat("SHORT")."):"?></td>
		<td><?echo CalendarPeriod("find_el_date_active_to_from", htmlspecialcharsex($find_el_date_active_to_from), "find_el_date_active_to_to", htmlspecialcharsex($find_el_date_active_to_to), "find_form")?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_FIELD_ACTIVE")?>:</td>
		<td>
			<select name="find_el_active">
				<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_VALUE_ANY'))?></option>
				<option value="Y"<?if($find_el_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_el_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_FIELD_NAME")?>:</td>
		<td><input type="text" name="find_el_name" value="<?echo htmlspecialcharsex($find_el_name)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_EL_ADMIN_DESC")?></td>
		<td><input type="text" name="find_el_intext" value="<?echo htmlspecialcharsex($find_el_intext)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>

	<tr>
		<td><?=GetMessage("IBEL_A_CODE")?>:</td>
		<td><input type="text" name="find_el_code" value="<?echo htmlspecialcharsex($find_el_code)?>" size="30"></td>
	</tr>
	<tr>
		<td><?=GetMessage("IBEL_A_EXTERNAL_ID")?>:</td>
		<td><input type="text" name="find_el_external_id" value="<?echo htmlspecialcharsex($find_el_external_id)?>" size="30"></td>
	</tr>
	<tr>
		<td><?=GetMessage("IBEL_A_TAGS")?>:</td>
		<td>
			<?if(CModule::IncludeModule('search')):
				echo InputTags("find_el_tags", $find_el_tags, $arIBlock["SITE_ID"]);
			else:
			?>
				<input type="text" name="find_el_tags" value="<?echo htmlspecialcharsex($find_el_tags)?>" size="30">
			<?endif?>
		</td>
	</tr>

	<?

function _ShowGroupPropertyField($name, $property_fields, $values)
{
	if(!is_array($values)) $values = Array();

	$res = "";
	$result = "";
	$bWas = false;
	$sections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]));
	while($ar = $sections->GetNext())
	{
		$res .= '<option value="'.$ar["ID"].'"';
		if(in_array($ar["ID"], $values))
		{
			$bWas = true;
			$res .= ' selected';
		}
		$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
	}
	$result .= '<select name="'.$name.'[]" size="'.($property_fields["MULTIPLE"]=="Y" ? "5":"1").'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
	$result .= '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
	$result .= $res;
	$result .= '</select>';
	return $result;
}

foreach($arProps as $arProp):
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F"):
?>
<tr>
	<td><?=$arProp["NAME"]?>:</td>
	<td>
		<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
			echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
				$arProp,
				array("VALUE" => "find_el_property_".$arProp["ID"]),
			));
		elseif($arProp["PROPERTY_TYPE"]=='S'):?>
			<input type="text" name="find_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_el_property_".$arProp["ID"]})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		<?elseif($arProp["PROPERTY_TYPE"]=='N' || $arProp["PROPERTY_TYPE"]=='E'):?>
			<input type="text" name="find_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_el_property_".$arProp["ID"]})?>" size="30">
		<?elseif($arProp["PROPERTY_TYPE"]=='L'):?>
			<select name="find_el_property_<?=$arProp["ID"]?>">
				<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option>
				<option value="NOT_REF"><?echo GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET")?></option><?
				$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array("PROPERTY_ID"=>$arProp["ID"]));
				while($arPEnum = $dbrPEnum->GetNext()):
				?>
					<option value="<?=$arPEnum["ID"]?>"<?if(${"find_el_property_".$arProp["ID"]} == $arPEnum["ID"])echo " selected"?>><?=$arPEnum["VALUE"]?></option>
				<?
				endwhile;
		?></select>
		<?
		elseif($arProp["PROPERTY_TYPE"]=='G'):
			echo _ShowGroupPropertyField('find_el_property_'.$arProp["ID"], $arProp, ${'find_el_property_'.$arProp["ID"]});
		endif;
		?>
	</td>
</tr>
<?
	endif;
endforeach;?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?type='.$type.'&IBLOCK_ID='.$IBLOCK_ID, "form"=>"find_form"));
$oFilter->End();
?>
</form>
<script language="JavaScript">
<!--
function addNewRow(tableID, row_to_clone)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	if(row_to_clone == null)
		row_to_clone = -2;
	var sHTML = tbl.rows[cnt+row_to_clone].cells[0].innerHTML;
	var oRow = tbl.insertRow(cnt+row_to_clone+1);
	var oCell = oRow.insertCell(0);

	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('[n',p);
		if(s<0)break;
		var e = sHTML.indexOf(']',s);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+2,e-s));
		sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
		p=s+1;
	}
	p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__n',p);
		if(s<0)break;
		var e = sHTML.indexOf('__',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__n'+(++n)+'__'+sHTML.substr(e+2);
		p=e+2;
	}
	p = 0;
	while(true)
	{
		var s = sHTML.indexOf('%5Bn',p);
		if(s<0)break;
		var e = sHTML.indexOf('%5D',s+3);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+4,e-s));
		sHTML = sHTML.substr(0, s)+'%5Bn'+(++n)+'%5D'+sHTML.substr(e+3);
		p=e+3;
	}
	oCell.innerHTML = sHTML;
}
//-->
</script>
<?
$lAdmin->DisplayList();
?>
<?if($bWorkFlow || $bBizproc):?>
	<?echo BeginNote();?>
	<table cellpadding="0" cellspacing="4" border="0">
	<tr><td><img src="/bitrix/images/workflow/green.gif" width="14" height="14" border=0 alt=""></td><td><font class="legendtext"> - <?echo GetMessage("IBLOCK_GREEN_ALT")?></font></td></tr>
	<tr><td><img src="/bitrix/images/workflow/yellow.gif" width="14" height="14" border=0 alt=""></td><td><font class="legendtext"> - <?echo GetMessage("IBLOCK_YELLOW_ALT")?></font></td></tr>
	<tr><td><img src="/bitrix/images/workflow/red.gif" width="14" height="14" border=0 alt=""></td><td><font class="legendtext"> - <?echo GetMessage("IBLOCK_RED_ALT")?></font></td></tr>
	</table>
	<?echo EndNote();?>
<?endif;?>
<?
if($BlockPerm >= "X")
{
	echo
		BeginNote(),
		GetMessage("IBEL_A_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_element_admin.php?".$sThisSectionUrl).'">',
		GetMessage("IBEL_A_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
