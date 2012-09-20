<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLF_MODULE_NOT_INSTALLED"));
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
		ShowError(GetMessage("CC_BLF_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLF_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLF_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLF_UNKNOWN_ERROR"));
		return;
	}
}
elseif($lists_perm < CListPermissions::CAN_READ)
{
	ShowError(GetMessage("CC_BLF_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = $lists_perm >= CListPermissions::IS_ADMIN;
$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));

$arResult["FILES"] = array();
$arResult["ELEMENT"] = false;
$arResult["SECTION"] = false;

if($arParams["ELEMENT_ID"] > 0)
{
	$rsElement = CIBlockElement::GetList(
		array(),
		array(
			"IBLOCK_ID" => $arIBlock["ID"],
			"=ID" => $arParams["ELEMENT_ID"],
			"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"),
		),
		false,
		false,
		array("ID", $arParams["FIELD_ID"])
	);
	while($ar = $rsElement->GetNext())
	{
		if(isset($ar[$arParams["FIELD_ID"]]))
		{
			$arResult["FILES"][] = $ar[$arParams["FIELD_ID"]];
		}
		elseif(isset($ar[$arParams["FIELD_ID"]."_VALUE"]))
		{
			if(is_array($ar[$arParams["FIELD_ID"]."_VALUE"]))
				$arResult["FILES"] = array_merge($arResult["FILES"], $ar[$arParams["FIELD_ID"]."_VALUE"]);
			else
				$arResult["FILES"][] = $ar[$arParams["FIELD_ID"]."_VALUE"];
		}
		$arResult["ELEMENT"] = $ar;
	}
}
elseif($arParams["SECTION_ID"] > 0)
{
	$rsSection = CIBlockSection::GetList(
		array(),
		array(
			"IBLOCK_ID" => $arIBlock["ID"],
			"ID" => $arParams["SECTION_ID"],
			"GLOBAL_ACTIVE"=>"Y",
			"CHECK_PERMISSIONS" => ($arParams["CAN_EDIT"] || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
		),
		false,
		array("ID", $arParams["FIELD_ID"])
	);
	$arResult["SECTION"] = $rsSection->GetNext();
}

if(!in_array($arParams["FILE_ID"], $arResult["FILES"]))
{
	ShowError(GetMessage("CC_BLF_WRONG_FILE"));
}
else
{
	$arFile = CFile::GetFileArray($arParams["FILE_ID"]);
	if(is_array($arFile))
	{
		$APPLICATION->RestartBuffer();

		$filename = $_SERVER["DOCUMENT_ROOT"].$arFile["SRC"];
		$filesize = filesize($filename);
		$filetime = filemtime($filename);

		if($_SERVER["REQUEST_METHOD"]=="HEAD")
		{
			CHTTP::SetStatus("200 OK");
			header("Accept-Ranges: bytes");
			header("Content-Length: ".$filesize);
			header("Content-Type: ".$arFile["CONTENT_TYPE"]."; name=\"".$arFile["FILE_NAME"]."\"");
			header("Last-Modified: ".date("r", $filetime));
		}
		else
		{
			//Handle ETag
			$ETag = md5($filename.$filesize.$filetime);
			if(array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER) && ($_SERVER['HTTP_IF_NONE_MATCH'] === $ETag))
			{
				CHTTP::SetStatus("304 Not Modified");
				die();
			}
			header("ETag: ".$ETag);

			//Handle Last Modified
			$lastModified = gmdate('D, d M Y H:i:s', $filetime).' GMT';
			if(array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER) && ($_SERVER['HTTP_IF_MODIFIED_SINCE'] === $lastModified))
			{
				CHTTP::SetStatus("304 Not Modified");
				die();
			}

			$cur_pos = 0;
			$size = $filesize - 1;
			if(isset($_SERVER["HTTP_RANGE"]) && preg_match("/^bytes=([0-9-,]+)$/", $_SERVER["HTTP_RANGE"], $arRange))
			{
				if(preg_match("/^(\\d*)-(\\d*)/", $arRange[1], $arFirstRange))
				{
					$size = IntVal($arFirstRange[2]);
					if($size <= 0)
						$size = $filesize - 1;

					$cur_pos = IntVal($arFirstRange[1]);
					if($cur_pos > $size)
					{
						$cur_pos = 0;
						$size = $filesize - 1;
					}
				}
			}

			if($cur_pos > 0)
				CHTTP::SetStatus("206 Partial Content");
			else
				CHTTP::SetStatus("200 OK");

			header("Content-Type: ".$arFile["CONTENT_TYPE"]."; name=\"".$arFile["FILE_NAME"]."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".($size-$cur_pos+1));
			header("Content-Disposition: inline; filename=\"".$arFile["FILE_NAME"]."\"");
			header("Accept-Ranges: bytes");
			header("Content-Range: bytes ".$cur_pos."-".$size."/".$filesize);
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Expires: 0");
			header('Last-Modified: '.$lastModified);
			header("Pragma: public");

			$f = fopen($filename, "rb");
			fseek($f, $cur_pos);
			while($cur_pos <= $size)
			{
				$bufsize = 32768;
				if(($bufsize + $cur_pos) > $size)
					$bufsize = $size - $cur_pos + 1;

				echo fread($f, $bufsize);
				flush();

				$cur_pos += $bufsize;
			}
			fclose($f);
			die();
		}
	}
}
?>
