<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/ratings.php");
IncludeModuleLangFile(__FILE__);

class CRatings extends CAllRatings
{
	function err_mess()
	{
		return "<br>Class: CRatings<br>File: ".__FILE__;
	}

	// building rating on computed components
	function BuildRating($ID)
	{
		global $DB;

		$ID = intval($ID);
		$err_mess = (CRatings::err_mess())."<br>Function: BuildRating<br>Line: ";

		$resRating = CRatings::GetByID($ID);
		$arRating = $resRating->Fetch();
		if ($arRating && $arRating['ACTIVE'] == 'Y') {
			$DB->Query("UPDATE b_rating SET CALCULATED = 'C' WHERE id = ".$ID, false, $err_mess.__LINE__);

			// Insert new results
			$sqlFunc = ($arRating['CALCULATION_METHOD'] == 'SUM') ? 'SUM' : 'AVG';
			$strSql  = "
				INSERT INTO b_rating_results 
					(RATING_ID, ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, PREVIOUS_VALUE)
				SELECT 
					".$ID." RATING_ID, 
					'".$arRating['ENTITY_ID']."' ENTITY_TYPE_ID, 
					RC.ENTITY_ID, 
					".$sqlFunc."(RC.CURRENT_VALUE) CURRENT_VALUE,	
					0 PREVIOUS_VALUE
				FROM 
					b_rating_component_results RC LEFT JOIN b_rating_results RR ON RR.RATING_ID = RC.RATING_ID and RR.ENTITY_ID = RC.ENTITY_ID
				WHERE 
					RC.RATING_ID = ".$ID." and RR.ID IS NULL
				GROUP BY RC.ENTITY_ID";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);

			// Update current results
			$strSql =  "
					UPDATE
						b_rating_results RR,
						(	SELECT '".$arRating['ENTITY_ID']."' ENTITY_TYPE_ID,	RC.ENTITY_ID, ".$sqlFunc."(RC.CURRENT_VALUE) CURRENT_VALUE
							FROM b_rating_component_results RC INNER JOIN b_rating_results RR on RR.RATING_ID = RC.RATING_ID and RR.ENTITY_ID = RC.ENTITY_ID
							WHERE RC.RATING_ID = ".$ID."
							GROUP BY RC.ENTITY_ID
						) as RCR
					SET
						RR.PREVIOUS_VALUE = IF(RR.CURRENT_VALUE = RCR.CURRENT_VALUE, RR.PREVIOUS_VALUE, RR.CURRENT_VALUE),
						RR.CURRENT_VALUE = RCR.CURRENT_VALUE
					WHERE
						RR.RATING_ID=".$ID."
					and	RR.ENTITY_TYPE_ID = RCR.ENTITY_TYPE_ID
					and	RR.ENTITY_ID = RCR.ENTITY_ID
					";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);

			// Calculation position in rating
			if ($arRating['POSITION'] == 'Y') {
				$strSql =  "
					UPDATE
						b_rating_results RR,
						(	SELECT ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, @nPos:=@nPos+1  as POSITION
							FROM b_rating_results, (select @nPos:=0) tmp
							WHERE RATING_ID = ".$ID."
							ORDER BY CURRENT_VALUE DESC
						) as RP
					SET
						RR.PREVIOUS_POSITION = IF(RR.CURRENT_POSITION = RP.POSITION, RR.PREVIOUS_POSITION, RR.CURRENT_POSITION),
						RR.CURRENT_POSITION = RP.POSITION
					WHERE
						RR.RATING_ID=".$ID."
					and	RR.ENTITY_TYPE_ID = RP.ENTITY_TYPE_ID
					and	RR.ENTITY_ID = RP.ENTITY_ID
					";
				$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			}
			
			// Insert new user rating prop
			$strSql  = "
				INSERT INTO b_rating_user
					(RATING_ID, ENTITY_ID)
				SELECT
					".$ID." RATING_ID,
					U.ID as ENTITY_ID
				FROM
					b_user U LEFT JOIN b_rating_user RU ON RU.RATING_ID = ".$ID." and RU.ENTITY_ID = U.ID
				WHERE RU.ID IS NULL	";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			// authority calc
			if ($arRating['AUTHORITY'] == 'Y') {
						
				$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
				if ($sRatingWeightType == 'auto')
				{					
					$arCI = CRatings::GetCommunityInfo($ID);
					$communitySize = $arCI['COMMUNITY_SIZE'];
					$communityAuthority = $arCI['COMMUNITY_AUTHORITY'];
					$ratingNormalization = COption::GetOptionString("main", "rating_normalization", 1000);

					$voteWeight = 1;
					if ($communitySize > 0)
						$voteWeight = $ratingNormalization/$communitySize;
						
					COption::SetOptionString("main", "rating_community_size", $communitySize);	
					COption::SetOptionString("main", "rating_community_authority", $communityAuthority);
					COption::SetOptionString("main", "rating_vote_weight", $voteWeight);	
				
					$ratingCountVote = COption::GetOptionString("main", "rating_count_vote", 10);
					$strSql =  "UPDATE b_rating_user SET VOTE_COUNT = 0, VOTE_WEIGHT =0 WHERE RATING_ID=".$ID;
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);
					// default vote count + user authority 
					$strSql =  "
						UPDATE
							b_rating_user RU,
							(	SELECT ENTITY_ID, CURRENT_VALUE
								FROM b_rating_results
								WHERE RATING_ID = ".$ID."						
							) as RP
						SET
							RU.VOTE_COUNT = ".intval($ratingCountVote)."+RP.CURRENT_VALUE,
							RU.VOTE_WEIGHT = RP.CURRENT_VALUE*".$voteWeight."
						WHERE
							RU.RATING_ID=".$ID."
							and	RU.ENTITY_ID = RP.ENTITY_ID
					";
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);

				}
				else
				{
					// Depending on current authority set correct weight votes
					// Depending on current authority set correct vote count
					$strSql =  "UPDATE b_rating_user SET VOTE_COUNT = 0, VOTE_WEIGHT =0 WHERE RATING_ID=".$ID;
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);
					$strSql =  "
						UPDATE
							b_rating_user RU,
							(	SELECT 
									RW.RATING_FROM, RW.RATING_TO, RW.WEIGHT, RW.COUNT, RR.ENTITY_ID
								FROM 
									b_rating_weight RW,
									b_rating_results RR
								WHERE
									RR.RATING_ID = ".$ID."
								and RR.CURRENT_VALUE BETWEEN RW.RATING_FROM AND RW.RATING_TO		
							) as RP
						SET
							RU.VOTE_COUNT = RP.COUNT,
							RU.VOTE_WEIGHT = RP.WEIGHT
						WHERE
							RU.RATING_ID=".$ID."
							and	RU.ENTITY_ID = RP.ENTITY_ID
					";
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);
				}
			}

			$DB->Query("UPDATE b_rating SET CALCULATED = 'Y', LAST_CALCULATED = ".$DB->GetNowFunction()." WHERE id = ".$ID, false, $err_mess.__LINE__);
		}
		return true;
	}

	// insert result calculate rating   
	function AddResults($arResults)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: AddComponentResults<br>Line: ";		
		
		// Only Mysql
		$strSqlPrefix = "
				INSERT INTO b_rating_results
				(RATING_ID, ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, PREVIOUS_VALUE)
				VALUES
		";
		$maxValuesLen = 2048;
		$strSqlValues = "";

		foreach($arResults as $arResult)
		{
			$strSqlValues .= ",\n(".IntVal($arResult['RATING_ID']).", '".$DB->ForSql($arResult['ENTITY_TYPE_ID'])."', '".$DB->ForSql($arResult['ENTITY_ID'])."', '".$DB->ForSql($arResult['CURRENT_VALUE'])."', '".$DB->ForSql($arResult['PREVIOUS_VALUE'])."')";
			if(strlen($strSqlValues) > $maxValuesLen)
			{
				$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, $err_mess.__LINE__);
				$strSqlValues = "";
			}
		}
		if(strlen($strSqlValues) > 0)
		{
			$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, $err_mess.__LINE__);
			$strSqlValues = "";
		}
		
		return true;
	}
	
	// insert result calculate rating-components  	
	function AddComponentResults($arComponentConfigs)
	{
		global $DB;
		$err_mess = (CRatings::err_mess())."<br>Function: AddComponentResults<br>Line: ";		
		
		if (!is_array($arComponentConfigs)) 
			return false;	
			
		$strSql  = "
			UPDATE b_rating_component 
			SET LAST_CALCULATED = ".$DB->GetNowFunction().", 
				NEXT_CALCULATION = '".date('Y-m-d H:i:s', time()+$arComponentConfigs['REFRESH_INTERVAL'])."'
			WHERE RATING_ID = ".IntVal($arComponentConfigs['RATING_ID'])." AND COMPLEX_NAME = '".$DB->ForSql($arComponentConfigs['COMPLEX_NAME'])."'";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		
		return true;
	}
	
	function SetAuthorityRating($ratingId)
	{
		global $DB, $stackCacheManager;
		
		$err_mess = (CRatings::err_mess())."<br>Function: SetAuthorityRating<br>Line: ";

		$ratingId = intval($ratingId);
		
		$DB->Query("UPDATE b_rating SET AUTHORITY = IF(ID <> $ratingId, 'N', 'Y')", false, $err_mess.__LINE__);
		
		$stackCacheManager->Clear("b_rating");
		
		return true;
	}
	
	function GetCommunityInfo($ratingId)
	{
		global $DB;
		
		$bAllGroups = false;
		$arInfo = Array();
		$arGroups = Array();
		$communityLastVisit = COption::GetOptionString("main", "rating_community_last_visit", '90');
		$res = CRatings::GetVoteGroup();
		while ($arVoteGroup = $res->Fetch()) 
		{
			if ($arVoteGroup['GROUP_ID'] == 2)
			{
				$bAllGroups = true;
				break;
			}
			$arGroups[] = $arVoteGroup['GROUP_ID'];
		}
			
		$strSql = 
			'SELECT COUNT(*) as COMMUNITY_SIZE, SUM(CURRENT_VALUE) COMMUNITY_AUTHORITY
			FROM b_rating_results RC
			WHERE RATING_ID = '.intval($ratingId);
		
		$strModulesSql = '';	
		if (IsModuleInstalled("forum"))
		{
			$strModulesSql .= "
					SELECT USER_START_ID as ENTITY_ID
					FROM b_forum_topic
					WHERE START_DATE > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
					GROUP BY USER_START_ID
				UNION ALL
					SELECT AUTHOR_ID as ENTITY_ID
					FROM b_forum_message
					WHERE POST_DATE > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
					GROUP BY AUTHOR_ID
				UNION ALL
			";
		}
		if (IsModuleInstalled("blog"))
		{
			$strModulesSql .= "
					SELECT	AUTHOR_ID as ENTITY_ID 
					FROM b_blog_post
					WHERE DATE_PUBLISH > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
					GROUP BY AUTHOR_ID
				UNION ALL
					SELECT AUTHOR_ID as ENTITY_ID
					FROM b_blog_comment
					WHERE DATE_CREATE > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
					GROUP BY AUTHOR_ID
				UNION ALL";
		}
		if (!empty($strModulesSql))
		{
			$strModulesSql = "
				(
					".$strModulesSql."
					SELECT USER_ID as ENTITY_ID
					FROM b_rating_vote
					WHERE CREATED > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
					GROUP BY USER_ID
				) MS,
			";
		}
		
		if ($bAllGroups || empty($arGroups)) 
		{
			$strSql .= "
				AND ENTITY_ID IN (
					SELECT DISTINCT ENTITY_ID
					FROM ".$strModulesSql."
						b_user U
					WHERE ".(!empty($strModulesSql)? "U.ID = MS.ENTITY_ID AND": "")." 
					U.ACTIVE = 'Y'
					AND U.LAST_LOGIN > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)	
				)
			";	
		}
		else
		{
			$strSql .= "
				AND ENTITY_ID IN (
					SELECT DISTINCT ENTITY_ID
					FROM ".$strModulesSql."
						b_user_group UG, 
						b_user U
					WHERE ".(!empty($strModulesSql)? "UG.USER_ID = MS.ENTITY_ID AND": "")." 
					UG.USER_ID = U.ID 
					AND U.ACTIVE = 'Y' 
					AND UG.GROUP_ID IN (".implode(',', $arGroups).") 
					AND U.LAST_LOGIN > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)	
					AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) 
					AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) 
				)
			";
		}
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		
		return $res->Fetch();	
	}
	
	function CheckAllowVote($arVoteParam)
	{
		global $USER;
		
		$userId = $USER->GetId();
		$bUserAuth = $USER->IsAuthorized();
		$bAllGroups = false;
		
		$arInfo = array(
			'RESULT' => true,
			'ERROR_TYPE' => '',
			'ERROR_MSG' => '',		
		);	
		
		if (IntVal($arVoteParam['OWNER_ID']) == $userId)
		{
			$arInfo = array(
				'RESULT' => false,
				'ERROR_TYPE' => 'SELF',
				'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_SELF'),		
			);
		} 
		else if (!$bUserAuth)
		{
		   $arInfo = array(
				'RESULT' => false,
				'ERROR_TYPE' => 'GUEST',
				'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_GUEST'),		
			);
		}
		else 
		{
			static $cacheAllowVote = array();
			static $cacheUserVote = array();
			static $cacheVoteSize = 0;
			if(!array_key_exists($userId, $cacheAllowVote))
			{
				global $DB;
				$arGroups = array();
				$bAllGroups = false;
				$sVoteType = $arVoteParam['ENTITY_TYPE_ID'] == 'USER'? 'A': 'R';
				$rsGroups = CRatings::GetVoteGroup($sVoteType);
				while ($arVoteGroup = $rsGroups->Fetch()) 
				{
					if ($arVoteGroup['GROUP_ID'] == 2)
					{
						$bAllGroups = true;
						break;
					}
					$arGroups[] = $arVoteGroup['GROUP_ID'];
				}
				if (!$bAllGroups && !empty($arGroups)) 
				{
					$strSql = '
						SELECT * FROM b_user_group UG
						WHERE UG.GROUP_ID IN ('.implode(',', $arGroups).') 
						  AND UG.USER_ID = '.$userId.'
						  AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= '.$DB->CurrentTimeFunction().')) 
						  AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= '.$DB->CurrentTimeFunction().'))';
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);
					if (!$res->Fetch())
					{
						$arInfo = $cacheAllowVote[$userId] = array(
							'RESULT' => false,
							'ERROR_TYPE' => 'ACCESS',
							'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_ACCESS'),		
						);
					}				
				}
				
				$authorityRatingId	 = CRatings::GetAuthorityRating();
				$arAuthorityUserProp = CRatings::GetRatingUserProp($authorityRatingId, $userId);
				if ($arAuthorityUserProp['VOTE_WEIGHT'] <= 0)
				{
					$arInfo = $cacheAllowVote[$userId] = array(
						'RESULT' => false,
						'ERROR_TYPE' => 'ACCESS',
						'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_ACCESS'),		
					);
				}
				
				if ($arInfo['RESULT'] && $sVoteType == 'A')
				{
				
					$strSql = '
						SELECT COUNT(*) as VOTE
						FROM b_rating_vote RV
						WHERE RV.USER_ID = '.$userId.'
						AND RV.CREATED > DATE_SUB(NOW(), INTERVAL 1 DAY)';
					$res = $DB->Query($strSql, false, $err_mess.__LINE__);
					$countVote = $res->Fetch();				
					$cacheVoteSize = $_SESSION['RATING_VOTE_COUNT'] = $countVote['VOTE'];
					
					$cacheUserVote[$userId] = $_SESSION['RATING_USER_VOTE_COUNT'] = $arAuthorityUserProp['VOTE_COUNT'];
					
					if ($cacheVoteSize >= $cacheUserVote[$userId])
					{
						$arInfo = $cacheAllowVote[$userId] = array(
							'RESULT' => false,
							'ERROR_TYPE' => 'COUNT_VOTE',
							'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_COUNT_VOTE'),		
						);
					}
				}
			}
			else
			{
				if ($cacheAllowVote[$userId]['RESULT'])
				{
					if ($cacheVoteSize >= $cacheUserVote[$userId])
					{
						$arInfo = $cacheAllowVote[$userId] = array(
							'RESULT' => false,
							'ERROR_TYPE' => 'COUNT_VOTE',
							'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_COUNT_VOTE'),		
						);
					}
				}	
				$arInfo = $cacheAllowVote[$userId];
			}
		}
		
		return $arInfo;
	}
	
	function SetAuthorityDefaultValue($arParams)
	{
		global $DB;
		
		$rsRatings = CRatings::GetList(array('ID' => 'ASC'), array('ENTITY_ID' => 'USER'));
		while ($arRatingsTmp = $rsRatings->GetNext())
			$arRatingList[] = $arRatingsTmp['ID'];
			
		if (isset($arParams['DEFAULT_USER_ACTIVE']) && $arParams['DEFAULT_USER_ACTIVE'] == 'Y' && IsModuleInstalled("forum") && is_array($arRatingList) && !empty($arRatingList))
		{		
			$ratingStartValue = 0;
			if (isset($arParams['DEFAULT_CONFIG_NEW_USER']) && $arParams['DEFAULT_CONFIG_NEW_USER'] == 'Y')
				$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
			
			$strSql =  "UPDATE b_rating_user SET BONUS = $ratingStartValue WHERE RATING_ID IN (".implode(',', $arRatingList).")";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);	
			$strSql =  "
				UPDATE
					b_rating_user RU,
					(	SELECT 
							TO_USER_ID as ENTITY_ID, COUNT(*) as CNT
						FROM 
							b_forum_user_points FUP
						GROUP BY TO_USER_ID	
					) as RP
				SET
					RU.BONUS = ".$DB->IsNull('RP.CNT', '0')."+".$ratingStartValue."
				WHERE
					RU.RATING_ID IN (".implode(',', $arRatingList).")
				and	RU.ENTITY_ID = RP.ENTITY_ID
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);	
		} 
		else if (isset($arParams['DEFAULT_CONFIG_NEW_USER']) && $arParams['DEFAULT_CONFIG_NEW_USER'] == 'Y' && is_array($arRatingList) && !empty($arRatingList))
		{
			$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
			$strSql =  "UPDATE b_rating_user SET BONUS = ".$ratingStartValue." WHERE RATING_ID IN (".implode(',', $arRatingList).")";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);	
		}
		
		return true;
	}
}
?>