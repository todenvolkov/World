<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

$IBLOCK_ID = IntVal($IBLOCK_ID);

echo "<script type=\"text/javascript\">\n";
echo "window.parent.Tree=new Array();";
echo "window.parent.Tree[0]=new Array();";

$db_section = CIBlockSection::GetList(array("LEFT_MARGIN"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
$bNoTree = True;
while ($ar_section = $db_section->Fetch())
{
	$bNoTree = False;
	if (IntVal($ar_section["RIGHT_MARGIN"])-IntVal($ar_section["LEFT_MARGIN"])>1)
	{
		?>window.parent.Tree[<?echo IntVal($ar_section["ID"]);?>]=new Array();<?
	}
	?>window.parent.Tree[<?echo IntVal($ar_section["IBLOCK_SECTION_ID"]);?>][<?echo IntVal($ar_section["ID"]);?>]=Array('<?echo CUtil::JSEscape(htmlspecialchars($ar_section["NAME"]));?>', '');<?
}

if ($IBLOCK_ID<=0)		//($bNoTree)
{
	echo "window.parent.buildNoMenu();";
}
else
{
	echo "window.parent.buildMenu();";
}
echo "</script>";
?>