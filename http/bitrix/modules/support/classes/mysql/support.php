<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/support.php");

class CTicket extends CAllTicket
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CTicket<br>File: ".__FILE__;
	}

	function AutoClose()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: AutoClose<br>Line: ";
		global $DB;
		/*$strSql = "
			SELECT 
				T.ID
			FROM
				b_ticket T
			WHERE
				T.AUTO_CLOSE_DAYS > 0
			and (T.DATE_CLOSE is null or length(T.DATE_CLOSE)<=0)
			and	(UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(T.TIMESTAMP_X))/86400 > T.AUTO_CLOSE_DAYS			
			";*/
		$strSql = "
			SELECT 
				T.ID
			FROM
				b_ticket T
			WHERE
				T.AUTO_CLOSE_DAYS > 0
			and (T.DATE_CLOSE is null or length(T.DATE_CLOSE)<=0)
			and	(UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(T.LAST_MESSAGE_DATE))/86400 > T.AUTO_CLOSE_DAYS			
			";
		//echo "<pre>".$strSql."</pre>";
		$rsTickets = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($arTicket = $rsTickets->Fetch())
		{
			$arFields = array(
				"TIMESTAMP_X"			=> $DB->GetNowFunction(),
				"DATE_CLOSE"			=> $DB->GetNowFunction(),
				"MODIFIED_USER_ID"		=> "null",
				"MODIFIED_GUEST_ID"		=> "null",
				"MODIFIED_MODULE_NAME"	=> "'auto closing'",
				"AUTO_CLOSE_DAYS"		=> "null",
				"AUTO_CLOSED"			=> "'Y'"
				);
			$DB->Update("b_ticket",$arFields,"WHERE ID='".$arTicket["ID"]."'",$err_mess.__LINE__);
		}
		return "CTicket::AutoClose();";
	}

	function CleanUpOnline()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: CleanUpOnline<br>Line: ";
		global $DB;
		$ONLINE_INTERVAL = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));
		$strSql = "
			DELETE FROM b_ticket_online WHERE
				TIMESTAMP_X < DATE_ADD(now(), INTERVAL - $ONLINE_INTERVAL SECOND)
			";
		$DB->Query($strSql,false,$err_mess.__LINE__);
		return "CTicket::CleanUpOnline();";
	}

	function GetOnline($TICKET_ID)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetOnline<br>Line: ";
		global $DB;
		$TICKET_ID = intval($TICKET_ID);
		$ONLINE_INTERVAL = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("max(T.TIMESTAMP_X)")."		TIMESTAMP_X,
				T.USER_ID,
				T.CURRENT_MODE,
				U.EMAIL													USER_EMAIL,
				U.LOGIN													USER_LOGIN,
				concat(ifnull(U.NAME,''),' ',ifnull(U.LAST_NAME,''))	USER_NAME
			FROM
				b_ticket_online T,
				b_user U
			WHERE
				T.TICKET_ID = $TICKET_ID
			and T.TIMESTAMP_X >= DATE_ADD(now(), INTERVAL - $ONLINE_INTERVAL SECOND)
			and U.ID = T.USER_ID
			GROUP BY
				T.USER_ID, U.EMAIL, U.LOGIN, U.NAME, U.LAST_NAME
			ORDER BY 
				T.USER_ID
			";
		//echo "<pre>".$strSql."</pre>";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $z;
	}

	function DeleteMessage($ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: DeleteMessage<br>Line: ";
		global $DB;
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
			$strSql = "
				SELECT 
					F.ID FILE_ID,
					M.TICKET_ID
				FROM
					b_ticket_message M
				LEFT JOIN b_ticket_message_2_file MF ON (MF.MESSAGE_ID = M.ID)
				LEFT JOIN b_file F ON (F.ID = MF.FILE_ID)
				WHERE 
					M.ID='$ID' 
				";
			//echo "<pre>".$strSql."</pre>";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch()) 
			{
				$TICKET_ID = $zr["TICKET_ID"];
				if (intval($zr["FILE_ID"])>0) 
				{
					CFile::Delete($zr["FILE_ID"]);		
				}
			}

			$z = $DB->Query("DELETE FROM b_ticket_message WHERE ID='$ID'", false, $err_mess.__LINE__);
			if (intval($z->AffectedRowsCount())>0)
			{
				CTicket::UpdateLastParams($TICKET_ID);
			}
		}
	}

	function UpdateMessage($MESSAGE_ID, $arFields, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: UpdateMessage<br>Line: ";
		global $DB, $USER;

		$MESSAGE_ID = intval($MESSAGE_ID);
		$bAdmin = "N";
		$bSupportTeam = "N";
		if ($CHECK_RIGHTS=="Y") 
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$uid = $USER->GetID();
		}
		else 
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
			$uid = 0;
		}

		if ($bAdmin=="Y")
		{
			$OWNER_SID = $arFields["OWNER_SID"];
			$OWNER_USER_ID = $arFields["OWNER_USER_ID"];
			$arFields_u = array(
				"TIMESTAMP_X"		=> $DB->GetNowFunction(),
				"C_NUMBER"			=> intval($arFields["C_NUMBER"]),
				"MESSAGE"			=> "'".$DB->ForSql($arFields["MESSAGE"])."'",
				"MESSAGE_SEARCH"	=> "'".ToUpper($DB->ForSql($arFields["MESSAGE"]))."'",
				"SOURCE_ID"			=> (intval($arFields["SOURCE_ID"])>0 ? intval($arFields["SOURCE_ID"]) : "null"),
				"OWNER_SID"			=> "'".$DB->ForSql($OWNER_SID, 255)."'",
				"OWNER_USER_ID"		=> (intval($OWNER_USER_ID)>0 ? intval($OWNER_USER_ID) : "null"),
				"MODIFIED_USER_ID"	=> (intval($uid)>0 ? intval($uid) : "null"),
				"MODIFIED_GUEST_ID"	=> (intval($_SESSION["SESS_GUEST_ID"])>0 ? intval($_SESSION["SESS_GUEST_ID"]) : "null"),
				"EXTERNAL_ID"		=> (intval($arFields["EXTERNAL_ID"])>0 ? intval($arFields["EXTERNAL_ID"]) : "null"),
				"TASK_TIME"		=> (intval($arFields["TASK_TIME"])>0 ? intval($arFields["TASK_TIME"]) : "null"),
				"EXTERNAL_FIELD_1"	=> "'".$DB->ForSql($arFields["EXTERNAL_FIELD_1"])."'",
				"IS_SPAM"			=> (strlen($arFields["IS_SPAM"])>0 ? "'".$arFields["IS_SPAM"]."'" : "null"),
				"IS_HIDDEN"			=> ($arFields["IS_HIDDEN"]=="Y" ? "'Y'" : "'N'"),
				"IS_LOG"			=> ($arFields["IS_LOG"]=="Y" ? "'Y'" : "'N'"),
				"IS_OVERDUE"		=> ($arFields["IS_OVERDUE"]=="Y" ? "'Y'" : "'N'"),
				"NOT_CHANGE_STATUS" => ($arFields["NOT_CHANGE_STATUS"]=="Y" ? "'Y'" : "'N'")
				);

			$NOT_CHANGE_STATUS = (
				is_set($arFields, "NOT_CHANGE_STATUS") && $arFields["NOT_CHANGE_STATUS"]=="Y"
				? "Y"
				: "N"
			);
			//echo "<pre>"; print_r($arFields_u); echo "</pre>";

			$rows = $DB->Update("b_ticket_message",$arFields_u,"WHERE ID='".$MESSAGE_ID."'",$err_mess.__LINE__);
			if (intval($rows)>0)
			{
				$rsMessage = CTicket::GetMessageByID($MESSAGE_ID, $CHECK_RIGHTS);
				if ($arMessage = $rsMessage->Fetch())
				{
					$TICKET_ID = $arMessage["TICKET_ID"];

					// обновим прикрепленные файлы
					$not_image_extension_suffix = COption::GetOptionString("support", "NOT_IMAGE_EXTENSION_SUFFIX");
					$not_image_upload_dir = COption::GetOptionString("support", "NOT_IMAGE_UPLOAD_DIR");
					$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
					
					$arrFiles = $arFields["FILES"];
					if (is_array($arrFiles) && count($arrFiles)>0)
					{
						foreach ($arrFiles as $arFile)
						{
							if (strlen($arFile["name"])>0 || $arFile["del"]=="Y")
							{
								if ($bSupportTeam!="Y" && $bAdmin!="Y") $max_file_size = intval($max_size)*1024;
								$fes = "";
								$upload_dir = "support";
								if (!CFile::IsImage($arFile["name"], $arFile["type"])) 
								{
									$fes = $not_image_extension_suffix;
									$arFile["name"] .= $fes;
									$upload_dir = $not_image_upload_dir;
								}
								$fid = intval(CFile::SaveFile($arFile, $upload_dir, $max_file_size));

								// если стоял флаг "Удалить" то
								if ($arFile["del"]=="Y")
								{
									// удалим связку
									$strSql = "
										DELETE FROM 
											b_ticket_message_2_file 
										WHERE 
											FILE_ID=".intval($arFile["old_file"])." 
										";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}

								// если успешно загрузили файл то
								if ($fid>0) 
								{
									// если это был новый файл то
									if (intval($arFile["old_file"])<=0)
									{
										// добавим связку
										$md5 = md5(uniqid(mt_rand(), true).time());
										$arFields_fi = array(
											"HASH"				=> "'".$DB->ForSql($md5, 255)."'",
											"MESSAGE_ID"		=> $MESSAGE_ID,
											"FILE_ID"			=> $fid,
											"TICKET_ID"			=> $TICKET_ID,
											"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
											);
										$DB->Insert("b_ticket_message_2_file",$arFields_fi, $err_mess.__LINE__);
									}
									else // иначе
									{
										// обновим связку
										$arFields_fu = array(
											"FILE_ID"			=> $fid,
											"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
											);
										$DB->Update("b_ticket_message_2_file", $arFields_fu, "WHERE FILE_ID = ".intval($arFile["old_file"]),$err_mess.__LINE__);
									}
								}
							}
						}
					}
					if ($arFields["IS_SPAM"]=="Y") 
						CTicket::MarkMessageAsSpam($MESSAGE_ID,"Y",$CHECK_RIGHTS);
					elseif ($arFields["IS_SPAM"]=="N") 
						CTicket::MarkMessageAsSpam($MESSAGE_ID,"N",$CHECK_RIGHTS);
					elseif ($arFields["IS_SPAM"]!="Y" && $arFields["IS_SPAM"]!="N") 
						CTicket::UnMarkMessageAsSpam($MESSAGE_ID,$CHECK_RIGHTS);

					//if ($NOT_CHANGE_STATUS != "Y")
					CTicket::UpdateLastParams($TICKET_ID);
					if ($NOT_CHANGE_STATUS!="Y" && $HIDDEN!="Y" && $LOG!="Y")
					{
						CTicketReminder::Update($TICKET_ID);
					}
				}
			}
		}
	}

	function AddMessage($TICKET_ID, $arFields, &$arrFILES, $CHECK_RIGHTS="Y")
	{
		if (strlen($arFields["MESSAGE"])>0 || (is_array($arFields["FILES"]) && count($arFields["FILES"])>0))
		{
			$err_mess = (CTicket::err_mess())."<br>Function: AddMessage<br>Line: ";
			global $DB, $USER;

			$bAdmin = "N";
			$bSupportTeam = "N";
			$bSupportClient = "N";
			if ($CHECK_RIGHTS=="Y") 
			{
				$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N"; 
				$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
				$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
				$uid = intval($USER->GetID());
			}
			else 
			{
				$bAdmin = "Y";
				$bSupportTeam = "Y";
				$bSupportClient = "Y";
				//if (is_object($USER)) $uid = intval($USER->GetID()); else $uid = -1;
				$uid = 0;
			}
			if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y") return false;

			$TICKET_ID = intval($TICKET_ID);
			if ($TICKET_ID<=0) return 0;

			$strSql = "SELECT RESPONSIBLE_USER_ID, LAST_MESSAGE_USER_ID, REOPEN FROM b_ticket WHERE ID='$TICKET_ID'";
			$rsTicket = $DB->Query($strSql, false, $err_mess.__LINE__);
			$arTicket = $rsTicket->Fetch();
			$CURRENT_RESPONSIBLE_USER_ID = $arTicket["RESPONSIBLE_USER_ID"];			
			
			$strSql = "SELECT max(C_NUMBER) MAX_NUMBER FROM b_ticket_message WHERE TICKET_ID='$TICKET_ID'";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			$zr = $z->Fetch();
			$MAX_NUMBER = intval($zr['MAX_NUMBER']);

			if ((strlen(trim($arFields["MESSAGE_AUTHOR_SID"]))>0 || intval($arFields["MESSAGE_AUTHOR_USER_ID"])>0 || intval($arFields["MESSAGE_CREATED_USER_ID"])>0) && ($bSupportTeam=="Y" || $bAdmin=="Y"))
			{
				$OWNER_USER_ID = intval($arFields["MESSAGE_AUTHOR_USER_ID"]);
				$OWNER_SID = "'".$DB->ForSql($arFields["MESSAGE_AUTHOR_SID"],2000)."'";
				$OWNER_GUEST_ID = intval($arFields["MESSAGE_AUTHOR_GUEST_ID"])>0 ? intval($arFields["MESSAGE_AUTHOR_GUEST_ID"]) : "null";

				$CREATED_USER_ID = intval($arFields["MESSAGE_CREATED_USER_ID"])>0 ? intval($arFields["MESSAGE_CREATED_USER_ID"]) : intval($uid);
				$CREATED_GUEST_ID = intval($arFields["MESSAGE_CREATED_GUEST_ID"])>0 ? intval($arFields["MESSAGE_CREATED_GUEST_ID"]) : intval($_SESSION["SESS_GUEST_ID"]);
			}
			else
			{
				$OWNER_USER_ID = intval($uid);
				$OWNER_SID = "null";
				$OWNER_GUEST_ID = intval($_SESSION["SESS_GUEST_ID"]);

				$CREATED_USER_ID = intval($uid);
				$CREATED_GUEST_ID = intval($_SESSION["SESS_GUEST_ID"]);				
			}

			if (intval($OWNER_GUEST_ID)<=0) $OWNER_GUEST_ID = "null";
			
			$MESSAGE_BY_SUPPORT_TEAM = "null";
			if ($OWNER_USER_ID<=0) $OWNER_USER_ID = "null";
			else
			{
				$MESSAGE_BY_SUPPORT_TEAM = "'N'";
				if (CTicket::IsSupportTeam($OWNER_USER_ID) || CTicket::IsAdmin($OWNER_USER_ID))
				{
					$MESSAGE_BY_SUPPORT_TEAM = "'Y'";
				}
			}

			if ($CREATED_USER_ID<=0) $CREATED_USER_ID = "null";
			if (intval($CREATED_GUEST_ID)<=0) $CREATED_GUEST_ID = "null";

			$CREATED_MODULE_NAME = (strlen($arFields["MESSAGE_CREATED_MODULE_NAME"])>0) ? "'".$DB->ForSql($arFields["MESSAGE_CREATED_MODULE_NAME"],255)."'" : "'support'";

			$EXTERNAL_ID = intval($arFields["EXTERNAL_ID"])>0 ? intval($arFields["EXTERNAL_ID"]) : "null";
			$EXTERNAL_FIELD_1 = $arFields["EXTERNAL_FIELD_1"];

			if (is_set($arFields, "HIDDEN")) $HIDDEN = ($arFields["HIDDEN"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_HIDDEN")) $HIDDEN = ($arFields["IS_HIDDEN"]=="Y") ? "Y" : "N";
			$HIDDEN = ($HIDDEN=="Y") ? "Y" : "N";

			$NOT_CHANGE_STATUS = (
				is_set($arFields, "NOT_CHANGE_STATUS") && $arFields["NOT_CHANGE_STATUS"]=="Y"
				? "Y"
				: "N"
			);

			$CHANGE_LAST_MESSAGE_DATE = true;
			if ($arTicket["LAST_MESSAGE_USER_ID"] == $uid && $arTicket["REOPEN"] != "Y")
				$CHANGE_LAST_MESSAGE_DATE = false;

			$TASK_TIME = intval($arFields["TASK_TIME"])>0 ? intval($arFields["TASK_TIME"]) : "null";

			if (is_set($arFields, "LOG")) $LOG = ($arFields["LOG"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_LOG")) $LOG = ($arFields["IS_LOG"]=="Y") ? "Y" : "N";
			$LOG = ($LOG=="Y") ? "Y" : "N";

			if (is_set($arFields, "OVERDUE")) $OVERDUE = ($arFields["OVERDUE"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_OVERDUE")) $OVERDUE = ($arFields["IS_OVERDUE"]=="Y") ? "Y" : "N";
			$OVERDUE = ($OVERDUE=="Y") ? "Y" : "N";

			$arFields_i = array(
				"TIMESTAMP_X"					=> $DB->GetNowFunction(),
				"DATE_CREATE"					=> $DB->GetNowFunction(),
				"DAY_CREATE"					=> $DB->CurrentDateFunction(),
				"C_NUMBER"						=> $MAX_NUMBER + 1,
				"TICKET_ID"						=> $TICKET_ID,
				"IS_HIDDEN"						=> "'".$HIDDEN."'",
				"IS_LOG"						=> "'".$LOG."'",
				"IS_OVERDUE"					=> "'".$OVERDUE."'",
				"MESSAGE"						=> "'".$DB->ForSql($arFields["MESSAGE"])."'",
				"MESSAGE_SEARCH"				=> "'".$DB->ForSql(ToUpper($arFields["MESSAGE"]))."'",
				"EXTERNAL_ID"					=> $EXTERNAL_ID,
				"EXTERNAL_FIELD_1"				=> (strlen($EXTERNAL_FIELD_1)>0 ? "'".$DB->ForSql($EXTERNAL_FIELD_1)."'" : "null"),
				"OWNER_USER_ID"					=> $OWNER_USER_ID,
				"OWNER_GUEST_ID"				=> $OWNER_GUEST_ID,
				"OWNER_SID"						=> $OWNER_SID,
				"SOURCE_ID"						=> intval($arFields["MESSAGE_SOURCE_ID"]),
				"CREATED_USER_ID"				=> $CREATED_USER_ID,
				"CREATED_GUEST_ID"				=> $CREATED_GUEST_ID,
				"CREATED_MODULE_NAME"			=> $CREATED_MODULE_NAME,
				"MODIFIED_USER_ID"				=> $CREATED_USER_ID,
				"MODIFIED_GUEST_ID"				=> $CREATED_GUEST_ID,
				"MESSAGE_BY_SUPPORT_TEAM"		=> $MESSAGE_BY_SUPPORT_TEAM,
				"TASK_TIME" => $TASK_TIME,
				"NOT_CHANGE_STATUS" => "'".$NOT_CHANGE_STATUS."'"
				);

			if ($HIDDEN!="Y" && $LOG!="Y" && $CHANGE_LAST_MESSAGE_DATE == false)
			{
				if ($MESSAGE_BY_SUPPORT_TEAM == "'Y'" || ($MAX_NUMBER <= 0 && array_key_exists('SOURCE_SID', $arFields) && $arFields['SOURCE_SID'] === 'email'))
					$arFields_i["NOT_CHANGE_STATUS"] = "'N'";
				else
					$arFields_i["NOT_CHANGE_STATUS"] = "'Y'";
			}

			if (intval($CURRENT_RESPONSIBLE_USER_ID)>0) $arFields_i["CURRENT_RESPONSIBLE_USER_ID"] = $CURRENT_RESPONSIBLE_USER_ID;

			//echo "<pre>";print_r($arFields_i);echo "</pre>";

			$MID = $DB->Insert("b_ticket_message",$arFields_i, $err_mess.__LINE__);
			if (intval($MID)>0)
			{
				$not_image_extension_suffix = COption::GetOptionString("support", "NOT_IMAGE_EXTENSION_SUFFIX");
				$not_image_upload_dir = COption::GetOptionString("support", "NOT_IMAGE_UPLOAD_DIR");
				$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
				// сохраняем приаттаченные файлы
				$arFILES = $arFields["FILES"];
				if (is_array($arFILES) && count($arFILES)>0)
				{
					while (list($key, $arFILE) = each($arFILES))
					{
						if (strlen($arFILE["name"])>0) 
						{
							if ($bSupportTeam!="Y" && $bAdmin!="Y") $max_file_size = intval($max_size)*1024;
							$fes = "";
							$upload_dir = "support";
							if (!CFile::IsImage($arFILE["name"], $arFILE["type"])) 
							{
								$fes = $not_image_extension_suffix;
								$arFILE["name"] .= $fes;
								$upload_dir = $not_image_upload_dir;
							}
							$fid = intval(CFile::SaveFile($arFILE, $upload_dir, $max_file_size));
							if ($fid>0) 
							{
								$md5 = md5(uniqid(mt_rand(), true).time());
								$arFILE["HASH"] = $md5;
								$arFILE["FILE_ID"] = $fid;
								$arFILE["MESSAGE_ID"] = $MID;
								$arFILE["TICKET_ID"] = $TICKET_ID;
								$arFILE["EXTENSION_SUFFIX"] = $fes;
								$arFields_fi = array(
									"HASH"				=> "'".$DB->ForSql($md5, 255)."'",
									"MESSAGE_ID"		=> $MID,
									"FILE_ID"			=> $fid,
									"TICKET_ID"			=> $TICKET_ID,
									"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
									);
								$link_id = $DB->Insert("b_ticket_message_2_file",$arFields_fi, $err_mess.__LINE__);
								if (intval($link_id)>0)
								{
									$arFILE["LINK_ID"] = $link_id;
									$arrFILES[] = $arFILE;
								}
							}
						}
					}
				}

				// если это не было скрытым сообщением или сообщение лога, то
				if ($NOT_CHANGE_STATUS!="Y" && $HIDDEN!="Y" && $LOG!="Y")
				{
					// обновим ряд параметров обращения
					if (!isset($arFields["AUTO_CLOSE_DAYS"])) $RESET_AUTO_CLOSE = "Y";

					CTicket::UpdateLastParams($TICKET_ID, $RESET_AUTO_CLOSE, $CHANGE_LAST_MESSAGE_DATE);

					// при необходимости создадим или удалим агенты-напоминальщики
					CTicketReminder::Update($TICKET_ID);
				}

				//если была установлена галочка "не изменять статус обращени" - пересчитаем количество собщений
				if ($NOT_CHANGE_STATUS == "Y" || $HIDDEN == "Y")
					CTicket::UpdateMessages($TICKET_ID);
			}
		}
		return $MID;
	}

	function GetStatus($TICKET_ID)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetStatus<br>Line: ";
		global $DB, $USER;

		$TICKET_ID = intval($TICKET_ID);
		if ($TICKET_ID<=0) return false;

		$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N"; 
		$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
		$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
		$uid = intval($USER->GetID());

		if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
		{
			$lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(ifnull(T.LAST_MESSAGE_USER_ID,0)='$uid', 'green',
						if(ifnull(T.OWNER_USER_ID,0)='$uid', 'red',						
							if(T.LAST_MESSAGE_BY_SUPPORT_TEAM='Y','green_s',
								if(ifnull(T.RESPONSIBLE_USER_ID,0)='$uid', 'red', 
									'yellow')))))
				";
		}
		else
		{
			$lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(ifnull(T.LAST_MESSAGE_USER_ID,0)='$uid', 'green', 'red'))
				";
		}

		$strSql = "
			SELECT 
				$lamp	LAMP
			FROM 
				b_ticket T
			WHERE 
				ID = $TICKET_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch()) return $ar["LAMP"];

		return false;
	}

	function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $CHECK_RIGHTS="Y", $get_user_name="Y", $get_extra_names="Y", $site_id=false)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER;

		$bAdmin = 'N';
		$bSupportTeam = 'N';
		$bSupportClient = 'N';
		$bDemo = 'N';
		if ($CHECK_RIGHTS=='Y') 
		{
			$bAdmin = (CTicket::IsAdmin()) ? 'Y' : 'N'; 
			$bSupportTeam = (CTicket::IsSupportTeam()) ? 'Y' : 'N';
			$bSupportClient = (CTicket::IsSupportClient()) ? 'Y' : 'N';
			$bDemo = (CTicket::IsDemo()) ? 'Y' : 'N';
			$uid = intval($USER->GetID());
		}
		else 
		{
			$bAdmin = 'Y';
			$bSupportTeam = 'Y';
			$bSupportClient = 'Y';
			$bDemo = 'Y';
			if (is_object($USER)) $uid = intval($USER->GetID()); else $uid = -1;
		}
		if ($bAdmin!='Y' && $bSupportTeam!='Y' && $bSupportClient!='Y' && $bDemo!='Y') return false;

		if ($bSupportTeam=='Y' || $bAdmin=='Y' || $bDemo=='Y')
		{
			$lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(ifnull(T.LAST_MESSAGE_USER_ID,0)='$uid', 'green',
						if(ifnull(T.OWNER_USER_ID,0)='$uid', 'red',						
							if(T.LAST_MESSAGE_BY_SUPPORT_TEAM='Y','green_s',
								if(ifnull(T.RESPONSIBLE_USER_ID,0)='$uid', 'red', 
									'yellow')))))
				";
		}
		else
		{
			$lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(ifnull(T.LAST_MESSAGE_USER_ID,0)='$uid', 'green', 'red'))
				";
		}
		$bJoinSupportTeamTbl = $bJoinClientTbl = false;

		$arSqlSearch = Array();
		$strSqlSearch = "";
		//echo "<pre>"; print_R($arFilter); echo "</pre>";
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
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.ID",$val,$match);
						break;
					case "HOLD_ON":
						$arSqlSearch[] = ($val=="Y") ? "T.HOLD_ON='Y'" : "T.HOLD_ON = 'N'";
						break;

					case "LID":
					case "SITE":
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SITE_ID",$val,$match);
						break;
					case "LAMP":
						if (is_array($val))
						{
							if (count($val)>0)
							{
								foreach ($val as $value) $str .= ", '".$DB->ForSQL($value)."'";
								$str = TrimEx($str, ",");
								$arSqlSearch[] = " ".$lamp." in (".$str.")";
							}
						}
						elseif (strlen($val)>0)
						{
							$arSqlSearch[] = " ".$lamp." = '".$DB->ForSQL($val)."'";
						}
						break;
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "DATE_TIMESTAMP_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.TIMESTAMP_X>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_TIMESTAMP_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.TIMESTAMP_X<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "DATE_CLOSE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CLOSE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CLOSE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CLOSE<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "CLOSE":
						$arSqlSearch[] = ($val=="Y") ? "T.DATE_CLOSE is not null" : "T.DATE_CLOSE is null";
						break;
					case "AUTO_CLOSE_DAYS1":
						$arSqlSearch[] = "T.AUTO_CLOSE_DAYS>='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS2":
						$arSqlSearch[] = "T.AUTO_CLOSE_DAYS<='".intval($val)."'";
						break;
					case "TICKET_TIME_1":
						$arSqlSearch[] = "UNIX_TIMESTAMP(T.DATE_CLOSE) - UNIX_TIMESTAMP(T.DATE_CREATE)>='".(intval($val)*86400)."'";
						break;
					case "TICKET_TIME_2":
						$arSqlSearch[] = "UNIX_TIMESTAMP(T.DATE_CLOSE) - UNIX_TIMESTAMP(T.DATE_CREATE)<='".(intval($val)*86400)."'";
						break;
					case "TITLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.TITLE", $val, $match);
						break;
					case "MESSAGES1":
						$arSqlSearch[] = "T.MESSAGES>='".intval($val)."'";
						break;
					case "MESSAGES2":
						$arSqlSearch[] = "T.MESSAGES<='".intval($val)."'";
						break;

					case "PROBLEM_TIME1":
						$arSqlSearch[] = "T.PROBLEM_TIME>='".intval($val)."'";
						break;
					case "PROBLEM_TIME2":
						$arSqlSearch[] = "T.PROBLEM_TIME<='".intval($val)."'";
						break;

					case "OVERDUE_MESSAGES1":
						$arSqlSearch[] = "T.OVERDUE_MESSAGES>='".intval($val)."'";
						break;
					case "OVERDUE_MESSAGES2":
						$arSqlSearch[] = "T.OVERDUE_MESSAGES<='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS_LEFT1":
						$arSqlSearch[] = "TO_DAYS(ADDDATE(T.TIMESTAMP_X, INTERVAL T.AUTO_CLOSE_DAYS DAY))-TO_DAYS(now())>='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS_LEFT2":
						$arSqlSearch[] = "TO_DAYS(ADDDATE(T.TIMESTAMP_X, INTERVAL T.AUTO_CLOSE_DAYS DAY))-TO_DAYS(now())<='".intval($val)."'";
						break;
					case "OWNER":
						$get_user_name = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.OWNER_USER_ID, UO.LOGIN, UO.LAST_NAME, UO.NAME, T.OWNER_SID", $val, $match, array("@", "."));
						break;
					case "OWNER_USER_ID":
					case "OWNER_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CREATED_BY":
						$get_user_name = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.CREATED_USER_ID, UC.LOGIN, UC.LAST_NAME, UC.NAME, T.CREATED_MODULE_NAME", $val, $match);
						break;
					case "RESPONSIBLE":
						$get_user_name = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CATEGORY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DC.SID", $val, $match);
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "CRITICALITY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DK.SID", $val, $match);
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "STATUS_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DS.SID", $val, $match);
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "MARK_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DM.SID", $val, $match);
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;
					case "SOURCE_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DSR.SID", $val, $match);
						break;

					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
					case "DIFFICULTY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DD.SID", $val, $match);
						break;



					case "MODIFIED_BY":
						$get_user_name = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.MODIFIED_USER_ID, T.MODIFIED_MODULE_NAME, UM.LOGIN, UM.LAST_NAME, UM.NAME", $val, $match);
						break;
					case "MESSAGE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("M.MESSAGE_SEARCH", ToUpper($val), $match);
						if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
						{
							$mess_join = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID)";
						}
						else
						{
							$mess_join = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID and M.IS_HIDDEN='N' and M.IS_LOG='N')";
						}
						break;
					case "LAST_MESSAGE_USER_ID":
					case "LAST_MESSAGE_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;
					case "SUPPORT_COMMENTS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.SUPPORT_COMMENTS", $val, $match);
						break;
					case "IS_SPAM":
						$arSqlSearch[] = ($val=="Y") ? "T.IS_SPAM ='Y'" : "(T.IS_SPAM = 'N' or T.IS_SPAM is null)";
						break;
					case "IS_SPAM_MAYBE":
						$arSqlSearch[] = ($val=="Y") ? "T.IS_SPAM='N'" : "(T.IS_SPAM='Y' or T.IS_SPAM is null)";
						break;
					
					case 'SUPPORTTEAM_GROUP_ID':
					case 'CLIENT_GROUP_ID':
						if ($key == 'SUPPORTTEAM_GROUP_ID')
						{
							$table = 'UGS';
							$bJoinSupportTeamTbl = true;
						}
						else 
						{
							$table = 'UGC';
							$bJoinClientTbl = true;
						}
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$val = array_unique($val);
							$val = array_filter($val);
							if (count($val) > 0)
							{
								$arSqlSearch[] = '('.$table.'.GROUP_ID IS NOT NULL AND '.$table.'.GROUP_ID IN ('.implode(',', $val).'))';
							}
						}
						else 
						{
							$val = intval($val);
							if ($val > 0)
							{
								$arSqlSearch[] = '('.$table.'.GROUP_ID IS NOT NULL AND '.$table.'.GROUP_ID=\''.$val.'\')';
							}
						}
						break;
					case 'COUPON':
						$match = ($match_value_set && $arFilter[$key."_EXACT_MATCH"]!="Y") ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id")								$strSqlOrder = "ORDER BY T.ID";
		elseif ($by == "s_last_message_date")		$strSqlOrder = "ORDER BY T.LAST_MESSAGE_DATE";
		elseif ($by == "s_site_id" || $by == "s_lid")	$strSqlOrder = "ORDER BY T.SITE_ID";
		elseif ($by == "s_lamp")						$strSqlOrder = "ORDER BY LAMP";
		elseif ($by == "s_is_overdue")					$strSqlOrder = "ORDER BY T.IS_OVERDUE";
		elseif ($by == "s_is_notified")					$strSqlOrder = "ORDER BY T.IS_NOTIFIED";		
		elseif ($by == "s_date_create")					$strSqlOrder = "ORDER BY T.DATE_CREATE";
		elseif ($by == "s_timestamp")					$strSqlOrder = "ORDER BY T.TIMESTAMP_X";
		elseif ($by == "s_date_close")					$strSqlOrder = "ORDER BY T.DATE_CLOSE";
		elseif ($by == "s_owner")						$strSqlOrder = "ORDER BY T.OWNER_USER_ID";
		elseif ($by == "s_modified_by")					$strSqlOrder = "ORDER BY T.MODIFIED_USER_ID";
		elseif ($by == "s_title")						$strSqlOrder = "ORDER BY T.TITLE ";
		elseif ($by == "s_responsible")					$strSqlOrder = "ORDER BY T.RESPONSIBLE_USER_ID";
		elseif ($by == "s_messages")					$strSqlOrder = "ORDER BY T.MESSAGES";
		elseif ($by == "s_category")					$strSqlOrder = "ORDER BY T.CATEGORY_ID";
		elseif ($by == "s_criticality")					$strSqlOrder = "ORDER BY T.CRITICALITY_ID";
		elseif ($by == "s_sla")							$strSqlOrder = "ORDER BY T.SLA_ID";
		elseif ($by == "s_status")						$strSqlOrder = "ORDER BY T.STATUS_ID";
		elseif ($by == "s_difficulty")					$strSqlOrder = "ORDER BY T.DIFFICULTY_ID";
		elseif ($by == "s_problem_time")				$strSqlOrder = "ORDER BY T.PROBLEM_TIME";
		elseif ($by == "s_mark")						$strSqlOrder = "ORDER BY T.MARK_ID";
		elseif ($by == "s_online")						$strSqlOrder = "ORDER BY USERS_ONLINE";
		elseif ($by == "s_support_comments")			$strSqlOrder = "ORDER BY T.SUPPORT_COMMENTS";
		elseif ($by == "s_auto_close_days_left")		$strSqlOrder = "ORDER BY AUTO_CLOSE_DAYS_LEFT";
		elseif ($by == 's_coupon')						$strSqlOrder = 'ORDER BY T.COUPON';
		else 
		{
			$by = "s_default";
			$strSqlOrder = "ORDER BY IS_SUPER_TICKET DESC, T.IS_OVERDUE DESC, T.IS_NOTIFIED DESC, T.LAST_MESSAGE_DATE";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		if ($get_user_name=="Y")
		{
			$u_select = "
				,
				UO.LOGIN													OWNER_LOGIN,
				UO.EMAIL													OWNER_EMAIL,
				concat(ifnull(UO.NAME,''),' ',ifnull(UO.LAST_NAME,''))		OWNER_NAME,
				UR.LOGIN													RESPONSIBLE_LOGIN,
				UR.EMAIL													RESPONSIBLE_EMAIL,
				concat(ifnull(UR.NAME,''),' ',ifnull(UR.LAST_NAME,''))		RESPONSIBLE_NAME,
				UM.LOGIN													MODIFIED_BY_LOGIN,
				UM.EMAIL													MODIFIED_BY_EMAIL,
				concat(ifnull(UM.NAME,''),' ',ifnull(UM.LAST_NAME,''))		MODIFIED_BY_NAME,
				UM.LOGIN													MODIFIED_LOGIN,
				UM.EMAIL													MODIFIED_EMAIL,
				concat(ifnull(UM.NAME,''),' ',ifnull(UM.LAST_NAME,''))		MODIFIED_NAME,
				UL.LOGIN													LAST_MESSAGE_LOGIN,
				UL.EMAIL													LAST_MESSAGE_EMAIL,
				concat(ifnull(UL.NAME,''),' ',ifnull(UL.LAST_NAME,''))		LAST_MESSAGE_NAME,
				UC.LOGIN													CREATED_LOGIN,
				UC.EMAIL													CREATED_EMAIL,
				concat(ifnull(UC.NAME,''),' ',ifnull(UC.LAST_NAME,''))		CREATED_NAME
			";
			$u_join = "
			LEFT JOIN b_user UO ON (UO.ID = T.OWNER_USER_ID)
			LEFT JOIN b_user UR ON (UR.ID = T.RESPONSIBLE_USER_ID)
			LEFT JOIN b_user UM ON (UM.ID = T.MODIFIED_USER_ID)
			LEFT JOIN b_user UL ON (UL.ID = T.LAST_MESSAGE_USER_ID)
			LEFT JOIN b_user UC ON (UC.ID = T.CREATED_USER_ID)
			";
		}
		if ($get_extra_names=="Y")
		{
			$d_select = "
				,
				DC.NAME														CATEGORY_NAME,
				DC.DESCR													CATEGORY_DESC,
				DC.SID														CATEGORY_SID,
				DK.NAME														CRITICALITY_NAME,
				DK.DESCR													CRITICALITY_DESC,
				DK.SID														CRITICALITY_SID,
				DS.NAME														STATUS_NAME,
				DS.DESCR													STATUS_DESC,
				DS.SID														STATUS_SID,
				DM.NAME													MARK_NAME,
				DM.DESCR													MARK_DESC,
				DM.SID														MARK_SID,
				DSR.NAME													SOURCE_NAME,
				DSR.DESCR													SOURCE_DESC,
				DSR.SID														SOURCE_SID,
				DD.NAME													DIFFICULTY_NAME,
				DD.DESCR													DIFFICULTY_DESC,
				DD.SID														DIFFICULTY_SID,
				SLA.NAME													SLA_NAME
			";
			$d_join = "
			LEFT JOIN b_ticket_dictionary DC ON (DC.ID = T.CATEGORY_ID and DC.C_TYPE = 'C')
			LEFT JOIN b_ticket_dictionary DK ON (DK.ID = T.CRITICALITY_ID and DK.C_TYPE = 'K')
			LEFT JOIN b_ticket_dictionary DS ON (DS.ID = T.STATUS_ID and DS.C_TYPE = 'S')
			LEFT JOIN b_ticket_dictionary DM ON (DM.ID = T.MARK_ID and DM.C_TYPE = 'M')
			LEFT JOIN b_ticket_dictionary DSR ON (DSR.ID = T.SOURCE_ID and DSR.C_TYPE = 'SR')
			LEFT JOIN b_ticket_dictionary DD ON (DD.ID = T.DIFFICULTY_ID and DD.C_TYPE = 'D')
			LEFT JOIN b_ticket_sla SLA ON (SLA.ID = T.SLA_ID)
			";
		}
		if (strlen($site_id)>0)
		{
			$dates_select = "
				".$DB->DateToCharFunction("T.DATE_CREATE","FULL",$site_id,true)."	DATE_CREATE,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","FULL",$site_id,true)."	TIMESTAMP_X,
				".$DB->DateToCharFunction("T.LAST_MESSAGE_DATE","FULL",$site_id,true)."	LAST_MESSAGE_DATE,
				".$DB->DateToCharFunction("T.DATE_CLOSE","FULL",$site_id,true)."	DATE_CLOSE,
				".$DB->DateToCharFunction("T.DATE_CREATE","SHORT",$site_id,true)."	DATE_CREATE_SHORT,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","SHORT",$site_id,true)."	TIMESTAMP_X_SHORT,
				".$DB->DateToCharFunction("T.DATE_CLOSE","SHORT",$site_id,true)."	DATE_CLOSE_SHORT,
				".$DB->DateToCharFunction("ADDDATE(T.TIMESTAMP_X, INTERVAL T.AUTO_CLOSE_DAYS DAY)","FULL",$site_id,true)."	AUTO_CLOSE_DATE
			";
		}
		else
		{
			$dates_select = "
				".$DB->DateToCharFunction("T.DATE_CREATE","FULL")."		DATE_CREATE,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","FULL")."		TIMESTAMP_X,
				".$DB->DateToCharFunction("T.LAST_MESSAGE_DATE","FULL")."	LAST_MESSAGE_DATE,
				".$DB->DateToCharFunction("T.DATE_CLOSE","FULL")."		DATE_CLOSE,
				".$DB->DateToCharFunction("T.DATE_CREATE","SHORT")."	DATE_CREATE_SHORT,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","SHORT")."	TIMESTAMP_X_SHORT,
				".$DB->DateToCharFunction("T.DATE_CLOSE","SHORT")."		DATE_CLOSE_SHORT,
				".$DB->DateToCharFunction("ADDDATE(T.TIMESTAMP_X, INTERVAL T.AUTO_CLOSE_DAYS DAY)","FULL")."	AUTO_CLOSE_DATE
			";
		}
		
		$ugroup_join = '';
		$strSqlSearchUser = '';
		
		if ($bJoinSupportTeamTbl || (!($bAdmin == 'Y' || $bDemo == 'Y') && $bSupportTeam == 'Y'))
		{
			$ugroup_join .= "
				LEFT JOIN b_ticket_user_ugroup UGS ON (UGS.USER_ID = T.RESPONSIBLE_USER_ID) ";
		}
		if ($bJoinClientTbl || (!($bAdmin == 'Y' || $bDemo == 'Y') && $bSupportClient == 'Y'))
		{
			$ugroup_join .= "
				LEFT JOIN b_ticket_user_ugroup UGC ON (UGC.USER_ID = T.OWNER_USER_ID) ";
		}
		
		if (!($bAdmin == 'Y' || $bDemo == 'Y'))
		{
			$strSqlSearchUser = "(T.OWNER_USER_ID='$uid' OR T.RESPONSIBLE_USER_ID='$uid'";
			
			if($bSupportTeam == 'Y')
			{
				$strSqlSearchUser .= " OR (UGS2.USER_ID IS NOT NULL AND UGS2.USER_ID='$uid' AND UUS.IS_TEAM_GROUP IS NOT NULL AND UUS.IS_TEAM_GROUP='Y')";
				$ugroup_join .= "
					LEFT JOIN b_ticket_user_ugroup UGS2 ON (UGS2.GROUP_ID = UGS.GROUP_ID AND UGS2.CAN_VIEW_GROUP_MESSAGES = 'Y') 
					LEFT JOIN b_ticket_ugroups UUS ON (UUS.ID = UGS.GROUP_ID) ";
			}
			elseif ($bSupportClient == 'Y')
			{
				$strSqlSearchUser .= " OR (UGC2.USER_ID IS NOT NULL AND UGC2.USER_ID='$uid' AND UUC.IS_TEAM_GROUP IS NOT NULL AND UUC.IS_TEAM_GROUP<>'Y')";
				$ugroup_join .= "
					LEFT JOIN b_ticket_user_ugroup UGC2 ON (UGC2.GROUP_ID = UGC.GROUP_ID AND UGC2.CAN_VIEW_GROUP_MESSAGES = 'Y') 
					LEFT JOIN b_ticket_ugroups UUC ON (UUC.ID = UGC.GROUP_ID) ";
			}
			
			$strSqlSearchUser .= ')';
			$arSqlSearch[] = $strSqlSearchUser;
		}
		
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$ONLINE_INTERVAL = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));
		
		$strSql = "
			SELECT 
				T.*,
				T.SITE_ID,
				T.SITE_ID																			LID,
				$dates_select,
				UNIX_TIMESTAMP(T.DATE_CLOSE)-UNIX_TIMESTAMP(T.DATE_CREATE)							TICKET_TIME,				
				TO_DAYS(ADDDATE(T.TIMESTAMP_X, INTERVAL T.AUTO_CLOSE_DAYS DAY))-TO_DAYS(now())		AUTO_CLOSE_DAYS_LEFT,
				count(distinct TN.USER_ID)															USERS_ONLINE,				
				if(T.COUPON IS NOT NULL, 1, 0)														IS_SUPER_TICKET,
				$lamp																				LAMP
				$d_select
				$u_select
			FROM 
				b_ticket T
			LEFT JOIN b_ticket_online TN ON (TN.TICKET_ID = T.ID and TN.TIMESTAMP_X >= DATE_ADD(now(), INTERVAL - $ONLINE_INTERVAL SECOND))
			$u_join
			$d_join
			$mess_join
			$ugroup_join
			WHERE 
			$strSqlSearch
			GROUP BY
				T.ID
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	function GetSupportTeamList()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetSupportTeamList<br>Line: ";
		global $DB;
		$arrGid = CTicket::GetSupportTeamGroups();
		$arrAid = CTicket::GetAdminGroups();
		if (count($arrGid)>0) $gid = implode(",",$arrGid); else $gid = 0;
		if (count($arrAid)>0) $aid = implode(",",$arrAid); else $aid = 0;
		$strSql = "
			SELECT DISTINCT
				U.ID as REFERENCE_ID,
				concat('[',U.ID,'] ',' (',U.LOGIN,') ',ifnull(U.NAME,''),' ',ifnull(U.LAST_NAME,'')) as REFERENCE, 
				U.ACTIVE
			FROM 
				b_user U,
				b_user_group G
			WHERE
				U.ID = G.USER_ID
			and G.GROUP_ID in ($gid, $aid)
			ORDER BY
				U.ID
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
	
	function GetResponsibleList($user_id)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetSupportTeamMailList<br>Line: ";
		global $DB;

		$strSql = "
			SELECT DISTINCT
				U.ID as ID,
				U.LOGIN as LOGIN,
				concat('[',U.ID,'] ',' (',U.LOGIN,') ',ifnull(U.NAME,''),' ',ifnull(U.LAST_NAME,'')) as NAME,
				U.EMAIL as EMAIL
			FROM 
				b_user U,
				b_ticket_user_ugroup TUG,
				b_ticket_user_ugroup TUG2
			WHERE
			    TUG.USER_ID = '".intval($user_id)."'
			and TUG2.GROUP_ID = TUG.GROUP_ID
			and U.ID = TUG2.USER_ID
			and TUG2.CAN_MAIL_GROUP_MESSAGES = 'Y'
			ORDER BY
				U.ID
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function GetMessageList(&$by, &$order, $arFilter=Array(), &$is_filtered, $CHECK_RIGHTS="Y", $get_user_name="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetMessageList<br>Line: ";
		global $DB, $USER, $APPLICATION;

		$bAdmin = "N";
		$bSupportTeam = "N";
		$bSupportClient = "N";
		$bDemo = "N";
		if ($CHECK_RIGHTS=="Y") 
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
			$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
			$uid = intval($USER->GetID());
		}
		else 
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
			$bSupportClient = "Y";
			$bDemo = "Y";
			$uid = 0;
		}
		if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y" && $bDemo!="Y") return false;

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
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("M.ID",$val,$match);
						break;
					case "TICKET_ID":
						$arSqlSearch[] = "M.TICKET_ID = ".intval($val);
						break;
					case "TICKET":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("M.TICKET_ID",$val,$match);
						break;
					case "IS_MESSAGE":
						$arSqlSearch[] = ($val=="Y") ? "(M.IS_HIDDEN = 'N' and M.IS_LOG='N' and M.IS_OVERDUE='N')" : "(M.IS_HIDDEN = 'Y' or M.IS_LOG='Y' or M.IS_OVERDUE='Y')";
						break;
					case "IS_HIDDEN":
					case "IS_LOG":
					case "IS_OVERDUE":
					case "NOT_CHANGE_STATUS":
					case "MESSAGE_BY_SUPPORT_TEAM":
						$arSqlSearch[] = ($val=="Y") ? "M.".$key."='Y'" : "M.".$key."='N'";
						break;
					case "EXTERNAL_FIELD_1":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("M.EXTERNAL_FIELD_1", $val, $match);
						break;						
				}
			}
		}
		if ($get_user_name=="Y")
		{
			$u_select = "
				,
				UO.EMAIL												OWNER_EMAIL,
				UO.LOGIN												OWNER_LOGIN,
				concat(ifnull(UO.NAME,''),' ',ifnull(UO.LAST_NAME,''))	OWNER_NAME,
				UO.LOGIN												LOGIN,
				concat(ifnull(UO.NAME,''),' ',ifnull(UO.LAST_NAME,''))	NAME,
				UC.EMAIL												CREATED_EMAIL,
				UC.LOGIN												CREATED_LOGIN,
				concat(ifnull(UC.NAME,''),' ',ifnull(UC.LAST_NAME,''))	CREATED_NAME,
				UM.EMAIL												MODIFIED_EMAIL,
				UM.LOGIN												MODIFIED_LOGIN,
				concat(ifnull(UM.NAME,''),' ',ifnull(UM.LAST_NAME,''))	MODIFIED_NAME
				";
			$u_join = "
			LEFT JOIN b_user UO ON (UO.ID = M.OWNER_USER_ID)
			LEFT JOIN b_user UC ON (UC.ID = M.CREATED_USER_ID)
			LEFT JOIN b_user UM ON (UM.ID = M.MODIFIED_USER_ID)
			";
		}

		if ($bSupportTeam!="Y" && $bAdmin!="Y")
		{
			$arSqlSearch[] = "M.IS_HIDDEN='N'";
			$arSqlSearch[] = "M.IS_LOG='N'";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		if ($by == "s_id")			$strSqlOrder = "ORDER BY M.ID";
		elseif ($by == "s_number")	$strSqlOrder = "ORDER BY M.C_NUMBER";
		else 
		{
			$by = "s_number";
			$strSqlOrder = "ORDER BY M.C_NUMBER";
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

		$strSql = "
			SELECT
				M.*,
				T.SLA_ID,
				".$DB->DateToCharFunction("M.DATE_CREATE")."			DATE_CREATE,
				".$DB->DateToCharFunction("M.TIMESTAMP_X")."			TIMESTAMP_X,
				DS.NAME													SOURCE_NAME
				$u_select
			FROM 
				b_ticket_message M
			INNER JOIN b_ticket T ON (T.ID = M.TICKET_ID)
			LEFT JOIN b_ticket_dictionary DS ON (DS.ID = M.SOURCE_ID)
			$u_join
			WHERE
				$strSqlSearch
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function GetDynamicList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetDynamicList<br>Line: ";
		global $DB;
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
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "RESPONSIBLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;
					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_date_create") $strSqlOrder = "ORDER BY T.DATE_CREATE";
		else 
		{
			$by = "s_date_create";
			$strSqlOrder = "ORDER BY T.DATE_CREATE";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSql = "
			SELECT 
				count(T.ID)							ALL_TICKETS,
				sum(if(T.DATE_CLOSE is null,1,0))	OPEN_TICKETS,
				sum(if(T.DATE_CLOSE is null,0,1))	CLOSE_TICKETS,
				DAYOFMONTH(T.DAY_CREATE)			CREATE_DAY,
				MONTH(T.DAY_CREATE)					CREATE_MONTH,
				YEAR(T.DAY_CREATE)					CREATE_YEAR
			FROM 
				b_ticket T
			LEFT JOIN b_user UR ON (T.RESPONSIBLE_USER_ID = UR.ID)
			WHERE 
			$strSqlSearch
			and	T.DAY_CREATE is not null
			GROUP BY 
				TO_DAYS(T.DAY_CREATE)
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function GetMessageDynamicList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetMessageDynamicList<br>Line: ";
		global $DB;
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
					case "SITE":
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SITE_ID",$val,$match);
						break;
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "M.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "M.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "OWNER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("M.OWNER_USER_ID, U.LOGIN, U.LAST_NAME, U.NAME", $val, $match);
						break;
					case "OWNER_ID":
						if (intval($val)>0) $arSqlSearch[] = "M.OWNER_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(M.OWNER_USER_ID is null or M.OWNER_USER_ID=0)";
						break;
					case "IS_HIDDEN":
					case "IS_LOG":
					case "IS_OVERDUE":
						$arSqlSearch[] = ($val=="Y") ? "M.".$key."='Y'" : "M.".$key."='N'";
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;
					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_date_create") $strSqlOrder = "ORDER BY M.DATE_CREATE";
		else 
		{
			$by = "s_date_create";
			$strSqlOrder = "ORDER BY M.DATE_CREATE";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSql = "
			SELECT 
				count(M.ID)								COUNTER,
				sum(if(M.EXPIRE_AGENT_DONE='Y', 1, 0))	COUNTER_OVERDUE,
				DAYOFMONTH(M.DAY_CREATE)				CREATE_DAY,
				MONTH(M.DAY_CREATE)						CREATE_MONTH,
				YEAR(M.DAY_CREATE)						CREATE_YEAR
			FROM 
				b_ticket_message M
			INNER JOIN b_ticket T ON (T.ID = M.TICKET_ID)
			LEFT JOIN b_user U ON (M.OWNER_USER_ID = U.ID)
			WHERE 
			$strSqlSearch
			GROUP BY 
				TO_DAYS(M.DAY_CREATE)
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
}

class CTicketDictionary extends CAllTicketDictionary
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CTicketDictionary<br>File: ".__FILE__;
	}

	function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = (CTicketDictionary::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB;
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
					case "ID":
					case "SID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.".$key, $val, $match);
						break;
					case "SITE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DS.SITE_ID", $val, $match);
						$left_join_site .= "LEFT JOIN b_ticket_dictionary_2_site DS ON (D.ID = DS.DICTIONARY_ID)";
						$select_user = ", DS.SITE_ID ";
						break;
					case "TYPE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.C_TYPE", $val, $match);
						break;
					case "NAME":
					case "DESCR":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("D.".$key, $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "D.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(D.RESPONSIBLE_USER_ID is null or D.RESPONSIBLE_USER_ID=0)";
						break;
					case "RESPONSIBLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("D.RESPONSIBLE_USER_ID, U.LOGIN, U.LAST_NAME, U.NAME", $val, $match);
						$select_user = ",
							U.LOGIN														RESPONSIBLE_LOGIN,
							concat(ifnull(U.NAME,''),' ',ifnull(U.LAST_NAME,''))		RESPONSIBLE_NAME
							";
						$left_join_user = "LEFT JOIN b_user U ON (U.ID = D.RESPONSIBLE_USER_ID)";
						break;
					case "DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "D.SET_AS_DEFAULT='Y'" : "D.SET_AS_DEFAULT='N'";
						break;
					case "LID":
					case "FIRST_SITE_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.FIRST_SITE_ID",$val,$match);
						break;
				}
			}
		}

		if ($by == "s_id")				$strSqlOrder = "D.ID";
		elseif ($by == "s_c_sort")		$strSqlOrder = "D.C_SORT";
		elseif ($by == "s_sid")			$strSqlOrder = "D.SID";
		elseif ($by == "s_lid")			$strSqlOrder = "D.FIRST_SITE_ID";
		elseif ($by == "s_name")		$strSqlOrder = "D.NAME";
		elseif ($by == "s_responsible")	$strSqlOrder = "D.RESPONSIBLE_USER_ID";
		elseif ($by == "s_dropdown")	$strSqlOrder = "D.C_SORT, D.ID, D.NAME";
		else 
		{
			$by = "s_c_sort";
			$strSqlOrder = "D.C_SORT";
		}
		if ($order!="desc")
		{
			$strSqlOrder .= " asc ";
			$order="asc";
		}
		else 
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT 
				D.*,
				D.FIRST_SITE_ID						LID,
				D.ID								REFERENCE_ID,
			    D.NAME								REFERENCE
				$select_user
			FROM 
				b_ticket_dictionary D
			$left_join_user
			$left_join_site
			WHERE 
			$strSqlSearch
			GROUP BY
				D.ID
			ORDER BY 
				case D.C_TYPE 
					when 'C'	then '1'
					when 'F'	then '2'
					when 'S'	then '3'
					when 'M'	then '4'
					when 'K'	then '5'
					when 'SR'	then '6'
					when 'D'	then '7'
					else ''	end,
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}

class CTicketSLA extends CAllTicketSLA
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CTicketSLA<br>File: ".__FILE__;
	}

	// возвращает список SLA
	function GetList(&$arSort, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = (CTicketSLA::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$is_filtered = false;

		// если параметры фильтра корректны то
		if (CTicket::CheckFilter($arFilter)):

			$arSqlSearch = Array();

			// если определен фильтр то
			if (is_array($arFilter) && count($arFilter)>0):
			
				// сформируем массив фильтра
				$filter_keys = array_keys($arFilter);
				for ($i=0; $i<count($filter_keys); $i++):
			
					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
						continue;
					$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
					$key = strtoupper($key);
					if (is_array($val)) $val = implode(" | ",$val);
					switch($key) :

						case "ID":
						case "SLA_ID":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("S.".$key,$val,$match);
							break;
						case "NAME":
						case "DESCRIPTION":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
							break;
						case "SITE":
							$val .= " | ALL";
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("SS.SITE_ID", $val, $match);
							$left_join_site = "LEFT JOIN b_ticket_sla_2_site SS ON (S.ID = SS.SLA_ID)";
							break;

					endswitch;
				endfor;
			endif;
		endif;

		// SQL строка фильтра
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		// сортировка
		$arSort = is_array($arSort) ? $arSort : array();
		if (count($arSort)>0)
		{
			$ar1 = array_merge($DB->GetTableFieldsList("b_ticket_sla"), array());
			$ar2 = array_keys($arSort);
			$arDiff = array_diff($ar2, $ar1);
			if (is_array($arDiff) && count($arDiff)>0) foreach($arDiff as $value) unset($arSort[$value]);
		}
		if (count($arSort)<=0) $arSort = array("PRIORITY" => "DESC");
		while(list($by, $order) = each($arSort)) 
		{
			if ($by=="RESPONSE_TIME")
			{
				$arSqlOrder[] = "case RESPONSE_TIME_UNIT when 'day' then 3 when 'hour' then 2 when 'minute' then 1 end $order";
				$arSqlOrder[] = $by." ".$order;
			}
			else
			{
				$arSqlOrder[] = $by." ".$order;
			}
		}
		if (is_array($arSqlOrder) && count($arSqlOrder)>0) $strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);

		$strSql = "
			SELECT DISTINCT 
				S.*,
				case S.RESPONSE_TIME_UNIT 
					when 'day' then S.RESPONSE_TIME*1440
					when 'hour' then S.RESPONSE_TIME*60
					when 'minute' then S.RESPONSE_TIME 
					end											M_RESPONSE_TIME,
				case S.NOTICE_TIME_UNIT 
					when 'day' then S.NOTICE_TIME*1440
					when 'hour' then S.NOTICE_TIME*60
					when 'minute' then S.NOTICE_TIME 
					end											M_NOTICE_TIME,
				S.ID											REFERENCE_ID,
				S.NAME											REFERENCE,
				".$DB->DateToCharFunction("S.DATE_MODIFY")."	DATE_MODIFY_F,
				".$DB->DateToCharFunction("S.DATE_CREATE")."	DATE_CREATE_F
			FROM 
				b_ticket_sla S
			$left_join_site
			WHERE 
			$strSqlSearch
			$strSqlOrder
			";
		//echo "<pre>".$strSql."</pre>";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $rs;
	}

}

class CTicketReminder extends CAllTicketReminder
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CTicketReminder<br>File: ".__FILE__;
	}
}
?>