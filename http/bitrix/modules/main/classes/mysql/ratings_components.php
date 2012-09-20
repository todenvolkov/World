<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/ratings_components.php");
IncludeModuleLangFile(__FILE__);

class CRatingsComponentsMain extends CAllRatingsComponentsMain
{	
	// Calc function
	function CalcVoteUser($arConfigs)
	{
		global $DB;

		$err_mess = (CRatings::err_mess())."<br>Function: CalcUserVoteForumTopic<br>Line: ";

		CRatings::AddComponentResults($arConfigs);
			
		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".IntVal($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						'".IntVal($arConfigs['RATING_ID'])."'  RATING_ID,
						'".$DB->ForSql($arConfigs['MODULE_ID'])."'  MODULE_ID,
						'".$DB->ForSql($arConfigs['RATING_TYPE'])."'  RATING_TYPE,
						'".$DB->ForSql($arConfigs['NAME'])."'  NAME,
						'".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'  COMPLEX_NAME,
						RV.ENTITY_ID as ENTITY_ID,
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID,
						SUM(RVE.VALUE)*".$arConfigs['CONFIG']['COEFFICIENT']." CURRENT_VALUE
					FROM
						b_rating_voting RV,
						b_rating_vote RVE
					WHERE
						RV.ENTITY_TYPE_ID = 'USER' AND RV.ENTITY_ID > 0
					AND RVE.RATING_VOTING_ID = RV.ID".($arConfigs['CONFIG']['LIMIT'] > 0 ? " AND RVE.CREATED > DATE_SUB(NOW(), INTERVAL ".$arConfigs['CONFIG']['LIMIT']." DAY)" : "")."
					GROUP BY ENTITY_ID";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}
	
	function CalcUserBonus($arConfigs)
	{
		global $DB;

		$err_mess = (CRatings::err_mess())."<br>Function: CalcUserBonus<br>Line: ";

		$communityLastVisit = COption::GetOptionString("main", "rating_community_last_visit", '90');
		
		CRatings::AddComponentResults($arConfigs);
		
		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".IntVal($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		
		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						'".IntVal($arConfigs['RATING_ID'])."'  RATING_ID,
						'".$DB->ForSql($arConfigs['MODULE_ID'])."'  MODULE_ID,
						'".$DB->ForSql($arConfigs['RATING_TYPE'])."'  RATING_TYPE,
						'".$DB->ForSql($arConfigs['NAME'])."'  NAME,
						'".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'  COMPLEX_NAME,
						RB.ENTITY_ID as ENTITY_ID,
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID,
						SUM(RB.BONUS)*".$arConfigs['CONFIG']['COEFFICIENT']."  CURRENT_VALUE
					FROM
						b_rating_user RB
					WHERE
						RB.RATING_ID = ".IntVal($arConfigs['RATING_ID'])." 	
						AND ENTITY_ID IN (
							SELECT U.ID
							FROM b_user U
							WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN > DATE_SUB(NOW(), INTERVAL ".intval($communityLastVisit)." DAY)
						)	
					GROUP BY ENTITY_ID";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}
}
?>