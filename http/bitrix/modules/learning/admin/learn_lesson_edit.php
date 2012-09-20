<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$message = null;
$bVarsFromForm = false;
$ID = intval($ID);
$COURSE_ID = intval($COURSE_ID);
$CHAPTER_ID = intval($CHAPTER_ID);

$course = CCourse::GetByID($COURSE_ID);

if($arCourse = $course->Fetch())
	$bBadCourse=(CCourse::GetPermission($COURSE_ID)<"W");
else
	$bBadCourse = true;

$aTabs = array(
	array(
		"DIV" => "edit1",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB1"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")
	),

	array(
		"DIV" => "edit2",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB2"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")
	),

	array(
		"DIV" => "edit3",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB3"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if (!$bBadCourse && $_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
	$arPREVIEW_PICTURE["del"] = ${"PREVIEW_PICTURE_del"};
	$arPREVIEW_PICTURE["MODULE_ID"] = "learning";

	$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
	$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
	$arDETAIL_PICTURE["MODULE_ID"] = "learning";

	$cl = new CLesson;

	$arFields = Array(
		"ACTIVE" => $ACTIVE,
		"CHAPTER_ID" => $CHAPTER_ID,
		"COURSE_ID" => $COURSE_ID,
		"NAME" => $NAME,
		"SORT" => $SORT,

		"DETAIL_PICTURE" => $arDETAIL_PICTURE,
		"DETAIL_TEXT" => $DETAIL_TEXT,
		"DETAIL_TEXT_TYPE" => $DETAIL_TEXT_TYPE,

		"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
		"PREVIEW_TEXT" => $PREVIEW_TEXT,
		"PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE
	);

	if ($CONTENT_SOURCE == "file")
	{
		$arFields["DETAIL_TEXT_TYPE"] = "file";
		$arFields["LAUNCH"] = $LAUNCH;
	}



	if($ID>0)
	{
		$res = $cl->Update($ID, $arFields);
	}
	else
	{
		$ID = $cl->Add($arFields);
		$res = ($ID>0);
	}



	if(!$res)
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);

		$bVarsFromForm = true;
	}
	else
	{
		if(strlen($apply)<=0)
		{
			if ($from == "learn_admin")
				LocalRedirect("/bitrix/admin/learn_course_admin.php?lang=".LANG.GetFilterParams("filter_", false));
			elseif ($from == "learn_chapter_admin")
				LocalRedirect("/bitrix/admin/learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_", false));
			elseif (strlen($return_url)>0)
			{
				if(strpos($return_url, "#LESSON_ID#")!==false)
				{
					$return_url = str_replace("#LESSON_ID#", $ID, $return_url);
				}
				LocalRedirect($return_url);
			}
			else
				LocalRedirect("/bitrix/admin/learn_lesson_admin.php?lang=". LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID.GetFilterParams("filter_", false));
		}

		LocalRedirect("/bitrix/admin/learn_lesson_edit.php?ID=".$ID."&CHAPTER_ID=".$CHAPTER_ID."&lang=". LANG."&COURSE_ID=".$COURSE_ID."&tabControl_active_tab=".urlencode($tabControl_active_tab).GetFilterParams("filter_", false));
	}
}

if (!$bBadCourse)
{
	if ($ID > 0)
		$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage("LEARNING_LESSONS").": ".GetMessage("LEARNING_EDIT_TITLE"));
	else
		$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_LESSONS').": ".GetMessage("LEARNING_NEW_TITLE"));
}
else
	$APPLICATION->SetTitle(GetMessage('LEARNING_LESSONS').": ".GetMessage("LEARNING_EDIT_TITLE"));

//Defaults
$str_ACTIVE = "Y";
$str_DETAIL_TEXT_TYPE = $str_PREVIEW_TEXT_TYPE = "text";
$str_SORT = "500";
$str_CHAPTER_ID = $CHAPTER_ID;

$result = CLesson::GetByID($ID);
if(!$result->ExtractFields("str_"))
	$ID = 0;

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$DB->InitTableVarsForEdit("b_learn_lesson", "", "str_");
}


//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id="));


if(intval($CHAPTER_ID)>0)
{
	/*
	$nav = CChapter::GetNavChain($COURSE_ID, $str_CHAPTER_ID);
	while($nav->ExtractFields("nav_"))
	{
		$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
	}*/
}
else
{
	$adminChain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_CONTENT")."</b>", "LINK"=>""));
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($message)
	echo $message->Show();

if (!$bBadCourse):

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_lesson_admin.php?COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$CHAPTER_ID."&lang=".LANG,
		"TITLE"=>GetMessage("MAIN_ADMIN_MENU_LIST")
	),
);


if ($ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_lesson_edit.php?COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID."&lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_lesson_admin.php?COURSE_ID=".$COURSE_ID."&action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get().GetFilterParams("filter_")."';",
	);

}
$context = new CAdminContextMenu($aContext);
$context->Show();


?>

<script type="text/javascript">
function toggleSource() {
	if (document.lesson_edit.CONTENT_SOURCE[0].checked)
	{
		document.getElementById("source_field[0]").style.display = "";
		if (document.getElementById("source_field[1]"))
			document.getElementById("source_field[1]").style.display = "";
		document.getElementById("source_file").style.display = "none";
	}
	else
	{
		document.getElementById("source_field[0]").style.display = "none";
		if (document.getElementById("source_field[1]"))
			document.getElementById("source_field[1]").style.display = "none";
		document.getElementById("source_file").style.display = "";
	}
}
</script>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&COURSE_ID=<?echo $COURSE_ID?><?echo GetFilterParams("filter_");?>" enctype="multipart/form-data" name="lesson_edit">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<input type="hidden" name="return_url" value="<?echo htmlspecialchars($return_url)?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
	<?if($ID>0):?>
	<tr>
		<td valign="top" align="right">ID:</td>
		<td valign="top"><?echo $str_ID?></td>
	</tr>
	<tr>
		<td valign="top" align="right"><?echo GetMessage("LEARNING_LAST_UPDATE")?>:</td>
		<td valign="top"><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td valign="top" width="50%" align="right"><?echo GetMessage("LEARNING_ACTIVE")?>:</td>
		<td valign="top" width="50%">
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right"><?echo GetMessage("LEARNING_PARENT_CHAPTER_ID")?>:</td>
		<td valign="top">
		<?$l = CChapter::GetTreeList($COURSE_ID);?>
		<select name="CHAPTER_ID">
			<option value="0"><?echo GetMessage("LEARNING_CONTENT")?></option>
		<?
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if($str_CHAPTER_ID == $l_ID)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $l_DEPTH_LEVEL)?><?echo $l_NAME?></option><?
			endwhile;
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right"><span class="required">*</span><?echo GetMessage("LEARNING_NAME")?>:</td>
		<td valign="top">
			<input type="text" name="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td valign="top" align="right"><?echo GetMessage("LEARNING_SORT")?>:</td>
		<td valign="top">
			<input type="text" name="SORT" size="7" maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileBrowserWindMedia",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array("SITE" => $_GET["site"], "PATH" =>(strlen($str_FILENAME)>0 ? GetDirPath($str_FILENAME) : '')),
		"select" => 'F',// F - file only, D - folder only,
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'wmv,flv,mp4,wma,mp3',//'' - don't shjow select, 'image' - only images; "ext1,ext2" - Only files with ext1 and ext2 extentions;
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
<?$tabControl->BeginNextTab();?>
<!-- 	<tr class="heading">
		<td colspan="2"><b><?echo GetMessage("LEARNING_ELEMENT_DETAIL")?></td>
	</tr> -->
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?php function CustomizeEditor() {?>
				<?php ob_start()?>
					<div class="bxed-dialog">
						<table class="bx-image-dialog-tbl">
					        <tr>
					        	<td class="bx-par-title"><?echo GetMessage("LEARNING_PATH_TO_FILE")?>:</td>
					        	<td class="bx-par-val" colspan="3">
					        		<input type="text" size="30" id="mediaPath" />
									<input type="button" value="..." id="OpenFileBrowserWindMedia_button">
					        	</td>
					    	</tr>
					    	<tr>
					        	<td class="bx-par-title"><?echo GetMessage("LEARNING_WIDTH")?>:</td>
					        	<td width="80px"><input type="text" size="3" id="mediaWidth" /></td>
					        	<td><?echo GetMessage("LEARNING_HEIGHT")?>:</td>
					        	<td class="bx-par-val"><input type="text" size="3" id="mediaHeight" /></td>
					    	</tr>
						</table>
					</div>
				<?php $dialogHTML = ob_get_clean()?>
				<script type="text/javascript">
					var pEditor;
					var pElement;

					function SetUrl(filename, path, site)
					{
						if (path.substr(-1) == "/")
						{
							path = path.substr(0, path.length - 1);
						}
						var url = path+'/'+filename;
						BX("mediaPath").value = url;
						if(BX("mediaPath").onchange)
							BX("mediaPath").onchange();
					}

					function _mediaParser(_str, pMainObj)
					{
						// **** Parse WMV ****
						// b1, b3 - quotes
						// b2 - id of the div
						// b4 - javascript config
						var ReplaceWMV = function(str, b1, b2, b3, b4)
						{
							var
								id = b2,
								JSConfig, w, h, prPath;

							try {eval('JSConfig = ' + b4); } catch (e) { JSConfig = false; }
							if (!id || !JSConfig)
								return '';

							var w = (parseInt(JSConfig.width) || 50);
							var h = (parseInt(JSConfig.height) || 25);

							var arTagParams = {file: JSConfig.file};
							var bxTag =  pMainObj.GetBxTag(id);

							if (bxTag && bxTag && bxTag.tag == "media")
							{
								arTagParams.id = id;
							}
							return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
						}
						_str = _str.replace(/<script.*?silverlight\.js.*?<\/script>\s*?<script.*?wmvplayer\.js.*?<\/script>\s*?<div.*?id\s*?=\s*?("|\')(.*?)\1.*?<\/div>\s*?<script.*?jeroenwijering\.Player\(document\.getElementById\(("|\')\2\3.*?wmvplayer\.xaml.*?({.*?})\).*?<\/script>/ig, ReplaceWMV);

						// **** Parse FLV ****
						var ReplaceFLV = function(str, attr)
						{
							attr = attr.replace(/[\r\n]+/ig, ' '); attr = attr.replace(/\s+/ig, ' '); attr = jsUtils.trim(attr);
							var
								arParams = {},
								arFlashvars = {},
								w, h, id, prPath;

							attr.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]+?)\3/ig, function(s, b0, b1, b2, b3)
							{
								b1 = b1.toLowerCase();
								if (b1 == 'src' || b1 == 'type' || b1 == 'allowscriptaccess' || b1 == 'allowfullscreen' || b1 == 'pluginspage' || b1 == 'wmode')
									return '';
								arParams[b1] = b3; return b0;
							});
							id = arParams.id;

							if (!id || !arParams.flashvars)
								return str;

							arParams.flashvars.replace(/(\w+?)=((?:\s|\S)*?)&/ig, function(s, name, val) { arFlashvars[name] = val; return ''; });
							var w = (parseInt(arParams.width) || 50);
							var h = (parseInt(arParams.height) || 25);

							var arTagParams = {file: arFlashvars["file"]};
							var bxTag =  pMainObj.GetBxTag(id);

							if (bxTag && bxTag && bxTag.tag == "media")
							{
								arTagParams.id = id;
							}
							return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
						}

						_str = _str.replace(/<object.*?>.*?<embed((?:\s|\S)*?player\/mediaplayer\/player\.swf(?:\s|\S)*?)(?:>\s*?<\/embed)?(?:\/?)?>.*?<\/object>/ig, ReplaceFLV);
						return _str;
					}
					arContentParsers.unshift(_mediaParser);

					function _mediaUnParser(_node, pMainObj)
					{
						bxTag = pMainObj.GetBxTag(_node.arAttributes["id"]);

						if (bxTag && bxTag.tag && bxTag.tag == "media")
						{
							var ext = bxTag.params.file.substr(bxTag.params.file.length - 3);
							var bWM = ext == "wmv" || ext == "wma";
							if (!bWM) // FL
							{
								var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" ';
								str += 'id="' + _node.arAttributes["id"] + '" ';
								str += 'width="' + _node.arAttributes["width"] + '" ';
								str += 'height="' + _node.arAttributes["height"] + '" ';
								str += '>';
								str += '<param name="movie" value="/bitrix/components/bitrix/player/mediaplayer/player.swf">';

								var embed = '<embed src="/bitrix/components/bitrix/player/mediaplayer/player.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" pluginspage="http:/' + '/www.macromedia.com/go/getflashplayer" ';
								embed += 'id="' + _node.arAttributes["id"] + '" ';

								var arParams = {
									"menu": "true",
									"wmode": "transparent",
									"width": _node.arAttributes["width"],
									"height": _node.arAttributes["height"],
									"flashvars" : {
										"file" : bxTag.params.file,
										"logo.hide" : "true",
										"skin": "/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf",
										"repeat" : "N",
										"bufferlength" : "10",
										"dock" : "true"
									}
								}

								for (i in arParams)
								{
									if (i == 'flashvars')
									{
										embed += 'flashvars="';
										str += '<param name="flashvars" value="';
										for (k in arParams[i])
										{
											embed += k + '=' + arParams[i][k] + '&';
											str += k + '=' + arParams[i][k] + '&';
										}
										embed = embed.substring(0, embed.length - 1) + '" ';
										str = str.substring(0, str.length - 1) + '">';
									}
									else
									{
										embed += i + '="' + arParams[i] + '" ';
										str += '<param name="' + i +'" value="' + arParams[i] +'">';
									}
								}
								embed += '/>';
								str += embed +'</object>';
							}
							else // WM
							{
								str = '<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js" /><\/script>' +
								'<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"><\/script>' +
								'<div id="' + _node.arAttributes["id"] + '">WMV Player</div>' +
								'<script type="text/javascript">new jeroenwijering.Player(document.getElementById("' + _node.arAttributes["id"] + '"), "/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml", {';

								var arParams = {
									"file" : bxTag.params.file,
									"bufferlength" : "10",
									"width": _node.arAttributes["width"],
									"height": _node.arAttributes["height"],
									"windowless": "true"
								}


								for (i in arParams)
									str += i + ': "' + arParams[i] + '", ';
								str = str.substring(0, str.length - 2);

								str += '});<\/script>';
							}
							return str;
						}

						return false;
					}
					oBXEditorUtils.addUnParser(_mediaUnParser);

	       			var pSaveButton = new BX.CWindowButton({
	           			'title': '<?echo GetMessage("LEARNING_SAVE")?>',
	           			'action': function() {
	           				var path = BX('mediaPath').value;
	           				var width = BX('mediaWidth').value;
	           				var height = BX('mediaHeight').value;

	       					this.parentWindow.Close();
							if (path.length > 0 && parseInt(width) > 0 && parseInt(height) > 0)
							{
		       					if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
		       					{
									var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
									if (bxTag && bxTag.tag && bxTag.tag == "media")
									{
										bxTag.params.file = path;
				       					SAttr(pElement, "width", width);
				       					SAttr(pElement, "height", height);
				       					pElement.style.width = width + "px";
				       					pElement.style.height = height + "px";
									}
		       					}
		       					else
		       					{
			       					var arParams = {file: path};

		       						pEditor.insertHTML('<img id="' + pEditor.SetBxTag(false, {tag: 'media', params: arParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+width+'px; height: '+height+'px;" width="'+width+'" height="'+height+'" />');
		       					}
							}
							pElement = null;
						}
	       			});
	    			var pDialog = new BX.CDialog({
	    				title : '<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>',
	    				content: '<?php echo CUtil::JSEscape(preg_replace("~>\s+<~", "><",  trim($dialogHTML)))?>',
	    				height: 180,
	    				width: 520,
	    				resizable: false,
	    				buttons: [pSaveButton, BX.CDialog.btnClose]
	    			});
					var pMediaButton = [
                       	'BXButton',
                       	{
                       		id : 'media',
                       		src : '/bitrix/images/learning/icons/media.gif',
                       		name : "<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>",
                       		handler : function () {
                       			pDialog.Show();
                       			pEditor = this.pMainObj;
            					BX("OpenFileBrowserWindMedia_button").onclick = OpenFileBrowserWindMedia;

                   				pElement = pEditor.GetSelectionObject();
                   				if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
                       			{
									var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
									if (bxTag && bxTag.tag && bxTag.tag == "media")
									{
	                       				BX('mediaPath').value = bxTag.params.file;
	                   					BX('mediaWidth').value = pElement.getAttribute("width");
	                   					BX('mediaHeight').value = pElement.getAttribute("height");
									}
                   				}
                   				else
                       			{
                       				BX('mediaPath').value = "";
                   					BX('mediaWidth').value = "400";
                   					BX('mediaHeight').value = "300";
                   				}
                       		}
                       	}
                    ];
                    if (window.lightMode)
                    {
                		for(var i = 0, l = arGlobalToolbar.length; i < l ; i++)
                		{
                			var arButton = arGlobalToolbar[i];
                			if (arButton[1] && arButton[1].id == "insert_flash" && arGlobalToolbar[i+1][1].id != "media") {
                				arGlobalToolbar.splice(i + 1, 0, pMediaButton);
                				break;
                			}
                		}
                    }
                    else
                    {
						oBXEditorUtils.appendButton("insert_media", pMediaButton, "standart");
                    }
				</script>
			<?php }?>
			<?php AddEventHandler("fileman", "OnIncludeHTMLEditorScript", "CustomizeEditor"); ?>
			<?php CFileMan::AddHTMLEditorFrame(
				"PREVIEW_TEXT",
				$str_PREVIEW_TEXT,
				"PREVIEW_TEXT_TYPE",
				$str_PREVIEW_TEXT_TYPE,
				300,
				"N",
				0,
				"",
				"",
				false,
				true,
				false,
				array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
			);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td align="center"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>

				<input type="radio" name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>

		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<textarea style="width:100%;height:300px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top" width="50%"><?echo GetMessage("LEARNING_PICTURE")?></td>
		<td width="50%">
			<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
		</td>
	</tr>


<?$tabControl->BeginNextTab();?>
	<!-- <tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_ELEMENT_PREVIEW")?></td>
	</tr> -->
	<tr>
		<td valign="top" width="50%" align="right"><?echo GetMessage("LEARNING_CONTENT_SOURCE")?>:</td>
		<td valign="top" width="50%">
			<label><input onClick="toggleSource()" type="radio" name="CONTENT_SOURCE" value="field"<?php echo $str_DETAIL_TEXT_TYPE!="file" ? " checked" : ""?>>&nbsp;<?echo GetMessage("LEARNING_CONTENT_SOURCE_FIELD")?></label><br />
			<label><input onClick="toggleSource()" type="radio" name="CONTENT_SOURCE" value="file"<?php echo $str_DETAIL_TEXT_TYPE=="file" ? " checked" : ""?>>&nbsp;<?echo GetMessage("LEARNING_CONTENT_SOURCE_FILE")?></label>
		</td>
	</tr>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr id="source_field[0]">
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DETAIL_TEXT",
				$str_DETAIL_TEXT,
				"DETAIL_TEXT_TYPE",
				$str_DETAIL_TEXT_TYPE,
				300,
				"N",
				0,
				"",
				"",
				false,
				true,
				false,
				array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
			);?>
		</td>
	</tr>
	<?else:?>
	<tr id="source_field[0]">
		<td valign="top"><?echo GetMessage("LEARNING_DESC_TYPE")?></td>
		<td valign="top">
				<input type="radio" name="DETAIL_TEXT_TYPE" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="DETAIL_TEXT_TYPE" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr id="source_field[1]">
		<td valign="top" align="center" colspan="2">
			<textarea style="width:100%;height:300px;" name="DETAIL_TEXT" wrap="virtual"><?echo $str_DETAIL_TEXT?></textarea>
		</td>
	</tr>
	<?endif;?>
	<tr id="source_file">
		<td valign="top" align="right"><?echo GetMessage("LEARNING_PATH_TO_FILE")?>:</td>
		<td valign="top">
			<input type="text" name="LAUNCH" size="50" maxlength="255" value="<?echo $str_LAUNCH?>">
		</td>
	</tr>

	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_PICTURE")?></td>
		<td>
			<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>
		</td>
	</tr>
	<script type="text/javascript">toggleSource()</script>

<?$tabControl->Buttons(Array("back_url" =>"/bitrix/admin/learn_lesson_admin.php?lang=". LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID.GetFilterParams("filter_", false)."&filter_chapter_id=".$CHAPTER_ID));?>
<?$tabControl->End();?>

</form>
<?$tabControl->ShowWarnings("lesson_edit", $message);?>

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?else: //if (!$bBadCourse)

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_course_admin.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
	),
);

$context = new CAdminContextMenu($aContext);
$context->Show();


CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));
endif;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>