<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

	COption::SetOptionString("fileman", "propstypes", serialize(array("description"=>GetMessage("MAIN_OPT_DESCRIPTION"), "keywords"=>GetMessage("MAIN_OPT_KEYWORDS"), "title"=>GetMessage("MAIN_OPT_TITLE"), "keywords_inner"=>GetMessage("MAIN_OPT_KEYWORDS_INNER"))), false, $siteID);
	
	COption::SetOptionInt("search", "suggest_save_days", 250);
	COption::SetOptionInt("search", "use_tf_cache", "Y");
	
	COption::SetOptionInt("advertising", "DONT_USE_CONTRACT", "N");
	COption::SetOptionInt("advertising", "DONT_FIX_BANNER_SHOWS", "N");
	
	COption::SetOptionInt("iblock", "use_htmledit", "Y");

?>