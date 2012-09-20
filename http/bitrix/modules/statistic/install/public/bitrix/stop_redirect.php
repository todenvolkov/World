<?
if(substr($_REQUEST["url"],0,4)!="http" && substr($_REQUEST["url"],0,1)!="/")
	$_REQUEST["url"] = "/".$_REQUEST["url"];
//This function will protect against utf-7 xss
//on page with no character setting
function htmlspecialchars_plus($str)
{
 	return str_replace("+","&#43;",htmlspecialchars($str));
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars_plus($_REQUEST["charset"])?>">
<meta http-equiv="Refresh" content="3;URL=<?=htmlspecialchars_plus($_REQUEST["url"])?>">
</head>
<body>
<div align="center"><h3><?=htmlspecialchars_plus($_REQUEST["mess"])?></h3></div>
</body>
</html>