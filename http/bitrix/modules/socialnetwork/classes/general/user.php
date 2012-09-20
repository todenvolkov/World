<?
IncludeModuleLangFile(__FILE__);

class CAllSocNetUser
{
	function OnUserDelete($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);
		$bSuccess = True;

		if (!CSocNetGroup::DeleteNoDemand($ID))
		{
			if($ex = $GLOBALS["APPLICATION"]->GetException())
				$err = $ex->GetString();
			$GLOBALS["APPLICATION"]->ThrowException($err);				
			$bSuccess = false;
		}

		if ($bSuccess)
		{
			CSocNetUserRelations::DeleteNoDemand($ID);
			CSocNetUserPerms::DeleteNoDemand($ID);
			CSocNetUserEvents::DeleteNoDemand($ID);
			CSocNetMessages::DeleteNoDemand($ID);
			CSocNetUserToGroup::DeleteNoDemand($ID);
			CSocNetLogEvents::DeleteNoDemand($ID);
			CSocNetLog::DeleteNoDemand($ID);
			CSocNetFeatures::DeleteNoDemand($ID);

			CUserOptions::DeleteOption("socialnetwork", "~menu_".SONET_ENTITY_USER."_".$ID, false, 0);
		}

		return $bSuccess;
	}

	function OnBeforeUserUpdate(&$arFields)
	{
		$rsUser = CUser::GetByID($arFields["ID"]);
		if ($arUser = $rsUser->Fetch())
			define("GLOBAL_ACTIVE_VALUE", $arUser["ACTIVE"]);
	}

	function OnAfterUserAdd(&$arFields)
	{
		CSocNetEventUserView::SetUser($arFields["ID"], false, false, true);
	}

	function OnAfterUserUpdate(&$arFields)
	{
		if (defined("GLOBAL_ACTIVE_VALUE") && GLOBAL_ACTIVE_VALUE != $arFields["ACTIVE"]):

			$dbResult = CSocNetUserToGroup::GetList(array(), array("USER_ID" => $arFields["ID"]), false, false, array("GROUP_ID"));
			while ($arResult = $dbResult->Fetch())
				$arGroups[] = $arResult["GROUP_ID"];
		
			for ($i = 0; $i < count($arGroups); $i++)
				CSocNetGroup::SetStat($arGroups[$i]);
				
			if ($arFields["ACTIVE"] == "Y")
				CSocNetEventUserView::SetUser($arFields["ID"], false, false, true);
			else
				$GLOBALS["DB"]->Query("DELETE FROM b_sonet_event_user_view WHERE 
					USER_ID = ".$arFields["ID"]." 
					OR (ENTITY_TYPE = '".SONET_ENTITY_USER."' AND (ENTITY_ID = ".$arFields["ID"]." OR USER_IM_ID = ".$arFields["ID"].")
					)", true);

		endif;
	}
	
	function OnBeforeProlog()
	{
		if (!$GLOBALS["USER"]->IsAuthorized())
			return;

		CUser::SetLastActivityDate($GLOBALS["USER"]->GetID());
	}

	function IsOnLine($userID)
	{
		$userID = IntVal($userID);
		if ($userID <= 0)
			return false;

		return CUser::IsOnLine($userID, 120);
	}

	function IsFriendsAllowed()
	{
		if (array_key_exists("SONET_ALLOW_FRIENDS_CACHE", $GLOBALS) && !array_key_exists("SONET_ALLOW_FRIENDS_CACHE", $_REQUEST))
			return $GLOBALS["SONET_ALLOW_FRIENDS_CACHE"];

		$GLOBALS["SONET_ALLOW_FRIENDS_CACHE"] = (COption::GetOptionString("socialnetwork", "allow_frields", "Y") == "Y");
		return $GLOBALS["SONET_ALLOW_FRIENDS_CACHE"];
	}

	function IsCurrentUserModuleAdmin()
	{
		if (!$GLOBALS["USER"]->IsAuthorized())
			return false;

		if ($GLOBALS["USER"]->IsAdmin())
			return true;
		
		$modulePerms = $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork");
		return ($modulePerms >= "W");
	}

	function IsUserModuleAdmin($userID)	
	{
		if ($userID <= 0)
			return false;
			
		$arGroups = array();
		
		$strSql =
			"SELECT G.ID ".
			"FROM b_user_group UG, b_group G  ".
			"WHERE UG.USER_ID = ".$userID." ".
				"	AND G.ID=UG.GROUP_ID  ".
				"	AND G.ACTIVE='Y' ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$GLOBALS["DB"]->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$GLOBALS["DB"]->CurrentTimeFunction().")) ".
				"	AND (G.ANONYMOUS<>'Y' OR G.ANONYMOUS IS NULL) ";

		$result = $GLOBALS["DB"]->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		while($ar = $result->Fetch())
			$arGroups[]	= IntVal($ar["ID"]);

		return (CMain::GetUserRight("socialnetwork", $arGroups, "N", "Y") >= "W");
	}
			
	function FormatName($name, $lastName, $login)
	{
		$name = Trim($name);
		$lastName = Trim($lastName);
		$login = Trim($login);

		$formatName = $name;
		if (StrLen($formatName) > 0 && StrLen($lastName) > 0)
			$formatName .= " ";
		$formatName .= $lastName;
		if (StrLen($formatName) <= 0)
			$formatName = $login;

		return $formatName;
	}

	function FormatNameEx($name, $secondName, $lastName, $login, $email, $id)
	{
		$name = Trim($name);
		$lastName = Trim($lastName);
		$secondName = Trim($secondName);
		$login = Trim($login);
		$email = Trim($email);
		$id = IntVal($id);

		$formatName = $name;
		if (StrLen($formatName) > 0 && StrLen($secondName) > 0)
			$formatName .= " ";
		$formatName .= $secondName;
		if (StrLen($formatName) > 0 && StrLen($lastName) > 0)
			$formatName .= " ";
		$formatName .= $lastName;
		if (StrLen($formatName) <= 0)
			$formatName = $login;

		if (StrLen($email) > 0)
			$formatName .= " &lt;".$email."&gt;";
		$formatName .= " [".$id."]";

		return $formatName;
	}

	function SearchUser($user, $bIntranet = false)
	{
		$user = Trim($user);
		if (StrLen($user) <= 0)
			return false;

		$userID = 0;
		if ($user."|" == IntVal($user)."|")
			$userID = IntVal($user);

		if ($userID <= 0)
		{
			$arMatches = array();
			if (preg_match("#\[(\d+)\]#i", $user, $arMatches))
				$userID = IntVal($arMatches[1]);
		}


		$dbUsers =  false;
		if ($userID > 0)
		{
			$arFilter = array("ID_EQUAL_EXACT" => $userID);

			$dbUsers = CUser::GetList(
				($by = "LAST_NAME"),
				($order = "asc"),
				$arFilter,
				array(
					"NAV_PARAMS" => false,
				)
			);
		}
		else
		{
			$email = "";
			$arMatches = array();
			if (preg_match("#<(.+?)>#i", $user, $arMatches))
			{

				if (check_email($arMatches[1]))
				{
					$email = $arMatches[1];
					$user = Trim(Str_Replace("<".$email.">", "", $user));

				}
			}


			$arUser = array();
			$arUserTmp = Explode(" ", $user);
			foreach ($arUserTmp as $s)
			{
				$s = Trim($s);
				if (StrLen($s) > 0)
					$arUser[] = $s;
			}

			if (count($arUser) <= 0 && strlen($email) > 0):
				$arFilter = array
					(
						"ACTIVE"              => "Y",
						"EMAIL"               => $email,
					);
				$dbUsers = CUser::GetList(($by="id"), ($order="asc"), $arFilter);
			else:
				$dbUsers = CUser::SearchUserByName($arUser, $email);
			endif;

		}

		if ($dbUsers)
		{
			$arResult = array();
			while ($arUsers = $dbUsers->GetNext())
			{
				$arResult[$arUsers["ID"]] = CSocNetUser::FormatNameEx(
					$arUsers["NAME"],
					$arUsers["SECOND_NAME"],
					$arUsers["LAST_NAME"],
					$arUsers["LOGIN"],
					($bIntranet ? $arUsers["EMAIL"] : ""),
					$arUsers["ID"]
				);
			}

			return $arResult;
		}

		return false;
	}

	function GetByID($ID)
	{
		$ID = IntVal($ID);

		$dbUser = CUser::GetByID($ID);
		if ($arUser = $dbUser->GetNext())
			return $arUser;
		else
			return false;
	}
		
	
}
?>