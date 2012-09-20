<?
global $DBType;

IncludeModuleLangFile(__FILE__);

define("BLOG_PERMS_DENY", "D");	/* CAN'T EVEN LOOK AT ANY OBJECTS*/
define("BLOG_PERMS_READ", "I");	/* CAN ONLY READ ALL OBJECTS */
define("BLOG_PERMS_PREMODERATE", "K");	/* CAN READ ALL OBJECTS, CAN WRITE OWN HIDDEN OBJECTS */
define("BLOG_PERMS_WRITE", "P");	/* CAN READ ALL OBJECTS, CAN WRITE OWN OBJECTS */
define("BLOG_PERMS_MODERATE", "T");	/* CAN READ ALL OBJECT, CAN WRITE OWN OBJECTS, CAN HIDE AND SHOW ALL OBLECT */
define("BLOG_PERMS_FULL", "W");	/* CAN READ ALL OBJECT, CAN WRITE ALL OBJECTS */

$GLOBALS["AR_BLOG_PERMS"] = array(
	"D" => GetMessage("BLI_P_D"),
	"I" => GetMessage("BLI_P_I"),
	"K" => GetMessage("BLI_P_K"),
	"P" => GetMessage("BLI_P_P"),
	"T" => GetMessage("BLI_P_T"),
	"W" => GetMessage("BLI_P_W")
);

$GLOBALS["AR_BLOG_POST_PERMS"] = array(
	BLOG_PERMS_DENY, 
	BLOG_PERMS_READ,
	BLOG_PERMS_PREMODERATE,
	BLOG_PERMS_WRITE, 
	BLOG_PERMS_MODERATE,
	BLOG_PERMS_FULL
	);
$GLOBALS["AR_BLOG_COMMENT_PERMS"] = array(
	BLOG_PERMS_DENY, 
	BLOG_PERMS_READ, 
	BLOG_PERMS_PREMODERATE,
	BLOG_PERMS_WRITE, 
	BLOG_PERMS_MODERATE,
	BLOG_PERMS_FULL
	);

define("BLOG_PERMS_POST", "P");
define("BLOG_PERMS_COMMENT", "C");


define("BLOG_PUBLISH_STATUS_DRAFT", "D");
define("BLOG_PUBLISH_STATUS_READY", "K");
define("BLOG_PUBLISH_STATUS_PUBLISH", "P");

$GLOBALS["AR_BLOG_PUBLISH_STATUS"] = array(
	"D" => GetMessage("BLI_PS_D"),
	"K" => GetMessage("BLI_PS_K"),
	"P" => GetMessage("BLI_PS_P")
);

define("BLOG_BY_USER_ID", 1);
define("BLOG_BY_BLOG_USER_ID", 2);

define("BLOG_ADD", 1);
define("BLOG_CHANGE", 2);
define("BLOG_RESET", 3);

$GLOBALS["AR_BLOG_RESERVED_NAMES"] = array("admin", "users", "group", "rss", "new", "user", "user_friends", "search", "user_settings", "user_settings_edit", "group_edit", "blog_edit", "category_edit", "post_edit", "draft", "moderation", "trackback", "post", "post_rss", "rss", "rss_all", "index");
$GLOBALS["AR_BLOG_POST_RESERVED_CODES"] = Array("admin", "users", "index", "group", "blog", "user", "user_friends", "search", "user_settings", "user_settings_edit", "group_edit", "blog_edit", "category_edit", "post_edit", "draft", "moderation", "trackback", "post", "post_rss", "rss", "rss_all", "new");

CModule::AddAutoloadClasses(
	"blog",
	array(
		"CBlog" => $DBType."/blog.php",
		"CBlogCandidate" => $DBType."/blog_candid.php",
		"CBlogGroup" => $DBType."/blog_group.php",
		"CBlogImage" => $DBType."/blog_image.php",
		"CBlogPost" => $DBType."/blog_post.php",
		"CBlogCategory" => $DBType."/blog_category.php",
		"CBlogComment" => $DBType."/blog_comment.php",
		"CBlogUser" => $DBType."/blog_user.php",
		"CBlogUserGroup" => $DBType."/blog_user_group.php",
		"CBlogTrackback" => $DBType."/blog_trackback.php",
		"CBlogUserGroupPerms" => $DBType."/blog_user_group_perms.php",
		"CBlogSitePath" => $DBType."/blog_site_path.php",
		"CBlogSmile" => $DBType."/smile.php",
		"CBlogPostCategory" => $DBType."/blog_post_category.php",

		"CBlogSearch" => "general/blog_search.php",
		"CBlogSoNetPost" => "general/sonet.php",
		"blogTextParser" => "general/functions.php",
		"CBlogTools" => "general/functions.php",
		"CBlogMetaWeblog" => "general/blog_metaweblog.php",
		
		"CRatingsComponentsBlog" => $DBType."/ratings_components.php",
		)
	);

AddEventHandler("blog", "videoConvert", "blogConvertVideo", 1000);
function blogConvertVideo($arParams)
{
 	ob_start();
	$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:player", "",
		Array(
			"PLAYER_TYPE" => "auto", 
			"USE_PLAYLIST" => "N", 
			"PATH" => $arParams["PATH"], 
			"WIDTH" => $arParams["WIDTH"], 
			"HEIGHT" => $arParams["HEIGHT"], 
			"PREVIEW" => $arParams["PREVIEW"], 
			"LOGO" => "", 
			"FULLSCREEN" => "Y", 
			"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins", 
			"SKIN" => "bitrix.swf", 
			"CONTROLBAR" => "bottom", 
			"WMODE" => "transparent", 
			"HIDE_MENU" => "N", 
			"SHOW_CONTROLS" => "Y", 
			"SHOW_STOP" => "N", 
			"SHOW_DIGITS" => "Y", 
			"CONTROLS_BGCOLOR" => "FFFFFF", 
			"CONTROLS_COLOR" => "000000", 
			"CONTROLS_OVER_COLOR" => "000000", 
			"SCREEN_COLOR" => "000000", 
			"AUTOSTART" => "N", 
			"REPEAT" => "N", 
			"VOLUME" => "90", 
			"DISPLAY_CLICK" => "play", 
			"MUTE" => "N", 
			"HIGH_QUALITY" => "Y", 
			"ADVANCED_MODE_SETTINGS" => "N", 
			"BUFFER_LENGTH" => "10", 
			"DOWNLOAD_LINK" => "", 
			"DOWNLOAD_LINK_TARGET" => "_self"));
	$video = ob_get_contents();
	ob_end_clean();
	return $video;
}
?>