<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/blog/general/ratings_components.php");

class CAllRatingsComponentsBlog
{
	// return configs of component-rating
	function OnGetRatingConfigs()
	{
		$arConfigs = array(
			"MODULE_ID" => "BLOG",
			"MODULE_NAME" => GetMessage("BLOG_MODULE_NAME"),
		);
		$arConfigs["COMPONENT"]["USER"]["VOTE"][] = array(
			"ID"	=> 'POST',
			"REFRESH_TIME"	=> '3600',
			"CLASS"	=> 'CRatingsComponentsBlog',
			"CALC_METHOD"	=> 'CalcPost',
			"NAME" 	=> GetMessage('BLOG_RATING_USER_VOTE_POST_NAME'),
		    "FIELDS" => array(
				array(
					"ID" => 'COEFFICIENT',
					"DEFAULT" => '1',
				),
			)
		);
		$arConfigs["COMPONENT"]["USER"]["VOTE"][] = array(
		    "ID"	=> 'COMMENT',
			"REFRESH_TIME"	=> '3600',
			"CLASS"	=> 'CRatingsComponentsBlog',
			"CALC_METHOD"	=> 'CalcComment',		
			"NAME" => GetMessage('BLOG_RATING_USER_VOTE_COMMENT_NAME'),
		    "FIELDS" => array(
				array(
					"ID" => 'COEFFICIENT',
					"DEFAULT" => '1',
				),
			)
		);
		$arConfigs["COMPONENT"]["USER"]["RATING"][] = array(
		    "ID"	=> 'ACTIVITY',
			"REFRESH_TIME"	=> '7200',
			"CLASS"	=> 'CRatingsComponentsBlog',
			"CALC_METHOD"	=> 'CalcActivity',				
			//"EXCEPTION_METHOD"	=> 'ExceptionUserRatingBlogActivity',				
			"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_NAME'),
			"DESC" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_DESC'),
			"FORMULA" => 'T<sub>1</sub> * K<sub>T1</sub> + T<sub>7</sub> * K<sub>T7</sub> + T<sub>30</sub> * K<sub>T30</sub> + P<sub>1</sub> * K<sub>P1</sub> + P<sub>7</sub> * K<sub>P7</sub> + P<sub>30</sub> * K<sub>P30</sub>',
			"FORMULA_DESC" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FORMULA_DESC'),
		    "FIELDS" => array(
				array(
					"ID" => 'TODAY_POST_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_TODAY_POST_COEF'),
					"DEFAULT" => '20',
				),
				array(
					"ID" => 'WEEK_POST_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_WEEK_POST_COEF'),
					"DEFAULT" => '10',
				),
				array(
					"ID" => 'MONTH_POST_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_MONTH_POST_COEF'),
					"DEFAULT" => '5',
				),
				array(
					"ID" => 'TODAY_COMMENT_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_TODAY_COMMENT_COEF'),
					"DEFAULT" => '0.4',
				),
				array(
					"ID" => 'WEEK_COMMENT_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_WEEK_COMMENT_COEF'),
					"DEFAULT" => '0.2',
				),
				array(
					"ID" => 'MONTH_COMMENT_COEF',
					"NAME" => GetMessage('BLOG_RATING_USER_RATING_ACTIVITY_FIELDS_MONTH_COMMENT_COEF'),
					"DEFAULT" => '0.1',
				),
			)
		);
		
		return $arConfigs;
	}
	
	// Calculation functions
	
	
	function CalcPost($arConfigs)
	{
		global $DB;
		
		$err_mess = (CRatings::err_mess())."<br>Function: CRatingsComponentsBlog::CalcPost<br>Line: ";

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
						FT.AUTHOR_ID  ENTITY_ID, 
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID, 
						SUM(RV.TOTAL_VALUE)*".$arConfigs['CONFIG']['COEFFICIENT']."  CURRENT_VALUE
					FROM 
						b_rating_voting RV LEFT JOIN b_blog_post FT ON RV.ENTITY_ID = FT.ID
					WHERE RV.ENTITY_TYPE_ID = 'BLOG_POST' AND FT.AUTHOR_ID > 0
					GROUP BY AUTHOR_ID";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		
		return true;
	}
	
	
	function CalcComment($arConfigs)
	{
		global $DB;
		
		$err_mess = (CRatings::err_mess())."<br>Function: CRatingsComponentsBlog::CalcComment<br>Line: ";
			
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
						FM.AUTHOR_ID  ENTITY_ID, 
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID, 
						SUM(RV.TOTAL_VALUE)*".$arConfigs['CONFIG']['COEFFICIENT']."  CURRENT_VALUE
					FROM 
						b_rating_voting RV LEFT JOIN b_blog_comment FM ON RV.ENTITY_ID = FM.ID
					WHERE RV.ENTITY_TYPE_ID = 'BLOG_COMMENT' AND FM.AUTHOR_ID > 0
					GROUP BY AUTHOR_ID";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}
	
		
	// return support object
	function OnGetRatingObject()
	{
		$arRatingConfigs = CRatingsComponentsBlog::OnGetRatingConfigs();
		foreach ($arRatingConfigs["COMPONENT"] as $SupportType => $value)
			$arSupportType[] = $SupportType;
			
		return $arSupportType;
	}
	
	// check the value of the component-rating which relate to the module
	function OnAfterAddRating($ID, $arFields)
	{
		$arFields['CONFIGS']['BLOG'] = CRatingsComponentsBlog::__CheckFields($arFields['ENTITY_ID'], $arFields['CONFIGS']['BLOG']);
		
		return $arFields;
	}
	
	// check the value of the component-rating which relate to the module
	function OnAfterUpdateRating($ID, $arFields)
	{
		$arFields['CONFIGS']['BLOG'] = CRatingsComponentsBlog::__CheckFields($arFields['ENTITY_ID'], $arFields['CONFIGS']['BLOG']);
		
		return $arFields;
	}
	
	// Exception function
	/*
	function ExceptionUserRatingBlogActivity()
	{
		global $DB;
		$bIndex1 = $DB->IndexExists("B_BLOG_POST", array("START_DATE", "USER_START_ID"));
		$bIndex2 = $DB->IndexExists("B_BLOG_MESSAGE", array("POST_DATE", "AUTHOR_ID"));

		if(!$bIndex1 || !$bIndex2) 
		{		
			$arIndex = Array();
			if (!$bIndex1)
				$arIndex[] = 'CREATE INDEX IX_BLOG_POST_DATE_USER_ID ON B_BLOG_POST(START_DATE, USER_START_ID)';
				
			if (!$bIndex2)
				$arIndex[] = 'CREATE INDEX IX_BLOG_MESSAGE_DATE_USER_ID ON B_BLOG_MESSAGE(POST_DATE, AUTHOR_ID)';
		
			return GetMessage('EXCEPTION_USER_RATING_BLOG_ACTIVITY_TEXT').'<br>1. <b>'.$arIndex[0].'</b>'.(isset($arIndex[1]) ? '<br> 2. <b>'.$arIndex[1].'</b>' : '');
		}
		else
			return false;
	}
	*/
	// Utilities
	
	// check input values, if value does not validate, set the default value
	function __CheckFields($entityId, $arConfigs)
	{
		$arDefaultConfig = CRatingsComponentsBlog::__AssembleConfigDefault($entityId);
		if ($entityId == "USER") {
			if (isset($arConfigs['VOTE']['POST']))
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['VOTE']['POST']['COEFFICIENT']))
					$arConfigs['VOTE']['POST']['COEFFICIENT'] = $arDefaultConfig['VOTE']['POST']['COEFFICIENT']['DEFAULT'];
					
			if (isset($arConfigs['VOTE']['COMMENT']))
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['VOTE']['COMMENT']['COEFFICIENT']))
					$arConfigs['VOTE']['COMMENT']['COEFFICIENT'] = $arDefaultConfig['VOTE']['COMMENT']['COEFFICIENT']['DEFAULT'];	
				
			if (isset($arConfigs['RATING']['ACTIVITY']))
			{
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['TODAY_POST_COEF']))
					$arConfigs['RATING']['ACTIVITY']['TODAY_POST_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['TODAY_POST_COEF']['DEFAULT'];
					
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['WEEK_POST_COEF']))
					$arConfigs['RATING']['ACTIVITY']['WEEK_POST_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['WEEK_POST_COEF']['DEFAULT'];
					
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['MONTH_POST_COEF']))
					$arConfigs['RATING']['ACTIVITY']['MONTH_POST_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['MONTH_POST_COEF']['DEFAULT'];
					
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['TODAY_COMMENT_COEF']))
					$arConfigs['RATING']['ACTIVITY']['TODAY_COMMENT_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['TODAY_POST_COEF']['DEFAULT'];
					
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['WEEK_COMMENT_COEF']))
					$arConfigs['RATING']['ACTIVITY']['WEEK_COMMENT_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['WEEK_COMMENT_COEF']['DEFAULT'];
					
				if (!preg_match('/^\d{1,7}\.?\d{0,4}$/', $arConfigs['RATING']['ACTIVITY']['MONTH_COMMENT_COEF']))
					$arConfigs['RATING']['ACTIVITY']['MONTH_COMMENT_COEF'] = $arDefaultConfig['RATING']['ACTIVITY']['MONTH_COMMENT_COEF']['DEFAULT'];
			}
		}
		
		return $arConfigs;
	}
	
	// collect the default and regular expressions for the fields component-rating
	function __AssembleConfigDefault($objectType = null) 
	{
		$arConfigs = array();
		$arRatingConfigs = CRatingsComponentsBlog::OnGetRatingConfigs();
		if (is_null($objectType)) 
		{
			foreach ($arRatingConfigs as $OBJ_TYPE => $TYPE_VALUE)
				foreach ($TYPE_VALUE as $RAT_TYPE => $RAT_VALUE)
					foreach ($RAT_VALUE as $VALUE_CONFIG)
				   		foreach ($VALUE_CONFIG['FIELDS'] as $VALUE_FIELDS) 
				   		   $arConfigs[$OBJ_TYPE][$RAT_TYPE][$VALUE_CONFIG['ID']][$VALUE_FIELDS['ID']]['DEFAULT'] = $VALUE_FIELDS['DEFAULT'];
		}
		else 
		{
			foreach ($arRatingConfigs[$objectType] as $RAT_TYPE => $RAT_VALUE)
				foreach ($RAT_VALUE as $VALUE_CONFIG)
					foreach ($VALUE_CONFIG['FIELDS'] as $VALUE_FIELDS) 
				   		$arConfigs[$RAT_TYPE][$VALUE_CONFIG['ID']][$VALUE_FIELDS['ID']]['DEFAULT'] = $VALUE_FIELDS['DEFAULT'];

		}
		
		return $arConfigs;
	}	
}

?>