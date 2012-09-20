<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (WIZARD_INSTALL_DEMO_DATA)	
{
		$DB->Query("UPDATE B_USER SET LAST_LOGIN = ".$DB->GetNowFunction());
		
        $arParams = array();
        $arParams['DEFAULT_CONFIG_NEW_USER'] = 'Y';
        CRatings::SetAuthorityDefaultValue($arParams);

        $ratingId = false;
		
		$rsData = CRatings::GetList(array('ID'=>'ASC'), array('AUTHORITY'=>'N'));
		if($arRes = $rsData->Fetch())
		{
			if ($arRes['ACTIVE'] == 'N')
			{
				CRatings::Delete($arRes['ID']);
			} 
			else 
			{
				$ratingId = $arRes['ID'];
			}
		}

		if($ratingId == false){
				$arConfig = array();
		
				$arConfig['MAIN'] = array(
					'RATING' => array(
						'BONUS' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '1',
						),
					),
				);
				
				$arConfig['FORUM'] = array(
					'VOTE' => array(
						'TOPIC' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '0.5',
							'LIMIT' => '30'
						),
						'POST' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '0.1',
							'LIMIT' => '30'
						),
					),
					'RATING' => array(
						'ACTIVITY' => array(
							'ACTIVE' => 'Y',
							'TODAY_TOPIC_COEF' => '0.4',
							'WEEK_TOPIC_COEF' => '0.2',
							'MONTH_TOPIC_COEF' => '0.1',
							'ALL_TOPIC_COEF' => '0',
							'TODAY_POST_COEF' => '0.2',
							'WEEK_POST_COEF' => '0.1',
							'MONTH_POST_COEF' => '0.05',
							'ALL_POST_COEF' => '0',
						),
					),
				);
				    
				$arConfig['BLOG'] = array(
					'VOTE' => array(
						'POST' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '1',
							'LIMIT' => '30'
						),
						'COMMENT' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '1',
							'LIMIT' => '30'
						),
					),
					'RATING' => array(
						'ACTIVITY' => array(
							'ACTIVE' => 'Y',
							'TODAY_POST_COEF' => '0.4',
							'WEEK_POST_COEF' => '0.2',
							'MONTH_POST_COEF' => '0.1',
							'ALL_POST_COEF' => '0',
							'TODAY_COMMENT_COEF' => '0.2',
							'WEEK_COMMENT_COEF' => '0.1',
							'MONTH_COMMENT_COEF' => '0.05',
							'ALL_COMMENT_COEF' => '0',
						),
					),
				);
			
				$arAddRating = array(
					'ACTIVE' => 'Y',
					'NAME' => GetMessage('MAIN_RATING_NAME'),
					'ENTITY_ID' => 'USER',
					'CALCULATION_METHOD' => 'SUM',
					'POSITION' => 'Y',
					'AUTHORITY' => 'N',
					'CONFIGS' => $arConfig
				);
				$ratingId = CRatings::Add($arAddRating);
		}
		CRatings::Calculate($ratingId, true);
		
		$authorityId = false;
		
		$rsData = CRatings::GetList(array('ID'=>'ASC'), array('AUTHORITY'=>'Y'));
		if($arRes = $rsData->Fetch())
		{
			if ($arRes['ACTIVE'] == 'N')
			{
				CRatings::Delete($arRes['ID']);
			} 
			else 
			{
				$authorityId = $arRes['ID'];
			}
		} 
		if($authorityId == false) {
			$arConfig = array();
		
				$arConfig['MAIN'] = array(
					'VOTE' => array(
						'USER' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '1',
							'LIMIT' => '30'
						),
					),
					'RATING' => array(
						'BONUS' => array(
							'ACTIVE' => 'Y',
							'COEFFICIENT' => '1',
						),
					),
				);
							
				$arAddRating = array(
					'ACTIVE' => 'Y',
					'NAME' => GetMessage('MAIN_RATING_AUTHORITY'),
					'ENTITY_ID' => 'USER',
					'CALCULATION_METHOD' => 'SUM',
					'POSITION' => 'Y',
					'AUTHORITY' => 'Y',
					'CONFIGS' => $arConfig
				);
				$authorityId = CRatings::Add($arAddRating);
		}
		CRatings::Calculate($authorityId, true);
		
		$rsData = CRatingRule::GetList(array('ID'=>'ASC'));
	 	while($arRes = $rsData->Fetch())
		{
			$arUpdate['ACTIVE'] = 'Y';
			CRatingRule::Update($arRes["ID"], $arUpdate);
			CRatingRule::Apply($arRes["ID"], true);
		}
		$ratingArray = 'array(1 => "'.$ratingId.'", 2 => "'.$authorityId.'")';
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/forum/index.php", Array("SHOW_RATING" => 'Y'));
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/forum/index.php", Array("RATING_ID" => $ratingArray));
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/people/index.php", Array("RATING_ID" => $ratingId));
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/people/user.php", Array("RATING_ID" => $ratingArray));
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/groups/group.php", Array("RATING_ID" => $ratingId));
		COption::SetOptionString("socialnetwork", "tooltip_rating_id", serialize(array($ratingId, $authorityId)), "", WIZARD_SITE_ID); 
		COption::SetOptionString("socialnetwork", "tooltip_show_rating", "Y", "", WIZARD_SITE_ID);
		COption::SetOptionString("main", "rating_normalization", 10);
		COption::SetOptionString("forum", "SHOW_VOTES", "N");
}
?>