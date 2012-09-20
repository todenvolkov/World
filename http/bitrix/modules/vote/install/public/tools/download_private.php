<?
function initialize_params($url)
{
        if (strpos($url,"?")>0)
        {
                $par = substr($url,strpos($url,"?")+1,strlen($url));
                $arr = explode("#",$par);
                $par = $arr[0];
                $arr1 = explode("&",$par);
                foreach ($arr1 as $pair)
                {
                        $arr2 = explode("=",$pair);
                        global $$arr2[0];
                        $$arr2[0] = $arr2[1];
                }
        }
}

// script location
$SELF_SCRIPT_DIR = "/public_private";

// real place for files with restriced access
$PRIVATE_DIR = "/download/private ";

// a folder for redirect with direct download protection
$PRIVATE_WWW = "/private_download";

$DIR = dirname($_SERVER["REQUEST_URI"]);

$DIR_ASKED = substr(strtolower($DIR), strlen(strtolower($SELF_SCRIPT_DIR))-1);
if (!empty($DIR_ASKED)) $DIR_ASKED = "/" . $DIR_ASKED;

$sapi = php_sapi_name();
set_time_limit(0);
$arr1 = explode("?",$_SERVER["REQUEST_URI"]);
$arr2 = explode("#",$arr1[0]);
$URI = $arr2[0];
$file = str_replace("..", "", $file);
$file = substr($URI, strlen($DIR)+1);
$filename = urldecode($_SERVER["DOCUMENT_ROOT"].$PRIVATE_DIR.$DIR_ASKED."/".$file);

if(file_exists($filename))
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
        $FILE_PERM = $APPLICATION->GetFileAccessPermission($PRIVATE_DIR.$DIR_ASKED."/".$file, $USER->GetUserGroupArray());

        $FILE_PERM = (strlen($FILE_PERM)>0 ? $FILE_PERM : "D");
        if($FILE_PERM<"R")
        {
                LocalRedirect($DIR."/auth.php?fname=".urlencode($file)."&DIR=".urlencode($DIR));
        }
        else
        {
				if (CModule::IncludeModule("statistic"))
				{
					initialize_params($_SERVER["REQUEST_URI"]);
					if (strlen($event1)<=0 && strlen($event2)<=0)
					{
						$event1 = "download";
						$event2 = "private";
						$event3 = $file;
					}
					$e = $event1."/".$event2."/".$event3;
					if (!in_array($e, $_SESSION["DOWNLOAD_EVENTS"])) // check if was not downloaded in current session
					{
						$w = CStatEvent::GetByEvents($event1, $event2);
						$wr = $w->Fetch();
						$z = CStatEvent::GetEventsByGuest($_SESSION["SESS_GUEST_ID"], $wr["EVENT_ID"], $event3, 21600);
						if (!($zr=$z->Fetch())) // check if the user did not any download for the last 6 hours
						{
							CStatistic::Set_Event($event1, $event2, $event3);
							$_SESSION["DOWNLOAD_EVENTS"][] = $e;
						}
					}
				}
                header("X-Accel-Redirect: {$PRIVATE_WWW}{$DIR_ASKED}/{$file}");
                die();
        }
}
else
{
        include($_SERVER["DOCUMENT_ROOT"]."/404.php");
}
?>
