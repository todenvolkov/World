BX.CLBlock = function(arParams) 
{
	this.arData = new Array();
	this.arData["Subscription"] = new Array();
	this.arData["Transport"] = new Array();	
	this.arData["Visible"] = new Array();
}

BX.CLBlock.prototype.DataParser = function(str)
{
	str = str.replace(/^\s+|\s+$/g, '');
	while (str.length > 0 && str.charCodeAt(0) == 65279)
		str = str.substring(1);

	if (str.length <= 0)
		return false;
	
	if (str.substring(0, 1) != '{' && str.substring(0, 1) != '[' && str.substring(0, 1) != '*')
		str = '"*"';
		
	eval("arData = " + str);

	return arData;
}

BX.CLBlock.prototype.ShowContent = function(entity_type, entity_id, event_id, cb_id, ind)
{
	node_code = entity_type + '_' + entity_id + '_' + event_id + '_' + ind;

	if (this.arData["Subscription"][node_code].length <= 0)
		return BX.create('DIV', {
				props: {},
				html: BX.message('sonetLNoSubscriptions')
			});

	var div = BX.create('DIV', {	
		props: {
			'className': 'popup-window-content-div'
		}
	} );

	var form = div.appendChild(BX.create('form', {	
	} ));

	var table = form.appendChild(BX.create('table', {}));
	
	var tbody = table.appendChild(BX.create('tbody', {
		props: {},
		children: [
			BX.create('tr', {
				props: {},
				children: [
					BX.create('td', {
						props: {}
					})
				]
			})
		]
	}));	
	
	select = tbody.firstChild.firstChild.appendChild(BX.create('select', {
		props: {
			'bx_node_code':	node_code,
			'id':			'ls_' + ind
		},
		events: {
			'change': BX.delegate(this.RecalcRadio, this)
		}
	}));

	if (
		typeof this.arData["Subscription"][node_code]["EVENT"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["EVENT"]["TITLE"] != 'undefined'
	)
	{
		select.appendChild(BX.create('option', {
				props: {
					'value': 'EVENT',
					'selected': false,
					'defaultSelected': false
				},
				html: this.arData["Subscription"][node_code]["EVENT"]["TITLE"]
		}));
	}
	
	if (
		typeof this.arData["Subscription"][node_code]["ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["ALL"]["TITLE"] != 'undefined'
	)
	{
		select.appendChild(BX.create('option', {
				props: {
					'value': 'ALL',
					'selected': false,
					'defaultSelected': false
				},
				html: this.arData["Subscription"][node_code]["ALL"]["TITLE"]
		}));	
	}	

	if (
		typeof this.arData["Subscription"][node_code]["CB_ALL"] != 'undefined'
		&& typeof this.arData["Subscription"][node_code]["CB_ALL"]["TITLE"] != 'undefined'
	)
	{
		select.appendChild(BX.create('option', {
				props: {
					'value': 'CB_ALL',
					'selected': false,
					'defaultSelected': false
				},
				html: this.arData["Subscription"][node_code]["CB_ALL"]["TITLE"]
		}));	
	}
	
	var tr = tbody.appendChild(BX.create('tr', {
		props: {},
		children: [
			BX.create('td', {
				props: {}
			})
		]
	}));

/* visible */
	
	divVisibleInherited = tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('DIV', {
		props: {
			'id': 'v_ld_' + node_code + '_I'
		}
	}));

	radio = divVisibleInherited.appendChild(BX.create('INPUT', {
		props: {
			'type': 			'radio',
			'name': 			'v_lr_' + node_code,
			'value': 			'I',
			'id': 				'v_lr_' + node_code + '_I',
			'bx_entity_type':	entity_type,
			'bx_entity_id':		entity_id,
			'bx_event_id':		event_id,
			'bx_cb_id':			cb_id,
			'bx_ls_id':			'ls_' + ind,
			'bx_node_code':		node_code,
			'bx_site_id':		this.arData["Subscription"][node_code]["SITE_ID"]
		},
		events: {
			'click': BX.delegate(this.SetSubscription, this)
		}
	}));

	if (this.arData["Subscription"][node_code]["EVENT"]["VISIBLE_INHERITED"])
	{
		radio.checked = true;
		divVisibleInherited.style.block = 'block';
	}
	else
	{
		radio.checked = false;
		divVisibleInherited.style.display = 'none';
	}

	for (var i = 0; i < this.arData["Visible"].length; i++)
	{
		if (this.arData["Visible"][i]["Key"] == this.arData["Subscription"][node_code]["EVENT"]["VISIBLE"])
		{
			InheritedName = this.arData["Visible"][i]["Value"];
			break;
		}
	}

	label = divVisibleInherited.appendChild(BX.create('LABEL', {
		props: {
			'id': 'v_ll_' + node_code + '_I'
		},	
		attrs: {
			'for': 'v_lr_' + node_code + '_I'
		},
		html: BX.message('sonetLInherited') + ' (' + InheritedName + ')'
	}));	
		
	for (var i = 0; i < this.arData["Visible"].length; i++)
	{
		radio = tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('INPUT', {
			props: {
				'type':				'radio',
				'name':				'v_lr_' + node_code,
				'value':			this.arData["Visible"][i]["Key"],
				'id':				'v_lr_' + node_code + '_' + this.arData["Visible"][i]["Key"],
				'bx_entity_type':	entity_type,
				'bx_entity_id':		entity_id,
				'bx_event_id':		event_id,
				'bx_cb_id':			cb_id,
				'bx_ls_id':			'ls_' + ind,
				'bx_site_id':		this.arData["Subscription"][node_code]["SITE_ID"],
				'bx_node_code':		node_code
			},
			events: {
				'click': BX.delegate(this.SetSubscription, this)
			}
		}));

		if (
			!this.arData["Subscription"][node_code]["EVENT"]["VISIBLE_INHERITED"]
			&& this.arData["Subscription"][node_code]["EVENT"]["VISIBLE"] == this.arData["Visible"][i]["Key"]
		)
			radio.checked = true;

		label = tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('LABEL', {
			attrs: {
				'for': 'v_lr_' + node_code + '_' + this.arData["Visible"][i]["Key"]
			},
			html: this.arData["Visible"][i]["Value"]
		}));
		
		label.parentNode.appendChild(BX.create('BR', {}));
	}

/* -- visible */

	tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('BR', {}));	
	tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('DIV', {
		props: {
			'className': 'sonet-log-transport-title'
		},
		html: BX.message('sonetLTransportTitle')
	}));		
	
/* transport */

	divTransportInherited = tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('DIV', {
		props: {
			'id': 't_ld_' + node_code + '_I'
		}
	}));

	for (var i = 0; i < this.arData["Transport"].length; i++)
	{
		if (this.arData["Transport"][i]["Key"] == this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT"])
		{
			InheritedName = this.arData["Transport"][i]["Value"];
			break;
		}
	}

	label = divTransportInherited.appendChild(BX.create('LABEL', {
		props: {
			'id': 't_ll_' + node_code + '_I'
		},	
		children: [
			BX.create('span', {
				props: {
					'className': 'sonet-log-transport sonet-log-transport-' + this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT"]
				}
			})
		]
		
	}));	

	radio = label.appendChild(BX.create('INPUT', {
		props: {
			'type': 			'radio',
			'name': 			't_lr_' + node_code,
			'value': 			'I',
			'id': 				't_lr_' + node_code + '_I',
			'bx_entity_type':	entity_type,
			'bx_entity_id':		entity_id,
			'bx_event_id':		event_id,
			'bx_cb_id':			cb_id,
			'bx_ls_id':			'ls_' + ind,
			'bx_node_code':		node_code,
			'bx_site_id':		this.arData["Subscription"][node_code]["SITE_ID"]
		},
		events: {
			'click': BX.delegate(this.SetSubscription, this)
		}
	}));

	if (this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT_INHERITED"])
	{
		radio.checked = true;
		divTransportInherited.style.block = 'block';
	}
	else
	{
		radio.checked = false;
		divTransportInherited.style.display = 'none';
	}
	

	label.appendChild(BX.create('span', {
				props: {
					'className': 'sonet-log-transport-label'
				},
				html: BX.message('sonetLInherited') + ' (' + InheritedName + ')'
			}));
	
/* all transports */

	for (var i = 0; i < this.arData["Transport"].length; i++)
	{

		label = tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('LABEL', {
			attrs: {
				'for': 't_lr_' + node_code + '_' + this.arData["Transport"][i]["Key"]
			},
			children: [
				BX.create('span', {
					props: {
						'className': 'sonet-log-transport sonet-log-transport-' + this.arData["Transport"][i]["Key"]
					}
				})
			]
		}));
		
		radio = label.appendChild(BX.create('INPUT', {
			props: {
				'type':				'radio',
				'name':				't_lr_' + node_code,
				'value':			this.arData["Transport"][i]["Key"],
				'id':				't_lr_' + node_code + '_' + this.arData["Transport"][i]["Key"],
				'bx_entity_type':	entity_type,
				'bx_entity_id':		entity_id,
				'bx_event_id':		event_id,
				'bx_cb_id':			cb_id,
				'bx_ls_id':			'ls_' + ind,
				'bx_node_code':		node_code,
				'bx_site_id':		this.arData["Subscription"][node_code]["SITE_ID"]
			},
			events: {
				'click': BX.delegate(this.SetSubscription, this)
			}
		}));

		if (
			!this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT_INHERITED"]
			&& this.arData["Subscription"][node_code]["EVENT"]["TRANSPORT"] == this.arData["Transport"][i]["Key"]
		)
			radio.checked = true;

		label.appendChild(BX.create('span', {
				props: {
					'className': 'sonet-log-transport-label'
				},
				html: this.arData["Transport"][i]["Value"]
			}));
					
		label.appendChild(BX.create('BR', {}));
	}

/* -- transport */

	tbody.firstChild.nextSibling.firstChild.appendChild(BX.create('BR', {}));

	return div;

}

BX.CLBlock.prototype.RecalcRadio = function()
{
	var ob = BX.proxy_context;
	var val = ob.value;
	var node_code = ob.bx_node_code;

	if (this.arData["Subscription"][node_code][val]["TRANSPORT_INHERITED"])
	{
		for (var i = 0; i < this.arData["Transport"].length; i++)
		{
			if (this.arData["Transport"][i]["Key"] == this.arData["Subscription"][node_code][val]["TRANSPORT"])
			{
				InheritedName = this.arData["Transport"][i]["Value"];
				break;
			}
		}	
	
		BX('t_ll_' + node_code + '_I').firstChild.className = 'sonet-log-transport sonet-log-transport-' + this.arData["Subscription"][node_code][val]["TRANSPORT"];
		BX('t_ll_' + node_code + '_I').firstChild.nextSibling.nextSibling.innerHTML = BX.message('sonetLInherited') + ' (' + InheritedName + ')';
		BX('t_ld_' + node_code + '_I').style.display = 'block';		
		
		BX('t_lr_' + node_code + '_I').checked = true;
		
		for (var i = 0; i < this.arData["Transport"].length; i++)
			BX('t_lr_' + node_code + '_' + this.arData["Transport"][i]["Key"]).checked = false;
	}
	else
	{
		BX('t_ld_' + node_code + '_I').style.display = 'none';		
		BX('t_lr_' + node_code + '_' + this.arData["Subscription"][node_code][val]["TRANSPORT"]).checked = true;
	}
	
	if (this.arData["Subscription"][node_code][val]["VISIBLE_INHERITED"])
	{
		for (var i = 0; i < this.arData["Visible"].length; i++)
		{
			if (this.arData["Visible"][i]["Key"] == this.arData["Subscription"][node_code][val]["VISIBLE"])
			{
				InheritedName = this.arData["Visible"][i]["Value"];
				break;
			}
		}	
	
		BX('v_ld_' + node_code + '_I').style.display = 'block';
		BX('v_ll_' + node_code + '_I').innerHTML = BX.message('sonetLInherited') + ' (' + InheritedName + ')';
		BX('v_lr_' + node_code + '_I').checked = true;

		for (var i = 0; i < this.arData["Visible"].length; i++)
			BX('v_lr_' + node_code + '_' + this.arData["Visible"][i]["Key"]).checked = false;
	}
	else
	{
		BX('v_ld_' + node_code + '_I').style.display = 'none';		
		BX('v_lr_' + node_code + '_' + this.arData["Subscription"][node_code][val]["VISIBLE"]).checked = true;
	}	
}

BX.CLBlock.prototype.SetSubscription = function()
{
	var ob = BX.proxy_context;

	if (
		ob == null 
		|| ob.bx_ls_id == null
		|| BX(ob.bx_ls_id) == null
	)
		return;

		
	var node_code = ob.bx_node_code;
	var ls = BX(ob.bx_ls_id).value;
	
	if (ob.name.indexOf('t_') === 0)
	{
		this.arData["Subscription"][node_code][ls]["TRANSPORT"] = ob.value;
		if (ob.value == 'I')
			this.arData["Subscription"][node_code][ls]["TRANSPORT_INHERITED"] = true;
		else
			this.arData["Subscription"][node_code][ls]["TRANSPORT_INHERITED"] = false;
	}
	else if (ob.name.indexOf('v_') === 0)
	{
		this.arData["Subscription"][node_code][ls]["VISIBLE"] = ob.value;
		if (ob.value == 'I')
			this.arData["Subscription"][node_code][ls]["VISIBLE_INHERITED"] = true;
		else
			this.arData["Subscription"][node_code][ls]["VISIBLE_INHERITED"] = false;
	}
		
	var site = ob.bx_site_id;

	if (sonetLXmlHttpSet.readyState % 4)
		return;
	
	if (sonetEventsErrorDiv != null)
		sonetEventsErrorDiv.style.display = "none";

	params = 'entity_type=' + ob.bx_entity_type + '&entity_id=' + ob.bx_entity_id + '&event_id=' + ob.bx_event_id + '&cb_id=' + ob.bx_cb_id + '&ls=' + ls + '&action=set';

	if (ob.name.indexOf('t_') === 0)
		params += '&transport=' + ob.value;
	else if (ob.name.indexOf('v_') === 0)
		params += '&visible=' + ob.value;		

	if (site.length > 0)
		params += '&site=' + site;
		
	sonetLXmlHttpSet.open(
		"get",
		BX.message('sonetLSetPath') + "?" + BX.message('sonetLSessid')
			+ "&" + params
			+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetLXmlHttpSet.send(null);

	sonetLXmlHttpSet.onreadystatechange = function()
	{
		if (sonetLXmlHttpSet.readyState == 4 && sonetLXmlHttpSet.status == 200)
		{
			if (sonetLXmlHttpSet.responseText)
			{
				if (sonetEventsErrorDiv != null)
				{
					sonetEventsErrorDiv.style.display = "block";
					sonetEventsErrorDiv.innerHTML = sonetEventXmlHttpSet.responseText;
				}
			}
		}
	}
}
	
__logSwitchBody = function(ind)
{
	if (BX('sonet_log_message_full_' + ind))
	{
		if (BX.hasClass(BX('sonet_log_message_full_' + ind), 'sonet-log-message-hide'))
		{
			BX.removeClass(BX('sonet_log_message_full_' + ind), 'sonet-log-message-hide');
			BX.addClass(BX('sonet_log_message_full_' + ind), 'sonet-log-message-show');
			BX.removeClass(BX('sonet_log_message_short_' + ind), 'sonet-log-message-show');
			BX.addClass(BX('sonet_log_message_short_' + ind), 'sonet-log-message-hide');
			BX('sonet_log_message_switch_show_' + ind).style.display = 'none';
			BX('sonet_log_message_switch_hide_' + ind).style.display = 'inline-block';
		}
		else
		{
			BX.removeClass(BX('sonet_log_message_full_' + ind), 'sonet-log-message-show');
			BX.addClass(BX('sonet_log_message_full_' + ind), 'sonet-log-message-hide');
			BX.removeClass(BX('sonet_log_message_short_' + ind), 'sonet-log-message-hide');
			BX.addClass(BX('sonet_log_message_short_' + ind), 'sonet-log-message-show');
			BX('sonet_log_message_switch_show_' + ind).style.display = 'inline-block';
			BX('sonet_log_message_switch_hide_' + ind).style.display = 'none';
		}
	}
}

function __logFilterClick(featureId)
{
	var chkbx = document.getElementById("flt_event_id_"+featureId);
	var chkbx_tmp = false;

	var bIsAllChecked = true;
	
	for(var flt_cnt in arFltFeaturesID)
	{
		chkbx_tmp = document.getElementById("flt_event_id_"+arFltFeaturesID[flt_cnt]);
		if (null != chkbx_tmp)
		{
			if (chkbx_tmp.checked == false)
			{
				bIsAllChecked = false;
				break;
			}
		}
	}

	chkbx_tmp = document.getElementById("flt_event_id_all");	
	if (bIsAllChecked)
		chkbx_tmp.value = "Y";
	else
		chkbx_tmp.value = "";	
}

function __logFilterShow()
{
	if (BX('bx_sl_filter').style.display == 'none')
	{
		BX('bx_sl_filter').style.display = 'block';
		BX('bx_sl_filter_hidden').style.display = 'none';
	}
	else
	{
		BX('bx_sl_filter').style.display = 'none';
		BX('bx_sl_filter_hidden').style.display = 'block';
	}
}


function __logDayShow(id)
{
	for (var i = 0; i < arDays[id].length; i++)
	{
		if (
			BX('sonet_log_day_row_' + arDays[id][i])
			&& BX('sonet_log_day_row_' + arDays[id][i]).style.display == 'none'
		)		
			BX('sonet_log_day_row_' + arDays[id][i]).style.display = 'table-row';
		else if (BX('sonet_log_day_row_' + arDays[id][i]))
			BX('sonet_log_day_row_' + arDays[id][i]).style.display = 'none';
	}
		
	if (BX('sonet_log_day_arrow_' + id))
	{
		if (BX.hasClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-a'))
		{
			BX.removeClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-a');
			BX.addClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-na');
		}
		else if (BX.hasClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-na'))
		{
			BX.removeClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-na');
			BX.addClass(BX('sonet_log_day_arrow_' + id), 'sonet-log-header-arrow-a');
		}
	}
	
	if (
		BX('sonet_log_day_counter_' + id)
		&& BX('sonet_log_day_counter_' + id).style.display == 'none'
	)
		BX('sonet_log_day_counter_' + id).style.display = 'block';
	else if (
		BX('sonet_log_day_counter_' + id)
	)
		BX('sonet_log_day_counter_' + id).style.display = 'none';

	return;
}

__logRowOver = function(ob)
{
	BX.addClass(ob, "sonet-log-row-over");
	ob.BXOVER = true;
}

__logRowOut = function(ob)
{
	if (ob.BXTIMER)
		clearTimeout(ob.BXTIMER);

	ob.BXOVER = false;
	ob.BXTIMER = setTimeout(BX.proxy(__logRowOverChange, ob), 50);
}

__logRowOverChange = function()
{
	if (this.BXOVER)
		return;
	
	BX.removeClass(this, "sonet-log-row-over");
}

__logShowSubscribeDialog = function(ind, entity_type, entity_id, event_id, cb_id, val)
{

	var closeButton = new BX.PopupWindowButton(
					{
						'text': BX.message('sonetLDialogClose'),
						'id': 'bx_log_subscribe_popup_close'
					}
				);
				
				
	var popup = BX.PopupWindowManager.create(
				'bx_log_subscribe_popup', 
				BX('sonet_log_subscribe_' + ind),
				{
					buttons: [closeButton]
				}
			);

	if (
		val
		&& entity_type != null 
		&& entity_type != false
		&& entity_id != null 
		&& entity_id != false
		&& event_id != null 
		&& event_id != false			
	)
	{
	
		var params = BX.message('sonetLGetPath') + "?" + BX.message('sonetLSessid')
			+ "&action=get_data"
			+ "&lang=" + BX.util.urlencode(BX.message('sonetLLangId'))
			+ "&site=" + BX.util.urlencode(BX.message('sonetLSiteId'))		
			+ "&et=" + BX.util.urlencode(entity_type)
			+ "&eid=" + BX.util.urlencode(entity_id)
			+ "&evid=" + BX.util.urlencode(event_id)
			+ "&r=" + Math.floor(Math.random() * 1000);

		if (
			cb_id != null 
			&& cb_id != false
		)
			params += "&cb_id=" + BX.util.urlencode(cb_id);
			
		sonetLXmlHttpGet.open(
			"get",
			params
		);
		sonetLXmlHttpGet.send(null);

		sonetLXmlHttpGet.onreadystatechange = function()
		{
			if (sonetLXmlHttpGet.readyState == 4 && sonetLXmlHttpGet.status == 200)
			{
				var data = LBlock.DataParser(sonetLXmlHttpGet.responseText);
				if (typeof(data) == "object")
				{
					if (data[0] == '*')
					{
						if (sonetLErrorDiv != null)
						{
							sonetLErrorDiv.style.display = "block";
							sonetLErrorDiv.innerHTML = sonetLXmlHttpSet.responseText;
						}
						return;
					}
					sonetLXmlHttpGet.abort();
					LBlock.arData["Subscription"][entity_type + '_' + entity_id + '_' + event_id + '_' + ind] = data["Subscription"];
					
					if (
						typeof LBlock.arData["Transport"] == 'undefined'
						|| LBlock.arData["Transport"].length <= 0
					)
						LBlock.arData["Transport"] = data["Transport"];

					if (
						typeof LBlock.arData["Visible"] == 'undefined'
						|| LBlock.arData["Visible"].length <= 0
					)
						LBlock.arData["Visible"] = data["Visible"];

					if (popup.bindElementPos != null)
					{
						popup.setBindElement(BX('sonet_log_subscribe_' + ind));
						BX.cleanNode(popup.contentContainer);
					}

					var content = LBlock.ShowContent(entity_type, entity_id, event_id, cb_id, ind);
					popup.setContent(content);
					popup.show();

					BX.bind(BX('bx_log_subscribe_popup_close'), "click", BX.delegate(popup.close, popup));
				}
			}
		}
	}

}

if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var sonetLXmlHttpGet = new XMLHttpRequest();
var sonetLXmlHttpSet = new XMLHttpRequest();

var LBlock = new BX.CLBlock();	

/*========================================Popup===========================================*/

(function(window) {

if (BX.PopupWindowManager)
	return;

BX.PopupWindowManager =
{
	_popups : [],
	_currentPopup : null,

	create : function(uniquePopupId, bindElement, params)
	{
		var index = -1;
		if ( (index = this._getPopupIndex(uniquePopupId)) !== -1)
			return this._popups[index];

		var popupWindow = new BX.PopupWindow(uniquePopupId, bindElement, params);

		BX.addCustomEvent(popupWindow, "onPopupShow", BX.delegate(this.onPopupShow, this));
		BX.addCustomEvent(popupWindow, "onPopupClose", BX.delegate(this.onPopupClose, this));
		BX.addCustomEvent(popupWindow, "onPopupDestroy", BX.delegate(this.onPopupDestroy, this));

		if (params && params.events)
		{
			for (var eventName in params.events)
				BX.addCustomEvent(popupWindow, eventName, params.events[eventName]);
		}

		this._popups.push(popupWindow);

		return popupWindow;
	},

	onPopupShow : function(popupWindow)
	{
		if (this._currentPopup !== null)
			this._currentPopup.close();

		this._currentPopup = popupWindow;
	},

	onPopupClose : function(popupWindow)
	{
		this._currentPopup = null;
	},

	onPopupDestroy : function(popupWindow)
	{
		var index = -1;
		if ( (index = this._getPopupIndex(popupWindow.uniquePopupId)) !== -1)
			this._popups = BX.util.deleteFromArray(this._popups, index);
	},

	getCurrentPopup : function()
	{
		return this._currentPopup;
	},

	isPopupExists : function(uniquePopupId)
	{
		return this._getPopupIndex(uniquePopupId) !== -1
	},

	_getPopupIndex : function(uniquePopupId)
	{
		var index = -1;

		for (var i = 0; i < this._popups.length; i++)
			if (this._popups[i].uniquePopupId == uniquePopupId)
				return i;

		return index;
	}
}

BX.PopupWindow = function(uniquePopupId, bindElement, params)
{
	this.uniquePopupId = uniquePopupId;
	this.params = params || {};
	this.buttons = this.params.buttons && BX.type.isArray(this.params.buttons) ? this.params.buttons : [];
	this.offsetTop = this.params.offsetTop && BX.type.isNumber(this.params.offsetTop) ? this.params.offsetTop : 0;
	this.offsetLeft = this.params.offsetLeft && BX.type.isNumber(this.params.offsetLeft) ? this.params.offsetLeft : 0;

	this.firstShow = false;
	this.bordersWidth = 12;
	this.bindElementPos = null;
	
	this.popupContainer = document.createElement("DIV");

	BX.adjust(this.popupContainer, {
		props : {
			id : uniquePopupId
		},
		style : {
			zIndex: 0,
			position: "absolute",
			display: "none",
			top: "0px",
			left: "0px"
		}
	});

	this.popupContainer.innerHTML = ['<table class="popup-window', (params.lightShadow ? " popup-window-light" : ""),'" cellspacing="0"> \
		<tr class="popup-window-top-row"> \
			<td class="popup-window-left-column"></td> \
			<td class="popup-window-center-column"></td> \
			<td class="popup-window-right-column"></td> \
		</tr> \
		<tr class="popup-window-content-row"> \
			<td class="popup-window-left-column"></td> \
			<td class="popup-window-center-column"><div class="popup-window-content" id="popup-window-content-', uniquePopupId ,'"> \
			</div></td> \
			<td class="popup-window-right-column"></td> \
		</tr> \
		<tr class="popup-window-bottom-row"> \
			<td class="popup-window-left-column"></td> \
			<td class="popup-window-center-column"></td> \
			<td class="popup-window-right-column"></td> \
		</tr> \
	</table>'].join("");
	document.body.appendChild(this.popupContainer);
	
	this.contentContainer = BX("popup-window-content-" +  uniquePopupId);
	this.buttonsContainer = this.buttonsHr = null;

	this.setBindElement(bindElement);
	this.setContent(this.params.content)
	this.setButtons(this.params.buttons);

	BX.bind(window, "resize", BX.delegate(this._onResizeWindow, this));
}

BX.PopupWindow.prototype.setContent = function(content)
{
	if (!this.contentContainer || !content)
		return;

	if (BX.type.isElementNode(content))
	{
		this.contentContainer.appendChild(content.parentNode ? content.parentNode.removeChild(content) : content );
		content.style.display = "block";
	}
	else if (BX.type.isString(content))
	{
		this.contentContainer.innerHTML = content;
	}
	else
		this.contentContainer.innerHTML = "&nbsp;";

}

BX.PopupWindow.prototype.setButtons = function(buttons)
{
	this.buttons = buttons && BX.type.isArray(buttons) ? buttons : [];

	if (this.buttonsHr)
		BX.remove(this.buttonsHr);
	if (this.buttonsContainer)
		BX.remove(this.buttonsContainer);
	
	if (this.buttons.length > 0 && this.contentContainer)
	{
		var newButtons = [];
		for (var i = 0; i < this.buttons.length; i++)
		{
			var button = this.buttons[i];
			if (button == null || !BX.is_subclass_of(button, BX.PopupWindowButton))
				continue;

			button.popupWindow = this;
			newButtons.push(button.render());
		}

		this.buttonsHr = this.contentContainer.parentNode.appendChild(
			BX.create("div",{
				props : { className : "popup-window-hr popup-window-buttons-hr" },
				children : [ BX.create("i", {}) ]
			})
		);

		this.buttonsContainer = this.contentContainer.parentNode.appendChild(
			BX.create("div",{
				props : { className : "popup-window-buttons" },
				children : newButtons
			})
		);
	}
}

BX.PopupWindow.prototype.setBindElement = function(bindElement)
{
	if (BX.type.isDomNode(bindElement))
		this.bindElement = bindElement;
}

BX.PopupWindow.prototype.show = function()
{
	if (!this.firstShow)
	{
		BX.onCustomEvent(this, "onPopupFirstShow", [this]);
		this.firstShow = true;
	}
	BX.onCustomEvent(this, "onPopupShow", [this]);

	this.popupContainer.style.display = "block";

	this.adjustPosition();
	
	if (this.params.autoHide)
	{
		setTimeout(
			BX.proxy(function() {
				BX.bind(this.contentContainer, "click", this.cancelBubble);
				BX.bind(document, "click", BX.proxy(this.close, this));
			}, this), 0
		);

	}	
}

BX.PopupWindow.prototype.cancelBubble = function(event)
{
	if(!event)
		event = window.event;

	if (event.stopPropagation)
		event.stopPropagation();
	else
		event.cancelBubble = true;
};

BX.PopupWindow.prototype.close = function()
{
	BX.onCustomEvent(this, "onPopupClose", [this]);
	this.popupContainer.style.display = "none";
	
	if (this.params.autoHide)
	{
		BX.unbind(this.contentContainer, "click", BX.PreventDefault);
		BX.unbind(document, "click", BX.proxy(this.close, this));
	}
}

BX.PopupWindow.prototype.destroy = function()
{
	BX.onCustomEvent(this, "onPopupDestroy", [this]);
	BX.unbindAll(this);
	BX.remove(this.popupContainer);
}

BX.PopupWindow.prototype.adjustPosition = function()
{
	if (!this.bindElement)
		return;

	var bindElementPos = BX.pos(this.bindElement, false);
	if (this.bindElementPos != null && bindElementPos.top == this.bindElementPos.top && bindElementPos.left == this.bindElementPos.left)
		return;
	this.bindElementPos = bindElementPos;

	var windowSize = BX.GetWindowInnerSize();
	var windowScroll = BX.GetWindowScrollPos();
	var popupWidth = this.popupContainer.offsetWidth;
	var popupHeight = this.popupContainer.offsetHeight;

	var top = this.bindElementPos.bottom + this.offsetTop;
	var left = this.bindElementPos.left + this.offsetLeft;

	if ( (left + popupWidth) >= (windowSize.innerWidth + windowScroll.scrollLeft) && (windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth) > 0)
		left = windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth;

	if (left < 0)
		left = 0;

	if ( (top + popupHeight) > (windowSize.innerHeight + windowScroll.scrollTop) && (this.bindElementPos.top - popupHeight) >= 0)
		top =  this.bindElementPos.top - popupHeight;

	if (top < 0)
		top = 0;

	BX.adjust(this.popupContainer, {
		style: {
			top: top + "px",
			left: left + "px",
			zIndex: 1000
		}
	});
}

BX.PopupWindow.prototype._onResizeWindow = function(event)
{
	this.adjustPosition();
}

/*========================================Buttons===========================================*/

BX.PopupWindowButton = function(params)
{
	this.popupWindow = null;

	this.params = params || {};

	this.text = this.params.text || "";
	this.id = this.params.id || "";
	this.className = this.params.className || "";
	this.events = this.params.events || {};

	this.contextEvents = {};
	for (var eventName in this.events)
		this.contextEvents[eventName] = BX.proxy(this.events[eventName], this);

	this.nameNode = BX.create("span", { props : { className : "popup-window-button-text"}, text : this.text } );
	this.buttonNode = BX.create(
		"span",
		{
			props : { className : "popup-window-button" + (this.className.length > 0 ? " " + this.className : ""), id : this.id },
			children : [
				BX.create("span", { props : { className : "popup-window-button-left"} } ),
				this.nameNode,
				BX.create("span", { props : { className : "popup-window-button-right"} } )
			],
			events : this.contextEvents
		}
	);
}

BX.PopupWindowButton.prototype.render = function()
{
	return this.buttonNode;
}

BX.PopupWindowButton.prototype.setName = function(name)
{
	this.text = name || "";
	if (this.nameNode)
	{
		BX.cleanNode(this.nameNode);
		BX.adjust(this.nameNode, { text : this.text} );
	}
}

BX.PopupWindowButton.prototype.setClassName = function(className)
{
	if (this.buttonNode)
	{
		BX.removeClass(this.buttonNode, this.className);
		BX.addClass(this.buttonNode, className)
	}

	this.className = className;
}

BX.PopupWindowButtonLink = function(params)
{
	BX.PopupWindowButtonLink.superclass.constructor.apply(this, arguments);

	this.nameNode = BX.create("a", { props : { href : "" }, text : this.text, events : this.contextEvents });
	this.buttonNode = BX.create(
		"span",
		{
			props : { className : "popup-window-button" + (this.className.length > 0 ? " " + this.className : ""), id : this.id },
			children : [this.nameNode]
		}
	);

}

BX.extend(BX.PopupWindowButtonLink, BX.PopupWindowButton);

})(window);