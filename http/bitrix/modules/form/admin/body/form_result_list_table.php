<?
$page_split = intval(COption::GetOptionString("form", "RESULTS_PAGEN"));
$res_counter = 0;
$can_delete_some = false;
while ($arR = $result->Fetch())
{
	$res_counter++;
	$arResult[] = $arR;
	$arRID[] = $arR["ID"]; // массив ID всех результатов
	
	if (!$can_delete_some)
	{
		if ($F_RIGHT>=20 || ($F_RIGHT>=15 && $USER_ID==$arR["USER_ID"]))
		{
			$arrRESULT_PERMISSION = CFormResult::GetPermissions($arR["ID"], $v);
			if (in_array("DELETE",$arrRESULT_PERMISSION)) $can_delete_some = true;
		}
	}
}
$result = new CDBResult;
$result->InitFromArray($arResult);
?>
<?if ($can_delete_some):?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function OnDelete()
{
	var show_conf;
	var arCheckbox = document.rform.all("ARR_RESULT[]");
	if(!arCheckbox) return;
	if(arCheckbox.length>0 || arCheckbox.value>0)
	{
		show_conf = false;
		if (arCheckbox.value>0 && arCheckbox.checked) show_conf = true;
		else
		{
			for(i=0; i<arCheckbox.length; i++)
			{
				if (arCheckbox[i].checked) 
				{
					show_conf = true;
					break;
				}
			}
		}
		if (show_conf)
			return confirm("<?=GetMessage("FORM_DELETE_CONFIRMATION")?>");
		else
			alert('<?=GetMessage("FORM_SELECT_RESULTS")?>');
	}
	return false;
}

function OnSelectAll(fl)
{
	var arCheckbox = document.rform.all("ARR_RESULT[]");
	if(!arCheckbox) return;
	if(arCheckbox.length>0)
		for(i=0; i<arCheckbox.length; i++)
			arCheckbox[i].checked = fl;
	else
		arCheckbox.checked = fl;
}
//-->
</SCRIPT>
<?endif;?>

<form name="rform" method="POST" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?=$WEB_FORM_ID?>&by=<?echo htmlspecialchars($by)?>&order=<?echo htmlspecialchars($order)?>&WEB_FORM_NAME=<?=$WEB_FORM_NAME?>#nav_start">
<input type="hidden" name="WEB_FORM_ID" value="<?=$WEB_FORM_ID?>">
<?=bitrix_sessid_post()?>

<p><?
$result->NavStart($page_split); echo $result->NavPrint(GetMessage("FORM_PAGES"));
if (!$result->NavShowAll)
{
	$pagen_from = (intval($result->NavPageNomer)-1)*intval($result->NavPageSize);
	$arRID_tmp = array();
	if (is_array($arRID) && count($arRID)>0)
	{
		$i=0;
		foreach($arRID as $rid)
		{
			if ($i>=$pagen_from && $i<$pagen_from+$page_split) 
			{
				$arRID_tmp[] = $rid; // массив ID результатов для данной страницы
			}
			$i++;
		}
	}
	$arRID = $arRID_tmp;
}

?></p>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<?
	/***********************************************
				  Шапка таблицы
	************************************************/
	?>
	<tr>
		<td valign="top" align="center" class="tablehead1" nowrap>								
			<table border="0" width="0%" cellspacing="0" cellpadding="0">
				<tr>
					<td <?if ($SHOW_STATUS!="Y"):?> align="center" <?endif;?>valign="top" nowrap><?
					if ($can_delete_some):
						?><input type="checkbox" name="selectall" value="Y" onclick="OnSelectAll(this.checked)">&nbsp;<?
					endif;
					?><font class="tableheadtext">ID<?if ($SHOW_STATUS!="Y"):?><br><?=SortingEx("s_id")?><?endif;?></font></td>
					<?if ($SHOW_STATUS=="Y"):?>
					<td><img src="/bitrix/images/1.gif" width="5" height="1"></td>
					<td><?=SortingEx("s_id")?></td>
					<?endif;?>
				</tr>
				<?if ($SHOW_STATUS=="Y"):?>
				<tr>
					<td valign="top" nowrap><font class="tableheadtext"><?=GetMessage("FORM_STATUS")?></font></td>
					<td><img src="/bitrix/images/1.gif" width="5" height="1"></td>
					<td><?echo SortingEx("s_status")?></td>
				</tr>
				<?endif;?>
			</table>
		<td valign="top" align="center" class="tablehead2">
		<font class="tableheadtext"><?=GetMessage("FORM_TIMESTAMP")?><br><?echo SortingEx("s_timestamp")?></font></td>
		<?if ($F_RIGHT>=25):?>
		<td valign="top" align="center" nowrap class="tablehead2">
			<table border="0" width="0%" cellspacing="0" cellpadding="0">
				<?if (CModule::IncludeModule("statistic")):?>
				<tr>
					<td nowrap><font class="tableheadtext"><?echo GetMessage("FORM_USER")?></font></td>
					<td><img src="/bitrix/images/1.gif" width="5" height="1"></td>
					<td><?echo SortingEx("s_user_id")?></td>
				</tr>
				<tr>
					<td nowrap><font class="tableheadtext"><?echo GetMessage("FORM_GUEST_ID")?></font></td>
					<td><img src="/bitrix/images/1.gif" width="5" height="1"></td>
					<td><?echo SortingEx("s_guest_id")?></td>
				</tr>
				<tr>
					<td nowrap><font class="tableheadtext"><?echo GetMessage("FORM_SESSION_ID")?></font></td>
					<td><img src="/bitrix/images/1.gif" width="5" height="1"></td>
					<td><?echo SortingEx("s_session_id")?></td>
				</tr>
				<?else:?>
				<tr>
					<td nowrap align="center"><font class="tableheadtext"><?echo GetMessage("FORM_USER")?></font></td>
				</tr>
				<tr>
					<td nowrap align="center"><?echo SortingEx("s_user_id")?></td>
				</tr>
				<?endif;?>
			</table></td>
		<?endif;?>
		<?
		if ($res_counter>0)
		{
			$arFilter = array(
				"IN_RESULTS_TABLE"	=> "Y",
				"RESULT_ID"			=> implode(" | ", $arRID)
				);
			CForm::GetResultAnswerArray($WEB_FORM_ID, $arrColumns, $arrAnswers, $arrAnswersSID, $arFilter);
		}
		else
		{
			$arFilter = array("IN_RESULTS_TABLE" => "Y");
			$rsFields = CFormField::GetList($WEB_FORM_ID, "ALL", ($v1="s_c_sort"), ($v2="asc"), $arFilter, $v3);
			while ($arField = $rsFields->Fetch()) $arrColumns[$arField["ID"]] = $arField;
		}

		//echo "<pre>"; print_r($arrColumns); echo "</pre>";
		//echo "<pre>"; print_r($arrAnswers); echo "</pre>";
		//echo "<pre>"; print_r($arrAnswersSID); echo "</pre>";
		//echo "<pre>"; print_r($arFilter); echo "</pre>";

		$i=0;
		$colspan = 4;
		$class=2;
		$total = count($arrColumns);
		$arrColumns = (is_array($arrColumns)) ? $arrColumns : array();
		reset($arrColumns);
		while (list($key, $arrCol) = each($arrColumns)) :

			$i++;

			if ($i==$total) $class=3;

			if (!is_array($arrNOT_SHOW_TABLE) || !in_array($arrCol["SID"],$arrNOT_SHOW_TABLE)):

			if (($arrCol["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrCol["ADDITIONAL"]!="Y") :
				$colspan++;
				if (strlen($arrCol["RESULTS_TABLE_TITLE"])<=0)
				{
					$title = ($arrCol["TITLE_TYPE"]=="html") ? strip_tags($arrCol["TITLE"]) : htmlspecialchars($arrCol["TITLE"]);
					$title = TruncateText($title,100);
				}
				else $title = htmlspecialchars($arrCol["RESULTS_TABLE_TITLE"]);
				?>
				<td valign="top" class="tablehead<?=$class?>"><font class="tableheadtext"><?
				if ($F_RIGHT>=25) :
				?>[<a title="<?=GetMessage("FORM_FIELD_PARAMS")?>" class="tablebodylink" href="/bitrix/admin/form_field_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arrCol["ID"]?>&WEB_FORM_ID=<?=$WEB_FORM_ID?>&additional=<?=$arrCol["ADDITIONAL"]?>"><?=$arrCol["ID"]?></a>]<br><?
				endif;
				echo $title;
				?></td><?
			endif;
			endif;
		endwhile;
		?>
	</tr>
	<?
	/***********************************************
				  Тело таблицы
	************************************************/

	$j=0;
	$arrUsers = array();
	while ($result->NavNext(true, "f_")) : 
		$j++;
		$arrRESULT_PERMISSION = CFormResult::GetPermissions($f_ID, $v);

		$can_view = false;
		$can_edit = false;
		$can_delete = false;
		if ($F_RIGHT>=20 || ($F_RIGHT>=15 && $USER_ID==$f_USER_ID))
		{
			if (in_array("VIEW",$arrRESULT_PERMISSION)) $can_view = true;
			if (in_array("EDIT",$arrRESULT_PERMISSION)) $can_edit = true;
			if (in_array("DELETE",$arrRESULT_PERMISSION)) $can_delete = true;
		}

	?>
	<?if ($j>1):?>
	<tr><td colspan="<?=$colspan?>" class="tablebody4 selectedbody">&nbsp;</td></tr>
	<?endif;?>
	<tr>
		<td colspan="<?=$colspan?>" class="tablebody4">
			<table cellspacing=0 cellpadding=2>
				<tr>
					<td><font class="tablebodytext"><?
						if ($can_delete_some && $can_delete):
							?><input type="checkbox" name="ARR_RESULT[]" value="<?=$f_ID?>"><?
						endif;
						?><input type="hidden" name="RESULT_ID[]" value="<?=$f_ID?>">&nbsp;ID:&nbsp;<b><?
						echo ($USER_ID==$f_USER_ID) ? "<font class='required'>".$f_ID."</font>" : $f_ID;
						?></b></font></td>
				</tr>
				<?if ($SHOW_STATUS=="Y"):?>
				<tr>
					<td><font class="tablebodytext"><?echo GetMessage("FORM_STATUS")?>:&nbsp;</font><?
					echo "<font class='tablebodytext'>[&nbsp;</font><font class='".$f_STATUS_CSS."'>".$f_STATUS_TITLE."</font><font class='tablebodytext'>&nbsp;]</font>";
					?></td>
				<?if (in_array("EDIT",$arrRESULT_PERMISSION) && $F_RIGHT>=25):?>
					<td><font class="smalltext"><?=GetMessage("FORM_CHANGE_TO")?></font></td>
					<td><input type="hidden" name="STATUS_PREV_<?=$f_ID?>" value="<?=$f_STATUS_ID?>"><?
					echo SelectBox("STATUS_".$f_ID, CFormStatus::GetDropdown($WEB_FORM_ID, array("MOVE"), $f_USER_ID), " ");
					?></td>
				<?endif;?>
				</tr>
				<?endif;?>
			</table>
		</td>
	</tr>
	<tr valign="top">
		<td class="tablebody1" nowrap>
			<table cellspacing=0 cellpadding=0>

				<?if ($can_edit):?>
				<tr>
					<td><font class="tablebodytext"><a title="<?=GetMessage("FORM_EDIT_ALT")?>" href="<?=$EDIT_URL?>?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?echo $WEB_FORM_ID?>&RESULT_ID=<?echo $f_ID?>&WEB_FORM_NAME=<?=$WEB_FORM_NAME?>"><? echo GetMessage("FORM_EDIT")?></a></font></td>
				</tr>
				<?endif;?>

				<?if ($can_view):?>
				<tr>
					<td><font class="tablebodytext"><a title="<?=GetMessage("FORM_VIEW_ALT")?>" href="<?=$VIEW_URL?>?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?echo $WEB_FORM_ID?>&RESULT_ID=<?echo $f_ID?>&WEB_FORM_NAME=<?=$WEB_FORM_NAME?>"><? echo GetMessage("FORM_VIEW")?></a></font></td>
				</tr>
				<?endif;?>

				<?if ($can_delete):?>
				<tr>
					<td><font class="tablebodytext"><a title="<?=GetMessage("FORM_DELETE_ALT")?>" href="javascript:if(confirm('<?=GetMessage("FORM_CONFIRM_DELETE")?>')) window.location='?lang=<?=LANGUAGE_ID?>&WEB_FORM_ID=<?echo $WEB_FORM_ID?>&WEB_FORM_NAME=<?=$WEB_FORM_NAME?>&del_id=<?echo $f_ID?>&<?=bitrix_sessid_get()?>#nav_start'" class="tablebodytext"><?=GetMessage("FORM_DELETE")?></a></font></td>
				</tr>
				<?endif;?>

			</table></font></td>
		<td align="center" class="tablebody2" nowrap><font class="tablebodytext"><?$arr = explode(" ",$f_TIMESTAMP_X); echo $arr[0]."<br>".$arr[1]?></font></td>
		<?if ($F_RIGHT>=25):?>
		<td class="tablebody2"><font class="tablebodytext"><?
			if ($f_USER_ID>0) :
				if (!in_array($f_USER_ID, array_keys($arrUsers)))
				{
					$rsUser = CUser::GetByID($f_USER_ID);
					$rsUser->ExtractFields("u_");
					$f_LOGIN = $u_LOGIN;
					$f_USER_NAME = $u_NAME." ".$u_LAST_NAME;
					$arrUsers[$f_USER_ID]["USER_NAME"] = $f_USER_NAME;
					$arrUsers[$f_USER_ID]["LOGIN"] = $f_LOGIN;
				}
				else
				{
					$f_USER_NAME = $arrUsers[$f_USER_ID]["USER_NAME"];
					$f_LOGIN = $arrUsers[$f_USER_ID]["LOGIN"];
				}
				echo "<font class='tablebodytext'>[</font><a class='tablebodylink' title='".GetMessage("FORM_EDIT_USER")."' href='user_edit.php?lang=".LANGUAGE_ID."&ID=".$f_USER_ID."'>$f_USER_ID</a><font class='tablebodytext'>] ($f_LOGIN) $f_USER_NAME</font>";
				echo ($f_USER_AUTH=="N") ? " <font class='pointed'>".GetMessage("FORM_NOT_AUTH")."</font>" : "";
			else :
				echo "<font class='tablebodytext'>".GetMessage("FORM_NOT_REGISTERED")."</font>";
			endif;
			if (CModule::IncludeModule("statistic")):
				if (intval($f_STAT_GUEST_ID)>0) :
					echo " <font class='tablebodytext'>[<a title='".GetMessage("FORM_GUEST")."' class='tablebodylink' href='/bitrix/admin/guest_list.php?lang=".LANGUAGE_ID."&find_id=". $f_STAT_GUEST_ID."&set_filter=Y'>".$f_STAT_GUEST_ID."</a>]</font>";
				endif;
				if (intval($f_STAT_SESSION_ID)>0) :
					echo " <font class='tablebodytext'>(<a title='".GetMessage("FORM_SESSION")."' class='tablebodylink' href='/bitrix/admin/session_list.php?lang=".LANGUAGE_ID."&find_id=". $f_STAT_SESSION_ID."&set_filter=Y'>".$f_STAT_SESSION_ID."</a>)</font>";
				endif;
			endif;
		?></font></td>
		<?endif;?>
		<?
		reset($arrColumns);
		$k=0;
		$columns = count($arrColumns);
		while (list($FIELD_ID,$arrC) = each($arrColumns)):

			$k++;
			if ($columns==$k) $bs=3; else $bs=2;

			if (!is_array($arrNOT_SHOW_TABLE) || !in_array($arrC["SID"],$arrNOT_SHOW_TABLE)):

			if (($arrC["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrC["ADDITIONAL"]!="Y") :						
		?>
		<td valign="top" align="left" class="tablebody<?=$bs?>" nowrap>
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<?
			$arrAnswer = $arrAnswers[$f_ID][$FIELD_ID];
			if (is_array($arrAnswer)) :
			reset($arrAnswer);
			$count = count($arrAnswer);
			$i = 0;
			while (list($key,$arrA) = each($arrAnswer)):
				$i++;
			?>
				<tr>
					<td valign="top" width="100%" <?if ($i!=$count) echo "class='tline'"?>><font class="tablebodytext"><?

						if (strlen(trim($arrA["USER_TEXT"]))>0)
						{
							if (intval($arrA["USER_FILE_ID"])>0)
							{
								if ($arrA["USER_FILE_IS_IMAGE"]=="Y" && $USER->IsAdmin())
									echo htmlspecialchars($arrA["USER_TEXT"])."<br>";
							}
							else echo TxtToHTML($arrA["USER_TEXT"],true,100)."<br>";
						}

						if (strlen(trim($arrA["ANSWER_TEXT"]))>0)
						{
							$answer = "[<font class='anstext'>".TxtToHTML($arrA["ANSWER_TEXT"],true,100)."</font>]";
							if (strlen(trim($arrA["ANSWER_VALUE"]))>0 && $SHOW_ANSWER_VALUE=="Y") $answer .= "&nbsp;"; else $answer .= "<br>";
							echo $answer;
						}

						if (strlen(trim($arrA["ANSWER_VALUE"]))>0 && $SHOW_ANSWER_VALUE=="Y")
							echo "(<font class='ansvalue'>".TxtToHTML($arrA["ANSWER_VALUE"],true,100)."</font>)<br>";

						if (intval($arrA["USER_FILE_ID"])>0)
						{
							if ($arrA["USER_FILE_IS_IMAGE"]=="Y") :
								echo CFile::ShowImage($arrA["USER_FILE_ID"], 0, 0, "border=0", "", true);
							else :
								?><a title="<?=GetMessage("FORM_VIEW_FILE")?>" target="_blank" class="tablebodylink" href="/bitrix/tools/form_show_file.php?rid=<?=$f_ID?>&hash=<?echo $arrA["USER_FILE_HASH"]?>&lang=<?=LANGUAGE_ID?>"><?echo htmlspecialchars($arrA["USER_FILE_NAME"])?></a><br>(<?
								$a = array("b", "Kb", "Mb", "Gb");
								$pos = 0;
								$size = $arrA["USER_FILE_SIZE"];
								while($size>=1024) {$size /= 1024; $pos++;}
								echo round($size,2)." ".$a[$pos];
								?>)<br>[&nbsp;<a title="<?echo str_replace("#FILE_NAME#", $arrA["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))?>" class="tablebodylink" href="/bitrix/tools/form_show_file.php?rid=<?=$f_ID?>&hash=<?echo $arrA["USER_FILE_HASH"]?>&lang=<?=LANGUAGE_ID?>&action=download"><?echo GetMessage("FORM_DOWNLOAD")?></a>&nbsp;]<?
							endif;
						}
						?></font></td>
				</tr>
			<? 
			endwhile; 
			endif;
			?>
			</table></td>
		<?
			endif;
			endif;
		endwhile;
		?>
	</tr>
	<? 
	endwhile; 
	?>
	<tr valign="top">
		<td align="left" class="tablebody4 selectedbody"  colspan="<?=$colspan?>"><font class="tablebodytext"><?=GetMessage("FORM_TOTAL")?>&nbsp;<?echo intval($res_counter)?></font></td>
	</tr>
</table>
<p><?echo $result->NavPrint(GetMessage("FORM_PAGES"))?></p>
<?if (intval($res_counter)>0 && $SHOW_STATUS=="Y" && $F_RIGHT>=25):?>
<p><input type="submit" name="save" value="<? echo GetMessage("FORM_SAVE")?>" class="button"><input type="hidden" name="save" value="Y">&nbsp;<input type="reset" class="button" value="<?echo GetMessage("FORM_RESET")?>"></p>
<?endif;?>

<?if ($can_delete_some):?>
<p><input type="submit" name="delete" value="<?=GetMessage("FORM_DELETE_SELECTED")?>" class="button" onClick="return OnDelete()"></p>
<?endif;?>

</form>