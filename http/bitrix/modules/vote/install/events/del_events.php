<?
$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME in ('VOTE_NEW')");
$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME in ('VOTE_NEW')");
?>
