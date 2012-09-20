<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule('lists'))
	die();

__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/'.basename(__FILE__));

CUtil::JSPostUnescape();

$arListsPerm = CLists::GetPermission($_REQUEST["IBLOCK_TYPE_ID"]);
if(!count($arListsPerm))
	die();

//Check permissions for add or edit iblock
$USER_GROUPS = $USER->GetUserGroupArray();
$CAN_EDIT = count(array_intersect($arListsPerm, $USER_GROUPS)) > 0;

//Check then iblock belongs to proper type
$iblock_id = intval($_REQUEST["IBLOCK_ID"]);
$arIBlock = false;
if($iblock_id)
{
	$arIBlock = CIBlock::GetArrayByID($iblock_id);
	if($arIBlock["IBLOCK_TYPE_ID"] != $_REQUEST["IBLOCK_TYPE_ID"])
		die();
}
else
{
	die();
}

if(!$arParams['CAN_EDIT'])
{
	$IBLOCK_PERM = CIBlock::GetPermission($arIBlock["ID"]);
	if($IBLOCK_PERM < "R")
		die();
}

if($_REQUEST['MODE'] == 'SEARCH')
{
	$APPLICATION->RestartBuffer();

	$arResult = array();
	$search = $_REQUEST['search'];

	$matches = array();
	if(preg_match('/^(.*?)\[([\d]+?)\]/i', $search, $matches))
	{
		$matches[2] = intval($matches[2]);
		if($matches[2] > 0)
		{
			$dbRes = CIBlockElement::GetList(
				array(),
				array("IBLOCK_ID" => $arIBlock["ID"], "=ID" => $matches[2]),
				false,
				false,
				array("ID", "NAME")
			);
			if($arRes = $dbRes->Fetch())
			{
				$arResult[] = array(
					'ID' => $arRes['ID'],
					'NAME' => $arRes['NAME'],
					'READY' => 'Y',
				);

				Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
				echo CUtil::PhpToJsObject($arUsers);
				die();
			}
		}
		elseif(strlen($matches[1]) > 0)
		{
			$search = $matches[1];
		}
	}

	$dbRes = CIBlockElement::GetList(
		array(),
		array("IBLOCK_ID" => $arIBlock["ID"], "%NAME" => $search),
		false,
		array("nTopCount" => 20),
		array("ID", "NAME")
	);

	while($arRes = $dbRes->Fetch())
	{
		$arResult[] = array(
			'ID' => $arRes['ID'],
			'NAME' => $arRes['NAME'],
		);
	}

	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($arResult);
	die();
}
?>