<?
IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_ORDER"] = Array();

class CAllSaleOrder
{
	//*************** USER PERMISSIONS *********************/
	function CanUserViewOrder($ID, $arUserGroups = false, $userID = 0)
	{
		$ID = IntVal($ID);
		$userID = IntVal($userID);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");
		if ($userRights >= "W")
			return True;

		$arOrder = CSaleOrder::GetByID($ID);
		if ($arOrder)
		{
			if (IntVal($arOrder["USER_ID"]) == $userID)
				return True;

			if ($userRights == "U")
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);
				if (IntVal($num) > 0)
				{
					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => "PERM_VIEW")
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
					{
						if ($arStatusPerms["PERM_VIEW"] == "Y")
							return True;
					}
				}
			}
		}

		return False;
	}

	function CanUserUpdateOrder($ID, $arUserGroups = false)
	{
		$ID = IntVal($ID);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");

		if ($userRights >= "W")
			return True;

		if ($userRights == "U")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			if ($arOrder)
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);

				if (IntVal($num) > 0)
				{
					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => "PERM_UPDATE")
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
						if ($arStatusPerms["PERM_UPDATE"] == "Y")
							return True;
				}
			}
		}

		return False;
	}

	function CanUserCancelOrder($ID, $arUserGroups = false, $userID = 0)
	{
		$ID = IntVal($ID);
		$userID = IntVal($userID);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");
		if ($userRights >= "W")
			return True;

		$arOrder = CSaleOrder::GetByID($ID);
		if ($arOrder)
		{
			if (IntVal($arOrder["USER_ID"]) == $userID)
				return True;

			if ($userRights == "U")
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);

				if (IntVal($num) > 0)
				{
					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => "PERM_CANCEL")
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
						if ($arStatusPerms["PERM_CANCEL"] == "Y")
							return True;
				}
			}
		}

		return False;
	}

	function CanUserChangeOrderFlag($ID, $flag, $arUserGroups = false)
	{
		$ID = IntVal($ID);
		$flag = trim($flag);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");
		if ($userRights >= "W")
			return True;

		if ($userRights == "U")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			if ($arOrder)
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);

				if (IntVal($num) > 0)
				{
					if ($flag == "P" || $flag == "PERM_PAYMENT")
						$fieldName = "PERM_PAYMENT";
					else
						$fieldName = "PERM_DELIVERY";

					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => $fieldName)
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
						if ($arStatusPerms[$fieldName] == "Y")
							return True;
				}
			}
		}

		return False;
	}

	function CanUserChangeOrderStatus($ID, $statusID, $arUserGroups = false)
	{
		$ID = IntVal($ID);
		$statusID = Trim($statusID);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");
		if ($userRights >= "W")
			return True;

		if ($userRights == "U")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			if ($arOrder)
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);

				if (IntVal($num) > 0)
				{
					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => "PERM_STATUS_FROM")
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
					{
						if ($arStatusPerms["PERM_STATUS_FROM"] == "Y")
						{
							$dbStatusPerms = CSaleStatus::GetPermissionsList(
								array(),
								array(
									"STATUS_ID" => $statusID,
									"GROUP_ID" => $arUserGroups
								),
								array("MAX" => "PERM_STATUS")
							);
							if ($arStatusPerms = $dbStatusPerms->Fetch())
								if ($arStatusPerms["PERM_STATUS"] == "Y")
									return True;
						}
					}
				}
			}
		}

		return False;
	}

	function CanUserDeleteOrder($ID, $arUserGroups = false, $userID = 0)
	{
		$ID = IntVal($ID);
		$userID = IntVal($userID);

		$userRights = CMain::GetUserRight("sale", $arUserGroups, "Y", "Y");
		if ($userRights >= "W")
			return True;

		if ($userRights == "U")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			if ($arOrder)
			{
				$num = CSaleGroupAccessToSite::GetList(
						array(),
						array(
								"SITE_ID" => $arOrder["LID"],
								"GROUP_ID" => $arUserGroups
							),
						array()
					);

				if (IntVal($num) > 0)
				{
					$dbStatusPerms = CSaleStatus::GetPermissionsList(
						array(),
						array(
							"STATUS_ID" => $arOrder["STATUS_ID"],
							"GROUP_ID" => $arUserGroups
						),
						array("MAX" => "PERM_DELETE")
					);
					if ($arStatusPerms = $dbStatusPerms->Fetch())
						if ($arStatusPerms["PERM_DELETE"] == "Y")
							return True;
				}
			}
		}

		return False;
	}


	//*************** ADD, UPDATE, DELETE *********************/
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if (is_set($arFields, "SITE_ID") && strlen($arFields["SITE_ID"]) > 0)
			$arFields["LID"] = $arFields["SITE_ID"];

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && strlen($arFields["LID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_EMPTY_SITE"), "EMPTY_SITE_ID");
			return false;
		}
		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && IntVal($arFields["PERSON_TYPE_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_EMPTY_PERS_TYPE"), "EMPTY_PERSON_TYPE_ID");
			return false;
		}
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && IntVal($arFields["USER_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}

		if (is_set($arFields, "PAYED") && $arFields["PAYED"]!="Y")
			$arFields["PAYED"]="N";
		if (is_set($arFields, "CANCELED") && $arFields["CANCELED"]!="Y")
			$arFields["CANCELED"]="N";
		if (is_set($arFields, "STATUS_ID") && strlen($arFields["STATUS_ID"])<=0)
			$arFields["STATUS_ID"]="N";
		if (is_set($arFields, "ALLOW_DELIVERY") && $arFields["ALLOW_DELIVERY"]!="Y")
			$arFields["ALLOW_DELIVERY"]="N";

		if (is_set($arFields, "PRICE") || $ACTION=="ADD")
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = DoubleVal($arFields["PRICE"]);
		}
		if (is_set($arFields, "PRICE_DELIVERY") || $ACTION=="ADD")
		{
			$arFields["PRICE_DELIVERY"] = str_replace(",", ".", $arFields["PRICE_DELIVERY"]);
			$arFields["PRICE_DELIVERY"] = DoubleVal($arFields["PRICE_DELIVERY"]);
		}
		if (is_set($arFields, "SUM_PAID") || $ACTION=="ADD")
		{
			$arFields["SUM_PAID"] = str_replace(",", ".", $arFields["SUM_PAID"]);
			$arFields["SUM_PAID"] = DoubleVal($arFields["SUM_PAID"]);
		}
		if (is_set($arFields, "DISCOUNT_VALUE") || $ACTION=="ADD")
		{
			$arFields["DISCOUNT_VALUE"] = str_replace(",", ".", $arFields["DISCOUNT_VALUE"]);
			$arFields["DISCOUNT_VALUE"] = DoubleVal($arFields["DISCOUNT_VALUE"]);
		}
		if (is_set($arFields, "TAX_VALUE") || $ACTION=="ADD")
		{
			$arFields["TAX_VALUE"] = str_replace(",", ".", $arFields["TAX_VALUE"]);
			$arFields["TAX_VALUE"] = DoubleVal($arFields["TAX_VALUE"]);
		}

		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && strlen($arFields["CURRENCY"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}

		if (is_set($arFields, "CURRENCY"))
		{
			if (!($arCurrency = CCurrency::GetByID($arFields["CURRENCY"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["CURRENCY"], GetMessage("SKGO_WRONG_CURRENCY")), "ERROR_NO_CURRENCY");
				return false;
			}
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGO_WRONG_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGO_WRONG_USER")), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "PERSON_TYPE_ID"))
		{
			if (!($arPersonType = CSalePersonType::GetByID($arFields["PERSON_TYPE_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PERSON_TYPE_ID"], GetMessage("SKGO_WRONG_PERSON_TYPE")), "ERROR_NO_PERSON_TYPE");
				return false;
			}
		}

		if (is_set($arFields, "PAY_SYSTEM_ID") && IntVal($arFields["PAY_SYSTEM_ID"]) > 0)
		{
			if (!($arPaySystem = CSalePaySystem::GetByID(IntVal($arFields["PAY_SYSTEM_ID"]))))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PAY_SYSTEM_ID"], GetMessage("SKGO_WRONG_PS")), "ERROR_NO_PAY_SYSTEM");
				return false;
			}
		}

		if (is_set($arFields, "DELIVERY_ID") && (
				strpos($arFileds["DELIVERY_ID"], ":") !== false
				||
				IntVal($arFields["DELIVERY_ID"]) > 0
			)
		)	
		{
			if (strpos($arFileds["DELIVERY_ID"], ":") !== false)
			{
				$arId = explode(":", $arFields["DELIVERY_ID"]);
				$obDelivery = new CSaleDeliveryHandler();
				if ($arDelivery = $obDelivery->GetBySID($arId[0]))
				{
					if (!is_set($arDelivery, $arFields["LID"]) || !is_set($arDelivery[$arFields["LID"]]["PROFILES"], $arId[1]))
					{
						$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["DELIVERY_ID"], GetMessage("SKGO_WRONG_DELIVERY")), "ERROR_NO_DELIVERY");
						return false;
					}
				}
				else
				{
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["DELIVERY_ID"], GetMessage("SKGO_WRONG_DELIVERY")), "ERROR_NO_DELIVERY");
					return false;
				}
			}
			else
			{
				if (!($arDelivery = CSaleDelivery::GetByID(IntVal($arFields["DELIVERY_ID"]))))
				{
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["DELIVERY_ID"], GetMessage("SKGO_WRONG_DELIVERY")), "ERROR_NO_DELIVERY");
					return false;
				}
			}
		}

		if (is_set($arFields, "STATUS_ID"))
		{
			if (!($arStatus = CSaleStatus::GetByID($arFields["STATUS_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["STATUS_ID"], GetMessage("SKGO_WRONG_STATUS")), "ERROR_NO_STATUS_ID");
				return false;
			}
		}

		return True;
	}

	function _Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$bSuccess = True;

		$db_events = GetModuleEvents("sale", "OnBeforeOrderDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
				return false;

		$events = GetModuleEvents("sale", "OnOrderDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID));

		$DB->StartTransaction();

		if ($bSuccess)
		{
			$dbBasket = CSaleBasket::GetList(array(), array("ORDER_ID" => $ID));
			while ($arBasket = $dbBasket->Fetch())
			{
				$bSuccess = CSaleBasket::Delete($arBasket["ID"]);
				if (!$bSuccess)
					break;
			}
		}

		if ($bSuccess)
		{
			$dbRecurring = CSaleRecurring::GetList(array(), array("ORDER_ID" => $ID));
			while ($arRecurring = $dbRecurring->Fetch())
			{
				$bSuccess = CSaleRecurring::Delete($arRecurring["ID"]);
				if (!$bSuccess)
					break;
			}
		}

		if ($bSuccess)
			$bSuccess = CSaleOrderPropsValue::DeleteByOrder($ID);

		if ($bSuccess)
			$bSuccess = CSaleOrderTax::DeleteEx($ID);
		
		if($bSuccess)
			$bSuccess = CSaleUserTransact::DeleteByOrder($ID);

		if ($bSuccess)
			unset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]);

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sale_order WHERE ID = ".$ID."", true);

		if ($bSuccess)
			$DB->Commit();
		else
			$DB->Rollback();

		return $bSuccess;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		$arOrder = CSaleOrder::GetByID($ID);
		if ($arOrder)
		{
			if ($arOrder["CANCELED"] != "Y")
				CSaleBasket::OrderCanceled($ID, "Y");

			if ($arOrder["ALLOW_DELIVERY"] == "Y")
				CSaleOrder::DeliverOrder($ID, "N");

			if ($arOrder["PAYED"] != "Y")
			{
				return CSaleOrder::_Delete($ID);
			}
			else
			{
				if (CSaleOrder::PayOrder($ID, "N", True, True))
					return CSaleOrder::_Delete($ID);
			}
		}

		return False;
	}

	//*************** COMMON UTILS *********************/
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (substr($key, 0, 1)=="+")
		{
			$key = substr($key, 1);
			$strOrNull = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="~")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	function PrepareSql(&$arFields, $arOrder, &$arFilter, $arGroupBy, $arSelectFields)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";
		$strSqlOrderBy = "";

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = array();

		// GROUP BY -->
		if (is_array($arGroupBy) && count($arGroupBy) > 0)
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = strtoupper($val);
				$key = strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if (strlen($strSqlGroupBy) > 0)
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& strlen($arFields[$val]["FROM"]) > 0
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && strlen($arSelectFields)>0 && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields)<=0
				|| in_array("*", $arSelectFields))
			{
				for ($i = 0; $i < count($arFieldsKeys); $i++)
				{
					if (isset($arFields[$arFieldsKeys[$i]]["WHERE_ONLY"])
						&& $arFields[$arFieldsKeys[$i]]["WHERE_ONLY"] == "Y")
					{
						continue;
					}

					if (strlen($strSqlSelect) > 0)
						$strSqlSelect .= ", ";

					if ($arFields[$arFieldsKeys[$i]]["TYPE"] == "datetime")
					{
						if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($arFieldsKeys[$i], $arOrder)))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "FULL")." as ".$arFieldsKeys[$i];
					}
					elseif ($arFields[$arFieldsKeys[$i]]["TYPE"] == "date")
					{
						if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($arFieldsKeys[$i], $arOrder)))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "SHORT")." as ".$arFieldsKeys[$i];
					}
					else
						$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i];

					if (isset($arFields[$arFieldsKeys[$i]]["FROM"])
						&& strlen($arFields[$arFieldsKeys[$i]]["FROM"]) > 0
						&& !in_array($arFields[$arFieldsKeys[$i]]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$arFieldsKeys[$i]]["FROM"];
						$arAlreadyJoined[] = $arFields[$arFieldsKeys[$i]]["FROM"];
					}
				}
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					$val = strtoupper($val);
					$key = strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if (strlen($strSqlSelect) > 0)
							$strSqlSelect .= ", ";

						if (in_array($key, $arGroupByFunct))
						{
							$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
						}
						else
						{
							if ($arFields[$val]["TYPE"] == "datetime")
							{
								if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($val, $arOrder)))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "FULL")." as ".$val;
							}
							elseif ($arFields[$val]["TYPE"] == "date")
							{
								if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($val, $arOrder)))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT")." as ".$val;
							}
							else
								$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val;
						}

						if (isset($arFields[$val]["FROM"])
							&& strlen($arFields[$val]["FROM"]) > 0
							&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
						{
							if (strlen($strSqlFrom) > 0)
								$strSqlFrom .= " ";
							$strSqlFrom .= $arFields[$val]["FROM"];
							$arAlreadyJoined[] = $arFields[$val]["FROM"];
						}
					}
				}
			}

			if (strlen($strSqlGroupBy) > 0)
			{
				if (strlen($strSqlSelect) > 0)
					$strSqlSelect .= ", ";
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
			else
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
		}
		// <-- SELECT

		// WHERE -->
		$arSqlSearch = Array();

		if (!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$key = $filter_keys[$i];
			$key_res = CSaleOrder::GetFilterOperation($key);
			$key = $key_res["FIELD"];
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			$strOrNull = $key_res["OR_NULL"];

			if (array_key_exists($key, $arFields))
			{
				$arSqlSearch_tmp = array();
				if (count($vals) > 0)
				{
					if ($strOperation == "IN")
					{
						if (isset($arFields[$key]["WHERE"]))
						{
							$arSqlSearch_tmp1 = call_user_func_array(
									$arFields[$key]["WHERE"],
									array($vals, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter)
								);
							if ($arSqlSearch_tmp1 !== false)
								$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
						}
						else
						{
							if ($arFields[$key]["TYPE"] == "int")
							{
								array_walk($vals, create_function("&\$item", "\$item=IntVal(\$item);"));
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (count($vals) <= 0)
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." IN (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "double")
							{
								array_walk($vals, create_function("&\$item", "\$item=DoubleVal(\$item);"));
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (count($vals) <= 0)
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
							{
								array_walk($vals, create_function("&\$item", "\$item=\"'\".\$GLOBALS[\"DB\"]->ForSql(\$item).\"'\";"));
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (count($vals) <= 0)
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "datetime")
							{
								array_walk($vals, create_function("&\$item", "\$item=\"'\".\$GLOBALS[\"DB\"]->CharToDateFunction(\$GLOBALS[\"DB\"]->ForSql(\$item), \"FULL\").\"'\";"));
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (count($vals) <= 0)
									$arSqlSearch_tmp[] = "1 = 2";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "date")
							{
								array_walk($vals, create_function("&\$item", "\$item=\"'\".\$GLOBALS[\"DB\"]->CharToDateFunction(\$GLOBALS[\"DB\"]->ForSql(\$item), \"SHORT\").\"'\";"));
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (count($vals) <= 0)
									$arSqlSearch_tmp[] = "1 = 2";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
						}
					}
					else
					{
						for ($j = 0; $j < count($vals); $j++)
						{
							$val = $vals[$j];

							if (isset($arFields[$key]["WHERE"]))
							{
								$arSqlSearch_tmp1 = call_user_func_array(
										$arFields[$key]["WHERE"],
										array($val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter)
									);
								if ($arSqlSearch_tmp1 !== false)
									$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
							}
							else
							{
								if ($arFields[$key]["TYPE"] == "int")
								{
									if ((IntVal($val) == 0) && (strpos($strOperation, "=") !== False))
										$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
									else
										$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".IntVal($val)." )";
								}
								elseif ($arFields[$key]["TYPE"] == "double")
								{
									$val = str_replace(",", ".", $val);

									if ((DoubleVal($val) == 0) && (strpos($strOperation, "=") !== False))
										$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
									else
										$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".DoubleVal($val)." )";
								}
								elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
								{
									if ($strOperation == "QUERY")
									{
										$arSqlSearch_tmp[] = GetFilterQuery($arFields[$key]["FIELD"], $val, "Y");
									}
									else
									{
										if ((strlen($val) == 0) && (strpos($strOperation, "=") !== False))
											$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$DB->Length($arFields[$key]["FIELD"])." <= 0) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
										else
											$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
									}
								}
								elseif ($arFields[$key]["TYPE"] == "datetime")
								{
									if (strlen($val) <= 0)
										$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
									else
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
								}
								elseif ($arFields[$key]["TYPE"] == "date")
								{
									if (strlen($val) <= 0)
										$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
									else
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
								}
							}
						}
					}
				}

				if (isset($arFields[$key]["FROM"])
					&& strlen($arFields[$key]["FROM"]) > 0
					&& !in_array($arFields[$key]["FROM"], $arAlreadyJoined))
				{
					if (strlen($strSqlFrom) > 0)
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$key]["FROM"];
					$arAlreadyJoined[] = $arFields[$key]["FROM"];
				}

				$strSqlSearch_tmp = "";
				for ($j = 0; $j < count($arSqlSearch_tmp); $j++)
				{
					if ($j > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arSqlSearch_tmp[$j].")";
				}
				if ($strOrNull == "Y")
				{
					if (strlen($strSqlSearch_tmp) > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." IS ".($strNegative=="Y" ? "NOT " : "")."NULL)";

					if ($arFields[$key]["TYPE"] == "int" || $arFields[$key]["TYPE"] == "double")
					{
						if (strlen($strSqlSearch_tmp) > 0)
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." 0)";
					}
					elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
					{
						if (strlen($strSqlSearch_tmp) > 0)
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." '')";
					}
				}

				if ($strSqlSearch_tmp != "")
					$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
			}
		}

		for ($i = 0; $i < count($arSqlSearch); $i++)
		{
			if (strlen($strSqlWhere) > 0)
				$strSqlWhere .= " AND ";
			$strSqlWhere .= "(".$arSqlSearch[$i].")";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = Array();
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);

			if ($order != "ASC")
				$order = "DESC";
			else
				$order = "ASC";

			if (array_key_exists($by, $arFields))
			{
				$arSqlOrder[] = " ".$arFields[$by]["FIELD"]." ".$order." ";

				if (isset($arFields[$by]["FROM"])
					&& strlen($arFields[$by]["FROM"]) > 0
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if (strlen($strSqlFrom) > 0)
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
		}
		
		$strSqlOrderBy = "";
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if (strlen($strSqlOrderBy) > 0)
				$strSqlOrderBy .= ", ";

			if(strtoupper($DB->type)=="ORACLE")
			{
				if(substr($arSqlOrder[$i], -3)=="ASC")
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS FIRST";
				else
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS LAST";
			}
			else
				$strSqlOrderBy .= $arSqlOrder[$i];
		}
		// <-- ORDER BY

		return array(
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy
		);
	}


	//*************** SELECT *********************/
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		if (isset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]) && is_array($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]) && is_set($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT O.*, ".
				"	".$DB->DateToCharFunction("O.DATE_INSERT", "SHORT")." as DATE_INSERT_FORMAT ".
				"FROM b_sale_order O ".
				"WHERE O.ID = ".$ID."";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				$GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID] = $res;
				return $res;
			}
		}

		return False;
	}


	//*************** EVENTS *********************/
	function OnBeforeCurrencyDelete($currency)
	{
		if (strlen($currency)<=0) return false;

		$dbOrders = CSaleOrder::GetList(array(), array("CURRENCY" => $currency), false, array("nTopCount" => 1), array("ID", "CURRENCY"));
		if ($arOrders = $dbOrders->Fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#CURRENCY#", $currency, GetMessage("SKGO_ERROR_ORDERS_CURRENCY")), "ERROR_ORDERS_CURRENCY");
			return False;
		}

		return True;
	}

	function OnBeforeUserDelete($userID)
	{
		global $DB;

		$userID = IntVal($userID);
		if ($userID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty user ID", "EMPTY_USER_ID");
			return false;
		}

		$dbOrders = CSaleOrder::GetList(array(), array("USER_ID" => $userID), false, array("nTopCount" => 1), array("ID", "USER_ID"));
		if ($arOrders = $dbOrders->Fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#USER_ID#", $userID, GetMessage("SKGO_ERROR_ORDERS")), "ERROR_ORDERS");
			return False;
		}

		return True;
	}

	//*************** ACTIONS *********************/
	function PayOrder($ID, $val, $bWithdraw = True, $bPay = True, $recurringID = 0, $arAdditionalFields = array())
	{
		global $DB, $USER;

		$ID = IntVal($ID);
		$val = (($val != "Y") ? "N" : "Y");
		$bWithdraw = ($bWithdraw ? True : False);
		$bPay = ($bPay ? True : False);
		$recurringID = IntVal($recurringID);

		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_NO_ORDER_ID"), "NO_ORDER_ID");
			return False;
		}

		$arOrder = CSaleOrder::GetByID($ID);
		if (!$arOrder)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_NO_ORDER")), "NO_ORDER");
			return False;
		}

		if ($arOrder["PAYED"] == $val)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_DUB_PAY")), "ALREADY_FLAG");
			return False;
		}

		$db_events = GetModuleEvents("sale", "OnSaleBeforePayOrder");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, $val, $bWithdraw, $bPay, $recurringID, $arAdditionalFields))===false)
				return false;

		if ($bWithdraw)
		{
			if ($val == "Y")
			{
				$needPaySum = DoubleVal($arOrder["PRICE"]) - DoubleVal($arOrder["SUM_PAID"]);

				if ($bPay)
					if (!CSaleUserAccount::UpdateAccount($arOrder["USER_ID"], $needPaySum, $arOrder["CURRENCY"], "OUT_CHARGE_OFF", $ID))
						return False;

				if (!CSaleUserAccount::Pay($arOrder["USER_ID"], $needPaySum, $arOrder["CURRENCY"], $ID, False))
					return False;
			}
			else
			{
				if (!CSaleUserAccount::UpdateAccount($arOrder["USER_ID"], $arOrder["PRICE"], $arOrder["CURRENCY"], "ORDER_UNPAY", $ID))
					return False;
			}
		}

		$arFields = array(
				"PAYED" => $val,
				"DATE_PAYED" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"EMP_PAYED_ID" => ( IntVal($USER->GetID())>0 ? IntVal($USER->GetID()) : false ),
				"SUM_PAID" => 0
			);
		if (count($arAdditionalFields) > 0)
		{
			foreach ($arAdditionalFields as $addKey => $addValue)
			{
				if (!array_key_exists($addKey, $arFields))
					$arFields[$addKey] = $addValue;
			}
		}
		$res = CSaleOrder::Update($ID, $arFields);

		$events = GetModuleEvents("sale", "OnSalePayOrder");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $val));

		if ($val == "Y")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			
			$orderStatus = COption::GetOptionString("sale", "status_on_paid", "");
			if(strlen($orderStatus) > 0 && $orderStatus != $arOrder["STATUS_ID"])
			{
				$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
				while ($arStatus = $dbStatus->GetNext())
				{
					$arStatuses[$arStatus["ID"]] = $arStatus["SORT"];
				}
				
				if($arStatuses[$orderStatus] >= $arStatuses[$arOrder["STATUS_ID"]])
					CSaleOrder::StatusOrder($ID, $orderStatus);
			}
			
			$userEMail = "";
			$dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $arOrder["ID"], "PROP_IS_EMAIL" => "Y"));
			if($arOrderProp = $dbOrderProp->Fetch())
				$userEMail = $arOrderProp["VALUE"];

			if(strlen($userEMail) <= 0)
			{
				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
				if($arUser = $dbUser->Fetch())
					$userEMail = $arUser["EMAIL"];
			}
			$event = new CEvent;
			$arFields = Array(
					"ORDER_ID" => $ID,
					"ORDER_DATE" => $arOrder["DATE_INSERT"],
					"EMAIL" => $userEMail,
					"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
				);
			$event->Send("SALE_ORDER_PAID", $arOrder["LID"], $arFields, "N");

			if (CModule::IncludeModule("statistic"))
			{
				CStatEvent::AddByEvents("eStore", "order_paid", $ID, "", $arOrder["STAT_GID"], $arOrder["PRICE"], $arOrder["CURRENCY"]);
			}
		}
		else
		{
			if (CModule::IncludeModule("statistic"))
			{
				CStatEvent::AddByEvents("eStore", "order_chargeback", $ID, "", $arOrder["STAT_GID"], $arOrder["PRICE"], $arOrder["CURRENCY"], "Y");
			}
		}

		return $res;
	}

	function DeliverOrder($ID, $val, $recurringID = 0, $arAdditionalFields = array())
	{
		global $DB, $USER;

		$ID = IntVal($ID);
		$val = (($val != "Y") ? "N" : "Y");
		$recurringID = IntVal($recurringID);

		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_NO_ORDER_ID"), "NO_ORDER_ID");
			return False;
		}

		$arOrder = CSaleOrder::GetByID($ID);
		if (!$arOrder)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_NO_ORDER")), "NO_ORDER");
			return False;
		}

		if ($arOrder["ALLOW_DELIVERY"] == $val)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_DUB_DELIVERY")), "ALREADY_FLAG");
			return False;
		}

		$db_events = GetModuleEvents("sale", "OnSaleBeforeDeliveryOrder");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, $val, $recurringID, $arAdditionalFields))===false)
				return false;

		$arFields = array(
				"ALLOW_DELIVERY" => $val,
				"DATE_ALLOW_DELIVERY" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"EMP_ALLOW_DELIVERY_ID" => ( IntVal($USER->GetID())>0 ? IntVal($USER->GetID()) : false )
			);
		if (count($arAdditionalFields) > 0)
		{
			foreach ($arAdditionalFields as $addKey => $addValue)
			{
				if (!array_key_exists($addKey, $arFields))
					$arFields[$addKey] = $addValue;
			}
		}
		$res = CSaleOrder::Update($ID, $arFields);

		unset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]);

		if ($recurringID <= 0)
		{
			if (IntVal($arOrder["RECURRING_ID"]) > 0)
				$recurringID = IntVal($arOrder["RECURRING_ID"]);
		}

		CSaleBasket::OrderDelivery($ID, (($val=="Y") ? True : False), $recurringID);

		$events = GetModuleEvents("sale", "OnSaleDeliveryOrder");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $val));

		if ($val == "Y")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			
			$orderStatus = COption::GetOptionString("sale", "status_on_allow_delivery", "");
			if(strlen($orderStatus) > 0 && $orderStatus != $arOrder["STATUS_ID"])
			{
				$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID), false, false, Array("ID", "SORT"));
				while ($arStatus = $dbStatus->GetNext())
				{
					$arStatuses[$arStatus["ID"]] = $arStatus["SORT"];
				}
				
				if($arStatuses[$orderStatus] >= $arStatuses[$arOrder["STATUS_ID"]])
					CSaleOrder::StatusOrder($ID, $orderStatus);
			}
			
			$userEMail = "";
			$dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $arOrder["ID"], "PROP_IS_EMAIL" => "Y"));
			if($arOrderProp = $dbOrderProp->Fetch())
				$userEMail = $arOrderProp["VALUE"];

			if(strlen($userEMail) <= 0)
			{
				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
				if($arUser = $dbUser->Fetch())
					$userEMail = $arUser["EMAIL"];
			}

			$event = new CEvent;
			$arFields = Array(
					"ORDER_ID" => $ID,
					"ORDER_DATE" => $arOrder["DATE_INSERT"],
					"EMAIL" => $userEMail,
					"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
				);
			$event->Send("SALE_ORDER_DELIVERY", $arOrder["LID"], $arFields, "N");
		}

		return $res;
	}

	function CancelOrder($ID, $val, $description = "")
	{
		global $DB, $USER;

		$ID = IntVal($ID);
		$val = (($val != "Y") ? "N" : "Y");
		$description = Trim($description);

		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGO_NO_ORDER_ID1"), "NO_ORDER_ID");
			return False;
		}

		$arOrder = CSaleOrder::GetByID($ID);
		if (!$arOrder)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_NO_ORDER")), "NO_ORDER");
			return False;
		}

		if ($arOrder["CANCELED"] == $val)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGO_DUB_CANCEL")), "ALREADY_FLAG");
			return False;
		}

		$db_events = GetModuleEvents("sale", "OnSaleBeforeCancelOrder");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, $val))===false)
				return false;

		if ($val == "Y")
		{
			if ($arOrder["PAYED"] == "Y")
			{
				if (!CSaleOrder::PayOrder($ID, "N", True, True))
					return False;
			}
			else
			{
				$arOrder["SUM_PAID"] = DoubleVal($arOrder["SUM_PAID"]);
				if ($arOrder["SUM_PAID"] > 0)
				{
					if (!CSaleUserAccount::UpdateAccount($arOrder["USER_ID"], $arOrder["SUM_PAID"], $arOrder["CURRENCY"], "ORDER_CANCEL_PART", $ID))
						return False;
				}
			}
		}

		$arFields = array(
				"CANCELED" => $val,
				"DATE_CANCELED" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"REASON_CANCELED" => ( strlen($description)>0 ? $description : false ),
				"EMP_CANCELED_ID" => ( IntVal($USER->GetID())>0 ? IntVal($USER->GetID()) : false )
			);
		$res = CSaleOrder::Update($ID, $arFields);

		unset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]);

		CSaleBasket::OrderCanceled($ID, (($val=="Y") ? True : False));

		$events = GetModuleEvents("sale", "OnSaleCancelOrder");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $val));

		if ($val == "Y")
		{
			$arOrder = CSaleOrder::GetByID($ID);
			$userEmail = "";
			$dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $ID, "PROP_IS_EMAIL" => "Y"));
			if($arOrderProp = $dbOrderProp->Fetch())
				$userEmail = $arOrderProp["VALUE"];
			if(strlen($userEmail) <= 0)
			{
				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
				if($arUser = $dbUser->Fetch())
					$userEmail = $arUser["EMAIL"];
			}

			$event = new CEvent;
			$arFields = Array(
					"ORDER_ID" => $ID,
					"ORDER_DATE" => $arOrder["DATE_INSERT"],
					"EMAIL" => $userEmail,
					"ORDER_CANCEL_DESCRIPTION" => $description,
					"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
				);
			$event->Send("SALE_ORDER_CANCEL", $arOrder["LID"], $arFields, "N");
			
			if (CModule::IncludeModule("statistic"))
			{
				CStatEvent::AddByEvents("eStore", "order_cancel", $ID, "", $arOrder["STAT_GID"]);
			}
			
		}

		return $res;
	}

	function StatusOrder($ID, $val)
	{
		global $DB, $USER;

		$ID = IntVal($ID);
		$val = trim($val);

		$db_events = GetModuleEvents("sale", "OnSaleBeforeStatusOrder");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, $val))===false)
				return false;

		$arFields = array(
				"STATUS_ID" => $val,
				"DATE_STATUS" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"EMP_STATUS_ID" => ( IntVal($USER->GetID())>0 ? IntVal($USER->GetID()) : false )
			);
		$res = CSaleOrder::Update($ID, $arFields);

		unset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]);

		$events = GetModuleEvents("sale", "OnSaleStatusOrder");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $val));

		$arOrder = CSaleOrder::GetByID($ID);
		$userEmail = "";
		$dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $ID, "PROP_IS_EMAIL" => "Y"));
		if($arOrderProp = $dbOrderProp->Fetch())
			$userEmail = $arOrderProp["VALUE"];
		if(strlen($userEmail) <= 0)
		{
			$dbUser = CUser::GetByID($arOrder["USER_ID"]);
			if($arUser = $dbUser->Fetch())
				$userEmail = $arUser["EMAIL"];
		}
		$dbSite = CSite::GetByID($arOrder["LID"]);
		$arSite = $dbSite->Fetch();
		$arStatus = CSaleStatus::GetByID($arOrder["STATUS_ID"], $arSite["LANGUAGE_ID"]);

		$event = new CEvent;

		$arFields = Array(
				"ORDER_ID" => $ID,
				"ORDER_DATE" => $arOrder["DATE_INSERT"],
				"ORDER_STATUS" => $arStatus["NAME"],
				"EMAIL" => $userEmail,
				"ORDER_DESCRIPTION" => $arStatus["DESCRIPTION"],
				"TEXT" => "",
				"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
			);

		$events = GetModuleEvents("sale", "OnSaleStatusEMail");
		if ($arEvent = $events->Fetch())
		{
			$arFields["TEXT"] = ExecuteModuleEventEx($arEvent, Array($ID, $arStatus["ID"]));
		}

		$eventName = "SALE_STATUS_CHANGED_".$arOrder["STATUS_ID"];

		$eventMessage = new CEventMessage;
		$dbEventMessage = $eventMessage->GetList(
				($b = ""),
				($o = ""),
				array(
						"EVENT_NAME" => $eventName,
						"SITE_ID" => $arOrder["LID"]
					)
			);
		if (!($arEventMessage = $dbEventMessage->Fetch()))
			$eventName = "SALE_STATUS_CHANGED";

		$event->Send($eventName, $arOrder["LID"], $arFields, "N");

		return $res;
	}

	function CommentsOrder($ID, $val)
	{
		global $DB;

		$ID = IntVal($ID);
		$val = Trim($val);

		$arFields = array(
			"COMMENTS" => ( strlen($val)>0 ? $val : false )
			);
		$res = CSaleOrder::Update($ID, $arFields);

		unset($GLOBALS["SALE_ORDER"]["SALE_ORDER_CACHE_".$ID]);

		return $res;
	}


	function Lock($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		$arFields = array(
			"=DATE_LOCK" => $DB->GetNowFunction(),
			"LOCKED_BY" => $GLOBALS["USER"]->GetID()
		);
		return CSaleOrder::Update($ID, $arFields, false);
	}

	function UnLock($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		$arOrder = CSaleOrder::GetByID($ID);
		if (!$arOrder)
			return False;

		$userRights = CMain::GetUserRight("sale", $GLOBALS["USER"]->GetUserGroupArray(), "Y", "Y");

		if (($userRights >= "W") || ($arOrder["LOCKED_BY"] == $GLOBALS["USER"]->GetID()))
		{
			$arFields = array(
				"DATE_LOCK" => false,
				"LOCKED_BY" => false
			);

			if (!CSaleOrder::Update($ID, $arFields, false))
				return False;
			else
				return True;
		}

		return False;
	}

	function IsLocked($ID, &$lockedBY, &$dateLock)
	{
		$ID = IntVal($ID);

		$lockStatus = CSaleOrder::GetLockStatus($ID, $lockedBY, $dateLock);
		if ($lockStatus == "red")
			return true;

		return false;
	}
	
	function RemindPayment()
	{
		$reminder = COption::GetOptionString("sale", "pay_reminder", "");
		$arReminder = unserialize($reminder);
		
		if(!empty($arReminder))
		{
			$arSites = Array();
			$minDay = mktime();
			foreach($arReminder as $key => $val)
			{
				if($val["use"] == "Y")
				{
					$arSites[] = $key;
					$days = Array();
					
					for($i=0; $i <= floor($val["period"] / $val["frequency"]); $i++)
					{
						$day = AddToTimeStamp(Array("DD" => -($val["after"] + $val["period"] - $val["frequency"]*$i)));
						if($day < mktime())
						{
							if($minDay > $day)
								$minDay = $day;
								
							$day = ConvertTimeStamp($day);
							
							$days[] = $day;
						}
					}
					$arReminder[$key]["days"] = $days;
				}
			}

			if(!empty($arSites))
			{
				$bTmpUser = False;
				if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
				{
					$bTmpUser = True;
					$GLOBALS["USER"] = new CUser;
				}
				
				$arFilter = Array(
						"LID" => $arSites,
						"PAYED" => "N",
						"CANCELED" => "N",
						">=DATE_INSERT" => ConvertTimeStamp($minDay),
					);
				
				$dbOrder = CSaleOrder::GetList(Array("ID" => "DESC"), $arFilter, false, false, Array("ID", "DATE_INSERT", "PAYED", "USER_ID", "LID", "PRICE", "CURRENCY"));
				while($arOrder = $dbOrder -> GetNext())
				{
					$date_insert = ConvertDateTime($arOrder["DATE_INSERT"], CSite::GetDateFormat("SHORT"));
					
					if(in_array($date_insert, $arReminder[$arOrder["LID"]]["days"]))
					{
						$event = new CEvent;

						$strOrderList = "";
						$dbBasketTmp = CSaleBasket::GetList(
								array("NAME" => "ASC"),
								array("ORDER_ID" => $arOrder["ID"]),
								false,
								false,
								array("ID", "NAME", "QUANTITY")
							);
						while ($arBasketTmp = $dbBasketTmp->Fetch())
						{
							$strOrderList .= $arBasketTmp["NAME"]." (".$arBasketTmp["QUANTITY"].")";
							$strOrderList .= "\n";
						}
						
						$payerEMail = "";
						$dbOrderProp = CSaleOrderPropsValue::GetList(Array(), Array("ORDER_ID" => $arOrder["ID"], "PROP_IS_EMAIL" => "Y"));
						if($arOrderProp = $dbOrderProp->Fetch())
							$payerEMail = $arOrderProp["VALUE"];

						$payerName = "";
						$dbUser = CUser::GetByID($arOrder["USER_ID"]);
						if ($arUser = $dbUser->Fetch())
						{
							if (strlen($payerName) <= 0)
								$payerName = $arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"];
							if (strlen($payerEMail) <= 0)
								$payerEMail = $arUser["EMAIL"];
						}

						$arFields = Array(
							"ORDER_ID" => $arOrder["ID"],
							"ORDER_DATE" => $date_insert,
							"ORDER_USER" => $payerName,
							"PRICE" => SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]),
							"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
							"EMAIL" => $payerEMail,
							"ORDER_LIST" => $strOrderList,
							"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
						);

						$event->Send("SALE_ORDER_REMIND_PAYMENT", $arOrder["LID"], $arFields, "N");
					}
				}

				if ($bTmpUser)
				{
					unset($GLOBALS["USER"]);
				}

			}
		}
		return "CSaleOrder::RemindPayment();";
	}
}
?>