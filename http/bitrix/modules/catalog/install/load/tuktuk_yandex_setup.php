<?
//<title>Yandex</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/export_setup_templ.php"));

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
		<table width="100%">
			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">1.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<select name="IBLOCK_ID" OnChange="document.all['ifr'].src='/bitrix/php_interface/include/catalog_export/yandex_util.php?IBLOCK_ID='+this[this.selectedIndex].value;">
						<option value=""><?echo GetMessage("CET_SELECT_IBLOCK");?> -&gt;</option>
						<?
						$iblocks = CIBlock::GetList(array("SORT"=>"ASC"));
						while ($iblocks->ExtractFields("f_"))
						{
							?><option value="<?echo $f_ID ?>" <?if (IntVal($f_ID)==$IBLOCK_ID) echo "selected";?>><?echo $f_NAME ?></option><?
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

					<SCRIPT LANGUAGE="JavaScript">
						clevel = 0;

						function buildNoMenu()
						{
							var buffer;
							document.getElementById('tree').innerHTML="";
							buffer  = '<br><table border=0 cellspacing=0 cellpadding=0>';
							buffer += '<tr><td><font class="text" style="color: #A9A9A9;"><?echo GetMessage("CET_FIRST_SELECT_IBLOCK");?></font></td></tr>';
							buffer += '</table>';
							document.getElementById('tree').insertAdjacentHTML('beforeEnd', buffer);
						}

						function buildMenu()
						{
							var i;
							var buffer;
							var imgSpace;

							document.getElementById('tree').innerHTML="";

							buffer = '<br><table border=0 cellspacing=0 cellpadding=0>';
							buffer += '<tr>';
							buffer += '<td colspan="2" valign="top" align="left"><input type="checkbox" name="V[]" value="0" id="v0"><label for="v0"><font class="text"><b><?echo GetMessage("CET_ALL_GROUPS");?></b></font></label></td>';
							buffer += '</tr>';

							for (i in Tree[0])
							{
								if (!Tree[i])
								{
//									space = '<input type="checkbox" name="V[]" value="'+i+'"><a href="' + Tree[0][i][1] + '">' + Tree[0][i][0] + '</a>';
									space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"><label for="V'+i+'"><font class="text">' + Tree[0][i][0] + '</font></label>';
									imgSpace = '';
								}
								else
								{
									space = '<input type="checkbox" name="V[]" value="'+i+'"><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[0][i][0] + '</b></font></a>';
									imgSpace = '<img src="/bitrix/images/admin/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
								}

								buffer += '<tr>';
								buffer += '<td width=20 valign="top" align="center">' + imgSpace + '</td>';
								buffer += '<td id="node_' + i + '">' + space + '</td>';
								buffer += '</tr>';
							}

							buffer += '</table>';

							document.getElementById('tree').insertAdjacentHTML('beforeEnd', buffer);

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
										imgSpace = '<img src="/bitrix/images/admin/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
									}

									buffer += '<tr>';
									buffer += '<td width=20 align="center" valign="top">' + imgSpace + '</td>';
									buffer += '<td id="node_' + i + '">' + space + '</td>';
									buffer += '</tr>';
								}

								buffer += '</table>';

								document.getElementById('node_' + node).insertAdjacentHTML('beforeEnd',buffer);
								document.getElementById('img_' + node).src = '/bitrix/images/admin/minus.gif';
							}
							else
							{
								document.getElementById('table_' + node).removeNode(true);
								document.getElementById('img_' + node).src = '/bitrix/images/admin/plus.gif';
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
					</SCRIPT>

					<iframe src="/bitrix/php_interface/include/catalog_export/yandex_util.php?IBLOCK_ID=<?=$IBLOCK_ID?>" id="ifr" name="ifr" style="display:none"></iframe>
					<br><br>
				</td>
			</tr>

			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">3.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<font class="text">
					<?echo GetMessage("CET_SAVE_FILENAME");?> <input type="text" name="SETUP_FILE_NAME" value="<?echo (strlen($SETUP_FILE_NAME)>0) ? $SETUP_FILE_NAME : "/upload/yandex.php" ?>" size="50">
					</font>
					<br><br>
				</td>
			</tr>

			<?if ($ACTION=="EXPORT_SETUP"):?>
				<tr>
					<td width="0%" valign="top">
						<font class="text" style="font-size: 20px;">4.&nbsp;&nbsp;&nbsp;</font>
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
					<input type="hidden" name="lang" value="<?echo $lang ?>">
					<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialchars($_REQUEST["ACT_FILE"]) ?>">
					<input type="hidden" name="ACTION" value="<?echo $ACTION ?>">
					<input type="hidden" name="STEP" value="<?echo $STEP + 1 ?>">
					<input type="hidden" name="SETUP_FIELDS_LIST" value="V,IBLOCK_ID,SETUP_FILE_NAME">
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
	$FINITE = True;
}
?>