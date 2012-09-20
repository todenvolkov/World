<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$bBizproc = CModule::IncludeModule("bizproc");

$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);

if($arIBTYPE!==false):

$strWarning="";
$bVarsFromForm = false;
$ID=IntVal($ID);

$Perm = CIBlock::GetPermission($ID);
if($Perm>="X" && $REQUEST_METHOD=="POST" && strlen($_POST["Update"])>0 && !isset($_POST["propedit"]) && check_bitrix_sessid())
{
	$DB->StartTransaction();

	$arPICTURE = $HTTP_POST_FILES["PICTURE"];
	$arPICTURE["del"] = ${"PICTURE_del"};
	$arPICTURE["MODULE_ID"] = "iblock";

	if ($VERSION != 2) $VERSION = 1;
	if ($RSS_ACTIVE != "Y") $RSS_ACTIVE = "N";
	if ($RSS_FILE_ACTIVE != "Y") $RSS_FILE_ACTIVE = "N";
	if ($RSS_YANDEX_ACTIVE != "Y") $RSS_YANDEX_ACTIVE = "N";

	$ib = new CIBlock;
	$arFields = Array(
		"ACTIVE"=>$ACTIVE,
		"NAME"=>$NAME,
		"CODE"=>$CODE,
		"LIST_PAGE_URL"=>$LIST_PAGE_URL,
		"DETAIL_PAGE_URL"=>$DETAIL_PAGE_URL,
		"INDEX_ELEMENT"=>$INDEX_ELEMENT,
		"IBLOCK_TYPE_ID"=>$type,
		"LID"=>$LID,
		"SORT"=>$SORT,
		"PICTURE"=>$arPICTURE,
		"DESCRIPTION"=>$DESCRIPTION,
		"DESCRIPTION_TYPE"=>$DESCRIPTION_TYPE,
		"EDIT_FILE_BEFORE"=>$EDIT_FILE_BEFORE,
		"EDIT_FILE_AFTER"=>$EDIT_FILE_AFTER,
		"WORKFLOW"=>$WF_TYPE=="WF"? "Y": "N",
		"BIZPROC"=>$WF_TYPE=="BP"? "Y": "N",
		"SECTION_CHOOSER"=>$SECTION_CHOOSER,
		"LIST_MODE"=>$LIST_MODE,
		"FIELDS" => $_REQUEST["FIELDS"],
		//MESSAGES
		"ELEMENTS_NAME"=>$ELEMENTS_NAME,
		"ELEMENT_NAME"=>$ELEMENT_NAME,
		"ELEMENT_ADD"=>$ELEMENT_ADD,
		"ELEMENT_EDIT"=>$ELEMENT_EDIT,
		"ELEMENT_DELETE"=>$ELEMENT_DELETE,
		);

	if($arIBTYPE["SECTIONS"]=="Y")
	{
		$arFields["SECTION_PAGE_URL"]=$SECTION_PAGE_URL;
		$arFields["INDEX_SECTION"]=$INDEX_SECTION;
		//MESSAGES
		$arFields["SECTIONS_NAME"]=$SECTIONS_NAME;
		$arFields["SECTION_NAME"]=$SECTION_NAME;
		$arFields["SECTION_ADD"]=$SECTION_ADD;
		$arFields["SECTION_EDIT"]=$SECTION_EDIT;
		$arFields["SECTION_DELETE"]=$SECTION_DELETE;
	}

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if($arIBTYPE["IN_RSS"]=="Y")
	{
		$arFields = array_merge($arFields, Array(
			"RSS_ACTIVE"=>$RSS_ACTIVE,
			"RSS_FILE_ACTIVE"=>$RSS_FILE_ACTIVE,
			"RSS_YANDEX_ACTIVE"=>$RSS_YANDEX_ACTIVE,
			"RSS_FILE_LIMIT"=>$RSS_FILE_LIMIT,
			"RSS_FILE_DAYS"=>$RSS_FILE_DAYS,
			"RSS_TTL"=>$RSS_TTL)
			);
	}

	if($Perm=="X")
		$arFields["GROUP_ID"]=$GROUP;

	//Assembly properties for check followed by add/update
	$arProperties = array();
	if($ID > 0)
	{
		$props = CIBlock::GetProperties($ID);
		while($p = $props->Fetch())
		{
			$arProperty = array(
				//All but IBLOCK_ID
				"NAME" => ${"PROPERTY_".$p["ID"]."_NAME"},
				"ACTIVE" => ${"PROPERTY_".$p["ID"]."_ACTIVE"},
				"SORT" => ${"PROPERTY_".$p["ID"]."_SORT"},
				"DEFAULT_VALUE" => ${"PROPERTY_".$p["ID"]."_DEFAULT_VALUE"},
				"CODE" => ${"PROPERTY_".$p["ID"]."_CODE"},
				"ROW_COUNT" => ${"PROPERTY_".$p["ID"]."_ROW_COUNT"},
				"COL_COUNT" => ${"PROPERTY_".$p["ID"]."_COL_COUNT"},
				"LINK_IBLOCK_ID" => ${"PROPERTY_".$p["ID"]."_LINK_IBLOCK_ID"},
				"WITH_DESCRIPTION" => ${"PROPERTY_".$p["ID"]."_WITH_DESCRIPTION"},
				"FILTRABLE" => ${"PROPERTY_".$p["ID"]."_FILTRABLE"},
				"SEARCHABLE" => ${"PROPERTY_".$p["ID"]."_SEARCHABLE"},
				"MULTIPLE"  => ${"PROPERTY_".$p["ID"]."_MULTIPLE"},
				"MULTIPLE_CNT" => ${"PROPERTY_".$p["ID"]."_MULTIPLE_CNT"},
				"IS_REQUIRED" => ${"PROPERTY_".$p["ID"]."_IS_REQUIRED"},
				"FILE_TYPE" => ${"PROPERTY_".$p["ID"]."_FILE_TYPE"},
				"LIST_TYPE" => ${"PROPERTY_".$p["ID"]."_LIST_TYPE"},
			);
			if(strstr(${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"}, ":")!==false)
			{
				list($arProperty["PROPERTY_TYPE"], $arProperty["USER_TYPE"])=explode(":", ${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"}, 2);
				$arProperty["USER_TYPE_SETTINGS"] = ${"PROPERTY_".$p["ID"]."_USER_TYPE_SETTINGS"};
			}
			else
			{
				$arProperty["PROPERTY_TYPE"] = ${"PROPERTY_".$p["ID"]."_PROPERTY_TYPE"};
				$arProperty["USER_TYPE"] = false;
				$arProperty["USER_TYPE_SETTINGS"] = false;
			}

			if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "PROPERTY_".$p["ID"]."_XML_ID"))
				$arProperty["XML_ID"] = $_POST["PROPERTY_".$p["ID"]."_XML_ID"];

			if(isset($_POST["PROPERTY_".$p["ID"]."_CNT"]))
			{
				$arProperty["VALUES"] = Array();

				$arDEFS = ${"PROPERTY_".$p["ID"]."_VALUES_DEF"};
				if(!is_array($arDEFS))
					$arDEFS = Array();
				$arSORTS = ${"PROPERTY_".$p["ID"]."_VALUES_SORT"};
				if(!is_array($arSORTS))
					$arSORTS = Array();
				$arXML = ${"PROPERTY_".$p["ID"]."_VALUES_XML"};
				if(!is_array($arXML))
					$arXML = Array();

				if(is_array(${"PROPERTY_".$p["ID"]."_VALUES"}))
				{
					foreach(${"PROPERTY_".$p["ID"]."_VALUES"} as $key=>$val)
					{
						$arProperty["VALUES"][$key] = array(
							"VALUE" => $val,
							"DEF" => (in_array($key, $arDEFS)?"Y":"N")
						);
						if(IntVal($arSORTS[$key])>0)
							$arProperty["VALUES"][$key]["SORT"] = IntVal($arSORTS[$key]);
						if(strlen($arXML[$key])>0)
							$arProperty["VALUES"][$key]["XML_ID"] = $arXML[$key];
					}
				}
			}

			$ibp = new CIBlockProperty;
			$res = $ibp->CheckFields($arProperty, $p["ID"], true);
			if(!$res)
			{
				$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
				$bVarsFromForm = true;
			}

			$arProperties[$p["ID"]] = $arProperty;
		}
	}

	for($i=0; $i<5; $i++)
	{
		if(strlen(${"PROPERTY_n".$i."_NAME"})<=0) continue;

		$arProperty = array(
			"NAME" => ${"PROPERTY_n".$i."_NAME"},
			"ACTIVE" => ${"PROPERTY_n".$i."_ACTIVE"},
			"SORT" => ${"PROPERTY_n".$i."_SORT"},
			"DEFAULT_VALUE" => ${"PROPERTY_n".$i."_DEFAULT_VALUE"},
			"CODE" => ${"PROPERTY_n".$i."_CODE"},
			"ROW_COUNT" => ${"PROPERTY_n".$i."_ROW_COUNT"},
			"COL_COUNT" => ${"PROPERTY_n".$i."_COL_COUNT"},
			"LINK_IBLOCK_ID" => ${"PROPERTY_n".$i."_LINK_IBLOCK_ID"},
			"WITH_DESCRIPTION" => ${"PROPERTY_n".$i."_WITH_DESCRIPTION"},
			"SEARCHABLE" => ${"PROPERTY_n".$i."_SEARCHABLE"},
			"FILTRABLE" => ${"PROPERTY_n".$i."_FILTRABLE"},
			"MULTIPLE" => ${"PROPERTY_n".$i."_MULTIPLE"},
			"MULTIPLE_CNT" => ${"PROPERTY_n".$i."_MULTIPLE_CNT"},
			"IS_REQUIRED" => ${"PROPERTY_n".$i."_IS_REQUIRED"},
			"FILE_TYPE" => ${"PROPERTY_n".$i."_FILE_TYPE"},
			"LIST_TYPE" => ${"PROPERTY_n".$i."_LIST_TYPE"},
			"IBLOCK_ID" => $ID,
		);
		if(strstr(${"PROPERTY_n".$i."_PROPERTY_TYPE"}, ":")!==false)
		{
			list($arProperty["PROPERTY_TYPE"], $arProperty["USER_TYPE"])=explode(":", ${"PROPERTY_n".$i."_PROPERTY_TYPE"}, 2);
			$arProperty["USER_TYPE_SETTINGS"] = ${"PROPERTY_n".$i."_USER_TYPE_SETTINGS"};
		}
		else
		{
			$arProperty["PROPERTY_TYPE"]=${"PROPERTY_n".$i."_PROPERTY_TYPE"};
			$arProperty["USER_TYPE"]=false;
			$arProperty["USER_TYPE_SETTINGS"]=false;
		}

		if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "PROPERTY_n".$i."_XML_ID"))
			$arProperty["XML_ID"] = $_POST["PROPERTY_n".$i."_XML_ID"];

		if(isset($_POST["PROPERTY_n".$i."_CNT"]))
		{
			$arProperty["VALUES"] = Array();
			$arDEFS = ${"PROPERTY_n".$i."_VALUES_DEF"};
			if(!is_array($arDEFS))
				$arDEFS = Array();
			$arSORTS = ${"PROPERTY_n".$i."_VALUES_SORT"};
			if(!is_array($arSORTS))
				$arSORTS = Array();
			$arXML = ${"PROPERTY_n".$i."_VALUES_XML"};
			if(!is_array($arXML))
				$arXML = Array();
			if(is_array(${"PROPERTY_n".$i."_VALUES"}))
			{
				foreach(${"PROPERTY_n".$i."_VALUES"} as $key=>$val)
				{
					$arProperty["VALUES"][$key] = Array(
						"VALUE" => $val,
						"DEF" => (in_array($key, $arDEFS)?"Y":"N")
					);
					if(IntVal($arSORTS[$key])>0)
						$arProperty["VALUES"][$key]["SORT"] = IntVal($arSORTS[$key]);
					if(strlen($arXML[$key])>0)
						$arProperty["VALUES"][$key]["XML_ID"] = $arXML[$key];
				}
			}
		}

		$ibp = new CIBlockProperty;
		$res = $ibp->CheckFields($arProperty, false, true);
		if(!$res)
		{
			$strWarning .= $ibp->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}

		$arProperties["n".$i] = $arProperty;
	}


	$bCreateRecord = $ID <= 0;

	if(!$bVarsFromForm)
	{
		if($ID>0)
		{
			$res = $ib->Update($ID, $arFields);
		}
		else
		{
			$arFields["VERSION"]=$VERSION;
			$ID = $ib->Add($arFields);
			$res = ($ID>0);
		}

		if(!$res)
		{
			$strWarning .= $ib->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			// RSS agent creation
			if ($RSS_FILE_ACTIVE == "Y")
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");
				CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
			}
			else
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");

			if ($RSS_YANDEX_ACTIVE == "Y")
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");
				CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
			}
			else
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");

			/********************/
			foreach($arProperties as $property_id => $arProperty)
			{
				$arProperty["IBLOCK_ID"] = $ID;
				if(intval($property_id) > 0)
				{
					if(${"PROPERTY_".$property_id."_DEL"} == "Y")
					{
						if(!CIBlockProperty::Delete($property_id) && ($ex = $APPLICATION->GetException()))
						{
							$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ex->GetString()."<br>";
							$bVarsFromForm = true;
						}
					}
					else
					{
						$ibp = new CIBlockProperty;
						$res = $ibp->Update($property_id, $arProperty);
						if(!$res)
						{
							$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
							$bVarsFromForm = true;
						}
					}
				}
				else
				{
					$ibp = new CIBlockProperty;
					$PropID = $ibp->Add($arProperty);
					if(IntVal($PropID)<=0)
					{
						$strWarning .= $ibp->LAST_ERROR."<br>";
						$bVarsFromForm = true;
					}
				}
			}
			/*******************************************/

			if(!$bVarsFromForm && $arIBTYPE["IN_RSS"]=="Y")
			{
				CIBlockRSS::Delete($ID);
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach($arNodesRSS as $key => $val)
				{
					if(strlen(${"RSS_NODE_VALUE_".$key}) > 0)
						CIBlockRSS::Add($ID, $val, ${"RSS_NODE_VALUE_".$key});
				}
			}

			if(!$bVarsFromForm && !$bCreateRecord && $bBizproc)
			{
				$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID));
				foreach ($arWorkflowTemplates as $t)
				{
					$create_bizproc = (array_key_exists("create_bizproc_".$t["ID"], $_REQUEST) && $_REQUEST["create_bizproc_".$t["ID"]] == "Y");
					$edit_bizproc = (array_key_exists("edit_bizproc_".$t["ID"], $_REQUEST) && $_REQUEST["edit_bizproc_".$t["ID"]] == "Y");

					$create_bizproc1 = (($t["AUTO_EXECUTE"] & 1) != 0);
					$edit_bizproc1 = (($t["AUTO_EXECUTE"] & 2) != 0);

					if ($create_bizproc != $create_bizproc1 || $edit_bizproc != $edit_bizproc1)
					{
						CBPDocument::UpdateWorkflowTemplate(
							$t["ID"],
							array("iblock", "CIBlockDocument", "iblock_".$ID),
							array(
								"AUTO_EXECUTE" => (($create_bizproc ? 1 : 0) | ($edit_bizproc ? 2 : 0))
							),
							$arErrorsTmp
						);
					}
				}
			}

			if(!$bVarsFromForm)
			{
				if(
					$bBizproc
					&& $_REQUEST['BIZ_PROC_ADD_DEFAULT_TEMPLATES']=='Y'
					&& CBPDocument::GetNumberOfWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID))<=0
					&& $arFields["BIZPROC"] == "Y"
				)
					CBPDocument::AddDefaultWorkflowTemplates(array("iblock", "CIBlockDocument", "iblock_".$ID));

				$DB->Commit();

				//Check if index needed
				CIBlock::CheckForIndexes($ID);

				if(strlen($apply)<=0)
				{
					if(strlen($_REQUEST["return_url"])>0)
						LocalRedirect($_REQUEST["return_url"]);
					else
						LocalRedirect("/bitrix/admin/iblock_admin.php?type=".$type."&lang=".LANG."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N"));
				}
				LocalRedirect("/bitrix/admin/iblock_edit.php?type=".$type."&tabControl_active_tab=".urlencode($tabControl_active_tab)."&lang=".LANG."&ID=".$ID."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N").(strlen($_REQUEST["return_url"])>0? "&return_url=".urlencode($_REQUEST["return_url"]): ""));
			}
		}
	}

	$DB->Rollback();
}

if($Perm>="X" && $REQUEST_METHOD=="GET" && intval($_REQUEST["delete_bizproc_template"])>0 && check_bitrix_sessid() && $bBizproc)
{
	$arErrorTmp = array();
	CBPDocument::DeleteWorkflowTemplate($_REQUEST["delete_bizproc_template"], array("iblock", "CIBlockDocument", "iblock_".$ID), $arErrorTmp);
	if (count($arErrorTmp) > 0)
	{
		foreach ($arErrorTmp as $e)
			$strWarning .= $e["message"]."<br />";
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", Array("delete_bizproc_template", "sessid")));
		die();
	}
}


if($ID>0)
	$APPLICATION->SetTitle(GetMessage("IB_E_EDIT_TITLE", array("#IBLOCK_TYPE#"=>$arIBTYPE["NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("IB_E_NEW_TITLE", array("#IBLOCK_TYPE#"=>$arIBTYPE["NAME"])));


ClearVars("str_");
$str_ACTIVE="Y";
$str_WORKFLOW="Y";
$str_BIZPROC="N";
$str_SECTION_CHOOSER="L";
$str_LIST_MODE="";
$str_INDEX_ELEMENT="Y";
$str_INDEX_SECTION="Y";
$str_PROPERTY_FILE_TYPE = "jpg, gif, bmp, png, jpeg";
$str_LIST_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/index.php?ID=#IBLOCK_ID#";
$str_SECTION_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/list.php?SECTION_ID=#ID#";
$str_DETAIL_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/detail.php?ID=#ID#";
$str_SORT="500";
$str_VERSION="1";
$str_RSS_ACTIVE="N";
$str_RSS_TTL="24";
$str_RSS_FILE_ACTIVE="N";
$str_RSS_FILE_LIMIT="10";
$str_RSS_FILE_DAYS="7";
$str_RSS_YANDEX_ACTIVE="N";

$bCurrentBPDisabled = true;

$ib = new CIBlock;
$ib_result = $ib->GetByID($ID);
if(!$ib_result->ExtractFields("str_"))
{
	$ID=0;
}
else
{
	$bCurrentBPDisabled = ($str_BIZPROC!='Y');

	$str_LID = Array();
	$db_LID = CIBlock::GetSite($ID);
	while($ar_LID = $db_LID->Fetch())
		$str_LID[] = $ar_LID["LID"];
}

if(isset($_POST["propedit"]) && is_array($_POST["propedit"]))
{
	$prop_id = array_keys($_POST["propedit"]);
	$str_PROPERTY_ID = $prop_id[0];

	if(IntVal($str_PROPERTY_ID)>0)
	{
		$db_Prop = CIBlockProperty::GetByID(IntVal($str_PROPERTY_ID));
		if(($res = $db_Prop->Fetch()) && $res["IBLOCK_ID"]==$ID)
			$str_PROPERTY_ID = IntVal($str_PROPERTY_ID);
		else
			$str_PROPERTY_ID = "";
	}
}

if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n"))
	$APPLICATION->SetTitle(GetMessage("IB_E_PROPERTY_TITLE", array("#IBLOCK_TYPE#"=>$arIBTYPE["NAME"], "#IBLOCK_NAME#"=>$_POST["NAME"])));

endif; //$arIBTYPE!==false

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($arIBTYPE!==false):

$bVarsFromForm = ($bVarsFromForm || isset($_POST["propedit"]));

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$WORKFLOW = $WF_TYPE == "WF"? "Y": "N";
	$BIZPROC = $WF_TYPE == "BP"? "Y": "N";
	$RSS_FILE_ACTIVE = ($RSS_FILE_ACTIVE != "Y"? "N":"Y");
	$RSS_YANDEX_ACTIVE = ($RSS_YANDEX_ACTIVE != "Y"? "N":"Y");
	$RSS_ACTIVE = ($RSS_ACTIVE != "Y"? "N":"Y");
	$VERSION = ($VERSION != 2? 1:2);
	unset($PICTURE);
	$DB->InitTableVarsForEdit("b_iblock", "", "str_");
	$str_LID = $LID;
}

if($Perm>="X"):
	$aMenu = array(
		array(
			"TEXT"=>GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK"=>'iblock_admin.php?lang='.$lang.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N"),
			"ICON"=>"btn_list",
		)
	);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$u = new CAdminPopup(
	"mnu_LIST_PAGE_URL",
	"mnu_LIST_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("LIST", "__SetUrlVar", "mnu_LIST_PAGE_URL", "LIST_PAGE_URL"),
	array("zIndex" => 2000)
);
$u->Show();
$u = new CAdminPopup(
	"mnu_SECTION_PAGE_URL",
	"mnu_SECTION_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("SECTION", "__SetUrlVar", "mnu_SECTION_PAGE_URL", "SECTION_PAGE_URL"),
	array("zIndex" => 2000)
);
$u->Show();
$u = new CAdminPopup(
	"mnu_DETAIL_PAGE_URL",
	"mnu_DETAIL_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("DETAIL", "__SetUrlVar", "mnu_DETAIL_PAGE_URL", "DETAIL_PAGE_URL"),
	array("zIndex" => 2000)
);
$u->Show();

?>
<script>
	function __SetUrlVar(id, mnu_id, el_id)
	{
		var mnu_list = eval(mnu_id);
		var obj_ta = document.getElementById(el_id);
		obj_ta.focus();
		obj_ta.value += id;

		mnu_list.PopupHide();
		obj_ta.focus();
	}

	function __ShUrlVars(div, el_id)
	{
		var pos = jsUtils.GetRealPos(div);
		var mnu_list = eval('mnu_'+el_id);
		setTimeout(function(){mnu_list.PopupShow(pos)}, 10);
	}
</script>

<form method="POST" name="frm" id="frm" action="iblock_edit.php?type=<?echo htmlspecialchars($type)?>&amp;lang=<?echo LANG?>&amp;admin=<?echo ($_REQUEST["admin"]=="Y"? "Y": "N")?>"  ENCTYPE="multipart/form-data">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<?if($bBizproc && $bCurrentBPDisabled):?>
<input type="hidden" name="BIZ_PROC_ADD_DEFAULT_TEMPLATES" value="Y">
<?endif?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($_REQUEST["return_url"])>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($_REQUEST["return_url"])?>"><?endif?>
<?CAdminMessage::ShowOldStyleError($strWarning);?>
<?
function show_post_var($vname, $vvalue, $var_stack=array())
{
	if(is_array($vvalue))
	{
		foreach($vvalue as $key=>$value)
			show_post_var($key, $value, array_merge($var_stack ,array($vname)));
	}
	else
	{
		if(count($var_stack)>0)
		{
			$var_name=$var_stack[0];
			for($i=1; $i<count($var_stack);$i++)
				$var_name.="[".$var_stack[$i]."]";
			$var_name.="[".$vname."]";
		}
		else
			$var_name=$vname;
		?><input type="hidden" name="<?echo htmlspecialchars($var_name)?>" value="<?echo htmlspecialchars($vvalue)?>"><?
	}
}

if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):

	foreach($_POST as $key => $value)
	{
		if($key!="propedit" && substr($key, 0, strlen("PROPERTY_".$str_PROPERTY_ID."_")) != "PROPERTY_".$str_PROPERTY_ID."_")
		{
			show_post_var($key, $value);
		}
	}

	${"PROPERTY_MULTIPLE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_MULTIPLE"}!="Y"?"N":"Y");
	${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!=="Y"?"N":"Y");
	${"PROPERTY_ACTIVE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_ACTIVE"}!="Y"?"N":"Y");
	${"PROPERTY_DEL_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_DEL"}!="Y"?"N":"Y");
	if(strpos(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, ":")!==false)
	{
		list(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"},${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"})=explode(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, 2);
	}
	else
		${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"}="";
	$tmp_PROP_ID = $str_PROPERTY_ID;
	$DB->InitTableVarsForEdit("b_iblock_property", "PROPERTY_".$str_PROPERTY_ID."_", "str_PROPERTY_");
	$str_PROPERTY_ID = $tmp_PROP_ID;

	$aTabs = array(
		array(
			"DIV" => $_REQUEST["tabControl_active_tab"],
			"TAB" => GetMessage("IB_E_TAB1"),
			"ICON"=>"iblock_property",
			"TITLE"=>GetMessage("IB_E_TAB1_T"),
		),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();

		$arFieldList = $DB->GetTableFieldsList("b_iblock_property");
		$arProperty = array();
		foreach($arFieldList as $strFieldName)
			$arProperty[$strFieldName]=${"PROPERTY_".$str_PROPERTY_ID."_".$strFieldName};
		$arProperty["ID"] = $str_PROPERTY_ID;
		$arProperty["WITH_DESCRIPTION"] = "N";

		if($str_PROPERTY_USER_TYPE!="")
			$arUserType = CIBlockProperty::GetUserType($str_PROPERTY_USER_TYPE);
		else
			$arUserType = array();

		$arPropertyFields = array();
		if(array_key_exists("GetSettingsHTML", $arUserType))
			$USER_TYPE_SETTINGS_HTML = call_user_func_array($arUserType["GetSettingsHTML"],
				array(
					$arProperty,
					array(
						"NAME"=>"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE_SETTINGS",
					),
					&$arPropertyFields,
				));
		else
			$USER_TYPE_SETTINGS_HTML = "";
	?>
		<tr>
			<td width="40%">ID:<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_PROPERTY_TYPE" value="<?echo $str_PROPERTY_PROPERTY_TYPE.($str_PROPERTY_USER_TYPE? ":".$str_PROPERTY_USER_TYPE: "")?>"></td>
			<td width="60%"><?echo ($str_PROPERTY_ID>0?$str_PROPERTY_ID:GetMessage("IB_E_PROP_NEW"))?></td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE"><?echo GetMessage("IB_E_PROP_ACT")?></label></td>
			<td><input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="Y"<?if($str_PROPERTY_ACTIVE=="Y")echo " checked"?>></td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IB_E_PROP_SORT_DET")?></td>
			<td><input type="text" size="3" maxlength="10"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_SORT" value="<?echo $str_PROPERTY_SORT?>"></td>
		</tr>
		<tr>
			<td><?echo GetMessage("IB_E_PROP_NAME_DET")?></td>
			<td ><input type="text" size="30" maxlength="50"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_NAME" value="<?echo $str_PROPERTY_NAME?>"></td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IB_E_PROP_CODE_DET")?></td>
			<td><input type="text" size="30" maxlength="50"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_CODE" value="<?echo $str_PROPERTY_CODE?>"></td>
		</tr>
		<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
		<tr>
			<td><?echo GetMessage("IB_E_PROP_EXTERNAL_CODE")?></td>
			<td><input type="text" size="30" maxlength="50"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_XML_ID" value="<?echo $str_PROPERTY_XML_ID?>"></td>
		</tr>
		<?endif?>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE"><?echo GetMessage("IB_E_PROP_MULTIPLE")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="Y"<?if($str_PROPERTY_MULTIPLE=="Y")echo " checked"?> onClick="if(document.getElementById('<?echo 'PROPERTY_'.$str_PROPERTY_ID.'_MULTIPLE_CNT'?>')) document.getElementById('<?echo 'PROPERTY_'.$str_PROPERTY_ID.'_MULTIPLE_CNT'?>').disabled = !this.checked">
			</td>
		</tr>
		<tr>
			<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED"><?echo GetMessage("IB_E_PROP_IS_REQUIRED")?></label></td>
			<td>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="N">
			<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="Y"<?if($str_PROPERTY_IS_REQUIRED==="Y")echo " checked"?>>
			</td>
		</tr>

		<?
		if(is_array($arPropertyFields["SHOW"]) && in_array("SEARCHABLE", $arPropertyFields["SHOW"]))
			$bShow = true;
		elseif(is_array($arPropertyFields["HIDE"]) && in_array("SEARCHABLE", $arPropertyFields["HIDE"]))
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "E")
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "G")
			$bShow = false;
		else
			$bShow = true;

		if($bShow):?>
			<tr>
				<td><label id="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE"><?echo GetMessage("IB_E_PROP_SEARCHABLE")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="Y" <?if($str_PROPERTY_SEARCHABLE=="Y")echo " checked"?>>
				</td>
			</tr>
		<?elseif(
			is_array($arPropertyFields["SET"])
			&& array_key_exists("SEARCHABLE", $arPropertyFields["SET"])
		):?>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="<?echo htmlspecialchars($arPropertyFields["SET"]["SEARCHABLE"])?>">
		<?endif?>

		<?
		if(is_array($arPropertyFields["SHOW"]) && in_array("FILTRABLE", $arPropertyFields["SHOW"]))
			$bShow = true;
		elseif(is_array($arPropertyFields["HIDE"]) && in_array("FILTRABLE", $arPropertyFields["HIDE"]))
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "F")
			$bShow = false;
		else
			$bShow = true;

		if($bShow):?>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE"><?echo GetMessage("IB_E_PROP_FILTRABLE")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="Y" <?if($str_PROPERTY_FILTRABLE=="Y")echo " checked"?>>
				</td>
			</tr>
		<?elseif(
			is_array($arPropertyFields["SET"])
			&& array_key_exists("FILTRABLE", $arPropertyFields["SET"])
		):?>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="<?echo htmlspecialchars($arPropertyFields["SET"]["FILTRABLE"])?>">
		<?endif?>

		<?
		if(is_array($arPropertyFields["SHOW"]) && in_array("WITH_DESCRIPTION", $arPropertyFields["SHOW"]))
			$bShow = true;
		elseif(is_array($arPropertyFields["HIDE"]) && in_array("WITH_DESCRIPTION", $arPropertyFields["HIDE"]))
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "L")
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "G")
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "E")
			$bShow = false;
		else
			$bShow = true;

		if($bShow):?>
			<tr>
				<td><label for="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION"><?echo GetMessage("IB_E_PROP_WITH_DESC")?></label></td>
				<td>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="N">
				<input type="checkbox" id="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="Y" <?if($str_PROPERTY_WITH_DESCRIPTION=="Y")echo " checked"?>>
				</td>
			</tr>
		<?elseif(
			is_array($arPropertyFields["SET"])
			&& array_key_exists("WITH_DESCRIPTION", $arPropertyFields["SET"])
		):?>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="<?echo htmlspecialchars($arPropertyFields["SET"]["WITH_DESCRIPTION"])?>">
		<?endif?>

		<?
		if(is_array($arPropertyFields["SHOW"]) && in_array("MULTIPLE_CNT", $arPropertyFields["SHOW"]))
			$bShow = true;
		elseif(is_array($arPropertyFields["HIDE"]) && in_array("MULTIPLE_CNT", $arPropertyFields["HIDE"]))
			$bShow = false;
		elseif($str_PROPERTY_PROPERTY_TYPE == "L")
			$bShow = false;
		else
			$bShow = true;

		if($bShow):?>
			<tr>
				<td><?echo GetMessage("IB_E_PROP_MULTIPLE_CNT")?></td>
				<td><input type="text" id="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT"  value="<?echo $str_PROPERTY_MULTIPLE_CNT?>" size="3" <?echo $str_PROPERTY_MULTIPLE=="Y"? "": "disabled"?>></td>
			</tr>
		<?elseif(
			is_array($arPropertyFields["SET"])
			&& array_key_exists("MULTIPLE_CNT", $arPropertyFields["SET"])
		):?>
			<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["MULTIPLE_CNT"])?>">
		<?endif?>



		<?if($str_PROPERTY_PROPERTY_TYPE=="L"):?>
			<tr>
				<td ><?echo GetMessage("IB_E_PROP_APPEARANCE")?></td>
				<td>
					<select name="PROPERTY_<?echo $str_PROPERTY_ID?>_LIST_TYPE" >
						<option value="L"<?if($str_PROPERTY_LIST_TYPE!="C")echo " selected"?>><?echo GetMessage("IB_E_PROP_APPEARANCE_LIST")?></option>
						<option value="C"<?if($str_PROPERTY_LIST_TYPE=="C")echo " selected"?>><?echo GetMessage("IB_E_PROP_APPEARANCE_CHECKBOX")?></option>
					</select>
				</td>
			</tr>

			<?
			if(is_array($arPropertyFields["SHOW"]) && in_array("ROW_COUNT", $arPropertyFields["SHOW"]))
				$bShow = true;
			elseif(is_array($arPropertyFields["HIDE"]) && in_array("ROW_COUNT", $arPropertyFields["HIDE"]))
				$bShow = false;
			else
				$bShow = true;

			if($bShow):?>
				<tr>
					<td ><?echo GetMessage("IB_E_PROP_ROW_CNT")?></td>
					<td><input type="text" size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>"></td>
				</tr>
			<?elseif(
				is_array($arPropertyFields["SET"])
				&& array_key_exists("ROW_COUNT", $arPropertyFields["SET"])
			):?>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["ROW_COUNT"])?>">
			<?endif?>

			<tr class="heading">
				<td valign="top" colspan="2"><?echo GetMessage("IB_E_PROP_LIST_VALUES")?></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
				<table cellpadding="1" cellspacing="0" border="0">
				<tr>
				<td>ID</td>
				<td>XML_ID</td>
				<td><?echo GetMessage("IB_E_PROP_LIST_VALUE")?></td>
				<td><?echo GetMessage("IB_E_PROP_LIST_SORT")?></td>
				<td><?echo GetMessage("IB_E_PROP_LIST_DEFAULT")?></td>
				</tr>
				<?
				if(!isset($_POST["PROPERTY_".$str_PROPERTY_ID."_CNT"]))
				{
					$MAX_NEW_ID = 0;
					$arPROPERTY_VALUES = Array();
					$arPROPERTY_VALUES_DEF = Array();
					$arPROPERTY_VALUES_SORT = Array();
					$arPROPERTY_VALUES_XML = Array();
					if(IntVal($str_PROPERTY_ID)>0)
					{
						$props = CIBlockProperty::GetPropertyEnum($str_PROPERTY_ID);
						while($res = $props->Fetch())
						{
							$arPROPERTY_VALUES[$res["ID"]] = $res["VALUE"];
							$arPROPERTY_VALUES_SORT[$res["ID"]] = $res["SORT"];
							$arPROPERTY_VALUES_XML[$res["ID"]] = $res["XML_ID"];
							if($res["DEF"]=="Y")
								$arPROPERTY_VALUES_DEF[] = $res["ID"];
						}
					}
				}
				else
				{
					$MAX_NEW_ID = IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"});
					$arPROPERTY_VALUES = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES"};
					$arPROPERTY_VALUES_DEF = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_DEF"};
					$arPROPERTY_VALUES_SORT = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_SORT"};
					$arPROPERTY_VALUES_XML = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_XML"};
					if(!is_array($arPROPERTY_VALUES))
						$arPROPERTY_VALUES = Array();
					if(!is_array($arPROPERTY_VALUES_DEF))
						$arPROPERTY_VALUES_DEF = Array();
					if(!is_array($arPROPERTY_VALUES_SORT))
						$arPROPERTY_VALUES_SORT = Array();
					if(!is_array($arPROPERTY_VALUES_XML))
						$arPROPERTY_VALUES_XML = Array();
				}
				?>
				<?if($str_PROPERTY_MULTIPLE!="Y"):?>
				<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td colspan="2"><?echo GetMessage("IB_E_PROP_LIST_DEFAULT_NO")?></td>
				<td><input type="radio" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="0" <?if(in_array(0, $arPROPERTY_VALUES_DEF) || count($arPROPERTY_VALUES_DEF)<=0)echo " checked"?>> </td>
				</tr>
				<?endif?>
				<tr>
				<?
				$arPV_Keys = array_keys($arPROPERTY_VALUES);
				for($i=0; $i<count($arPV_Keys); $i++):
					if(strlen($arPROPERTY_VALUES[$arPV_Keys[$i]])<=0)
						continue;
				?>
				<tr>
				<td><?=(intval($arPV_Keys[$i])>0?htmlspecialchars($arPV_Keys[$i]):"&nbsp;")?></td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_XML[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialchars($arPROPERTY_VALUES_XML[$arPV_Keys[$i]])?>" size="15" maxlength="200"></td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialcharsex($arPROPERTY_VALUES[$arPV_Keys[$i]])?>" size="35" maxlength="255"></td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_SORT[<?echo htmlspecialchars($arPV_Keys[$i])?>]" value="<?echo htmlspecialchars($arPROPERTY_VALUES_SORT[$arPV_Keys[$i]])?>" size="5" maxlength="11"></td>
				<td><input type="<?=($str_PROPERTY_MULTIPLE!="Y"?"radio":"checkbox")?>" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="<?echo htmlspecialchars($arPV_Keys[$i])?>" <?if(in_array($arPV_Keys[$i], $arPROPERTY_VALUES_DEF))echo " checked"?>></td>
				</tr>
				<?endfor?>
				<?for($i=$MAX_NEW_ID; $i<$MAX_NEW_ID+5; $i++):?>
				<tr>
				<td>&nbsp;</td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_XML[n<?=$i?>]" size="15" maxlength="200"></td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES[n<?=$i?>]" size="35" maxlength="255"></td>
				<td><input type="text"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_SORT[n<?=$i?>]" size="5" maxlength="15" value="500"></td>
				<td><input type="<?=($str_PROPERTY_MULTIPLE!="Y"?"radio":"checkbox")?>" name="PROPERTY_<?echo $str_PROPERTY_ID?>_VALUES_DEF[]" value="n<?=$i?>"></td>
				</tr>
				<?endfor?>
				</table>
				<input type="hidden" name="PROPERTY_<?=htmlspecialchars($str_PROPERTY_ID)?>_CNT" value="<?echo ($MAX_NEW_ID+5)?>">
				<input type="submit"  name="propedit[<?echo $str_PROPERTY_ID?>]" value="<?echo GetMessage("IB_E_PROP_LIST_MORE")?>">

				</td>
			</tr>
		<?elseif($str_PROPERTY_PROPERTY_TYPE=="F"):?>
			<?
			if(is_array($arPropertyFields["SHOW"]) && in_array("COL_COUNT", $arPropertyFields["SHOW"]))
				$bShow = true;
			elseif(is_array($arPropertyFields["HIDE"]) && in_array("COL_COUNT", $arPropertyFields["HIDE"]))
				$bShow = false;
			else
				$bShow = true;

			if($bShow):?>
				<tr>
					<td ><?echo GetMessage("IB_E_PROP_FILE_TYPES_COL_CNT")?></td>
					<td><input type="text" size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>"></td>
				</tr>
			<?elseif(
				is_array($arPropertyFields["SET"])
				&& array_key_exists("COL_COUNT", $arPropertyFields["SET"])
			):?>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["COL_COUNT"])?>">
			<?endif?>

			<tr>
				<td ><?echo GetMessage("IB_E_PROP_FILE_TYPES")?></td>
				<td>
					<input type="text"  size="30" maxlength="255" name="PROPERTY_<?=$str_PROPERTY_ID?>_FILE_TYPE" value="<?echo $str_PROPERTY_FILE_TYPE?>">

					<select  onchange="if(this.selectedIndex!=0) document.frm.PROPERTY_<?=$str_PROPERTY_ID?>_FILE_TYPE.value=this[this.selectedIndex].value">
						<option value="-"></option>
						<option value=""<?if($str_PROPERTY_FILE_TYPE=="")echo " selected"?>><?echo GetMessage("IB_E_PROP_FILE_TYPES_ANY")?></option>
						<option value="jpg, gif, bmp, png, jpeg"<?if($str_PROPERTY_FILE_TYPE=="jpg, gif, bmp, png, jpeg")echo " selected"?>><?echo GetMessage("IB_E_PROP_FILE_TYPES_PIC")?></option>
						<option value="mp3, wav, midi, snd, au, wma"<?if($str_PROPERTY_FILE_TYPE=="mp3, wav, midi, snd, au, wma")echo " selected"?>><?echo GetMessage("IB_E_PROP_FILE_TYPES_SOUND")?></option>
						<option value="mpg, avi, wmv, mpeg, mpe"<?if($str_PROPERTY_FILE_TYPE=="mpg, avi, wmv, mpeg, mpe")echo " selected"?>><?echo GetMessage("IB_E_PROP_FILE_TYPES_VIDEO")?></option>
						<option value="doc, txt, rtf"<?if($str_PROPERTY_FILE_TYPE=="doc, txt, rtf")echo " selected"?>><?echo GetMessage("IB_E_PROP_FILE_TYPES_DOCS")?></option>
					</select>
				</td>
			</tr>
		<?elseif($str_PROPERTY_PROPERTY_TYPE=="G" || $str_PROPERTY_PROPERTY_TYPE=="E"):?>
			<?
			if(is_array($arPropertyFields["SHOW"]) && in_array("COL_COUNT", $arPropertyFields["SHOW"]))
				$bShow = true;
			else
				$bShow = false;

			if($bShow):?>
				<tr>
					<td ><?echo GetMessage("IB_E_PROP_FILE_TYPES_COL_CNT")?></td>
					<td><input type="text" size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>"></td>
				</tr>
			<?elseif(
				is_array($arPropertyFields["SET"])
				&& array_key_exists("COL_COUNT", $arPropertyFields["SET"])
			):?>
				<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["COL_COUNT"])?>">
			<?endif?>

			<tr>
				<td><?echo GetMessage("IB_E_PROP_LINK_IBLOCK")?></td>
				<td>
					<?
					if($str_PROPERTY_PROPERTY_TYPE=="G")
						$b_f = Array("!ID"=>$ID);
					else
						$b_f = Array();
					echo GetIBlockDropDownList(
						$str_PROPERTY_LINK_IBLOCK_ID,
						"PROPERTY_".$str_PROPERTY_ID."_LINK_IBLOCK_TYPE_ID",
						"PROPERTY_".$str_PROPERTY_ID."_LINK_IBLOCK_ID",
						$b_f
					);
					?>
				</td>
			</tr>
		<?else:?>
			<?
			if(is_array($arPropertyFields["HIDE"]) && in_array("COL_COUNT", $arPropertyFields["HIDE"]))
				$bShow = false;
			elseif(is_array($arPropertyFields["HIDE"]) && in_array("ROW_COUNT", $arPropertyFields["HIDE"]))
				$bShow = false;
			else
				$bShow = true;

			if($bShow):?>
			<tr>
				<td><?echo GetMessage("IB_E_PROP_SIZE")?></td>
				<td>
					<input type="text"  size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>"> x <input type="text"  size="2" maxlength="10" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>">
				</td>
			</tr>
			<?else:?>
				<?if(is_array($arPropertyFields["SET"]) && array_key_exists("ROW_COUNT", $arPropertyFields["SET"])):?>
					<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["ROW_COUNT"])?>">
				<?else:?>
					<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>">
				<?endif;?>

				<?if(is_array($arPropertyFields["SET"]) && array_key_exists("COL_COUNT", $arPropertyFields["SET"])):?>
					<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo htmlspecialchars($arPropertyFields["SET"]["COL_COUNT"])?>">
				<?else:?>
					<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>">
				<?endif;?>
			<?endif;

			if(is_array($arPropertyFields["HIDE"]) && in_array("DEFAULT_VALUE", $arPropertyFields["HIDE"]))
				$bShow = false;
			else
				$bShow = true;

			if($bShow):?>
			<tr>
				<td ><?echo GetMessage("IB_E_PROP_DEFAULT")?></td>
				<td>
				<?if(array_key_exists("GetPropertyFieldHtml", $arUserType)):
					echo call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$arProperty,
							array(
								"VALUE"=>${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"},
								"DESCRIPTION"=>""
							),
							array(
								"VALUE"=>"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE",
								"DESCRIPTION"=>"",
								"MODE" => "EDIT_FORM",
								"FORM_NAME" => "frm"
							),
						));
				else:?>
					<input type="text"  size="40" maxlength="2000" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE" value="<?echo $str_PROPERTY_DEFAULT_VALUE?>">
				<?endif;?>
				</td>
			</tr>
			<?endif;
		endif?>

		<?if($USER_TYPE_SETTINGS_HTML):?>
			<tr class="heading">
				<td colspan="2"><?
					if(isset($arPropertyFields["USER_TYPE_SETTINGS_TITLE"]))
						echo $arPropertyFields["USER_TYPE_SETTINGS_TITLE"];
					else
						GetMessage("IB_E_PROP_USER_TYPE_SETTINGS");
				?></td>
			</tr>
			<?
			echo $USER_TYPE_SETTINGS_HTML;
		endif;
		?>

	<?
	$tabControl->Buttons();
	?>
	<input type="submit"  name="propedit[x]" value="<?echo GetMessage("IB_E_PROP_MORE")?>" title="<?echo GetMessage("IB_E_PROP_MORE_TITLE")?>">
	<input type="reset"  name="reset" value="<?echo GetMessage("IB_E_PROP_RESET")?>">
	<?
	$tabControl->End();
	?>
<?
else: //if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):
?>
<?
$bTab3 = ($arIBTYPE["IN_RSS"]=="Y");
$bWorkflow = CModule::IncludeModule("workflow");
$bBizprocTab = $bBizproc && $str_BIZPROC == "Y";

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("IB_E_TAB2"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB2_T"));
$aTabs[] = array("DIV" => "edit6", "TAB" => GetMessage("IB_E_TAB6"), "ICON"=>"iblock_fields", "TITLE"=>GetMessage("IB_E_TAB6_T"));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("IB_E_TAB3"), "ICON"=>"iblock_props", "TITLE"=>GetMessage("IB_E_TAB3_T"));
$aTabs[] = array("DIV" => "edit8", "TAB" => GetMessage("IB_E_TAB8"), "ICON"=>"section_fields", "TITLE"=>GetMessage("IB_E_TAB8_T"));
if($bTab3) $aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("IB_E_TAB7"), "ICON"=>"iblock_rss", "TITLE"=>GetMessage("IB_E_TAB7_T"));
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("IB_E_TAB4"), "ICON"=>"iblock_access", "TITLE"=>GetMessage("IB_E_TAB4_T"));
$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("IB_E_TAB5"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB5_T"));
if ($bBizprocTab) $aTabs[] = array("DIV" => "edit7", "TAB" => GetMessage("IB_E_TAB7_BP"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB7_BP"));
$aTabs[] = array("DIV" => "log", "TAB" => GetMessage("IB_E_TAB_LOG"), "ICON"=>"iblock", "TITLE"=>GetMessage("IB_E_TAB_LOG_TITLE"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<?if($ID>0):?>
	<tr>
		<td valign="top" width="40%"><?=GetMessage("IB_E_ID")?>:</td>
		<td valign="top" width="60%"><?echo $str_ID?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
		<td valign="top" width="60%">
			<input type="hidden" name="VERSION" value="<?=$str_VERSION?>">
			<?if($str_VERSION==1)echo GetMessage("IB_E_COMMON_STORAGE")?>
			<?if($str_VERSION==2)echo GetMessage("IB_E_SEPARATE_STORAGE")?>
			<br><a href="iblock_convert.php?lang=<?=LANG?>&amp;IBLOCK_ID=<?echo $str_ID?>"><?=$str_LAST_CONV_ELEMENT>0?"<span class=\"required\">".GetMessage("IB_E_CONVERT_CONTINUE"):GetMessage("IB_E_CONVERT_START")."</span>"?></a>
		</td>
	</tr>
	<tr>
		<td valign="top" ><?echo GetMessage("IB_E_LAST_UPDATE")?></td>
		<td valign="top"><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? else: ?>
	<tr>
		<td valign="top" width="40%"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
		<td valign="top" width="60%">
				<label><input type="radio" name="VERSION" value="1" <?if($str_VERSION==1)echo " checked"?>><?=GetMessage("IB_E_COMMON_STORAGE")?></label><br>
				<label><input type="radio" name="VERSION" value="2" <?if($str_VERSION==2)echo " checked"?>><?=GetMessage("IB_E_SEPARATE_STORAGE")?></label>
		</td>
	</tr>
	<? endif; ?>
	<tr>
		<td valign="top"><label for="ACTIVE"><?echo GetMessage("IB_E_ACTIVE")?>:</label></td>
		<td valign="top">
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
			<span style="display:none;"><input type="submit" name="save" value="Y" style="width:0px;height:0px"></span>
		</td>
	</tr>
	<tr>
		<td valign="top"  width="40%"><? echo GetMessage("IB_E_CODE")?>:</td>
		<td valign="top" width="60%">
			<input type="text" name="CODE" size="20" maxlength="50" value="<?echo $str_CODE?>" >
		</td>
	</tr>

	<tr valign="top">
		<td><span class="required">*</span><?echo GetMessage("IB_E_SITES")?></td>
		<td><?=CLang::SelectBoxMulti("LID", $str_LID);?></td>
	</tr>

	<tr>
		<td valign="top" ><span class="required">*</span><? echo GetMessage("IB_E_NAME")?>:</td>
		<td valign="top">
			<input type="text" name="NAME" size="40" maxlength="255"  value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IB_E_SORT")?>:</td>
		<td valign="top">
			<input type="text" name="SORT" size="10"  maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
	<tr>
		<td valign="top" ><?echo GetMessage("IB_E_XML_ID")?>:</td>
		<td valign="top">
			<input type="text" name="XML_ID"  size="20" maxlength="50" value="<?echo $str_XML_ID?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top" ><?echo GetMessage("IB_E_LIST_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="LIST_PAGE_URL" id="LIST_PAGE_URL" size="40" maxlength="255" value="<?echo $str_LIST_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'LIST_PAGE_URL')" value='...'>
		</td>
	</tr>
	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top" ><?echo GetMessage("IB_E_SECTION_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="SECTION_PAGE_URL" id="SECTION_PAGE_URL" size="40" maxlength="255" value="<?echo $str_SECTION_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'SECTION_PAGE_URL')" value='...'>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top" ><?echo GetMessage("IB_E_DETAIL_PAGE_URL")?></td>
		<td valign="top">
			<input type="text" name="DETAIL_PAGE_URL" id="DETAIL_PAGE_URL" size="40" maxlength="255" value="<?echo $str_DETAIL_PAGE_URL?>">
			<input type="button" onclick="__ShUrlVars(this, 'DETAIL_PAGE_URL')" value='...'>
		</td>
	</tr>

	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top"><label for="INDEX_SECTION"><?echo GetMessage("IB_E_INDEX_SECTION")?></label></td>
		<td valign="top">
			<input type="hidden" name="INDEX_SECTION" value="N">
			<input type="checkbox" id="INDEX_SECTION" name="INDEX_SECTION" value="Y"<?if($str_INDEX_SECTION=="Y")echo " checked"?>>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><label for="INDEX_ELEMENT"><?echo GetMessage("IB_E_INDEX_ELEMENT")?></label></td>
		<td valign="top">
			<input type="hidden" name="INDEX_ELEMENT" value="N">
			<input type="checkbox" id="INDEX_ELEMENT" name="INDEX_ELEMENT" value="Y"<?if($str_INDEX_ELEMENT=="Y")echo " checked"?>>
		</td>
	</tr>
	<?if($bWorkflow && $bBizproc):?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_WF_TYPE")?></td>
		<td valign="top">
			<select name="WF_TYPE">
				<option value="N"><?echo GetMessage("IB_E_WF_TYPE_NONE")?></option>
				<option value="WF" <?if($str_WORKFLOW=="Y")echo "selected"?>><?echo GetMessage("IB_E_WF_TYPE_WORKFLOW")?></option>
				<option value="BP" <?if($str_BIZPROC=="Y")echo "selected"?>><?echo GetMessage("IB_E_WF_TYPE_BIZPROC")?></option>
			</select>
		</td>
	</tr>
	<?elseif($bWorkflow && !$bBizproc):?>
	<tr>
		<td valign="top"><label for="WF_TYPE"><?echo GetMessage("IB_E_WORKFLOW")?></label></td>
		<td valign="top">
			<input type="hidden" name="WF_TYPE" value="N">
			<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="WF"<?if($str_WORKFLOW=="Y")echo " checked"?>>
		</td>
	</tr>
	<?elseif($bBizproc && !$bWorkflow):?>
	<tr>
		<td valign="top"><label for="WF_TYPE"><?echo GetMessage("IB_E_BIZPROC")?></label></td>
		<td valign="top">
			<input type="hidden" name="WF_TYPE" value="N">
			<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="BP"<?if($str_BIZPROC=="Y")echo " checked"?>>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_CHOOSER")?>:</td>
		<td valign="top">
			<select name="SECTION_CHOOSER">
			<option value="L"<?if($str_SECTION_CHOOSER=="L")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_LIST")?></option>
			<option value="D"<?if($str_SECTION_CHOOSER=="D")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_DROPDOWNS")?></option>
			<option value="P"<?if($str_SECTION_CHOOSER=="P")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_POPUP")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_LIST_MODE")?>:</td>
		<td valign="top">
			<select name="LIST_MODE">
			<option value=""><?echo GetMessage("IB_E_LIST_MODE_GLOBAL")?></option>
			<option value="S"<?if($str_LIST_MODE=="S") echo " selected"?>><?echo GetMessage("IB_E_LIST_MODE_SECTIONS")?></option>
			<option value="C"<?if($str_LIST_MODE=="C") echo " selected"?>><?echo GetMessage("IB_E_LIST_MODE_COMBINED")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_BEFORE"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_BEFORE)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IB_E_FILE_BEFORE")?></td>
		<td><input type="text" name="EDIT_FILE_BEFORE" size="50"  maxlength="255" value="<?echo $str_EDIT_FILE_BEFORE?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick()"></td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick2",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_AFTER"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_AFTER)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IB_E_FILE_AFTER")?></td>
		<td><input type="text" name="EDIT_FILE_AFTER" size="50"  maxlength="255" value="<?echo $str_EDIT_FILE_AFTER?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_DESCRIPTION")?></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_PICTURE")?></td>
		<td valign="top">
			<?echo CFile::InputFile("PICTURE", 20, $str_PICTURE);?><br>
			<?echo CFile::ShowImage($str_PICTURE, 200, 200, "border=0", "", true)?>
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td valign="top" colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame("DESCRIPTION", $str_DESCRIPTION, "DESCRIPTION_TYPE", $str_DESCRIPTION_TYPE, 250);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td ><?echo GetMessage("IB_E_DESCRIPTION_TYPE")?></td>
		<td >
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE1" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>><label for="DESCRIPTION_TYPE1"> <?echo GetMessage("IB_E_DESCRIPTION_TYPE_TEXT")?></label> /
			<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE2" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>><label for="DESCRIPTION_TYPE2"> <?echo GetMessage("IB_E_DESCRIPTION_TYPE_HTML")?></label>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<textarea cols="60" rows="15" name="DESCRIPTION" style="width:100%;"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td valign="top" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center">
				<tr class="heading">
					<td nowrap><?echo GetMessage("IB_E_FIELD_NAME")?></td>
					<td nowrap><?echo GetMessage("IB_E_FIELD_IS_REQUIRED")?></td>
					<td nowrap><?echo GetMessage("IB_E_FIELD_DEFAULT_VALUE")?></td>
				</tr>
				<?
				if($bVarsFromForm)
					$arFields = $_REQUEST["FIELDS"];
				else
					$arFields = CIBlock::GetFields($ID);
				$arDefFields = CIBlock::GetFieldsDefaults();
				foreach($arDefFields as $FIELD_ID => $arField):
					if(preg_match("/^(SECTION_|LOG_)/", $FIELD_ID)) continue;
					?>
					<tr valign="top">
						<td nowrap><?echo $arDefFields[$FIELD_ID]["NAME"]?></td>
						<td nowrap align="center">
							<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
							<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
						</td>
						<td nowrap>
						<?
						switch($FIELD_ID)
						{
							case "ACTIVE":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="Y" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="Y") echo "selected"?>><?echo GetMessage("MAIN_YES")?></option>
									<option value="N" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="N") echo "selected"?>><?echo GetMessage("MAIN_NO")?></option>
								</select>
								<?
								break;
							case "ACTIVE_FROM":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_EMPTY")?></option>
									<option value="=now" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=now") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_NOW")?></option>
									<option value="=today" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=today") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_TODAY")?></option>
								</select>
								<?
								break;
							case "ACTIVE_TO":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]"><?echo GetMessage("IB_E_FIELD_ACTIVE_TO")?></label></td></tr>
								<tr><td><input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="5"></td></tr>
								</table>
								<?
								break;
							case "NAME":
								?>
								<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
								<?
								break;
							case "SORT":
								?>
								<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="hidden" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>">
								<?
								break;
							case "DETAIL_TEXT_TYPE":
							case "PREVIEW_TEXT_TYPE":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="text" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="text") echo "selected"?>>text</option>
									<option value="html" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="html") echo "selected"?>>html</option>
								</select>
								<?
								break;
							case "DETAIL_TEXT":
							case "PREVIEW_TEXT":
								?>
								<textarea name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" rows="5" cols="47"><?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
								<?
								break;
							case "PREVIEW_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								<tr><td><input type="checkbox" value="resample" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>" size="7"></td></tr>
								</table>
								<?
								break;
							case "DETAIL_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								<tr><td><input type="checkbox" value="resample" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>" size="7"></td></tr>
								</table>
								<?
								break;
							case "CODE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"><?echo GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"><?echo GetMessage("IB_E_FIELD_EL_TRANSLITERATION")?></label></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"><?echo GetMessage("IB_E_FIELD_TRANS_LEN")?></label>&nbsp;<input type="text" size="4" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]"><?echo GetMessage("IB_E_FIELD_TRANS_CASE")?></label>&nbsp;<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]">
									<option value=""><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE")?></option>
									<option value="L" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="L") echo "selected"?>><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?></option>
									<option value="U" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="U") echo "selected"?>><?echo GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?></option>
								</select><td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"><?echo GetMessage("IB_E_FIELD_TRANS_SPACE")?></label>&nbsp;<input type="text" size="2" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"><?echo GetMessage("IB_E_FIELD_TRANS_OTHER")?></label>&nbsp;<input type="text" size="2" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"><?echo GetMessage("IB_E_FIELD_TRANS_EAT")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"><?echo GetMessage("IB_E_FIELD_EL_TRANS_USE_GOOGLE")?></label></td></tr>
								</table>
								<?
								break;
							default:
								?>
								<input type="hidden" value="" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]">&nbsp;
								<?
								break;
						}
						?>
						</td>
					</tr>
				<?endforeach?>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td valign="top" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("IB_E_PROP_NAME_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_TYPE_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_MULT_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_REQIRED_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_SORT_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_CODE_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_MODIFY_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_DELETE_SHORT")?></td>
				</tr>
				<?
				ClearVars("str_PROPERTY_");
				$props = CIBlock::GetProperties($ID, Array("sort"=>"asc"));
				$i=0;
				function _GetOldAndNew($props)
				{
					global $i;
					if($i==0 && ($tmp = $props->ExtractFields("str_PROPERTY_")))
						return $tmp;

					global $str_PROPERTY_ID, $str_PROPERTY_NAME, $str_PROPERTY_IS_REQUIRED, $str_PROPERTY_DEFAULT_VALUE, $str_PROPERTY_CODE;
					global $str_PROPERTY_SORT, $str_PROPERTY_MULTIPLE_CNT, $str_PROPERTY_XML_ID, $str_PROPERTY_ROW_COUNT,
					$str_PROPERTY_COL_COUNT, $str_PROPERTY_LINK_IBLOCK_ID, $str_PROPERTY_MULTIPLE, $str_PROPERTY_IS_REQUIRED, $str_PROPERTY_PROPERTY_TYPE;
					global $str_PROPERTY_WITH_DESCRIPTION, $str_PROPERTY_ACTIVE;
					global $str_PROPERTY_SEARCHABLE, $str_PROPERTY_FILTRABLE;
					global $str_PROPERTY_USER_TYPE, $str_PROPERTY_USER_TYPE_SETTINGS;

					if($i>4) return false;

					$str_PROPERTY_ID = "n".$i;
					$str_PROPERTY_NAME = "";
					$str_PROPERTY_ACTIVE = "Y";
					$str_PROPERTY_MULTIPLE = "N";
					$str_PROPERTY_MULTIPLE_CNT = "5";
					$str_PROPERTY_IS_REQUIRED = "N";
					$str_PROPERTY_DEFAULT_VALUE = "";
					$str_PROPERTY_XML_ID = "";
					$str_PROPERTY_PROPERTY_TYPE = "S";
					$str_PROPERTY_USER_TYPE = "";
					$str_PROPERTY_USER_TYPE_SETTINGS = "";
					$str_PROPERTY_CODE = "";
					$str_PROPERTY_SORT = "500";
					$str_PROPERTY_ROW_COUNT = "1";
					$str_PROPERTY_COL_COUNT = "30";
					$str_PROPERTY_LINK_IBLOCK_ID = "";
					$str_PROPERTY_WITH_DESCRIPTION = "";
					$str_PROPERTY_FILTRABLE = "";
					$str_PROPERTY_SEARCHABLE = "";

					$i++;

					return true;
				}

				while($r = _GetOldAndNew($props)):

					if($bVarsFromForm)
					{
						${"PROPERTY_MULTIPLE_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_MULTIPLE"}!="Y"?"N":"Y");
						${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!=="Y"?"N":"Y");
						${"PROPERTY_IS_REQUIRED_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_IS_REQUIRED"}!="Y"?"N":"Y");
						${"PROPERTY_DEL_".$str_PROPERTY_ID} = (${"PROPERTY_".$str_PROPERTY_ID."_DEL"}!="Y"?"N":"Y");
						if(strpos(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, ":")!==false)
						{
							list(${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"},${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"})=explode(":", ${"PROPERTY_".$str_PROPERTY_ID."_PROPERTY_TYPE"}, 2);
						}
						else
							${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE"}="";

						echo ${"PROPERTY_$str_PROPERTY_ID_TYPE"};
						$tmp_PROP_ID = $str_PROPERTY_ID;

						$DB->InitTableVarsForEdit("b_iblock_property", "PROPERTY_".$str_PROPERTY_ID."_", "str_PROPERTY_");
						$str_PROPERTY_ID = $tmp_PROP_ID;
						if(is_array(${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"}))
							$str_PROPERTY_DEFAULT_VALUE = ${"PROPERTY_".$str_PROPERTY_ID."_DEFAULT_VALUE"};
						if(is_array(${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE_SETTINGS"}))
							$str_PROPERTY_USER_TYPE_SETTINGS = ${"PROPERTY_".$str_PROPERTY_ID."_USER_TYPE_SETTINGS"};
					}
				?>
					<tr>
						<td><?echo ($str_PROPERTY_ID>0?$str_PROPERTY_ID:"")?></td>
						<td>
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILE_TYPE" value="<?echo $str_PROPERTY_FILE_TYPE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_LIST_TYPE" value="<?echo $str_PROPERTY_LIST_TYPE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ROW_COUNT" value="<?echo $str_PROPERTY_ROW_COUNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_COL_COUNT" value="<?echo $str_PROPERTY_COL_COUNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_LINK_IBLOCK_ID" value="<?echo $str_PROPERTY_LINK_IBLOCK_ID?>">
<?if(is_array($str_PROPERTY_DEFAULT_VALUE)):?>
	<?foreach($str_PROPERTY_DEFAULT_VALUE as $key=>$value):?>
		<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE[<?=htmlspecialchars($key)?>]" value="<?=htmlspecialchars($value)?>">
	<?endforeach?>
<?else:?>
	<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEFAULT_VALUE" value="<?echo $str_PROPERTY_DEFAULT_VALUE?>">
<?endif?>
<?if(is_array($str_PROPERTY_USER_TYPE_SETTINGS)):?>
	<?foreach($str_PROPERTY_USER_TYPE_SETTINGS as $key=>$value):?>
		<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_USER_TYPE_SETTINGS[<?=htmlspecialchars($key)?>]" value="<?=htmlspecialchars($value)?>">
	<?endforeach?>
<?else:?>
	<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_USER_TYPE_SETTINGS" value="<?echo $str_PROPERTY_USER_TYPE_SETTINGS?>">
<?endif?>
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_WITH_DESCRIPTION" value="<?echo $str_PROPERTY_WITH_DESCRIPTION?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_SEARCHABLE" value="<?echo $str_PROPERTY_SEARCHABLE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_FILTRABLE" value="<?echo $str_PROPERTY_FILTRABLE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_ACTIVE" value="<?echo $str_PROPERTY_ACTIVE?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE_CNT" value="<?echo $str_PROPERTY_MULTIPLE_CNT?>">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_XML_ID" value="<?echo $str_PROPERTY_XML_ID?>">
							<?
							if($str_PROPERTY_PROPERTY_TYPE=="L")
							{
								$arPROPERTY_VALUES = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES"};
								if(is_array($arPROPERTY_VALUES))
								{
									foreach($arPROPERTY_VALUES as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES[<?echo htmlspecialchars($key)?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_DEF = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_DEF"};
								if(is_array($arPROPERTY_VALUES_DEF))
								{
									foreach($arPROPERTY_VALUES_DEF as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_DEF[<?echo htmlspecialchars($key)?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_XML = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_XML"};
								if(is_array($arPROPERTY_VALUES_XML))
								{
									foreach($arPROPERTY_VALUES_XML as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_XML[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								$arPROPERTY_VALUES_SORT = ${"PROPERTY_".$str_PROPERTY_ID."_VALUES_SORT"};
								if(is_array($arPROPERTY_VALUES_SORT))
								{
									foreach($arPROPERTY_VALUES_SORT as $key=>$value)
									{
										if(strlen($value)<=0)
											continue;
										?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_VALUES_SORT[<?=$key?>]" value="<?=htmlspecialchars($value)?>"><?
									}
								}

								if(IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"})>0):
									?><input type="hidden" name="PROPERTY_<?=$str_PROPERTY_ID?>_CNT" value="<?=IntVal(${"PROPERTY_".$str_PROPERTY_ID."_CNT"})?>"><?
								endif;
							}
							?>
							<input type="text" size="20"  maxlength="50" name="PROPERTY_<?echo $str_PROPERTY_ID?>_NAME" value="<?echo $str_PROPERTY_NAME?>">
						</td>
						<td>
						<select name="PROPERTY_<?echo $str_PROPERTY_ID?>_PROPERTY_TYPE" >
							<option value="S" <?if($str_PROPERTY_PROPERTY_TYPE=="S" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_S")?></option>
							<option value="N" <?if($str_PROPERTY_PROPERTY_TYPE=="N" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_N")?></option>
							<option value="L" <?if($str_PROPERTY_PROPERTY_TYPE=="L" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_L")?></option>
							<option value="F" <?if($str_PROPERTY_PROPERTY_TYPE=="F" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_F")?></option>
							<option value="G" <?if($str_PROPERTY_PROPERTY_TYPE=="G" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_G")?></option>
							<option value="E" <?if($str_PROPERTY_PROPERTY_TYPE=="E" && !$str_PROPERTY_USER_TYPE)echo " selected"?>><?echo GetMessage("IB_E_PROP_TYPE_E")?></option>
							<?foreach(CIBlockProperty::GetUserType() as  $ar):?>
								<option value="<?=htmlspecialchars($ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"])?>" <?if($str_PROPERTY_PROPERTY_TYPE==$ar["PROPERTY_TYPE"] && $str_PROPERTY_USER_TYPE==$ar["USER_TYPE"])echo " selected"?>><?=htmlspecialchars($ar["DESCRIPTION"])?></option>
							<?endforeach;?>
						</select>
						</td>
						<td align="center">
						<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="N">
						<input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_MULTIPLE" value="Y"<?if($str_PROPERTY_MULTIPLE=="Y")echo " checked"?>>
						</td>
						<td align="center">
							<input type="hidden" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="N">
							<input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_IS_REQUIRED" value="Y"<?if($str_PROPERTY_IS_REQUIRED=="Y")echo " checked"?>>
						</td>
						<td>
							<input type="text" size="3" maxlength="10"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_SORT" value="<?echo $str_PROPERTY_SORT?>">
						</td>
						<td><input type="text" size="15" maxlength="20"  name="PROPERTY_<?echo $str_PROPERTY_ID?>_CODE" value="<?echo $str_PROPERTY_CODE?>"></td>
						<td><input type="submit" title="<?echo GetMessage("IB_E_PROP_EDIT_TITLE")?>" name="propedit[<?echo $str_PROPERTY_ID?>]"  value="..."></td>
						<td><?if(intval($str_PROPERTY_ID)>0):?><input type="checkbox" name="PROPERTY_<?echo $str_PROPERTY_ID?>_DEL" value="Y"><?endif?></td>
					</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td valign="top" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center">
				<tr class="heading">
					<td nowrap><?echo GetMessage("IB_E_SECTION_FIELD_NAME")?></td>
					<td nowrap><?echo GetMessage("IB_E_SECTION_FIELD_IS_REQUIRED")?></td>
					<td nowrap><?echo GetMessage("IB_E_SECTION_FIELD_DEFAULT_VALUE")?></td>
				</tr>
				<?
				if($bVarsFromForm)
					$arFields = $_REQUEST["FIELDS"];
				else
					$arFields = CIBlock::GetFields($ID);
				$arDefFields = CIBlock::GetFieldsDefaults();
				foreach($arDefFields as $FIELD_ID => $arField):
					if(!preg_match("/^SECTION_/", $FIELD_ID)) continue;
					?>
					<tr valign="top">
						<td nowrap><?echo $arDefFields[$FIELD_ID]["NAME"]?></td>
						<td nowrap align="center">
							<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
							<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
						</td>
						<td nowrap>
						<?
						switch($FIELD_ID)
						{
							case "SECTION_NAME":
								?>
								<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
								<?
								break;
							case "SECTION_DESCRIPTION_TYPE":
								?>
								<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
									<option value="text" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="text") echo "selected"?>>text</option>
									<option value="html" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="html") echo "selected"?>>html</option>
								</select>
								<?
								break;
							case "SECTION_DESCRIPTION":
								?>
								<textarea name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" rows="5" cols="47"><?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
								<?
								break;
							case "SECTION_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								<tr><td><input type="checkbox" value="resample" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>" size="7"></td></tr>
								</table>
								<?
								break;
							case "SECTION_DETAIL_PICTURE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7"></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label></td></tr>
								<tr><td><input type="checkbox" value="resample" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label></td></tr>
								<tr><td><?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]" type="text" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>" size="7"></td></tr>
								</table>
								<?
								break;
							case "SECTION_CODE":
								?>
								<table border="0" cellspacing="2" cellpadding="0">
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"><?echo GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"><?echo GetMessage("IB_E_FIELD_SEC_TRANSLITERATION")?></label></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"><?echo GetMessage("IB_E_FIELD_TRANS_LEN")?></label>&nbsp;<input type="text" size="4" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]"><?echo GetMessage("IB_E_FIELD_TRANS_CASE")?></label>&nbsp;<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]">
									<option value=""><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE")?></option>
									<option value="L" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="L") echo "selected"?>><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?></option>
									<option value="U" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="U") echo "selected"?>><?echo GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?></option>
								</select><td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"><?echo GetMessage("IB_E_FIELD_TRANS_SPACE")?></label>&nbsp;<input type="text" size="2" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"></td></tr>
								<tr><td><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"><?echo GetMessage("IB_E_FIELD_TRANS_OTHER")?></label>&nbsp;<input type="text" size="2" value="<?echo htmlspecialchars($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"><?echo GetMessage("IB_E_FIELD_TRANS_EAT")?></label></td></tr>
								<tr><td><input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y") echo "checked"?>><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"><?echo GetMessage("IB_E_FIELD_EL_TRANS_USE_GOOGLE")?></label></td></tr>
								</table>
								<?
								break;
							default:
								?>
								<input type="hidden" value="" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]">&nbsp;
								<?
								break;
						}
						?>
						</td>
					</tr>
				<?endforeach?>
			</table>
		</td>
	</tr>
<?
if($bTab3):
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td valign="top"  width="40%"><label for="RSS_ACTIVE"><?echo GetMessage("IB_E_RSS_ACTIVE")?></label></td>
		<td valign="top" width="60%">
			<input type="hidden" name="RSS_ACTIVE" value="N">
			<input type="checkbox" id="RSS_ACTIVE" name="RSS_ACTIVE" value="Y"<?if($str_RSS_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IB_E_RSS_TTL")?></td>
		<td valign="top">
			<input type="text" name="RSS_TTL" size="20"  maxlength="40" value="<?echo $str_RSS_TTL?>">
		</td>
	</tr>

	<tr>
		<td valign="top"><label for="RSS_FILE_ACTIVE"><?echo GetMessage("IB_E_RSS_FILE_ACTIVE")?></label></td>
		<td valign="top">
			<input type="hidden" name="RSS_FILE_ACTIVE" value="N">
			<input type="checkbox" id="RSS_FILE_ACTIVE" name="RSS_FILE_ACTIVE" value="Y"<?if($str_RSS_FILE_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top"  ><? echo GetMessage("IB_E_RSS_FILE_LIMIT")?></td>
		<td valign="top"  >
			<input type="text" name="RSS_FILE_LIMIT"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_LIMIT?>">
		</td>
	</tr>
	<tr>
		<td valign="top" ><? echo GetMessage("IB_E_RSS_FILE_DAYS")?></td>
		<td valign="top">
			<input type="text" name="RSS_FILE_DAYS"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_DAYS?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><label for="RSS_YANDEX_ACTIVE"><?echo GetMessage("IB_E_RSS_YANDEX_ACTIVE")?></label></td>
		<td valign="top">
			<input type="hidden" name="RSS_YANDEX_ACTIVE" value="N">
			<input type="checkbox" id="RSS_YANDEX_ACTIVE" name="RSS_YANDEX_ACTIVE" value="Y"<?if($str_RSS_YANDEX_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_RSS_TITLE")?>:</td>
	</tr>
	<tr>
		<td valign="top"  colspan="2" align="center">
			<table>
				<tr class="heading">
					<td><?echo GetMessage("IB_E_RSS_FIELD")?></td>
					<td><?echo GetMessage("IB_E_RSS_TEMPL")?></td>
				</tr>
				<?
				$arCurNodesRSS = CIBlockRSS::GetNodeList(IntVal($ID));
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach($arNodesRSS as $key => $val):
					if($bVarsFromForm)
						$DB->InitTableVarsForEdit("b_iblock_rss", "RSS_", "str_RSS_", "_".$key);
					?>
					<tr>
						<td>
							<input type="text"  size="15" readonly maxlength="50" name="RSS_NODE_<?echo $key?>" value="<?echo $val?>">
						</td>
						<td><input type="text"  name="RSS_NODE_VALUE_<?echo $key?>" value="<?echo $arCurNodesRSS[$val]?>"></td>
					</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
	<?
endif;

$tabControl->BeginNextTab();
?>
	<?
	if ($bWorkflow && $str_WORKFLOW=="Y") :
		$arPermType = Array(
			"D"=>GetMessage("IB_E_ACCESS_D"),
			"R"=>GetMessage("IB_E_ACCESS_R"),
			"U"=>GetMessage("IB_E_ACCESS_U"),
			"W"=>GetMessage("IB_E_ACCESS_W"),
			"X"=>GetMessage("IB_E_ACCESS_X"));
	elseif ($bBizprocTab) :
		$arPermType = Array(
			"D"=>GetMessage("IB_E_ACCESS_D"),
			"R"=>GetMessage("IB_E_ACCESS_R"),
			"U"=>GetMessage("IB_E_ACCESS_U2"),
			"W"=>GetMessage("IB_E_ACCESS_W"),
			"X"=>GetMessage("IB_E_ACCESS_X"));
	else :
		$arPermType = Array(
			"D"=>GetMessage("IB_E_ACCESS_D"),
			"R"=>GetMessage("IB_E_ACCESS_R"),
			"W"=>GetMessage("IB_E_ACCESS_W"),
			"X"=>GetMessage("IB_E_ACCESS_X"));
	endif;
	$perm = $ib->GetGroupPermissions($ID);
	if(!array_key_exists(1, $perm))
		$perm[1] = "X";
	?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_DEFAULT_ACCESS_TITLE")?></td>
	</tr>
	<tr>
		<td valign="top" nowrap width="40%"><?echo GetMessage("IB_E_EVERYONE")?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=2&amp;lang=<?=LANGUAGE_ID?>">2</a>]:</td>
		<td valign="top" width="60%">

				<select name="GROUP[2]" id="group_2">
				<?
				if($bVarsFromForm)
					$strSelected = $GROUP[2];
				else
					$strSelected = $perm[2];
				foreach($arPermType as $key => $val):
				?>
					<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
				<?endforeach?>
				</select>

				<script language="JavaScript">
				function OnGroupChange(control, message)
				{
					var all = document.getElementById('group_2');
					var msg = document.getElementById(message);
					if(all && all.value >= control.value && control.value != '')
					{
						if(msg) msg.innerHTML = '<?echo CUtil::JSEscape(GetMessage("IB_E_ACCESS_WARNING"))?>';
					}
					else
					{
						if(msg) msg.innerHTML = '';
					}
				}
				</script>

		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_GROUP_ACCESS_TITLE")?></td>
	</tr>
	<?
	$groups = CGroup::GetList($by="sort", $order="asc", Array("ID"=>"~2"));
	while($r = $groups->GetNext()):
		if($bVarsFromForm)
			$strSelected = $GROUP[$r["ID"]];
		else
			$strSelected = $perm[$r["ID"]];

		if($strSelected=="U" && !CModule::IncludeModule("workflow"))
			$strSelected="R";

		if($strSelected!="R" &&
			$strSelected!="U" &&
			$strSelected!="W" &&
			$strSelected!="X" &&
			$ID>0 && !$bVarsFromForm)
				$strSelected="";
		?>
	<tr>
		<td valign="top" nowrap width="40%"><?echo $r["NAME"]?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=<?=$r["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$r["ID"]?></a>]:</td>
		<td valign="top" width="60%">

				<select name="GROUP[<?echo $r["ID"]?>]" OnChange="OnGroupChange(this, 'spn_group_<?echo $r["ID"]?>');">
					<option value=""><?echo GetMessage("IB_E_DEFAULT_ACCESS")?></option>
				<?
				foreach($arPermType as $key => $val):
				?>
					<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
				<?endforeach?>
				</select>
				<span id="spn_group_<?echo $r["ID"]?>"></span>
		</td>
	</tr>
	<?endwhile?>
	<?
$tabControl->BeginNextTab();
	$arMessages = CIBlock::GetMessages($ID);
	if($bVarsFromForm)
	{
		foreach($arMessages as $MESSAGE_ID => $MESSAGE_TEXT)
			$arMessages[$MESSAGE_ID] = $_REQUEST[$MESSAGE_ID];
	}
	if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTIONS_NAME")?></td>
		<td valign="top">
			<input type="text" name="SECTIONS_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTIONS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_NAME")?></td>
		<td valign="top">
			<input type="text" name="SECTION_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_ADD")?></td>
		<td valign="top">
			<input type="text" name="SECTION_ADD" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_EDIT")?></td>
		<td valign="top">
			<input type="text" name="SECTION_EDIT" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_SECTION_DELETE")?></td>
		<td valign="top">
			<input type="text" name="SECTION_DELETE" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["SECTION_DELETE"])?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENTS_NAME")?></td>
		<td valign="top">
			<input type="text" name="ELEMENTS_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENTS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_NAME")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_NAME" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_ADD")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_ADD" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_EDIT")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_EDIT" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IB_E_ELEMENT_DELETE")?></td>
		<td valign="top">
			<input type="text" name="ELEMENT_DELETE" size="20" maxlength="100" value="<?echo htmlspecialchars($arMessages["ELEMENT_DELETE"])?>">
		</td>
	</tr>
	<?
if ($bBizprocTab):
$tabControl->BeginNextTab();

	if (!isset($arWorkflowTemplates))
		$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID));
	?>
	<tr>
		<td valign="top" colspan="2">
			<?if (count($arWorkflowTemplates) > 0):?>
				<table border="0" cellspacing="0" cellpadding="0" class="internal">
					<tr class="heading">
						<td><?echo GetMessage("IB_E_BP_NAME")?></td>
						<td><?echo GetMessage("IB_E_BP_CHANGED")?></td>
						<td><?echo GetMessage("IB_E_BP_AUTORUN")?></td>
					</tr>
					<?
					foreach ($arWorkflowTemplates as $arTemplate)
					{
						?>
						<tr>
							<td valign="top">
								<?if(IsModuleInstalled("bizprocdesigner")):?>
									<a href="/bitrix/admin/iblock_bizproc_workflow_edit.php?document_type=iblock_<?= $ID ?>&lang=<?=LANGUAGE_ID?>&ID=<?=$arTemplate["ID"]?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", array()))?>" target="_blank"><?= $arTemplate["NAME"] ?> [<?=$arTemplate["ID"]?>]</a>
								<?else:?>
									<?= $arTemplate["NAME"] ?>
								<?endif?>
								<br /><small><?= $arTemplate["DESCRIPTION"] ?></small></td>
							<td valign="top"><?= $arTemplate["MODIFIED"] ?><br />[<a href="user_edit.php?ID=<?= $arTemplate["USER_ID"] ?>"><?= $arTemplate["USER_ID"] ?></a>] <?= $arTemplate["USER"] ?></td>
							<td valign="top">
								<?
									if($bVarsFromForm)
										$checked = $_REQUEST["create_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 1) != 0;
								?>
								<label><input type="checkbox" id="id_create_bizproc_<?= $arTemplate["ID"] ?>" name="create_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?echo $checked? " checked" : ""?>><?echo GetMessage("IB_E_BP_AUTORUN_CREATE")?></label><br />
								<?
									if($bVarsFromForm)
										$checked = $_REQUEST["edit_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 2) != 0;
								?>
								<label><input type="checkbox" id="id_edit_bizproc_<?= $arTemplate["ID"] ?>" name="edit_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?echo $checked? " checked" : ""?>><?echo GetMessage("IB_E_BP_AUTORUN_UPDATE")?></label><br />
							</td>
						</tr>
						<?
					}
					?>
				</table>
				<br>
			<?endif;?>
			<?if(IsModuleInstalled("bizprocdesigner")):?>
			<a href="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_<?= $ID ?>&lang=<?=LANGUAGE_ID?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", array())) ?>" target="_blank"><?echo GetMessage("IB_E_GOTO_BP")?></a>
			<?endif?>
		</td>
	</tr>
	<?
endif;

$tabControl->BeginNextTab();
	if($bVarsFromForm)
		$arFields = $_REQUEST["FIELDS"];
	else
		$arFields = CIBlock::GetFields($ID);
	$arDefFields = CIBlock::GetFieldsDefaults();
	foreach($arDefFields as $FIELD_ID => $arField):
		if(!preg_match("/^LOG_/", $FIELD_ID)) continue;
		?>
		<tr valign="top">
			<td width="40%"><label for="<?echo $FIELD_ID?>"><?echo GetMessage("IB_E_".$FIELD_ID)?></label>:</td>
			<td>
				<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
			</td>
		</tr>
	<?endforeach?>
<?
	$tabControl->Buttons(array("disabled"=>false, "back_url"=>'iblock_admin.php?lang='.$lang.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N")));
	$tabControl->End();
	?>
<?endif //if(IntVal($str_PROPERTY_ID)>0 || (strlen($str_PROPERTY_ID)>0 && $str_PROPERTY_ID[0]=="n")):?>
</form>

<?else: //if($Perm<="X"):?>
<br>
<?echo ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>

<?
endif;

else: //if($arIBTYPE!==false):?>
<br>
<?echo ShowError(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));?>

<?
endif;// if($arIBTYPE!==false):

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
