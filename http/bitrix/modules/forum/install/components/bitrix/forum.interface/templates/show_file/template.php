<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["FILE"] = (is_array($arParams["FILE"]) ? $arParams["FILE"] : intVal($arParams["FILE"]));
// ************************* ADDITIONAL ****************************************************************
$arParams["MAX_FILE_SIZE"] = intVal($arParams["MAX_FILE_SIZE"] > 0 ? $arParams["MAX_FILE_SIZE"] : 100)*1024*1024;
$arParams["WIDTH"] = (intVal($arParams["WIDTH"]) > 0 ? intVal($arParams["WIDTH"]) : 100);
$arParams["HEIGHT"] = (intVal($arParams["HEIGHT"]) > 0 ? intVal($arParams["HEIGHT"]) : 100);
$arParams["CONVERT"] = ($arParams["CONVERT"] == "N" ? "N" : "Y");
$arParams["FAMILY"] = trim($arParams["FAMILY"]);
$arParams["FAMILY"] = CUtil::addslashes(empty($arParams["FAMILY"]) ? "FORUM" : $arParams["FAMILY"]);
$arParams["SINGLE"] = ($arParams["SINGLE"] == "N" ? "N" : "Y");
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" ? "Y" : "N");
$arParams["SHOW_LINK"] = ($arParams["SHOW_LINK"] == "Y" ? "Y" : "N");
$arParams["ADDITIONAL_URL"] = htmlspecialcharsEx(trim($arParams["ADDITIONAL_URL"]));
// *************************/Input params***************************************************************

// ************************* Default params*************************************************************
$arResult["FILE"] = $arParams["FILE"];
if (!is_array($arParams["FILE"]) && intVal($arParams["FILE"]) > 0)
{
	$arResult["FILE"] = CFile::GetFileArray($arParams["FILE"]);
}

$arResult["RETURN_DATA"] = "";
// *************************/Default params*************************************************************

if (is_array($arResult["FILE"]) && !empty($arResult["FILE"]["SRC"]))
{
	$arResult["FILE"]["FULL_SRC"] = "http://".str_replace("//", "/", $_SERVER["HTTP_HOST"]."/".$arResult["FILE"]["SRC"]);
	$ct = strToLower($arResult["FILE"]["CONTENT_TYPE"]);
	
	if ($arParams["MAX_FILE_SIZE"] >= $arResult["FILE"]["FILE_SIZE"] && (substr($ct, 0, 6) == "video/" || substr($ct, 0, 6) == "audio/"))
	{
		$arResult["RETURN_DATA"] =
			'<OBJECT ID="WMP64" WIDTH="'.($arParams["WIDTH"] > 0 ? $arParams["WIDTH"] : '250').'" HEIGHT="'.(substr($ct, 0, 6) == "audio/"?'45':($arParams["HEIGHT"] > 0 ? $arParams["HEIGHT"] : '220')).'" CLASSID="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject"> '.
			'<PARAM NAME="AutoStart" VALUE="false"> '.
			'<PARAM NAME="ShowDisplay" VALUE="false">'.
			'<PARAM NAME="ShowControls" VALUE="true" >'.
			'<PARAM NAME="ShowStatusBar" VALUE="0">'.
			'<PARAM NAME="FileName" VALUE="'.$arResult["FILE"]["SRC"].'"> '.
			'</OBJECT>';
	}
	elseif (strToLower(substr($arResult["FILE"]["ORIGINAL_NAME"], -4)) == ".swf" && strpos($arResult["FILE"]["CONTENT_TYPE"], "flash") !== false)
	{
		if ($arResult["FILE"]["WIDTH"] > $arParams["WIDTH"] || $arResult["FILE"]["HEIGHT"] > $arParams["HEIGHT"])
		{
			$coeff = max($arResult["FILE"]["WIDTH"]/$arParams["WIDTH"], $arResult["FILE"]["HEIGHT"]/$arParams["HEIGHT"]);
			$arResult["FILE"]["WIDTH"] = $arResult["FILE"]["WIDTH"]/$coeff;
			$arResult["FILE"]["HEIGHT"] = $arResult["FILE"]["HEIGHT"]/$coeff;
		}
		$arResult["RETURN_DATA"] = '
			<object
				classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000"
				codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
				id="banner"
				WIDTH="'.$arResult["FILE"]["WIDTH"].'"
				HEIGHT="'.$arResult["FILE"]["HEIGHT"].'"
				ALIGN="">
					<PARAM NAME="movie" VALUE="'.$arResult["FILE"]["SRC"].'" />
					<PARAM NAME="quality" VALUE="high" />
					<PARAM NAME="bgcolor" VALUE="#FFFFFF" />
					<embed
						src="'.$arResult["FILE"]["SRC"].'"
						quality="high"
						bgcolor="#FFFFFF"
						WIDTH="'.$arResult["FILE"]["WIDTH"].'"
						HEIGHT="'.$arResult["FILE"]["HEIGHT"].'"
						NAME="banner"
						ALIGN=""
						TYPE="application/x-shockwave-flash"
						PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
					</embed>
			</object>
			';
	}
	elseif ($arParams["MAX_FILE_SIZE"] >= $arResult["FILE"]["FILE_SIZE"] && substr($ct, 0, 6) == "image/")
	{
		$arResult["RETURN_DATA"] = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			"popup_image",
			Array(
				"URL" => "/bitrix/components/bitrix/forum.interface/show_file.php?fid=".$arResult["FILE"]["ID"].
					(!empty($arParams["ADDITIONAL_URL"]) ? "&".$arParams["ADDITIONAL_URL"] : ""),
				"WIDTH"=> $arParams["WIDTH"],
				"HEIGHT"=> $arParams["HEIGHT"],
				"CONVERT" => $arParams["CONVERT"],
				"FAMILY" => $arParams["FAMILY"],
				"SINGLE" => $arParams["SINGLE"],
				"RETURN" => "Y"
			),
			null,
			array("HIDE_ICONS" => "Y"));
	}
	
	if ($arParams["SHOW_LINK"] == "Y" || empty($arResult["RETURN_DATA"])):
		$str = "";
		if (!empty($arResult["FILE"]["ORIGINAL_NAME"]))
		{
			$str .= "<a href=\"/bitrix/components/bitrix/forum.interface/show_file.php?fid=".
				htmlspecialchars($arResult["FILE"]["ID"]).
				(!empty($arParams["ADDITIONAL_URL"]) ? "&".$arParams["ADDITIONAL_URL"] : "")."\" title=\"".
					str_replace("#FILE_NAME#", $arResult["FILE"]["ORIGINAL_NAME"], GetMessage("FRM_VIEW_TITLE")).'" target="_blank">'.
				$arResult["FILE"]["ORIGINAL_NAME"].'</a>';
		}
		
		if (intVal($arResult["FILE"]["FILE_SIZE"]) > 0)
		{
			$size = $arResult["FILE"]["FILE_SIZE"];
			$deep = 0;
			$loop = true;
			do
			{
				if (round($size/1024) <= 0 || $deep > 3) 
				{
					$loop = false;
				}
				else
				{
					$size = round($size/1024, 2);
					$deep++;
				}
			}while($loop);

			$str .= " (".$size." ".GetMessage("F_FILE_SIZE_".$deep).") ";
		}
		
		$str .= " [ <a href=\"/bitrix/components/bitrix/forum.interface/show_file.php?fid=".
			htmlspecialchars($arResult["FILE"]["ID"])."&action=download".
					(!empty($arParams["ADDITIONAL_URL"]) ? "&".$arParams["ADDITIONAL_URL"] : "")."\" title=\"".
				str_replace("#FILE_NAME#", $arResult["FILE"]["ORIGINAL_NAME"], GetMessage("FRM_DOWNLOAD_TITLE")).'" target="_blank">'.
			GetMessage("FRM_DOWNLOAD").'</a> ] ';

		if (!empty($str))
		{
			$arResult["RETURN_DATA"] .= "<div>".$str."</div>";
		}
	endif;
	
	if (!empty($arResult["RETURN_DATA"]))
		$arResult["RETURN_DATA"] = "<div class='forum-attach'>".$arResult["RETURN_DATA"]."</div>";
}

if ($arParams["RETURN"] == "Y")
	$this->__component->arParams["RETURN_DATA"] = $arResult["RETURN_DATA"];
else 
	echo $arResult["RETURN_DATA"];

return 0;
/*
GetMessage("F_FILE_SIZE_0");
GetMessage("F_FILE_SIZE_1");
GetMessage("F_FILE_SIZE_2");
GetMessage("F_FILE_SIZE_3");
*/
?>