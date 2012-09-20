<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/install/events/set_events.php");
echo "Ok - ".date("Y.m.d H:m:s", time());
?>