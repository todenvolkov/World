<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/coupons.php");
IncludeModuleLangFile(__FILE__);

global $SUPPORT_CACHE_USER_ROLES;
$SUPPORT_CACHE_USER_ROLES  = Array();

class CAllTicket
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllTicket<br>File: ".__FILE__;
	}

	/***************************************************************

	Группа функций по работе с ролями на модуль

	Идентификаторы ролей:

	D - доступ закрыт
	R - клиент техподдержки
	T - сотрудник техподдержки
	V - демо-доступ
	W - администратор техподдержки

	*****************************************************************/

	function GetDeniedRoleID()
	{
		return "D";
	}

	function GetSupportClientRoleID()
	{
		return "R";
	}

	function GetSupportTeamRoleID()
	{
		return "T";
	}

	function GetDemoRoleID()
	{
		return "V";
	}

	function GetAdminRoleID()
	{
		return "W";
	}

	// возвращает true если заданный пользователь имеет заданную роль на модуль
	function HaveRole($role, $USER_ID=false)
	{
		global $DB, $USER, $APPLICATION, $SUPPORT_CACHE_USER_ROLES;
		if (!is_object($USER)) $USER = new CUser;

		if ($USER_ID===false && is_object($USER))
			$UID = $USER->GetID();
		else
			$UID = $USER_ID;

		$arRoles = Array();
		if (array_key_exists($UID, $SUPPORT_CACHE_USER_ROLES) && is_array($SUPPORT_CACHE_USER_ROLES[$UID]))
		{
			$arRoles = $SUPPORT_CACHE_USER_ROLES[$UID];
		}
		else
		{
			$arrGroups = Array();
			if ($USER_ID===false && is_object($USER))
				$arrGroups = $USER->GetUserGroupArray();
			else
				$arrGroups = CUser::GetUserGroup($USER_ID);

			sort($arrGroups);
			$arRoles = $APPLICATION->GetUserRoles("support", $arrGroups);
			$SUPPORT_CACHE_USER_ROLES[$UID] = $arRoles;
		}

		if (in_array($role, $arRoles))
			return true;

		return false;

	}

	// true - если пользователь имеет роль "администратор техподдержки"
	// false - в противном случае
	function IsAdmin($USER_ID=false)
	{
		global $USER;

		if ($USER_ID===false && is_object($USER))
		{
			if ($USER->IsAdmin()) return true;
		}
		return CTicket::HaveRole(CTicket::GetAdminRoleID(), $USER_ID);
	}

	// true - если пользователь имеет роль "демо-доступ"
	// false - в противном случае
	function IsDemo($USER_ID=false)
	{
		return CTicket::HaveRole(CTicket::GetDemoRoleID(), $USER_ID);
	}

	// true - если пользователь имеет роль "сотрудник техподдержки"
	// false - в противном случае
	function IsSupportTeam($USER_ID=false)
	{
		return CTicket::HaveRole(CTicket::GetSupportTeamRoleID(), $USER_ID);
	}

	// true - если пользователь имеет роль "сотрудник техподдержки"
	// false - в противном случае
	function IsSupportClient($USER_ID=false)
	{
		return CTicket::HaveRole(CTicket::GetSupportClientRoleID(), $USER_ID);
	}

	function IsOwner($TICKET_ID, $USER_ID=false)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: IsOwner<br>Line: ";
		global $DB, $USER;
		if ($USER_ID===false && is_object($USER)) $USER_ID = $USER->GetID();
		$USER_ID = intval($USER_ID);
		$TICKET_ID = intval($TICKET_ID);
		if ($USER_ID<=0 || $TICKET_ID<=0) return false;

		$strSql = "SELECT 'x' FROM b_ticket WHERE ID=$TICKET_ID and (OWNER_USER_ID=$USER_ID or CREATED_USER_ID=$USER_ID)";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch()) return true;

		return false;
	}

	// возвращает роли заданного пользователя
	function GetRoles(&$isDemo, &$isSupportClient, &$isSupportTeam, &$isAdmin, &$isAccess, &$USER_ID, $CHECK_RIGHTS=true)
	{
		global $DB, $USER, $APPLICATION;
		static $arTICKET_USER_ROLES;
		$isDemo = $isSupportClient = $isSupportTeam = $isAdmin = $isAccess = false;
		if (is_object($USER)) $USER_ID = intval($USER->GetID()); else $USER_ID = 0;
		if ($CHECK_RIGHTS)
		{
			if ($USER_ID>0)
			{
				if (is_array($arTICKET_USER_ROLES) && in_array($USER_ID, array_keys($arTICKET_USER_ROLES)))
				{
					$isDemo = $arTICKET_USER_ROLES[$USER_ID]["isDemo"];
					$isSupportClient = $arTICKET_USER_ROLES[$USER_ID]["isSupportClient"];
					$isSupportTeam = $arTICKET_USER_ROLES[$USER_ID]["isSupportTeam"];
					$isAdmin = $arTICKET_USER_ROLES[$USER_ID]["isAdmin"];
				}
				else
				{
					$isDemo = CTicket::IsDemo($USER_ID);
					$isSupportClient = CTicket::IsSupportClient($USER_ID);
					$isSupportTeam = CTicket::IsSupportTeam($USER_ID);
					$isAdmin = CTicket::IsAdmin($USER_ID);
					$arTICKET_USER_ROLES[$USER_ID] = array(
						"isDemo"			=> $isDemo,
						"isSupportClient"	=> $isSupportClient,
						"isSupportTeam"		=> $isSupportTeam,
						"isAdmin"			=> $isAdmin,
						);
				}
			}
		}
		else $isAdmin = true;

		if ($isDemo || $isSupportClient || $isSupportTeam || $isAdmin) $isAccess = true;
	}

	// возвращает массив ID групп для которых задана роль
	// $role - идентификатор роли
	function GetGroupsByRole($role)
	{
		//Todo: определиться с доступом по умолчанию

		global $APPLICATION, $USER;
		if (!is_object($USER)) $USER = new CUser;

		$arGroups = array(); $arBadGroups = Array();
		$res = $APPLICATION->GetGroupRightList(Array("MODULE_ID" => "support"/*, "G_ACCESS" => $role*/));
		while($ar = $res->Fetch())
		{
			if ($ar["G_ACCESS"] == $role)
				$arGroups[] = $ar["GROUP_ID"];
			else
				$arBadGroups[] = $ar["GROUP_ID"];
		}

		$right = COption::GetOptionString("support", "GROUP_DEFAULT_RIGHT", "D");
		if ($right == $role)
		{
			$res = CGroup::GetList($v1="dropdown", $v2="asc", array("ACTIVE" => "Y"));
			while ($ar = $res->Fetch())
			{
				if (!in_array($ar["ID"],$arGroups) && !in_array($ar["ID"],$arBadGroups))
					$arGroups[] = $ar["ID"];
			}
		}

		//echo "1".$role."1"; print_r($arGroups);echo "<br>";
		return $arGroups;

		/*$arGroups = array();

		$z = CGroup::GetList($v1="dropdown", $v2="asc", array("ACTIVE" => "Y"));
		while($zr = $z->Fetch())
		{
			$arRoles = $APPLICATION->GetUserRoles("support", array(intval($zr["ID"])), "Y", "N");
			if (in_array($role, $arRoles)) $arGroups[] = intval($zr["ID"]);
		}

		//echo "2".$role."2"; print_r(array_unique($arGroups));echo "<br>";
		return array_unique($arGroups);*/
	}

	// возвращает массив групп с ролью "администратор техподдержки"
	function GetAdminGroups()
	{
		return CTicket::GetGroupsByRole(CTicket::GetAdminRoleID());
	}

	// возвращает массив групп с ролью "сотрудник техподдержки"
	function GetSupportTeamGroups()
	{
		return CTicket::GetGroupsByRole(CTicket::GetSupportTeamRoleID());
	}

	// возвращает массив EMail адресов всех пользователей имеющих заданную роль
	function GetEmailsByRole($role)
	{
		global $DB, $APPLICATION, $USER;
		if (!is_object($USER)) $USER = new CUser;
		$arrEMail = array();
		$arGroups = CTicket::GetGroupsByRole($role);
		if (is_array($arGroups) && count($arGroups)>0)
		{
			$rsUser = CUser::GetList($v1="id", $v2="desc", array("ACTIVE" => "Y", "GROUPS_ID" => $arGroups));
			while ($arUser = $rsUser->Fetch()) $arrEMail[$arUser["EMAIL"]] = $arUser["EMAIL"];
		}
		return array_unique($arrEMail);
	}

	// возвращает массив EMail'ов всех пользователей имеющих роль "администратор"
	function GetAdminEmails()
	{
		return CTicket::GetEmailsByRole(CTicket::GetAdminRoleID());
	}

	// возвращает массив EMail'ов всех пользователей имеющих роль "менеджер баннеров"
	function GetSupportTeamEmails()
	{
		return CTicket::GetEmailsByRole(CTicket::GetSupportTeamRoleID());
	}

	/*****************************************************************
			   Группа функций общие для всех классов
	*****************************************************************/

	// проверка полей фильтра
	function CheckFilter($arFilter)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: CheckFilter<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$str = "";
		$arMsg = Array();

		$arDATES = array(
			"DATE_MODIFY_1",
			"DATE_MODIFY_2",
			"DATE_CREATE_1",
			"DATE_CREATE_2",
			);
		foreach($arDATES as $key)
		{
			if (is_set($arFilter, $key) && strlen($arFilter[$key])>0 && !CheckDateTime($arFilter[$key]))
				$arMsg[] = array("id"=>$key, "text"=> GetMessage("SUP_ERROR_REQUIRED_".$key));
				//$str.= GetMessage("SUP_ERROR_INCORRECT_".$key)."<br>";
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	// проверка полей перед вставкой в базу данных
	function CheckFields($arFields, $ID, $arrREQUIRED)
	{
		global $DB, $USER, $APPLICATION, $MESS;

		$arMsg = Array();

		// проверяем указанные обязательные поля
		if (is_array($arrREQUIRED) && count($arrREQUIRED)>0)
		{
			foreach($arrREQUIRED as $key)
			{
				if ($ID<=0 || ($ID>0 && is_set($arFields, $key)))
				{
					if (!is_array($arFields[$key]) && (strlen($arFields[$key])<=0 || $arFields[$key]=="NOT_REF"))
					{
						$arMsg[] = array("id"=>$key, "text"=> GetMessage("SUP_ERROR_REQUIRED_".$key));
						//$str.= GetMessage("SUP_ERROR_REQUIRED_".$key)."<br>";
					}
				}
			}
		}

		// проверяем корректность дат
		$arrDATE = array(
			"DATE_CREATE",
			"DATE_MODIFY",
			"LAST_MESSAGE_DATE",
			);
		foreach($arrDATE as $key)
		{
			if (strlen($arFields[$key])>0)
			{
				if (!CheckDateTime($arFields[$key]))
					$arMsg[] = array("id"=>$key, "text"=> GetMessage("SUP_ERROR_INCORRECT_".$key));
					//$str.= GetMessage("SUP_ERROR_INCORRECT_".$key)."<br>";
			}
		}

		$arrEMAIL = array(
			"EMAIL",
			);
		foreach($arrEMAIL as $key)
		{
			if (strlen($arFields[$key])>0)
			{
				if (!check_email($arFields[$key]))
					$arMsg[] = array("id"=>$key, "text"=> GetMessage("SUP_ERROR_INCORRECT_".$key));
					//$str.= GetMessage("SUP_ERROR_INCORRECT_".$key)."<br>";
			}
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	// предварительно обрабатывает массив значений для вставки в базу данных
	function PrepareFields($arFields, $table, $ID)
	{
		global $DB, $USER, $APPLICATION;

		$ID = intval($ID);
		$arFields_i = array();

		// числа
		$arrNUMBER = array(
			"SLA_ID",
			"AGENT_ID",
			"CATEGORY_ID",
			"CRITICALITY_ID",
			"STATUS_ID",
			"MARK_ID",
			"SOURCE_ID",
			"DIFFICULTY_ID",
			"DICTIONARY_ID",
			"TICKET_ID",
			"MESSAGE_ID",
			"AUTO_CLOSE_DAYS",
			"MESSAGES",
			"OVERDUE_MESSAGES",
			"EXTERNAL_ID",
			"OWNER_USER_ID",
			"OWNER_GUEST_ID",
			"CREATED_USER_ID",
			"CREATED_GUEST_ID",
			"MODIFIED_USER_ID",
			"MODIFIED_GUEST_ID",
			"RESPONSIBLE_USER_ID",
			"LAST_MESSAGE_USER_ID",
			"LAST_MESSAGE_GUEST_ID",
			"CURRENT_RESPONSIBLE_USER_ID",
			"USER_ID",
			"C_NUMBER",
			"C_SORT",
			"PRIORITY",
			"RESPONSE_TIME",
			"NOTICE_TIME",
			"WEEKDAY_NUMBER",
			"MINUTE_FROM",
			"MINUTE_TILL"
			);
		foreach($arrNUMBER as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = (strlen($arFields[$key])>0) ? intval($arFields[$key]) : "null";

		// тип текста
		$arrTYPE = array(
			"PREVIEW_TYPE",
			"DESCRIPTION_TYPE",
			);
		foreach($arrTYPE as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = $arFields[$key]=="text" ? "'text'" : "'html'";

		// булевые
		$arrBOOLEAN = array(
			"AUTO_CLOSED",
			"IS_SPAM",
			"LAST_MESSAGE_BY_SUPPORT_TEAM",
			"IS_HIDDEN",
			"IS_LOG",
			"IS_OVERDUE",
			"IS_SPAM",
			"MESSAGE_BY_SUPPORT_TEAM",
			"SET_AS_DEFAULT",
			"AUTO_CLOSED",
			"HOLD_ON",
			"NOT_CHANGE_STATUS",
			);
		foreach($arrBOOLEAN as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = $arFields[$key]=="Y" ? "'Y'" : "'N'";

		// текст
		$arrTEXT = array(
			"OWNER_SID",
			"LAST_MESSAGE_SID",
			"SUPPORT_COMMENTS",
			"MESSAGE",
			"MESSAGE_SEARCH",
			"EXTERNAL_FIELD_1",
			"DESCR",
			"DESCRIPTION",
			);
		foreach($arrTEXT as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = (strlen($arFields[$key])>0) ? "'".$DB->ForSql($arFields[$key])."'" : "null";

		// строка
		$arrSTRING = array(
			"NAME",
			"TITLE",
			"CREATED_MODULE_NAME",
			"MODIFIED_MODULE_NAME",
			"HASH",
			"EXTENSION_SUFFIX",
			"C_TYPE",
			"SID",
			"EVENT1",
			"EVENT2",
			"EVENT3",
			"RESPONSE_TIME_UNIT",
			"NOTICE_TIME_UNIT",
			"OPEN_TIME",
			);
		foreach($arrSTRING as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = (strlen($arFields[$key])>0) ? "'".$DB->ForSql($arFields[$key], 255)."'" : "null";

		// даты
		$arrDATE = array(
			"TIMESTAMP_X",
			"DATE_CLOSE",
			"LAST_MESSAGE_DATE",
			);
		foreach($arrDATE as $key)
			if (is_set($arFields, $key))
				$arFields_i[$key] = (strlen($arFields[$key])>0) ? $DB->CharToDateFunction($arFields[$key]) : "null";

		// изображения
		$arIMAGE = array();
		foreach($arIMAGE as $key)
		{
			if (is_set($arFields, $key))
			{
				if (is_array($arFields[$key]))
				{
					$arIMAGE = $arFields[$key];
					$arIMAGE["MODULE_ID"] = "support";
					$arIMAGE["del"] = $_POST[$key."_del"];
					if ($ID>0)
					{
						$rs = $DB->Query("SELECT $key FROM $table WHERE ID=$ID", false, $err_mess.__LINE__);
						$ar = $rs->Fetch();
						$arIMAGE["old_file"] = $ar[$key];
					}
					if (strlen($arIMAGE["name"])>0 || strlen($arIMAGE["del"])>0)
					{
						$fid = CFile::SaveFile($arIMAGE, "support");
						$arFields_i[$key] = (intval($fid)>0) ? intval($fid) : "null";
					}
				}
				else
				{
					if ($ID>0)
					{
						$rs = $DB->Query("SELECT $key FROM $table WHERE ID=$ID", false, $err_mess.__LINE__);
						$ar = $rs->Fetch();
						if (intval($ar[$key])>0) CFile::Delete($ar[$key]);
					}
					$arFields_i[$key] = intval($arFields[$key]);
				}
			}
		}

		if (is_set($arFields, "CREATED_USER_ID"))
		{
			if (intval($arFields["CREATED_USER_ID"])>0) $arFields_i["CREATED_USER_ID"] = intval($arFields["CREATED_USER_ID"]);
		}
		elseif($ID<=0 && $USER->IsAuthorized()) $arFields_i["CREATED_USER_ID"] = intval($USER->GetID());

		if (is_set($arFields, "CREATED_GUEST_ID"))
		{
			if (intval($arFields["CREATED_GUEST_ID"])>0) $arFields_i["CREATED_GUEST_ID"] = intval($arFields["CREATED_GUEST_ID"]);
		}
		elseif($ID<=0 && array_key_exists('SESS_GUEST_ID', $_SESSION)) $arFields_i["CREATED_GUEST_ID"] = intval($_SESSION["SESS_GUEST_ID"]);

		if (is_set($arFields, "MODIFIED_USER_ID"))
		{
			if (intval($arFields["MODIFIED_USER_ID"])>0) $arFields_i["MODIFIED_USER_ID"] = intval($arFields["MODIFIED_USER_ID"]);
		}
		elseif ($USER->IsAuthorized()) $arFields_i["MODIFIED_USER_ID"] = intval($USER->GetID());

		if (is_set($arFields, "MODIFIED_GUEST_ID"))
		{
			if (intval($arFields["MODIFIED_GUEST_ID"])>0) $arFields_i["MODIFIED_GUEST_ID"] = intval($arFields["MODIFIED_GUEST_ID"]);
		}
		elseif (array_key_exists('SESS_GUEST_ID', $_SESSION)) $arFields_i["MODIFIED_GUEST_ID"] = intval($_SESSION["SESS_GUEST_ID"]);

		if (is_set($arFields, "DATE_CREATE"))
		{
			if (strlen($arFields["DATE_CREATE"])>0) $arFields_i["DATE_CREATE"] = $DB->CharToDateFunction($arFields["DATE_CREATE"]);
		}
		elseif ($ID<=0) $arFields_i["DATE_CREATE"] = $DB->CurrentTimeFunction();


		if (is_set($arFields, "LAST_MESSAGE_DATE"))
		{
			if (strlen($arFields["LAST_MESSAGE_DATE"])>0) $arFields_i["LAST_MESSAGE_DATE"] = $DB->CharToDateFunction($arFields["LAST_MESSAGE_DATE"]);
		}
		elseif ($ID<=0) $arFields_i["LAST_MESSAGE_DATE"] = $DB->CurrentTimeFunction();



		if (is_set($arFields, "DATE_MODIFY"))
		{
			if (strlen($arFields["DATE_MODIFY"])>0) $arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
		}
		else $arFields_i["DATE_MODIFY"] = $DB->CurrentTimeFunction();

		// убираем лишние поля для указанной таблицы
		unset($arFields_i["ID"]);
		$ar1 = $DB->GetTableFieldsList($table);
		$ar2 = array_keys($arFields_i);
		$arDiff = array_diff($ar2, $ar1);
		if (is_array($arDiff) && count($arDiff)>0) foreach($arDiff as $value) unset($arFields_i[$value]);

		return $arFields_i;
	}

	/*****************************************************************
				   Группа функций по работе со спамом
	*****************************************************************/

	function MarkMessageAsSpam($MESSAGE_ID, $EXACTLY="Y", $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: MarkMessageAsSpam<br>Line: ";
		global $DB, $USER;
		$MESSAGE_ID = intval($MESSAGE_ID);
		if ($MESSAGE_ID<=0) return;

		$bAdmin = "N";
		$bSupportTeam = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
		}

		if (($bAdmin=="Y" || $bSupportTeam=="Y") && CModule::IncludeModule("mail"))
		{
			$EXACTLY = ($EXACTLY=="Y" && $bAdmin=="Y") ? "Y" : "N";
			if ($rsMessage = CTicket::GetMessageByID($MESSAGE_ID, $CHECK_RIGHTS))
			{
				if ($arMessage = $rsMessage->Fetch())
				{
					if ($arMessage["IS_LOG"]!="Y")
					{
						$email_id = intval($arMessage["EXTERNAL_ID"]);
						$header = $arMessage["EXTERNAL_FIELD_1"];
						$arFields = array("IS_SPAM" => "'".$EXACTLY."'");
						$DB->Update("b_ticket_message",$arFields,"WHERE ID=".$MESSAGE_ID,$err_mess.__LINE__);

						$EXACTLY = ($EXACTLY=="Y") ? true : false;
						$rsEmail = CMailMessage::GetByID($email_id);
						if ($rsEmail->Fetch())
						{
							CMailMessage::MarkAsSpam($email_id, $EXACTLY);
						}
						else
						{
							CmailFilter::MarkAsSpam($header." \n\r ".$arMessage["MESSAGE"], $EXACTLY);
						}
					}
				}
			}
		}
	}

	function UnMarkMessageAsSpam($MESSAGE_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: UnMarkMessageAsSpam<br>Line: ";
		global $DB, $USER;
		$MESSAGE_ID = intval($MESSAGE_ID);
		if ($MESSAGE_ID<=0) return;

		$bAdmin = "N";
		$bSupportTeam = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
		}

		if (($bAdmin=="Y" || $bSupportTeam=="Y") && CModule::IncludeModule("mail"))
		{
			$rsMessage = CTicket::GetMessageByID($MESSAGE_ID, $CHECK_RIGHTS);
			if ($arMessage = $rsMessage->Fetch())
			{
				$arFields = array("IS_SPAM" => "null");
				$DB->Update("b_ticket_message", $arFields, "WHERE ID=".$MESSAGE_ID, $err_mess.__LINE__);

				$email_id = intval($arMessage["EXTERNAL_ID"]);
				$header = $arMessage["EXTERNAL_FIELD_1"];
				$rsEmail = CMailMessage::GetByID($email_id);
				if ($rsEmail->Fetch())
				{
					CMailMessage::MarkAsSpam($email_id, false);
				}
				else
				{
					CmailFilter::DeleteFromSpamBase($header." \n\r ".$arMessage["MESSAGE"], true);
					CmailFilter::MarkAsSpam($header." \n\r ".$arMessage["MESSAGE"], false);
				}
			}
		}
	}

	function MarkAsSpam($TICKET_ID, $EXACTLY="Y", $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: MarkAsSpam<br>Line: ";
		global $DB, $USER;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$bAdmin = "N";
		$bSupportTeam = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
		}

		if ($bAdmin=="Y" || $bSupportTeam=="Y")
		{
			$EXACTLY = ($EXACTLY=="Y" && $bAdmin=="Y") ? "Y" : "N";

			$arFilter = array("TICKET_ID" => $TICKET_ID, "TICKET_ID_EXACT_MATCH" => "Y", "IS_LOG" => "N");
			if ($rsMessages = CTicket::GetMessageList($a, $b, $arFilter, $c, $CHECK_RIGHTS))
			{
				// помечаем исходное сообщение
				if ($arMessage = $rsMessages->Fetch())
				{
					CTicket::MarkMessageAsSpam($arMessage["ID"], $EXACTLY, $CHECK_RIGHTS);
				}
			}
			$arFields = array("IS_SPAM" => "'".$EXACTLY."'");
			$DB->Update("b_ticket",$arFields,"WHERE ID=".$TICKET_ID,$err_mess.__LINE__);
		}
	}

	function UnMarkAsSpam($TICKET_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: UnMarkAsSpam<br>Line: ";
		global $DB, $USER;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
		}

		if ($bAdmin=="Y" || $bSupportTeam=="Y")
		{
			$arFilter = array("TICKET_ID" => $TICKET_ID, "TICKET_ID_EXACT_MATCH" => "Y");
			if ($rsMessages = CTicket::GetMessageList($a, $b, $arFilter, $c, $CHECK_RIGHTS))
			{
				// снимаем отметку о спаме только у первого сообщения
				if ($arMessage = $rsMessages->Fetch())
				{
					CTicket::UnMarkMessageAsSpam($arMessage["ID"], $CHECK_RIGHTS);
				}
			}
			$arFields = array("IS_SPAM" => "null");
			$DB->Update("b_ticket",$arFields,"WHERE ID=".$TICKET_ID,$err_mess.__LINE__);
		}
	}


	/*****************************************************************
			   Группа функций по управлению обращениями
	*****************************************************************/

	function UpdateLastParams($TICKET_ID, $RESET_AUTO_CLOSE=false, $CHANGE_LAST_MESSAGE_DATE = true, $SET_REOPEN_DEFAULT = true)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: UpdateLastParams<br>Line: ";
		global $DB, $USER;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$arFields = array();
		if ($RESET_AUTO_CLOSE=="Y") $arFields["AUTO_CLOSE_DAYS"] = "null";

		// определим последнего автора
		$strSql = "
			SELECT
				ID,
				".$DB->DateToCharFunction("DATE_CREATE","FULL")." DATE_CREATE,
				OWNER_USER_ID,
				OWNER_GUEST_ID,
				OWNER_SID
			FROM
				b_ticket_message
			WHERE
				TICKET_ID=$TICKET_ID
			and (NOT_CHANGE_STATUS='N')
			and (IS_HIDDEN='N' or IS_HIDDEN is null or ".$DB->Length("IS_HIDDEN")."<=0)
			and (IS_LOG='N' or IS_LOG is null or ".$DB->Length("IS_LOG")."<=0)
			and (IS_OVERDUE='N' or IS_OVERDUE is null or ".$DB->Length("IS_OVERDUE")."<=0)
			ORDER BY
				C_NUMBER desc
			";
		//echo $strSql; exit;
		$rs = $DB->Query($strSql,false,$err_mess.__LINE__);
		if ($arLastMess = $rs->Fetch())
		{
			$LAST_MESSAGE_BY_SUPPORT_TEAM = (CTicket::IsAdmin($arLastMess["OWNER_USER_ID"]) || CTicket::IsSupportTeam($arLastMess["OWNER_USER_ID"])) ? "Y" : "N";

			$arFields["LAST_MESSAGE_USER_ID"] = $arLastMess["OWNER_USER_ID"];

			if ($CHANGE_LAST_MESSAGE_DATE)
				$arFields["LAST_MESSAGE_DATE"] = $DB->CharToDateFunction($arLastMess["DATE_CREATE"]);//NN

			$arFields["LAST_MESSAGE_GUEST_ID"] = intval($arLastMess["OWNER_GUEST_ID"]);
			$arFields["LAST_MESSAGE_SID"] = "'".$DB->ForSql($arLastMess["OWNER_SID"],255)."'";
			$arFields["LAST_MESSAGE_BY_SUPPORT_TEAM"] = "'".$LAST_MESSAGE_BY_SUPPORT_TEAM."'";
		}

		// определим количество сообщений
		$strSql = "
			SELECT
				SUM(CASE WHEN IS_HIDDEN='Y' THEN 0 ELSE 1 END) MESSAGES,
				SUM(TASK_TIME) ALL_TIME
			FROM
				b_ticket_message
			WHERE
				TICKET_ID = $TICKET_ID
			and (IS_LOG='N' or IS_LOG is null or ".$DB->Length("IS_LOG")."<=0)
			and (IS_OVERDUE='N' or IS_OVERDUE is null or ".$DB->Length("IS_OVERDUE")."<=0)
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		$arFields["MESSAGES"] = intval($zr["MESSAGES"]);
		$arFields["PROBLEM_TIME"] = intval($zr["ALL_TIME"]);

		if ($SET_REOPEN_DEFAULT)
			$arFields["REOPEN"] = "'N'";

		$DB->Update("b_ticket",$arFields,"WHERE ID='".$TICKET_ID."'",$err_mess.__LINE__);
	}

	function UpdateMessages($TICKET_ID)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: UpdateMessages<br>Line: ";
		global $DB;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$arFields = array();

		// определим количество сообщений
		$strSql = "
			SELECT
				SUM(CASE WHEN IS_HIDDEN='Y' THEN 0 ELSE 1 END) MESSAGES,
				SUM(TASK_TIME) ALL_TIME
			FROM
				b_ticket_message
			WHERE
				TICKET_ID = $TICKET_ID
			and (IS_LOG='N' or IS_LOG is null or ".$DB->Length("IS_LOG")."<=0)
			and (IS_OVERDUE='N' or IS_OVERDUE is null or ".$DB->Length("IS_OVERDUE")."<=0)
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		$arFields["MESSAGES"] = intval($zr["MESSAGES"]);
		$arFields["PROBLEM_TIME"] = intval($zr["ALL_TIME"]);

		$DB->Update("b_ticket",$arFields,"WHERE ID='".$TICKET_ID."'",$err_mess.__LINE__);
	}


	function GetFileList(&$by, &$order, $arFilter=array())
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: GetFileList<br>Line: ";
		global $DB, $USER;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
					continue;
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "LINK_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("MF.ID",$val,$match);
						break;
					case "MESSAGE":
					case "TICKET_ID":
					case "FILE_ID":
					case "HASH":
					case "MESSAGE_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("MF.".$key,$val,$match);
						break;
				}
			}
		}
		if ($by == "s_id")				$strSqlOrder = "ORDER BY MF.ID";
		elseif ($by == "s_file_id")		$strSqlOrder = "ORDER BY F.ID";
		elseif ($by == "s_message_id")	$strSqlOrder = "ORDER BY MF.MESSAGE_ID";
		else
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY MF.ID";
		}
		if ($order=="desc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		else
		{
			$strSqlOrder .= " asc ";
			$order="asc";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				F.*,
				MF.ID as LINK_ID,
				MF.HASH,
				MF.MESSAGE_ID,
				MF.TICKET_ID,
				MF.EXTENSION_SUFFIX
			FROM
				b_ticket_message_2_file MF
			INNER JOIN b_file F ON (MF.FILE_ID = F.ID)
			WHERE
				$strSqlSearch
			$strSqlOrder
		";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function GetMessageByID($ID, $CHECK_RIGHTS="Y", $get_user_name="Y")
	{
		return CTicket::GetMessageList($by, $order, array("ID" => $ID, "ID_EXACT_MATCH" => "Y"), $is_filtered, $CHECK_RIGHTS, $get_user_name);
	}

	function GetByID($ID, $lang=LANG, $CHECK_RIGHTS="Y", $get_user_name="Y", $get_extra_names="Y")
	{
		return CTicket::GetList($by, $order, array("ID" => $ID, "ID_EXACT_MATCH" => "Y"), $is_filtered, $CHECK_RIGHTS, $get_user_name, $get_extra_names, $lang);
	}

	function Delete($TICKET_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $USER;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;
		$bAdmin = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
		}
		if ($bAdmin=="Y")
		{
			if (CTicket::ExecuteEvents('OnBeforeTicketDelete', $TICKET_ID, false) === false)
				return false;
			CTicket::ExecuteEvents('OnTicketDelete', $TICKET_ID, false);
				
			$strSql = "
				SELECT
					F.ID
				FROM
					b_ticket_message_2_file MF,
					b_file F
				WHERE
					MF.TICKET_ID = '$TICKET_ID'
				and F.ID=MF.FILE_ID
				";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch()) CFile::Delete($zr["ID"]);

			CTicketReminder::Delete($TICKET_ID);
			$DB->Query("DELETE FROM b_ticket_message_2_file WHERE TICKET_ID='$TICKET_ID'", false, $err_mess.__LINE__);
			$DB->Query("DELETE FROM b_ticket_message WHERE TICKET_ID='$TICKET_ID'", false, $err_mess.__LINE__);
			$DB->Query("DELETE FROM b_ticket WHERE ID='$TICKET_ID'", false, $err_mess.__LINE__);
		}
	}

	function UpdateOnline($TICKET_ID, $USER_ID=false, $CURRENT_MODE="")
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: UpdateOnline<br>Line: ";
		global $DB, $USER;
		if ($USER_ID===false && is_object($USER)) $USER_ID = $USER->GetID();
		$TICKET_ID = intval($TICKET_ID);
		$USER_ID = intval($USER_ID);
		if ($TICKET_ID<=0 || $USER_ID<=0) return;
		$arFields = array(
			"TIMESTAMP_X"	=> $DB->GetNowFunction(),
			"TICKET_ID"		=> $TICKET_ID,
			"USER_ID"		=> $USER_ID,
			);
		if ($CURRENT_MODE!==false)
		{
			$arFields["CURRENT_MODE"] = strlen($CURRENT_MODE)>0 ? "'".$DB->ForSQL($CURRENT_MODE, 20)."'" : "null";
		}
		$rows = $DB->Update("b_ticket_online", $arFields, "WHERE TICKET_ID=$TICKET_ID and USER_ID=$USER_ID", $err_mess.__LINE__);
		if (intval($rows)<=0)
		{
			$DB->Insert("b_ticket_online",$arFields, $err_mess.__LINE__);
		}
	}

	function SetTicket($arFields, $TICKET_ID="", $CHECK_RIGHTS="Y", $SEND_EMAIL_TO_AUTHOR="Y", $SEND_EMAIL_TO_TECHSUPPORT="Y")
	{
		//global $DB;
		//$DB->DebugToFile = true;
		$x = CTicket::Set($arFields, $MESSAGE_ID, $TICKET_ID, $CHECK_RIGHTS, $SEND_EMAIL_TO_AUTHOR, $SEND_EMAIL_TO_TECHSUPPORT);
		//$DB->DebugToFile = false;
		return $x;
	}

	function Set($arFields, &$MID, $ID="", $CHECK_RIGHTS="Y", $SEND_EMAIL_TO_AUTHOR="Y", $SEND_EMAIL_TO_TECHSUPPORT="Y")
	{
		global $DB, $APPLICATION, $USER;
		$err_mess = (CAllTicket::err_mess())."<br>Function: Set<br>Line: ";
		if (!is_object($USER)) $USER = new CUser;
		$ID = intval($ID);

		// заголовок и сообщение - обязательные поля для нового обращения
		if ($ID <= 0)
		{
			if(strlen($arFields["TITLE"]) <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_EMPTY_TITLE'));
				return false;
			}

			if (strlen($arFields["MESSAGE"]) <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_EMPTY_MESSAGE'));
				return false;
			}
		}

		$CHECK_RIGHTS = ($CHECK_RIGHTS=="Y") ? "Y" : "N";

		$bAdmin = $bSupportTeam = $bSupportClient = $bDemo = $bOwner = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
			$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
			$uid = intval($USER->GetID());
			if ($ID<=0)	$bOwner = "Y"; else $bOwner = CTicket::IsOwner($ID, $uid) ? "Y" : "N";
		}
		else
		{
			$bAdmin = $bSupportTeam = $bSupportClient = $bDemo = $bOwner = "Y";
			$uid = 0;
		}
		if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y") return false;

		$bActiveCoupon = false;
		if (array_key_exists('COUPON', $arFields) && strlen($arFields['COUPON']) > 0)
		{
			if ($ID > 0)
			{
				unset($arFields['COUPON']);
			}
			else
			{
				$bActiveCoupon = CSupportSuperCoupon::UseCoupon($arFields['COUPON']);
				if (!$bActiveCoupon)
				{
					$APPLICATION->ThrowException(GetMessage('SUP_ERROR_INVALID_COUPON'));
					return false;
				}
				else
				{
					$rsCoupon = CSupportSuperCoupon::GetList(false, array('COUPON' => $arFields['COUPON']));
					if ($arCoupon = $rsCoupon->Fetch())
					{
						$arCoupon['SLA_ID'] = intval($arCoupon['SLA_ID']);
						if ($arCoupon['SLA_ID'] > 0)
						{
							$arFields['SLA_ID'] = $arCoupon['SLA_ID'];
						}
					}
				}
			}
		}

		// получаем ID записей справочника
		if (strlen($arFields["SITE_ID"])>0) $SITE_ID = $arFields["SITE_ID"];
		elseif (strlen($arFields["SITE"])>0) $SITE_ID = $arFields["SITE"];
		elseif (strlen($arFields["LANG"])>0) $SITE_ID = $arFields["LANG"];  // совместимость со старой версией
		else $SITE_ID = SITE_ID;
		$arr = array(
			"CATEGORY"			=> "C",
			"CRITICALITY"		=> "K",
			"STATUS"			=> "S",
			"MARK"				=> "M",
			"SOURCE"			=> "SR",
			"MESSAGE_SOURCE"	=> "SR",
			"DIFFICULTY" => "D"
			);
		while (list($key,$value)=each($arr))
		{
			if (intval($arFields[$key."_ID"])<=0 && strlen($arFields[$key."_SID"])>0)
			{
				$z = CTicketDictionary::GetBySID($arFields[$key."_SID"], $value, $SITE_ID);
				$zr = $z->Fetch();
				$arFields[$key."_ID"] = $zr["ID"];
			}
		}

		// выбираем административные email'ы
		$arAdminEMails = CTicket::GetAdminEmails();
		if (count($arAdminEMails)>0) $support_admin_email = implode(",",$arAdminEMails);

		// если модифицируем обращение то
		if ($ID>0)
		{
			$arFields = CTicket::ExecuteEvents('OnBeforeTicketUpdate', $arFields, false);
			$close = ($arFields["CLOSE"]=="Y") ? $DB->GetNowFunction() : "null";

			// запоминаем предыдущие важные значения
			$arr = array(
				"RESPONSIBLE_USER_ID",
				"SLA_ID",
				"CATEGORY_ID",
				"CRITICALITY_ID",
				"STATUS_ID",
				"MARK_ID",
				"DIFFICULTY_ID",
				"DATE_CLOSE"
				);
			$str = "ID";
			foreach ($arr as $s) $str .= ",".$s;
			$strSql = "SELECT ".$str.", SITE_ID FROM b_ticket WHERE ID='$ID'";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			if ($zr=$z->Fetch())
			{
				$SITE_ID=$zr["SITE_ID"];
				if (intval($uid)==$zr["RESPONSIBLE_USER_ID"]) $bSupportTeam = "Y";
				foreach ($arr as $key) $arOldFields[$key] = $zr[$key];
			}

			/******************************************
				update админа либо ответственного
			******************************************/

			$MODIFIED_MODULE_NAME = strlen($arFields["MODIFIED_MODULE_NAME"])>0 ? $DB->ForSql($arFields["MODIFIED_MODULE_NAME"],255) : "support";
			$MODIFIED_GUEST_ID = intval($_SESSION["SESS_GUEST_ID"])>0 ? intval($_SESSION["SESS_GUEST_ID"]) : "null";
			$MODIFIED_USER_ID = intval($uid)>0	? intval($uid) : "null";
			$OWNER_USER_ID = intval($arFields["OWNER_USER_ID"])>0 ? intval($arFields["OWNER_USER_ID"]) : "null";
			$OWNER_SID = strlen($arFields["OWNER_SID"])>0 ? "'".$DB->ForSql($arFields["OWNER_SID"],255)."'" : "null";
			$SUPPORT_COMMENTS = strlen($arFields["SUPPORT_COMMENTS"])>0	? "'".$DB->ForSql($arFields["SUPPORT_COMMENTS"],2000)."'" : "null";
			$RESPONSIBLE_USER_ID = intval($arFields["RESPONSIBLE_USER_ID"])>0 ? intval($arFields["RESPONSIBLE_USER_ID"]) : "null";
			$HOLD_ON = ($arFields["HOLD_ON"]=="Y" ? "Y" : "N");

			$IS_GROUP_USER = 'N';
			if($bAdmin == 'Y')
			{
				$IS_GROUP_USER = 'Y';
			}
			elseif ($CHECK_RIGHTS == 'Y' && ($bSupportClient == 'Y' || $bSupportTeam == 'Y'))
			{
				if ($bSupportTeam == 'Y')
				{
					$join_query = '(T.RESPONSIBLE_USER_ID IS NOT NULL AND T.RESPONSIBLE_USER_ID=O.USER_ID)';
				}
				else//if ($bSupportClient == 'Y')
				{
					$join_query = '(T.OWNER_USER_ID IS NOT NULL AND T.OWNER_USER_ID=O.USER_ID)';
				}
				$strSql = "SELECT 'x'
				FROM b_ticket T
				INNER JOIN b_ticket_user_ugroup O ON $join_query
				INNER JOIN b_ticket_user_ugroup C ON (O.GROUP_ID=C.GROUP_ID)
				INNER JOIN b_ticket_ugroups G ON (O.GROUP_ID=G.ID)
				WHERE T.ID='$ID' AND C.USER_ID='$uid' AND C.CAN_VIEW_GROUP_MESSAGES='Y' AND G.IS_TEAM_GROUP='$bSupportTeam'";
				$z = $DB->Query($strSql);
				if ($zr = $z->Fetch())
				{
					$IS_GROUP_USER = 'Y';
				}
			}

			$arFields_i = array(
				"TIMESTAMP_X"			=> $DB->GetNowFunction(),
				"DATE_CLOSE"			=> $close,
				"SLA_ID"				=> intval($arFields["SLA_ID"]),
				"CATEGORY_ID"			=> intval($arFields["CATEGORY_ID"]),
				"STATUS_ID"				=> intval($arFields["STATUS_ID"]),
				"SOURCE_ID"				=> intval($arFields["SOURCE_ID"]),
				"DIFFICULTY_ID"				=> intval($arFields["DIFFICULTY_ID"]),
				"CRITICALITY_ID"		=> intval($arFields["CRITICALITY_ID"]),
				"OWNER_USER_ID"			=> $OWNER_USER_ID,
				"OWNER_SID"				=> $OWNER_SID,
				"RESPONSIBLE_USER_ID"	=> $RESPONSIBLE_USER_ID,
				"MODIFIED_USER_ID"		=> $MODIFIED_USER_ID,
				"HOLD_ON" => "'".$HOLD_ON."'",
				"MODIFIED_GUEST_ID"		=> $MODIFIED_GUEST_ID,
				"MODIFIED_MODULE_NAME"	=> "'".$MODIFIED_MODULE_NAME."'",
			    "SUPPORT_COMMENTS"		=> $SUPPORT_COMMENTS
				);
			if (
				intval($arFields["AUTO_CLOSE_DAYS"])>0 &&
				strlen($arFields["MESSAGE"])>0 &&
				$arFields["HIDDEN"]!="Y" && $arFields["NOT_CHANGE_STATUS"]!="Y"
				)
				$arFields_i["AUTO_CLOSE_DAYS"] = intval($arFields["AUTO_CLOSE_DAYS"]);
			elseif (intval($arFields["AUTO_CLOSE_DAYS"])==0)
				$arFields_i["AUTO_CLOSE_DAYS"] = "null";

			$arrUnset = array(
				"AUTO_CLOSE_DAYS",
				"SLA_ID",
				"CATEGORY_ID",
				"STATUS_ID",
				"SOURCE_ID",
				"DIFFICULTY_ID",
				"CRITICALITY_ID",
				"OWNER_USER_ID",
				"OWNER_SID",
				"RESPONSIBLE_USER_ID",
				"SUPPORT_COMMENTS"
				);
			while(list($key,$value)=each($arrUnset)) if (!isset($arFields[$value])) unset($arFields_i[$value]);
			if (!isset($arFields["CLOSE"])) unset($arFields_i["DATE_CLOSE"]);

			// сайт
			if (strlen($arFields["SITE_ID"])>0)
			{
				$arFields_i["SITE_ID"] = "'".$DB->ForSql($arFields["SITE_ID"],2)."'";
				$SITE_ID = $arFields["SITE_ID"];
			}
			elseif (strlen($arFields["LANG"])>0) // совместимость со старой версией
			{
				$arFields_i["SITE_ID"] = "'".$DB->ForSql($arFields["LANG"],2)."'";
				$SITE_ID = $arFields["LANG"];
			}

			if (is_array($arOldFields) && is_array($arFields) && $arFields["CLOSE"]=="N" && strlen($arOldFields["DATE_CLOSE"])>0)
				$arFields_i["REOPEN"] = "'Y'";

			if (is_array($arFields_i) && count($arFields_i)>0 && ($bSupportTeam=="Y" || $bAdmin=="Y"))
			{
				$rows1 = $DB->Update("b_ticket",$arFields_i,"WHERE ID='$ID'",$err_mess.__LINE__);
				// если указана отметка о спаме то
				if (strlen($arFields["IS_SPAM"])>0)
				{
					// установим отметку о спаме
					CTicket::MarkAsSpam($ID, $arFields["IS_SPAM"], $CHECK_RIGHTS);
				}
			}


			/******************************************
					update создателя запроса
			******************************************/

			$arFields_i = array(
				"TIMESTAMP_X"			=> $DB->GetNowFunction(),
				"DATE_CLOSE"			=> $close,
				"CRITICALITY_ID"		=> intval($arFields["CRITICALITY_ID"]),
				"MARK_ID"				=> intval($arFields["MARK_ID"]),
				"MODIFIED_USER_ID"		=> $MODIFIED_USER_ID,
				"MODIFIED_GUEST_ID"		=> $MODIFIED_GUEST_ID,
				"MODIFIED_MODULE_NAME"	=> "'".$MODIFIED_MODULE_NAME."'"
				);
			$arrUnset = array(
				"MARK_ID",
				"CRITICALITY_ID"
				);
			while(list($key,$value)=each($arrUnset))
			{
				if (!isset($arFields[$value])) unset($arFields_i[$value]);
			}
			if (!isset($arFields["CLOSE"])) unset($arFields_i["DATE_CLOSE"]);

			if (is_array($arOldFields) && is_array($arFields) && $arFields["CLOSE"]=="N" && strlen($arOldFields["DATE_CLOSE"])>0)
				$arFields_i["REOPEN"] = "'Y'";

			if (is_array($arFields_i) && count($arFields_i)>0 && ($bOwner=="Y" || $bSupportClient=="Y"))
			{
				$rows2 = $DB->Update("b_ticket",$arFields_i,"WHERE ID='$ID' and (OWNER_USER_ID='$uid' or CREATED_USER_ID='$uid' or '$CHECK_RIGHTS'='N' or '$IS_GROUP_USER'='Y')",$err_mess.__LINE__);
			}

			// поля для записи лога
			$arFields_log = array(
				"LOG"							=> "Y",
				"MESSAGE_CREATED_USER_ID"		=> $MODIFIED_USER_ID,
				"MESSAGE_CREATED_MODULE_NAME"	=> $MODIFIED_MODULE_NAME,
				"MESSAGE_CREATED_GUEST_ID"		=> $MODIFIED_GUEST_ID,
				"MESSAGE_SOURCE_ID"				=> intval($arFields["SOURCE_ID"])
				);

			// если необходимо соблюдать права то
			if ($CHECK_RIGHTS=="Y")
			{
				// если update техподдержки не прошел то
				if (intval($rows1)<=0)
				{
					// убираем из массива исходных значений то что может менять только техподдержка
					unset($arOldFields["RESPONSIBLE_USER_ID"]);
					unset($arOldFields["SLA_ID"]);
					unset($arOldFields["CATEGORY_ID"]);
					unset($arOldFields["DIFFICULTY_ID"]);
					unset($arOldFields["STATUS_ID"]);
				}
				// если update автора не прошел то
				if (intval($rows2)<=0)
				{
					// убираем из массива исходных значений то что может менять только автор
					unset($arOldFields["MARK_ID"]);
					//unset($arOldFields["CRITICALITY_ID"]);
				}
			}

			// если состоялся один из updat'ов то
			if (intval($rows1)>0 || intval($rows2)>0)
			{
				// добавляем сообщение
				$arFields["MESSAGE_CREATED_MODULE_NAME"] = $arFields["MODIFIED_MODULE_NAME"];
				if (is_set($arFields, "IMAGE")) $arFields["FILES"][] = $arFields["IMAGE"];
				$MID = CTicket::AddMessage($ID, $arFields, $arrFILES, $CHECK_RIGHTS);
				$MID = intval($MID);

				// если обращение закрывали то
				if (strlen($close)>0 && $close!="null")
				{
					// удалим агентов-напоминальщиков и обновим параметры обращения
					CTicketReminder::Remove($ID);

					//$DB->Update("b_ticket_message",Array("NOT_CHANGE_STATUS" => "'N'"),"WHERE TICKET_ID='".$ID."' AND NOT_CHANGE_STATUS = 'Y' ",$err_mess.__LINE__);
				}

				/**********************************
						отсылаем оповещение
				**********************************/

				if (is_array($arOldFields) && is_array($arFields))
				{
					// определяем что изменилось
					if ($MID>0)
					{
						if ($arFields["HIDDEN"]!="Y") $arChange["MESSAGE"] = "Y";
						else $arChange["HIDDEN_MESSAGE"] = "Y";
					}
					//echo "<pre>"; print_r($arOldFields); echo "</pre>";
					//echo "<pre>"; print_r($arFields); echo "</pre>";
					while (list($key,$value)=each($arOldFields))
					{
						if ($arFields["CLOSE"]=="Y" && strlen($arOldFields["DATE_CLOSE"])<=0)
							$arChange["CLOSE"]="Y";
						elseif ($arFields["CLOSE"]=="N" && strlen($arOldFields["DATE_CLOSE"])>0)
							$arChange["OPEN"]="Y";

						if (isset($arFields[$key]) && intval($value)!=intval($arFields[$key])) $arChange[$key]="Y";
					}

					// получим текущие значения обращения
					$z = CTicket::GetByID($ID, $SITE_ID, "N");
					if ($zr = $z->Fetch())
					{
						// сформируем список того что изменилось
						$rsSite = CSite::GetByID($zr["SITE_ID"]);
						$arSite = $rsSite->Fetch();
						$change = "";
						$change_log = "";
						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/messages.php", $arSite["LANGUAGE_ID"]);

						// сформируем ссылки на прикрепленые файлы
						if (is_array($arrFILES) && count($arrFILES)>0)
						{
							$FILES_LINKS = GetMessage("SUP_ATTACHED_FILES")."\n";
							foreach($arrFILES as $arFile)
							{
								$FILES_LINKS .= (CMain::IsHTTPS()? "https" : "http")."://".$_SERVER["HTTP_HOST"]."/bitrix/tools/ticket_show_file.php?hash=". $arFile["HASH"]."&action=download&lang=".$arSite["LANGUAGE_ID"]."\n";
							}
							if (strlen($FILES_LINKS)>0) $FILES_LINKS = "\n\n".$FILES_LINKS;
						}

						// инициализируем переменную с телом сообщения
						$MESSAGE = PrepareTxtForEmail($arFields["MESSAGE"], $arSite["LANGUAGE_ID"], false, false);
						if (strlen($MESSAGE)>0)
						{
							if (strlen($FILES_LINKS)>0) $MESSAGE = "\n".$MESSAGE."\n";
							else $MESSAGE = "\n\n".$MESSAGE."\n";
						}

						// сформируем email автора
						$arrOwnerEMail = array($zr["OWNER_EMAIL"]);
						$arrEmails = explode(",",$zr["OWNER_SID"]);
						if (is_array($arrEmails) && count($arrEmails)>0)
						{
							foreach($arrEmails as $email)
							{
								$email = trim($email);
								if (strlen($email)>0)
								{
									preg_match_all("#[<\[\(](.*?)[>\]\)]#i".BX_UTF_PCRE_MODIFIER, $email, $arr);
									if (is_array($arr[1]) && count($arr[1])>0)
									{
										foreach($arr[1] as $email)
										{
											$email = trim($email);
											if (strlen($email)>0 && !in_array($email, $arrOwnerEMail) && check_email($email))
											{
												$arrOwnerEMail[] = $email;
											}
										}
									}
									elseif (!in_array($email, $arrOwnerEMail) && check_email($email)) $arrOwnerEMail[] = $email;
								}
							}
						}
						TrimArr($arrOwnerEMail);
						$owner_email = implode(", ", $arrOwnerEMail);

						// сформируем email техподдержки
						$support_email = $zr["RESPONSIBLE_EMAIL"];
						if (strlen($support_email)<=0)
							$support_email = $support_admin_email;
						if (strlen($support_email)<=0)
							$support_email = COption::GetOptionString("main","email_from","");

						// из группы ответсвенного, выбираем всех пользователей кто имеет доступ к рассылке
						$rs = CTicket::GetResponsibleList($zr["RESPONSIBLE_USER_ID"]);
						while ($arr = $rs->Fetch())
							$support_email = $support_email.','.$arr['EMAIL'];
							
						// удалим продублированные адреса из макроса #SUPPORT_ADMIN_EMAIL#
						$arr = explode(",",$support_email);
						$arr = array_unique($arr);
						$support_email = implode(",",$arr);
						if (is_array($arr) && count($arr)>0)
						{
							foreach($arr as $email) unset($arAdminEMails[$email]);
						}
						$support_admin_email = implode(",",$arAdminEMails);

						if (array_key_exists('PUBLIC_EDIT_URL', $arFields) && strlen($arFields['PUBLIC_EDIT_URL']) > 0)
						{
							$public_edit_url = $arFields['PUBLIC_EDIT_URL'];
						}
						else
						{
							$public_edit_url = COption::GetOptionString("support", "SUPPORT_DIR");
							$public_edit_url = str_replace("#LANG_DIR#", $arSite["DIR"], $public_edit_url); // совместимость
							$public_edit_url = str_replace("#SITE_DIR#", $arSite["DIR"], $public_edit_url);
							$public_edit_url = str_replace("\\", "/", $public_edit_url);
							$public_edit_url = str_replace("//", "/", $public_edit_url);
							$public_edit_url = TrimEx($public_edit_url,"/");
							$public_edit_url = "/".$public_edit_url."/".COption::GetOptionString("support", "SUPPORT_EDIT");
						}

						$SUPPORT_COMMENTS = PrepareTxtForEmail($zr["SUPPORT_COMMENTS"], $arSite["LANGUAGE_ID"]);
						if (strlen($SUPPORT_COMMENTS)>0) $SUPPORT_COMMENTS = "\n\n".$SUPPORT_COMMENTS."\n";

						// #OWNER_TEXT# = #SOURCE##OWNER_SID##OWNER_TEXT#
						$SOURCE_NAME = strlen($zr["SOURCE_NAME"])<=0 ? "" : "[".$zr["SOURCE_NAME"]."] ";
						if (intval($zr["OWNER_USER_ID"])>0 || strlen(trim($zr["OWNER_LOGIN"]))>0)
						{
							$OWNER_TEXT = "[".$zr["OWNER_USER_ID"]."] (".$zr["OWNER_LOGIN"].") ".$zr["OWNER_NAME"];
							if (strlen(trim($zr["OWNER_SID"]))>0) $OWNER_TEXT = " / ".$OWNER_TEXT;
							if (intval($zr["OWNER_USER_ID"])>0)
							{
								if (CTicket::IsSupportTeam($zr["OWNER_USER_ID"]) || CTicket::IsAdmin($zr["OWNER_USER_ID"]))
								{
									$OWNER_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
								}
							}
						}

						// #CREATED_TEXT# = #CREATED_TEXT##CREATED_MODULE_NAME#
						if ($zr["CREATED_MODULE_NAME"]=="support" && strlen($zr["CREATED_MODULE_NAME"])>0)
						{
							$CREATED_MODULE_NAME = "";
							if (intval($zr["CREATED_USER_ID"])>0)
							{
								$CREATED_TEXT = "[".$zr["CREATED_USER_ID"]."] (".$zr["CREATED_LOGIN"].") ".$zr["CREATED_NAME"];
								if (CTicket::IsSupportTeam($zr["CREATED_USER_ID"]) || CTicket::IsAdmin($zr["CREATED_USER_ID"]))
								{
									$CREATED_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
								}
							}
						}
						else $CREATED_MODULE_NAME = "[".$zr["CREATED_MODULE_NAME"]."]";

						// #MODIFIED_TEXT# = #MODIFIED_TEXT##MODIFIED_MODULE_NAME#
						if ($zr["MODIFIED_MODULE_NAME"]=="support" && strlen($zr["MODIFIED_MODULE_NAME"])>0)
						{
							$MODIFIED_MODULE_NAME = "";
							if (intval($zr["MODIFIED_USER_ID"])>0)
							{
								$MODIFIED_TEXT = "[".$zr["MODIFIED_USER_ID"]."] (".$zr["MODIFIED_LOGIN"].") ".$zr["MODIFIED_NAME"];
								if (CTicket::IsSupportTeam($zr["MODIFIED_USER_ID"]) || CTicket::IsAdmin($zr["MODIFIED_USER_ID"]))
								{
									$MODIFIED_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
								}
							}
						}
						else $MODIFIED_MODULE_NAME = "[".$zr["MODIFIED_MODULE_NAME"]."]";

						// #MESSAGE_SOURCE##MESSAGE_AUTHOR_SID##MESSAGE_AUTHOR_TEXT#
						$arSource = array();
						if ($rsSource = CTicketDictionary::GetByID($arFields["MESSAGE_SOURCE_ID"]))
						{
							$arSource = $rsSource->Fetch();
						}
						$MESSAGE_SOURCE_NAME = strlen($arSource["NAME"])<=0 ? "" : "[".$arSource["NAME"]."] ";

						if ((strlen(trim($arFields["MESSAGE_AUTHOR_SID"]))>0 || intval($arFields["MESSAGE_AUTHOR_USER_ID"])>0) && $bSupportTeam=="Y")
						{
							$MESSAGE_AUTHOR_ID = intval($arFields["MESSAGE_AUTHOR_USER_ID"]);
							$MESSAGE_AUTHOR_SID = $arFields["MESSAGE_AUTHOR_SID"];
						}
						else
						{
							$MESSAGE_AUTHOR_ID = intval($uid);
							$MESSAGE_AUTHOR_SID = "null";
						}

						// #MESSAGE_AUTHOR_TEXT#
						$arMA = array();
						if ($rsMA = CUser::GetByID($MESSAGE_AUTHOR_ID))
						{
							$arMA = $rsMA->Fetch();
						}
						if (intval($MESSAGE_AUTHOR_ID)>0 || strlen(trim($arMA["LOGIN"]))>0)
						{
							$MESSAGE_AUTHOR_TEXT = "[".$MESSAGE_AUTHOR_ID."] (".$arMA["LOGIN"].") ".$arMA["NAME"]." ".$arMA["LAST_NAME"];
							if (strlen(trim($arFields["MESSAGE_AUTHOR_SID"]))>0)
							{
								$MESSAGE_AUTHOR_TEXT = " / ".$MESSAGE_AUTHOR_TEXT;
							}
							if (intval($MESSAGE_AUTHOR_ID)>0)
							{
								if (CTicket::IsSupportTeam($MESSAGE_AUTHOR_ID) || CTicket::IsAdmin($MESSAGE_AUTHOR_ID))
								{
									$MESSAGE_AUTHOR_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
								}
							}
						}

						// #RESPONSIBLE_TEXT#
						if (intval($zr["RESPONSIBLE_USER_ID"])>0)
						{
							$RESPONSIBLE_TEXT = "[".$zr["RESPONSIBLE_USER_ID"]."] (".$zr["RESPONSIBLE_LOGIN"].") ".$zr["RESPONSIBLE_NAME"];
							if (CTicket::IsSupportTeam($zr["RESPONSIBLE_USER_ID"]) || CTicket::IsAdmin($zr["RESPONSIBLE_USER_ID"]))
							{
								$RESPONSIBLE_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
							}
						}

						$spam_mark = "";
						if (strlen($zr["IS_SPAM"])>0)
						{
							if ($zr["IS_SPAM"]=="Y") $spam_mark = "\n".GetMessage("SUP_EXACTLY_SPAM")."\n";
							else $spam_mark = "\n".GetMessage("SUP_POSSIBLE_SPAM")."\n";
						}

						$line1 = str_repeat("=", 23);
						$line2 = str_repeat("=", 34);
						$MESSAGE_HEADER = $line1." ".GetMessage("SUP_MAIL_MESSAGE")." ".$line2;

						//echo "<pre>"; print_r($arChange); echo "</pre>";
						if (is_array($arChange) && count($arChange)>0)
						{
							while (list($key,$value)=each($arChange))
							{
								if ($value=="Y")
								{
									switch ($key)
									{
										case "CLOSE":
											$change .= GetMessage("SUP_REQUEST_CLOSED")."\n";
											$change_log .= "<li>".GetMessage("SUP_REQUEST_CLOSED_LOG");
											break;
										case "OPEN":
											$change .= GetMessage("SUP_REQUEST_OPENED")."\n";
											$change_log .= "<li>".GetMessage("SUP_REQUEST_OPENED_LOG");
											break;
										case "RESPONSIBLE_USER_ID":
											$change .= GetMessage("SUP_RESPONSIBLE_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $RESPONSIBLE_TEXT, GetMessage("SUP_RESPONSIBLE_CHANGED_LOG")));
											break;
										case "CATEGORY_ID":
											$change .= GetMessage("SUP_CATEGORY_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["CATEGORY_NAME"], GetMessage("SUP_CATEGORY_CHANGED_LOG")));
											break;
										case "CRITICALITY_ID":
											$change .= GetMessage("SUP_CRITICALITY_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["CRITICALITY_NAME"], GetMessage("SUP_CRITICALITY_CHANGED_LOG")));
											break;
										case "STATUS_ID":
											$change .= GetMessage("SUP_STATUS_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["STATUS_NAME"], GetMessage("SUP_STATUS_CHANGED_LOG")));
											break;

										case "DIFFICULTY_ID":
											$change_hidden .= GetMessage("SUP_DIFFICULTY_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["DIFFICULTY_NAME"], GetMessage("SUP_DIFFICULTY_CHANGED_LOG")));
											break;

										case "MARK_ID":
											$change .= GetMessage("SUP_MARK_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["MARK_NAME"], GetMessage("SUP_MARK_CHANGED_LOG")));
											break;
										case "SLA_ID":
											$change .= GetMessage("SUP_SLA_CHANGED")."\n";
											$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["SLA_NAME"], GetMessage("SUP_SLA_CHANGED_LOG")));
											break;
										case "MESSAGE":
											$change .= GetMessage("SUP_NEW_MESSAGE")."\n";
											break;
										case "HIDDEN_MESSAGE":
											$change_hidden .= GetMessage("SUP_NEW_HIDDEN_MESSAGE")."\n";
											$line1 = str_repeat("=", 20);
											$line2 = str_repeat("=", 30);
											$MESSAGE_HEADER = $line1." ".GetMessage("SUP_MAIL_HIDDEN_MESSAGE")." ".$line2;
											break;
									}
								}
							}
						}

						$MESSAGE_FOOTER = str_repeat("=", strlen($MESSAGE_HEADER));

						// запишем изменения в лог
						if (strlen($change_log)>0)
						{
							$arFields_log["MESSAGE"] = $change_log;
							//echo "<pre>"; print_r($arFields_log); echo "</pre>";
							CTicket::AddMessage($ID, $arFields_log, $v, "N");
						}

						if (
							(is_set($arChange, 'SLA_ID') && $arChange['SLA_ID'] == 'Y') ||
							(is_set($arChange, 'OPEN') && $arChange['OPEN'] == 'Y')
							)
						{
							CTicketReminder::Update($ID, true);
						}

						$arEventFields = array(
							"ID"						=> $ID,
							"LANGUAGE"					=> $arSite["LANGUAGE_ID"],
							"LANGUAGE_ID"				=> $arSite["LANGUAGE_ID"],
							"WHAT_CHANGE"				=> $change,
							"DATE_CREATE"				=> $zr["DATE_CREATE"],
							"TIMESTAMP"					=> $zr["TIMESTAMP_X"],
							"DATE_CLOSE"				=> $zr["DATE_CLOSE"],
							"TITLE"						=> $zr["TITLE"],
							"STATUS"					=> $zr["STATUS_NAME"],
							"DIFFICULTY"					=> $zr["DIFFICULTY_NAME"],
							"CATEGORY"					=> $zr["CATEGORY_NAME"],
							"CRITICALITY"				=> $zr["CRITICALITY_NAME"],
							"RATE"						=> $zr["MARK_NAME"],
							"SLA"						=> $zr["SLA_NAME"],
							"SOURCE"					=> $SOURCE_NAME,
							"MESSAGES_AMOUNT"			=> $zr["MESSAGES"],
							"SPAM_MARK"					=> $spam_mark,
							"ADMIN_EDIT_URL"			=> "/bitrix/admin/ticket_edit.php",
							"PUBLIC_EDIT_URL"			=> $public_edit_url,

							"OWNER_EMAIL"				=> TrimEx($owner_email,","),
							"OWNER_USER_ID"				=> $zr["OWNER_USER_ID"],
							"OWNER_USER_NAME"			=> $zr["OWNER_NAME"],
							"OWNER_USER_LOGIN"			=> $zr["OWNER_LOGIN"],
							"OWNER_USER_EMAIL"			=> $zr["OWNER_EMAIL"],
							"OWNER_TEXT"				=> $OWNER_TEXT,
							"OWNER_SID"					=> $zr["OWNER_SID"],

							"SUPPORT_EMAIL"				=> TrimEx($support_email,","),
							"RESPONSIBLE_USER_ID"		=> $zr["RESPONSIBLE_USER_ID"],
							"RESPONSIBLE_USER_NAME"		=> $zr["RESPONSIBLE_NAME"],
							"RESPONSIBLE_USER_LOGIN"	=> $zr["RESPONSIBLE_LOGIN"],
							"RESPONSIBLE_USER_EMAIL"	=> $zr["RESPONSIBLE_EMAIL"],
							"RESPONSIBLE_TEXT"			=> $RESPONSIBLE_TEXT,
							"SUPPORT_ADMIN_EMAIL"		=> TrimEx($support_admin_email,","),

							"CREATED_USER_ID"			=> $zr["CREATED_USER_ID"],
							"CREATED_USER_LOGIN"		=> $zr["CREATED_LOGIN"],
							"CREATED_USER_EMAIL"		=> $zr["CREATED_EMAIL"],
							"CREATED_USER_NAME"			=> $zr["CREATED_NAME"],
							"CREATED_MODULE_NAME"		=> $CREATED_MODULE_NAME,
							"CREATED_TEXT"				=> $CREATED_TEXT,

							"MODIFIED_USER_ID"			=> $zr["MODIFIED_USER_ID"],
							"MODIFIED_USER_LOGIN"		=> $zr["MODIFIED_LOGIN"],
							"MODIFIED_USER_EMAIL"		=> $zr["MODIFIED_EMAIL"],
							"MODIFIED_USER_NAME"		=> $zr["MODIFIED_NAME"],
							"MODIFIED_MODULE_NAME"		=> $MODIFIED_MODULE_NAME,
							"MODIFIED_TEXT"				=> $MODIFIED_TEXT,

							"MESSAGE_AUTHOR_USER_ID"	=> $MESSAGE_AUTHOR_ID,
							"MESSAGE_AUTHOR_USER_NAME"	=> $arMA["NAME"]." ".$arMA["LAST_NAME"],
							"MESSAGE_AUTHOR_USER_LOGIN"	=> $arMA["LOGIN"],
							"MESSAGE_AUTHOR_USER_EMAIL"	=> $arMA["EMAIL"],
							"MESSAGE_AUTHOR_TEXT"		=> $MESSAGE_AUTHOR_TEXT,
							"MESSAGE_AUTHOR_SID"		=> $arFields["MESSAGE_AUTHOR_SID"],
							"MESSAGE_SOURCE"			=> $MESSAGE_SOURCE_NAME,
							"MESSAGE_HEADER"			=> $MESSAGE_HEADER,
							"MESSAGE_BODY"				=> $MESSAGE,
							"MESSAGE_FOOTER"			=> $MESSAGE_FOOTER,
							"FILES_LINKS"				=> $FILES_LINKS,
							"IMAGE_LINK"				=> $FILES_LINKS,  // старый параметр

							"SUPPORT_COMMENTS"			=> $SUPPORT_COMMENTS
							);

						$arEventFields_support = $arEventFields;
						$arEventFields_author = $arEventFields;

						if ($SEND_EMAIL_TO_TECHSUPPORT=="Y" && (strlen($change)>0 || strlen($change_hidden)>0))
						{
							$arEventFields_support["WHAT_CHANGE"] .= $change_hidden;
							//echo "<pre>TICKET_CHANGE_FOR_TECHSUPPORT: "; print_r($arEventFields_support); echo "</pre>";
							$arEventFields_support = CTicket::ExecuteEvents('OnBeforeSendMailToSupport', $arEventFields_support, false);
							if($arEventFields_support)
								CEvent::Send("TICKET_CHANGE_FOR_TECHSUPPORT",$arSite["ID"],$arEventFields_support);
						}

						if ($SEND_EMAIL_TO_AUTHOR=="Y" && strlen($change)>0)
						{
							if ($arFields["HIDDEN"]=="Y" && (strlen($arEventFields_author["MESSAGE_BODY"])>0 ||
								strlen($arEventFields_author["IMAGE_LINK"])>0))
							{
								$arrUnsetHidden = array(
									"MESSAGE_BODY",
									"IMAGE_LINK"
									);
								foreach($arrUnsetHidden as $value) $arEventFields_author[$value]="";
							}
							$event_type = "TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR";
							if (CTicket::IsSupportTeam($MESSAGE_AUTHOR_ID) || CTicket::IsAdmin($MESSAGE_AUTHOR_ID))
							{
								$event_type = "TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR";
							}
							//echo "<pre>".$event_type.": "; print_r($arEventFields_author); echo "</pre>";
							$arEventFields_author = CTicket::ExecuteEvents('OnBeforeSendMailToAuthor', $arEventFields_author, false);
							if ($arEventFields_author)
								CEvent::Send($event_type,$arSite["ID"],$arEventFields_author);
						}
					}
				}
				CTicket::ExecuteEvents('OnAfterTicketUpdate', $arFields, false);
			}
		}
		else
		{
			$arFields = CTicket::ExecuteEvents('OnBeforeTicketAdd', $arFields, false);
			if ((strlen(trim($arFields["OWNER_SID"]))>0 || intval($arFields["OWNER_USER_ID"])>0) && ($bSupportTeam=="Y" || $bAdmin=="Y"))
			{
				$OWNER_USER_ID = intval($arFields["OWNER_USER_ID"])>0 ? intval($arFields["OWNER_USER_ID"]) : "null";
				$OWNER_SID = "'".$DB->ForSql($arFields["OWNER_SID"],2000)."'";
				$OWNER_GUEST_ID = "null";
			}
			else
			{
				$OWNER_USER_ID = intval($uid)>0 ? intval($uid) : "null";
				$OWNER_SID = "null";
				$OWNER_GUEST_ID = intval($_SESSION["SESS_GUEST_ID"])>0 ? intval($_SESSION["SESS_GUEST_ID"]) : "null";
			}

			$CREATED_MODULE_NAME = (strlen($arFields["CREATED_MODULE_NAME"])>0) ? $DB->ForSql($arFields["CREATED_MODULE_NAME"],255) : "support";

			$CATEGORY_ID		= (intval($arFields["CATEGORY_ID"])>0		? intval($arFields["CATEGORY_ID"])		: "null");
			$STATUS_ID			= (intval($arFields["STATUS_ID"])>0			? intval($arFields["STATUS_ID"])		: "null");
			$DIFFICULTY_ID			= (intval($arFields["DIFFICULTY_ID"])>0			? intval($arFields["DIFFICULTY_ID"])		: "null");
			$CRITICALITY_ID		= (intval($arFields["CRITICALITY_ID"])>0	? intval($arFields["CRITICALITY_ID"])	: "null");
			$SOURCE_ID			= (intval($arFields["SOURCE_ID"])>0			? intval($arFields["SOURCE_ID"])		: "null");
			$MODIFIED_GUEST_ID	= (intval($_SESSION["SESS_GUEST_ID"])>0		? intval($_SESSION["SESS_GUEST_ID"])	: "null");
			$CREATED_USER_ID	= (intval($uid)>0							? intval($uid)							: "null");
			$HOLD_ON = ($arFields["HOLD_ON"]=="Y" ? "Y" : "N");

			if (strlen($arFields["SITE_ID"])>0) $SITE_ID = $arFields["SITE_ID"];
			elseif (strlen($arFields["SITE"])>0) $SITE_ID = $arFields["SITE"];
			elseif (strlen($arFields["LANG"])>0) $SITE_ID = $arFields["LANG"];  // совместимость со старой версией
			else $SITE_ID = SITE_ID;

			// поля для записи обращения
			$arFields_i = array(
				"SITE_ID"				=> "'".$DB->ForSql($SITE_ID,2)."'",
				"DATE_CREATE"			=> $DB->GetNowFunction(),
				"LAST_MESSAGE_DATE"			=> $DB->GetNowFunction(),
				"DAY_CREATE"			=> $DB->CurrentDateFunction(),
				"TIMESTAMP_X"			=> $DB->GetNowFunction(),
				"TITLE"					=> "'".$DB->ForSql($arFields["TITLE"],255)."'",
				"CATEGORY_ID"			=> $CATEGORY_ID,
				"STATUS_ID"				=> $STATUS_ID,
				"DIFFICULTY_ID"				=> $DIFFICULTY_ID,
				"CRITICALITY_ID"		=> $CRITICALITY_ID,
				"SOURCE_ID"				=> $SOURCE_ID,
				"MESSAGES"				=> 0,
				"HOLD_ON" => "'".$HOLD_ON."'",
				"OWNER_USER_ID"			=> $OWNER_USER_ID,
				"OWNER_SID"				=> $OWNER_SID,
				"OWNER_GUEST_ID"		=> $OWNER_GUEST_ID,
				"MODIFIED_USER_ID"		=> $CREATED_USER_ID,
				"MODIFIED_GUEST_ID"		=> $MODIFIED_GUEST_ID,
				"MODIFIED_MODULE_NAME"	=> "'".$CREATED_MODULE_NAME."'",
				"CREATED_USER_ID"		=> $CREATED_USER_ID,
				"CREATED_MODULE_NAME"	=> "'".$CREATED_MODULE_NAME."'",
				"CREATED_GUEST_ID"		=> $MODIFIED_GUEST_ID
				);

			// поля для записи лога
			$arFields_log = array(
				"LOG"							=> "Y",
				"MESSAGE_CREATED_USER_ID"		=> $CREATED_USER_ID,
				"MESSAGE_CREATED_MODULE_NAME"	=> $CREATED_MODULE_NAME,
				"MESSAGE_CREATED_GUEST_ID"		=> $MODIFIED_GUEST_ID,
				"MESSAGE_SOURCE_ID"				=> $SOURCE_ID
				);

			// если обращение создается сотрудником техподдержки, администратором или демо пользователем
			if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
			{
				$arFields_i["SUPPORT_COMMENTS"] = (strlen($arFields["SUPPORT_COMMENTS"])>0) ? "'".$DB->ForSql($arFields["SUPPORT_COMMENTS"],2000)."'" : "null";

				// если прямо задан ответственный то
				if (intval($arFields["RESPONSIBLE_USER_ID"])>0)
				{
					// устанавливаем заданного ответственного
					$RESPONSIBLE_USER_ID = intval($arFields["RESPONSIBLE_USER_ID"]);
					$arFields_i["RESPONSIBLE_USER_ID"] = "'".$RESPONSIBLE_USER_ID."'";
				} else {
					unset($arFields["RESPONSIBLE_USER_ID"]);
				}
			}
			else unset($arFields["RESPONSIBLE_USER_ID"]);

			/*
			получим идентификаторы события и ответственного в зависимости от
				1) Категории
				2) Критичности
				3) Источника
			*/
			$strSql = "
				SELECT ID, C_TYPE, RESPONSIBLE_USER_ID, EVENT1, EVENT2, EVENT3
				FROM b_ticket_dictionary
				WHERE
					(ID='".intval($arFields["CATEGORY_ID"])."'		and C_TYPE='C') or
					(ID='".intval($arFields["CRITICALITY_ID"])."'	and C_TYPE='K') or
					(ID='".intval($arFields["SOURCE_ID"])."'		and C_TYPE='SR')
				ORDER BY
					C_TYPE
				";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch())
			{
				// если
				//    1) ответственный определен в справочнике
				//    2) до сих пор он не был определен
				//    3) не был задан явно пользователем имеющим на это права
				if (intval($zr["RESPONSIBLE_USER_ID"])>0 && intval($RESPONSIBLE_USER_ID)<=0 && !is_set($arFields,"RESPONSIBLE_USER_ID"))
				{
					$RESPONSIBLE_USER_ID = intval($zr["RESPONSIBLE_USER_ID"]);
					if (CTicket::IsSupportTeam($RESPONSIBLE_USER_ID) || CTicket::IsAdmin($RESPONSIBLE_USER_ID))
					{
						$arFields_i["RESPONSIBLE_USER_ID"] = "'".$RESPONSIBLE_USER_ID."'";
					}
				}
				if ($zr["C_TYPE"]=="C")
				{
					$T_EVENT1 = trim($zr["EVENT1"]);
					$T_EVENT2 = trim($zr["EVENT2"]);
					$T_EVENT3 = trim($zr["EVENT3"]);
					$category_set = "Y";
					break;
				}
			}

			// получаем SLA
			if (($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y" || $bActiveCoupon) && intval($arFields["SLA_ID"])>0)
			{
				$SLA_ID = intval($arFields["SLA_ID"]);
			}
			elseif (intval($arFields["SLA_ID"])<=0)
			{
				$SLA_ID = CTicketSLA::GetForUser($SITE_ID, $OWNER_USER_ID);
			}

			if (intval($SLA_ID)<=0) $SLA_ID = 1;
			$SLA_ID = intval($SLA_ID);
			$arFields_i["SLA_ID"] = $SLA_ID;

			// если ответственный явно не определен то
			if (!is_set($arFields,"RESPONSIBLE_USER_ID"))
			{
				// ответственный из настроек SLA
				if (intval($RESPONSIBLE_USER_ID)<=0)
				{
					$rsSLA = CTicketSLA::GetByID($SLA_ID);
					if ($arSLA = $rsSLA->Fetch())
					{
						if (intval($arSLA["RESPONSIBLE_USER_ID"])>0)
						{
							$RESPONSIBLE_USER_ID = $arSLA["RESPONSIBLE_USER_ID"];
							$arFields_i["RESPONSIBLE_USER_ID"] = "'".$RESPONSIBLE_USER_ID."'";
						}
					}
				}

				// ответственный из настроек модуля
				if (intval($RESPONSIBLE_USER_ID)<=0)
				{
					// берем из настроек модуля ответственного по умолчанию
					$RESPONSIBLE_USER_ID = intval(COption::GetOptionString("support", "DEFAULT_RESPONSIBLE_ID"));
					if ($RESPONSIBLE_USER_ID>0)
						$arFields_i["RESPONSIBLE_USER_ID"] = "'".$RESPONSIBLE_USER_ID."'";
				}
			}

			if ($bActiveCoupon)
			{
				$arFields_i['COUPON'] = "'" . $DB->ForSql($arFields['COUPON'], 255) . "'";
			}
			
			// создаем обращение
			$ID = $DB->Insert("b_ticket",$arFields_i, $err_mess.__LINE__);
			if ($ID>0)
			{
				$arFields["MESSAGE_AUTHOR_SID"] = $arFields["OWNER_SID"];
				$arFields["MESSAGE_AUTHOR_USER_ID"] = $arFields["OWNER_USER_ID"];
				$arFields["MESSAGE_CREATED_MODULE_NAME"] = $arFields["CREATED_MODULE_NAME"];
				$arFields["MESSAGE_SOURCE_ID"] = $arFields["SOURCE_ID"];
				$arFields["HIDDEN"] = "N";
				$arFields["LOG"] = "N";
				if (is_set($arFields, "IMAGE")) $arFields["FILES"][] = $arFields["IMAGE"];
				$MID = CTicket::AddMessage($ID, $arFields, $arrFILES, $CHECK_RIGHTS);
				$MID = intval($MID);
			
				if (intval($MID)>0)
				{
					// если указана отметка о спаме то
					if (strlen($arFields["IS_SPAM"])>0)
					{
						// установим отметку о спаме
						CTicket::MarkAsSpam($ID, $arFields["IS_SPAM"], $CHECK_RIGHTS);
					}

					$z = CTicket::GetByID($ID, $SITE_ID, "N");
					if ($zr = $z->Fetch())
					{
						$rsSite = CSite::GetByID($zr["SITE_ID"]);
						$arSite = $rsSite->Fetch();

						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/messages.php", $arSite["LANGUAGE_ID"]);

						// сформируем ссылки на прикрепленые файлы
						if (is_array($arrFILES) && count($arrFILES)>0)
						{
							$FILES_LINKS = GetMessage("SUP_ATTACHED_FILES")."\n";
							foreach($arrFILES as $arFile)
							{
								$FILES_LINKS .= (CMain::IsHTTPS()? "https" : "http")."://".$_SERVER["HTTP_HOST"]."/bitrix/tools/ticket_show_file.php?hash=". $arFile["HASH"]."&action=download&lang=".$arSite["LANGUAGE_ID"]."\n";
							}
							if (strlen($FILES_LINKS)) $FILES_LINKS .= "\n";
						}

						$MESSAGE = PrepareTxtForEmail($arFields["MESSAGE"], $arSite["LANGUAGE_ID"], false, false);

						// сформируем email автора
						$arrOwnerEMail = array($zr["OWNER_EMAIL"]);
						$arrEmails = explode(",",$zr["OWNER_SID"]);
						if (is_array($arrEmails) && count($arrEmails)>0)
						{
							foreach($arrEmails as $email)
							{
								$email = trim($email);
								if (strlen($email)>0)
								{
									preg_match_all("#[<\[\(](.*?)[>\]\)]#i".BX_UTF_PCRE_MODIFIER, $email, $arr);
									if (is_array($arr[1]) && count($arr[1])>0)
									{
										foreach($arr[1] as $email)
										{
											$email = trim($email);
											if (strlen($email)>0 && !in_array($email, $arrOwnerEMail) && check_email($email))
											{
												$arrOwnerEMail[] = $email;
											}
										}
									}
									elseif (!in_array($email, $arrOwnerEMail) && check_email($email)) $arrOwnerEMail[] = $email;
								}
							}
						}
						TrimArr($arrOwnerEMail);
						$owner_email = implode(", ", $arrOwnerEMail);

						// сформируем email техподдержки
						$support_email = $zr["RESPONSIBLE_EMAIL"];
						if (strlen($support_email)<=0)	$support_email = $support_admin_email;
						if (strlen($support_email)<=0)	$support_email = COption::GetOptionString("main","email_from","");

						// из группы ответсвенного, выбираем всех пользователей кто имеет доступ к рассылке
						$rs = CTicket::GetResponsibleList($zr["RESPONSIBLE_USER_ID"]);
						while ($arr = $rs->Fetch())
							$support_email = $support_email.','.$arr['EMAIL'];
							
						// удалим продублированные адреса из макроса #SUPPORT_ADMIN_EMAIL#
						$arr = explode(",",$support_email);
						$arr = array_unique($arr);
						$support_email = implode(",",$arr);
						if (is_array($arr) && count($arr)>0)
						{
							foreach($arr as $email) unset($arAdminEMails[$email]);
						}
						$support_admin_email = implode(",",$arAdminEMails);

						if (array_key_exists('PUBLIC_EDIT_URL', $arFields) && strlen($arFields['PUBLIC_EDIT_URL']) > 0)
						{
							$public_edit_url = $arFields['PUBLIC_EDIT_URL'];
						}
						else
						{
							$public_edit_url = COption::GetOptionString("support", "SUPPORT_DIR");
							$public_edit_url = str_replace("#LANG_DIR#", $arSite["DIR"], $public_edit_url); // совместимость
							$public_edit_url = str_replace("#SITE_DIR#", $arSite["DIR"], $public_edit_url);
							$public_edit_url = str_replace("\\", "/", $public_edit_url);
							$public_edit_url = str_replace("//", "/", $public_edit_url);
							$public_edit_url = TrimEx($public_edit_url,"/");
							$public_edit_url = "/".$public_edit_url."/".COption::GetOptionString("support", "SUPPORT_EDIT");
						}

						$SUPPORT_COMMENTS = PrepareTxtForEmail($arFields["SUPPORT_COMMENTS"], $arSite["LANGUAGE_ID"]);
						if (strlen($SUPPORT_COMMENTS)>0) $SUPPORT_COMMENTS = "\n\n".$SUPPORT_COMMENTS."\n";

						// #SOURCE##OWNER_SID##OWNER_TEXT#
						$SOURCE_NAME = strlen($zr["SOURCE_NAME"])<=0 ? "" : "[".$zr["SOURCE_NAME"]."] ";
						if (intval($zr["OWNER_USER_ID"])>0 || strlen(trim($zr["OWNER_LOGIN"]))>0)
						{
							$OWNER_TEXT = "[".$zr["OWNER_USER_ID"]."] (".$zr["OWNER_LOGIN"].") ".$zr["OWNER_NAME"];
							if (strlen(trim($OWNER_SID))>0 && $OWNER_SID!="null") $OWNER_TEXT = " / ".$OWNER_TEXT;
						}

						// #CREATED_TEXT##CREATED_MODULE_NAME#
						if ($zr["CREATED_MODULE_NAME"]=="support" && strlen($zr["CREATED_MODULE_NAME"])>0)
						{
							$CREATED_MODULE_NAME = "";
							$CREATED_TEXT = "[".$zr["CREATED_USER_ID"]."] (".$zr["CREATED_LOGIN"].") ".$zr["CREATED_NAME"];
						}
						else
						{
							$CREATED_MODULE_NAME = "[".$zr["CREATED_MODULE_NAME"]."]";
						}

						// #RESPONSIBLE_TEXT#
						if (intval($zr["RESPONSIBLE_USER_ID"])>0)
						{
							$RESPONSIBLE_TEXT = "[".$zr["RESPONSIBLE_USER_ID"]."] (".$zr["RESPONSIBLE_LOGIN"].") ".$zr["RESPONSIBLE_NAME"];
						}

						$change_log = "";

						$spam_mark = "";
						if (strlen($zr["IS_SPAM"])>0)
						{
							if ($zr["IS_SPAM"]=="Y") $spam_mark = "\n".GetMessage("SUP_EXACTLY_SPAM")."\n";
							else $spam_mark = "\n".GetMessage("SUP_POSSIBLE_SPAM")."\n";
						}

						if (strlen($zr["SLA_NAME"])>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["SLA_NAME"], GetMessage("SUP_SLA_CHANGED_LOG")));
						}
						if (strlen($zr["CATEGORY_NAME"])>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["CATEGORY_NAME"], GetMessage("SUP_CATEGORY_CHANGED_LOG")));
						}
						if (strlen($zr["CRITICALITY_NAME"])>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["CRITICALITY_NAME"], GetMessage("SUP_CRITICALITY_CHANGED_LOG")));
						}
						if (strlen($zr["STATUS_NAME"])>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["STATUS_NAME"], GetMessage("SUP_STATUS_CHANGED_LOG")));
						}

						if (strlen($zr["DIFFICULTY_NAME"])>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $zr["DIFFICULTY_NAME"], GetMessage("SUP_DIFFICULTY_CHANGED_LOG")));
						}

						if (strlen($RESPONSIBLE_TEXT)>0)
						{
							$change_log .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $RESPONSIBLE_TEXT, GetMessage("SUP_RESPONSIBLE_CHANGED_LOG")));
						}

						if ($bActiveCoupon)
						{
							$change_log .= "<li>".htmlspecialcharsEx(GetMessage('SUP_IS_SUPER_COUPON', array('#COUPON#' => $arFields['COUPON'])));
						}

						// запишем изменения в лог
						if (strlen($change_log)>0)
						{
							$arFields_log["MESSAGE"] = $change_log;
							//echo "<pre>"; print_r($arFields_log); echo "</pre>";
							CTicket::AddMessage($ID, $arFields_log, $v, "N");
						}

						$arEventFields = array(
							"ID"						=> $ID,
							"LANGUAGE"					=> $arSite["LANGUAGE_ID"],
							"LANGUAGE_ID"				=> $arSite["LANGUAGE_ID"],
							"DATE_CREATE"				=> $zr["DATE_CREATE"],
							"TIMESTAMP"					=> $zr["TIMESTAMP_X"],
							"DATE_CLOSE"				=> $zr["DATE_CLOSE"],
							"TITLE"						=> $zr["TITLE"],
							"CATEGORY"					=> $zr["CATEGORY_NAME"],
							"CRITICALITY"				=> $zr["CRITICALITY_NAME"],
							"DIFFICULTY"				=> $zr["DIFFICULTY_NAME"],
							"STATUS"					=> $zr["STATUS_NAME"],
							"SLA"						=> $zr["SLA_NAME"],
							"SOURCE"					=> $SOURCE_NAME,
							"SPAM_MARK"					=> $spam_mark,
							"MESSAGE_BODY"				=> $MESSAGE,
							"FILES_LINKS"				=> $FILES_LINKS,
							"IMAGE_LINK"				=> $FILES_LINKS, // старый параметр
							"ADMIN_EDIT_URL"			=> "/bitrix/admin/ticket_edit.php",
							"PUBLIC_EDIT_URL"			=> $public_edit_url,

							"OWNER_EMAIL"				=> TrimEx($owner_email,","),
							"OWNER_USER_ID"				=> $zr["OWNER_USER_ID"],
							"OWNER_USER_NAME"			=> $zr["OWNER_NAME"],
							"OWNER_USER_LOGIN"			=> $zr["OWNER_LOGIN"],
							"OWNER_USER_EMAIL"			=> $zr["OWNER_EMAIL"],
							"OWNER_TEXT"				=> $OWNER_TEXT,
							"OWNER_SID"					=> $zr["OWNER_SID"],

							"SUPPORT_EMAIL"				=> TrimEx($support_email,","),
							"RESPONSIBLE_USER_ID"		=> $zr["RESPONSIBLE_USER_ID"],
							"RESPONSIBLE_USER_NAME"		=> $zr["RESPONSIBLE_NAME"],
							"RESPONSIBLE_USER_LOGIN"	=> $zr["RESPONSIBLE_LOGIN"],
							"RESPONSIBLE_USER_EMAIL"	=> $zr["RESPONSIBLE_EMAIL"],
							"RESPONSIBLE_TEXT"			=> $RESPONSIBLE_TEXT,
							"SUPPORT_ADMIN_EMAIL"		=> TrimEx($support_admin_email,","),

							"CREATED_USER_ID"			=> $zr["CREATED_USER_ID"],
							"CREATED_USER_LOGIN"		=> $zr["CREATED_LOGIN"],
							"CREATED_USER_EMAIL"		=> $zr["CREATED_EMAIL"],
							"CREATED_USER_NAME"			=> $zr["CREATED_NAME"],
							"CREATED_MODULE_NAME"		=> $CREATED_MODULE_NAME,
							"CREATED_TEXT"				=> $CREATED_TEXT,

							"SUPPORT_COMMENTS"			=> $SUPPORT_COMMENTS
							);
						if ($bActiveCoupon)
						{
							$arEventFields['COUPON'] = $arFields['COUPON'];
						}

						// отсылаем письмо автору
						if ($SEND_EMAIL_TO_AUTHOR=="Y")
						{
							//echo "TICKET_NEW_FOR_AUTHOR:<pre>"; echo htmlspecialcharsEx(print_r($arEventFields,true));echo "</pre>";
							$arEventFields_author = CTicket::ExecuteEvents('OnBeforeSendMailToAuthor', $arEventFields, true);
							if ($arEventFields_author)
								CEvent::Send("TICKET_NEW_FOR_AUTHOR", $arSite["ID"], $arEventFields_author);
						}

						// отсылаем письмо техподдержке
						if ($SEND_EMAIL_TO_TECHSUPPORT=="Y")
						{
							//echo "TICKET_NEW_FOR_TECHSUPPORT:<pre>"; echo htmlspecialcharsEx(print_r($arEventFields,true));echo "</pre>";
							$arEventFields_support = CTicket::ExecuteEvents('OnBeforeSendMailToSupport', $arEventFields, true);
							if ($arEventFields_support)
								CEvent::Send("TICKET_NEW_FOR_TECHSUPPORT", $arSite["ID"], $arEventFields_support);
						}

						// создаем событие в модуле статистики
						if (CModule::IncludeModule("statistic"))
						{
							if ($category_set!="Y")
							{
								$T_EVENT1 = "ticket";
								$T_EVENT2 = "";
							}
							if (strlen($T_EVENT3)<=0)
								$T_EVENT3 = "http://".$_SERVER["HTTP_HOST"]."/bitrix/admin/ticket_edit.php?ID=".$ID. "&lang=".$arSite["LANGUAGE_ID"];
							CStatEvent::AddCurrent($T_EVENT1, $T_EVENT2, $T_EVENT3);
						}
					}
				}
				$arFields['ID'] = $ID;
				CTicket::ExecuteEvents('OnAfterTicketAdd', $arFields, true);
			}
		}
		return $ID;
	}

	/***********************************************
			Старые функции для совместимости
	***********************************************/

	function GetFUA($site_id)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: GetFUA<br>Line: ";
		global $DB;
		if ($site_id=="all") $site_id = "";
		$arFilter = array("TYPE" => "F", "SITE" => $site_id);
		$rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);
		return $rs;
	}

	function GetRefBookValues($type, $site_id=false)
	{
		$err_mess = (CAllTicket::err_mess())."<br>Function: GetRefBookValues<br>Line: ";
		global $DB;
		if ($site_id=="all") $site_id = "";
		$arFilter = array("TYPE" => $type, "SITE" => $site_id);
		$rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);
		return $rs;
	}

	function GetMessages($TICKET_ID, $arFilter=array(), $CHECK_RIGHTS="Y")
	{
		$arFilter["TICKET_ID"] = $TICKET_ID;
		$arFilter["TICKET_ID_EXACT_MATCH"] = "Y";
		return CTicket::GetMessageList($by, $order, $arFilter, $is_filtered, $CHECK_RIGHTS, "Y");
	}

	function GetResponsible()
	{
		return CTicket::GetSupportTeamList();
	}

	function IsResponsible($USER_ID=false)
	{
		return CTicket::IsSupportTeam($USER_ID);
	}

	function ExecuteEvents($message, $arFields, $is_new)
	{
		$rs = GetModuleEvents('support', $message);
		while ($arr = $rs->Fetch())
		{
			$arFields = ExecuteModuleEventEx($arr, array($arFields, $is_new));
		}

		return $arFields;
	}
}

class CAllTicketDictionary
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllTicketDictionary<br>File: ".__FILE__;
	}

	function GetDefault($type, $site_id=SITE_ID)
	{
		if ($site_id=="all") $site_id = "";
		$arFilter = array("DEFAULT" => "Y", "TYPE" => $type, "SITE" => $site_id);
		$rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);
		$ar = $rs->Fetch();
		return $ar["ID"];
	}

	function GetNextSort($TYPE_ID)
	{
		global $DB;
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetNextSort<br>Line: ";
		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_ticket_dictionary WHERE C_TYPE='".$DB->ForSql($TYPE_ID,5)."'";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	function GetDropDown($type="C", $site_id=false, $sla_id=false)
	{
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetDropDown<br>Line: ";
		global $DB;
		if ($site_id==false || $site_id=="all") $site_id = "";
		$arFilter = array("TYPE" => $type, "SITE" => $site_id);
		$rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);
		if (intval($sla_id)>0 && ($type=="C" || $type=="K" || $type=="M"))
		{
			$sla_id = intval($sla_id);
			switch($type)
			{
				case "C": $strSql = "SELECT CATEGORY_ID as DID FROM b_ticket_sla_2_category WHERE SLA_ID=$sla_id"; break;
				case "K": $strSql = "SELECT CRITICALITY_ID as DID FROM b_ticket_sla_2_criticality WHERE SLA_ID=$sla_id"; break;
				case "M": $strSql = "SELECT MARK_ID as DID FROM b_ticket_sla_2_mark WHERE SLA_ID=$sla_id"; break;
			}
			$r = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($a = $r->Fetch()) $arDID[] = $a["DID"];
			$arRecords = array();
			while($ar = $rs->Fetch()) if (is_array($arDID) && (in_array($ar["ID"], $arDID) || in_array(0,$arDID))) $arRecords[] = $ar;

			$rs = new CDBResult;
			$rs->InitFromArray($arRecords);
		}
		return $rs;
	}


	function GetDropDownArray($SITE_ID = false, $SLA_ID = false, $arUnsetType = Array("F"))
	{
		//M, C, K, S, SR, D, F
		global $DB;

		if ($SITE_ID == false || $SITE_ID == "all")
			$SITE_ID = "";

		$arFilter = Array("SITE" => $SITE_ID);

		$arReturn = Array();
		$rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);
		while ($ar = $rs->Fetch())
		{
			if (in_array($ar["C_TYPE"], $arUnsetType))
				continue;

			$arReturn[$ar["C_TYPE"]][$ar["ID"]] = $ar;
		}

		if (intval($SLA_ID)>0)
		{
			$SLA_ID = intval($SLA_ID);

			$strSql = "SELECT 'M' as C_TYPE, SLA_ID, MARK_ID as DIC_ID FROM b_ticket_sla_2_mark WHERE SLA_ID = ".$SLA_ID."
						UNION ALL
						SELECT 'K' as C_TYPE, SLA_ID, CRITICALITY_ID as DIC_ID FROM b_ticket_sla_2_criticality WHERE SLA_ID = ".$SLA_ID."
						UNION ALL
						SELECT 'C' as C_TYPE, SLA_ID, CATEGORY_ID as DIC_ID FROM b_ticket_sla_2_category WHERE SLA_ID = ".$SLA_ID;

			$r = $DB->Query($strSql, false, $err_mess.__LINE__);

			$arUnset = Array();
			while ($ar = $r->Fetch())
			{
				if ($ar["DIC_ID"] == 0)
					continue;
				else
					$arUnset[$ar["C_TYPE"]][] = $ar["DIC_ID"];
			}

			if (!empty($arUnset) && !empty($arReturn))
			{
				foreach ($arReturn as $type => $arID)
				{
					if (!array_key_exists($type, $arUnset))
						continue;

					$arID = array_keys($arID);
					$arID = array_diff($arID, $arUnset[$type]);
					foreach ($arID as $val)
						unset($arReturn[$type][$val]);
				}
			}
		}

		

		return $arReturn;

	}

	// получаем массив языков связанных с контрактом
	function GetSiteArray($DICTIONARY_ID)
	{
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB;
		$DICTIONARY_ID = intval($DICTIONARY_ID);
		if ($DICTIONARY_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT
				DS.SITE_ID
			FROM
				b_ticket_dictionary_2_site DS
			WHERE
				DS.DICTIONARY_ID = $DICTIONARY_ID
			";
		//echo "<pre>".$strSql."</pre>";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["SITE_ID"];
		return $arrRes;
	}

	function GetTypeList()
	{
		$arr = array(
			"reference"=>array(
				GetMessage("SUP_CATEGORY"),
				GetMessage("SUP_CRITICALITY"),
				GetMessage("SUP_STATUS"),
				GetMessage("SUP_MARK"),
				GetMessage("SUP_FUA"),
				GetMessage("SUP_SOURCE"),
				GetMessage("SUP_DIFFICULTY")
				),
			"reference_id"=>array(
				"C",
				"K",
				"S",
				"M",
				"F",
				"SR",
				"D")
			);
		return $arr;
	}

	function GetTypeNameByID($ID)
	{
		$arr = CTicketDictionary::GetTypeList();
		$KEY = array_search($ID, $arr["reference_id"]);
		return $arr["reference"][$KEY];
	}

	function GetByID($ID)
	{
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetByID<br>Line: ";
		global $DB;
		$ID = intval($ID);
		if ($ID<=0) return;
		$res = CTicketDictionary::GetList($by, $order, array("ID" => $ID), $is_filtered);
		return $res;
	}

	function GetBySID($sid, $type, $site_id=SITE_ID)
	{
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetBySID<br>Line: ";
		global $DB;
		$rs = CTicketDictionary::GetList($v1, $v2, array("SITE_ID"=>$site_id, "TYPE"=>$type, "SID"=>$sid), $v3);
		return $rs;
	}

	function Delete($ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllTicketDictionary::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $APPLICATION;
		$ID = intval($ID);
		if ($ID<=0) return;
		$bAdmin = "N";
		if ($CHECK_RIGHTS=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
		}
		if ($bAdmin=="Y")
		{
			$DB->Query("DELETE FROM b_ticket_dictionary WHERE ID='$ID'", false, $err_mess.__LINE__);
			$DB->Query('DELETE FROM b_ticket_dictionary_2_site WHERE DICTIONARY_ID=' . $ID, false, $err_mess.__LINE__);
		}
	}

	function CheckFields($arFields, $ID = false)
	{
		$arMsg = Array();

		if ( $ID ===false && !(array_key_exists('NAME', $arFields) && strlen($arFields['NAME']) > 0) )
		{
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("SUP_FORGOT_NAME"));
		}

		if ($ID !== false)
		{
			$rs = CTicketDictionary::GetByID($ID);
			if (!$rs->Fetch())
			{
				$arMsg[] = array("id"=>"ID", "text"=> GetMessage("SUP_UNKNOWN_ID", array('#ID#' => $ID)));
			}
		}

		if ( array_key_exists('SID', $arFields) && preg_match("#[^A-Za-z_0-9]#", $arFields['SID']) )
		{
			$arMsg[] = array("id"=>"SID", "text"=> GetMessage("SUP_INCORRECT_SID"));
		}
		elseif (
				strlen($arFields['SID']) > 0 && array_key_exists('arrSITE', $arFields) &&
				is_array($arFields['arrSITE']) && count($arFields['arrSITE']) > 0
			)
		{
			$arFilter = array(
				"TYPE"	=> $arFields['C_TYPE'],
				"SID"	=> $arFields['SID'],
				"SITE"	=> $arFields['arrSITE'],
			);
			if (intval($ID) > 0)
			{
				$arFilter['ID'] = '~'.intval($ID);
			}
			
			$z = CTicketDictionary::GetList($v1, $v2, $arFilter, $v3);
			if ($zr = $z->Fetch())
			{
				$arMsg[] = array(
							"id"=>"SID",
							"text"=> GetMessage(
									'SUP_SID_ALREADY_IN_USE',
									array(
										'#TYPE#' => CTicketDictionary::GetTypeNameByID($arFields['C_TYPE']),
										'#LANG#' => strlen($zr['LID']) > 0? $zr['LID']: $zr['SITE_ID'],
										'#RECORD_ID#' => $zr['ID'],
									)
							)
					);
			}
		}

		if (count($arMsg) > 0)
		{
			$e = new CAdminException($arMsg);
			$GLOBALS['APPLICATION']->ThrowException($e);
			return false;
		}

		return true;
	}

	function Add($arFields)
	{
		global $DB;
		$DB->StartTransaction();
		if (!CTicketDictionary::CheckFields($arFields))
		{
			return false;
		}

		CTicketDictionary::__CleanDefault($arFields);

		$ID = intval($DB->Add('b_ticket_dictionary', $arFields));
		if ($ID > 0)
		{
			CTicketDictionary::__SetSites($ID, $arFields);
			$DB->Commit();
			return $ID;
		}

		$DB->Rollback();
		$GLOBALS['APPLICATION']->ThrowException(GetMessage('SUP_ERROR_ADD_DICTONARY'));
		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$DB->StartTransaction();
		$ID = intval($ID);
		if (!CTicketDictionary::CheckFields($arFields, $ID))
		{
			return false;
		}

		CTicketDictionary::__CleanDefault($arFields);

		$strUpdate = $DB->PrepareUpdate('b_ticket_dictionary', $arFields);
		$rs = $DB->Query('UPDATE b_ticket_dictionary SET ' . $strUpdate . ' WHERE ID=' . $ID);
		if ($rs->AffectedRowsCount() > 0);
		{
			CTicketDictionary::__SetSites($ID, $arFields);
			$DB->Commit();
			return true;
		}

		$DB->Rollback();
		$GLOBALS['APPLICATION']->ThrowException(GetMessage('SUP_ERROR_UPDATE_DICTONARY'));
		return false;
	}

	function __CleanDefault(&$arFields)
	{
		if (
				array_key_exists('SET_AS_DEFAULT', $arFields) && $arFields['SET_AS_DEFAULT'] == 'Y' &&
				array_key_exists('arrSITE', $arFields) && array_key_exists('C_TYPE',  $arFields)
			)
		{
			global $DB;
			$arFilter = array(
				'TYPE'	=> $arFields['C_TYPE'],
				'SITE'	=> $arFields['arrSITE']
				);
			$z = CTicketDictionary::GetList($v1, $v2, $arFilter, $v3);
			while ($zr = $z->Fetch())
			{
				$DB->Update('b_ticket_dictionary', array('SET_AS_DEFAULT' => "'N'"), 'WHERE ID=' . $zr['ID'], '', false, false, false);
			}
		}
		else
		{
			$arFields['SET_AS_DEFAULT'] = 'N';
		}
	}

	function __SetSites($ID, $arFields)
	{
		global $DB;
		if (!array_key_exists('arrSITE', $arFields))
		{
			return ;
		}
		$ID = intval($ID);
		$DB->Query('DELETE FROM b_ticket_dictionary_2_site WHERE DICTIONARY_ID=' . $ID);
		if (is_array($arFields['arrSITE']) && count($arFields['arrSITE']) > 0)
		{
			foreach($arFields['arrSITE'] as $sid)
			{
				$strSql = "INSERT INTO b_ticket_dictionary_2_site (DICTIONARY_ID, SITE_ID) VALUES ($ID, '".$DB->ForSql($sid, 2)."')";
				$DB->Query($strSql);
			}
		}
	}
}

class CAllTicketSLA
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllTicketSLA<br>File: ".__FILE__;
	}

	// добавляет новый SLA или модифицируем существующий
	function Set($arFields, $ID, $CHECK_RIGHTS=true)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$ID = intval($ID);
		$table = "b_ticket_sla";
		CTicket::GetRoles($isDemo, $isSupportClient, $isSupportTeam, $isAdmin, $isAccess, $USER_ID, $CHECK_RIGHTS);
		if ($isAdmin)
		{
			if (CTicket::CheckFields($arFields, $ID, array("NAME")))
			{
				//echo "arFields:<pre>"; print_r($arFields); echo "</pre>";
				$arFields_i = CTicket::PrepareFields($arFields, $table, $ID);
				//echo "arFields_i:<pre>"; print_r($arFields_i); echo "</pre>";
				if (intval($ID)>0) $DB->Update($table, $arFields_i, "WHERE ID=".intval($ID), $err_mess.__LINE__);
				else $ID = $DB->Insert($table, $arFields_i, $err_mess.__LINE__);

				if (intval($ID)>0)
				{
					if (is_set($arFields, "arGROUPS"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_user_group WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arGROUPS"]) && count($arFields["arGROUPS"])>0)
						{
							foreach($arFields["arGROUPS"] as $GROUP_ID)
							{
								$GROUP_ID = intval($GROUP_ID);
								if ($GROUP_ID>0)
								{
									$strSql = "INSERT INTO b_ticket_sla_2_user_group (SLA_ID, GROUP_ID) VALUES ($ID, $GROUP_ID)";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					if (is_set($arFields, "arSITES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_site WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arSITES"]) && count($arFields["arSITES"])>0)
						{
							foreach($arFields["arSITES"] as $SITE_ID)
							{
								if (strlen($FIRST_SITE_ID)<=0) $FIRST_SITE_ID = $SITE_ID;
								$SITE_ID = $DB->ForSql($SITE_ID);
								$strSql = "INSERT INTO b_ticket_sla_2_site (SLA_ID, SITE_ID) VALUES ($ID, '$SITE_ID')";
								$DB->Query($strSql, false, $err_mess.__LINE__);
							}
						}
					}

					if (is_set($arFields, "arCATEGORIES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_category WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arCATEGORIES"]) && count($arFields["arCATEGORIES"])>0)
						{
							foreach($arFields["arCATEGORIES"] as $CATEGORY_ID)
							{
								$CATEGORY_ID = intval($CATEGORY_ID);
								$strSql = "INSERT INTO b_ticket_sla_2_category (SLA_ID, CATEGORY_ID) VALUES ($ID, $CATEGORY_ID)";
								$DB->Query($strSql, false, $err_mess.__LINE__);
							}
						}
					}

					if (is_set($arFields, "arCRITICALITIES"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_criticality WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arCRITICALITIES"]) && count($arFields["arCRITICALITIES"])>0)
						{
							foreach($arFields["arCRITICALITIES"] as $CRITICALITY_ID)
							{
								$CRITICALITY_ID = intval($CRITICALITY_ID);
								$strSql = "INSERT INTO b_ticket_sla_2_criticality (SLA_ID, CRITICALITY_ID) VALUES ($ID, $CRITICALITY_ID)";
								$DB->Query($strSql, false, $err_mess.__LINE__);
							}
						}
					}

					if (is_set($arFields, "arMARKS"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_2_mark WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arMARKS"]) && count($arFields["arMARKS"])>0)
						{
							foreach($arFields["arMARKS"] as $MARK_ID)
							{
								$MARK_ID = intval($MARK_ID);
								$strSql = "INSERT INTO b_ticket_sla_2_mark (SLA_ID, MARK_ID) VALUES ($ID, $MARK_ID)";
								$DB->Query($strSql, false, $err_mess.__LINE__);
							}
						}
					}

					if (is_set($arFields, "arSHEDULE"))
					{
						$DB->Query("DELETE FROM b_ticket_sla_shedule WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
						if (is_array($arFields["arSHEDULE"]) && count($arFields["arSHEDULE"])>0)
						{
							while(list($weekday, $arSHEDULE) = each($arFields["arSHEDULE"]))
							{
								$arF = array(
									"SLA_ID"			=> $ID,
									"WEEKDAY_NUMBER"	=> intval($weekday),
									"OPEN_TIME"			=> "'".$DB->ForSql($arSHEDULE["OPEN_TIME"], 10)."'",
									);
								if ($arSHEDULE["OPEN_TIME"]=="CUSTOM" && is_array($arSHEDULE["CUSTOM_TIME"]) && count($arSHEDULE["CUSTOM_TIME"])>0)
								{
									foreach($arSHEDULE["CUSTOM_TIME"] as $ar)
									{
										if (strlen(trim($ar["MINUTE_FROM"]))>0 || strlen(trim($ar["MINUTE_TILL"]))>0)
										{
											$minute_from = strlen($ar["MINUTE_FROM"])>0 ? $ar["MINUTE_FROM"] : "00:00";
											$a = explode(":",$minute_from);
											$minute_from = intval($a[0]*60 + $a[1]);
											$arF["MINUTE_FROM"] = $minute_from;

											$minute_till = strlen($ar["MINUTE_TILL"])>0 ? $ar["MINUTE_TILL"] : "23:59";
											$a = explode(":",$minute_till);
											$minute_till = intval($a[0]*60 + $a[1]);
											$arF["MINUTE_TILL"] = $minute_till;

											$DB->Insert("b_ticket_sla_shedule", $arF, $err_mess.__LINE__);
										}
									}
								}
								else $DB->Insert("b_ticket_sla_shedule", $arF, $err_mess.__LINE__);
							}
						}
					}

					$FIRST_SITE_ID = strlen($FIRST_SITE_ID)>0 ? "'".$DB->ForSql($FIRST_SITE_ID)."'" : "null";
					$DB->Update($table, array("FIRST_SITE_ID" => $FIRST_SITE_ID), "WHERE ID=".intval($ID), $err_mess.__LINE__);
				}
			}
		}
		else
		{
			//$APPLICATION->ThrowException(GetMessage("SUP_ERROR_ACCESS_DENIED"));
			$arMsg = Array();
			$arMsg[] = array("id"=>"PERMISSION", "text"=> GetMessage("SUP_ERROR_ACCESS_DENIED"));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
		}
		return $ID;
	}

	// удаляет SLA
	function Delete($ID, $CHECK_RIGHTS=true)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$ID = intval($ID);
		if ($ID<=1) return false;
		CTicket::GetRoles($isDemo, $isSupportClient, $isSupportTeam, $isAdmin, $isAccess, $USER_ID, $CHECK_RIGHTS);
		if ($isAdmin)
		{
			$strSql = "SELECT DISTINCT 'x' FROM b_ticket WHERE SLA_ID = $ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			if (!$rs->Fetch())
			{
				$DB->Query("DELETE FROM b_ticket_sla_2_site WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla_2_category WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla_2_criticality WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla_2_mark WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla_2_user_group WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla_shedule WHERE SLA_ID = $ID", false, $err_mess.__LINE__);
				$DB->Query("DELETE FROM b_ticket_sla WHERE ID = $ID", false, $err_mess.__LINE__);
				return true;
			}
			else
				$APPLICATION->ThrowException(str_replace("#ID#", "$ID", GetMessage("SUP_ERROR_SLA_HAS_TICKET")));
		}
		else
			$APPLICATION->ThrowException(GetMessage("SUP_ERROR_ACCESS_DENIED"));
		return false;
	}

	// возвращает SLA по ID
	function GetByID($ID)
	{
		$ID = intval($ID);
		if ($ID<=0) return false;
		$arFilter = array("ID" => $ID, "ID_EXACT_MATCH" => "Y");
		$rs = CTicketSLA::GetList($arSort, $arFilter, $is_filtered);
		return $rs;
	}

	// возвращает массив расписания указанного SLA
	function GetSheduleArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetSheduleArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT * FROM b_ticket_sla_shedule WHERE SLA_ID = $SLA_ID ORDER BY WEEKDAY_NUMBER, MINUTE_FROM, MINUTE_TILL";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch())
			{
				if ($ar["OPEN_TIME"]=="CUSTOM")
				{
					if (intval($ar["MINUTE_FROM"])>0)
					{
						$h_from = floor($ar["MINUTE_FROM"]/60);
						$m_from = $ar["MINUTE_FROM"] - $h_from*60;
					}
					if (intval($ar["MINUTE_TILL"])>0)
					{
						$h_till = floor($ar["MINUTE_TILL"]/60);
						$m_till = $ar["MINUTE_TILL"] - $h_till*60;
					}
					$arResult[$ar["WEEKDAY_NUMBER"]]["OPEN_TIME"] = $ar["OPEN_TIME"];
					$arResult[$ar["WEEKDAY_NUMBER"]]["CUSTOM_TIME"][] = array(
						"MINUTE_FROM"	=> $ar["MINUTE_FROM"],
						"SECOND_FROM"	=> $ar["MINUTE_FROM"]*60,
						"MINUTE_TILL"	=> $ar["MINUTE_TILL"],
						"SECOND_TILL"	=> $ar["MINUTE_TILL"]*60,
						"FROM"			=> $h_from.":".str_pad($m_from, 2, 0),
						"TILL"			=> $h_till.":".str_pad($m_till, 2, 0)
						);
				}
				else
				{
					$arResult[$ar["WEEKDAY_NUMBER"]] = array("OPEN_TIME" => $ar["OPEN_TIME"]);
				}
				$arResult[$ar["WEEKDAY_NUMBER"]]["WEEKDAY_TITLE"] = GetMessage("SUP_WEEKDAY_".$ar["WEEKDAY_NUMBER"]);
			}
		}
		return $arResult;
	}

	// возвращает массив ID групп указанного SLA
	function GetGroupArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetGroupArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT GROUP_ID FROM b_ticket_sla_2_user_group WHERE SLA_ID = $SLA_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch()) $arResult[] = $ar["GROUP_ID"];
		}
		return $arResult;
	}

	// возвращает массив ID сайтов указанного SLA
	function GetSiteArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT SITE_ID FROM b_ticket_sla_2_site WHERE SLA_ID = $SLA_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch()) $arResult[] = $ar["SITE_ID"];
		}
		return $arResult;
	}

	// возвращает массив ID категорий указанного SLA
	function GetCategoryArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetCategoryArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT CATEGORY_ID FROM b_ticket_sla_2_category WHERE SLA_ID = $SLA_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch()) $arResult[] = $ar["CATEGORY_ID"];
		}
		return $arResult;
	}

	// возвращает массив ID критичностей указанного SLA
	function GetCriticalityArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetCriticalityArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT CRITICALITY_ID FROM b_ticket_sla_2_criticality WHERE SLA_ID = $SLA_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch()) $arResult[] = $ar["CRITICALITY_ID"];
		}
		return $arResult;
	}

	// возвращает массив ID оценок указанного SLA
	function GetMarkArray($SLA_ID)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetMarkArray<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$arResult = array();
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID>0)
		{
			$strSql = "SELECT MARK_ID FROM b_ticket_sla_2_mark WHERE SLA_ID = $SLA_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $rs->Fetch()) $arResult[] = $ar["MARK_ID"];
		}
		return $arResult;
	}

	function GetDropDown($SITE_ID="")
	{
		if (strlen($SITE_ID)>0 && strtoupper($SITE_ID)!="ALL") $arFilter = array("SITE" => $SITE_ID);
		$arSort = array("FIRST_SITE_ID" => "ASC", "PRIORITY" => "ASC");
		$rs = CTicketSLA::GetList($arSort, $arFilter, $is_filtered);
		return $rs;
	}

	function GetForUser($SITE_ID=false, $USER_ID=false)
	{
		$err_mess = (CAllTicketSLA::err_mess())."<br>Function: GetForUser<br>Line: ";
		global $DB, $USER, $APPLICATION;

		$SLA_ID = 1; // SLA по умолчанию

		$arrGroups = array();
		if (!is_object($USER)) $USER = new CUser;
		if ($USER_ID===false && is_object($USER)) $USER_ID = $USER->GetID();
		if ($SITE_ID==false) $SITE_ID = SITE_ID;

		$USER_ID = intval($USER_ID);
		if ($USER_ID>0) $arrGroups = CUser::GetUserGroup($USER_ID);
		if (count($arrGroups)<=0) $arrGroups[] = 2;

		$arSLA_2_SITE = array();
		$rs = $DB->Query("SELECT SLA_ID, SITE_ID FROM b_ticket_sla_2_site", false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arSLA_2_SITE[$ar["SLA_ID"]][] = $ar["SITE_ID"];

		$strSql = "
			SELECT
				SG.SLA_ID
			FROM
				b_ticket_sla_2_user_group SG
			INNER JOIN b_ticket_sla S ON (S.ID = SG.SLA_ID)
			WHERE
				SG.GROUP_ID in (".implode(",",$arrGroups).")
			GROUP BY
				SG.SLA_ID, S.PRIORITY
			ORDER BY
				S.PRIORITY DESC
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
		{
			if (is_array($arSLA_2_SITE[$ar["SLA_ID"]]) && (in_array($SITE_ID, $arSLA_2_SITE[$ar["SLA_ID"]]) || in_array("ALL", $arSLA_2_SITE[$ar["SLA_ID"]])))
			{
				$SLA_ID = $ar["SLA_ID"];
				break;
			}
		}
		return $SLA_ID;
	}
}

class CAllTicketReminder
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllTicketReminder<br>File: ".__FILE__;
	}

	// создает или удаляет агента-напоминальщика и обновляет параметры обращения
	function Update($TICKET_ID, $sla_changed = false)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: Update<br>Line: ";
		global $DB;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$strSql = "
			SELECT
				NOTIFY_AGENT_ID,
				EXPIRE_AGENT_ID,
				SLA_ID,
				IS_NOTIFIED,
				IS_OVERDUE,
				".$DB->DateToCharFunction("DATE_CLOSE","FULL")."	DATE_CLOSE
			FROM
				b_ticket
			WHERE
				ID = $TICKET_ID
			";
		$rsTicket = $DB->Query($strSql, false, $err_mess.__LINE__);
		$arTicket = $rsTicket->Fetch();
		$NOTIFY_AGENT_ID = intval($arTicket["NOTIFY_AGENT_ID"]);
		$EXPIRE_AGENT_ID = intval($arTicket["EXPIRE_AGENT_ID"]);

		if (strlen($arTicket["DATE_CLOSE"])<=0/* && $arTicket['IS_OVERDUE'] != 'Y'*/)
		{
			$strSql = "
				SELECT
					ID,
					OWNER_USER_ID,
					NOTIFY_AGENT_DONE,
					EXPIRE_AGENT_DONE,
					".$DB->DateToCharFunction("DATE_CREATE","FULL")."		DATE_CREATE
				FROM
					b_ticket_message
				WHERE
					TICKET_ID=$TICKET_ID
				and (NOT_CHANGE_STATUS='N')
				and (IS_HIDDEN='N' or IS_HIDDEN is null or ".$DB->Length("IS_HIDDEN")."<=0)
				and (IS_LOG='N' or IS_LOG is null or ".$DB->Length("IS_LOG")."<=0)
				and (IS_OVERDUE='N' or IS_OVERDUE is null or ".$DB->Length("IS_OVERDUE")."<=0)
				ORDER BY
					C_NUMBER desc
				";
			$rs = $DB->Query($strSql,false,$err_mess.__LINE__);
			if ($arLastMess = $rs->Fetch())
			{
				$LAST_MESSAGE_BY_SUPPORT_TEAM = (CTicket::IsAdmin($arLastMess["OWNER_USER_ID"]) || CTicket::IsSupportTeam($arLastMess["OWNER_USER_ID"])) ? "Y" : "N";

				$arFields = array();
				if ($LAST_MESSAGE_BY_SUPPORT_TEAM=="Y")
				{
					$arFields["IS_OVERDUE"] = "'N'";
					$arFields["IS_NOTIFIED"] = "'N'";
					if ($NOTIFY_AGENT_ID>0 || $EXPIRE_AGENT_ID>0)
					{
						CTicketReminder::Delete($TICKET_ID);
						$arFields["NOTIFY_AGENT_ID"] = "null";
						$arFields["EXPIRE_AGENT_ID"] = "null";
					}
				}
				elseif($LAST_MESSAGE_BY_SUPPORT_TEAM=="N" && $arTicket['SLA_ID']>0 && $arTicket['IS_OVERDUE'] != 'Y')
				{
					CTicketReminder::Delete($TICKET_ID);
					if ($arLastMess["NOTIFY_AGENT_DONE"]!="Y" || $arLastMess["EXPIRE_AGENT_DONE"]!="Y")
					{
						$stmp = MakeTimeStamp($arLastMess["DATE_CREATE"]);

						if ($sla_changed)
						{
							$NOW_TS = time();
							$EXP_TS = CAllTicketReminder::GetNextExec($TICKET_ID, $arTicket['SLA_ID'], $stmp);
							if ($EXP_TS < $NOW_TS)
							{
								$stmp = $NOW_TS;//+= $NOW_TS - $EXP_TS + 3;
							}

							//$arFields["IS_OVERDUE"] = "'N'";
							$arFields["IS_NOTIFIED"] = "'N'";

							$DB->Update('b_ticket_message', array("NOTIFY_AGENT_DONE" => "'N'"), "WHERE ID='".$arLastMess['ID']."'", $err_mess.__LINE__);
						}

						$arAgents = CTicketReminder::Add($TICKET_ID, $arLastMess["ID"], $arTicket["SLA_ID"], $stmp);
						if (intval($arAgents["NOTIFY_AGENT_ID"])>0) $arFields["NOTIFY_AGENT_ID"] = $arAgents["NOTIFY_AGENT_ID"];
						if (intval($arAgents["EXPIRE_AGENT_ID"])>0) $arFields["EXPIRE_AGENT_ID"] = $arAgents["EXPIRE_AGENT_ID"];
					}
					else
					{
						$arFields["NOTIFY_AGENT_ID"] = "null";
						$arFields["EXPIRE_AGENT_ID"] = "null";
					}
				}
				if (count($arFields)>0)
					$DB->Update("b_ticket",$arFields,"WHERE ID='".$TICKET_ID."'",$err_mess.__LINE__);
			}
		}
		else CTicketReminder::Remove($TICKET_ID);
	}

	function GetNextPoint(&$WORK_TIME, $INITIAL_STMP, $arSHEDULE)
	{
		$WORK_TIME = 0;
		$IS_OPEN = false;
		$weekday = date("w", $INITIAL_STMP);
		switch($weekday)
		{
			case 0: $weekday=6; break; case 1: $weekday=0; break; case 2: $weekday=1; break;
			case 3: $weekday=2; break; case 4: $weekday=3; break; case 5: $weekday=4; break;
			case 6: $weekday=5; break;
		}
		$arDayShedule = $arSHEDULE[$weekday];
		if (is_array($arDayShedule))
		{
			$day = date("d", $INITIAL_STMP);
			$month = date("m", $INITIAL_STMP);
			$year = date("Y", $INITIAL_STMP);
			$hour = date("H", $INITIAL_STMP);
			$minute = date("i", $INITIAL_STMP);
			$second = date("s", $INITIAL_STMP);

			if ($arDayShedule["OPEN_TIME"]=="24H")
			{
				$SHEDULE_POINT = mktime(0,0,0,$month,$day+1,$year);
				$IS_OPEN = true;
			}
			elseif ($arDayShedule["OPEN_TIME"]=="CLOSED")
			{
				$SHEDULE_POINT = mktime(0,0,0,$month,$day+1,$year);
				$IS_OPEN = false;
			}
			elseif ($arDayShedule["OPEN_TIME"]=="CUSTOM")
			{
				$arInterval = array();
				$arCustomTime = $arDayShedule["CUSTOM_TIME"];
				$null_point = mktime(0,0,0,$month,$day,$year);
				foreach($arCustomTime as $arPeriod)
				{
					$arInterval[$null_point + $arPeriod["SECOND_FROM"]] = "FROM";
					$arInterval[$null_point + $arPeriod["SECOND_TILL"]] = "TILL";
				}
				if (count($arInterval)>0)
				{
					reset($arInterval);
					$prev_value = $null_point;
					while(list($value, $type)=each($arInterval))
					{
						$next_value = $value;
						if ($INITIAL_STMP>=$prev_value && $INITIAL_STMP<$next_value)
						{
							$SHEDULE_POINT = $value;
							$IS_OPEN = ($type=="FROM") ? false : true;
						}
						$prev_value = $next_value;
					}
					if (doubleval($SHEDULE_POINT)<=0)
					{
						$SHEDULE_POINT = mktime(0,0,0,$month,$day+1,$year);
						$IS_OPEN = false;
					}

				}
			}
			if ($IS_OPEN) $WORK_TIME = $SHEDULE_POINT - $INITIAL_STMP;
			return $SHEDULE_POINT;
		}
		return false;
	}

	// определяет дату когда необходимо напомнить
	function GetNextExec($TICKET_ID, $SLA_ID, $DATE_START_STMP, $GET_EXPIRE_STMP=false)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: GetNextExec<br>Line: ";
		global $DB, $APPLICATION;
		$SLA_ID = intval($SLA_ID);
		if ($SLA_ID<=0 || strlen($DATE_START_STMP)<0) return;

		$rs = CTicketSLA::GetByID($SLA_ID);
		if ($arSLA = $rs->Fetch())
		{
			// если время реакции на сообщение ограничено, то
			if (intval($arSLA["M_RESPONSE_TIME"])>0)
			{
				// получаем расписание работы техподдержки
				$arSHEDULE = CTicketSLA::GetSheduleArray($SLA_ID);

				// проверим расписание чтобы был хоть один рабочий день
				$arr = array();
				while (list($weekday, $ar) = each($arSHEDULE)) $arr[$weekday] = $ar["OPEN_TIME"];
				foreach($arr as $s)
				{
					if ($s=="24H" || $s=="CUSTOM")
					{
						$WORK_DAY_EXISTS = "Y";
						break;
					}
				}

				if ($WORK_DAY_EXISTS=="Y")
				{
					// если нужно в результате получить дату истечения времени реакции, то
					if ($GET_EXPIRE_STMP)
					{
						$DEADLINE_PERIOD = 60*intval($arSLA["M_RESPONSE_TIME"]);
					}
					else
					{
						$DEADLINE_PERIOD = 60*(intval($arSLA["M_RESPONSE_TIME"]) - intval($arSLA["M_NOTICE_TIME"]));
					}

					$WORK_TIME = $i = 0;
					$point = $DATE_START_STMP;
					while($WORK_TIME < $DEADLINE_PERIOD)
					{
						$i++;
						$point = CTicketReminder::GetNextPoint($WTIME, $point, $arSHEDULE);
						$WORK_TIME = $WORK_TIME + $WTIME;
					}
					$SURPLUS = $WORK_TIME - $DEADLINE_PERIOD;
					$WARNING_STMP = $point - $SURPLUS;
					return $WARNING_STMP;
				}
			}
		}
		return false;
	}

	// создает агентов
	function Add($TICKET_ID, $MESSAGE_ID, $SLA_ID, $DATE_START_STMP)
	{
		$NEXT_EXEC = CAllTicketReminder::GetNextExec($TICKET_ID, $SLA_ID, $DATE_START_STMP);
		if (intval($NEXT_EXEC)>0)
		{
			$NEXT_EXEC = ConvertTimeStamp($NEXT_EXEC, "FULL");
			$NOTIFY_AGENT_ID = CAgent::AddAgent("CTicketReminder::Notify(".$TICKET_ID.", ".$MESSAGE_ID.");", "support", "Y", 0, "", "Y", $NEXT_EXEC);
		}
		$NEXT_EXEC = 0;

		$NEXT_EXEC = CTicketReminder::GetNextExec($TICKET_ID, $SLA_ID, $DATE_START_STMP, true);
		if (intval($NEXT_EXEC)>0)
		{
			$NEXT_EXEC = ConvertTimeStamp($NEXT_EXEC, "FULL");
			$EXPIRE_AGENT_ID = CAgent::AddAgent("CTicketReminder::Expire(".$TICKET_ID.", ".$MESSAGE_ID.");", "support", "Y", 0, "", "Y", $NEXT_EXEC);
		}

		return array("NOTIFY_AGENT_ID" => $NOTIFY_AGENT_ID, "EXPIRE_AGENT_ID" => $EXPIRE_AGENT_ID);
	}

	// удаляет агентов и обновляет параметры обращения
	function Remove($TICKET_ID)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: Remove<br>Line: ";
		global $DB;
		$TICKET_ID = intval($TICKET_ID);
		CTicketReminder::Delete($TICKET_ID);
		$arFields = array(
			"IS_OVERDUE"		=> "'N'",
			"NOTIFY_AGENT_ID"	=> "null",
			"EXPIRE_AGENT_ID"	=> "null"
			);
		$DB->Update("b_ticket",$arFields,"WHERE ID='".$TICKET_ID."'",$err_mess.__LINE__);
	}

	// удаляет агентов
	function Delete($TICKET_ID)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$TICKET_ID = intval($TICKET_ID);
		$rs = $DB->Query("SELECT NOTIFY_AGENT_ID, EXPIRE_AGENT_ID FROM b_ticket WHERE ID=$TICKET_ID", false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch())
		{
			CAgent::Delete($ar["NOTIFY_AGENT_ID"]);
			CAgent::Delete($ar["EXPIRE_AGENT_ID"]);
		}
	}

	// отсылает напоминание о необходимости ответа на сообщение клиента
	function Notify($TICKET_ID, $MESSAGE_ID)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: Notify<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$rs = $DB->Query("SELECT SITE_ID FROM b_ticket WHERE ID=$TICKET_ID", false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch())
		{
			$rs = CTicket::GetByID($TICKET_ID, $ar["SITE_ID"], "N");
			if ($arTicket = $rs->Fetch())
			{
				$rsMessage = CTicket::GetMessageByID($MESSAGE_ID, "N", "N");
				if ($arMessage = $rsMessage->Fetch())
				{
					if ($arMessage["MESSAGE_BY_SUPPORT_TEAM"]!="Y")
					{
						$arMessage["EXPIRATION_DATE_STMP"] = CTicketReminder::GetNextExec($arMessage["TICKET_ID"], $arMessage["SLA_ID"], MakeTimeStamp($arMessage["DATE_CREATE"]), true);
						if (intval($arMessage["EXPIRATION_DATE_STMP"])>0)
						{
							$arMessage["EXPIRATION_DATE"] = ConvertTimeStamp($arMessage["EXPIRATION_DATE_STMP"], "FULL", $arTicket["SITE_ID"]);
						}
					}

					if (strlen($arMessage["EXPIRATION_DATE"])>0)
					{
						$rsSite = CSite::GetByID($arTicket["SITE_ID"]);
						$arSite = $rsSite->Fetch();

						global $MESS;
						$OLD_MESS = $MESS;
						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/messages.php", $arSite["LANGUAGE_ID"]);

						$SOURCE_NAME = strlen($arTicket["SOURCE_NAME"])<=0 ? "" : "[".$arTicket["SOURCE_NAME"]."] ";
						if (intval($arTicket["OWNER_USER_ID"])>0 || strlen(trim($arTicket["OWNER_LOGIN"]))>0)
						{
							$OWNER_TEXT = "[".$arTicket["OWNER_USER_ID"]."] (".$arTicket["OWNER_LOGIN"].") ".$arTicket["OWNER_NAME"];
							if (strlen(trim($OWNER_SID))>0 && $OWNER_SID!="null") $OWNER_TEXT = " / ".$OWNER_TEXT;
						}

						if (intval($arTicket["RESPONSIBLE_USER_ID"])>0)
						{
							$RESPONSIBLE_TEXT = "[".$arTicket["RESPONSIBLE_USER_ID"]."] (".$arTicket["RESPONSIBLE_LOGIN"].") ".$arTicket["RESPONSIBLE_NAME"];
							if (CTicket::IsSupportTeam($arTicket["RESPONSIBLE_USER_ID"]) || CTicket::IsAdmin($arTicket["RESPONSIBLE_USER_ID"]))
							{
								$RESPONSIBLE_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
							}
						}

						$arAdminEMails = CTicket::GetAdminEmails();
						if (count($arAdminEMails)>0) $support_admin_email = implode(",",$arAdminEMails);

						// сформируем email автора
						$arrOwnerEMail = array($arTicket["OWNER_EMAIL"]);
						$arrEmails = explode(",",$arTicket["OWNER_SID"]);
						if (is_array($arrEmails) && count($arrEmails)>0)
						{
							foreach($arrEmails as $email)
							{
								$email = trim($email);
								if (strlen($email)>0)
								{
									preg_match_all("#[<\[\(](.*?)[>\]\)]#i".BX_UTF_PCRE_MODIFIER, $email, $arr);
									if (is_array($arr[1]) && count($arr[1])>0)
									{
										foreach($arr[1] as $email)
										{
											$email = trim($email);
											if (strlen($email)>0 && !in_array($email, $arrOwnerEMail) && check_email($email))
											{
												$arrOwnerEMail[] = $email;
											}
										}
									}
									elseif (!in_array($email, $arrOwnerEMail) && check_email($email)) $arrOwnerEMail[] = $email;
								}
							}
						}
						TrimArr($arrOwnerEMail);
						$owner_email = implode(", ", $arrOwnerEMail);

						// сформируем email техподдержки
						$support_email = $arTicket["RESPONSIBLE_EMAIL"];
						if (strlen($support_email)<=0)
							$support_email = $support_admin_email;
						if (strlen($support_email)<=0)
							$support_email = COption::GetOptionString("main","email_from","");

						$arr = explode(",",$support_email);
						$arr = array_unique($arr);
						$support_email = implode(",",$arr);
						if (is_array($arr) && count($arr)>0)
						{
							foreach($arr as $email) unset($arAdminEMails[$email]);
						}
						$support_admin_email = implode(",",$arAdminEMails);

						if ($arTicket["CREATED_MODULE_NAME"]=="support" && strlen($arTicket["CREATED_MODULE_NAME"])>0)
						{
							$CREATED_MODULE_NAME = "";
							if (intval($arTicket["CREATED_USER_ID"])>0)
							{
								$CREATED_TEXT = "[".$arTicket["CREATED_USER_ID"]."] (".$arTicket["CREATED_LOGIN"].") ".$arTicket["CREATED_NAME"];
								if (CTicket::IsSupportTeam($arTicket["CREATED_USER_ID"]) || CTicket::IsAdmin($arTicket["CREATED_USER_ID"]))
								{
									$CREATED_TEXT .= " ".GetMessage("SUP_TECHSUPPORT_HINT");
								}
							}
						}
						else $CREATED_MODULE_NAME = "[".$arTicket["CREATED_MODULE_NAME"]."]";

						$MESSAGE = PrepareTxtForEmail($arMessage["MESSAGE"], $arSite["LANGUAGE_ID"], false, false);
						$EXPIRATION_DATE = ConvertTimeStamp($arMessage["EXPIRATION_DATE"], "FULL", $arTicket["SITE_ID"]);
						$REMAINED_TIME = $arMessage["EXPIRATION_DATE_STMP"] - time();
						if ($REMAINED_TIME>0)
						{
							$str_REMAINED_TIME = "";
							$hours = intval($REMAINED_TIME/3600);
							if ($hours>0)
							{
								$str_REMAINED_TIME .= $hours." ".GetMessage("SUP_HOUR")." ";
								$REMAINED_TIME = $REMAINED_TIME - $hours*3600;
							}
							$str_REMAINED_TIME .= intval($REMAINED_TIME/60)." ".GetMessage("SUP_MIN")." ";
							$str_REMAINED_TIME .= ($REMAINED_TIME%60)." ".GetMessage("SUP_SEC");
						}

						$arFields = array(
							"ID"						=> $arTicket["ID"],
							"LANGUAGE_ID"				=> $arSite["LANGUAGE_ID"],
							"DATE_CREATE"				=> $arTicket["DATE_CREATE"],
							"TITLE"						=> $arTicket["TITLE"],
							"STATUS"					=> $arTicket["STATUS_NAME"],
							"CATEGORY"					=> $arTicket["CATEGORY_NAME"],
							"CRITICALITY"				=> $arTicket["CRITICALITY_NAME"],
							"DIFFICULTY"				=> $arTicket["DIFFICULTY_NAME"],
							"RATE"						=> $arTicket["MARK_NAME"],
							"SLA"						=> $arTicket["SLA_NAME"],
							"SOURCE"					=> $SOURCE_NAME,
							"ADMIN_EDIT_URL"			=> "/bitrix/admin/ticket_edit.php",
							"EXPIRATION_DATE"			=> $arMessage["EXPIRATION_DATE"],
							"REMAINED_TIME"				=> $str_REMAINED_TIME,

							"OWNER_EMAIL"				=> TrimEx($owner_email,","),
							"OWNER_USER_ID"				=> $arTicket["OWNER_USER_ID"],
							"OWNER_USER_NAME"			=> $arTicket["OWNER_NAME"],
							"OWNER_USER_LOGIN"			=> $arTicket["OWNER_LOGIN"],
							"OWNER_USER_EMAIL"			=> $arTicket["OWNER_EMAIL"],
							"OWNER_TEXT"				=> $OWNER_TEXT,
							"OWNER_SID"					=> $arTicket["OWNER_SID"],

							"SUPPORT_EMAIL"				=> TrimEx($support_email,","),
							"RESPONSIBLE_USER_ID"		=> $arTicket["RESPONSIBLE_USER_ID"],
							"RESPONSIBLE_USER_NAME"		=> $arTicket["RESPONSIBLE_NAME"],
							"RESPONSIBLE_USER_LOGIN"	=> $arTicket["RESPONSIBLE_LOGIN"],
							"RESPONSIBLE_USER_EMAIL"	=> $arTicket["RESPONSIBLE_EMAIL"],
							"RESPONSIBLE_TEXT"			=> $RESPONSIBLE_TEXT,
							"SUPPORT_ADMIN_EMAIL"		=> TrimEx($support_admin_email,","),

							"CREATED_USER_ID"			=> $arTicket["CREATED_USER_ID"],
							"CREATED_USER_LOGIN"		=> $arTicket["CREATED_LOGIN"],
							"CREATED_USER_EMAIL"		=> $arTicket["CREATED_EMAIL"],
							"CREATED_USER_NAME"			=> $arTicket["CREATED_NAME"],
							"CREATED_MODULE_NAME"		=> $CREATED_MODULE_NAME,
							"CREATED_TEXT"				=> $CREATED_TEXT,

							"MESSAGE_BODY"				=> $MESSAGE
							);
						//echo "<pre>"; print_r($arFields); echo "</pre>";
						CEvent::Send("TICKET_OVERDUE_REMINDER", $arTicket["SITE_ID"], $arFields);
						$MESS = $OLD_MESS;

						$arFields = array("NOTIFY_AGENT_ID" => "null", "IS_NOTIFIED" => "'Y'");
						$DB->Update("b_ticket",$arFields,"WHERE ID='".$arTicket["ID"]."'",$err_mess.__LINE__);

						$arFields = array("NOTIFY_AGENT_DONE" => "'Y'");
						$DB->Update("b_ticket_message",$arFields,"WHERE ID='".$arMessage["ID"]."'",$err_mess.__LINE__);
					}
				}
			}
		}
	}

	function Expire($TICKET_ID, $MESSAGE_ID)
	{
		$err_mess = (CAllTicketReminder::err_mess())."<br>Function: Expire<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return;

		$rs = $DB->Query("SELECT SITE_ID FROM b_ticket WHERE ID=$TICKET_ID", false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch())
		{
			$rs = CTicket::GetByID($TICKET_ID, false, "N");
			if ($arTicket = $rs->Fetch())
			{
				$rsMessage = CTicket::GetMessageByID($MESSAGE_ID, "N", "N");
				if ($arMessage = $rsMessage->Fetch())
				{
					$rsSite = CSite::GetByID($arTicket["SITE_ID"]);
					$arSite = $rsSite->Fetch();

					global $MESS;
					$OLD_MESS = $MESS;
					IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/messages.php", $arSite["LANGUAGE_ID"]);

					// обновляем параметры обращения
					$arFields = array(
						"EXPIRE_AGENT_ID"		=> "null",
						"IS_OVERDUE"			=> "'Y'",
						"OVERDUE_MESSAGES"		=> "OVERDUE_MESSAGES + 1",
						);
					$DB->Update("b_ticket",$arFields,"WHERE ID='".$arTicket["ID"]."'",$err_mess.__LINE__);

					// обновляем параметры сообщения
					$arFields = array("EXPIRE_AGENT_DONE" => "'Y'");
					$DB->Update("b_ticket_message",$arFields,"WHERE ID='".$arMessage["ID"]."'",$err_mess.__LINE__);

					// добавляем лог-сообщение
					$message = str_replace("#ID#", $arMessage["ID"], GetMessage("SUP_MESSAGE_OVERDUE_LOG"));
					$message = str_replace("#NUMBER#", $arMessage["C_NUMBER"], $message);
					$message .= "<br><li>".htmlspecialcharsEx(str_replace("#VALUE#", $arTicket["SLA_NAME"], GetMessage("SUP_SLA_LOG")));

					if (intval($arTicket["RESPONSIBLE_USER_ID"])>0)
					{
						$rsUser = CUser::GetByID(intval($arTicket["RESPONSIBLE_USER_ID"]));
						$arUser = $rsUser->Fetch();
						$RESPONSIBLE_TEXT = "[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
						$message .= "<li>".htmlspecialcharsEx(str_replace("#VALUE#", $RESPONSIBLE_TEXT, GetMessage("SUP_RESPONSIBLE_LOG")));
					}

					$arFields = array(
						"IS_LOG"						=> "Y",
						"IS_OVERDUE"					=> "Y",
						"MESSAGE_CREATED_USER_ID"		=> "null",
						"MESSAGE_CREATED_MODULE_NAME"	=> "auto expiration",
						"MESSAGE_CREATED_GUEST_ID"		=> "null",
						"MESSAGE_SOURCE_ID"				=> "null",
						"MESSAGE"						=> $message
						);
					$MID = CTicket::AddMessage($TICKET_ID, $arFields, $v, "N");

					$MESS = $OLD_MESS;
				}
			}
		}
	}
}

class CSupportEMail
{
	function OnGetFilterList()
	{
		return Array(
			"ID"					=>	"support",
			"NAME"					=>	GetMessage("SUP_ADD_MESSAGE_TO_TECHSUPPORT"),
			"ACTION_INTERFACE"		=>	$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/mail/action.php",
			"PREPARE_RESULT_FUNC"	=>	Array("CSupportEMail", "PrepareVars"),
			"CONDITION_FUNC"		=>	Array("CSupportEMail", "EMailMessageCheck"),
			"ACTION_FUNC"			=>	Array("CSupportEMail", "EMailMessageAdd")
			);
	}

	function PrepareVars()
	{
		return
			'W_SUPPORT_CATEGORY='.urlencode($_REQUEST["W_SUPPORT_CATEGORY"]).
			'&W_SUPPORT_SITE_ID='.urlencode($_REQUEST["W_SUPPORT_SITE_ID"]).
			'&W_SUPPORT_CRITICALITY='.urlencode($_REQUEST["W_SUPPORT_CRITICALITY"]).
			'&W_SUPPORT_ADD_MESSAGE_AS_HIDDEN='.urlencode($_REQUEST["W_SUPPORT_ADD_MESSAGE_AS_HIDDEN"]).
			'&W_SUPPORT_SUBJECT='.urlencode($_REQUEST["W_SUPPORT_SUBJECT"]).
			'&W_SUPPORT_SEC='.urlencode($_REQUEST["W_SUPPORT_SEC"]).
			'&W_SUPPORT_USER_FIND='.urlencode($_REQUEST["W_SUPPORT_USER_FIND"]);
	}

	function EMailMessageCheck($arFields, $ACTION_VARS)
	{
		$arACTION_VARS = explode("&", $ACTION_VARS);
		for($i=0; $i<count($arACTION_VARS); $i++)
		{
			$v = $arACTION_VARS[$i];
			if($pos = strpos($v, "="))
				${substr($v, 0, $pos)} = urldecode(substr($v, $pos+1));
		}
		return true;
	}

	function EMailMessageAdd($arMessageFields, $ACTION_VARS)
	{
		$arACTION_VARS = explode("&", $ACTION_VARS);
		for($i=0; $i<count($arACTION_VARS); $i++)
		{
			$v = $arACTION_VARS[$i];
			if($pos = strpos($v, "="))
				${substr($v, 0, $pos)} = urldecode(substr($v, $pos+1));
		}

		if(!CModule::IncludeModule("support"))
			return false;

		if (strlen($W_SUPPORT_SITE_ID)>0)
		{
			$rs = CSite::GetByID($W_SUPPORT_SITE_ID);
			if ($ar = $rs->Fetch()) $SITE_ID = $ar["LID"];
		}
		if (strlen($SITE_ID)<=0)
		{
			$SITE_ID = $arMessageFields["LID"];
		}

		$SOURCE_MAIL = COption::GetOptionString("support", "SOURCE_MAIL");
		$dbr = CTicketDictionary::GetBySID($SOURCE_MAIL, "SR", $SITE_ID);
		if(!($ar = $dbr->Fetch()))
			return false;

		$TICKET_SOURCE_ID = $ar["ID"];
		$ID = $arMessageFields["ID"];
		$message_email = (strlen($arMessageFields["FIELD_REPLY_TO"])>0) ? $arMessageFields["FIELD_REPLY_TO"] : $arMessageFields["FIELD_FROM"];
		$message_email_addr = strtolower(CMailUtil::ExtractMailAddress($message_email));

		$TID = 0;
		$arSubjects = explode("\n", trim($W_SUPPORT_SUBJECT));
		for($i=0; $i<count($arSubjects); $i++)
		{
			$arSubjects[$i] = Trim($arSubjects[$i]);
			if(strlen($arSubjects[$i])>0)
			{
				if(preg_match("/".$arSubjects[$i]."/".BX_UTF_PCRE_MODIFIER, $arMessageFields["SUBJECT"], $regs))
				{
					$TID = IntVal($regs[1]);
					break;
				}
			}
		}

		if($TID>0)
		{
			$db_ticket = CTicket::GetByID($TID, $SITE_ID, "N", "N", "N");
			if($ar_ticket = $db_ticket->Fetch())
			{
				//check user email address limits
				if($W_SUPPORT_SEC == "domain" || $W_SUPPORT_SEC == "email")
				{
					$bEMailOK = false;
					if($TICKET_SOURCE_ID == $ar_ticket["SOURCE_ID"])
					{
						$ticket_email = strtolower(CMailUtil::ExtractMailAddress($ar_ticket["OWNER_SID"]));
						if($W_SUPPORT_SEC == "domain")
							$ticket_email = substr($ticket_email, strpos($ticket_email, "@"));

						if(strpos($message_email_addr, $ticket_email)!==false)
							$bEMailOK = true;
					}

					if(!$bEMailOK && $ar_ticket["OWNER_USER_ID"]>0)
					{
						$db_user = CUser::GetByID($ar_ticket["OWNER_USER_ID"]);
						if($arUser = $db_user->Fetch())
						{
							$ticket_email = strtolower(CMailUtil::ExtractMailAddress($arUser["EMAIL"]));
							if($check_type == "domain")
								$ticket_email = substr($ticket_email, strpos($ticket_email, "@"));

							if(strpos($message_email_addr, $ticket_email)!==false)
								$bEMailOK = true;
						}
					}
					if(!$bEMailOK) $TID = 0;
				}
			}
			else $TID=0;
		}

		//when message subject is empty - generate it from message body
		$title = trim($arMessageFields["SUBJECT"]);
		if(strlen($title)<=0)
		{
			$title = trim($arMessageFields["BODY"]);
			$title = preg_replace("/[\n\r\t ]+/s".BX_UTF_PCRE_MODIFIER, " ", $title);
			$title = substr($title, 0, 50);
		}

		$arFieldsTicket = array(
			"CLOSE"					=> "N",
			"TITLE"					=> $title,
			"MESSAGE"				=> $arMessageFields["BODY"],
			"MESSAGE_AUTHOR_SID"	=> $message_email,
			"MESSAGE_SOURCE_SID"	=> "email",
			"MODIFIED_MODULE_NAME"	=> "mail",
			"EXTERNAL_ID"			=> $ID,
			"EXTERNAL_FIELD_1"		=> $arMessageFields["HEADER"]
			);

		if($W_SUPPORT_USER_FIND=="Y")
		{
			$o = "LAST_LOGIN"; $b = "DESC";
			$res = CUser::GetList($o, $b, Array("ACTIVE" => "Y", "=EMAIL"=>$message_email_addr));
			if(($arr = $res->Fetch()) && strtolower(CMailUtil::ExtractMailAddress($arr["EMAIL"]))==$message_email_addr)
			{
				$AUTHOR_USER_ID = $arr["ID"];
			}
		}

		// обрабатываем приаттаченные файлы
		$arrUnlink = array();
		$arFILES = array();
		$rsAttach = CMailAttachment::GetList(Array(), Array("MESSAGE_ID"=>$ID));
		while ($arAttach = $rsAttach->Fetch())
		{
			// сохраняем ее из базы на диск
			$dir = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/support/tmp/";
			CheckDirPath($dir);
			$filename = $dir.md5(uniqid("")).".tmp";
			if($handle = @fopen($filename, 'wb'))
			{
				if(!@fwrite($handle, $arAttach["FILE_DATA"])) fclose($handle);
				else
				{
					fclose($handle);
					$arFile = array(
						"name"		=> $arAttach["FILE_NAME"],
						"type"		=> $arAttach["CONTENT_TYPE"],
						"size"		=> @filesize($filename),
						"tmp_name"	=> $filename,
						"MODULE_ID"	=> "support"
						);
					$arFILES[] = $arFile;
					$arrUnlink[] = $filename;
				}
			}
		}
		if (count($arFILES)>0) $arFieldsTicket["FILES"] = $arFILES;

		//echo "TID = ".$TID."<br>";
		//echo "<pre>"; print_r($arFieldsTicket); echo "</pre>";

		if($TID>0) // добавим в существующее обращение
		{
			$arFieldsTicket["MESSAGE_AUTHOR_USER_ID"] = $AUTHOR_USER_ID;

			if ($W_SUPPORT_ADD_MESSAGE_AS_HIDDEN=="Y")	$arFieldsTicket["HIDDEN"] = "Y";
			if ($arMessageFields["SPAM"]=="Y")			$arFieldsTicket["IS_SPAM"] = "Y";

			$TID = CTicket::Set($arFieldsTicket, $MESSAGE_ID, $TID, "N");
		}
		else // новое обращение
		{
			$arFieldsTicket["SITE_ID"] = $SITE_ID;
			$arFieldsTicket["OWNER_USER_ID"] = $AUTHOR_USER_ID;
			$arFieldsTicket["OWNER_SID"] = $message_email;
			$arFieldsTicket["CREATED_MODULE_NAME"] = "mail";
			$arFieldsTicket["SOURCE_SID"] = "email";

			if ($arMessageFields["SPAM"]=="Y")	$arFieldsTicket["IS_SPAM"] = "Y";
			if ($W_SUPPORT_CATEGORY>0)			$arFieldsTicket["CATEGORY_ID"] = $W_SUPPORT_CATEGORY;
			if ($W_SUPPORT_CRITICALITY>0)		$arFieldsTicket["CRITICALITY_ID"] = $W_SUPPORT_CRITICALITY;

			if (strlen(trim($arFieldsTicket["TITLE"]))<=0)
			{
				$arFieldsTicket["TITLE"] = " ";
			}
			if (strlen(trim($arFieldsTicket["MESSAGE"]))<=0)
			{
				$arFieldsTicket["MESSAGE"] = " ";
			}
			//echo "<pre>"; print_r($arFieldsTicket); echo "</pre>";
			$TID = CTicket::Set($arFieldsTicket, $MESSAGE_ID, "", "N");
		}
		if (count($arrUnlink)>0)
		{
			foreach ($arrUnlink as $unlink_file) @unlink($unlink_file);
		}
	}
}

class CSupportUserGroup
{
	function GetList($arOrder = array(), $arFilter = array())
	{
		global $DB;

		$arFields = array(
			'ID' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'NAME' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'NAME',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'XML_ID' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'XML_ID',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'SORT' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'SORT',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'IS_TEAM_GROUP' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'IS_TEAM_GROUP',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
		);

		$strOrder = '';
		if (is_array($arOrder) && count($arOrder) > 0)
		{
			foreach ($arOrder as $k => $v)
			{
				if (array_key_exists($k, $arFields))
				{
					$v = strtoupper($v);
					if($v != 'DESC')
					{
						$v  ='ASC';
					}
					if (strlen($strOrder) > 0)
					{
						$strOrder .= ', ';
					}
					$strOrder .= $arFields[$k]['TABLE_ALIAS'] . '.' . $arFields[$k]['FIELD_NAME'] . ' ' . $v;
				}
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields($arFields);

		$where = $obQueryWhere->GetQuery($arFilter);

		$strQuery = 'SELECT G.* FROM b_ticket_ugroups G';

		if (strlen($where) > 0)
		{
			$strQuery .= ' WHERE ' . $where;
		}

		if (strlen($strOrder) > 0)
		{
			$strQuery .= ' ORDER BY ' . $strOrder;
		}
		//echo $strQuery;
		return $DB->Query($strQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function Add($arFields)
	{
		global $DB, $APPLICATION;
		if ($this->CheckFields($arFields))
		{
			return $DB->Add('b_ticket_ugroups', $arFields);
		}
		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		if ($this->CheckFields($arFields, $ID))
		{
			$strUpdate = $DB->PrepareUpdate('b_ticket_ugroups', $arFields);
			$DB->Query("UPDATE b_ticket_ugroups SET $strUpdate WHERE ID=$ID");
			return true;
		}
		return false;
	}

	function CheckFields(&$arFields, $ID = 0)
	{
		global $APPLICATION;
		if ($ID > 0)
		{
			$rs = CSupportUserGroup::GetList(false, array('ID' => $ID));
			if (!$rs->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_GROUP_NOT_FOUND'));
				return false;
			}
		}

		if(array_key_exists('NAME', $arFields) && $arFields['NAME'] == '')
		{
			$APPLICATION->ThrowException(GetMessage('SUP_ERROR_GROUP_NAME_EMPTY'));
			return false;
		}
		if (array_key_exists('ID', $arFields))
			unset($arFields['ID']);
		if (array_key_exists('SORT', $arFields) && !is_numeric($arFields['SORT']))
		{
			unset($arFields['SORT']);
		}
		if (array_key_exists('IS_TEAM_GROUP', $arFields))
		{
			$arFields['IS_TEAM_GROUP'] = ($arFields['IS_TEAM_GROUP'] == 'Y' ? 'Y' : 'N');
		}

		return true;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		if ($ID > 0)
		{
			$DB->Query('DELETE FROM b_ticket_user_ugroup WHERE GROUP_ID=' . $ID);
			$DB->Query('DELETE FROM b_ticket_ugroups WHERE ID=' . $ID);
		}
	}

	function GetUserGroupList($arOrder = array(), $arFilter = array())
	{
		return CSupportUser2UserGroup::GetList($arOrder, $arFilter);
	}

	function AddUserGroup($arFields)
	{
		return CSupportUser2UserGroup::Add($arFields);
	}

	function UpdateUserGroup($GROUP_ID, $USER_ID, $arFields)
	{
		return CSupportUser2UserGroup::Update($GROUP_ID, $USER_ID, $arFields);
	}

	function DeleteUserGroup($GROUP_ID, $USER_ID)
	{
		return CSupportUser2UserGroup::Delete($GROUP_ID, $USER_ID);
	}
}

class CSupportUser2UserGroup
{
	function GetList($arOrder = array(), $arFilter = array())
	{
		global $DB;
		$arFields = array(
			'GROUP_ID' => array(
				'TABLE_ALIAS' => 'UG',
				'FIELD_NAME' => 'UG.GROUP_ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'USER_ID' => array(
				'TABLE_ALIAS' => 'UG',
				'FIELD_NAME' => 'UG.USER_ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'CAN_VIEW_GROUP_MESSAGES' => array(
				'TABLE_ALIAS' => 'UG',
				'FIELD_NAME' => 'UG.CAN_VIEW_GROUP_MESSAGES',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'CAN_MAIL_GROUP_MESSAGES' => array(
				'TABLE_ALIAS' => 'UG',
				'FIELD_NAME' => 'UG.CAN_MAIL_GROUP_MESSAGES',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),

			'GROUP_NAME' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'G.NAME',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'IS_TEAM_GROUP' => array(
				'TABLE_ALIAS' => 'G',
				'FIELD_NAME' => 'G.IS_TEAM_GROUP',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),

			'LOGIN' => array(
				'TABLE_ALIAS' => 'U',
				'FIELD_NAME' => 'U.LOGIN',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'FIRST_NAME' => array(
				'TABLE_ALIAS' => 'U',
				'FIELD_NAME' => 'U.NAME',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),
			'LAST_NAME' => array(
				'TABLE_ALIAS' => 'U',
				'FIELD_NAME' => 'U.LAST_NAME',
				'FIELD_TYPE' => 'string', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			),

		);

		$strOrder = '';
		if (is_array($arOrder) && count($arOrder) > 0)
		{
			foreach ($arOrder as $k => $v)
			{
				if (array_key_exists($k, $arFields))
				{
					$v = strtoupper($v);
					if($v != 'DESC')
					{
						$v  ='ASC';
					}
					if (strlen($strOrder) > 0)
					{
						$strOrder .= ', ';
					}
					$strOrder .= $arFields[$k]['FIELD_NAME'] . ' ' . $v;
				}
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields($arFields);

		$where = $obQueryWhere->GetQuery($arFilter);

		$strQuery = 'SELECT ' .
			'UG.*, G.NAME GROUP_NAME, G.IS_TEAM_GROUP, '.
			'U.LOGIN, U.NAME FIRST_NAME, U.LAST_NAME ' .
			'FROM b_ticket_user_ugroup UG ' .
			'INNER JOIN b_ticket_ugroups G ON (UG.GROUP_ID=G.ID) ' .
			'INNER JOIN b_user U ON (UG.USER_ID=U.ID) ';

		if (strlen($where) > 0)
		{
			$strQuery .= ' WHERE ' . $where;
		}
		if (strlen($strOrder) > 0)
		{
			$strQuery .= ' ORDER BY ' . $strOrder;
		}

		return $DB->Query($strQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function Add($arFields)
	{
		global $DB;
		if (CSupportUser2UserGroup::CheckFields($arFields))
		{
			$arInsert = $DB->PrepareInsert('b_ticket_user_ugroup', $arFields);
			return $DB->Query('INSERT INTO b_ticket_user_ugroup ('.$arInsert[0].') VALUES ('.$arInsert[1].')', false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return false;
	}

	function Update($GROUP_ID, $USER_ID, $arFields)
	{
		if (CSupportUser2UserGroup::CheckFields($arFields, $GROUP_ID, $USER_ID))
		{
			global $DB;
			$GROUP_ID = intval($GROUP_ID);
			$USER_ID = intval($USER_ID);

			$strUpdate = $DB->PrepareUpdate('b_ticket_user_ugroup', $arFields);
			if (strlen($strUpdate) > 0)
			{
				$strSql = "UPDATE b_ticket_user_ugroup SET $strUpdate WHERE USER_ID=$USER_ID AND GROUP_ID=$GROUP_ID";
				return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		return false;
	}

	function CheckFields(&$arFields, $GROUP_ID = 0, $USER_ID = 0)
	{
		global $APPLICATION, $DB, $USER;
		$GROUP_ID = intval($GROUP_ID);
		$USER_ID = intval($USER_ID);
		if (!is_array($arFields))
		{
			$arFields = array();
		}

		//if update
		if ($USER_ID > 0 || $GROUP_ID > 0)
		{
			if ($USER_ID <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_USER_ID_EMPTY'));
				return false;
			}
			if ($GROUP_ID <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_GROUP_ID_EMPTY'));
				return false;
			}

			if (array_key_exists('GROUP_ID', $arFields))
			{
				unset($arFields['GROUP_ID']);
			}
			if (array_key_exists('USER_ID', $arFields))
			{
				unset($arFields['USER_ID']);
			}
		}

		//if add
		if ($USER_ID <= 0 && $GROUP_ID <= 0)
		{
			$arFields['GROUP_ID'] = array_key_exists('GROUP_ID', $arFields) ? intval($arFields['GROUP_ID']) : 0;
			$arFields['USER_ID'] = array_key_exists('USER_ID', $arFields) ? intval($arFields['USER_ID']) : 0;

			if ($arFields['USER_ID'] <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_USER_ID_EMPTY'));
				return false;
			}
			if ($arFields['GROUP_ID'] <= 0)
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_GROUP_ID_EMPTY'));
				return false;
			}

			$rs = $USER->GetByID($arFields['USER_ID']);
			if (!$rs->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_NO_USER'));
				return false;
			}
			$rs = CSupportUserGroup::GetList(false, array('ID' => $arFields['GROUP_ID']));
			if(!$arGroup = $rs->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_NO_GROUP'));
				return false;
			}
			if (CTicket::IsAdmin($arFields['USER_ID']) || CTicket::IsSupportTeam($arFields['USER_ID']))
			{
				if ($arGroup['IS_TEAM_GROUP'] <> 'Y')
				{
					$APPLICATION->ThrowException(GetMessage('SUP_ERROR_USER_NO_CLIENT'));
					return false;
				}
			}
			elseif (CTicket::IsSupportClient($arFields['USER_ID']))
			{
				if ($arGroup['IS_TEAM_GROUP'] == 'Y')
				{
					$APPLICATION->ThrowException(GetMessage('SUP_ERROR_USER_NO_TEAM'));
					return false;
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_NO_SUPPORT_USER'));
				return false;
			}

			$rs = CSupportUser2UserGroup::GetList(false, array('GROUP_ID' => $arFields['GROUP_ID'], 'USER_ID' => $arFields['USER_ID']));
			if ($rs->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage('SUP_ERROR_USERGROUP_EXISTS'));
				return false;
			}
		}

		if (array_key_exists('CAN_VIEW_GROUP_MESSAGES', $arFields))
		{
			$arFields['CAN_VIEW_GROUP_MESSAGES'] = $arFields['CAN_VIEW_GROUP_MESSAGES'] == 'Y' ? 'Y' : 'N';
		}
		elseif ($USER_ID <= 0 && $GROUP_ID <= 0)
		{
			$arFields['CAN_VIEW_GROUP_MESSAGES'] = 'N';
		}

		if (array_key_exists('CAN_MAIL_GROUP_MESSAGES', $arFields))
		{
			$arFields['CAN_MAIL_GROUP_MESSAGES'] = $arFields['CAN_MAIL_GROUP_MESSAGES'] == 'Y' ? 'Y' : 'N';
		}
		elseif ($USER_ID <= 0 && $GROUP_ID <= 0)
		{
			$arFields['CAN_MAIL_GROUP_MESSAGES'] = 'N';
		}

		return true;
	}

	function Delete($GROUP_ID, $USER_ID)
	{
		$GROUP_ID = intval($GROUP_ID);
		$USER_ID = intval($USER_ID);
		if ($GROUP_ID > 0 && $USER_ID > 0)
		{
			global $DB;
			return $DB->Query("DELETE FROM b_ticket_user_ugroup WHERE USER_ID=$USER_ID AND GROUP_ID=$GROUP_ID");
		}
		return false;
	}

	function SetGroupUsers($GROUP_ID, $arUsers)
	{
		global $APPLICATION;
		$GROUP_ID = intval($GROUP_ID);

		$ret = array();

		if ($GROUP_ID > 0)
		{
			global $DB;
			$DB->Query('DELETE FROM b_ticket_user_ugroup WHERE GROUP_ID=' . $GROUP_ID);
			if (is_array($arUsers) && count($arUsers) > 0)
			{
				foreach ($arUsers as $user)
				{
					if (is_array($user) && isset($user['USER_ID']) && intval($user['USER_ID']) > 0)
					{
						$arr = array(
							'GROUP_ID' => $GROUP_ID,
							'USER_ID' => $user['USER_ID'],
							'CAN_VIEW_GROUP_MESSAGES' => $user['CAN_VIEW_GROUP_MESSAGES'] == 'Y' ? 'Y' : 'N',
							'CAN_MAIL_GROUP_MESSAGES' => $user['CAN_MAIL_GROUP_MESSAGES'] == 'Y' ? 'Y' : 'N'
						);

						if (!CSupportUser2UserGroup::Add($arr))
						{
							if ($e = $APPLICATION->GetException())
							{
								$ret[] = $e->GetString();
							}
						}
					}
				}
			}
		}

		return $ret;
	}
}

function __sup_debug($v, $name = false)
{
	if (!is_scalar($v))
	{
		$v = var_export($v, true);
	}

	$str = date('r') . ( $name ? " ### $name\n" :"\n");
	$str .= $v;
	$str .= "\n========================================\n\n";

	file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/xxx_support_debug.txt', $str, FILE_APPEND);
}

?>
