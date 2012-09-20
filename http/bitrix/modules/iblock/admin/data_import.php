<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

set_time_limit(0);
$IBLOCK_ID = IntVal($IBLOCK_ID);
$STEP = IntVal($STEP);
if ($STEP <= 0)
	$STEP = 1;
if ($REQUEST_METHOD=="POST" && strlen($backButton) > 0)
	$STEP = $STEP - 2;
if ($REQUEST_METHOD == "POST" && strlen($backButton2) > 0)
	$STEP = 1;

$NUM_CATALOG_LEVELS = 3;

$max_execution_time = IntVal($max_execution_time);
if ($max_execution_time <= 0)
	$max_execution_time = 0;

if (strlen($CUR_LOAD_SESS_ID) <= 0)
	$CUR_LOAD_SESS_ID = "CL".time();
$bAllLinesLoaded = True;
$CUR_FILE_POS = IntVal($CUR_FILE_POS);
$strError = "";

/////////////////////////////////////////////////////////////////////

$arCatalogAvailProdFields = array(
		array("value"=>"IE_XML_ID", "field"=>"XML_ID", "important"=>"Y", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_UNIXML")." (B_IBLOCK_ELEMENT.XML_ID)"),
		array("value"=>"IE_NAME", "field"=>"NAME", "important"=>"Y", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_NAME")." (B_IBLOCK_ELEMENT.NAME)"),
		array("value"=>"IE_ACTIVE", "field"=>"ACTIVE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_ACTIV")." (B_IBLOCK_ELEMENT.ACTIVE)"),
		array("value"=>"IE_ACTIVE_FROM", "field"=>"ACTIVE_FROM", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_ACTIVFROM")." (B_IBLOCK_ELEMENT.ACTIVE_FROM)"),
		array("value"=>"IE_ACTIVE_TO", "field"=>"ACTIVE_TO", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_ACTIVTO")." (B_IBLOCK_ELEMENT.ACTIVE_TO)"),
		array("value"=>"IE_SORT", "field"=>"SORT", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_SORT")." (B_IBLOCK_ELEMENT.SORT)"),
		array("value"=>"IE_PREVIEW_PICTURE", "field"=>"PREVIEW_PICTURE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_CATIMG")." (B_IBLOCK_ELEMENT.PREVIEW_PICTURE)"),
		array("value"=>"IE_PREVIEW_TEXT", "field"=>"PREVIEW_TEXT", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_CATDESCR")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT)"),
		array("value"=>"IE_PREVIEW_TEXT_TYPE", "field"=>"PREVIEW_TEXT_TYPE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_CATDESCRTYPE")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT_TYPE)"),
		array("value"=>"IE_DETAIL_PICTURE", "field"=>"DETAIL_PICTURE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_DETIMG")." (B_IBLOCK_ELEMENT.DETAIL_PICTURE)"),
		array("value"=>"IE_DETAIL_TEXT", "field"=>"DETAIL_TEXT", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_DETDESCR")." (B_IBLOCK_ELEMENT.DETAIL_TEXT)"),
		array("value"=>"IE_DETAIL_TEXT_TYPE", "field"=>"DETAIL_TEXT_TYPE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_DETDESCRTYPE")." (B_IBLOCK_ELEMENT.DETAIL_TEXT_TYPE)"),
		array("value"=>"IE_CODE", "field"=>"CODE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_CODE")." (B_IBLOCK_ELEMENT.CODE)"),
		array("value"=>"IE_TAGS", "field"=>"TAGS", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_TAGS")." (B_IBLOCK_ELEMENT.TAGS)"),
		array("value"=>"IE_ID", "field"=>"ID", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FI_ID")." (B_IBLOCK_ELEMENT.ID)")
	);
$defCatalogAvailProdFields = "IE_XML_ID,IE_NAME,IE_PREVIEW_PICTURE,IE_PREVIEW_TEXT,IE_PREVIEW_TEXT_TYPE,IE_DETAIL_PICTURE,IE_DETAIL_TEXT,IE_DETAIL_TEXT_TYPE,IE_ACTIVE,IE_ACTIVE_FROM,IE_ACTIVE_TO,IE_SORT,IE_CODE,IE_TAGS";

$arCatalogAvailGroupFields = array(
		array("value"=>"IC_XML_ID", "field"=>"XML_ID", "important"=>"Y", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_UNIXML")." (B_IBLOCK_SECTION.XML_ID)"),
		array("value"=>"IC_GROUP", "field"=>"NAME", "important"=>"Y", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_NAME")." (B_IBLOCK_SECTION.NAME)"),
		array("value"=>"IC_ACTIVE", "field"=>"ACTIVE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_ACTIV")." (B_IBLOCK_SECTION.ACTIVE)"),
		array("value"=>"IC_SORT", "field"=>"SORT", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_SORT")." (B_IBLOCK_SECTION.SORT)"),
		array("value"=>"IC_DESCRIPTION", "field"=>"DESCRIPTION", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_DESCR")." (B_IBLOCK_SECTION.DESCRIPTION)"),
		array("value"=>"IC_DESCRIPTION_TYPE", "field"=>"DESCRIPTION_TYPE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_DESCRTYPE")." (B_IBLOCK_SECTION.DESCRIPTION_TYPE)"),
		array("value"=>"IC_CODE", "field"=>"CODE", "important"=>"N", "name"=>GetMessage("IBLOCK_ADM_IMP_FG_CODE")." (B_IBLOCK_SECTION.CODE)")
	);
$defCatalogAvailGroupFields = "IC_GROUP,IC_XML_ID,IC_ACTIVE,IC_SORT,IC_DESCRIPTION,IC_DESCRIPTION_TYPE,IC_CODE";

/////////////////////////////////////////////////////////////////////

function GetValueByCodeTmp($code)
{
	global $NUM_FIELDS;
	for ($i = 0; $i < $NUM_FIELDS; $i++)
	{
		if ($GLOBALS["field_".$i] == $code)
		{
			return $i;
		}
	}
	return -1;
}

/////////////////////////////////////////////////////////////////////
if (($REQUEST_METHOD == "POST" || $CUR_FILE_POS > 0) && $STEP > 1 && check_bitrix_sessid())
{
	//*****************************************************************//
	if ($STEP > 1)
	{
		//*****************************************************************//
		$DATA_FILE_NAME = "";

		if (is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
		{
			if(strtolower(GetFileExtension($_FILES["DATA_FILE"]["name"])) != "csv")
				$strError .= GetMessage("IBLOCK_ADM_IMP_NOT_CSV")."<br>";
			else
			{
				$DATA_FILE_NAME = "/upload/".basename($_FILES["DATA_FILE"]["name"]);
				if($APPLICATION->GetFileAccessPermission($DATA_FILE_NAME)>="W")
					copy($_FILES["DATA_FILE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);
				else
					$DATA_FILE_NAME = "";
			}
		}

		if (strlen($strError) <= 0)
		{
			if(strlen($DATA_FILE_NAME) <= 0)
			{
				if (strlen($URL_DATA_FILE) > 0)
				{
					$URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)), "/");
					$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$URL_DATA_FILE);
					if(
						(strlen($FILE_NAME) > 1) &&
						($FILE_NAME === "/".$URL_DATA_FILE) &&
						file_exists($_SERVER["DOCUMENT_ROOT"].$FILE_NAME) &&
						is_file($_SERVER["DOCUMENT_ROOT"].$FILE_NAME) &&
						($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")
					)
					{
						$DATA_FILE_NAME = $FILE_NAME;
					}
				}
			}

			if (strlen($DATA_FILE_NAME) <= 0)
				$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DATA_FILE")."<br>";

			$arIBlockres = CIBlock::GetList(
				array("sort" => "asc"),
				array(
					"ID" => $IBLOCK_ID,
					"MIN_PERMISSION" => "W"
				)
			);
			$arIBlockres = new CIBlockResult($arIBlockres);
			if ($IBLOCK_ID<=0 || !($arIBlock = $arIBlockres->GetNext()))
				$strError .= GetMessage("IBLOCK_ADM_IMP_NO_IBLOCK")."<br>";
		}

		if (strlen($strError) <= 0)
		{
			if ($CUR_FILE_POS>0 && is_set($_SESSION, $CUR_LOAD_SESS_ID) && is_set($_SESSION[$CUR_LOAD_SESS_ID], "LOAD_SCHEME"))
			{
				parse_str($_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"]);
				$STEP = 4;
			}
		}

		if (strlen($strError)>0)
			$STEP = 1;
		//*****************************************************************//
	}

	if ($STEP > 2)
	{
		//*****************************************************************//
		$csvFile = new CCSVData();
		$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

		if ($fields_type!="F" && $fields_type!="R")
			$strError .= GetMessage("IBLOCK_ADM_IMP_NO_FILE_FORMAT")."<br>";

		$arDataFileFields = array();
		if (strlen($strError)<=0)
		{
			$fields_type = (($fields_type=="F") ? "F" : "R" );

			$csvFile->SetFieldsType($fields_type);

			if ($fields_type == "R")
			{
				$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_r=="Y")?true:false);

				$delimiter_r_char = "";
				switch ($delimiter_r)
				{
					case "TAB":
						$delimiter_r_char = "\t";
						break;
					case "ZPT":
						$delimiter_r_char = ",";
						break;
					case "SPS":
						$delimiter_r_char = " ";
						break;
					case "OTR":
						$delimiter_r_char = substr($delimiter_other_r, 0, 1);
						break;
					case "TZP":
						$delimiter_r_char = ";";
						break;
				}

				if (strlen($delimiter_r_char)!=1)
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DELIMITER")."<br>";

				if (strlen($strError)<=0)
				{
					$csvFile->SetDelimiter($delimiter_r_char);
				}
			}
			else
			{
				$first_names_f = (($first_names_f=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_f=="Y")?true:false);

				if (strlen($metki_f)<=0)
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_METKI")."<br>";

				if (strlen($strError)<=0)
				{
					$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

					$arMetki = array();
					for ($i = 0; $i < count($arMetkiTmp); $i++)
					{
						if (IntVal($arMetkiTmp[$i])>0)
						{
							$arMetki[] = IntVal($arMetkiTmp[$i]);
						}
					}

					if (!is_array($arMetki) || count($arMetki)<1)
						$strError .= GetMessage("IBLOCK_ADM_IMP_NO_METKI")."<br>";

					if (strlen($strError)<=0)
					{
						$csvFile->SetWidthMap($arMetki);
					}

				}
			}

			if (strlen($strError)<=0)
			{
				$bFirstHeaderTmp = $csvFile->GetFirstHeader();
				$csvFile->SetFirstHeader(false);
				if ($arRes = $csvFile->Fetch())
				{
					for ($i = 0; $i < count($arRes); $i++)
					{
						$arDataFileFields[$i] = $arRes[$i];
					}
				}
				else
				{
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DATA")."<br>";
				}
				$NUM_FIELDS = count($arDataFileFields);
			}
		}

		if (strlen($strError)>0)
			$STEP = 2;
		//*****************************************************************//
	}

	if ($STEP > 3)
	{
		//*****************************************************************//
		$bFieldsPres = False;
		for ($i = 0; $i < $NUM_FIELDS; $i++)
		{
			if (strlen(${"field_".$i})>0)
			{
				$bFieldsPres = True;
				break;
			}
		}
		if (!$bFieldsPres)
			$strError .= GetMessage("IBLOCK_ADM_IMP_NO_FIELDS")."<br>";

		if (strlen($strError)<=0)
		{
			$csvFile->SetPos($CUR_FILE_POS);
			if ($CUR_FILE_POS<=0 && $bFirstHeaderTmp)
			{
				$arRes = $csvFile->Fetch();
			}

			$bs = new CIBlockSection;
			$el = new CIBlockElement;
			$el->CancelWFSetMove();

			$tmpid = md5(uniqid(""));
			$line_num = 0;
			$correct_lines = 0;
			$error_lines = 0;
			$killed_lines = 0;
			$arIBlockProperty = array();
			$arIBlockFileProperty = array();
			$arIBlockPropertyValue = array();
			$bThereIsGroups = False;
			$arProductGroups = array();
			if ($CUR_FILE_POS>0 && is_set($_SESSION, $CUR_LOAD_SESS_ID))
			{
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "tmpid"))
					$tmpid = $_SESSION[$CUR_LOAD_SESS_ID]["tmpid"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "line_num"))
					$line_num = $_SESSION[$CUR_LOAD_SESS_ID]["line_num"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "correct_lines"))
					$correct_lines = $_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "error_lines"))
					$error_lines = $_SESSION[$CUR_LOAD_SESS_ID]["error_lines"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "killed_lines"))
					$killed_lines = $_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockProperty"))
					$arIBlockProperty = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockFileProperty"))
					$arIBlockFileProperty = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockFileProperty"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockPropertyValue"))
					$arIBlockPropertyValue = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockPropertyValue"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "bThereIsGroups"))
					$bThereIsGroups = $_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arProductGroups"))
					$arProductGroups = $_SESSION[$CUR_LOAD_SESS_ID]["arProductGroups"];
			}

			// Prepare arrays for sections loading
			$strAvailGroupFields = $defCatalogAvailGroupFields;
			$arAvailGroupFields = explode(",", $strAvailGroupFields);
			$arAvailGroupFields_names = array();
			for ($i = 0; $i < count($arAvailGroupFields); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
				{
					if ($arCatalogAvailGroupFields[$j]["value"]==$arAvailGroupFields[$i])
					{
						$arAvailGroupFields_names[$arAvailGroupFields[$i]] = array(
							"field" => $arCatalogAvailGroupFields[$j]["field"],
							"important" => $arCatalogAvailGroupFields[$j]["important"]
							);
						break;
					}
				}
			}

			// Prepare arrays for elements load
			$strAvailProdFields = $defCatalogAvailProdFields;
			$arAvailProdFields = explode(",", $strAvailProdFields);
			$arAvailProdFields_names = array();
			for ($i = 0; $i < count($arAvailProdFields); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailProdFields); $j++)
				{
					if ($arCatalogAvailProdFields[$j]["value"]==$arAvailProdFields[$i])
					{
						$arAvailProdFields_names[$arAvailProdFields[$i]] = array(
							"field" => $arCatalogAvailProdFields[$j]["field"],
							"important" => $arCatalogAvailProdFields[$j]["important"]
							);
						break;
					}
				}
			}

			$arSectionCache = array();
			$arEnumCache = array();
			$bWorkFlow = CModule::IncludeModule('workflow');

function FetchAssoc(&$csvFile)
{
	global $NUM_FIELDS;
	$ar = $csvFile->Fetch();
	if($ar)
	{
		$result = array();
		for($i = 0; $i < $NUM_FIELDS; $i++)
			$result[$GLOBALS["field_".$i]] = trim($ar[$i]);
		return $result;
	}
	return $ar;
}
			$PREV_PRODUCT_ID = false;
			// Main loop
			while($arRes = FetchAssoc($csvFile))
			{
				$strErrorR = "";
				$line_num++;

				// this array is path to element
				$arGroupsTmp = array();
				for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
				{
					$bOK = false; //will be true when at least one important field met
					$arGroupsTmp1 = array(
						"TMP_ID" => $tmpid,
					);
					foreach ($arAvailGroupFields_names as $key => $value)
					{
						if(array_key_exists($key.$i, $arRes))
						{
							$arGroupsTmp1[$value["field"]] = $arRes[$key.$i];
							$bThereIsGroups = True;
						}
						if($value["important"]=="Y" && strlen($arGroupsTmp1[$value["field"]])>0)
							$bOK = true;
					}
					// drop empty target sections
					if($bOK)
					{
						// When group does not have name  "<Empty name>"
						if(strlen($arGroupsTmp1["NAME"])<=0)
							$arGroupsTmp1["NAME"] = GetMessage("IBLOCK_ADM_IMP_NOMAME");
						$arGroupsTmp[] = $arGroupsTmp1;
					}
					else
						break;
				}

				// Create sections tree. Save section code for elemet insertions
				$LAST_GROUP_CODE = 0;
				foreach($arGroupsTmp as $i => $arGroup)
				{
					$arFilter = array(
						"IBLOCK_ID" => $IBLOCK_ID,
					);

					if(strlen($arGroup["XML_ID"]))
					{
						$arFilter["=XML_ID"] = $arGroup["XML_ID"];
					}
					elseif(strlen($arGroup["NAME"]))
					{
						$arFilter["=NAME"] = $arGroup["NAME"];
					}

					if($LAST_GROUP_CODE>0)
					{
						$arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
						$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
					}
					else
					{
						$arFilter["SECTION_ID"] = 0;
						$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
					}

					$cache_id = md5(serialize($arFilter));
					if(array_key_exists($cache_id, $arSectionCache))
					{
						$arr = $arSectionCache[$cache_id];
					}
					else
					{
						$res = CIBlockSection::GetList(array(), $arFilter);
						if($arr = $res->Fetch())
							$arSectionCache[$cache_id] = $arr;
					}

					if($arr)
					{
						$arGroupsTmp[$i]["IBLOCK_ID"] = $arr["IBLOCK_ID"];
						$LAST_GROUP_CODE = $arr["ID"];
						$bUpdate = false;
						foreach($arGroupsTmp[$i] as $field_code => $field_value)
						{
							if($field_value."" !== $arr[$field_code]."")
							{
								$bUpdate = true;
								break;
							}
						}
						if($bUpdate)
						{
							$res = $bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i]);
							unset($arSectionCache[$cache_id]);
						}
					}
					else
					{
						$arGroupsTmp[$i]["IBLOCK_ID"] = $IBLOCK_ID;
						$arGroupsTmp[$i]["ACTIVE"] = "Y";
						$LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i]);
					}
				}

				//CIBlockSection::ReSort($IBLOCK_ID);

				// Create element
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $USER->GetID(),
					"IBLOCK_ID" => $IBLOCK_ID,
					"TMP_ID" => $tmpid,
					);
				foreach ($arAvailProdFields_names as $key => $value)
				{
					if(array_key_exists($key, $arRes))
					{
						$arLoadProductArray[$value["field"]] = $arRes[$key];
					}
				}

				$arFilter = array(
					"IBLOCK_ID" => $IBLOCK_ID,
				);
				if(strlen($arLoadProductArray["XML_ID"]))
				{
					$arFilter["=XML_ID"] = $arLoadProductArray["XML_ID"];
				}
				elseif(strlen($arLoadProductArray["NAME"]))
				{
					$arFilter["=NAME"] = $arLoadProductArray["NAME"];
				}
				else
				{
					$strErrorR .= GetMessage("IBLOCK_ADM_IMP_LINE_NO")." ".$line_num.". ".GetMessage("IBLOCK_ADM_IMP_NOIDNAME")."<br>";
				}

				if (strlen($strErrorR)<=0)
				{
					if (is_set($arLoadProductArray, "PREVIEW_PICTURE"))
					{
						$bFilePres = False;
						if (strlen($arLoadProductArray["PREVIEW_PICTURE"])>0)
						{
							$strPictureName = $arLoadProductArray["PREVIEW_PICTURE"];
							if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
							{
								$arLoadProductArray["PREVIEW_PICTURE"] = array();
								$arLoadProductArray["PREVIEW_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
								$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
								if ($arImageProps[2]==1) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/gif";
								elseif ($arImageProps[2]==2) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/jpeg";
								elseif ($arImageProps[2]==3) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/png";
								$arLoadProductArray["PREVIEW_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
								$arLoadProductArray["PREVIEW_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
								$bFilePres = True;
							}
						}

						if (!$bFilePres)
						{
							unset($arLoadProductArray["PREVIEW_PICTURE"]);
						}
					}

					if (is_set($arLoadProductArray, "DETAIL_PICTURE"))
					{
						$bFilePres = False;
						if (strlen($arLoadProductArray["DETAIL_PICTURE"])>0)
						{
							$strPictureName = $arLoadProductArray["DETAIL_PICTURE"];
							if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
							{
								$arLoadProductArray["DETAIL_PICTURE"] = array();
								$arLoadProductArray["DETAIL_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
								$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
								if ($arImageProps[2]==1) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/gif";
								elseif ($arImageProps[2]==2) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/jpeg";
								elseif ($arImageProps[2]==3) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/png";
								$arLoadProductArray["DETAIL_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
								$arLoadProductArray["DETAIL_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
								$bFilePres = True;
							}
						}

						if (!$bFilePres)
						{
							unset($arLoadProductArray["DETAIL_PICTURE"]);
						}
					}

					$res = CIBlockElement::GetList(
						Array(),
						$arFilter, false, false,
						array("ID", "IBLOCK_ID", "TMP_ID", "PREVIEW_PICTURE", "DETAIL_PICTURE")
					);
					if ($arr = $res->Fetch())
					{
						$PRODUCT_ID = $arr["ID"];
						if($arr["TMP_ID"] != $tmpid)
						{
							if (is_set($arLoadProductArray, "PREVIEW_PICTURE") && IntVal($arr["PREVIEW_PICTURE"])>0)
							{
								$arLoadProductArray["PREVIEW_PICTURE"]["old_file"] = $arr["PREVIEW_PICTURE"];
							}
							if (is_set($arLoadProductArray, "DETAIL_PICTURE") && IntVal($arr["DETAIL_PICTURE"])>0)
							{
								$arLoadProductArray["DETAIL_PICTURE"]["old_file"] = $arr["DETAIL_PICTURE"];
							}
							$res = $el->Update($PRODUCT_ID, $arLoadProductArray, $bWorkFlow, false, $IMAGE_RESIZE==="Y");
						}
						else
							$res = true;
					}
					else
					{
						if($arLoadProductArray["ACTIVE"]!="N")
							$arLoadProductArray["ACTIVE"] = "Y";
						$PRODUCT_ID = $el->Add($arLoadProductArray, $bWorkFlow, false, $IMAGE_RESIZE==="Y");
						$res = ($PRODUCT_ID>0);
					}

					//Save element sections list for future binding
					if($res && $bThereIsGroups && ($LAST_GROUP_CODE > 0))
					{
						if(!array_key_exists($PRODUCT_ID, $arProductGroups))
						{
							$arProductGroups[$PRODUCT_ID] = array();
						}
						$arProductGroups[$PRODUCT_ID][] = $LAST_GROUP_CODE;
					}

					if (!$res)
					{
						$strErrorR .= GetMessage("IBLOCK_ADM_IMP_LINE_NO")." ".$line_num.". ".GetMessage("IBLOCK_ADM_IMP_ERROR_LOADING")." ".$el->LAST_ERROR."<br>";
					}
					elseif($PREV_PRODUCT_ID === false)
					{
						$PREV_PRODUCT_ID = $PRODUCT_ID;
					}
				}

				if (strlen($strErrorR)<=0)
				{
					if(!array_key_exists($PRODUCT_ID, $arIBlockPropertyValue))
						$arIBlockPropertyValue[$PRODUCT_ID] = array();
					foreach($arRes as $key => $value)
					{
						if(strncmp($key, "IP_PROP", 7)==0)
						{
							$cur_prop_id = IntVal(substr($key, 7));
							if (!array_key_exists($cur_prop_id, $arIBlockProperty))
							{
								$res1 = CIBlockProperty::GetByID($cur_prop_id, $IBLOCK_ID);
								if ($arRes1 = $res1->Fetch())
								{
									$arIBlockProperty[$cur_prop_id] = $arRes1;
									if($arRes1["PROPERTY_TYPE"]=="F")
										$arIBlockFileProperty[$cur_prop_id] = $cur_prop_id;
								}
								else
								{
									$arIBlockProperty[$cur_prop_id] = false;
								}
							}

							if (is_array($arIBlockProperty[$cur_prop_id]))
							{
								if ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="L")
								{
									if(!array_key_exists($cur_prop_id, $arEnumCache))
										$arEnumCache[$cur_prop_id] = array();
									if(array_key_exists($value, $arEnumCache[$cur_prop_id]))
									{
										$value = $arEnumCache[$cur_prop_id][$value];
									}
									else
									{
										$res2 = CIBlockProperty::GetPropertyEnum($cur_prop_id, Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "VALUE"=>$value));
										if ($arRes2 = $res2->Fetch())
										{
											$value = $arEnumCache[$cur_prop_id][$value] = $arRes2["ID"];
										}
										else
										{
											$value = $arEnumCache[$cur_prop_id][$value] = CIBlockPropertyEnum::Add(array("PROPERTY_ID"=>$cur_prop_id, "VALUE"=>$value, "TMP_ID"=>$tmpid));
										}
									}
								}
								elseif($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="N")
								{
									$value = str_replace(",", ".", $value);
								}

								if ($arIBlockProperty[$cur_prop_id]["MULTIPLE"]=="Y")
								{
									if( !array_key_exists($cur_prop_id, $arIBlockPropertyValue[$PRODUCT_ID]) )
										$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id] = array();
									if (!in_array($value, $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id]))
										$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id][] = $value;
								}
								else
								{
									$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id] = $value;
								}
							}
						}
					}

					if($PREV_PRODUCT_ID != $PRODUCT_ID)
					{
						if(array_key_exists($PREV_PRODUCT_ID, $arProductGroups))
							CIBlockElement::SetElementSection($PREV_PRODUCT_ID, $arProductGroups[$PREV_PRODUCT_ID]);
						if(array_key_exists($PREV_PRODUCT_ID, $arIBlockPropertyValue))
						{
							if(count($arIBlockFileProperty) > 0)
							{
								$arPropFiles = array();
								$dbPropFiles = CIBlockElement::GetProperty($IBLOCK_ID, $PREV_PRODUCT_ID, "sort", "asc", Array("ACTIVE"=>"Y", "PROPERTY_TYPE"=>"F", "EMPTY"=>"N"));
								while($arPropFile = $dbPropFiles->Fetch())
									$arPropFiles[$arPropFile["ID"]][$arPropFile["PROPERTY_VALUE_ID"]] = array("del"=>"Y", "tmp_name"=>"");

								foreach($arIBlockFileProperty as $prop_id)
								{
									if(!is_array($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id]))
										$arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] = array($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id]);

									foreach($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] as $i => $file_name)
										if(strlen($file_name))
											$arPropFiles[$prop_id]["n".$i] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$file_name);

									$arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] = $arPropFiles[$prop_id];
								}
							}

							CIBlockElement::SetPropertyValuesEx($PREV_PRODUCT_ID, $IBLOCK_ID, $arIBlockPropertyValue[$PREV_PRODUCT_ID]);
						}
						CIBlockElement::UpdateSearch($PREV_PRODUCT_ID);
						$PREV_PRODUCT_ID = $PRODUCT_ID;
					}
				}

				if (strlen($strErrorR)<=0)
				{
					$correct_lines++;
				}
				else
				{
					$error_lines++;
					$strError .= $strErrorR;
				}

				if (intval($max_execution_time)>0 && (getmicrotime()-START_EXEC_TIME)>intval($max_execution_time))
				{
					$bAllLinesLoaded = False;
					break;
				}
			}

			if($PREV_PRODUCT_ID !== false)
			{
				if(array_key_exists($PREV_PRODUCT_ID, $arProductGroups))
					CIBlockElement::SetElementSection($PREV_PRODUCT_ID, $arProductGroups[$PREV_PRODUCT_ID]);
				if(array_key_exists($PREV_PRODUCT_ID, $arIBlockPropertyValue))
				{
					if(count($arIBlockFileProperty) > 0)
					{
						$arPropFiles = array();
						$dbPropFiles = CIBlockElement::GetProperty($IBLOCK_ID, $PREV_PRODUCT_ID, "sort", "asc", Array("ACTIVE"=>"Y", "PROPERTY_TYPE"=>"F", "EMPTY"=>"N"));
						while($arPropFile = $dbPropFiles->Fetch())
							$arPropFiles[$arPropFile["ID"]][$arPropFile["PROPERTY_VALUE_ID"]] = array("del"=>"Y", "tmp_name"=>"");

						foreach($arIBlockFileProperty as $prop_id)
						{
							if(!is_array($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id]))
								$arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] = array($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id]);

							foreach($arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] as $i => $file_name)
								if(strlen($file_name))
									$arPropFiles[$prop_id]["n".$i] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$file_name);

							$arIBlockPropertyValue[$PREV_PRODUCT_ID][$prop_id] = $arPropFiles[$prop_id];
						}
					}

					CIBlockElement::SetPropertyValuesEx($PREV_PRODUCT_ID, $IBLOCK_ID, $arIBlockPropertyValue[$PREV_PRODUCT_ID]);
				}
				CIBlockElement::UpdateSearch($PREV_PRODUCT_ID);
			}

			// delete sections and elements which no in datafile. Properties does not deleted
			if ($bAllLinesLoaded)
			{
				CIBlockSection::ReSort($IBLOCK_ID);

				if (is_set($_SESSION, $CUR_LOAD_SESS_ID))
					unset($_SESSION[$CUR_LOAD_SESS_ID]);

				if ($bThereIsGroups)
				{
					if ($outFileAction=="D")
					{
						$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
						while($arr = $res->Fetch())
						{
							CIBlockSection::Delete($arr["ID"]);
						}
						CIBlockSection::ReSort($IBLOCK_ID);
					}
					elseif ($outFileAction=="F")
					{
					}
					else
					{
						$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid, "ACTIVE"=>"Y"));
						while($arr = $res->Fetch())
						{
							//no resort, but search update
							$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "N"), false);
						}
					}

					if ($inFileAction=="A")
					{
						$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "TMP_ID"=>$tmpid, "ACTIVE"=>"N"));
						while($arr = $res->Fetch())
						{
							//no resort, but search update
							$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "Y"), false);
						}
					}
				}

				if($outFileAction=="D")
				{
					$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!=TMP_ID"=>$tmpid), false, false, Array("ID", "IBLOCK_ID"));
					while($arr = $res->Fetch())
					{
						CIBlockElement::Delete($arr["ID"], "Y", "N");
						$killed_lines++;
					}
				}
				elseif ($outFileAction=="F")
				{
				}
				else
				{
					$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!=TMP_ID"=>$tmpid, "ACTIVE"=>"Y"), false, false, Array("ID", "IBLOCK_ID"));
					while($arr = $res->Fetch())
					{
						$el->Update($arr["ID"], Array("ACTIVE" => "N"));
						$killed_lines++;
					}
				}

				if ($inFileAction=="A")
				{
					$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "TMP_ID"=>$tmpid, "ACTIVE"=>"N"), false, false, Array("ID", "IBLOCK_ID"));
					while($arr = $res->Fetch())
					{
						$el->Update($arr["ID"], Array("ACTIVE" => "Y"));
					}
				}
			}
			else
			{
				if (strlen($CUR_LOAD_SESS_ID) <= 0)
					$CUR_LOAD_SESS_ID = "CL".time();
				$_SESSION[$CUR_LOAD_SESS_ID]["tmpid"] = $tmpid;
				$_SESSION[$CUR_LOAD_SESS_ID]["line_num"] = $line_num;
				$_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"] = $correct_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["error_lines"] = $error_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"] = $killed_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"] = $arIBlockProperty;
				$_SESSION[$CUR_LOAD_SESS_ID]["arIBlockFileProperty"] = $arIBlockFileProperty;
				$_SESSION[$CUR_LOAD_SESS_ID]["arIBlockPropertyValue"] = $arIBlockPropertyValue;
				$_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"] = $bThereIsGroups;
				$_SESSION[$CUR_LOAD_SESS_ID]["arProductGroups"] = $arProductGroups;

				$paramsStr  = "fields_type=".urlencode($fields_type);
				$paramsStr .= "&first_names_r=".urlencode($first_names_r);
				$paramsStr .= "&delimiter_r=".urlencode($delimiter_r);
				$paramsStr .= "&delimiter_other_r=".urlencode($delimiter_other_r);
				$paramsStr .= "&first_names_f=".urlencode($first_names_f);
				$paramsStr .= "&metki_f=".urlencode($metki_f);
				for ($i = 0; $i < $NUM_FIELDS; $i++)
				{
					$paramsStr .= "&field_".$i."=".urlencode(${"field_".$i});
				}
				$paramsStr .= "&PATH2IMAGE_FILES=".urlencode($PATH2IMAGE_FILES);
				$paramsStr .= "&IMAGE_RESIZE=".urlencode($IMAGE_RESIZE);
				$paramsStr .= "&PATH2PROP_FILES=".urlencode($PATH2PROP_FILES);
				$paramsStr .= "&outFileAction=".urlencode($outFileAction);
				$paramsStr .= "&inFileAction=".urlencode($inFileAction);
				$paramsStr .= "&max_execution_time=".urlencode($max_execution_time);
				$_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"] = $paramsStr;

				$curFilePos = $csvFile->GetPos();
			}
		}

		if (strlen($strError)>0)
		{
			$strError .= GetMessage("IBLOCK_ADM_IMP_TOTAL_ERRS")." ".IntVal($error_lines).".<br>";
			$strError .= GetMessage("IBLOCK_ADM_IMP_TOTAL_COR1")." ".IntVal($correct_lines)." ".GetMessage("IBLOCK_ADM_IMP_TOTAL_COR2")."<br>";
			$STEP = 3;
		}
		//*****************************************************************//
	}
	//*****************************************************************//
}
/////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_IMP_PAGE_TITLE").$STEP);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
CAdminMessage::ShowMessage($strError);

if (!$bAllLinesLoaded)
{
	$strParams = bitrix_sessid_get()."&CUR_FILE_POS=".$curFilePos."&CUR_LOAD_SESS_ID=".urlencode($CUR_LOAD_SESS_ID)."&STEP=4&URL_DATA_FILE=".urlencode($DATA_FILE_NAME)."&IBLOCK_ID=".$IBLOCK_ID."&fields_type=".urlencode($fields_type)."&max_execution_time=".IntVal($max_execution_time);
	if ($fields_type=="R")
		$strParams .= "&delimiter_r=".urlencode($delimiter_r)."&delimiter_other_r=".urlencode($delimiter_other_r)."&first_names_r=".urlencode($first_names_r);
	else
		$strParams .= "&metki_f=".urlencode($metki_f)."&first_names_f=".urlencode($first_names_f);
	?>

	<?echo GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH");?>
	<a href="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&<?echo $strParams ?>"><?echo GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH_STEP");?></a><br>

	<script language="JavaScript" type="text/javascript">
	<!--
	function DoNext()
	{
		window.location="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&<?echo $strParams ?>";
	}
	setTimeout('DoNext()', 2000);
	//-->
	</script>
	<?
}
?>

<form method="POST" action="<?echo $sDocPath?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB1"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB1_ALT")),
	array("DIV" => "edit2", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB2"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB2_ALT")),
	array("DIV" => "edit3", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB3"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB3_ALT")),
	array("DIV" => "edit4", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB4"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB4_ALT")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 1)
{
	?>
 	<tr>
		<td><?echo GetMessage("IBLOCK_ADM_IMP_DATA_FILE") ?></td>
		<td>
			<input type="text" name="URL_DATA_FILE" value="<?echo htmlspecialchars($URL_DATA_FILE)?>" size="30">
			<input type="button" value="<?echo GetMessage("IBLOCK_ADM_IMP_OPEN") ?>" OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					"event" => "BtnClick",
					"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',// O - open, S - save
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"fileFilter" => 'csv',
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
			?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_ADM_IMP_INFOBLOCK") ?></td>
		<td>
			<?echo GetIBlockDropDownList($IBLOCK_ID, 'IBLOCK_TYPE_ID', 'IBLOCK_ID');?>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 2)
{
	?>
	<tr>
		<td valign="middle" colspan="2">
			<SCRIPT LANGUAGE="JavaScript">
			function DeactivateAllExtra()
			{
				document.getElementById("table_r").disabled = true;
				document.getElementById("table_r1").disabled = true;
				document.getElementById("table_r2").disabled = true;
				document.getElementById("table_f").disabled = true;
				document.getElementById("table_f1").disabled = true;
				document.getElementById("table_f2").disabled = true;

				document.dataload.metki_f.disabled = true;
				document.dataload.first_names_f.disabled = true;

				var i;
				for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
				{
					document.dataload.delimiter_r[i].disabled = true;
				}
				document.dataload.delimiter_other_r.disabled = true;
				document.dataload.first_names_r.disabled = true;
			}

			function ChangeExtra()
			{
				if (document.dataload.fields_type[0].checked)
				{
					document.getElementById("table_r").disabled = false;
					document.getElementById("table_r1").disabled = false;
					document.getElementById("table_r2").disabled = false;
					document.getElementById("table_f").disabled = true;
					document.getElementById("table_f1").disabled = true;
					document.getElementById("table_f2").disabled = true;

					var i;
					for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
					{
						document.dataload.delimiter_r[i].disabled = false;
					}
					document.dataload.delimiter_other_r.disabled = false;
					document.dataload.first_names_r.disabled = false;

					document.dataload.metki_f.disabled = true;
					document.dataload.first_names_f.disabled = true;

					document.dataload.submit_btn.disabled = false;
				}
				else
				{
					if (document.dataload.fields_type[1].checked)
					{
						document.getElementById("table_r").disabled = true;
						document.getElementById("table_r1").disabled = true;
						document.getElementById("table_r2").disabled = true;
						document.getElementById("table_f").disabled = false;
						document.getElementById("table_f1").disabled = false;
						document.getElementById("table_f2").disabled = false;

						var i;
						for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
						{
							document.dataload.delimiter_r[i].disabled = true;
						}
						document.dataload.delimiter_other_r.disabled = true;
						document.dataload.first_names_r.disabled = true;

						document.dataload.metki_f.disabled = false;
						document.dataload.first_names_f.disabled = false;

						document.dataload.submit_btn.disabled = false;
					}
				}
			}
			</SCRIPT>

			<input type="radio" name="fields_type" id="fields_type_R" value="R" <?if ($fields_type=="R" || strlen($fields_type)<=0) echo "checked";?> onClick="ChangeExtra()"><label for="fields_type_R"><?echo GetMessage("IBLOCK_ADM_IMP_RAZDELITEL") ?></label><br>
			<input type="radio" name="fields_type" id="fields_type_F" value="F" <?if ($fields_type=="F") echo "checked";?> onClick="ChangeExtra()"><label for="fields_type_F"><?echo GetMessage("IBLOCK_ADM_IMP_FIXED") ?></label>

		</td>
	</tr>

	<tr id="table_r" class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_RAZDEL1") ?></td>
	</tr>
	<tr id="table_r1">
		<td valign="top" width="40%"><?echo GetMessage("IBLOCK_ADM_IMP_RAZDEL_TYPE") ?></td>
		<td valign="top" width="60%">
			<input type="radio" name="delimiter_r" id="delimiter_r_TZP" value="TZP" <?if ($delimiter_r=="TZP" || strlen($delimiter_r)<=0) echo "checked"?>><label for="delimiter_r_TZP"><?echo GetMessage("IBLOCK_ADM_IMP_TZP") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_ZPT" value="ZPT" <?if ($delimiter_r=="ZPT") echo "checked"?>><label for="delimiter_r_ZPT"><?echo GetMessage("IBLOCK_ADM_IMP_ZPT") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_TAB" value="TAB" <?if ($delimiter_r=="TAB") echo "checked"?>><label for="delimiter_r_TAB"><?echo GetMessage("IBLOCK_ADM_IMP_TAB") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_SPS" value="SPS" <?if ($delimiter_r=="SPS") echo "checked"?>><label for="delimiter_r_SPS"><?echo GetMessage("IBLOCK_ADM_IMP_SPS") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_OTR" value="OTR" <?if ($delimiter_r=="OTR") echo "checked"?>><label for="delimiter_r_OTR"><?echo GetMessage("IBLOCK_ADM_IMP_OTR") ?></label>
			<input type="text" name="delimiter_other_r" size="3" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
		</td>
	</tr>
	<tr id="table_r2">
		<td><?echo GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES") ?></td>
		<td>
			<input type="hidden" name="first_names_r" value="N">
			<input type="checkbox" name="first_names_r" value="Y" <?if ($first_names_r!="N") echo "checked"?>>
		</td>
	</tr>

	<tr id="table_f" class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_FIX1") ?></td>
	</tr>
	<tr id="table_f1">
		<td valign="top" width="40%">
			<?echo GetMessage("IBLOCK_ADM_IMP_FIX_MET") ?><br>
			<small><?echo GetMessage("IBLOCK_ADM_IMP_FIX_MET_DESCR") ?></small>
		</td>
		<td valign="top" width="60%">
			<textarea name="metki_f" rows="7" cols="3"><?echo htmlspecialchars($metki_f) ?></textarea>
		</td>
	</tr>
	<tr id="table_f2">
		<td><?echo GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES") ?></td>
		<td>
			<input type="checkbox" name="first_names_f" value="Y" <?if ($first_names_f=="Y") echo "checked"?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES") ?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<?
			$sContent = "";
			if(strlen($DATA_FILE_NAME)>0)
			{
				$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
				$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
				if((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W")
				{
					$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
					$sContent = fread($file_id, 10000);
					fclose($file_id);
				}
			}
			?>
			<textarea name="data" wrap="OFF" rows="7" cols="80"><?echo htmlspecialchars($sContent) ?></textarea>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 3)
{
	?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_FIELDS_SOOT") ?></td>
	</tr>

	<?
	$arAvailFields = array();

	$strVal = $defCatalogAvailProdFields;
	$arVal = explode(",", $strVal);
	$arCatalogAvailProdFields_tmp = $arCatalogAvailProdFields;
	for ($i = 0; $i < count($arVal); $i++)
	{
		for ($j = 0; $j < count($arCatalogAvailProdFields_tmp); $j++)
		{
			if ($arVal[$i]==$arCatalogAvailProdFields_tmp[$j]["value"]
				&& $arVal[$i]!="IE_ID")
			{
				$arAvailFields[] = array("value"=>$arCatalogAvailProdFields_tmp[$j]["value"], "name"=>$arCatalogAvailProdFields_tmp[$j]["name"]);
				break;
			}
		}
	}

	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
	while ($prop_fields = $properties->Fetch())
	{
		$arAvailFields[] = array("value"=>"IP_PROP".$prop_fields["ID"], "name"=>GetMessage("IBLOCK_ADM_IMP_FI_PROPS")." \"".$prop_fields["NAME"]."\"", "code" => "IP_PROP_".$prop_fields["CODE"]);
	}

	for ($k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
	{
		$strVal = $defCatalogAvailGroupFields;
		$arVal = explode(",", $strVal);
		for ($i = 0; $i < count($arVal); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
			{
				if ($arVal[$i]==$arCatalogAvailGroupFields[$j]["value"])
				{
					$arAvailFields[] = array("value"=>$arCatalogAvailGroupFields[$j]["value"].$k, "name"=>GetMessage("IBLOCK_ADM_IMP_FI_GROUP_LEV")." ".($k+1).": ".$arCatalogAvailGroupFields[$j]["name"]);
					break;
				}
			}
		}
	}

	for ($i = 0; $i < count($arDataFileFields); $i++)
	{
		?>
		<tr>
			<td valign="top" width="40%">
				<b><?echo GetMessage("IBLOCK_ADM_IMP_FIELD") ?> <?echo $i+1 ?></b> (<?echo htmlspecialchars($arDataFileFields[$i]);?>):
			</td>
			<td valign="top" width="60%">
				<select name="field_<?echo $i ?>">
					<option value=""> - </option>
					<?
					for ($j = 0; $j < count($arAvailFields); $j++)
					{
						$bSelected = ${"field_".$i}==$arAvailFields[$j]["value"];
						if(!$bSelected && !isset(${"field_".$i}))
							$bSelected = $arAvailFields[$j]["value"]==$arDataFileFields[$i];
						if(!$bSelected && !isset(${"field_".$i}))
							$bSelected = $arAvailFields[$j]["code"]==$arDataFileFields[$i];
						?>
						<option value="<?echo $arAvailFields[$j]["value"] ?>" <?if ($bSelected) echo "selected" ?>><?echo htmlspecialchars($arAvailFields[$j]["name"]) ?></option>
						<?
					}
					?>
				</select>
			</td>
		</tr>
		<?
	}
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_ADDIT_SETTINGS") ?></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_ADM_IMP_IMG_PATH") ?>:</td>
		<td valign="top">
			<input type="text" name="PATH2IMAGE_FILES" size="40" value="<?echo htmlspecialchars($PATH2IMAGE_FILES)?>"><br>
			<small><?echo GetMessage("IBLOCK_ADM_IMP_IMG_PATH_DESCR") ?><br></small>
		</td>
	</tr>
	<tr>
		<td valign="top"><label for="IMAGE_RESIZE"><?echo GetMessage("IBLOCK_ADM_IMP_IMG_RESIZE") ?>:</label></td>
		<td valign="top">
			<input type="checkbox" name="IMAGE_RESIZE" id="IMAGE_RESIZE" value="Y" <?echo ($IMAGE_RESIZE==="Y"? "checked": "")?>>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_ADM_IMP_PROP_PATH") ?>:</td>
		<td valign="top">
			<input type="text" name="PATH2PROP_FILES" size="40" value="<?echo htmlspecialchars($PATH2PROP_FILES)?>"><br>
			<small><?echo GetMessage("IBLOCK_ADM_IMP_PROP_PATH_DESCR") ?><br></small>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_ADM_IMP_OUTFILE") ?>:</td>
		<td valign="top">
			<input type="radio" id="outFileAction_H" name="outFileAction" value="H" <?if (strlen($outFileAction)<=0 || ($outFileAction=="H")) echo "checked";?>><label for="outFileAction_H"><?echo GetMessage("IBLOCK_ADM_IMP_OF_DEACT") ?></label><br>
			<input type="radio" id="outFileAction_D" name="outFileAction" value="D" <?if ($outFileAction=="D") echo "checked";?>><label for="outFileAction_D"><?echo GetMessage("IBLOCK_ADM_IMP_OF_DEL") ?></label><br>
			<input type="radio" id="outFileAction_F" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>><label for="outFileAction_F"><?echo GetMessage("IBLOCK_ADM_IMP_OF_KEEP") ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_ADM_IMP_INACTIVE_PRODS");?>:</td>
		<td valign="top">
			<input type="radio" id="inFileAction_F" name="inFileAction" value="F" <?if (strlen($inFileAction)<=0 || ($inFileAction=="F")) echo "checked";?>><label for="inFileAction_F"><?echo GetMessage("IBLOCK_ADM_IMP_KEEP_AS_IS");?></label><br>
			<input type="radio" id="inFileAction_A" name="inFileAction" value="A" <?if ($inFileAction=="A") echo "checked";?>><label for="inFileAction_A"><?echo GetMessage("IBLOCK_ADM_IMP_ACTIVATE_PROD");?></label>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME");?>:</td>
		<td valign="top" align="left">
			<input type="text" name="max_execution_time" size="6" value="<?echo htmlspecialchars($max_execution_time)?>"><br>
			<small><?echo GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME_NOTE");?><br></small>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES") ?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?
			$sContent = "";
			if(strlen($DATA_FILE_NAME)>0)
			{
				$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
				$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
				if((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W")
				{
					$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
					$sContent = fread($file_id, 10000);
					fclose($file_id);
				}

			}
			?>
			<textarea name="data" wrap="OFF" rows="7" cols="80"><?echo htmlspecialchars($sContent) ?></textarea>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 4)
{
	?>
	<tr>
		<td valign="middle" colspan="2" nowrap>
				<b><?
				if (!$bAllLinesLoaded)
					echo GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH_CONTINUE");
				else
					echo GetMessage("IBLOCK_ADM_IMP_SUCCESS");
				?></b>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" nowrap>
			<?echo GetMessage("IBLOCK_ADM_IMP_SU_ALL") ?> <b><?echo $line_num ?></b><br>
			<?echo GetMessage("IBLOCK_ADM_IMP_SU_CORR") ?> <b><?echo $correct_lines ?></b><br>
			<?echo GetMessage("IBLOCK_ADM_IMP_SU_ER") ?> <b><?echo $error_lines ?></b><br>
			<?
			if ($outFileAction=="D")
			{
				echo GetMessage("IBLOCK_ADM_IMP_SU_KILLED")." <b>".$killed_lines."</b>";
			}
			elseif ($outFileAction=="F")
			{
			}
			else	// H
			{
				echo GetMessage("IBLOCK_ADM_IMP_SU_HIDED")." <b>".$killed_lines."</b>";
			}
			?>

		</td>
	</tr>
<?
}
$tabControl->EndTab();
?>

<?
$tabControl->Buttons();
?>

<?if ($STEP < 4):?>
	<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
	<?=bitrix_sessid_post()?>
	<?if ($STEP>1):?>
		<input type="hidden" name="URL_DATA_FILE" value="<?echo htmlspecialchars($DATA_FILE_NAME) ?>">
		<input type="hidden" name="IBLOCK_ID" value="<?echo $IBLOCK_ID ?>">
	<?endif;?>

	<?if ($STEP<>2):?>
		<input type="hidden" name="fields_type" value="<?echo htmlspecialchars($fields_type) ?>">
		<input type="hidden" name="delimiter_r" value="<?echo htmlspecialchars($delimiter_r) ?>">
		<input type="hidden" name="delimiter_other_r" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
		<input type="hidden" name="first_names_r" value="<?echo htmlspecialchars($first_names_r) ?>">
		<input type="hidden" name="metki_f" value="<?echo htmlspecialchars($metki_f) ?>">
		<input type="hidden" name="first_names_f" value="<?echo htmlspecialchars($first_names_f) ?>">
	<?endif;?>

	<?if ($STEP<>3):?>
		<?foreach($_POST as $name => $value):?>
			<?if(preg_match("/^field_(\\d+)$/", $name)):?>
				<input type="hidden" name="<?echo $name?>" value="<?echo htmlspecialchars($value)?>">
			<?endif?>
		<?endforeach?>
		<input type="hidden" name="PATH2IMAGE_FILES" value="<?echo htmlspecialchars($PATH2IMAGE_FILES)?>">
		<input type="hidden" name="IMAGE_RESIZE" value="<?echo htmlspecialchars($IMAGE_RESIZE)?>">
		<input type="hidden" name="PATH2PROP_FILES" value="<?echo htmlspecialchars($PATH2PROP_FILES)?>">
		<input type="hidden" name="outFileAction" value="<?echo htmlspecialchars($outFileAction)?>">
		<input type="hidden" name="inFileAction" value="<?echo htmlspecialchars($inFileAction)?>">
		<input type="hidden" name="max_execution_time" value="<?echo htmlspecialchars($max_execution_time)?>">
	<?endif;?>

	<?if ($STEP>1):?>
	<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("IBLOCK_ADM_IMP_BACK") ?>">
	<?endif?>
	<input type="submit" value="<?echo ($STEP==3)?GetMessage("IBLOCK_ADM_IMP_NEXT_STEP_F"):GetMessage("IBLOCK_ADM_IMP_NEXT_STEP") ?> &gt;&gt;" name="submit_btn">

	<?
	if ($STEP == 2)
	{
		?>
		<SCRIPT LANGUAGE="JavaScript">
			DeactivateAllExtra();
			ChangeExtra();
		</SCRIPT>
		<?
	}
	?>
<?else:?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("IBLOCK_ADM_IMP_2_1_STEP") ?>">
<?endif;?>

<?
$tabControl->End();
?>

</form>

<script language="JavaScript">
<!--
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?elseif ($STEP == 3):?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit4");
<?elseif ($STEP > 3):?>
tabControl.SelectTab("edit4");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?endif;?>
//-->
</script>

<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>
