<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/iblock.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

/*Change any language identifiers carefully*/
/*because of user customized forms!*/
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iblock/admin/iblock_element_edit_compat.php");

IncludeModuleLangFile(__FILE__);
define("MODULE_ID", "iblock");
define("ENTITY", "CIBlockDocument");
define("DOCUMENT_TYPE", "iblock_".$IBLOCK_ID);

$strLookup = preg_replace("/[^a-zA-Z0-9_:]/", "", htmlspecialchars($_REQUEST["lookup"]));
if ('' != $strLookup)
{
	define('BT_UT_AUTOCOMPLETE',1);
}

$strWarning = "";
$bVarsFromForm = false;
$ID = IntVal($ID);	//ID of the persistent record
$bCopy = ($action == "copy");
if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
	$bCopy = false;

if($ID<=0 && IntVal($PID)>0)
	$ID = IntVal($PID);

$PREV_ID = intval($PREV_ID);

$WF_ID = $ID; 		//This is ID of the current copy

$bWorkflow = CModule::IncludeModule("workflow") && (CIBlock::GetArrayByID($IBLOCK_ID, "WORKFLOW") != "N");
$bBizproc = CModule::IncludeModule("bizproc") && (CIBlock::GetArrayByID($IBLOCK_ID, "BIZPROC") != "N");

if(($ID <= 0 || $bCopy) && $bWorkflow)
	$WF = "Y";
elseif(!$bWorkflow)
	$WF = "N";

$historyId = intval($history_id);
if ($historyId > 0 && $bBizproc)
	$view = "Y";
else
	$historyId = 0;

function _ShowStringPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false)
{
	global $bCopy;
	$start = 0;

	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';

	$rows = $property_fields["ROW_COUNT"];
	$cols = $property_fields["COL_COUNT"];

	if($property_fields["WITH_DESCRIPTION"]=="Y")
		$strAddDesc = "[VALUE]";
	else
		$strAddDesc = "";

	if(!is_array($values)) $values = Array();
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}
		echo '<tr><td>';
		$val_description = "";
		if(is_array($val) && is_set($val, "VALUE"))
		{
			$val_description = $val["DESCRIPTION"];
			$val = $val["VALUE"];
		}
		if($rows>1)
			echo '<textarea name="'.$name.'['.$key.']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
		else
			echo '<input name="'.$name.'['.$key.']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

		if($property_fields["WITH_DESCRIPTION"]=="Y")
			echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="'.$name.'['.$key.'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text" id="'.$name.'['.$key.'][DESCRIPTION]"></span>';

		echo "<br>";
		echo '</td></tr>';

		if($property_fields["MULTIPLE"]!="Y")
		{
			$bVarsFromForm = true;
			break;
		}
	}

	if(!$bVarsFromForm)
	{
		$val_description = "";
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			if($i==0 && $bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0)
				$val = $property_fields["DEFAULT_VALUE"];
			else
				$val = "";

			if($rows>1)
				echo '<textarea name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
			else
				echo '<input name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

			if($property_fields["WITH_DESCRIPTION"]=="Y")
				echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="'.$name.'[n'.($start + $i).'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';

			echo "<br>";
			echo '</td></tr>';
		}
	}
	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}

	echo '</table>';
}

function _ShowGroupPropertyField($name, $property_fields, $values)
{
	if(!is_array($values)) $values = Array();
	foreach($values as $key => $value)
	{
		if(is_array($value) && array_key_exists("VALUE", $value))
			$values[$key] = $value["VALUE"];
	}

	$res = "";
	$bWas = false;
	$sections = CIBlockSection::GetList(
		Array("left_margin"=>"asc"),
		Array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]),
		false,
		Array("ID", "DEPTH_LEVEL", "NAME")
	);
	while($ar = $sections->GetNext())
	{
		$res .= '<option value="'.$ar["ID"].'"';
		if(in_array($ar["ID"], $values))
		{
			$bWas = true;
			$res .= ' selected';
		}
		$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]-1).$ar["NAME"].'</option>';
	}
	echo '<select name="'.$name.'[]" size="'.$property_fields["MULTIPLE_CNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
	echo '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
	echo $res;
	echo '</select>';
}

function _ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm = false)
{
	global $bCopy;
	$start = 0;

	if(!is_array($values)) $values = Array();
	//echo '<IFRAME TABINDEX=100 name="hfpn_'.md5($name).'" src="" width=0 height=0></IFRAME>';
	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	$max_val = -1;
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}
		if(is_array($val) && is_set($val, "VALUE"))
			$val = $val["VALUE"];
		$db_res = CIBlockElement::GetByID($val);
		$ar_res = $db_res->GetNext();
		echo '<tr><td>'.
			'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'" >'.$ar_res['NAME'].'</span>'.
			'</td></tr>';
		if(substr($key, -1, 1)=='n' && $max_val < intval(substr($key, 1)))
			$max_val = intval(substr($key, 1));

		if($property_fields["MULTIPLE"]!="Y")
		{
			$bVarsFromForm = true;
			break;
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) : 1);
		for($i=$max_val+1; $i<$max_val+1+$cnt; $i++)
		{
			$val = "";
			$key = "n".($start + $i);
    		echo '<tr><td>'.
    			'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
    			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
    			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'"></span>'.
    			'</td></tr>';
		}
		$max_val += $cnt;
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td>'.
			'<input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;m=y&amp;k='.$key.'\', 600, 500);">'.
			'<span id="sp_'.md5($name).'_'.$key.'" ></span>'.
			'</td></tr>';
	}

	echo '</table>';
	echo "<script type=\"text/javascript\">\r\n";
	echo "var MV_".md5($name)." = ".($max_val+1).";\r\n";
	echo "function InS".md5($name)."(id, name){ \r\n";
	echo "	oTbl=document.getElementById('tb".md5($name)."');\r\n";
	echo "	oRow=oTbl.insertRow(oTbl.rows.length-1); \r\n";
	echo "	oCell=oRow.insertCell(-1); \r\n";
	echo "	oCell.innerHTML=".
		"'<input name=\"".$name."[n'+MV_".md5($name)."+']\" value=\"'+id+'\" size=\"5\" type=\"text\">'+\r\n".
		"'<input type=\"button\" value=\"...\" '+\r\n".
		"'onClick=\"jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=".LANG."&amp;IBLOCK_ID=".$property_fields["LINK_IBLOCK_ID"]."&amp;n=".$name."&amp;k='+MV_".md5($name)."+'\', '+\r\n".
		"' 600, 500);\">'+".
		"'&nbsp;<span id=\"sp_".md5($name)."_'+MV_".md5($name)."+'\" >'+name+'</span>".
		"';";
	echo 'MV_'.md5($name).'++;';
	echo '}';
	echo "\r\n</script>";
}

function _ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show=50000, $bVarsFromForm = false)
{
	global $bCopy, $historyId;

	$bFileman = CModule::IncludeModule('fileman');
	$bVarsFromForm = false;
	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	if(!is_array($values)) $values = Array();
	$cols = $property_fields["COL_COUNT"];

	if(!$bCopy)
	{
		foreach($values as $key=>$val)
		{
			echo '<tr><td>';
			$val_description = "";
			if(is_array($val) && is_set($val, "VALUE"))
			{
				$val_description = $val["DESCRIPTION"];
				$val = $val["VALUE"];
			}

			if($bFileman)
			{
				if ($historyId > 0)
					echo CMedialib::InputFile(
						$name."[".$key."]", $val,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)) //info
					);
				else
					echo CMedialib::InputFile(
						$name."[".$key."]", $val,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
						array("SIZE"=>$cols), //file
						array(), //server
						array(), //media lib
						($property_fields["WITH_DESCRIPTION"]=="Y"?
							array("NAME" => "DESCRIPTION_".$name."[".$key."]"):
							false
						), //descr
						array() //delete
					);
			}
			else
			{
				echo CFile::InputFile($name."[".$key."]", $cols, $val, false, 0, "");
				echo "<br>";
				if ($historyId > 0)
					echo CFile::ShowImage($val, 200, 200, "border=0", "", true);
				else
					echo CFile::ShowFile($val, $max_file_size_show, 400, 400, true)."<br>";
				if($property_fields["WITH_DESCRIPTION"]=="Y")
					echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'['.$key.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			}
			echo '<br></td></tr>';
			if($property_fields["MULTIPLE"]!="Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			$val_description = "";

			if($bFileman)
			{
				echo CMedialib::InputFile(
					$name."[n".$i."]", 0,
					array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
					"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
					array("SIZE"=>$cols), //file
					array(), //server
					array(), //media lib
					($property_fields["WITH_DESCRIPTION"]=="Y"?
						array("NAME" => "DESCRIPTION_".$name."[n".$i."]"):
						false
					), //descr
					array() //delete
				);
			}
			else
			{
				echo CFile::InputFile($name."[n".$i."]", $cols, "", false, 0, "");
				if($property_fields["WITH_DESCRIPTION"]=="Y")
					echo ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'[n'.$i.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			}
			echo '<br></td></tr>';
		}
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}

	echo '</table>';
}

function _ShowListPropertyField($name, $property_fields, $values, $bInitDef = false, $def_text = false)
{
	if(!is_array($values)) $values = Array();
	foreach($values as $key => $value)
	{
		if(is_array($value) && array_key_exists("VALUE", $value))
			$values[$key] = $value["VALUE"];
	}

	$id = $property_fields["ID"];
	$multiple = $property_fields["MULTIPLE"];
	$res = "";
	if($property_fields["LIST_TYPE"]=="C") //list property as checkboxes
	{
		$cnt = 0;
		$wSel = false;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			$cnt++;
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$wSel = true;

			$uniq = md5(uniqid(rand(), true));
			if($multiple=="Y") //multiple
				$res .= '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';
			else //if(MULTIPLE=="Y")
				$res .= '<input type="radio" name="'.$name.'[]" id="'.$uniq.'" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").'><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';

			if($cnt==1)
				$res_tmp = '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><br>';
		}


		$uniq = md5(uniqid(rand(), true));

		if($cnt==1)
			$res = $res_tmp;
		elseif($multiple!="Y")
			$res = '<input type="radio" name="'.$name.'[]" value=""'.(!$wSel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_ELEMENT_PROP_NO") )).'</label><br>'.$res;

		if($multiple=="Y" || $cnt==1)
			$res = '<input type="hidden" name="'.$name.'" value="">'.$res;
	}
	else //list property as list
	{
		$bNoValue = true;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$bNoValue = false;
			$res .= '<option value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" selected":"").'>'.htmlspecialcharsex($ar_enum["VALUE"]).'</option>';
		}

		if($property_fields["MULTIPLE"]=="Y" && IntVal($property_fields["ROW_COUNT"])<2)
			$property_fields["ROW_COUNT"] = 5;
		if($property_fields["MULTIPLE"]=="Y")
			$property_fields["ROW_COUNT"]++;
		$res = '<select name="'.$name.'[]" size="'.$property_fields["ROW_COUNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>'.
				'<option value=""'.($bNoValue?' selected':'').'>'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_ELEMENT_PROP_NA") )).'</option>'.
				$res.
				'</select>';
	}
	echo $res;
}

function _ShowUserPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show=50000, $form_name = "form_element")
{
	global $bCopy;
	$start = 0;

	if(!is_array($property_fields["~VALUE"]))
		$values = Array();
	else
		$values = $property_fields["~VALUE"];
	unset($property_fields["VALUE"]);
	unset($property_fields["~VALUE"]);

	$html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	$arUserType = CIBlockProperty::GetUserType($property_fields["USER_TYPE"]);
	$bMultiple = $property_fields["MULTIPLE"] == "Y" && array_key_exists("GetPropertyFieldHtmlMulty", $arUserType);
	$max_val = -1;

	if(($arUserType["PROPERTY_TYPE"] !== "F") || (!$bCopy))
	{
		if($bMultiple)
		{
			$html .= '<tr><td>';
			$html .= call_user_func_array($arUserType["GetPropertyFieldHtmlMulty"],
				array(
					$property_fields,
					$values,
					array(
						"VALUE"=>'PROP['.$property_fields["ID"].']',
						"FORM_NAME"=>$form_name,
						"MODE"=>"FORM_FILL"
					),
				));
			$html .= '</td></tr>';
		}
		else
		{
		foreach($values as $key=>$val)
		{
			if($bCopy)
			{
				$key = "n".$start;
				$start++;
			}

			if(!is_array($val) || !array_key_exists("VALUE",$val))
				$val = array("VALUE"=>$val, "DESCRIPTION"=>"");

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL"
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';

			if(substr($key, -1, 1)=='n' && $max_val < intval(substr($key, 1)))
				$max_val = intval(substr($key, 1));
			if($property_fields["MULTIPLE"] != "Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
		}
	}

	if(!$bVarsFromForm && !$bMultiple)
	{
		$bDefaultValue = is_array($property_fields["DEFAULT_VALUE"]) || strlen($property_fields["DEFAULT_VALUE"]);

		if($property_fields["MULTIPLE"]=="Y")
		{
			$cnt = IntVal($property_fields["MULTIPLE_CNT"]);
			if($cnt <= 0 || $cnt > 30)
				$cnt = 5;

			if($bInitDef && $bDefaultValue)
				$cnt++;
		}
		else
		{
			$cnt = 1;
		}

		for($i=$max_val+1; $i<$max_val+1+$cnt; $i++)
		{
			if($i==0 && $bInitDef && $bDefaultValue)
				$val = array(
					"VALUE"=>$property_fields["DEFAULT_VALUE"],
					"DESCRIPTION"=>"",
				);
			else
				$val = array(
					"VALUE"=>"",
					"DESCRIPTION"=>"",
				);

			$key = "n".($start + $i);

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL"
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';
		}
		$max_val += $cnt;
	}
	if($property_fields["MULTIPLE"]=="Y" && $arUserType["USER_TYPE"] !== "HTML" && !$bMultiple)
	{
		$html .= '<tr><td><input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}
	$html .= '</table>';
	echo $html;
}

function _ShowPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show = 50000, $form_name = "form_element")
{
	$type = $property_fields["PROPERTY_TYPE"];
	if($property_fields["USER_TYPE"]!="")
		_ShowUserPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm, $max_file_size_show, $form_name);
	elseif($type=="L") //list property
		_ShowListPropertyField($name, $property_fields, $values, $bInitDef);
	elseif($type=="F") //file property
		_ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show, $bVarsFromForm);
	elseif($type=="G") //section link
	{
		if(function_exists("_ShowGroupPropertyField_custom"))
			_ShowGroupPropertyField_custom($name, $property_fields, $values, $bVarsFromForm);
		else
			_ShowGroupPropertyField($name, $property_fields, $values, $bVarsFromForm);
	}
	elseif($type=="E") //element link
		_ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm);
	else
		_ShowStringPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm);
}

function _ShowHiddenValue($name, $value)
{
	$res = "";

	if(is_array($value))
	{
		foreach($value as $k => $v)
			$res .= _ShowHiddenValue($name.'['.htmlspecialchars($k).']', $v);
	}
	else
	{
		$res .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'">'."\n";
	}

	return $res;
}

class _CIBlockError
{
	var $err_type, $err_text, $err_level;

	function _CIBlockError($err_level = false, $err_type = "", $err_text = "")
	{
		$this->err_type = $err_type;
		$this->err_text = preg_replace("#<br>$#i", "", $err_text);
		$this->err_level = $err_level;
		_CIBlockError::GetErrorText($this);
	}

	function GetErrorText($error = false)
	{
		static $errors = array();
		$str = "";
		if(is_object($error))
		{
			$errors[] = $error;
		}
		else
		{
			foreach($errors as $error)
			{
				if($str)
					$str .= "<br>";
				$str .= $error->err_text;
			}
		}
		return $str;
	}
}


$error = false;

$IBLOCK_ID = IntVal($IBLOCK_ID); //information block ID
$WF = ($WF=="Y") ? "Y" : "N";	//workflow mode
$view = ($view=="Y") ? "Y" : "N"; //view mode

if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
{
	$return_url = '';
}
else
{
	if(strlen($return_url)>0 && strtolower(substr($return_url, strlen($APPLICATION->GetCurPage())))==strtolower($APPLICATION->GetCurPage()))
		$return_url = "";
	if(strlen($return_url)<=0 && $from=="iblock_section_admin")
		$return_url = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section)));
}

do{ //one iteration loop

	if ($historyId > 0)
	{
		$arErrorsTmp = array();
		$arResult = CBPDocument::GetDocumentFromHistory($historyId, $arErrorsTmp);

		if (count($arErrorsTmp) > 0)
		{
			foreach ($arErrorsTmp as $e)
			{
				$error = new _CIBlockError(1, $e["code"], $e["message"]);
				break;
			}
		}

		$canWrite = CBPDocument::CanUserOperateDocument(
			IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
			$GLOBALS["USER"]->GetID(),
			$arResult["DOCUMENT_ID"],
			array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray())
		);
		if (!$canWrite)
		{
			$error = new _CIBlockError(1, "ACCESS_DENIED", GetMessage("IBLOCK_ACCESS_DENIED_STATUS"));
			break;
		}

		$type = $arResult["DOCUMENT"]["FIELDS"]["IBLOCK_TYPE_ID"];
		$IBLOCK_ID = $arResult["DOCUMENT"]["FIELDS"]["IBLOCK_ID"];
	}

	$arIBTYPE = CIBlockType::GetByIDLang($type, LANG);
	if($arIBTYPE===false)
	{
		$error = new _CIBlockError(1, "BAD_IBLOCK_TYPE", GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));
		break;
	}

	$bBadBlock = true;
	$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
	if($arIBlock)
	{
		$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
		if($BlockPerm>="W")
			$bBadBlock=false;
		elseif(($WF=="Y" || $view=="Y") && $BlockPerm>="U" && $bWorkflow)
			$bBadBlock = false;
		elseif($BlockPerm>="U" && $bBizproc)
			$bBadBlock = false;

		//This is temporary permissions check
		//will b removed ASAP
		if($bBadBlock && CModule::IncludeModule('lists'))
		{
			//Find out if there is some groups to edit lists (so it's lists)
			$arListsPerm = CLists::GetPermission($arIBlock["IBLOCK_TYPE_ID"]);
			if(count($arListsPerm))
			{
				if($BlockPerm >= "R")
					$bBadBlock = false;
				else
				{
					//Check permissions for add or edit iblock
					$arUserGroups = $USER->GetUserGroupArray();
					if(count(array_intersect($arListsPerm, $arUserGroups)))
						$bBadBlock = false;
				}
			}
		}
	}

	if($bBadBlock)
	{
		$error = new _CIBlockError(1, "BAD_IBLOCK", GetMessage("IBLOCK_BAD_IBLOCK"));
		$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBTYPE["ELEMENT_NAME"].": ".GetMessage("IBLOCK_EDIT_TITLE"));
		break;
	}

	$bTab2 = ($arIBTYPE["SECTIONS"]=="Y");
	$bTab4 = $bWorkflow;
	$bTab7 = $bBizproc && ($historyId <= 0);

	$aTabs = array();
	$aTabs[] = array("DIV" => "edit1", "TAB" => $arIBlock["ELEMENT_NAME"], "ICON"=>"iblock_element", "TITLE"=>htmlspecialcharsex($arIBlock["ELEMENT_NAME"]));
	$aTabs[] = array("DIV" => "edit5", "TAB" => GetMessage("IBEL_E_TAB_PREV"), "ICON"=>"iblock_element", "TITLE"=>GetMessage("IBEL_E_TAB_PREV_TITLE"));
	$aTabs[] = array("DIV" => "edit6", "TAB" => GetMessage("IBEL_E_TAB_DET"), "ICON"=>"iblock_element", "TITLE"=>GetMessage("IBEL_E_TAB_DET_TITLE"));
	if($bTab2) $aTabs[] = array("DIV" => "edit2", "TAB" => $arIBlock["SECTIONS_NAME"], "ICON"=>"iblock_element_section", "TITLE"=>htmlspecialcharsex($arIBlock["SECTIONS_NAME"]));
	$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("IBLOCK_EL_TAB_MO"), "ICON"=>"iblock_element_params", "TITLE"=>GetMessage("IBLOCK_EL_TAB_MO_TITLE"));
	if($bTab4) $aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("IBLOCK_EL_TAB_WF"), "ICON"=>"iblock_element_wf", "TITLE"=>GetMessage("IBLOCK_EL_TAB_WF_TITLE"));
	if ($bTab7) $aTabs[] = array("DIV" => "edit7", "TAB" => GetMessage("IBEL_E_TAB_BIZPROC"), "ICON"=>"iblock_element_wf", "TITLE"=>GetMessage("IBEL_E_TAB_BIZPROC"));

	$bCustomForm = 	(strlen($arIBlock["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"]))
		|| (strlen($arIBTYPE["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"]));

	$tabControl = new CAdminForm($bCustomForm? "tabControl": "form_element_".$IBLOCK_ID, $aTabs);

	if($ID>0)
	{
		$rsElement = CIBlockElement::GetList(Array(), Array("ID" => $ID, "IBLOCK_ID" => $IBLOCK_ID, "SHOW_HISTORY"=>"Y"), false, false, Array("ID", "CREATED_BY"));
		if(!($arElement = $rsElement->Fetch()))
		{
			$error = new _CIBlockError(1, "BAD_ELEMENT", GetMessage("IBLOCK_BAD_ELEMENT"));
			$APPLICATION->SetTitle(/*$arIBTYPE["NAME"].": ".*/$arIBTYPE["ELEMENT_NAME"].": ".GetMessage("IBLOCK_EDIT_TITLE"));
			break;
		}
	}

	$customTabber = new CAdminTabEngine("OnAdminIBlockElementEdit", array("ID" => $ID, "IBLOCK"=>$arIBlock, "IBLOCK_TYPE"=>$arIBTYPE));


	// workflow mode
	if($ID>0 && $WF=="Y")
	{
		// get ID of the last record in workflow
		$WF_ID = CIBlockElement::WF_GetLast($ID);

		// check for edit permissions
		$STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ID, $STATUS_TITLE);
		$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($STATUS_ID);

		if($STATUS_ID>1 && $STATUS_PERMISSION<2)
		{
			$error = new _CIBlockError(1, "ACCESS_DENIED", GetMessage("IBLOCK_ACCESS_DENIED_STATUS"));
			break;
		}
		elseif($STATUS_ID==1)
		{
			$WF_ID = $ID;
			$STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ID, $STATUS_TITLE);
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($STATUS_ID);
		}

		// check if document is locked
		if(CIBlockElement::WF_IsLocked($ID, $locked_by, $date_lock))
		{
			if($locked_by > 0)
			{
				$rsUser = CUser::GetList(($by="ID"), ($order="ASC"), array("ID_EQUAL_EXACT" => $locked_by));
				if($arUser = $rsUser->GetNext())
					$locked_by = rtrim("[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);
			}
			$error = new _CIBlockError(2, "BLOCKED", GetMessage("IBLOCK_DOCUMENT_LOCKED", array("#ID#"=>$locked_by, "#DATE#"=>$date_lock)));
			break;
		}
	}
	elseif ($bBizproc)
	{

		$arDocumentStates = CBPDocument::GetDocumentStates(
			array(MODULE_ID, ENTITY, DOCUMENT_TYPE),
			($ID > 0) ? array(MODULE_ID, ENTITY, $ID) : null,
			"Y"
		);

		$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
		if ($ID > 0 && is_array($arElement))
		{
			if ($GLOBALS["USER"]->GetID() == $arElement["CREATED_BY"])
				$arCurrentUserGroups[] = "Author";
		}
		else
		{
			$arCurrentUserGroups[] = "Author";
		}

		if ($ID > 0)
		{
			$canWrite = CBPDocument::CanUserOperateDocument(
				$view=="Y" ? IBLOCK_DOCUMENT_OPERATION_READ_DOCUMENT : IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
				$GLOBALS["USER"]->GetID(),
				array(MODULE_ID, ENTITY, $ID),
				array("IBlockPermission" => $BlockPerm, "AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates)
			);
		}
		else
		{
			$canWrite = CBPDocument::CanUserOperateDocumentType(
				IBLOCK_DOCUMENT_OPERATION_WRITE_DOCUMENT,
				$GLOBALS["USER"]->GetID(),
				array(MODULE_ID, ENTITY, DOCUMENT_TYPE),
				array("IBlockPermission" => $BlockPerm, "AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates)
			);
		}

		if (!$canWrite)
		{
			$error = new _CIBlockError(1, "ACCESS_DENIED", GetMessage("IBLOCK_ACCESS_DENIED_STATUS"));
			break;
		}
	}

	//Find out files properties
	$arFileProps = array();
	$properties = CIBlockProperty::GetList(Array(), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_TYPE" => "F"));
	while($prop_fields = $properties->Fetch())
		$arFileProps[] = $prop_fields['ID'];

	//Assembly properties values from $_POST and $_FILES

	$PROP = $_POST['PROP'];

	//Recover some user defined properties
	if(is_array($PROP))
	{
		foreach($PROP as $k1 => $val1)
		{
			if(is_array($val1))
			{
				foreach($val1 as $k2 => $val2)
				{
					$text_name = preg_replace("/([^a-z0-9])/is", "_", "PROP[".$k1."][".$k2."][VALUE][TEXT]");
					if(array_key_exists($text_name, $_POST))
					{
						$type_name = preg_replace("/([^a-z0-9])/is", "_", "PROP[".$k1."][".$k2."][VALUE][TYPE]");
						$PROP[$k1][$k2]["VALUE"] = array(
							"TEXT" => $_POST[$text_name],
							"TYPE" => $_POST[$type_name],
						);
					}
				}
			}
		}

		foreach($PROP as $k1 => $val1)
		{
			if(is_array($val1))
			{
				foreach($val1 as $k2 => $val2)
				{
					if(!is_array($val2))
						$PROP[$k1][$k2] = array("VALUE" => $val2);
				}
			}
		}

		//Handle media library
		foreach($arFileProps as $id)
		{
			if(array_key_exists($id, $_POST["PROP"]) && is_array($_POST["PROP"][$id]))
			{
				foreach($_POST["PROP"][$id] as $prop_value_id => $prop_value)
				{
					$file_name = "";
					if(is_array($prop_value))
					{
						if(array_key_exists("VALUE", $prop_value))
							$file_name = $prop_value["VALUE"];
					}
					else
					{
						$file_name = $prop_value;
					}

					if(strlen($file_name) > 0 && is_file($_SERVER["DOCUMENT_ROOT"].$file_name))
					{
						$PROP[$id][$prop_value_id] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$file_name);
					}
				}
			}
		}
	}

	//transpose files array
	// [property id] [value id] = file array (name, type, tmp_name, error, size)
	$files = $_FILES["PROP"];
	if(is_array($files))
	{
		if(!is_array($PROP))
			$PROP = array();
		CAllFile::ConvertFilesToPost($_FILES["PROP"], $PROP);
	}

	if(is_array($PROP_del))
	{
		foreach($PROP_del as $k1=>$val1)
			foreach($val1 as $k2=>$val2)
				$PROP[$k1][$k2]["del"]=$val2;
	}

	$DESCRIPTION_PROP = $_POST["DESCRIPTION_PROP"];
	if(is_array($DESCRIPTION_PROP))
	{
		foreach($DESCRIPTION_PROP as $k1=>$val1)
		{
			foreach($val1 as $k2=>$val2)
			{
				if(is_set($PROP[$k1], $k2) && is_array($PROP[$k1][$k2]) && is_set($PROP[$k1][$k2], "DESCRIPTION"))
					$PROP[$k1][$k2]["DESCRIPTION"] = $val2;
				else
					$PROP[$k1][$k2] = Array("VALUE"=>$PROP[$k1][$k2], "DESCRIPTION"=>$val2);
			}
		}
	}

	function _prop_value_id_cmp($a, $b)
	{
		if(substr($a, 0, 1)==="n")
		{
			$a = intval(substr($a, 1));
			if(substr($b, 0, 1)==="n")
			{
				$b = intval(substr($b, 1));
				if($a < $b)
					return -1;
				elseif($a > $b)
					return 1;
				else
					return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			if(substr($b, 0, 1)==="n")
			{
				return -1;
			}
			else
			{
				if(preg_match("/^(\d+):(\d+)$/", $a, $a_match))
					$a = intval($a_match[2]);
				else
					$a = intval($a);

				if(preg_match("/^(\d+):(\d+)$/", $b, $b_match))
					$b = intval($b_match[2]);
				else
					$b = intval($b);

				if($a < $b)
					return -1;
				elseif($a > $b)
					return 1;
				else
					return 0;
			}
		}
	}

	//Now reorder property values
	if(is_array($PROP) && count($arFileProps) > 0)
	{
		foreach($arFileProps as $id)
		{
			if(is_array($PROP[$id]))
				uksort($PROP[$id], "_prop_value_id_cmp");
		}
	}

	if(strlen($arIBlock["EDIT_FILE_BEFORE"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_BEFORE"]))
	{
		include($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_BEFORE"]);
	}
	elseif(strlen($arIBTYPE["EDIT_FILE_BEFORE"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_BEFORE"]))
	{
		include($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_BEFORE"]);
	}

	if ($historyId <= 0 && $ID > 0 && $REQUEST_METHOD=="GET" && strlen($_REQUEST["stop_bizproc"]) > 0 && check_bitrix_sessid() && $view!="Y" && $bBizproc)
	{
		CBPDocument::TerminateWorkflow(
			$_REQUEST["stop_bizproc"],
			array(MODULE_ID, ENTITY, $ID),
			$ar
		);

		if (count($ar) > 0)
		{
			$str = "";
			foreach ($ar as $a)
				$str .= $a["message"];
			$error = new _CIBlockError(2, "STOP_BP_ERROR", $str);
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPageParam("", Array("stop_bizproc", "sessid")));
		}
	}

	if($historyId <= 0 && $REQUEST_METHOD=="POST" && strlen($Update)>0 && $view!="Y" && (!$error) && empty($dontsave))
	{

		$DB->StartTransaction();

		if(!(check_bitrix_sessid() || $_SESSION['IBLOCK_CUSTOM_FORM']===true))
		{
			$strWarning .= GetMessage("IBLOCK_WRONG_SESSION")."<br>";
			$error = new _CIBlockError(2, "BAD_SAVE", $strWarning);
			$bVarsFromForm = true;
		}
		elseif($WF=="Y" && $bWorkflow && intval($_POST["WF_STATUS_ID"])<=0)
			$strWarning .= GetMessage("IBLOCK_WRONG_WF_STATUS")."<br>";
		elseif($WF=="Y" && $bWorkflow && CIBlockElement::WF_GetStatusPermission($_POST["WF_STATUS_ID"])<1)
			$strWarning .= GetMessage("IBLOCK_ACCESS_DENIED_STATUS")." [".$_POST["WF_STATUS_ID"]."]."."<br>";
		else
		{
			if(!$customTabber->Check())
			{
				if($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString();
				else
					$strWarning .= "Error. ";
			}
			else
			{
				$bCatalog = false;
				if (
					CModule::IncludeModule("catalog")
					&& CCatalog::GetByID($IBLOCK_ID)
					&& file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_validator.php")
				)
				{
					$bCatalog = true;
					// errors'll be appended to $strWarning;
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_validator.php");
				}

				if ($bBizproc)
				{
					$arBizProcParametersValues = array();
					foreach ($arDocumentStates as $arDocumentState)
					{
						if (strlen($arDocumentState["ID"]) <= 0)
						{
							$arErrorsTmp = array();

							$arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]] = CBPDocument::StartWorkflowParametersValidate(
								$arDocumentState["TEMPLATE_ID"],
								$arDocumentState["TEMPLATE_PARAMETERS"],
								array(MODULE_ID, ENTITY, DOCUMENT_TYPE),
								$arErrorsTmp
							);

							if (count($arErrorsTmp) > 0)
							{
								foreach ($arErrorsTmp as $e)
									$strWarning .= $e["message"]."<br />";
							}
						}
					}
				}

				if (strlen($strWarning) <= 0)
				{
					$bs = new CIBlockElement;

					if(array_key_exists("PREVIEW_PICTURE", $_FILES))
						$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
					elseif(isset($_REQUEST["PREVIEW_PICTURE"]) && is_file($_SERVER["DOCUMENT_ROOT"].$_REQUEST["PREVIEW_PICTURE"]))
					{
						$arPREVIEW_PICTURE = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$_REQUEST["PREVIEW_PICTURE"]);
						$arPREVIEW_PICTURE["COPY_FILE"] = "Y";
					}
					else
						$arPREVIEW_PICTURE = array();

					$arPREVIEW_PICTURE["del"] = ${"PREVIEW_PICTURE_del"};
					$arPREVIEW_PICTURE["description"] = ${"PREVIEW_PICTURE_descr"};

					if(array_key_exists("DETAIL_PICTURE", $_FILES))
						$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
					elseif(isset($_REQUEST["DETAIL_PICTURE"]) && is_file($_SERVER["DOCUMENT_ROOT"].$_REQUEST["DETAIL_PICTURE"]))
					{
						$arDETAIL_PICTURE = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$_REQUEST["DETAIL_PICTURE"]);
						$arDETAIL_PICTURE["COPY_FILE"] = "Y";
					}
					else
						$arDETAIL_PICTURE = array();

					$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
					$arDETAIL_PICTURE["description"] = ${"DETAIL_PICTURE_descr"};

					$arFields = Array(
						"ACTIVE" => $_POST["ACTIVE"],
						"MODIFIED_BY" => $USER->GetID(),
						"IBLOCK_SECTION" => $_POST["IBLOCK_SECTION"],
						"IBLOCK_ID" => $IBLOCK_ID,
						"ACTIVE_FROM" => $_POST["ACTIVE_FROM"],
						"ACTIVE_TO" => $_POST["ACTIVE_TO"],
						"SORT" => $_POST["SORT"],
						"NAME" => $_POST["NAME"],
						"CODE" => $_POST["CODE"],
						"TAGS" => $_POST["TAGS"],
						"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
						"PREVIEW_TEXT" => $_POST["PREVIEW_TEXT"],
						"PREVIEW_TEXT_TYPE" => $_POST["PREVIEW_TEXT_TYPE"],
						"DETAIL_PICTURE" => $arDETAIL_PICTURE,
						"DETAIL_TEXT" => $_POST["DETAIL_TEXT"],
						"DETAIL_TEXT_TYPE" => $_POST["DETAIL_TEXT_TYPE"],
						"PROPERTY_VALUES" => $PROP,
						);
					if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
						$arFields["XML_ID"] = $_POST["XML_ID"];

					if ($bWorkflow)
					{
						$arFields["WF_COMMENTS"] = $_POST["WF_COMMENTS"];
						if(intval($_POST["WF_STATUS_ID"])>0)
						{
							$arFields["WF_STATUS_ID"] = $_POST["WF_STATUS_ID"];
						}
					}

					if($bBizproc && $ID<=0)
						$arFields["BP_PUBLISHED"] = "N";

					$bCreateRecord = false;
					if($ID>0)
						$res = $bs->Update($ID, $arFields, $WF=="Y", true, true);
					else
					{
						$bCreateRecord = true;
						$ID = $bs->Add($arFields, $bWorkflow, true, true);
						$res = ($ID>0);
						$PARENT_ID = $ID;
					}

					if(!$res)
						$strWarning .= $bs->LAST_ERROR."<br>";
					else
						CIBlockElement::RecalcSections($ID);

					if(($bCatalog || CModule::IncludeModule("catalog") && CCatalog::GetByID($IBLOCK_ID)) && strlen($strWarning)<=0)
					{
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit_action.php");
					}
				} // if ($strWarning)

				if ($bBizproc)
				{
					if (strlen($strWarning) <= 0)
					{
						$arBizProcWorkflowId = array();
						foreach ($arDocumentStates as $arDocumentState)
						{
							if (strlen($arDocumentState["ID"]) <= 0)
							{
								$arErrorsTmp = array();

								$arBizProcWorkflowId[$arDocumentState["TEMPLATE_ID"]] = CBPDocument::StartWorkflow(
									$arDocumentState["TEMPLATE_ID"],
									array(MODULE_ID, ENTITY, $ID),
									$arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]],
									$arErrorsTmp
								);

								if (count($arErrorsTmp) > 0)
								{
									foreach ($arErrorsTmp as $e)
										$strWarning .= $e["message"]."<br />";
								}
							}
						}
					}

					if (strlen($strWarning) <= 0)
					{
						$bizprocIndex = intval($_REQUEST["bizproc_index"]);
						if ($bizprocIndex > 0)
						{
							for ($i = 1; $i <= $bizprocIndex; $i++)
							{
								$bpId = trim($_REQUEST["bizproc_id_".$i]);
								$bpTemplateId = intval($_REQUEST["bizproc_template_id_".$i]);
								$bpEvent = trim($_REQUEST["bizproc_event_".$i]);

								if (strlen($bpEvent) > 0)
								{
									if (strlen($bpId) > 0)
									{
										if (!array_key_exists($bpId, $arDocumentStates))
											continue;
									}
									else
									{
										if (!array_key_exists($bpTemplateId, $arDocumentStates))
											continue;
										$bpId = $arBizProcWorkflowId[$bpTemplateId];
									}

									$arErrorTmp = array();
									CBPDocument::SendExternalEvent(
										$bpId,
										$bpEvent,
										array("Groups" => $arCurrentUserGroups, "User" => $GLOBALS["USER"]->GetID()),
										$arErrorTmp
									);

									if (count($arErrorsTmp) > 0)
									{
										foreach ($arErrorsTmp as $e)
											$strWarning .= $e["message"]."<br />";
									}
								}
							}
						}

						$arDocumentStates = null;
						CBPDocument::AddDocumentToHistory(array(MODULE_ID, ENTITY, $ID), $arFields["NAME"], $GLOBALS["USER"]->GetID());
					}
				}

			}//if(!$customTabber->Check())
		}

		if(strlen($strWarning)<=0)
		{
			if(!$customTabber->Action())
			{
				if ($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString();
				else
					$strWarning .= "Error. ";
			}
		}

		if(strlen($strWarning)>0)
		{
			$error = new _CIBlockError(2, "BAD_SAVE", $strWarning);
			$bVarsFromForm = true;
			$DB->Rollback();
		}
		else
		{
			if($bWorkflow)
				CIBlockElement::WF_UnLock($ID);

			$arFields['ID'] = $ID;
			if(function_exists('BXIBlockAfterSave'))
				BXIBlockAfterSave($arFields);

			$DB->Commit();

			if(strlen($apply) <= 0)
			{
				if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
				{
					?><script type="text/javascript">
					window.opener.<?php echo $strLookup; ?>.AddValue(<?php echo $ID;?>);
					window.close();
					</script><?php
				}
				elseif(strlen($return_url) > 0)
				{
					if(strpos($return_url, "#")!==false)
					{
						$rsElement = CIBlockElement::GetList(array(), array("ID" => $ID), false, false, array("DETAIL_PAGE_URL"));
						$arElement = $rsElement->Fetch();
						if($arElement)
							$return_url = CIBlock::ReplaceDetailUrl($return_url, $arElement, true, "E");
					}

					if(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
					{
						if($return_url === "reload_absence_calendar")
						{
							echo '<script type="text/javascript">top.jsBXAC.__reloadCurrentView();</script>';
							die();
						}
						else
						{
							LocalRedirect($return_url);
						}
					}
					else
					{
						LocalRedirect($return_url);
					}
				}
				else
				{
					LocalRedirect("/bitrix/admin/".CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section))));
				}
			}
			else
			{
				LocalRedirect("/bitrix/admin/iblock_element_edit.php?ID=".$ID.($WF=="Y"?"&WF=Y":"")."&lang=".LANG. "&type=".htmlspecialchars($type)."&".$tabControl->ActiveTabParam()."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"").((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE) ? "&lookup=".urlencode($strLookup) : ''));
			}
		}
	}

	if(!empty($dontsave) && check_bitrix_sessid())
	{
		if($bWorkflow)
			CIBlockElement::WF_UnLock($ID);

		if(strlen($return_url)>0)
		{
			if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
			{
					?><script type="text/javascript">
					window.opener.<?php echo $strLookup; ?>.AddValue(<?php echo $ID;?>);
					window.close();
					</script><?php
			}
			elseif(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
			{
				echo '<script type="text/javascript">top.BX.closeWait(); top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();</script>';
				die();
			}
			else
			{
				$rsElement = CIBlockElement::GetList(array(), array("=ID" => $ID), false, array("nTopCount" => 1), array("DETAIL_PAGE_URL"));
				$arElement = $rsElement->Fetch();
				if($arElement)
					$return_url = CIBlock::ReplaceDetailUrl($return_url, $arElement, true, "E");
				LocalRedirect($return_url);
			}
		}
		else
		{
			if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
			{
				?><script type="text/javascript">
					window.close();
					</script><?php
			}
			else
			{
				LocalRedirect("/bitrix/admin/".CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section))));
			}
		}
	}

}while(false);

if($error && $error->err_level==1)
{
	if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	}
	CAdminMessage::ShowOldStyleError($error->GetErrorText());
}
else
{
	if(!$arIBlock["ELEMENT_NAME"])
		$arIBlock["ELEMENT_NAME"] = $arIBTYPE["ELEMENT_NAME"]? $arIBTYPE["ELEMENT_NAME"]: GetMessage("IBEL_E_IBLOCK_ELEMENT");
	if(!$arIBlock["SECTIONS_NAME"])
		$arIBlock["SECTIONS_NAME"] = $arIBTYPE["SECTION_NAME"]? $arIBTYPE["SECTION_NAME"]: GetMessage("IBEL_E_IBLOCK_SECTIONS");

	ClearVars("str_");
	ClearVars("str_prev_");
	ClearVars("prn_");
	$str_ACTIVE="Y";
	$str_SORT="500";
	$str_DETAIL_TEXT_TYPE="html";
	$str_PREVIEW_TEXT_TYPE="html";

	if(!$error && $bWorkflow && $view!="Y")
	{
		if(!$bCopy)
			CIBlockElement::WF_Lock($ID);
		else
			CIBlockElement::WF_UnLock($ID);
	}

	if($historyId <= 0 && $view=="Y")
	{
		$WF_ID = $ID;
		$ID = CIBlockElement::GetRealElement($ID);

		if($PREV_ID)
		{
			$prev_result = CIBlockElement::GetByID($PREV_ID);
			$prev_arElement = $prev_result->ExtractFields("str_prev_");
			if(!$prev_arElement)
				$PREV_ID = 0;
		}
	}

	$str_IBLOCK_ELEMENT_SECTION = Array();
	$str_ACTIVE = $arIBlock["FIELDS"]["ACTIVE"]["DEFAULT_VALUE"] === "N"? "N": "Y";
	$str_NAME = htmlspecialchars($arIBlock["FIELDS"]["NAME"]["DEFAULT_VALUE"]);
	if($arIBlock["FIELDS"]["ACTIVE_FROM"]["DEFAULT_VALUE"] === "=now")
		$str_ACTIVE_FROM = ConvertTimeStamp(false, "FULL");
	elseif($arIBlock["FIELDS"]["ACTIVE_FROM"]["DEFAULT_VALUE"] === "=today")
		$str_ACTIVE_FROM = ConvertTimeStamp(false, "SHORT");

	if(intval($arIBlock["FIELDS"]["ACTIVE_TO"]["DEFAULT_VALUE"]) > 0)
		$str_ACTIVE_TO = ConvertTimeStamp(time() + intval($arIBlock["FIELDS"]["ACTIVE_TO"]["DEFAULT_VALUE"])*24*60*60, "FULL");

	$str_PREVIEW_TEXT_TYPE = $arIBlock["FIELDS"]["PREVIEW_TEXT_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
	$str_PREVIEW_TEXT = htmlspecialchars($arIBlock["FIELDS"]["PREVIEW_TEXT"]["DEFAULT_VALUE"]);
	$str_DETAIL_TEXT_TYPE = $arIBlock["FIELDS"]["DETAIL_TEXT_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
	$str_DETAIL_TEXT = htmlspecialchars($arIBlock["FIELDS"]["DETAIL_TEXT"]["DEFAULT_VALUE"]);

	if ($historyId > 0)
	{
		$view = "Y";
		foreach ($arResult["DOCUMENT"]["FIELDS"] as $k => $v)
			${"str_".$k} = $v;

	}
	else
	{
		$result = CIBlockElement::GetByID($WF_ID);

		if($arElement = $result->ExtractFields("str_"))
		{
			if($str_IN_SECTIONS=="N")
				$str_IBLOCK_ELEMENT_SECTION[] = 0;
			else
			{
				$result = CIBlockElement::GetElementGroups($WF_ID);
				while($ar = $result->Fetch())
					$str_IBLOCK_ELEMENT_SECTION[] = $ar["ID"];
			}
		}
		else
		{
			$WF_ID=0;
			$ID=0;
			if($IBLOCK_SECTION_ID>0)
				$str_IBLOCK_ELEMENT_SECTION[] = $IBLOCK_SECTION_ID;
		}
	}

	if($ID > 0 && !$bCopy)
	{
		if($view=="Y")
			$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".$arElement["NAME"]." - ".GetMessage("IBLOCK_ELEMENT_EDIT_VIEW"));
		else
			$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".$arElement["NAME"]." - ".GetMessage("IBLOCK_EDIT_TITLE"));
	}
	else
	{
		$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["ELEMENT_NAME"].": ".GetMessage("IBLOCK_NEW_TITLE"));
	}

	if($arIBTYPE["SECTIONS"]=="Y")
		$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0));
	else
		$sSectionUrl = CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section'=>0));

	$adminChain->AddItem(array(
		"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
		"LINK" => htmlspecialchars($sSectionUrl),
	));

	if($find_section_section > 0)
		$sLastFolder = $sSectionUrl;
	else
		$sLastFolder = '';

	if($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
		while($ar_nav = $nav->GetNext())
		{
			$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"]));
			$adminChain->AddItem(array(
				"TEXT" => $ar_nav["NAME"],
				"LINK" => htmlspecialchars($sSectionUrl),
			));

			if($ar_nav["ID"] != $find_section_section)
				$sLastFolder = $sSectionUrl;
		}
	}

	if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	}

	if($bVarsFromForm)
	{
		$DB->InitTableVarsForEdit("b_iblock_element", "", "str_");
		$str_IBLOCK_ELEMENT_SECTION = $IBLOCK_SECTION;
	}

	$arPROP_tmp = Array();
	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID, "CHECK_PERMISSIONS"=>"N"));
	while($prop_fields = $properties->Fetch())
	{
		$prop_values = Array();
		$prop_values_with_descr = Array();
		if($bVarsFromForm)
		{
			if($prop_fields["PROPERTY_TYPE"]=="F")
			{
				$db_prop_values = CIBlockElement::GetProperty($IBLOCK_ID, $WF_ID, "id", "asc", Array("ID"=>$prop_fields["ID"], "EMPTY"=>"N"));
				while($res = $db_prop_values->Fetch())
				{
					$prop_values[$res["PROPERTY_VALUE_ID"]] = $res["VALUE"];
					$prop_values_with_descr[$res["PROPERTY_VALUE_ID"]] = array("VALUE"=>$res["VALUE"],"DESCRIPTION"=>$res["DESCRIPTION"]);
				}
			}
			else
			{
				if(array_key_exists($prop_fields["ID"], $PROP))
					$prop_values = $PROP[$prop_fields["ID"]];
				else
					$prop_values = $PROP[$prop_fields["CODE"]];
				$prop_values_with_descr = $prop_values;
			}
		}
		else
		{
			if ($historyId > 0)
			{
				$vx = $arResult["DOCUMENT"]["PROPERTIES"][(strlen(trim($prop_fields["CODE"])) > 0) ? $prop_fields["CODE"] : $prop_fields["ID"]];

				$prop_values = array();
				if (is_array($vx["VALUE"]) && is_array($vx["DESCRIPTION"]))
				{
					for ($i = 0, $cnt = count($vx["VALUE"]); $i < $cnt; $i++)
						$prop_values[] = array("VALUE" => $vx["VALUE"][$i], "DESCRIPTION" => $vx["DESCRIPTION"][$i]);
				}
				else
				{
					$prop_values[] = array("VALUE" => $vx["VALUE"], "DESCRIPTION" => $vx["DESCRIPTION"]);
				}

				$prop_values_with_descr = $prop_values;
			}
			elseif($ID>0)
			{
				$db_prop_values = CIBlockElement::GetProperty($IBLOCK_ID, $WF_ID, "id", "asc", Array("ID"=>$prop_fields["ID"], "EMPTY"=>"N"));
				while($res = $db_prop_values->Fetch())
				{
					if($res["WITH_DESCRIPTION"]=="Y")
						$prop_values[$res["PROPERTY_VALUE_ID"]] = Array("VALUE"=>$res["VALUE"], "DESCRIPTION"=>$res["DESCRIPTION"]);
					else
						$prop_values[$res["PROPERTY_VALUE_ID"]] = $res["VALUE"];
					$prop_values_with_descr[$res["PROPERTY_VALUE_ID"]] = Array("VALUE"=>$res["VALUE"], "DESCRIPTION"=>$res["DESCRIPTION"]);
				}
			}
		}

		$prop_fields["VALUE"] = $prop_values;
		$prop_fields["~VALUE"] = $prop_values_with_descr;
		if(strlen(trim($prop_fields["CODE"]))>0)
			$arPROP_tmp[$prop_fields["CODE"]] = $prop_fields;
		else
			$arPROP_tmp[$prop_fields["ID"]] = $prop_fields;
	}
	$PROP = $arPROP_tmp;

	$intSKU = '';
	$intSKU = intval($_REQUEST["sku"]);
	if (0 == $ID)
	{
		if (0 < $intSKU)
		{
			$intSKUValue = intval($_REQUEST["skuvalue"]);
			if (0 < $intSKUValue)
			{
				foreach ($PROP as $strCode => $arProperty)
				{
					if ($intSKU == $arProperty['ID'])
					{
						$arProperty['VALUE'] = array(
							'n0' => array(
								'VALUE' => $intSKUValue,
							),
						);
						$arProperty['~VALUE'] = $arProperty['VALUE'];
						$PROP[$strCode] = $arProperty;
						break;
					}
				}
			}
		}
	}

	$aMenu = array();
	if (false == ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE)))
	{
		$aMenu = array(
			array(
				"TEXT" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
				"LINK" => CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section))),
				"ICON" => "btn_list",
			)
		);

		if($ID > 0 && !$bCopy)
		{
			$MENU_SECTION_ID = intval($IBLOCK_SECTION_ID)? intval($IBLOCK_SECTION_ID): intval($find_section_section);
			$aMenu[] = array("SEPARATOR"=>"Y");
			$aMenu[] = array(
				"TEXT"=>htmlspecialcharsex($arIBlock["ELEMENT_ADD"]),
				"LINK"=>"iblock_element_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&IBLOCK_SECTION_ID=".$MENU_SECTION_ID."&find_section_section=".intval($find_section_section),
				"ICON"=>"btn_new",
			);
			$aMenu[] = array(
				"TEXT"=>GetMessage("IBEL_E_COPY_ELEMENT"),
				"TITLE"=>GetMessage("IBEL_E_COPY_ELEMENT_TITLE"),
				"LINK"=>"iblock_element_edit.php?type=".htmlspecialchars($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&IBLOCK_SECTION_ID=".$MENU_SECTION_ID."&find_section_section=".intval($find_section_section)."&action=copy&ID=".$ID,
				"ICON"=>"btn_copy",
			);

			if($BlockPerm >="W")
			{
				$urlDelete = CIBlock::GetAdminElementListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section), 'action'=>'delete'));
				$urlDelete .= '&'.bitrix_sessid_get();
				$urlDelete .= '&ID='.(preg_match('/^iblock_list_admin\.php/', $urlDelete)? "E": "").$ID;
				$aMenu[] = array(
					"TEXT"=>htmlspecialcharsex($arIBlock["ELEMENT_DELETE"]),
					"LINK"=>"javascript:if(confirm('".GetMessage("IBLOCK_ELEMENT_DEL_CONF")."'))window.location='".CUtil::JSEscape($urlDelete)."';",
					"ICON"=>"btn_delete",
				);
			}
		}
		if(!$bCustomForm)
		{
			$link = DeleteParam(array("mode"));
			$link = $GLOBALS["APPLICATION"]->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");
			$aMenu[] = array(
				"TEXT"=>GetMessage("IBEL_E_SETTINGS"),
				"TITLE"=>GetMessage("IBEL_E_SETTINGS_TITLE"),
				"LINK"=>"javascript:".$tabControl->GetName().".ShowSettings('".urlencode($link)."')",
				"ICON"=>"btn_settings",
			);
		}
		$context = new CAdminContextMenu($aMenu);
		$context->Show();
	}

	if($error)
		CAdminMessage::ShowOldStyleError($error->GetErrorText());

	$bFileman = CModule::IncludeModule("fileman");
	$arTranslit = $arIBlock["FIELDS"]["CODE"]["DEFAULT_VALUE"];
	$bLinked = (!strlen($str_TIMESTAMP_X) || $bCopy) && $_POST["linked_state"]!=='N';

if(strlen($arIBlock["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"])):
	include($_SERVER["DOCUMENT_ROOT"].$arIBlock["EDIT_FILE_AFTER"]);
	$_SESSION['IBLOCK_CUSTOM_FORM'] = true;
elseif(strlen($arIBTYPE["EDIT_FILE_AFTER"])>0 && is_file($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"])):
	include($_SERVER["DOCUMENT_ROOT"].$arIBTYPE["EDIT_FILE_AFTER"]);
	$_SESSION['IBLOCK_CUSTOM_FORM'] = true;
else:
?>

<?
	//////////////////////////
	//START of the custom form
	//////////////////////////

	//We have to explicitly call calendar and editor functions because
	//first output may be discarded by form settings

	$tabControl->BeginPrologContent();
	echo CAdminCalendar::ShowScript();

	if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && $bFileman)
	{
		//TODO:This dirty hack will be replaced by special method like calendar do
		echo '<div style="display:none">';
		CFileMan::AddHTMLEditorFrame(
			"SOME_TEXT",
			"",
			"SOME_TEXT_TYPE",
			"text",
			array(
				'height' => 450,
				'width' => '100%'
			),
			"N",
			0,
			"",
			"",
			$arIBlock["LID"]
		);
		echo '</div>';
	}
	if($bFileman)
		CMedialibTabControl::ShowScript();

	if($arTranslit["TRANSLITERATION"] == "Y")
	{
		CUtil::InitJSCore(array('translit'));
		?>
		<script>
		var linked=<?if($bLinked) echo 'true'; else echo 'false';?>;
		function set_linked()
		{
			linked=!linked;

			var name_link = document.getElementById('name_link');
			if(name_link)
			{
				if(linked)
					name_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					name_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var code_link = document.getElementById('code_link');
			if(code_link)
			{
				if(linked)
					code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var linked_state = document.getElementById('linked_state');
			if(linked_state)
			{
				if(linked)
					linked_state.value='Y';
				else
					linked_state.value='N';
			}
		}
		var oldValue = '';
		function transliterate()
		{
			if(linked)
			{
				var from = document.getElementById('NAME');
				var to = document.getElementById('CODE');
				if(from && to && oldValue != from.value)
				{
					BX.translit(from.value, {
						'max_len' : <?echo intval($arTranslit['TRANS_LEN'])?>,
						'change_case' : '<?echo $arTranslit['TRANS_CASE']?>',
						'replace_space' : '<?echo $arTranslit['TRANS_SPACE']?>',
						'replace_other' : '<?echo $arTranslit['TRANS_OTHER']?>',
						'delete_repeat_replace' : <?echo $arTranslit['TRANS_EAT'] == 'Y'? 'true': 'false'?>,
						'use_google' : <?echo $arTranslit['USE_GOOGLE'] == 'Y'? 'true': 'false'?>,
						'callback' : function(result){to.value = result; setTimeout('transliterate()', 250);}
					});
					oldValue = from.value;
				}
				else
				{
					setTimeout('transliterate()', 250);
				}
			}
			else
			{
				setTimeout('transliterate()', 250);
			}
		}
		transliterate();
		</script>
		<?
	}

	$tabControl->EndPrologContent();

	$tabControl->BeginEpilogContent();
?>

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
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__n',p);
		if(s<0)break;
		var e = sHTML.indexOf('_',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__n'+(++n)+'_'+sHTML.substr(e+1);
		p=e+1;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('__N',p);
		if(s<0)break;
		var e = sHTML.indexOf('__',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'__N'+(++n)+'__'+sHTML.substr(e+2);
		p=e+2;
	}
	var p = 0;
	while(true)
	{
		var s = sHTML.indexOf('xxn',p);
		if(s<0)break;
		var e = sHTML.indexOf('xx',s+2);
		if(e<0)break;
		var n = parseInt(sHTML.substr(s+3,e-s));
		sHTML = sHTML.substr(0, s)+'xxn'+(++n)+'xx'+sHTML.substr(e+2);
		p=e+2;
	}
	var p = 0;
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

	var patt = new RegExp ("<"+"script"+">[^\000]*?<"+"\/"+"script"+">", "ig");
	var code = sHTML.match(patt);
	if(code)
	{
		for(var i = 0; i < code.length; i++)
		{
			if(code[i] != '')
			{
				var s = code[i].substring(8, code[i].length-9);
				jsUtils.EvalGlobal(s);
			}
		}
	}
}
//-->
</script>


<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="linked_state" id="linked_state" value="<?if($bLinked) echo 'Y'; else echo 'N';?>">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<input type="hidden" name="WF" value="<?echo htmlspecialchars($WF)?>">
<input type="hidden" name="return_url" value="<?echo htmlspecialchars($return_url)?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?echo $ID?>">
<?endif;?>
<input type="hidden" name="IBLOCK_SECTION_ID" value="<?echo IntVal($IBLOCK_SECTION_ID)?>">

<?
$tabControl->EndEpilogContent();

$customTabber->SetErrorState($bVarsFromForm);
$tabControl->AddTabs($customTabber);
$strFormAction = "/bitrix/admin/iblock_element_edit.php?type=".urlencode($type)."&lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section);
if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
{
	$strFormAction .= "&lookup=".urlencode($strLookup);
}
$tabControl->Begin(array(
	"FORM_ACTION" => $strFormAction,
));

$tabControl->BeginNextFormTab();
?>
	<?
	if($ID > 0 && !$bCopy):
		$p = CIblockElement::GetByID($ID);
		$pr = $p->ExtractFields("prn_");
	endif;
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("IBLOCK_FIELD_ACTIVE").":", false, "Y", $str_ACTIVE=="Y");
$tabControl->BeginCustomField("ACTIVE_FROM", GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_FROM"), $arIBlock["FIELDS"]["ACTIVE_FROM"]["IS_REQUIRED"] === "Y");
?>
	<tr id="tr_ACTIVE_FROM">
		<td><?echo $tabControl->GetCustomLabelHTML()?>:<br>(<?echo CLang::GetDateFormat("SHORT");?> / <?echo CLang::GetDateFormat("FULL");?>)</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_FROM", $str_ACTIVE_FROM, 19, true)?></td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVE_FROM", '<input type="hidden" id="ACTIVE_FROM" name="ACTIVE_FROM" value="'.$str_ACTIVE_FROM.'">');
$tabControl->BeginCustomField("ACTIVE_TO", GetMessage("IBLOCK_FIELD_ACTIVE_PERIOD_TO"), $arIBlock["FIELDS"]["ACTIVE_TO"]["IS_REQUIRED"] === "Y");
?>
	<tr id="tr_ACTIVE_TO">
		<td><?echo $tabControl->GetCustomLabelHTML()?>:<br>(<?echo CLang::GetDateFormat("SHORT");?> / <?echo CLang::GetDateFormat("FULL");?>)</td>
		<td><?echo CAdminCalendar::CalendarDate("ACTIVE_TO", $str_ACTIVE_TO, 19, true)?></td>
	</tr>

<?
$tabControl->EndCustomField("ACTIVE_TO", '<input type="hidden" id="ACTIVE_TO" name="ACTIVE_TO" value="'.$str_ACTIVE_TO.'">');

if($arTranslit["TRANSLITERATION"] == "Y")
{
	$tabControl->BeginCustomField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true);
	?>
		<tr id="tr_NAME">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>
				<input type="text" size="50" name="NAME" id="NAME" maxlength="255" value="<?echo $str_NAME?>"><image id="name_link" title="<?echo GetMessage("IBEL_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("NAME",
		'<input type="hidden" name="NAME" id="NAME" value="'.$str_NAME.'">'
	);

	$tabControl->BeginCustomField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["CODE"]["IS_REQUIRED"] === "Y");
	?>
		<tr id="tr_CODE">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>

				<input type="text" size="50" name="CODE" id="CODE" maxlength="255" value="<?echo $str_CODE?>"><image id="code_link" title="<?echo GetMessage("IBEL_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("CODE",
		'<input type="hidden" name="CODE" id="CODE" value="'.$str_CODE.'">'
	);
}
else
{
	$tabControl->AddEditField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true, array("size" => 50, "maxlength" => 255), $str_NAME);
}

if(count($PROP)>0):
	$tabControl->AddSection("IBLOCK_ELEMENT_PROP_VALUE", GetMessage("IBLOCK_ELEMENT_PROP_VALUE"));

	foreach($PROP as $prop_code=>$prop_fields):
		$prop_values = $prop_fields["VALUE"];
		$tabControl->BeginCustomField("PROPERTY_".$prop_fields["ID"], $prop_fields["NAME"], $prop_fields["IS_REQUIRED"]==="Y");
		?>
		<tr id="tr_PROPERTY_<?echo $prop_fields["ID"];?>">
			<td valign="top"><?echo $tabControl->GetCustomLabelHTML();?>:</td>
			<td><?_ShowPropertyField('PROP['.$prop_fields["ID"].']', $prop_fields, $prop_fields["VALUE"], (($historyId <= 0) && (!$bVarsFromForm) && ($ID<=0)), $bVarsFromForm, 50000, $tabControl->GetFormName());?></td>
		</tr>
		<?
			$hidden = "";
			if(!is_array($prop_fields["~VALUE"]))
				$values = Array();
			else
				$values = $prop_fields["~VALUE"];
			$start = 1;
			foreach($values as $key=>$val)
			{
				if($bCopy)
				{
					$key = "n".$start;
					$start++;
				}

				if(is_array($val) && array_key_exists("VALUE",$val))
				{
					$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][VALUE]', $val["VALUE"]);
					$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][DESCRIPTION]', $val["DESCRIPTION"]);
				}
				else
				{
					$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][VALUE]', $val);
					$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][DESCRIPTION]', "");
				}
			}
		$tabControl->EndCustomField("PROPERTY_".$prop_fields["ID"], $hidden);
		endforeach;?>
	<?endif?>

	<?
	if ($view!="Y" && CModule::IncludeModule("catalog") && CCatalog::GetByID($IBLOCK_ID))
	{
		$tabControl->BeginCustomField("CATALOG", GetMessage("IBLOCK_TCATALOG"), true);
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit.php");
		$tabControl->EndCustomField("CATALOG", "");
	}
	
	if (false == ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE)))
	{	
		$rsLinkedProps = CIBlockProperty::GetList(array(), array(
			"PROPERTY_TYPE" => "E",
			"LINK_IBLOCK_ID" => $IBLOCK_ID,
			"ACTIVE" => "Y",
			"FILTRABLE" => "Y",
		));
		$arLinkedProp = $rsLinkedProps->GetNext();
		if($arLinkedProp)
		{
			$tabControl->BeginCustomField("LINKED_PROP", GetMessage("IBLOCK_ELEMENT_EDIT_LINKED"));
			?>
			<tr class="heading" id="tr_LINKED_PROP">
				<td colspan="2"><?echo $tabControl->GetCustomLabelHTML();?></td>
			</tr>
			<?
			do {
				$elements_name = CIBlock::GetArrayByID($arLinkedProp["IBLOCK_ID"], "ELEMENTS_NAME");
				if(strlen($elements_name) <= 0)
					$elements_name = GetMessage("IBLOCK_ELEMENT_EDIT_ELEMENTS");
			?>
			<tr id="tr_LINKED_PROP<?echo $arLinkedProp["ID"]?>">
				<td colspan="2"><a href="<?echo htmlspecialchars(CIBlock::GetAdminElementListLink($arLinkedProp["IBLOCK_ID"], array('set_filter'=>'Y', 'find_el_property_'.$arLinkedProp["ID"]=>$ID)))?>"><?echo CIBlock::GetArrayByID($arLinkedProp["IBLOCK_ID"], "NAME").": ".$elements_name?></a></td>
			</tr>
			<?
			} while ($arLinkedProp = $rsLinkedProps->GetNext());
			$tabControl->EndCustomField("LINKED_PROP", "");
		}
	}
	?>
<?

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("PREVIEW_PICTURE", GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE"), $arIBlock["FIELDS"]["PREVIEW_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("PREVIEW_PICTURE", $_REQUEST) && $arElement)
	$str_PREVIEW_PICTURE = intval($arElement["PREVIEW_PICTURE"]);
?>
	<tr id="tr_PREVIEW_PICTURE">
		<td valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td width="60%">
			<?if($bFileman):?>
				<?if($historyId > 0):?>
					<?echo CMedialib::InputFile(
						"PREVIEW_PICTURE", $str_PREVIEW_PICTURE,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)) //info
					);
					?>
					<br>
				<?else:?>
					<?echo CMedialib::InputFile(
						"PREVIEW_PICTURE", ($ID > 0 && !$bCopy? $str_PREVIEW_PICTURE: 0),
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
						array(), //file
						array(), //server
						array(), //media lib
						array(), //descr
						array(), //delete
						$arIBlock["FIELDS"]["PREVIEW_PICTURE"]["DEFAULT_VALUE"] //scale hint
					);
					?>
					<br>
				<?endif?>
			<?else:?>
				<?if($historyId > 0):?>
					<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
				<?elseif($ID > 0 && !$bCopy):?>
					<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
					<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
				<?else:?>
					<?echo CFile::InputFile("PREVIEW_PICTURE", 20, "", false, 0, "IMAGE", "", 40);?><br>
					<?echo CFile::ShowImage("", 200, 200, "border=0", "", true)?>
				<?endif?>
			<?endif;?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("PREVIEW_PICTURE", "");
$tabControl->BeginCustomField("PREVIEW_TEXT", GetMessage("IBLOCK_FIELD_PREVIEW_TEXT"), $arIBlock["FIELDS"]["PREVIEW_TEXT"]["IS_REQUIRED"] === "Y");
?>
	<tr class="heading" id="tr_PREVIEW_TEXT_LABEL">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>
	<?if($ID && $PREV_ID && $bWorkflow):?>
	<tr id="tr_PREVIEW_TEXT_DIFF">
		<td colspan="2">
			<div style="width:95%;background-color:white;border:1px solid black;padding:5px">
				<?echo getDiff($prev_arElement["PREVIEW_TEXT"], $arElement["PREVIEW_TEXT"])?>
			</div>
		</td>
	</tr>
	<?elseif(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && $bFileman):?>
	<tr id="tr_PREVIEW_TEXT_EDITOR">
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
			"PREVIEW_TEXT",
			$str_PREVIEW_TEXT,
			"PREVIEW_TEXT_TYPE",
			$str_PREVIEW_TEXT_TYPE,
			//300,
			array(
					'height' => 450,
					'width' => '100%'
				),
			"N",
			0,
			"",
			"",
			$arIBlock["LID"],
			true,
			false,
			array(
				'toolbarConfig' => CFileman::GetEditorToolbarConfig("iblock_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')),
				'saveEditorKey' => $IBLOCK_ID
			)
			);?>
		</td>
	</tr>
	<?else:?>
	<tr id="tr_PREVIEW_TEXT_TYPE">
		<td><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td><input type="radio" name="PREVIEW_TEXT_TYPE" id="PREVIEW_TEXT_TYPE_text" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <label for="PREVIEW_TEXT_TYPE_text"><?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> / <input type="radio" name="PREVIEW_TEXT_TYPE" id="PREVIEW_TEXT_TYPE_html" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <label for="PREVIEW_TEXT_TYPE_html"><?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label></td>
	</tr>
	<tr id="tr_PREVIEW_TEXT">
		<td colspan="2" align="center">
			<textarea cols="60" rows="10" name="PREVIEW_TEXT" style="width:100%"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif;
$tabControl->EndCustomField("PREVIEW_TEXT",
	'<input type="hidden" name="PREVIEW_TEXT" value="'.$str_PREVIEW_TEXT.'">'.
	'<input type="hidden" name="PREVIEW_TEXT_TYPE" value="'.$str_PREVIEW_TEXT_TYPE.'">'
);
$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("DETAIL_PICTURE", GetMessage("IBLOCK_FIELD_DETAIL_PICTURE"), $arIBlock["FIELDS"]["DETAIL_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("DETAIL_PICTURE", $_REQUEST) && $arElement)
	$str_DETAIL_PICTURE = intval($arElement["DETAIL_PICTURE"]);
?>
	<tr id="tr_DETAIL_PICTURE">
		<td valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td width="60%">
			<?if($bFileman):?>
				<?if($historyId > 0):?>
					<?echo CMedialib::InputFile(
						"DETAIL_PICTURE", $str_DETAIL_PICTURE,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)) //info
					);
					?>
					<br>
				<?else:?>
					<?echo CMedialib::InputFile(
						"DETAIL_PICTURE", ($ID > 0 && !$bCopy? $str_DETAIL_PICTURE: 0),
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
						array(), //file
						array(), //server
						array(), //media lib
						array(), //descr
						array(), //delete
						$arIBlock["FIELDS"]["DETAIL_PICTURE"]["DEFAULT_VALUE"] //scale hint
					);
					?>
					<br>
				<?endif?>
			<?else:?>
				<?if($historyId > 0):?>
					<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>
				<?elseif($ID > 0 && !$bCopy):?>
					<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE, false, 0, "IMAGE", "", 40);?><br>
					<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>
				<?else:?>
					<?echo CFile::InputFile("DETAIL_PICTURE", 20, "", false, 0, "IMAGE", "", 40);?><br>
					<?echo CFile::ShowImage("", 200, 200, "border=0", "", true)?>
				<?endif?>
			<?endif;?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("DETAIL_PICTURE", "");
$tabControl->BeginCustomField("DETAIL_TEXT", GetMessage("IBLOCK_FIELD_DETAIL_TEXT"), $arIBlock["FIELDS"]["DETAIL_TEXT"]["IS_REQUIRED"] === "Y");
?>
	<tr class="heading" id="tr_DETAIL_TEXT_LABEL">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>
	<?if($ID && $PREV_ID && $bWorkflow):?>
	<tr id="tr_DETAIL_TEXT_DIFF">
		<td colspan="2">
			<div style="width:95%;background-color:white;border:1px solid black;padding:5px">
				<?echo getDiff($prev_arElement["DETAIL_TEXT"], $arElement["DETAIL_TEXT"])?>
			</div>
		</td>
	</tr>
	<?elseif(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && $bFileman):?>
	<tr id="tr_DETAIL_TEXT_EDITOR">
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DETAIL_TEXT",
				$str_DETAIL_TEXT,
				"DETAIL_TEXT_TYPE",
				$str_DETAIL_TEXT_TYPE,
				array(
						'height' => 450,
						'width' => '100%'
					),
					"N",
					0,
					"",
					"",
					$arIBlock["LID"],
					true,
					false,
					array('toolbarConfig' => CFileman::GetEditorToolbarConfig("iblock_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')), 'saveEditorKey' => $IBLOCK_ID)
				);
		?></td>
	</tr>
	<?else:?>
	<tr id="tr_DETAIL_TEXT_TYPE">
		<td><?echo GetMessage("IBLOCK_DESC_TYPE")?></td>
		<td><input type="radio" name="DETAIL_TEXT_TYPE" id="DETAIL_TEXT_TYPE_text" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <label for="DETAIL_TEXT_TYPE_text"><?echo GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> / <input type="radio" name="DETAIL_TEXT_TYPE" id="DETAIL_TEXT_TYPE_html" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <label for="DETAIL_TEXT_TYPE_html"><?echo GetMessage("IBLOCK_DESC_TYPE_HTML")?></label></td>
	</tr>
	<tr id="tr_DETAIL_TEXT">
		<td colspan="2" align="center">
			<textarea cols="60" rows="20" name="DETAIL_TEXT" style="width:100%"><?echo $str_DETAIL_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
<?
$tabControl->EndCustomField("DETAIL_TEXT",
	'<input type="hidden" name="DETAIL_TEXT" value="'.$str_DETAIL_TEXT.'">'.
	'<input type="hidden" name="DETAIL_TEXT_TYPE" value="'.$str_DETAIL_TEXT_TYPE.'">'
);
?>

<?if($bTab2):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("SECTIONS", GetMessage("IBLOCK_SECTION"), $arIBlock["FIELDS"]["IBLOCK_SECTION"]["IS_REQUIRED"] === "Y");
	?>
	<tr id="tr_SECTIONS">
	<?if($arIBlock["SECTION_CHOOSER"] != "D" && $arIBlock["SECTION_CHOOSER"] != "P"):?>

		<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));?>
		<td valign="top" width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%">
		<select name="IBLOCK_SECTION[]" size="14" multiple>
			<option value="0"<?if(is_array($str_IBLOCK_ELEMENT_SECTION) && in_array(0, $str_IBLOCK_ELEMENT_SECTION))echo " selected"?>><?echo GetMessage("IBLOCK_UPPER_LEVEL")?></option>
		<?
			while($ar_l = $l->GetNext()):
				?><option value="<?echo $ar_l["ID"]?>"<?if(is_array($str_IBLOCK_ELEMENT_SECTION) && in_array($ar_l["ID"], $str_IBLOCK_ELEMENT_SECTION))echo " selected"?>><?echo str_repeat(" . ", $ar_l["DEPTH_LEVEL"])?><?echo $ar_l["NAME"]?></option><?
			endwhile;
		?>
		</select>
		</td>

	<?elseif($arIBlock["SECTION_CHOOSER"] == "D"):?>

		<td>
			<table id="sections">
			<?
			if(is_array($str_IBLOCK_ELEMENT_SECTION))
			{
				$i = 0;
				foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
				{
					$rsChain = CIBlockSection::GetNavChain($IBLOCK_ID, $section_id);
					$strPath = "";
					while($arChain = $rsChain->Fetch())
						$strPath .= $arChain["NAME"]."&nbsp;/&nbsp;";
					if(strlen($strPath) > 0)
					{
						?><tr>
							<td><?echo $strPath?></td>
							<td>
							<input type="button" value="<?echo GetMessage("MAIN_DELETE")?>" OnClick="deleteRow(this)">
							<input type="hidden" name="IBLOCK_SECTION[]" value="<?echo intval($section_id)?>">
							</td>
						</tr><?
					}
					$i++;
				}
			}
			?>
			<tr>
				<td>
				<script>
				function deleteRow(button)
				{
					var my_row = button.parentNode.parentNode;
					var table = document.getElementById('sections');
					if(table)
					{
						for(var i=0; i<table.rows.length; i++)
						{
							if(table.rows[i] == my_row)
							{
								table.deleteRow(i);
							}
						}
					}
				}
				function addPathRow()
				{
					var table = document.getElementById('sections');
					if(table)
					{
						var section_id = 0;
						var html = '';
						var lev = 0;
						var oSelect;
						while(oSelect = document.getElementById('select_IBLOCK_SECTION_'+lev))
						{
							if(oSelect.value < 1)
								break;
							html += oSelect.options[oSelect.selectedIndex].text+'&nbsp;/&nbsp;';
							section_id = oSelect.value;
							lev++;
						}
						if(section_id > 0)
						{
							var cnt = table.rows.length;
							var oRow = table.insertRow(cnt-1);

							var i=0;
							var oCell = oRow.insertCell(i++);
							oCell.innerHTML = html;

							var oCell = oRow.insertCell(i++);
							oCell.innerHTML =
								'<input type="button" value="<?echo GetMessage("MAIN_DELETE")?>" OnClick="deleteRow(this)">'+
								'<input type="hidden" name="IBLOCK_SECTION[]" value="'+section_id+'">';
						}
					}
				}
				function find_path(item, value)
				{
					if(item.id==value)
					{
						var a = Array(1);
						a[0] = item.id;
						return a;
					}
					else
					{
						for(var s in item.children)
						{
							if(ar = find_path(item.children[s], value))
							{
								var a = Array(1);
								a[0] = item.id;
								return a.concat(ar);
							}
						}
						return null;
					}
				}
				function find_children(level, value, item)
				{
					if(level==-1 && item.id==value)
						return item;
					else
					{
						for(var s in item.children)
						{
							if(ch = find_children(level-1,value,item.children[s]))
								return ch;
						}
						return null;
					}
				}
				function change_selection(name_prefix, prop_id,value,level,id)
				{
					//alert(prop_id+','+value+','+level);
					var lev = level+1;
					var oSelect;
					while(oSelect = document.getElementById(name_prefix+lev))
					{
						for(var i=oSelect.length-1;i>-1;i--)
							oSelect.remove(i);
						var newoption = new Option('(<?echo GetMessage("MAIN_NO")?>)', '0', false, false);
						oSelect.options[0]=newoption;
						lev++;
					}
					oSelect = document.getElementById(name_prefix+(level+1))
					if(oSelect && (value!=0||level==-1))
					{
						var item = find_children(level,value,window['sectionListsFor'+prop_id]);
						var i=1;
						for(var s in item.children)
						{
							obj = item.children[s];
							var newoption = new Option(obj.name, obj.id, false, false);
							oSelect.options[i++]=newoption;
						}
					}
					if(document.getElementById(id))
						document.getElementById(id).value = value;
				}
				function init_selection(name_prefix, prop_id, value, id)
				{
					var a = find_path(window['sectionListsFor'+prop_id], value);
					//alert(a);
					change_selection(name_prefix, prop_id, 0, -1, id);
					for(var i=1;i<a.length;i++)
					{
						if(oSelect = document.getElementById(name_prefix+(i-1)))
						{
							for(var j=0;j<oSelect.length;j++)
							{
								if(oSelect[j].value==a[i])
								{
									oSelect[j].selected=true;
									break;
								}
							}
						}
						change_selection(name_prefix, prop_id, a[i], i-1, id);
					}
				}
				var sectionListsFor0 = {id:0,name:'',children:Array()};

				<?
				$rsItems = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
				$depth = 0;
				$max_depth = 0;
				$arChain = array();
				while($arItem = $rsItems->GetNext())
				{
					if($max_depth < $arItem["DEPTH_LEVEL"])
					{
						$max_depth = $arItem["DEPTH_LEVEL"];
					}
					if($depth < $arItem["DEPTH_LEVEL"])
					{
						$arChain[]=$arItem["ID"];

					}
					while($depth > $arItem["DEPTH_LEVEL"])
					{
						array_pop($arChain);
						$depth--;
					}
					$arChain[count($arChain)-1] = $arItem["ID"];
					echo "sectionListsFor0";
					foreach($arChain as $i)
						echo ".children['".intval($i)."']";

					echo " = { id : ".$arItem["ID"].", name : '".CUtil::JSEscape($arItem["NAME"])."', children : Array() };\n";
					$depth = $arItem["DEPTH_LEVEL"];
				}
				?>
				</script>
				<?
				for($i = 0; $i < $max_depth; $i++)
					echo '<select id="select_IBLOCK_SECTION_'.$i.'" onchange="change_selection(\'select_IBLOCK_SECTION_\',  0, this.value, '.$i.', \'IBLOCK_SECTION[n'.$key.']\')"><option value="0">('.GetMessage("MAIN_NO").')</option></select>&nbsp;';
				?>
				<script>
					init_selection('select_IBLOCK_SECTION_', 0, '', 0);
				</script>
				</td>
				<td><input type="button" value="<?echo GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD")?>" onClick="addPathRow()"></td>
			</tr>
			</table>
		</td>

	<?else:?>

		<td>
			<table id="sections">
			<?
			if(is_array($str_IBLOCK_ELEMENT_SECTION))
			{
				$i = 0;
				foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
				{
					$rsChain = CIBlockSection::GetNavChain($IBLOCK_ID, $section_id);
					$strPath = "";
					while($arChain = $rsChain->GetNext())
						$strPath .= $arChain["NAME"]."&nbsp;/&nbsp;";
					if(strlen($strPath) > 0)
					{
						?><tr>
							<td><?echo $strPath?></td>
							<td>
							<input type="button" value="<?echo GetMessage("MAIN_DELETE")?>" OnClick="deleteRow(this)">
							<input type="hidden" name="IBLOCK_SECTION[]" value="<?echo intval($section_id)?>">
							</td>
						</tr><?
					}
					$i++;
				}
			}
			?>
			<tr>
				<td>
				<script>
				function deleteRow(button)
				{
					var my_row = button.parentNode.parentNode;
					var table = document.getElementById('sections');
					if(table)
					{
						for(var i=0; i<table.rows.length; i++)
						{
							if(table.rows[i] == my_row)
							{
								table.deleteRow(i);
							}
						}
					}
				}
				function InS<?echo md5("input_IBLOCK_SECTION")?>(section_id, html)
				{
					var table = document.getElementById('sections');
					if(table)
					{
						if(section_id > 0 && html)
						{
							var cnt = table.rows.length;
							var oRow = table.insertRow(cnt-1);

							var i=0;
							var oCell = oRow.insertCell(i++);
							oCell.innerHTML = html;

							var oCell = oRow.insertCell(i++);
							oCell.innerHTML =
								'<input type="button" value="<?echo GetMessage("MAIN_DELETE")?>" OnClick="deleteRow(this)">'+
								'<input type="hidden" name="IBLOCK_SECTION[]" value="'+section_id+'">';
						}
					}
				}
				</script>
				<input name="input_IBLOCK_SECTION" id="input_IBLOCK_SECTION" type="hidden">
				<input type="button" value="<?echo GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD")?>..." onClick="jsUtils.OpenWindow('/bitrix/admin/iblock_section_search.php?lang=<?echo LANG?>&amp;IBLOCK_ID=<?echo $IBLOCK_ID?>&amp;n=input_IBLOCK_SECTION&amp;m=y', 600, 500);">
				</td>
				<td>&nbsp;</td>
			</tr>
			</table>
		</td>

	<?endif;?>
	</tr>
	<?
	$hidden = "";
	if(is_array($str_IBLOCK_ELEMENT_SECTION))
		foreach($str_IBLOCK_ELEMENT_SECTION as $section_id)
			$hidden .= '<input type="hidden" name="IBLOCK_SECTION[]" value="'.intval($section_id).'">';
	$tabControl->EndCustomField("SECTIONS", $hidden);
endif;

$tabControl->BeginNextFormTab();
$tabControl->AddEditField("SORT", GetMessage("IBLOCK_FIELD_SORT").":", $arIBlock["FIELDS"]["SORT"]["IS_REQUIRED"] === "Y", array("size" => 7, "maxlength" => 10), $str_SORT);

if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y")
	$tabControl->AddEditField("XML_ID", GetMessage("IBLOCK_FIELD_XML_ID").":", $arIBlock["FIELDS"]["XML_ID"]["IS_REQUIRED"] === "Y", array("size" => 20, "maxlength" => 255), $str_XML_ID);

if($arTranslit["TRANSLITERATION"] != "Y")
{
	$tabControl->AddEditField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["CODE"]["IS_REQUIRED"] === "Y", array("size" => 20, "maxlength" => 255), $str_CODE);
}

$tabControl->BeginCustomField("TAGS", GetMessage("IBLOCK_FIELD_TAGS").":", $arIBlock["FIELDS"]["TAGS"]["IS_REQUIRED"] === "Y");
?>
	<tr id="tr_TAGS">
		<td><?echo $tabControl->GetCustomLabelHTML()?><br><?echo GetMessage("IBLOCK_ELEMENT_EDIT_TAGS_TIP")?></td>
		<td>
			<?if(CModule::IncludeModule('search')):
				$arLID = array();
				$rsSites = CIBlock::GetSite($IBLOCK_ID);
				while($arSite = $rsSites->Fetch())
					$arLID[] = $arSite["LID"];
				echo InputTags("TAGS", htmlspecialcharsback($str_TAGS), $arLID, 'size="55"');
			else:?>
				<input type="text" size="20" name="TAGS" maxlength="255" value="<?echo $str_TAGS?>">
			<?endif?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("TAGS",
	'<input type="hidden" name="TAGS" value="'.$str_TAGS.'">'
);

if($bTab4):?>
<?
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("WORKFLOW_PARAMS", GetMessage("IBLOCK_EL_TAB_WF_TITLE"));
	if(strlen($pr["DATE_CREATE"])>0):
	?>
		<tr id="tr_WF_CREATED">
			<td width="40%"><?echo GetMessage("IBLOCK_CREATED")?></td>
			<td width="60%"><?echo $pr["DATE_CREATE"]?><?
			if (intval($pr["CREATED_BY"])>0):
			?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$pr["CREATED_BY"]?>"><?echo $pr["CREATED_BY"]?></a>]&nbsp;<?=htmlspecialcharsex($pr["CREATED_USER_NAME"])?><?
			endif;
			?></td>
		</tr>
	<?endif;?>
	<?if(strlen($str_TIMESTAMP_X) > 0 && !$bCopy):?>
	<tr id="tr_WF_MODIFIED">
		<td><?echo GetMessage("IBLOCK_LAST_UPDATE")?></td>
		<td><?echo $str_TIMESTAMP_X?><?
		if (intval($str_MODIFIED_BY)>0):
		?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]&nbsp;<?=$str_USER_NAME?><?
		endif;
		?></td>
	</tr>
	<?endif?>
	<?if($WF=="Y" && strlen($prn_WF_DATE_LOCK)>0):?>
	<tr id="tr_WF_LOCKED">
		<td><?echo GetMessage("IBLOCK_DATE_LOCK")?></td>
		<td><?echo $prn_WF_DATE_LOCK?><?
		if (intval($prn_WF_LOCKED_BY)>0):
		?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANG?>&amp;ID=<?=$prn_WF_LOCKED_BY?>"><?echo $prn_WF_LOCKED_BY?></a>]&nbsp;<?=$prn_LOCKED_USER_NAME?><?
		endif;
		?></td>
	</tr>
	<?endif;
	$tabControl->EndCustomField("WORKFLOW_PARAMS", "");
	if ($WF=="Y" || $view=="Y"):
	$tabControl->BeginCustomField("WF_STATUS_ID", GetMessage("IBLOCK_FIELD_STATUS").":");
	?>
	<tr id="tr_WF_STATUS_ID">
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
			<?if($ID > 0 && !$bCopy):?>
				<?echo SelectBox("WF_STATUS_ID", CWorkflowStatus::GetDropDownList("N", "desc"), "", $str_WF_STATUS_ID);?>
			<?else:?>
				<?echo SelectBox("WF_STATUS_ID", CWorkflowStatus::GetDropDownList("N", "desc"), "", "");?>
			<?endif?>
		</td>
	</tr>
	<?
	if($ID > 0 && !$bCopy)
		$hidden = '<input type="hidden" name="WF_STATUS_ID" value="'.$str_WF_STATUS_ID.'">';
	else
	{
		$rsStatus = CWorkflowStatus::GetDropDownList("N", "desc");
		$arDefaultStatus = $rsStatus->Fetch();
		if($arDefaultStatus)
			$def_WF_STATUS_ID = intval($arDefaultStatus["REFERENCE_ID"]);
		else
			$def_WF_STATUS_ID = "";
		$hidden = '<input type="hidden" name="WF_STATUS_ID" value="'.$def_WF_STATUS_ID.'">';
	}
	$tabControl->EndCustomField("WF_STATUS_ID", $hidden);
	endif;
	$tabControl->BeginCustomField("WF_COMMENTS", GetMessage("IBLOCK_COMMENTS"));
	?>
	<tr class="heading" id="tr_WF_COMMENTS_LABEL">
		<td colspan="2"><b><?echo $tabControl->GetCustomLabelHTML()?></b></td>
	</tr>
	<tr id="tr_WF_COMMENTS">
		<td colspan="2">
			<?if($ID > 0 && !$bCopy):?>
				<textarea name="WF_COMMENTS" style="width:100%" rows="10"><?echo $str_WF_COMMENTS?></textarea>
			<?else:?>
				<textarea name="WF_COMMENTS" style="width:100%" rows="10"><?echo ""?></textarea>
			<?endif?>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("WF_COMMENTS", '<input type="hidden" name="WF_COMMENTS" value="'.$str_WF_COMMENTS.'">');
endif;

if ($bBizproc && ($historyId <= 0)):

	$tabControl->BeginNextFormTab();

	$tabControl->BeginCustomField("BIZPROC_WF_STATUS", GetMessage("IBEL_E_PUBLISHED"));
	?>
	<tr id="tr_BIZPROC_WF_STATUS">
		<td style="width:40%;"><?=GetMessage("IBEL_E_PUBLISHED")?>:</td>
		<td style="width:60%;"><?=($str_BP_PUBLISHED=="Y"?GetMessage("MAIN_YES"):GetMessage("MAIN_NO"))?></td>
	</tr>
	<?
	$tabControl->EndCustomField("BIZPROC_WF_STATUS", '');

	$tabControl->BeginCustomField("BIZPROC", GetMessage("IBEL_E_TAB_BIZPROC"));

	CBPDocument::AddShowParameterInit(MODULE_ID, "only_users", DOCUMENT_TYPE);

	$bizProcIndex = 0;
	if (!isset($arDocumentStates))
	{
		$arDocumentStates = CBPDocument::GetDocumentStates(
			array(MODULE_ID, ENTITY, DOCUMENT_TYPE),
			($ID > 0) ? array(MODULE_ID, ENTITY, $ID) : null,
			"Y"
		);
	}
	foreach ($arDocumentStates as $arDocumentState)
	{
		$bizProcIndex++;

		$canViewWorkflow = CBPDocument::CanUserOperateDocument(
			IBLOCK_DOCUMENT_OPERATION_VIEW_WORKFLOW,
			$GLOBALS["USER"]->GetID(),
			array(MODULE_ID, ENTITY, $ID),
			array("IBlockPermission" => $BlockPerm, "AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates, "WorkflowId" => $arDocumentState["ID"] > 0 ? $arDocumentState["ID"] : $arDocumentState["TEMPLATE_ID"])
		);

		if (!$canViewWorkflow)
			continue;
		?>
		<tr class="heading">
			<td colspan="2">
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td width="99%" align="center">
							<?= htmlspecialchars($arDocumentState["TEMPLATE_NAME"]) ?>
						</td>
						<td width="1%" align="right">
							<?if (strlen($arDocumentState["ID"]) > 0 && strlen($arDocumentState["WORKFLOW_STATUS"]) > 0):?>
							<a href="iblock_element_edit.php?WF=<?= $WF ?>&ID=<?= $ID ?>&type=<?= $type ?>&lang=<?= $lang ?>&IBLOCK_ID=<?= $IBLOCK_ID ?>&find_section_section=<?= $find_section_section ?>&stop_bizproc=<?= $arDocumentState["ID"] ?>&<?= bitrix_sessid_get() ?>"><?echo GetMessage("IBEL_BIZPROC_STOP")?></a>
							<?endif;?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="40%"><?echo GetMessage("IBEL_BIZPROC_NAME")?></td>
			<td width="60%"><?= htmlspecialchars($arDocumentState["TEMPLATE_NAME"]) ?></td>
		</tr>
		<?if($arDocumentState["TEMPLATE_DESCRIPTION"]!=''):?>
		<tr>
			<td width="40%"><?echo GetMessage("IBEL_BIZPROC_DESC")?></td>
			<td width="60%"><?= htmlspecialchars($arDocumentState["TEMPLATE_DESCRIPTION"]) ?></td>
		</tr>
		<?endif?>
		<?if (strlen($arDocumentState["STATE_MODIFIED"]) > 0):?>
		<tr>
			<td width="40%"><?echo GetMessage("IBEL_BIZPROC_DATE")?></td>
			<td width="60%"><?= $arDocumentState["STATE_MODIFIED"] ?></td>
		</tr>
		<?endif;?>
		<?if (strlen($arDocumentState["STATE_NAME"]) > 0):?>
		<tr>
			<td width="40%"><?echo GetMessage("IBEL_BIZPROC_STATE")?></td>
			<td width="60%"><?if (strlen($arDocumentState["ID"]) > 0):?><a href="bizproc_log.php?ID=<?= $arDocumentState["ID"] ?>"><?endif;?><?= strlen($arDocumentState["STATE_TITLE"]) > 0 ? $arDocumentState["STATE_TITLE"] : $arDocumentState["STATE_NAME"] ?><?if (strlen($arDocumentState["ID"]) > 0):?></a><?endif;?></td>
		</tr>
		<?endif;?>
		<?
		if (strlen($arDocumentState["ID"]) <= 0)
		{
			CBPDocument::StartWorkflowParametersShow(
				$arDocumentState["TEMPLATE_ID"],
				$arDocumentState["TEMPLATE_PARAMETERS"],
				($bCustomForm ? "tabControl" : "form_element_".$IBLOCK_ID)."_form",
				$bVarsFromForm
			);
		}
		?>
		<?
		$arEvents = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arCurrentUserGroups, $arDocumentState);
		if (count($arEvents) > 0)
		{
			?>
			<tr>
				<td width="40%"><?echo GetMessage("IBEL_BIZPROC_RUN_CMD")?></td>
				<td width="60%">
					<input type="hidden" name="bizproc_id_<?= $bizProcIndex ?>" value="<?= $arDocumentState["ID"] ?>">
					<input type="hidden" name="bizproc_template_id_<?= $bizProcIndex ?>" value="<?= $arDocumentState["TEMPLATE_ID"] ?>">
					<select name="bizproc_event_<?= $bizProcIndex ?>">
						<option value=""><?echo GetMessage("IBEL_BIZPROC_RUN_CMD_NO")?></option>
						<?
						foreach ($arEvents as $e)
						{
							?><option value="<?= htmlspecialchars($e["NAME"]) ?>"<?= ($_REQUEST["bizproc_event_".$bizProcIndex] == $e["NAME"]) ? " selected" : ""?>><?= htmlspecialchars($e["TITLE"]) ?></option><?
						}
						?>
					</select>
				</td>
			</tr>
			<?
		}

		if (strlen($arDocumentState["ID"]) > 0)
		{
			$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $arDocumentState["ID"]);
			if (count($arTasks) > 0)
			{
				?>
				<tr>
					<td width="40%"><?echo GetMessage("IBEL_BIZPROC_TASKS")?></td>
					<td width="60%">
						<?
						foreach ($arTasks as $arTask)
						{
							?><a href="bizproc_task.php?id=<?= $arTask["ID"] ?>&back_url=<?= urlencode($APPLICATION->GetCurPageParam("", array())) ?>" title="<?= htmlspecialchars($arTask["DESCRIPTION"]) ?>"><?= $arTask["NAME"] ?></a><br /><?
						}
						?>
					</td>
				</tr>
				<?
			}
		}
	}
	if ($bizProcIndex <= 0)
	{
		?>
		<tr>
			<td><br /></td>
			<td><?=GetMessage("IBEL_BIZPROC_NA")?></td>
		</tr>
		<?
	}
	?>
	<input type="hidden" name="bizproc_index" value="<?= $bizProcIndex ?>">
	<?
	if ($ID > 0):
		$bStartWorkflowPermission = CBPDocument::CanUserOperateDocument(
			IBLOCK_DOCUMENT_OPERATION_START_WORKFLOW,
			$USER->GetID(),
			array(MODULE_ID, ENTITY, $ID),
			array("IBlockPermission" => $BlockPerm, "AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates, "WorkflowId" => $arDocumentState["TEMPLATE_ID"])
		);
		if ($bStartWorkflowPermission):
			?>
			<tr class="heading">
				<td colspan="2"><?echo GetMessage("IBEL_BIZPROC_NEW")?></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<a href="<?=MODULE_ID?>_start_bizproc.php?document_id=<?= $ID ?>&document_type=<?= DOCUMENT_TYPE ?>&back_url=<?= urlencode($APPLICATION->GetCurPageParam("", array())) ?>"><?echo GetMessage("IBEL_BIZPROC_START")?></a>
				</td>
			</tr>
			<?
		endif;
	endif;

	$tabControl->EndCustomField("BIZPROC", "");
endif;

$bDisabled = $view=="Y" || ($bWorkflow && $prn_LOCK_STATUS=="red");

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):
	ob_start();
	?>
	<input <?if ($bDisabled) echo "disabled";?> type="submit" class="button" name="save" value="<?echo GetMessage("IBLOCK_EL_SAVE")?>">
	<input <?if ($bDisabled) echo "disabled";?> type="submit" class="button" name="apply" value="<?echo GetMessage('IBLOCK_APPLY')?>">
	<input <?if ($bDisabled) echo "disabled";?> type="submit" class="button" name="dontsave" value="<?echo GetMessage("IBLOCK_EL_CANC")?>">
	<?
	$buttons_add_html = ob_get_contents();
	ob_end_clean();
	$tabControl->Buttons(false, $buttons_add_html);
else:
	$tabControl->ButtonsPublic(array(
		'.btnSave',
		($ID > 0 && $bWorkflow)
			? "{
	title: '".CUtil::JSEscape(GetMessage("IBLOCK_EL_CANC"))."',
	name: 'dontsave',
	id: 'dontsave',
	action: function () {
		var FORM = this.parentWindow.GetForm();
		FORM.appendChild(BX.create('INPUT', {
			props: {
				type: 'hidden',
				name: this.name,
				value: 'Y'
			}
		}));

		this.disableUntilError();
		this.parentWindow.Submit();
	}
}"
			: '.btnCancel'
	));
endif;

$tabControl->Show();
if (
	(!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)
	&& $BlockPerm >= "X"
	&& !(defined('BT_UT_AUTOCOMPLETE') && (1 == BT_UT_AUTOCOMPLETE))
)
{

	echo
		BeginNote(),
		GetMessage("IBEL_E_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialchars($type).'&amp;lang='.LANG.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode("iblock_element_edit.php?ID=".$ID.($WF=="Y"?"&WF=Y":"")."&lang=".LANG. "&type=".htmlspecialchars($type)."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"")).'">',
		GetMessage("IBEL_E_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}
	//////////////////////////
	//END of the custom form
	//////////////////////////
?>

<?endif;

}
if ((true == defined('BT_UT_AUTOCOMPLETE')) && (1 == BT_UT_AUTOCOMPLETE))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
}
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
