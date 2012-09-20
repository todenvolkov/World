<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$errorMessage = "";

if (check_bitrix_sessid())
{
	$GROUP_ID = intval($_REQUEST['GROUP_ID']);
	$USER_ID = intval($_REQUEST['USER_ID']);

	if ($GROUP_ID && $USER_ID && CModule::IncludeModule('socialnetwork'))
	{
		$arGroup = CSocNetGroup::GetByID($GROUP_ID);
		
		if (intval($arGroup["OWNER_ID"]) != $USER_ID)
		{
			if ($arGroup)
			{
				$CurrentUserPerms = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetID(), $arGroup, CSocNetUser::IsCurrentUserModuleAdmin());
				
				if ($CurrentUserPerms["UserCanModifyGroup"])
				{
					$GLOBALS["DB"]->StartTransaction();
				
					// setting relations for the old owner
					$dbRelation = CSocNetUserToGroup::GetList(array(), array("USER_ID" => $arGroup["OWNER_ID"], "GROUP_ID" => $GROUP_ID), false, false, array("ID"));
					if ($arRelation = $dbRelation->Fetch())
					{
						$arFields = array(
							"ROLE" => SONET_ROLES_USER,
							"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
							"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
							"INITIATED_BY_USER_ID" => $GLOBALS["USER"]->GetID(),
						);

						if (!CSocNetUserToGroup::Update($arRelation["ID"], $arFields))
						{
							$errorMessage = "";
							if ($e = $APPLICATION->GetException())
								$errorMessage = $e->GetString();
							if (StrLen($errorMessage) <= 0)
								$errorMessage = "Cannot update user2group relation";

							$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_USER2GROUP");
							$GLOBALS["DB"]->Rollback();
						}
					}
					else
					{
						$errorMessage = "";
						if ($e = $APPLICATION->GetException())
							$errorMessage = $e->GetString();
						if (StrLen($errorMessage) <= 0)
							$errorMessage = "Cannot get user2group relation for old owner";

						$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_GET_USER2GROUP");
						$GLOBALS["DB"]->Rollback();
					}

					// delete requests to the old owner
					if (strlen($errorMessage) <= 0)
						CSocNetUserToGroup::__SpeedFileDelete($arGroup["OWNER_ID"]);

					if (strlen($errorMessage) <= 0)
					{
						// setting relations for the new owner
						$dbRelation = CSocNetUserToGroup::GetList(array(), array("USER_ID" => $USER_ID, "GROUP_ID" => $GROUP_ID), false, false, array("ID"));
						if ($arRelation = $dbRelation->Fetch())
						{
							$arFields = array(
								"ROLE" => SONET_ROLES_OWNER,
								"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
								"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
								"INITIATED_BY_USER_ID" => $GLOBALS["USER"]->GetID(),
							);

							if (!CSocNetUserToGroup::Update($arRelation["ID"], $arFields))
							{
								$errorMessage = "";
								if ($e = $APPLICATION->GetException())
									$errorMessage = $e->GetString();
								if (StrLen($errorMessage) <= 0)
									$errorMessage = "Cannot update user2group relation for new owner";

								$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_USER2GROUP");
								$GLOBALS["DB"]->Rollback();
							}
						}
						else
						{
							$arFields = array(
								"USER_ID" => $USER_ID,
								"GROUP_ID" => $GROUP_ID,
								"ROLE" => SONET_ROLES_OWNER,
								"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
								"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
								"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
								"INITIATED_BY_USER_ID" => $GLOBALS["USER"]->GetID(),
								"MESSAGE" => false,
							);

							if (!CSocNetUserToGroup::Add($arFields))
							{
								$errorMessage = "";
								if ($e = $APPLICATION->GetException())
									$errorMessage = $e->GetString();
								if (StrLen($errorMessage) <= 0)
									$errorMessage = "Cannot add user2group relation for new owner";

								$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_ADD_USER2GROUP");
								$GLOBALS["DB"]->Rollback();
							}

						}
					}

					if (strlen($errorMessage) <= 0)
					{
						$groupID = CSocNetGroup::Update($GROUP_ID, array("OWNER_ID" => $USER_ID));
						if (!$groupID || IntVal($groupID) <= 0)
						{
							$errorMessage = "";
							if ($e = $GLOBALS["APPLICATION"]->GetException())
								$errorMessage = $e->GetString();
							if (StrLen($errorMessage) <= 0)
								$errorMessage = "Cannot update group";

							$GLOBALS["APPLICATION"]->ThrowException($errorMessage, "ERROR_UPDATE_GROUP");
							$GLOBALS["DB"]->Rollback();
						}
						else
							CSocNetEventUserView::SetGroup($groupID, true);
					}
					
					// send message to the old owner
					$arMessageFields = array(
						"FROM_USER_ID" => $GLOBALS["USER"]->GetID(),
						"TO_USER_ID" => $arGroup["OWNER_ID"],
						"MESSAGE" => str_replace("#NAME#", $arGroup["NAME"], GetMessage("SONET_UG_OWNER2MEMBER_MESSAGE")),
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
					);
					CSocNetMessages::Add($arMessageFields);

					// send message to the new owner
					$arMessageFields = array(
						"FROM_USER_ID" => $GLOBALS["USER"]->GetID(),
						"TO_USER_ID" => $USER_ID,
						"MESSAGE" => str_replace("#NAME#", $arGroup["NAME"], GetMessage("SONET_UG_MEMBER2OWNER_MESSAGE")),
						"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
						"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
					);
					CSocNetMessages::Add($arMessageFields);

					// add entry to log
					$logID = CSocNetLog::Add(
						array(
							"ENTITY_TYPE" => SONET_ENTITY_GROUP,
							"SITE_ID" => $arGroup["SITE_ID"],							
							"ENTITY_ID" => $GROUP_ID,
							"EVENT_ID" => "system",
							"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
							"TITLE_TEMPLATE" => false,
							"TITLE" => "owner",
							"MESSAGE" => $USER_ID,
							"URL" => false,
							"MODULE_ID" => false,
							"CALLBACK_FUNC" => false,
							"USER_ID" => $USER_ID,
						)
					);
					if (intval($logID) > 0)
						CSocNetLog::Update($logID, array("TMP_ID" => $logID));

					CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);

					if (strlen($errorMessage) <= 0)
					{
						$GLOBALS["DB"]->Commit();
						echo '<script>window.location.reload();</script>';					
					}
					else
						echo '<script>alert(\''.CUtil::JSEscape($errorMessage).'\');</script>';

				}
				else
				{
					echo '<script>alert(\'Access denied!\');</script>';
				}
			}
			else
			{
				echo '<script>alert(\'Group error!\');</script>';
			}
		}
		else
		{
			// new owner is equal to old one
			echo '<script>window.location.reload();</script>';					
		}
	}
	else
	{
		echo '<script>alert(\'Params error!\');</script>';
	}
}
else
{
	echo '<script>alert(\'Session expired!\');</script>';
}
?>