<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($arResult['ERROR_MESSAGE'])>0):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['ERROR_MESSAGE']?>
		</div>
	</div>
	<?
endif;
if(strlen($arResult['FATAL_MESSAGE'])>0):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	if($arResult['INSERT_IMAGE'] == 'Y'):	
		?>
		<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
		<tr>
			<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_IMAGE_URL')?>:</td>
			<td><input type="text" id="image_url" name="image_url" value="" /></td>
		</tr>
		</table>
		<script type="text/javascript">		
			BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_INSERT_IMAGE')?>');
			var _BTN = [	
				{
					'title': "<?=GetMessage('WIKI_BUTTON_INSERT');?>",
					'id': 'wk_insert',
					'action': function () {
						top.wiki_tag_image(BX('image_url').value); 
						BX.WindowManager.Get().Close();
					}
				},
				BX.CDialog.btnCancel
			];

			getSelectedText();
			BX.WindowManager.Get().ClearButtons();			
			BX.WindowManager.Get().SetButtons(_BTN);       
		</script>		
		<?
		die();	   	
	elseif($arResult['INSERT_CATEGORY'] == 'Y'):	
		?>
		<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
		<tr>
			<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_CATEGORY_NAME')?>:</td>
			<td><input type="text" id="category_name" name="category_name" value="" /></td>
		</tr>
		<?
		if (count($arResult['TREE']) > 1):
		?>
		<tr>
			<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_CATEGORY_SELECT')?>:</td>
			<td>
				<select id="category_select" onchange="if(this.options[this.selectedIndex].value != -1) BX('category_name').value = this.options[this.selectedIndex].value">
				<?
				foreach ($arResult['TREE'] as $key => $value)
				{
				?>				
					<option value="<?=CUtil::JSEscape($key)?>" title="<?=CUtil::JSEscape($value)?>"><?=CUtil::JSEscape($value)?></option>
				<?
				}
				?>				
				</select>
			</td>
		</tr>
		<?
		endif;
		?>		
		</table>
		<script type="text/javascript">
			BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_INSERT_CATEGORY')?>');
			var _BTN = [	
				{
					'title': "<?=GetMessage('WIKI_BUTTON_INSERT');?>",
					'id': 'wk_insert',
					'action': function () {
						top.wiki_tag_category(BX('category_name').value); 
						BX.WindowManager.Get().Close();
					}
				},
				BX.CDialog.btnCancel	
			];

			getSelectedText();
			BX.WindowManager.Get().ClearButtons();			
			BX.WindowManager.Get().SetButtons(_BTN);       
		</script>		
		<?
		die();	   	
	elseif ($arResult['INSERT_LINK'] == 'Y'):
		?>
		<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
		<tr>
        	<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_LINK_URL')?>:</td>
        	<td><input type="text" id="link_url" name="link_url" value="<?=(isset($_REQUEST['external']) ? 'http://' : '')?>" /></td>
		</tr>
		<tr>
			<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_LINK_NAME')?>:</td>
			<td><input type="text" id="link_name" name="link_name" value="" /></td>
		</tr>            
		</table>
		<script type="text/javascript">
			var _bExternal = <?=(isset($_REQUEST['external']) ? 'true' : 'false') ?>;
						
			BX.WindowManager.Get().SetTitle(_bExternal ? '<?=GetMessage('WIKI_INSERT_EXTERANL_HYPERLINK')?>' : '<?=GetMessage("WIKI_INSERT_HYPERLINK")?>');
			var _BTN = [	
				{
					'title': "<?=GetMessage('WIKI_BUTTON_INSERT');?>",
					'id': 'wk_insert',
					'action': function () {
						if (_bExternal)
							top.wiki_tag_url_external(BX('link_url').value, BX('link_name').value); 
						else 
							top.wiki_tag_url(BX('link_url').value, BX('link_name').value); 
						BX.WindowManager.Get().Close();
					}
				},
				BX.CDialog.btnCancel
			];


			BX.WindowManager.Get().ClearButtons();			
			BX.WindowManager.Get().SetButtons(_BTN);    

			var selectedText = false;
			selectedText = getSelectedText();
			if (selectedText)
				BX('link_name').value = selectedText;
         	
		</script>		
		<?
		die();	   	
	elseif($arResult['IMAGE_UPLOAD'] == 'Y'):
		if (!isset($_POST['do_upload'])) :
		?>
			<form action="<?=POST_FORM_ACTION_URI?>" name="load_form" method="post" enctype="multipart/form-data">
			<?=bitrix_sessid_post()?>
			<input type="hidden" name="do_upload" value="1" />
			<input type="hidden" name="image_upload" value="Y" />
			<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
			<tr>
				<td class="bx-width30 bx-popup-label"><?=GetMessage('WIKI_IMAGE')?>:</td>
				<td><?=CFile::InputFile('FILE_ID', 20, 0)?></td>
			</tr>
			</table>
			</form>
			<script type="text/javascript">
				var _BTN = [	
    				BX.CAdminDialog.btnSave,
    				BX.CAdminDialog.btnCancel	
				];         

				getSelectedText();		    
				BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_IMAGE_UPLOAD')?>');	
				BX.WindowManager.Get().SetButtons(_BTN);
			</script>		
		<?		
		elseif(strlen($_POST['do_upload'])>0):		
			?>
			<script type="text/javascript">
			<!--
			<? 
			if(!empty($arResult['IMAGE'])):
			?>

				var my_html = '<div class="wiki-post-image-item"><div class="blog-post-image-item-border"><?=$arResult['IMAGE']['FILE_SHOW']?></div>' +
					'<div class="wiki-post-image-item-input">'+
					'<div><input type="checkbox" name="IMAGE_ID_del[<?=$arResult['IMAGE']['ID']?>]" id="img_del_<?=$arResult['IMAGE']['ID']?>"/> <label for="img_del_<?=$arResult['IMAGE']['ID']?>"><?=GetMessage('WIKI_IMAGE_DELETE')?></label></div></div>';
					
				var imgTable = top.BX('wiki-post-image');

				imgTable.innerHTML += my_html;
				top.arWikiImg[<?=$arResult['IMAGE']['ID']?>] = '<?=CUTIL::JSEscape($arResult['IMAGE']['ORIGINAL_NAME'])?>';
				var pLEditor = top.pLEditorWiki;
				if(pLEditor && top.document.getElementById('wki-text-html').checked)
				{
					var __image = top.BX('<?=$arResult['IMAGE']['ID']?>');
					var imageSrc = top.BX('<?=$arResult['IMAGE']['ID']?>').src;
					if (!__image.naturalWidth)
					{    								
						var _imgStyle = '';
						var lgi = new Image();
						lgi.src = imageSrc;
						var _imgWidth = lgi.width;
					} 
					else
					{
						_imgWidth = __image.naturalWidth;
					}

					if (_imgWidth > <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>)
					{
						_imgStyle += 'width: <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>;';
					}
										
					_str = '<img id="' + pLEditor.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : <?=$arResult['IMAGE']['ID']?>, 'file_name' : '<?=CUTIL::JSEscape($arResult['IMAGE']['ORIGINAL_NAME'])?>'}}) + '"  \
						src="'+imageSrc+'" style="'+_imgStyle+'">';
													
					pLEditor.InsertHTML(_str);
				} 
				else {
					top.doInsert('[File:<?=CUTIL::JSEscape($arResult['IMAGE']['ORIGINAL_NAME'])?>]','',false);
				}

				top.BX.closeWait();
				top.BX.WindowManager.Get().Close();
				<? 			
			else :			
			?>
				top.BX.WindowManager.Get().ShowError('<?=CUtil::JSEscape($arResult['ERROR_MESSAGE'])?>');                
			<? 
			endif;
			?>
			//-->
			</script>
			<?
		endif;
		die();	
	elseif($arResult['LOAD_EDITOR'] == 'Y'):

		if(CModule::IncludeModule('fileman')):				
			AddEventHandler('fileman', 'OnIncludeLightEditorScript', 'CustomizeLightEditorForWiki');	
			function CustomizeLightEditorForWiki()
			{
				?>
				<script>
				window.LHEButtons['Category'] = {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/category.gif',
					id : 'Category',
					name : '<?=GetMessage('WIKI_BUTTON_CATEGORY')?>',
					title : '<?=GetMessage('WIKI_BUTTON_CATEGORY')?>',
					handler : function (p)
					{
						this.bNotFocus = true;             						
						ShowCategoryInsert();										
					}
				};
				window.LHEButtons['ImageUpload'] = {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/image.gif',
					id : 'ImageUpload',
					name : '<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>',
					title : '<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>',
					handler : function (p)
					{
						this.bNotFocus = true;            						
						ShowImageUpload();
					}
				};         					
				window.LHEButtons['ImageLink'] = {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/image_upload.gif',
					id : 'ImageLink',
					name : '<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>',
					title : '<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>',
					handler : function (p)
					{
						this.bNotFocus = true;            						
						ShowImageInsert();
					},
					parser : {
						name: 'wiki_img',
						obj: {
							Parse: function(sName, sContent, pLEditor)
							{
								sContent = sContent.replace(/\[?\[((File|<?=GetMessage('FILE_NAME');?>):(.+?))\]\]?/ig, function(s, s1, s2, f) 
								{
									var imageSrc = false;
									var _imgStyle = '';
									var id = 0;
									if (f.indexOf('http://') == 0)
										imageSrc = f;
									else  
									{
										if (isFinite(f) && BX(f))
										{
											id = f;
											imageSrc = BX(f).src;
										}
										else 
										{
											for (var i in arWikiImg)
											{
    											if (arWikiImg[i] == f)
    											{ 
    												id = i;
    												imageSrc = BX(id).src;
    												break;
    											}
											}											
										}
										if (!imageSrc) 
											return s;

										var lgi = new Image();
										lgi.src = imageSrc;
										var _imgWidth = lgi.width;

										if (_imgWidth > <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>)
											_imgStyle += 'width: <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>;';

									}
									
									if (imageSrc)
										return  '<img id="' + pLEditor.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : id, 'file_name' : f}}) + '" \
											src="'+imageSrc+'" style="'+_imgStyle+'" />';
									else 
										return s; 
								});
								return sContent;        									     									      
						
							},
							UnParse: function(bxTag, pNode, pLEditor)
							{
								if (bxTag && bxTag.tag && bxTag.tag == "wiki_img")
								{					
									return '[<?=GetMessage('FILE_NAME');?>:'+bxTag.params.file_name+']';
								} 
								return '';                   								               						
							}         							
						}
					}
				};
				window.LHEButtons['Signature']	= {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/signature.gif',
					id : 'Signature',
					name : '<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>',
					title : '<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>',
					handler : function (p)
					{      								
						wiki_signature();
					}
				};      						    						

				LHEButtons['Header'] = {
						id : 'Header',
						src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/header.gif',
						type: 'List',
						name : '<?=GetMessage('WIKI_BUTTON_WHEADER')?>',
						title : '<?=GetMessage('WIKI_BUTTON_WHEADER')?>',						
						handler: function() {},
						OnCreate: function(pList)
						{
							var
								pIt, pItem, i, oItem;

							pList.arItems = [
								{value: 'h1', name: LHE_MESS.Heading + ' 1'},
								{value: 'h2', name: LHE_MESS.Heading + ' 2'},
								{value: 'h3', name: LHE_MESS.Heading + ' 3'},
								{value: 'h4', name: LHE_MESS.Heading + ' 4'},
								{value: 'h5', name: LHE_MESS.Heading + ' 5'},
								{value: 'h6', name: LHE_MESS.Heading + ' 6'}
							];

							var innerCont = BX.create("DIV", {props: {className: 'lhe-header-innercont'}});

							for (i = 0; i < pList.arItems.length; i++)
							{
								oItem = pList.arItems[i];
								if (typeof oItem != 'object' || !oItem.name)
									continue;

								pItem = BX.create("DIV", {props: {className: 'lhe-header-cont', title: oItem.name, id: 'lhe_header__' + i}});
								pItem.appendChild(BX.create(oItem.value.toUpperCase(), {text: oItem.name}));

								pItem.onclick = function(){pList.oBut.Select(pList.arItems[this.id.substring('lhe_header__'.length)], pList);};
								pItem.onmouseover = function(){this.className = 'lhe-header-cont lhe-header-cont-over';};
								pItem.onmouseout = function(){this.className = 'lhe-header-cont';};

								oItem.pWnd = innerCont.appendChild(pItem);
							}
							pList.pValuesCont.appendChild(innerCont);
						},
						OnOpen: function(pList)
						{
							var
								frm = pList.pLEditor.queryCommand('FormatBlock'),
								i, v;

							if (pList.pSelectedItemId >= 0)
								pList.SelectItem(false);

							if (!frm)
								frm = 'h1';
							
							for (i = 0; i < pList.arItems.length; i++)
							{
								v = pList.arItems[i];
								if (v.value == frm)
								{
									pList.pSelectedItemId = i;
									pList.SelectItem(true);
								}
							}
						},
						Select: function(oItem, pList)
						{
							var selectedText = getSelectedText();
							if (selectedText != '')
							{
								pList.pLEditor.SelectRange(pList.pLEditor.oPrevRange);
								pList.pLEditor.executeCommand('FormatBlock', '<' + oItem.value + '>');
								pList.Close();
							}
							else
							{								
								pList.pLEditor.InsertHTML('<' + oItem.value + '>' + 'HEADER' + '</' + oItem.value + '>');
							}
						}
					};
				
				window.LHEButtons['intenalLink'] = {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/link.gif',
					id : 'intenalLink',
					name : '<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>',
					title : '<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>',
					handler : function (p)
					{
						this.bNotFocus = true;            						
						ShowInsertLink();											
					}
				}; 
				window.LHEButtons['nowiki'] = {
					src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/nowiki.gif',
					id : 'nowiki',
					name : '<?=GetMessage('WIKI_BUTTON_NOWIKI')?>',
					title : '<?=GetMessage('WIKI_BUTTON_NOWIKI')?>',
					handler : function (p)
					{
						var
							pElement = p.pLEditor.GetSelectionObjects(true),
							bFind = false, st;

						while(!bFind)
						{
							if (!pElement)
								break;
							
							if (pElement.nodeType == 1)
							{ 
								var bxTag = p.pLEditor.GetBxTag(pElement.id);
								if (bxTag && bxTag.tag && bxTag.tag == "wiki_no") 		 								
									bFind = true;
								else 
									pElement = pElement.parentNode;
							}
							else
								pElement = pElement.parentNode;
						}

						if (bFind)
						{
							pElement.style.border = "";
							p.pLEditor.RidOfNode(pElement, true);
							this.Check(false);
						}
						else
						{
							p.pLEditor.WrapSelectionWith("span", {props:{id: p.pLEditor.SetBxTag(false, {'tag': 'wiki_no', 'params': {}})}, 
																	style: {border : "1px dashed grey"}});
							//p.pLEditor.OnEvent("OnSelectionChange");
						}
					},
					OnSelectionChange: function (p) // ???
					{
						var
							pElement = p.pLEditor.GetSelectedNode(true),
							bFind = false, st;

						while(!bFind)
						{
							if (!pElement)
								break;
							
							if (pElement.nodeType == 1)
							{
								var bxTag = this.pMainObj.GetBxTag(pElement.id);
								if (bxTag && bxTag.tag && bxTag.tag == "wiki_no")
								{ 		 								
									bFind = true;
									break;
								}
								else 
									pElement = pElement.parentNode; 
							}
							else
								pElement = pElement.parentNode;
						}

						this.Check(bFind);
					},
					parser : {
						name: 'wiki_no',
						obj: {
							Parse: function(sName, sContent, pLEditor)
							{
								sContent = sContent.replace(/<nowiki>(.*?)<\/nowiki>/igm, '<span style="border: 1px dashed grey" id="' + pLEditor.SetBxTag(false, {'tag': 'wiki_no', 'params': {}}) + '" >$1</span>');
								return sContent;        									      
							},
							UnParse: function(bxTag, pNode, pLEditor)
							{

								if (bxTag && bxTag.tag && bxTag.tag == "wiki_no")
								{
									var res = "", i;          									
									for (var i = 0; i < pNode.arNodes.length; i++)             									        
									res += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);
									
									
									return '<NOWIKI>'+res+'</NOWIKI>';
								}
								return '';                						
							}
						}
					}
				}        					     						       					

				</script>
				<? 
			}			
			?>
			<script>
			BX.addCustomEvent('LHE_OnInit', setEditorContentAfterLoad);
			</script>
			<?
			$ar = array(
				'width' => '100%',
				'height' => '300',
				'inputName' => 'POST_MESSAGE_HTML',
				'inputId' => 'POST_MESSAGE_HTML',
				'jsObjName' => 'pLEditorWiki',
				'content' => $arResult["ELEMENT"]["~DETAIL_TEXT"],
				'bUseFileDialogs' => false,
				'bFloatingToolbar' => false,
				'bArisingToolbar' => false,
				'bResizable' => true,
				'bSaveOnBlur' => true,
				'toolbarConfig' => array(    
					'Bold', 'Italic', 'Underline', /*'RemoveFormat',*/
					'Header', 'intenalLink', 'Category',
					'Signature', 'nowiki', 'CreateLink', 'DeleteLink', 'ImageLink', 'ImageUpload', 'Table',
					'BackColor', 'ForeColor',
					'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
					'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent'
				)
			);
			$LHE = new CLightHTMLEditor;
			$LHE->Show($ar);			
					
		else:		
			ShowError(GetMessage('FILEMAN_MODULE_NOT_INSTALLED'));
		endif;
		die();	
	elseif ($arResult['WIKI_oper'] == 'delete'):	
		?>
		<form action="<?=$arResult['PATH_TO_DELETE']?>" name="load_form" method="post">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="<?=$arResult['PAGE_VAR']?>" value="<?=$arResult['ELEMENT']['ID']?>"/>		
		<input type="hidden" name="<?=$arResult['OPER_VAR']?>" value="delete"/>
		<input type="hidden" name="save" value="Y"/>
		<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
		<tr>
			<td><?=GetMessage('WIKI_DELETE_PAGE')?></td>
		</tr>
		</form>
		<script type="text/javascript">
			BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_DELETE_CONFIRM')?>');
			var _BTN = [	
				{
					'title': "<?=GetMessage('WIKI_BUTTON_DELETE');?>",
					'id': 'wk_delete',
					'action': function () {
						document.forms.load_form.submit();
						BX.WindowManager.Get().Close();
					}
				},
				BX.CDialog.btnCancel	
			];

			BX.WindowManager.Get().ClearButtons();			
			BX.WindowManager.Get().SetButtons(_BTN);       
		</script>		
		<?
		die();	    	
	else:	   
		include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/script.php');
		?>
		<div id="wiki-post">
			<div id="wiki-post-content">
				<div class="wiki-post-title">
					<div class="wiki-post-title-text">
					<h1><?=$arResult['ELEMENT']['NAME_LOCALIZE']?></h1>
					</div>
				</div>			
				<?		
				if($arResult['PREVIEW'] == 'Y' && !empty($arResult['ELEMENT_PREVIEW'])):		
				?>			
					<div class="wiki-prereview-header">
						<div class="wiki-prereview-header-title"><span><?=GetMessage('WIKI_PREVIEW_TITLE')?></span></div>
					</div>
					<div class="wiki-prereview-post-content">
						<div class="wiki-prereview-post-text"><?=$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT']?></div>
					</div>			
				<? 	
				endif;?>						
				<form action="<?=$arResult['PATH_TO_POST_EDIT']?>" name="REPLIER" method="post" >
				<?=bitrix_sessid_post();?>		
				<div class="wiki-post-fields">
					<div class="wiki-post-header">
						<?=GetMessage('WIKI_NAME')?><font color="#ff0000">*</font>
					</div>
					<div class="wiki-post-area">	
						<input maxlength="255" size="70" tabindex="1" type="text" name="POST_TITLE" id="POST_TITLE" value="<?=$arResult['ELEMENT']['NAME']?>"/>
					</div>
	
					<div class="wiki-post-header"><?=GetMessage('WIKI_PAGE_TEXT')?><font color="#ff0000">*</font></div>		
							
					<div class="wiki-post-area">
						<div class="wiki-post-textarea">
						<?				
						if($arResult['ALLOW_HTML'] == 'Y'):			
						?>
							<input type="radio" id="wki-text-text" name="POST_MESSAGE_TYPE" value="text"<?if($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] != 'html') echo " checked";?> onclick="showEditField('text', 'Y', 'Y')"/> <label for="wki-text-text"><?=GetMessage('WIKI_TEXT_TEXT')?></label> <input type="radio" id="wki-text-html" name="POST_MESSAGE_TYPE" value="html"<?if($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] == 'html') echo " checked";?> onclick="showEditField('html', 'Y', 'Y')"/> <label for="wki-text-html"><?=GetMessage('WIKI_TEXT_HTML')?></label>
							<input type="hidden" name="editor_loaded" id="editor_loaded" value="N"/>
							<div id="edit-post-html" style="display:none;"></div>
						<?
						endif;
						?>
						<div id="edit-post-text"  style="display:none;">
							<div class="wiki-post-wcode-line">
								<div class="wiki-wcode-line">
									<a id="bold" class="wiki-wcode-bold" href="javascript:wiki_bold()" title="<?=GetMessage('WIKI_BUTTON_BOLD')?>"></a>
									<a id="italic" class="wiki-wcode-italic" href="javascript:wiki_italic()" title="<?=GetMessage('WIKI_BUTTON_ITALIC')?>"></a>
									<a id="wheader" class="wiki-wcode-wheader" href="javascript:wiki_header()" title="<?=GetMessage('WIKI_BUTTON_HEADER')?>"></a>
									<a id="category" class="wiki-wcode-category" href="javascript:ShowCategoryInsert()" title="<?=GetMessage('WIKI_BUTTON_CATEGORY')?>"></a>
									<a id="url" class="wiki-wcode-url" href="javascript:ShowInsertLink(false)" title="<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>"></a>
									<a id="signature" class="wiki-wcode-signature" href="javascript:wiki_signature()" title="<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>"></a>
									<a id="line" class="wiki-wcode-line" href="javascript:wiki_line()"  title="<?=GetMessage('WIKI_BUTTON_LINE')?>"></a>
									<a id="ignore" class="wiki-wcode-ignore" href="javascript:wiki_nowiki()" title="<?=GetMessage('WIKI_BUTTON_NOWIKI')?>"></a>
									<a id="url" class="wiki-wcode-external-url" href="javascript:ShowInsertLink(true)" title="<?=GetMessage('WIKI_BUTTON_EXTERNAL_HYPERLINK')?>"></a>
									<a id="image" class="wiki-wcode-img" href="javascript:ShowImageInsert()" title="<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>"></a>
									<a id="image-upload" class="wiki-wcode-img-upload" href="javascript:ShowImageUpload()" title="<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>"></a>																
									<div class="wiki-clear-float"></div>
								</div>				
								<div class="wiki-clear-float"></div>
							</div>
							<div class="wiki-comment-field wiki-comment-field-text">
								<textarea cols="55" rows="15" tabindex="2" onKeyPress="check_ctrl_enter(arguments[0])" name="POST_MESSAGE" id="MESSAGE" onKeyPress="check_ctrl_enter(arguments[0])"><?=$arResult["ELEMENT"]["~DETAIL_TEXT"]?></textarea>
							</div>
						</div>	
						<? 
		    
						if($arResult['ALLOW_HTML'] == 'Y'):			
						?>
							<script type="text/javascript" src="/bitrix/js/main/ajax.js"></script>
							<script type="text/javascript" src="/bitrix/js/main/admin_tools.js"></script>
							<script type="text/javascript" src="/bitrix/js/main/utils.js"></script>
							<?
							$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/pubstyles.css');
							$APPLICATION->SetAdditionalCSS('/bitrix/admin/htmleditor2/editor.css');
							$APPLICATION->SetTemplateCSS('ajax/ajax.css');
						endif;					
				
						if($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] == 'html' && $arResult['ALLOW_HTML'] == 'Y'):
							?>
							<script>
							<!--
								setTimeout("showEditField('html', 'N', 'N')", 100);
							//-->
							</script>
							<?
						else:
							?>
							<script>
							<!--
								showEditField('text', 'N', 'Y');
							//-->
							</script>
							<?
						endif;						
						?>				
						</div>				
						<div class="wiki-post-image" id="wiki-post-image">
						<?
						if (!empty($arResult['IMAGES'])):			
							?>
							<div><?=GetMessage('WIKI_IMAGES')?></div>
							<?
							foreach($arResult['IMAGES'] as $aImg)
							{
								?>
								<div class="wiki-post-image-item">
									<div class="wiki-post-image-item-border"><?=$aImg['FILE_SHOW']?></div>
									<div>
										<input type="checkbox" name="IMAGE_ID_del[<?=$aImg['ID']?>]" id="img_del_<?=$aImg['ID']?>"/> <label for="img_del_<?=$aImg['ID']?>"><?=GetMessage('IKI_IMAGE_DELETE')?></label>
									</div>							
								</div>
								<?
							}
							?>
							<script type="text/javascript">							
							<?
							reset($arResult['IMAGES']);
							foreach($arResult['IMAGES'] as $aImg)
							{
								?>
								arWikiImg[<?=$aImg['ID']?>] = '<?=CUtil::JSEscape($aImg['ORIGINAL_NAME'])?>';
								<?
							}
							?>    							
							</script>
							<?
						endif;
						?>
						</div>
					</div>
					<div class="wiki-clear-float"></div>
					<? if ($arResult['SOCNET'] == false ) : ?>
						<div class="wiki-post-header">
							<?=GetMessage('WIKI_TAGS')?>
						</div>
						<div class="wiki-post-area">	
							<span><?
							if(IsModuleInstalled('search')):								
								$arSParams = Array(
									'NAME'	=>	'TAGS',
									'VALUE'	=>	$arResult['ELEMENT']['~TAGS'],
									'arrFILTER'	=>	'wiki',
									'PAGE_ELEMENTS'	=>	'10',
									'SORT_BY_CNT'	=>	'Y',
									'TEXT' => 'size="30" tabindex="3"'
								);

								$APPLICATION->IncludeComponent('bitrix:search.tags.input', '.default', $arSParams);
							else:
								?><input type="text" class="wiki-input" id="TAGS" tabindex="3" name="TAGS" size="30" value="<?=$arResult['ELEMENT']['~TAGS']?>"/><?
							endif?>
						</span>
						</div>
						<div class="wiki-clear-float"></div>
					<?
					endif; 
					?>
					<div class="wiki-post-buttons wiki-edit-buttons">
						<input type="hidden" name="<?=$arResult['PAGE_VAR']?>" value="<?=$arResult['ELEMENT']['NAME']?>"/>		
						<input type="hidden" name="<?=$arResult['OPER_VAR']?>" value="<?=$arResult['WIKI_oper']?>"/>
						<input type="hidden" name="save" value="Y"/>
						<input tabindex="4" type="submit" name="save" value="<?=GetMessage($arResult['WIKI_oper'] == 'add' || $arResult['WIKI_oper'] == 'edit' ? 'WIKI_PUBLISH' : 'WIKI_SAVE')?>"/>
						<? if ($arResult['WIKI_oper'] == 'edit' || $arResult['WIKI_oper'] == 'add'): ?>
						<input type="submit" name="apply" value="<?=GetMessage('WIKI_APPLY')?>"/>
						<input type="submit" name="preview" value="<?=GetMessage('WIKI_PREVIEW')?>"/>
						<? endif; ?>
					</div>
				</div>
				</form>
		
				<div class="wiki-post-note">
				<?
				if ($arResult['WIKI_oper'] != 'delete')
					echo GetMessage('WIKI_REQUIED_FIELDS_NOTE')
				?>
				</div>
			</div>
		</div>
		<?
		
	endif;	
endif;
?>
