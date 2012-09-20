<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$site_id = trim($_REQUEST["site"]);
define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = trim($_REQUEST["action"]);
$entity_type = trim($_REQUEST["et"]);
$entity_id = $_REQUEST["eid"];
$cb_id = $_REQUEST["cb_id"];
$event_id = trim($_REQUEST["evid"]);
if (array_key_exists("transport", $_REQUEST))
	$transport = trim($_REQUEST["transport"]);
if (array_key_exists("visible", $_REQUEST))
	$visible = trim($_REQUEST["visible"]);

$lng = trim($_REQUEST["lang"]);
$ls = trim($_REQUEST["ls"]);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__)."/lang/".$lng."/ajax.php");	

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$arData = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
		$arData[0] = "*";
	elseif (!check_bitrix_sessid())
		$arData[0] = "*";
	elseif ($action == "get_data")
	{
		$is_my = false;
			
		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])			
			&& array_key_exists("CLASS_MY_BY_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& array_key_exists("METHOD_MY_BY_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])				
		)
			$is_my = call_user_func(
				array(
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["CLASS_MY_BY_ID"],
					$GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["METHOD_MY_BY_ID"]
				),
				$entity_id
			);

		$arSubscribe = array();

		$arFilter = array(
			"USER_ID" 		=> $GLOBALS["USER"]->GetID(), 
			"ENTITY_TYPE" 	=> $entity_type,
			"ENTITY_ID" 	=> $entity_id,
			"ENTITY_CB" 	=> "N"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["EVENT"] = array(
									"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
									"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
									"TRANSPORT_INHERITED"	=> false,
									"VISIBLE_INHERITED"		=> false
								);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
				$arSubscribe["ALL"] = array(
									"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
									"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
									"TRANSPORT_INHERITED"	=> false,
									"VISIBLE_INHERITED"		=> false
								);	
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" 		=> $GLOBALS["USER"]->GetID(), 
			"ENTITY_TYPE" 	=> SONET_SUBSCRIBE_ENTITY_USER,
			"ENTITY_ID" 	=> $cb_id,
			"ENTITY_CB" 	=> "Y"
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);

		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($arSubscribesTmp["EVENT_ID"] == $event_id)
				$arSubscribe["CB_EVENT"] = array(
									"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
									"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
									"TRANSPORT_INHERITED"	=> false,
									"VISIBLE_INHERITED"		=> false
								);
			elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["CB_ALL"] = array(
									"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
									"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
									"TRANSPORT_INHERITED"	=> false,
									"VISIBLE_INHERITED"		=> false
								);
			else
				continue;
		}

		$arFilter = array(
			"USER_ID" => $GLOBALS["USER"]->GetID(), 
			"ENTITY_TYPE" => $entity_type,
			"ENTITY_ID" => 0
		);

		$dbResultTmp = CSocNetLogEvents::GetList(
				array(),
				$arFilter
			);
			
		while($arSubscribesTmp = $dbResultTmp->Fetch())
		{
			if ($is_my && $arSubscribesTmp["ENTITY_MY"] == "Y")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT_MY"] = array(
								"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
								"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
								"TRANSPORT_INHERITED"	=> false,
								"VISIBLE_INHERITED"		=> false
							);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL_MY"] = array(
								"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
								"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
								"TRANSPORT_INHERITED"	=> false,
								"VISIBLE_INHERITED"		=> false
							);
				else
					continue;
			}
			elseif ($arSubscribesTmp["ENTITY_MY"] == "N")
			{
				if ($arSubscribesTmp["EVENT_ID"] == $event_id)
					$arSubscribe["COMMON_EVENT"] = array(
								"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
								"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
								"TRANSPORT_INHERITED"	=> false,
								"VISIBLE_INHERITED"		=> false
							);
				elseif ($arSubscribesTmp["EVENT_ID"] == 'all')
					$arSubscribe["COMMON_ALL"] = array(
								"TRANSPORT" 			=> $arSubscribesTmp["TRANSPORT"],
								"VISIBLE" 				=> $arSubscribesTmp["VISIBLE"],
								"TRANSPORT_INHERITED"	=> false,
								"VISIBLE_INHERITED"		=> false
							);
				else
					continue;
			}
		}

		$arTmp = array("TRANSPORT", "VISIBLE");
		foreach ($arTmp as $strTmp)
		{
		
			if ($strTmp == "TRANSPORT")
				$value_default = "N";
			elseif ($strTmp == "VISIBLE")
				$value_default = "Y";

			if (
				!array_key_exists("EVENT", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["EVENT"])
				|| $arSubscribe["EVENT"][$strTmp] == "I"
			)
			{
				if (
					array_key_exists("ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["ALL"])
					&& $arSubscribe["ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["ALL"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					$is_my 
					&& array_key_exists("COMMON_EVENT_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
					&& $arSubscribe["COMMON_EVENT_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT_MY"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					$is_my 
					&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_EVENT", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
					&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["EVENT"][$strTmp] = $value_default;
					$arSubscribe["EVENT"][$strTmp."_INHERITED"] = true;
				}
			}
			
			if (
				!array_key_exists("ALL", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["ALL"])
				|| $arSubscribe["ALL"][$strTmp] == "I"
			)
			{
				if (
					$is_my 
					&& array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["ALL"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["ALL"][$strTmp] = $value_default;
					$arSubscribe["ALL"][$strTmp."_INHERITED"] = true;
				}
			}

			if (
				$is_my 
				&& 
				(
					!array_key_exists("COMMON_EVENT_MY", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT_MY"])
					|| $arSubscribe["COMMON_EVENT_MY"][$strTmp] == "I"
				)
			)
			{
				if (
					array_key_exists("COMMON_ALL_MY", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					&& $arSubscribe["COMMON_ALL_MY"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL_MY"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_EVENT", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
					&& $arSubscribe["COMMON_EVENT"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_EVENT"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				elseif (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_EVENT_MY"][$strTmp] = $value_default;
					$arSubscribe["COMMON_EVENT_MY"][$strTmp."_INHERITED"] = true;
				}
			}
			
			if (
				$is_my 
				&& 
				(
					!array_key_exists("COMMON_ALL_MY", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL_MY"])
					|| $arSubscribe["COMMON_ALL_MY"][$strTmp] == "I"
				)
			)
			{
				if (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_ALL_MY"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_ALL_MY"][$strTmp] = $value_default;
					$arSubscribe["COMMON_ALL_MY"][$strTmp."_INHERITED"] = true;
				}
			}		
			
			if (
				!array_key_exists("COMMON_EVENT", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_EVENT"])
				|| $arSubscribe["COMMON_EVENT"][$strTmp] == "I"
			)
			{
				if (
					array_key_exists("COMMON_ALL", $arSubscribe)
					&& array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
					&& $arSubscribe["COMMON_ALL"][$strTmp] != "I"
				)
				{
					$arSubscribe["COMMON_EVENT"][$strTmp] = $arSubscribe["COMMON_ALL"][$strTmp];
					$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
				}
				else
				{
					$arSubscribe["COMMON_EVENT"][$strTmp] = $value_default;
					$arSubscribe["COMMON_EVENT"][$strTmp."_INHERITED"] = true;
				}
			}
			
			if (
				!array_key_exists("COMMON_ALL", $arSubscribe)
				|| !array_key_exists($strTmp, $arSubscribe["COMMON_ALL"])
				|| $arSubscribe["COMMON_ALL"][$strTmp] == "I"
			)
			{
				$arSubscribe["COMMON_ALL"][$strTmp] = $value_default;
				$arSubscribe["COMMON_ALL"][$strTmp."_INHERITED"] = true;
			}
		
		}
		

		$bFound = false;
		foreach ($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
		{
			if (
				(
					$event_id_tmp == $event_id
					|| 
					(
						array_key_exists("FULL_SET", $arEventTmp)
						&& is_array($arEventTmp["FULL_SET"])
						&& in_array($event_id, $arEventTmp["FULL_SET"])	
					)
				)			
				&& array_key_exists("ENTITIES", $arEventTmp)
				&& is_array($arEventTmp["ENTITIES"])
				&& array_key_exists($entity_type, $arEventTmp["ENTITIES"])
				&& is_array($arEventTmp["ENTITIES"][$entity_type])
				&& array_key_exists("TITLE_SETTINGS", $arEventTmp["ENTITIES"][$entity_type])
				&& strlen($arEventTmp["ENTITIES"][$entity_type]["TITLE_SETTINGS"]) > 0
			)
			{
				$arSubscribe["EVENT"]["TITLE"] = $arEventTmp["ENTITIES"][$entity_type]["TITLE_SETTINGS"];
				$bFound = true;
				break;
			}
		}		

		if (!$bFound)
		{
			foreach ($GLOBALS["arSocNetFeaturesSettings"] as $feature_id_tmp => $arFeatureTmp)
			{
				if (array_key_exists("subscribe_events", $arFeatureTmp))
				{
					foreach ($arFeatureTmp["subscribe_events"] as $event_id_tmp => $arEventTmp)
					{
						if (
							(
								$event_id_tmp == $event_id
								|| 
								(
									array_key_exists("FULL_SET", $arEventTmp)
									&& is_array($arEventTmp["FULL_SET"])
									&& in_array($event_id, $arEventTmp["FULL_SET"])	
								)
							)
							&& array_key_exists("ENTITIES", $arEventTmp)
							&& is_array($arEventTmp["ENTITIES"])
							&& array_key_exists($entity_type, $arEventTmp["ENTITIES"])
							&& is_array($arEventTmp["ENTITIES"][$entity_type])
							&& array_key_exists("TITLE_SETTINGS", $arEventTmp["ENTITIES"][$entity_type])
							&& strlen($arEventTmp["ENTITIES"][$entity_type]["TITLE_SETTINGS"]) > 0
						)
						{
							$arSubscribe["EVENT"]["TITLE"] = $arEventTmp["ENTITIES"][$entity_type]["TITLE_SETTINGS"];
							$bFound = true;
							break;
						}
					}
				}
			}
		}

		if (
			array_key_exists($entity_type, $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("TITLE_SETTINGS_ALL", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL"]) > 0
		)
			$arSubscribe["ALL"]["TITLE"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["TITLE_SETTINGS_ALL"];
		
		$bFound = false;
		if (
			array_key_exists($event_id, $GLOBALS["arSocNetLogEvents"])
			&& array_key_exists("HAS_CB", $GLOBALS["arSocNetLogEvents"][$event_id])
			&& $GLOBALS["arSocNetLogEvents"][$event_id]["HAS_CB"] == "Y"
		)
			$bFound = true;

		if (!$bFound)
		{
			foreach($GLOBALS["arSocNetFeaturesSettings"] as $feature_id_tmp => $arFeatureTmp)
			{
				if (
					array_key_exists("subscribe_events", $arFeatureTmp)
					&& is_array($arFeatureTmp["subscribe_events"])
					&& array_key_exists($event_id, $arFeatureTmp["subscribe_events"])
				)
				{
					if (
						array_key_exists("HAS_CB", $arFeatureTmp["subscribe_events"][$event_id])
						&& $arFeatureTmp["subscribe_events"][$event_id]["HAS_CB"] == "Y"
					)
						$bFound = true;					
					break;
				}
			}
		}

		if ($bFound)
		{
			foreach ($arTmp as $strTmp)
			{
				if ($strTmp == "TRANSPORT")
					$value_default = "N";
				elseif ($strTmp == "VISIBLE")
					$value_default = "Y";
				
				if (
					!array_key_exists("CB_EVENT", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["CB_EVENT"])
					|| $arSubscribe["CB_EVENT"][$strTmp] == "I"
				)
				{
					if (
						array_key_exists("CB_ALL", $arSubscribe)
						&& array_key_exists($strTmp, $arSubscribe["CB_ALL"])
						&& $arSubscribe["CB_ALL"][$strTmp] != "I"
					)
					{
						$arSubscribe["CB_EVENT"][$strTmp] = $arSubscribe["CB_ALL"][$strTmp];
						$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
					}
					else
					{
						$arSubscribe["CB_EVENT"][$strTmp] = $value_default;
						$arSubscribe["CB_EVENT"][$strTmp."_INHERITED"] = true;
					}
				}
				
				if (
					!array_key_exists("CB_ALL", $arSubscribe)
					|| !array_key_exists($strTmp, $arSubscribe["CB_ALL"])
					|| $arSubscribe["CB_ALL"][$strTmp] == "I"				
				)
				{
					$arSubscribe["CB_ALL"][$strTmp] = $value_default;
					$arSubscribe["CB_ALL"][$strTmp."_INHERITED"] = true;
				}
			}
			
			$arSubscribe["CB_ALL"]["TITLE"]	= GetMessage("SUBSCRIBE_CB_ALL");
		}
		else
		{
			if (array_key_exists("CB_EVENT", $arSubscribe))
				unset($arSubscribe["CB_EVENT"]);
			if (array_key_exists("CB_ALL", $arSubscribe))
				unset($arSubscribe["CB_ALL"]);
		}

		$arSubscribe["SITE_ID"] = (
			array_key_exists("HAS_SITE_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
			&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
			&& strlen($site_id) > 0 
			? 
				$site_id 
			: 
				false
		);
								
		$arResult["Subscription"] = $arSubscribe;

		$arResult["Transport"] = array(
			0 => array("Key" => "N", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_NONE")),
			1 => array("Key" => "M", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_MAIL")),
//			3 => array("Key" => "D", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_DIGEST")),
//			4 => array("Key" => "E", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_DIGEST_WEEK"))
		);
		
		if (CBXFeatures::IsFeatureEnabled("WebMessenger"))
			$arResult["Transport"][] = array("Key" => "X", "Value" => GetMessage("SUBSCRIBE_TRANSPORT_XMPP"));
		
		$arResult["Visible"] = array(
			0 => array("Key" => "Y", "Value" => GetMessage("SUBSCRIBE_VISIBLE_Y")),
			1 => array("Key" => "N", "Value" => GetMessage("SUBSCRIBE_VISIBLE_N")),
		);		

	}
	elseif ($action == "set")
	{
		$arFields = false;

		if (in_array($ls, array("EVENT", "ALL")))
		{
			$arFields = array(
				"USER_ID" 		=> $GLOBALS["USER"]->GetID(),
				"ENTITY_TYPE" 	=> $entity_type,
				"ENTITY_ID" 	=> $entity_id,
				"ENTITY_CB" 	=> "N"
			);
			
			if($ls == "EVENT")
			{
				$bFound = false;
				foreach ($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
				{
					if (
						array_key_exists("FULL_SET", $arEventTmp)
						&& in_array($event_id, $arEventTmp["FULL_SET"])
					)
					{
						$arEventID = $arEventTmp["FULL_SET"];
						$bFound = true;
						break;
					}
				}
				
				if (!$bFound)
				{
					foreach($GLOBALS["arSocNetFeaturesSettings"] as $arFeatureTmp)
					{
						if (array_key_exists("subscribe_events", $arFeatureTmp))
						{
							foreach($arFeatureTmp["subscribe_events"] as $event_id_tmp => $arEventTmp)
							{
								if (
									array_key_exists("FULL_SET", $arEventTmp)
									&& in_array($event_id, $arEventTmp["FULL_SET"])
								)
								{
									$arEventID = $arEventTmp["FULL_SET"];
									$bFound = true;
									break;									
								}
							}
							if ($bFound)
								break;
						}
					}
				}
					
				if (!$bFound)
					$arEventID = array($event_id);
			}
			else
				$arEventID = array("all");

		}
		elseif (in_array($ls, array("CB_ALL")))
		{
			$arFields = array(
				"USER_ID" 		=> $GLOBALS["USER"]->GetID(),
				"ENTITY_TYPE" 	=> SONET_SUBSCRIBE_ENTITY_USER,
				"ENTITY_ID" 	=> $cb_id,
				"ENTITY_CB" 	=> "Y"
			);

			$arEventID = array("all");
		}
		
		if ($arFields && (strlen($transport) > 0 || strlen($visible) > 0))
		{
			if (
				$arFields["ENTITY_CB"] != "Y"
				&& array_key_exists("HAS_SITE_ID", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type])
				&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$entity_type]["HAS_SITE_ID"] == "Y"
				&& strlen($site_id) > 0
			)
				$arFieldsVal["SITE_ID"] = $site_id;
			else
				$arFieldsVal["SITE_ID"] = false;
									
			if (strlen($transport) > 0)
				$arFieldsVal["TRANSPORT"] = $transport;

			if (strlen($visible) > 0)
				$arFieldsVal["VISIBLE"] = $visible;
		
			foreach($arEventID as $event_id)
			{
				$arFields["EVENT_ID"] = $event_id;
				
				$dbResultTmp = CSocNetLogEvents::GetList(
									array(),
									$arFields,
									false, 
									false, 
									array("ID", "TRANSPORT", "VISIBLE")
								);

				$arFieldsSet = array_merge($arFields, $arFieldsVal);

				if ($arResultTmp = $dbResultTmp->Fetch())
				{
					if ($arFieldsVal["TRANSPORT"] == "I")
					{
						if ($arResultTmp["VISIBLE"] == "I")
							CSocNetLogEvents::Delete($arResultTmp["ID"]);
						else
							$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
					}
					elseif ($arFieldsVal["VISIBLE"] == "I")
					{
						if ($arResultTmp["TRANSPORT"] == "I")
							CSocNetLogEvents::Delete($arResultTmp["ID"]);
						else
							$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
					}
					else
							$idTmp = CSocNetLogEvents::Update($arResultTmp["ID"], $arFieldsSet);
				}	
				elseif(
					$arFieldsVal["TRANSPORT"] != "I"
					&& $arFieldsVal["VISIBLE"] != "I"
				)
				{
					if (!array_key_exists("TRANSPORT", $arFieldsSet))
						$arFieldsSet["TRANSPORT"] = "I";
					if (!array_key_exists("VISIBLE", $arFieldsSet))
						$arFieldsSet["VISIBLE"] = "I";
						
					$idTmp = CSocNetLogEvents::Add($arFieldsSet);
				}
			}
		}
	}

	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>