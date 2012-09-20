<?
IncludeModuleLangFile(__FILE__);

class CUserTypeFile
{
	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "file",
			"CLASS_NAME" => "CUserTypeFile",
			"DESCRIPTION" => GetMessage("USER_TYPE_FILE_DESCRIPTION"),
			"BASE_TYPE" => "file",
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "int(18)";
			case "oracle":
				return "number(18)";
			case "mssql":
				return "int";
		}
	}

	function PrepareSettings($arUserField)
	{
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$ar = array();
		$ext = explode(",", $arUserField["SETTINGS"]["EXTENSIONS"]);
		foreach($ext as $k=>$v)
		{
			$v = trim($v);
			if(strlen($v)>0)
				$ar[$v] = true;
		}
		return array(
			"SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
			"LIST_WIDTH" => intval($arUserField["SETTINGS"]["LIST_WIDTH"]),
			"LIST_HEIGHT" => intval($arUserField["SETTINGS"]["LIST_HEIGHT"]),
			"MAX_SHOW_SIZE" => intval($arUserField["SETTINGS"]["MAX_SHOW_SIZE"]),
			"MAX_ALLOWED_SIZE" => intval($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]),
			"EXTENSIONS" => $ar,
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["SIZE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["SIZE"]);
		else
			$value = 20;
		$result .= '
		<tr valign="top">
			<td>'.GetMessage("USER_TYPE_FILE_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$width = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_WIDTH"]);
		elseif(is_array($arUserField))
			$width = intval($arUserField["SETTINGS"]["LIST_WIDTH"]);
		else
			$width = 0;
		if($bVarsFromForm)
			$height = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"]);
		elseif(is_array($arUserField))
			$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		else
			$height = 0;
		$result .= '
		<tr valign="top">
			<td>'.GetMessage("USER_TYPE_FILE_WIDTH_AND_HEIGHT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_WIDTH]" size="7"  maxlength="20" value="'.$width.'">
				&nbsp;x&nbsp;
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_HEIGHT]" size="7"  maxlength="20" value="'.$height.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_SHOW_SIZE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MAX_SHOW_SIZE"]);
		else
			$value = 0;
		$result .= '
		<tr valign="top">
			<td>'.GetMessage("USER_TYPE_FILE_MAX_SHOW_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_SHOW_SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_ALLOWED_SIZE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]);
		else
			$value = 0;
		$result .= '
		<tr valign="top">
			<td>'.GetMessage("USER_TYPE_FILE_MAX_ALLOWED_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_ALLOWED_SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = htmlspecialchars($GLOBALS[$arHtmlControl["NAME"]]["EXTENSIONS"]);
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_FILE_EXTENSIONS").':</td>
				<td>
					<input type="text" size="20" name="'.$arHtmlControl["NAME"].'[EXTENSIONS]" value="'.$value.'">
				</td>
			</tr>
			';
		}
		else
		{
			if(is_array($arUserField))
				$arExt = $arUserField["SETTINGS"]["EXTENSIONS"];
			else
				$arExt = "";
			$value = array();
			if(is_array($arExt))
				foreach($arExt as $ext=>$flag)
					$value[] = htmlspecialchars($ext);
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_FILE_EXTENSIONS").':</td>
				<td>
					<input type="text" size="20" name="'.$arHtmlControl["NAME"].'[EXTENSIONS]" value="'.implode(", ", $value).'">
				</td>
			</tr>
			';
		}
		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if(($p=strpos($arHtmlControl["NAME"], "["))>0)
			$strOldIdName = substr($arHtmlControl["NAME"], 0, $p)."_old_id".substr($arHtmlControl["NAME"], $p);
		else
			$strOldIdName = $arHtmlControl["NAME"]."_old_id";
		return '<input type="hidden" name="'.$strOldIdName.'" value="'.$arHtmlControl["VALUE"].'">'.
			CFile::InputFile($arHtmlControl["NAME"], $arUserField["SETTINGS"]["SIZE"], $arHtmlControl["VALUE"], false, 0, "", ($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': ''), 0, "", 'value="'.$arHtmlControl["VALUE"].'"').
			'<br>'.
			CFile::ShowImage($arHtmlControl["VALUE"]);
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return '&nbsp;';
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return CFile::ShowFile($arHtmlControl["VALUE"], $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true);
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		//TODO edit mode
		return CFile::ShowFile($arHtmlControl["VALUE"], $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true);
	}

	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
	{
		//TODO edit mode
		$result = "&nbsp;";
		foreach($arHtmlControl["VALUE"] as $value)
		{
			$result .= CFile::ShowFile($value, $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true)."<br>";
		}
		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		if($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]>0 && $value["size"]>$arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_FILE_MAX_SIZE_ERROR",
					array(
						"#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
						"#MAX_ALLOWED_SIZE#"=>$arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]
					)
				),
			);
		}

		//Extention check
		if(is_array($arUserField["SETTINGS"]["EXTENSIONS"]) && count($arUserField["SETTINGS"]["EXTENSIONS"]))
		{
			foreach($arUserField["SETTINGS"]["EXTENSIONS"] as $ext => $tmp_val)
				$arUserField["SETTINGS"]["EXTENSIONS"][$ext] = $ext;
			$error = CFile::CheckFile($value, 0, false, implode(",", $arUserField["SETTINGS"]["EXTENSIONS"]));
		}
		else
		{
			$error = "";
		}

		if (strlen($error))
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => $error,
			);
		}

		//For user without edit php permissions
		//we allow only pictures upload
		global $USER;
		if(!is_object($USER) || !$USER->IsAdmin())
		{
			if(HasScriptExtension($value["name"]))
			{
				$aMsg[] = array(
					"id" => $arUserField["FIELD_NAME"],
					"text" => GetMessage("FILE_BAD_TYPE")." (".$value["name"].").",
				);
			}
		}

		return $aMsg;
	}

	function OnBeforeSave($arUserField, $value)
	{
		if(is_array($value))
		{
			if($value["del"] && $value["old_id"])
			{
				CFile::Delete($value["old_id"]);
				$value["old_id"] = false;
			}
			if($value["error"])
				return $value["old_id"];
			else
			{
				if($value["old_id"])
				{
					CFile::Delete($value["old_id"]);
				}
				$value["MODULE_ID"] = "main";
				$id =  CFile::SaveFile($value, "uf");
				return $id;
			}
		}
		else
			return $value;
	}
}
AddEventHandler("main", "OnUserTypeBuildList", array("CUserTypeFile", "GetUserTypeDescription"));
?>