<?

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/ratings.php");

class CAllRatings
{
	// get specified rating record
	function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: GetByID<br>Line: ";

		if($ID<=0)
			return false;

		return ($DB->Query("
			SELECT
				R.*,
				".$DB->DateToCharFunction("R.CREATED")." as CREATED,
				".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
				".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
			FROM
				b_rating R
			WHERE
				ID=".$ID,
			false, $err_mess.__LINE__));
	}
	
	function GetArrayByID($ID)
	{
		global $DB;
		
		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: GetArrayByID<br>Line: ";
		$strID = "b".$ID;
		if(CACHED_b_rating===false)
		{
			$res = $DB->Query("
				SELECT
					R.*,
					".$DB->DateToCharFunction("R.CREATED")." as CREATED,
					".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
					".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
				FROM
					b_rating R
				WHERE
					ID=".$ID,
			false, $err_mess.__LINE__);
			$arResult = $res->Fetch();
		}
		else
		{
			global $stackCacheManager;
			$stackCacheManager->SetLength("b_rating", 100);
			$stackCacheManager->SetTTL("b_rating", CACHED_b_rating);
			if($stackCacheManager->Exist("b_rating", $strID))
				$arResult = $stackCacheManager->Get("b_rating", $strID);
			else
			{
				$res = $DB->Query("
					SELECT
						R.*,
						".$DB->DateToCharFunction("R.CREATED")." as CREATED,
						".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
						".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
					FROM
						b_rating R
					WHERE
						ID=".$ID,
				false, $err_mess.__LINE__);
				$arResult = $res->Fetch();
				if($arResult)
					$stackCacheManager->Set("b_rating", $strID, $arResult);
			}
		}

		return $arResult;
	}

	// get rating record list
	function GetList($arSort=array(), $arFilter=Array())
	{
		global $DB;

		$arSqlSearch = Array();
		$strSqlSearch = "";
		$err_mess = (CRatings::err_mess())."<br>Function: GetList<br>Line: ";

		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($filter_keys[$i]))
				{
					case "ID":
						$arSqlSearch[] = GetFilterQuery("R.ID",$val,"N");
					break;
					case "ACTIVE":
						if (in_array($val, Array('Y','N')))
							$arSqlSearch[] = "R.ACTIVE = '".$val."'";
					break;
					case "AUTHORITY":
						if (in_array($val, Array('Y','N')))
							$arSqlSearch[] = "R.AUTHORITY = '".$val."'";
					break;
					case "POSITION":
						if (in_array($val, Array('Y','N')))
							$arSqlSearch[] = "R.POSITION = '".$val."'";
					break;
					case "CALCULATED":
						if (in_array($val, Array('Y','N','C')))
							$arSqlSearch[] = "R.CALCULATED = '".$val."'";
					break;
					case "NAME":
						$arSqlSearch[] = GetFilterQuery("R.NAME", $val);
					break;
					case "ENTITY_ID":
						$arSqlSearch[] = GetFilterQuery("R.ENTITY_ID", $val);
					break;
				}
			}
		}

		$sOrder = "";
		foreach($arSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":		$sOrder .= ", R.ID ".$ord; break;
				case "NAME":	$sOrder .= ", R.NAME ".$ord; break;
				case "CREATED":	$sOrder .= ", R.CREATED ".$ord; break;
				case "LAST_MODIFIED":	$sOrder .= ", R.LAST_MODIFIED ".$ord; break;
				case "LAST_CALCULATED":	$sOrder .= ", R.LAST_CALCULATED ".$ord; break;
				case "ACTIVE":	$sOrder .= ", R.ACTIVE ".$ord; break;
				case "AUTHORITY":$sOrder .= ", R.AUTHORITY ".$ord; break;
				case "POSITION":$sOrder .= ", R.POSITION ".$ord; break;
				case "STATUS":	$sOrder .= ", R.CALCULATED ".$ord; break;
				case "CALCULATED":	$sOrder .= ", R.CALCULATED ".$ord; break;
				case "CALCULATION_METHOD":	$sOrder .= ", R.CALCULATION_METHOD ".$ord; break;
				case "ENTITY_ID":	$sOrder .= ", R.ENTITY_ID ".$ord; break;
			}
		}

		if (strlen($sOrder)<=0)
			$sOrder = "R.ID DESC";

		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				R.ID, R.NAME, R.ACTIVE, R.CALCULATED, R.AUTHORITY, R.POSITION, R.ENTITY_ID, R.CALCULATION_METHOD,
				".$DB->DateToCharFunction("R.CREATED")." CREATED,
				".$DB->DateToCharFunction("R.LAST_MODIFIED")." LAST_MODIFIED,
				".$DB->DateToCharFunction("R.LAST_CALCULATED")." LAST_CALCULATED
			FROM
				b_rating R
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}
	
	function GetRatingValueInfo($ratingId)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: GetRatingValueInfo<br>Line: ";
		$ratingId = intval($ratingId);
		
		$strSql = "
			SELECT 
				MAX(CURRENT_VALUE) as MAX, 
				MIN(CURRENT_VALUE) as MIN, 
				AVG(CURRENT_VALUE) as AVG, 
				COUNT(*) as CNT
			FROM b_rating_results
			WHERE RATING_ID = ".$ratingId;
		return $DB->Query($strSql, false, $err_mess.__LINE__);
	}

	//Addition rating
	function Add($arFields)
	{
		global $DB, $stackCacheManager;

		$err_mess = (CRatings::err_mess())."<br>Function: Add<br>Line: ";

		// check only general field
		if(!CRatings::__CheckFields($arFields))
			return false;

		$arFields_i = Array(
			"ACTIVE"				=> $arFields["ACTIVE"] == 'Y' ? 'Y' : 'N',
			"POSITION"				=> $arFields["POSITION"] == 'Y' ? 'Y' : 'N',
			"AUTHORITY"				=> $arFields["AUTHORITY"] == 'Y' ? 'Y' : 'N',
			"NAME"					=> $arFields["NAME"],
			"ENTITY_ID"		 		=> $arFields["ENTITY_ID"],
			"CALCULATION_METHOD"	=> $arFields["CALCULATION_METHOD"],
			"~CREATED"				=> $DB->GetNowFunction(),
			"~LAST_MODIFIED"		=> $DB->GetNowFunction(),
		);
		$ID = $DB->Add("b_rating", $arFields_i);

		// queries modules and give them to inspect the field settings
		$db_events = GetModuleEvents("main", "OnAfterAddRating");
		while($arEvent = $db_events->Fetch())
			$arFields = ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		CRatings::__AddComponents($ID, $arFields);

		$arFields_u = Array(
			"CONFIGS" => "'".serialize($arFields["CONFIGS"])."'",
		);

		$DB->Update("b_rating", $arFields_u, "WHERE ID = ".$ID);
		
		if ($arFields['AUTHORITY'] == 'Y')
			CRatings::SetAuthorityRating($ID);
			
		CAgent::AddAgent("CRatings::Calculate($ID);", "main", "N", 3600, "", "Y", "");

		$stackCacheManager->Clear("b_rating");
		
		return $ID;
	}

	//Update rating
	function Update($ID, $arFields)
	{
		global $DB, $stackCacheManager;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: Update<br>Line: ";

		// check only general field
		if(!CRatings::__CheckFields($arFields))
			return false;

		$arFields_u = Array(
			"ACTIVE"				=> $arFields['ACTIVE'] == 'Y' ? 'Y' : 'N',
			"NAME"					=> $arFields["NAME"],
			"ENTITY_ID"		 		=> $arFields["ENTITY_ID"],
			"CALCULATION_METHOD"	=> $arFields["CALCULATION_METHOD"],
			"~LAST_MODIFIED"		=> $DB->GetNowFunction(),
		);
		$strUpdate = $DB->PrepareUpdate("b_rating", $arFields_u);
		if(!$DB->Query("UPDATE b_rating SET ".$strUpdate." WHERE ID=".$ID, false, $err_mess.__LINE__))
			return false;
		
		if (!isset($arFields["CONFIGS"]))
		{
			$stackCacheManager->Clear("b_rating");
			return true;
		}
		// queries modules and give them to inspect the field settings
		$db_events = GetModuleEvents("main", "OnAfterUpdateRating");
		while($arEvent = $db_events->Fetch())
			$arFields = ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		CRatings::__UpdateComponents($ID, $arFields);

		$arFields_u = Array(
			"POSITION" => "'".($arFields['POSITION'] == 'Y' ? 'Y' : 'N')."'",
			"AUTHORITY" => "'".($arFields['AUTHORITY'] == 'Y' ? 'Y' : 'N')."'",
			"CONFIGS"  => "'".serialize($arFields["CONFIGS"])."'",
		);
		$DB->Update("b_rating", $arFields_u, "WHERE ID = ".$ID);

		if ($arFields['AUTHORITY'] == 'Y')
			CRatings::SetAuthorityRating($ID);
			
		if ($arFields['NEW_CALC'] == 'Y')
			$DB->Query("UPDATE b_rating_results SET PREVIOUS_VALUE = 0 WHERE RATING_ID=".$ID." and ENTITY_TYPE_ID='".$DB->ForSql($arFields["ENTITY_ID"])."'", false, $err_mess.__LINE__);

		$strSql = "SELECT COMPLEX_NAME FROM b_rating_component WHERE RATING_ID = $ID and ACTIVE = 'N'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$arrRatingComponentId = array();
		while($arRes = $res->Fetch())
			$arrRatingComponentId[] = $arRes['COMPLEX_NAME'];

		if (!empty($arrRatingComponentId))
			$DB->Query("DELETE FROM b_rating_component_results WHERE RATING_ID = $ID AND COMPLEX_NAME IN ('".implode("','", $arrRatingComponentId)."')", false, $err_mess.__LINE__);

		CRatings::Calculate($ID, true);

		CAgent::RemoveAgent("CRatings::Calculate($ID);", "main");
		$AID = CAgent::AddAgent("CRatings::Calculate($ID);", "main", "N", 3600, "", "Y", "");

		$stackCacheManager->Clear("b_rating");		
		
		return true;
	}

	// delete rating
	function Delete($ID)
	{
		global $DB, $stackCacheManager;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: Delete<br>Line: ";

		$db_events = GetModuleEvents("main", "OnBeforeDeleteRating");
		while($arEvent = $db_events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		$DB->Query("DELETE FROM b_rating WHERE ID=$ID", false, $err_mess.__LINE__);
		$DB->Query("DELETE FROM b_rating_user WHERE RATING_ID=$ID", false, $err_mess.__LINE__);
		$DB->Query("DELETE FROM b_rating_component WHERE RATING_ID=$ID", false, $err_mess.__LINE__);
		$DB->Query("DELETE FROM b_rating_component_results WHERE RATING_ID=$ID", false, $err_mess.__LINE__);
		$DB->Query("DELETE FROM b_rating_results WHERE RATING_ID=$ID", false, $err_mess.__LINE__);

		CAgent::RemoveAgent("CRatings::Calculate($ID);", "main");

		$stackCacheManager->Clear("b_rating");	
		
		return true;
	}

	// start calculation rating-component
	function Calculate($ID, $bForceRecalc = false)
	{
		global $DB;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: Calculate<br>Line: ";

		$strSql = "SELECT
						RC.*,
						".$DB->DateToCharFunction("RC.LAST_MODIFIED")."	LAST_MODIFIED,
						".$DB->DateToCharFunction("RC.LAST_CALCULATED")." LAST_CALCULATED,
						".$DB->DateToCharFunction("RC.NEXT_CALCULATION")." NEXT_CALCULATION
				  FROM
				  		b_rating_component RC
				  WHERE
				  		RATING_ID = $ID
				  	and ACTIVE = 'Y' ".($bForceRecalc ? '' : 'AND NEXT_CALCULATION <= '.$DB->GetNowFunction());
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		while($arRes = $res->Fetch())
		{
			if(CModule::IncludeModule(strtolower($arRes['MODULE_ID']))) {
				$arRes['CONFIG'] = unserialize($arRes['CONFIG']);
				// If the type is automatic calculation of parameters * global vote weight
				$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
				if ($sRatingWeightType == 'auto') {
					$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);
					$arRes['CONFIG']['COEFFICIENT'] = $arRes['CONFIG']['COEFFICIENT']*$voteWeight;
				}
				if (strlen($arRes['EXCEPTION_METHOD']) > 0)
				{
					if (method_exists($arRes['CLASS'], $arRes['EXCEPTION_METHOD']))
					{
						$exceptionText = call_user_func(array($arRes['CLASS'], $arRes['EXCEPTION_METHOD']));
						if ($exceptionText === false)
							if (method_exists($arRes['CLASS'],  $arRes['CALC_METHOD']))
								$result = call_user_func(array($arRes['CLASS'], $arRes['CALC_METHOD']), $arRes);
					}
				}
				else
				{
					if (method_exists($arRes['CLASS'],  $arRes['CALC_METHOD']))
						$result = call_user_func(array($arRes['CLASS'], $arRes['CALC_METHOD']), $arRes);
				}
			}
		}

		CRatings::BuildRating($ID);

		return "CRatings::Calculate($ID);";
	}

	// queries modules and get all the available objects
	function GetRatingObjects()
	{
		$arObjects = array();

		$db_events = GetModuleEvents("main", "OnGetRatingsObjects");
		while($arEvent = $db_events->Fetch())
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			foreach ($arConfig as $OBJ_TYPE)
				if (!in_array($OBJ_TYPE, $arObjects))
					$arObjects[] = $OBJ_TYPE;
		}
		return $arObjects;
	}

	// queries modules and get all the available entity types
	function GetRatingEntityTypes($objectType = null)
	{
		$arEntityTypes = array();

		$db_events = GetModuleEvents("main", "OnGetRatingsConfigs");
		while($arEvent = $db_events->Fetch())
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			if (is_null($objectType))
			{
				foreach ($arConfig as $OBJ_TYPE => $OBJ_VALUE)
					foreach ($OBJ_VALUE['VOTE'] as $VOTE_VALUE)
					{
						$EntityTypeId = $VOTE_VALUE['MODULE_ID'].'_'.$VOTE_VALUE['ID'];
						if (!in_array($arEntityTypes[$OBJ_TYPE], $EntityTypeId))
							$arEntityTypes[$OBJ_TYPE][] = $EntityTypeId;
					}
			}
			else
			{
				foreach ($arConfig[$objectType]['VOTE'] as $VOTE_VALUE)
				{
					$EntityTypeId = $VOTE_VALUE['MODULE_ID'].'_'.$VOTE_VALUE['ID'];
					$arEntityTypes[$EntityTypeId] = $EntityTypeId;
				}
			}
		}

		return $arEntityTypes;
	}

	// queries modules and assemble an array of settings
	function GetRatingConfigs($objectType = null, $withRatingType = true)
	{
		$arConfigs = array();

		$db_events = GetModuleEvents("main", "OnGetRatingsConfigs");
		while($arEvent = $db_events->Fetch())
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			if (is_null($objectType))
			{
				foreach ($arConfig["COMPONENT"] as $OBJ_TYPE => $TYPE_VALUE)
					foreach ($TYPE_VALUE as $RAT_TYPE => $RAT_VALUE)
					   foreach ($RAT_VALUE as $VALUE)
					   		if ($withRatingType)
					   			$arConfigs[$OBJ_TYPE][$arConfig['MODULE_ID']][$RAT_TYPE][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
					   		else
					   			$arConfigs[$OBJ_TYPE][$arConfig['MODULE_ID']][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
			}
			else
			{
				foreach ($arConfig["COMPONENT"][$objectType] as $RAT_TYPE => $RAT_VALUE)
				{
					$arConfigs[$arConfig['MODULE_ID']]['MODULE_ID'] = $arConfig['MODULE_ID'];
					$arConfigs[$arConfig['MODULE_ID']]['MODULE_NAME'] = $arConfig['MODULE_NAME'];
					foreach ($RAT_VALUE as $VALUE)
						if ($withRatingType)
							$arConfigs[$arConfig['MODULE_ID']][$RAT_TYPE][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
						else
							$arConfigs[$arConfig['MODULE_ID']][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
				}
			}
		}

		return $arConfigs;
	}


	function GetRatingVoteResult($arEntityTypeId, $entityId, $user_id = false)
	{
		global $DB;
		$arResults = array();
		$arVotingIds = array();
		$arVotingEntityIds = array();
		$sqlEntityId = "";
		$bReturnEntityArray = true;
		$user_id = intval($user_id);
		if ($user_id == 0)
			$user_id = $GLOBALS["USER"]->GetID();

		$err_mess = (CRatings::err_mess())."<br>Function: GetRatingVoteResult<br>Line: ";

		if (empty($entityId))
			return $arRating;

		if (is_array($entityId))
		{
			foreach ($entityId as $key=>$value)
				$entityId[$key] = IntVal($value);
			$sqlEntityId = " AND ENTITY_ID IN (".implode(',', $entityId).") ";
			$bReturnEntityArray = true;
		}
		else
		{
			$sqlEntityId = " AND ENTITY_ID = ".$entityId;
			$bReturnEntityArray = false;
		}

		$sql_str = "SELECT
						ID,
						ENTITY_ID,
						TOTAL_VALUE,
						TOTAL_VOTES,
						TOTAL_POSITIVE_VOTES,
						TOTAL_NEGATIVE_VOTES
					FROM
						b_rating_voting
					WHERE
						ENTITY_TYPE_ID = '".$DB->ForSql($arEntityTypeId)."' ".$sqlEntityId."
					and ACTIVE = 'Y'";
		$z = $DB->Query($sql_str, false, $err_mess.__LINE__);
		while($r = $z->Fetch())
		{
			$arVotingIds[] = $r['ID'];
			$arVotingEntityIds[$r['ID']] = $r['ENTITY_ID'];

			$arResult = array(
				'USER_HAS_VOTED' => "N",
				'TOTAL_VALUE' => floatval($r['TOTAL_VALUE']),
				'TOTAL_VOTES' => intval($r['TOTAL_VOTES']),
				'TOTAL_POSITIVE_VOTES' => intval($r['TOTAL_POSITIVE_VOTES']),
				'TOTAL_NEGATIVE_VOTES' => intval($r['TOTAL_NEGATIVE_VOTES']),
			);
			if ($bReturnEntityArray)
				$arResults[$r['ENTITY_ID']] = $arResult;
			else
			    $arResults = $arResult;
		}

		if (!empty($arVotingIds) && IntVal($user_id) > 0)
		{
			$sql_str = "SELECT RATING_VOTING_ID FROM b_rating_vote WHERE RATING_VOTING_ID IN (".implode(',', $arVotingIds).") AND USER_ID = ".$user_id;
			$z = $DB->Query($sql_str, false, $err_mess.__LINE__);
			while($r = $z->Fetch())
			{
				if ($bReturnEntityArray)
					$arResults[$arVotingEntityIds[$r['RATING_VOTING_ID']]]['USER_HAS_VOTED'] = "Y";
				else
			    	$arResults['USER_HAS_VOTED'] = "Y";

			}
		}

		return $arResults;
	}

	function GetRatingResult($ID, $entityId)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: GetRatingResult<br>Line: ";
		$ID = IntVal($ID);

		static $cacheRatingResult = array();
		if(!array_key_exists($ID, $cacheRatingResult))
			$cacheRatingResult[$ID] = array();

		$arResult = array();
		$arToSelect = array();
		if(is_array($entityId))
		{
			foreach($entityId as $value)
			{
				$value = intval($value);
				if($value > 0)
				{
					if(array_key_exists($value, $cacheRatingResult[$ID]))
						$arResult[$value] = $cacheRatingResult[$ID][$value];
					else
					{	
						$arResult[$value] = $cacheRatingResult[$ID][$value] = array();
						$arToSelect[$value] = $value;
					}
				}
			}
		}
		else
		{
			$value = intval($entityId);
			if($value > 0)
			{
				if(isset($cacheRatingResult[$ID][$value]))
					$arResult[$value] = $cacheRatingResult[$ID][$value];
				else
				{	
					$arResult[$value] = $cacheRatingResult[$ID][$value] = array();
					$arToSelect[$value] = $value;
				}
			}
		}

		if(!empty($arToSelect))
		{
			$strSql  = "
				SELECT ENTITY_TYPE_ID, ENTITY_ID, PREVIOUS_VALUE, CURRENT_VALUE, PREVIOUS_POSITION, CURRENT_POSITION 
				FROM b_rating_results
				WHERE RATING_ID = '".$ID."'  AND ENTITY_ID IN (".implode(',', $arToSelect).")
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($arRes = $res->Fetch())
			{

				$arRes['PROGRESS_VALUE'] = $arRes['CURRENT_VALUE'] - $arRes['PREVIOUS_VALUE'];
				$arRes['PROGRESS_VALUE'] = round($arRes['PROGRESS_VALUE'], 2);
				$arRes['PROGRESS_VALUE'] = $arRes['PROGRESS_VALUE'] > 0? "+".$arRes['PROGRESS_VALUE']: $arRes['PROGRESS_VALUE'];
				$arRes['ROUND_CURRENT_VALUE'] = round($arRes['CURRENT_VALUE']) == 0? 0: round($arRes['CURRENT_VALUE']);
				$arRes['ROUND_PREVIOUS_VALUE'] = round($arRes['PREVIOUS_VALUE']) == 0? 0: round($arRes['CURRENT_VALUE']);				
				$arRes['CURRENT_POSITION'] = $arRes['CURRENT_POSITION'] > 0? $arRes['CURRENT_POSITION'] : GetMessage('RATING_NO_POSITION');
				if ($arRes['PREVIOUS_POSITION']>0)
				{
					$arRes['PROGRESS_POSITION'] = $arRes['PREVIOUS_POSITION'] - $arRes['CURRENT_POSITION'];
					$arRes['PROGRESS_POSITION'] = $arRes['PROGRESS_POSITION'] > 0? "+".$arRes['PROGRESS_POSITION']: $arRes['PROGRESS_POSITION'];
				}
				else
				{
					$arRes['PREVIOUS_POSITION'] = 0;
					$arRes['PROGRESS_POSITION'] = 0;
				}

				$arResult[$arRes["ENTITY_ID"]] = $cacheRatingResult[$ID][$arRes["ENTITY_ID"]] = $arRes;
			}
		}
		if(!is_array($entityId) && !empty($arResult))
			$arResult = array_pop($arResult);

		return $arResult;
	}


	function AddRatingVote($arParam)
	{
		global $DB;
		
		if (isset($_SESSION['RATING_VOTE_COUNT']) && $arParam['ENTITY_TYPE_ID'] == 'USER')
		{
			if ($_SESSION['RATING_VOTE_COUNT'] >= $_SESSION['RATING_USER_VOTE_COUNT'])
				return false;
			else
				$_SESSION['RATING_VOTE_COUNT']++;
		}
			
		$userId = intval($arParam['USER_ID']);
		$sqlStr = "
			SELECT *
			FROM 
				b_rating_voting RVG, 
				b_rating_vote RV
			WHERE 
				RVG.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
			and RVG.ENTITY_ID = ".intval($arParam['ENTITY_ID'])."
			and RVG.ID = RV.RATING_VOTING_ID
			and RV.USER_ID = ".$userId;
		$res = $DB->Query($sqlStr, false, $err_mess.__LINE__);
		if ($res->Fetch())
			return false;
		
		$err_mess = (CRatings::err_mess())."<br>Function: AddRatingVote<br>Line: ";
		$votePlus = $arParam['VALUE'] < 0 ? false : true;

		$ratingId = CRatings::GetAuthorityRating();
		
		$arRatingUserProp = CRatings::GetRatingUserProp($ratingId, $userId);
		$voteUserWeight = $arRatingUserProp['VOTE_WEIGHT'];
		
		$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
		if ($sRatingWeightType == 'auto')
		{
			if ($arParam['ENTITY_TYPE_ID'] == 'USER')
			{			
				$sRatingAuthrorityWeight = COption::GetOptionString("main", "rating_authority_weight_formula", 'Y');
				if ($sRatingAuthrorityWeight == 'Y') 
				{
					$communitySize = COption::GetOptionString("main", "rating_community_size", 1);	
					$communityAuthority = COption::GetOptionString("main", "rating_community_authority", 1);	
					$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);	
					$arParam['VALUE'] = $arParam['VALUE']*($communitySize*($voteUserWeight/$voteWeight)/$communityAuthority);
				}
				else
				{
					$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);
					$arParam['VALUE'] = $arParam['VALUE']*$voteWeight/$voteWeight;
				}
			}
			else
			{
				$arParam['VALUE'] = $arParam['VALUE']*$voteUserWeight;
			}
		} 
		else 
		{
			$arParam['VALUE'] = $arParam['VALUE']*$voteUserWeight;
		}
		$arFields = array(
			'ACTIVE' => "'Y'",
			'TOTAL_VOTES' => "TOTAL_VOTES+1",
			'TOTAL_VALUE' => "TOTAL_VALUE".($votePlus ? '+' : '').floatval($arParam['VALUE']),
			'LAST_CALCULATED' => $DB->GetNowFunction(),
		);
		$arFields[($votePlus ? 'TOTAL_POSITIVE_VOTES' : 'TOTAL_NEGATIVE_VOTES')] = ($votePlus ? 'TOTAL_POSITIVE_VOTES+1' : 'TOTAL_NEGATIVE_VOTES+1');

		$rowAffected = $DB->Update("b_rating_voting", $arFields, "WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".intval($arParam['ENTITY_ID'])."'" , $err_mess.__LINE__);
        if ($rowAffected > 0)
        {
			$rsRV = $DB->Query("SELECT ID FROM b_rating_voting WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".intval($arParam['ENTITY_ID'])."'", false, $err_mess.__LINE__);
			$arRV = $rsRV->Fetch();
			$votingId = $arRV['ID'];
        }
        else
        {
			$arFields = array(
				"ENTITY_TYPE_ID"		=> "'".$DB->ForSql($arParam["ENTITY_TYPE_ID"])."'",
				"ENTITY_ID"				=> intval($arParam['ENTITY_ID']),
				"ACTIVE"				=> "'Y'",
				"CREATED"				=> $DB->GetNowFunction(),
				"LAST_CALCULATED"		=> $DB->GetNowFunction(),
				"TOTAL_VOTES"			=> 1,
				"TOTAL_VALUE"			=> floatval($arParam['VALUE']),
				"TOTAL_POSITIVE_VOTES"	=> ($votePlus ? 1 : 0),
				"TOTAL_NEGATIVE_VOTES"	=> ($votePlus ? 0 : 1)
			);
			$votingId = $DB->Insert("b_rating_voting", $arFields, $err_mess.__LINE__);
        }

	    $arFields = array(
			"RATING_VOTING_ID"	=> $votingId,
			"VALUE"				=> floatval($arParam['VALUE']),
			"ACTIVE"			=> "'Y'",
			"CREATED"			=> $DB->GetNowFunction(),
			"USER_ID"			=> intval($arParam['USER_ID']),
			"USER_IP"			=> "'".$DB->ForSql($arParam["USER_IP"])."'"
		);
		$DB->Insert("b_rating_vote", $arFields, $err_mess.__LINE__);
		
		return true;
	}

	function UpdateRatingUserBonus($arParam)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: AddRatingBonus<br>Line: ";

		$arParam['RATING_ID'] = intval($arParam['RATING_ID']);
		$arParam['ENTITY_ID'] = intval($arParam['ENTITY_ID']);
		$arParam['BONUS'] = floatval($arParam['BONUS']);

	    $arFields = array(
			'RATING_ID'	=> $arParam['RATING_ID'],
			'ENTITY_ID'	=> $arParam['ENTITY_ID'],
			'BONUS'		=> $arParam['BONUS'],
		);
		$rows = $DB->Update("b_rating_user", $arFields, "WHERE RATING_ID = ".$arParam['RATING_ID']." AND ENTITY_ID = ".$arParam['ENTITY_ID']);
		if ($rows == 0)
		{	
			$rsRB = $DB->Query("SELECT * FROM b_rating_user WHERE RATING_ID = ".$arParam['RATING_ID']." AND ENTITY_ID = ".$arParam['ENTITY_ID'], false, $err_mess.__LINE__);
			if (!$rsRB->SelectedRowsCount())
				$DB->Insert("b_rating_user", $arFields, $err_mess.__LINE__);
		}
		return true;
	}

	function GetRatingUserProp($ratingId, $entityId)
	{
		global $DB;
		$ratingId = IntVal($ratingId);

		static $cache = array();
		if(!array_key_exists($ratingId, $cache))
			$cache[$ratingId] = array();

		$arResult = array();
		$arToSelect = array();
		if(is_array($entityId))
		{
			foreach($entityId as $value)
			{
				$value = intval($value);
				if($value > 0)
				{
					if(array_key_exists($value, $cache[$ratingId]))
						$arResult[$value] = $cache[$ratingId][$value];
					else
					{
						$arResult[$value] = $cache[$ratingId][$value] = array();
						$arToSelect[$value] = $value;
					}
				}
			}
		}
		else
		{
			$value = intval($entityId);
			if($value > 0)
			{
				if(isset($cache[$ratingId][$value]))
					$arResult[$value] = $cache[$ratingId][$value];
				else
				{
					$arResult[$value] = $cache[$ratingId][$value] = array();
					$arToSelect[$value] = $value;
				}
			}
		}

		if(!empty($arToSelect))
		{
			$strSql  = "
				SELECT RATING_ID, ENTITY_ID, BONUS, VOTE_WEIGHT, VOTE_COUNT
				FROM b_rating_user
				WHERE RATING_ID = '".$ratingId."' AND ENTITY_ID IN (".implode(',', $arToSelect).")
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($arRes = $res->Fetch())
				$arResult[$arRes["ENTITY_ID"]] = $cache[$ratingId][$arRes["ENTITY_ID"]] = $arRes;
		}

		if(!is_array($entityId) && !empty($arResult))
			$arResult = array_pop($arResult);

		return $arResult;
	}

	function GetAuthorityRating()
	{
		global $DB;

		static $authorityRatingId = null;
		if(!is_null($authorityRatingId))
			return $authorityRatingId;
					
		$db_res = CRatings::GetList(array("ID" => "ASC"), array( "ENTITY_ID" => "USER", "AUTHORITY" => "Y"));
		$res = $db_res->Fetch();
		
		return $authorityRatingId = intval($res['ID']);		
	}	
	
	function GetWeightList($arSort=array(), $arFilter=Array())
	{
		global $DB;

		$arSqlSearch = Array();
		$strSqlSearch = "";
		$err_mess = (CRatings::err_mess())."<br>Function: GetWeightList<br>Line: ";

		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($filter_keys[$i]))
				{
					case "ID":
						$arSqlSearch[] = GetFilterQuery("RW.ID",$val,"N");
					break;
					case "RATING_FROM":
						$arSqlSearch[] = GetFilterQuery("RW.RATING_FROM",$val,"N");
					break;
					case "RATING_TO":
						$arSqlSearch[] = GetFilterQuery("RW.RATING_TO",$val,"N");
					break;
					case "WEIGHT":
						$arSqlSearch[] = GetFilterQuery("RW.WEIGHT",$val,"N");
					break;
					case "COUNT":
						$arSqlSearch[] = GetFilterQuery("RW.COUNT",$val,"N");
					break;
					case "MAX":
						if (in_array($val, Array('Y','N')))
							$arSqlSearch[] = "R.MAX = '".$val."'";
					break;
				}
			}
		}

		$sOrder = "";
		foreach($arSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":		$sOrder .= ", RW.ID ".$ord; break;
				case "RATING_FROM":	$sOrder .= ", RW.RATING_FROM ".$ord; break;
				case "RATING_TO":		$sOrder .= ", RW.RATING_TO ".$ord; break;
				case "WEIGHT":	$sOrder .= ", RW.WEIGHT ".$ord; break;
				case "COUNT":	$sOrder .= ", RW.COUNT ".$ord; break;
			}
		}

		if (strlen($sOrder)<=0)
			$sOrder = "RW.ID DESC";

		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				RW.ID, RW.RATING_FROM, RW.RATING_TO, RW.WEIGHT, RW.COUNT
			FROM
				b_rating_weight RW
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}
	
	function SetWeight($arConfigs)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: SetWeight<br>Line: ";

		usort($arConfigs, array('CRatings', '__SortWeight'));
		// prepare insert
		$arAdd = array();
		foreach($arConfigs as $key => $arConfig)
		{
			//If the first condition is restricted to the bottom, otherwise we take the previous high value
			if ($key == 0)
				$arConfig['RATING_FROM'] = -1000000;
			else
				$arConfig['RATING_FROM'] = floatval($arConfigs[$key-1]['RATING_TO'])+0.0001;
			// If this last condition is restricted to the top
			if (!array_key_exists('RATING_TO', $arConfig))
				$arConfig['RATING_TO'] = 1000000;
			elseif ($arConfig['RATING_TO'] > 1000000)
				$arConfig['RATING_TO'] = 1000000;
				
			$arAdd[$key]['RATING_FROM']   = floatval($arConfig['RATING_FROM']);
			$arAdd[$key]['RATING_TO']     = floatval($arConfig['RATING_TO']);
			$arAdd[$key]['WEIGHT'] = floatval($arConfig['WEIGHT']);
			$arAdd[$key]['COUNT']  = intval($arConfig['COUNT']);
			$arConfigs[$key] = $arAdd[$key];
		}	
		// insert
		$DB->Query("DELETE FROM b_rating_weight", false, $err_mess.__LINE__);
		foreach($arAdd as $key => $arFields)
			$DB->Insert("b_rating_weight", $arFields, $err_mess.__LINE__);
		
		return true;
	}
	
	function SetVoteGroup($arGroupId, $type)
	{
		global $DB;
		if (!in_array($type, array('R', 'A')))
			return false;
		
		$arFields = array();
		foreach ($arGroupId as $key => $value)
		{
			$arField = array();
			$arField['GROUP_ID'] = intval($value);
			$arField['TYPE'] = "'".$type."'";
			$arFields[$key] = $arField;		
		}
		
		$DB->Query("DELETE FROM b_rating_vote_group WHERE TYPE = '".$type."'", false, $err_mess.__LINE__);
		foreach($arFields as $key => $arField)
			$DB->Insert("b_rating_vote_group", $arField, $err_mess.__LINE__);
		
		return true;
	}
	
	function GetVoteGroup($type = '')
	{
		global $DB;
		$bAllType = false;
		if (!in_array($type, array('R', 'A')))
			$bAllType = true;

		$strSql = "SELECT ID, GROUP_ID, TYPE FROM b_rating_vote_group RVG";
		
		if (!$bAllType)
			$strSql .= " WHERE TYPE = '".$type."'";
			
		return $DB->Query($strSql, false, $err_mess.__LINE__);
	}
	
	function OnAfterUserRegister($arFields)
    {
		$userId = isset($arFields["USER_ID"]) ? intval($arFields["USER_ID"]): (isset($arFields["ID"]) ? intval($arFields["ID"]): 0);
        if($userId>0)
        {
			$authorityRatingId = CRatings::GetAuthorityRating();
			$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
            $arParam = array(
				'RATING_ID' => $authorityRatingId,
				'ENTITY_ID' => $userId,
				'BONUS' => $ratingStartValue,
			);
			CRatings::UpdateRatingUserBonus($arParam);
        }
    }
	
	function __SortWeight($a, $b)
	{
		if (isset($a['RATING_FROM']) || isset($b['RATING_FROM']))
			return 1;
			
		return floatval($a['RATING_TO']) < floatval($b['RATING_TO']) ? -1 : 1;
	}
	
	// check only general field
	function __CheckFields($arFields)
	{
		$aMsg = array();

		if(is_set($arFields, "NAME") && trim($arFields["NAME"])=="")
			$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("RATING_GENERAL_ERR_NAME"));
		if(is_set($arFields, "ACTIVE") && !($arFields["ACTIVE"] == 'Y' || $arFields["ACTIVE"] == 'N'))
			$aMsg[] = array("id"=>"ACTIVE", "text"=>GetMessage("RATING_GENERAL_ERR_ACTIVE"));
		if(is_set($arFields, "ENTITY_ID"))
		{
			$arObjects = CRatings::GetRatingObjects();
			if(!in_array($arFields['ENTITY_ID'], $arObjects))
				$aMsg[] = array("id"=>"ENTITY_ID", "text"=>GetMessage("RATING_GENERAL_ERR_ENTITY_ID"));
		}
		if(is_set($arFields, "CALCULATION_METHOD") && trim($arFields["CALCULATION_METHOD"])=="")
			$aMsg[] = array("id"=>"CALCULATION_METHOD", "text"=>GetMessage("RATING_GENERAL_ERR_CAL_METHOD"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	// creates a configuration record for each item rating
	function __AddComponents($ID, $arFields)
	{
		global $DB;

		$arRatingConfigs = CRatings::GetRatingConfigs($arFields["ENTITY_ID"], false);

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: __AddComponents<br>Line: ";
		foreach ($arFields['CONFIGS'] as $MODULE_ID => $RAT_ARRAY)
			foreach ($RAT_ARRAY as $RAT_TYPE => $COMPONENT)
				foreach ($COMPONENT as $COMPONENT_NAME => $COMPONENT_VALUE)
				{
					$arFields_i = Array(
						"RATING_ID"			=> $ID,
						"ACTIVE"			=> isset($COMPONENT_VALUE["ACTIVE"]) && $COMPONENT_VALUE["ACTIVE"] == 'Y' ? 'Y' : 'N',
						"ENTITY_ID"			=> $arFields["ENTITY_ID"],
						"MODULE_ID"			=> $MODULE_ID,
						"RATING_TYPE"		=> $RAT_TYPE,
						"NAME"				=> $COMPONENT_NAME,
						"COMPLEX_NAME"		=> $arFields["ENTITY_ID"].'_'.$MODULE_ID.'_'.$RAT_TYPE.'_'.$COMPONENT_NAME,
						"CLASS"				=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["CLASS"],
						"CALC_METHOD"		=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["CALC_METHOD"],
						"EXCEPTION_METHOD"	=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["EXCEPTION_METHOD"],
						"REFRESH_INTERVAL"	=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["REFRESH_TIME"],
						"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
					    "~NEXT_CALCULATION" => $DB->GetNowFunction(),
						"IS_CALCULATED"		=> "N",
						"~CONFIG"			=> "'".serialize($COMPONENT_VALUE)."'",
					);

					$DB->Add("b_rating_component", $arFields_i, array(), "", false, $err_mess.__LINE__);
				}


		return true;
	}

	function __UpdateComponents($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: __UpdateComponents<br>Line: ";

		$DB->Query("DELETE FROM b_rating_component WHERE RATING_ID=$ID", false, $err_mess.__LINE__);

		CRatings::__AddComponents($ID, $arFields, $arConfigs);

		return true;
	}
	


	function err_mess()
	{
		return "<br>Class: CRatings<br>File: ".__FILE__;
	}
}
?>