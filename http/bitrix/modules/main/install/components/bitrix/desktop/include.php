<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function GDCSaveSettings($arParams, $POS)
{
	if ($arParams["DEFAULT_ID"])
	{
		$user_option_id = 0;
		$arUserOptionsDefault = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], false, $user_option_id);
	}
	else
	{
		$user_option_id = false;
		$arUserOptionsDefault = false;
	}
		
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
	if(!is_array($arUserOptions))
		$arUserOptions = Array("GADGETS"=>Array());
	$arNewUserOptions = Array("GADGETS"=>Array());
	foreach($POS as $col=>$items)
	{
		foreach($items as $row=>$gdId)
		{
			if(substr($gdId, -2, 2) == "*H")
			{
				$gdId = substr($gdId, 0, -2);
				$bHided = true;
			}
			else
				$bHided = false;

			if(is_array($arUserOptions["GADGETS"][$gdId]))
				$arNewUserOptions["GADGETS"][$gdId] = $arUserOptions["GADGETS"][$gdId];
			else
				$arNewUserOptions["GADGETS"][$gdId] = Array();

			$arNewUserOptions["GADGETS"][$gdId]["COLUMN"] = $col;
			$arNewUserOptions["GADGETS"][$gdId]["ROW"] = $row;
			$arNewUserOptions["GADGETS"][$gdId]["HIDE"] = ($bHided?"Y":"N");
		}
	}
		
	CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arNewUserOptions, false, $user_option_id);
}

function GDCGetGadgetObject($gdid, $arParams, $arGadgetParams, $bHide = false)
{
	return $arGadget;
}


class BXGadget
{
	function GetGadgetContent(&$arGadget, $arParams)
	{
		global $APPLICATION, $USER;

		if(file_exists($arGadget["PATH"]."/lang/".LANGUAGE_ID."/index.php"))
			__IncludeLang($arGadget["PATH"]."/lang/".LANGUAGE_ID."/index.php");

		$arGadgetParams = $arGadget["SETTINGS"];
		$id = $arGadget["ID"];
		ob_start();
		include($arGadget["PATH"]."/index.php");
		$r = ob_get_contents();
		ob_end_clean();
		return $r;
	}

	function GetList($bWithParameters = false, $arAllCurrentValues = false)
	{
		// Найдем все пространства имен гаджетов
		$arGdNS = Array("bitrix");
		$gdDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets";
		if($handle = opendir($gdDir))
		{
			while(false !== ($item = readdir($handle)))
				if(is_dir($gdDir."/".$item) && $item != "." && $item != ".." && $item != "bitrix")
					$arGdNS[] = $item;
		}

		// В цикле найдем все гаджеты
		$arGadgets = Array();
		foreach($arGdNS as $NS)
		{
			$gdDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets/".$NS;
			if ($handle = opendir($gdDir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if($file=="." || $file=="..")
						continue;
					$arGadgetParams = BXGadget::GetById($NS."/".$file, $bWithParameters, $arAllCurrentValues);
					if($arGadgetParams)
						$arGadgets[$file] = $arGadgetParams;
					else
						unset($arGadgets[$file]);
				}
				closedir($handle);
			}
		}

		uasort($arGadgets, Array("BXGadget", "_sort"));

		return $arGadgets;
	}

	function _sort($ar1, $ar2)
	{
		return strcmp($ar1["NAME"], $ar2["NAME"]);
	}

	function GetById($id, $bWithParameters = false, $arAllCurrentValues = false)
	{
		$id = strtolower($id);
		$arGdNS = Array("bitrix");
		if(($p = strpos($id, "/"))>0)
		{
			$arGdNS = Array(substr($id, 0, $p));
			$id = substr($id, $p+1);
		}
		else
		{
			// Найдем все пространства имен гаджетов
			$gdDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets";
			if($handle = opendir($gdDir))
			{
				while(false !== ($item = readdir($handle)))
					if(is_dir($gdDir."/".$item) && $item != "." && $item != ".." && $item != "bitrix")
						$arGdNS[] = $item;
			}
		}

		// В цикле найдем все гаджеты
		$arGadget = false;
		foreach($arGdNS as $NS)
		{
			$gdDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets/".$NS;
			if(is_dir($gdDir."/".$id))
			{
				$arDescription = Array();
				if(file_exists($gdDir."/".$id."/lang/".LANGUAGE_ID."/.description.php"))
					__IncludeLang($gdDir."/".$id."/lang/".LANGUAGE_ID."/.description.php");

				if(!file_exists($gdDir."/".$id."/.description.php"))
					continue;

				if(!@include($gdDir."/".$id."/.description.php"))
				{
					$arGadget = false;
					continue;
				}

				if(isset($arDescription["LANG_ONLY"]) && $arDescription["LANG_ONLY"]!=LANGUAGE_ID)
				{
					$arGadget = false;
					continue;
				}

				if($bWithParameters)
				{
					$arCurrentValues = Array();
					if(is_array($arAllCurrentValues))
					{
						foreach($arAllCurrentValues as $k=>$v)
						{
							$pref = "G_".strtoupper($id)."_";
							if(substr($k, 0, strlen($pref)) == $pref)
								$arCurrentValues[substr($k, strlen($pref))] = $v;
							else
							{
								$pref = "GU_".strtoupper($id)."_";
								if(substr($k, 0, strlen($pref)) == $pref)
									$arCurrentValues[substr($k, strlen($pref))] = $v;
							}
						}
					}

					if(file_exists($gdDir."/".$id."/lang/".LANGUAGE_ID."/.parameters.php"))
						__IncludeLang($gdDir."/".$id."/lang/".LANGUAGE_ID."/.parameters.php");

					$arParameters = Array();

					if(file_exists($gdDir."/".$id."/.parameters.php"))
						include($gdDir."/".$id."/.parameters.php");
					$arDescription["PARAMETERS"] = $arParameters["PARAMETERS"];
					$arDescription["USER_PARAMETERS"] = array(
						"TITLE_STD" => Array(
							"NAME" => GetMessage("CMDESKTOP_UP_TITLE_STD"),
							"TYPE" => "STRING",
							"DEFAULT" => ""
						)
					);
					if (array_key_exists("USER_PARAMETERS", $arParameters) && is_array($arParameters["USER_PARAMETERS"]))
						$arDescription["USER_PARAMETERS"] = array_merge($arDescription["USER_PARAMETERS"], $arParameters["USER_PARAMETERS"]);
				}
				$arDescription["PATH"] = $gdDir."/".$id;
				$arDescription["ID"] = strtoupper($id);
				if($arDescription["ICON"] && substr($arDescription["ICON"], 0, 1)!="/")
					$arDescription["ICON"] = "/bitrix/gadgets/".$NS."/".$id."/".$arDescription["ICON"];

				unset($arDescription["NOPARAMS"]);

				$arGadget = $arDescription;
			}
		}
		return $arGadget;
	}
}

?>
