<?
if($REQUEST_METHOD=="POST")
{
    $_POST["ACTIVE_FROM"] = date("d.m.Y");
    $_POST["ACTIVE_TO"] = date("d.m.Y");
    $_POST["ACTIVE"] = "Y";

}

?>
