<?
//<title>Yandex</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/export_setup_templ.php"));

ClearVars();

$strYandexError = "";

if ($STEP>1)
{
	$IBLOCK_ID = IntVal($IBLOCK_ID);
	$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID)));
	$arIBlockres = new CIBlockResult($arIBlockres);
	if ($IBLOCK_ID<=0 || !($arIBlock = $arIBlockres->GetNext()))
		$strYandexError .= GetMessage("CET_ERROR_NO_IBLOCK1")." #".$IBLOCK_ID." ".GetMessage("CET_ERROR_NO_IBLOCK2")."<br>";

	if (strlen($SETUP_FILE_NAME)<=0)
		$strYandexError .= GetMessage("CET_ERROR_NO_FILENAME")."<br>";

	if (strlen($strYandexError)<=0)
	{
		$bAllSections = False;
		$arSections = array();
		if (is_array($V))
		{
			foreach ($V as $key => $value)
			{
				if (trim($value)=="0")
				{
					$bAllSections = True;
					break;
				}
				if (IntVal($value)>0)
				{
					$arSections[] = IntVal($value);
				}
			}
		}

		if (!$bAllSections && count($arSections)<=0)
			$strYandexError .= GetMessage("CET_ERROR_NO_GROUPS")."<br>";
	}

	if ($ACTION=="EXPORT_SETUP" && strlen($SETUP_PROFILE_NAME)<=0)
		$strYandexError .= GetMessage("CET_ERROR_NO_PROFILE_NAME")."<br>";

	if (strlen($strYandexError)>0)
	{
		$STEP = 1;
	}
}

echo ShowError($strYandexError);

if ($STEP==1)
{
	if (CModule::IncludeModule("iblock"))
	{
		// Get IBlock list
		?>
		<form method="post" action="<?echo $APPLICATION->GetCurPage() ?>">
		<?=bitrix_sessid_post()?>
		<table width="100%" class="edit-table">
			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">1.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<select name="IBLOCK_ID" id="IBLOCK_ID" OnChange="document.getElementById('id_ifr').src='/bitrix/tools/catalog_export/yandex_util.php?IBLOCK_ID='+this[this.selectedIndex].value;">
						<option value=""><?echo GetMessage("CET_SELECT_IBLOCK");?> -&gt;</option>
						<?
						$iblocks = CIBlock::GetList(array("SORT"=>"ASC"));
						while ($ar = $iblocks->GetNext())
						{
							?><option value="<?echo $ar['ID']?>" <?if (IntVal($ar['ID'])==$IBLOCK_ID) echo "selected";?>><?echo $ar['NAME'] ?></option><?
						}
						?>
					</select>
					<br><br>
				</td>
			</tr>

			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">2.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<?// Get sections list ?>
					<font class="text"><b><?echo GetMessage("CET_SELECT_GROUP");?></b></font><br>
					<table border=0>
					<tr>
						<td><div id="tree"></div></td>
					</tr>
					</table>

					<script type="text/javascript">

						clevel = 0;

						function buildNoMenu()
						{
							var buffer;
							buffer  = '<br><table border=0 cellspacing=0 cellpadding=0>';
							buffer += '<tr><td><font class="text" style="color: #A9A9A9;"><?echo CUtil::JSEscape(GetMessage("CET_FIRST_SELECT_IBLOCK"));?></font></td></tr>';
							buffer += '</table>';
							document.getElementById('tree').innerHTML = buffer;
						}

						function buildMenu()
						{
							var i;
							var buffer;
							var imgSpace;

							buffer = '<br><table border=0 cellspacing=0 cellpadding=0>';
							buffer += '<tr>';
							buffer += '<td colspan="2" valign="top" align="left"><input type="checkbox" name="V[]" value="0" id="v0"><label for="v0"><font class="text"><b><?echo CUtil::JSEscape(GetMessage("CET_ALL_GROUPS"));?></b></font></label></td>';
							buffer += '</tr>';

							for (i in Tree[0])
							{
								if (!Tree[i])
								{
									space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"><label for="V'+i+'"><font class="text">' + Tree[0][i][0] + '</font></label>';
									imgSpace = '';
								}
								else
								{
									space = '<input type="checkbox" name="V[]" value="'+i+'"><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[0][i][0] + '</b></font></a>';
									imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
								}

								buffer += '<tr>';
								buffer += '<td width=20 valign="top" align="center">' + imgSpace + '</td>';
								buffer += '<td id="node_' + i + '">' + space + '</td>';
								buffer += '</tr>';
							}

							buffer += '</table>';

							document.getElementById('tree').innerHTML = buffer;
						}

						function collapse(node)
						{
							if (document.getElementById('table_' + node) == null)
							{
								var i;
								var buffer;
								var imgSpace;

								buffer = '<table border=0 id="table_' + node + '" cellspacing=0 cellpadding=0>';
									
								for (i in Tree[node])
								{
									if (!Tree[i])
									{
//										space = '<input type="checkbox" name="V[]" value="'+i+'"><a href="' + Tree[node][i][1] + '">' + Tree[node][i][0] + '</a>';
										space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"><label for="V'+i+'"><font class="text">' + Tree[node][i][0] + '</font></label>';
										imgSpace = '';
									}
									else
									{
										space = '<input type="checkbox" name="V[]" value="'+i+'"><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[node][i][0] + '</b></font></a>';
										imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
									}

									buffer += '<tr>';
									buffer += '<td width=20 align="center" valign="top">' + imgSpace + '</td>';
									buffer += '<td id="node_' + i + '">' + space + '</td>';
									buffer += '</tr>';
								}

								buffer += '</table>';

								document.getElementById('node_' + node).innerHTML += buffer;
								document.getElementById('img_' + node).src = '/bitrix/images/catalog/load/minus.gif';
							}
							else
							{
								var tbl = document.getElementById('table_' + node);
								tbl.parentNode.removeChild(tbl);
								document.getElementById('img_' + node).src = '/bitrix/images/catalog/load/plus.gif';
							}
						}

						function changeStyle(node,action)
						{
							docStyle = document.getElementById('node_' + node).style;

							with (docStyle)
							{
								if (action == 'to')
								{
									backgroundColor= '#e5e5e5';
								}
								else
								{
									backgroundColor = '#ffffff';
								}
							}
						}

					//-->
					</script>

					<iframe src="/bitrix/tools/catalog_export/yandex_util.php?IBLOCK_ID=<?=intval($IBLOCK_ID)?>" id="id_ifr" name="ifr" style="display:none"></iframe>
					<br><br>
				</td>
			</tr>

			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">3.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
<script type="text/javascript">
function showDetailPopup()
{
	if (null == obDetailWindow)
	{
		var s = BX('IBLOCK_ID');
		var obDetailWindow = new BX.CAdminDialog({
			'content_url': '/bitrix/tools/catalog_export/yandex_detail.php?lang=<?=LANGUAGE_ID?>&bxpublic=Y&IBLOCK=' + s[s.selectedIndex].value,
			'width': 900, 'height': 550,
			'resizable': true
		});
		obDetailWindow.Show();
	}
}

function setDetailData(data)
{
	BX('XML_DATA').value = data;
}
</script>
					<font class="text"><?=GetMessage('CAT_DETAIL_PROPS')?>: </font>
					<button onclick="showDetailPopup(); return false;"><?=GetMessage('CAT_DETAIL_PROPS_RUN')?></button>
					<input type="hidden" id="XML_DATA" name="XML_DATA" value="" />
				</td>
			</td>
			
			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">4.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<font class="text">
					<?echo GetMessage("CET_SERVER_NAME");?> <input type="text" name="SETUP_SERVER_NAME" value="<?echo (strlen($SETUP_SERVER_NAME)>0) ? htmlspecialchars($SETUP_SERVER_NAME) : '' ?>" size="50" /> <input type="button" onclick="this.form['SETUP_SERVER_NAME'].value = window.location.host;" value="<?echo htmlspecialchars(GetMessage('CET_SERVER_NAME_SET_CURRENT'))?>" />
					</font>
					<br><br>
				</td>
			</tr>

			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">5.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<font class="text">
					<?echo GetMessage("CET_SAVE_FILENAME");?> <b><?=COption::GetOptionString("catalog", "export_default_path", "/bitrix/catalog_export/")?></b><input type="text" name="SETUP_FILE_NAME" value="<?echo (strlen($SETUP_FILE_NAME)>0) ? htmlspecialchars($SETUP_FILE_NAME) : "yandex.php" ?>" size="50" />
					</font>
					<br><br>
				</td>
			</tr>

			<?if ($ACTION=="EXPORT_SETUP"):?>
				<tr>
					<td width="0%" valign="top">
						<font class="text" style="font-size: 20px;">5.&nbsp;&nbsp;&nbsp;</font>
					</td>
					<td width="100%" valign="top">
						<font class="text">
						<?echo GetMessage("CET_PROFILE_NAME");?> <input type="text" name="SETUP_PROFILE_NAME" value="<?echo htmlspecialchars($SETUP_PROFILE_NAME) ?>" size="30">
						</font>
						<br><br>
					</td>
				</tr>
			<?endif;?>

			<tr>
				<td width="0%" valign="top">
					
				</td>
				<td width="100%" valign="top">
					<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
					<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialchars($_REQUEST["ACT_FILE"]) ?>">
					<input type="hidden" name="ACTION" value="<?echo htmlspecialchars($ACTION) ?>">
					<input type="hidden" name="STEP" value="<?echo intval($STEP) + 1 ?>">
					<input type="hidden" name="SETUP_FIELDS_LIST" value="V,IBLOCK_ID,SETUP_SERVER_NAME,SETUP_FILE_NAME,XML_DATA">
					<input type="submit" value="<?echo ($ACTION=="EXPORT")?GetMessage("CET_EXPORT"):GetMessage("CET_SAVE")?>">
				</td>
			</tr>
		</table>
		</form>
		<?
	}
}
elseif ($STEP==2)
{
	$SETUP_FILE_NAME = COption::GetOptionString("catalog", "export_default_path", "/bitrix/catalog_export/").$SETUP_FILE_NAME;
	
	$FINITE = True;
}
?>