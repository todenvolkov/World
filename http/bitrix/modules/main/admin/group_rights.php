<?
IncludeModuleLangFile(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

$md = CModule::CreateModuleObject($module_id);

$arFilter = Array("ACTIVE"=>"Y");
if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
	$arFilter["ADMIN"] = "N";

$arGROUPS = array();
$z = CGroup::GetList($v1="sort", $v2="asc", $arFilter);
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialchars($zr["NAME"]);
	$arGROUPS[] = $ar;
}

if ($MODULE_RIGHT!="D") :

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $MODULE_RIGHT=="W" && check_bitrix_sessid())
{
	// set per module rights
	COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $GROUP_DEFAULT_RIGHT, "Right for groups by default");
	foreach($arGROUPS as $value)
	{
		$rt = ${"RIGHTS_".$value["ID"]};
		if (strlen($rt)>0 && $rt!="NOT_REF")
		{
			$APPLICATION->SetGroupRight($module_id, $value["ID"], $rt);
		}
		else
		{
			$APPLICATION->DelGroupRight($module_id, array($value["ID"]));
		}
	}
}
$GROUP_DEFAULT_RIGHT = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", "D");
?>
	<tr>
		<td width="50%"><b><?=GetMessage("MAIN_BY_DEFAULT");?></b></td>
		<td width="50%"><?
		if (method_exists($md, "GetModuleRightList"))
			$ar = call_user_func(array($md, "GetModuleRightList"));
		else
			$ar = $APPLICATION->GetDefaultRightList();
		echo SelectBoxFromArray("GROUP_DEFAULT_RIGHT", $ar, htmlspecialchars($GROUP_DEFAULT_RIGHT));
		?><?=bitrix_sessid_post()?></td>
	</tr>
<?
$arUsedGroups = array();
foreach($arGROUPS as $value) :
	$v = $APPLICATION->GetGroupRight($module_id, array($value["ID"]), "N", "N");
	if($v == '')
		continue;
	$arUsedGroups[$value["ID"]] = true;
?>
	<tr>
		<td><?=$value["NAME"]." [<a title=\"".GetMessage("MAIN_USER_GROUP_TITLE")."\" href=\"/bitrix/admin/group_edit.php?ID=".$value["ID"]."&amp;lang=".LANGUAGE_ID."\">".$value["ID"]."</a>]:"?><?
		if ($value["ID"]==1 && $md->SHOW_SUPER_ADMIN_GROUP_RIGHTS=="Y"):
			echo "<br><small>".GetMessage("MAIN_SUPER_ADMIN_RIGHTS_COMMENT")."</small>";
		endif;
		?></td>
		<td><?
		echo SelectBoxFromArray("RIGHTS_".$value["ID"], $ar, htmlspecialchars($v), GetMessage("MAIN_DEFAULT"));
		?></td>
	</tr>
<? endforeach; ?>

<?
if(count($arGROUPS) > count($arUsedGroups)):
?>
<tr>
	<td><select onchange="settingsSetGroupID(this)">
		<option value=""><?echo GetMessage("group_rights_select")?></option>
<?
foreach($arGROUPS as $group):
	if($arUsedGroups[$group["ID"]] == true)
		continue;
?>
		<option value="<?=$group["ID"]?>"><?=$group["NAME"]." [".$group["ID"]."]"?></option>
<?endforeach?>
	</select></td>
	<td><?
	echo SelectBoxFromArray("", $ar, "", GetMessage("MAIN_DEFAULT"));
	?></td>
</tr>
<tr>
	<td colspan="2" align="center" style="padding-bottom:10px;">
<script type="text/javascript">
function settingsSetGroupID(el)
{
	var tr = jsUtils.FindParentObject(el, "tr");
	var sel = jsUtils.FindChildObject(tr.cells[1], "select");
	sel.name = "RIGHTS_"+el.value;
}

function settingsAddRights(a)
{
	var row = jsUtils.FindParentObject(a, "tr");
	var tbl = row.parentNode;

	var tableRow = tbl.rows[row.rowIndex-1].cloneNode(true);
	tbl.insertBefore(tableRow, row);

	var sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
	sel.name = "";
	sel.selectedIndex = 0;

	sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
	sel.selectedIndex = 0;
}
</script>
<a href="javascript:void(0)" onclick="settingsAddRights(this)" hidefocus="true" class="bx-action-href"><?echo GetMessage("group_rights_add")?></a>
	</td>
</tr>
<?endif?>

<?endif;?>
