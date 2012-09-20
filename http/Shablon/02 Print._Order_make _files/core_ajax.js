(function(window){
if (window.BX.ajax) return;

var
	BX = window.BX, 
	
	tempDefaultConfig = {},
	defaultConfig = {
		method: 'GET', // request method: GET|POST
		dataType: 'html', // type of data loading: html|json|script
		timeout: 0, // request timeout in seconds. 0 for browser-default
		async: true, // whether request is asynchronous or not
		processData: true, // any data processing is disabled if false, only callback call
		scriptsRunFirst: false, // whether to run _all_ found scripts before onsuccess call. script tag can have an attribute "bxrunfirst" to turn  this flag on only for itself
		emulateOnload: true,
		start: true, // send request immediately (if false, request can be started manually via XMLHttpRequest object returned)
		cache: true // whether NOT to add random addition to URL
/*
other parameters:
	url: url to get/post
	data: data to post
	onsuccess: successful request callback. BX.proxy may be used.
	onfailure: request failure callback. BX.proxy may be used.
	
any of the default parameters can be overridden. defaults can be changed by BX.ajax.Setup() - for all further requests!
*/
	},
	ajax_session = null,
	loadedScripts = {},
	r = {
		'url_utf': /[^\034-\254]+/g,
		'script_self': /\/bitrix\/js\/main\/core\/core(_ajax)*.js$/i,
		'script_self_window': /\/bitrix\/js\/main\/core\/core_window.js$/i,
		'script_self_admin': /\/bitrix\/js\/main\/core\/core_admin.js$/i,
		'script_onload': /window.onload/g
	};

// low-level method
BX.ajax = function(config)
{
	var status, data;

	if (!config || !config.url || !BX.type.isString(config.url))
	{
		return false;
	}
	
	for (var i in tempDefaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = tempDefaultConfig[i];
	
	tempDefaultConfig = {};

	for (var i in defaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = defaultConfig[i];

	config.method = config.method.toUpperCase();
	
	if (BX.browser.IsIE())
	{
		var result = r.url_utf.exec(config.url);
		if (result)
		{
			do
			{
				config.url = config.url.replace(result, BX.util.urlencode(result)); 
				result = r.url_utf.exec(config.url); 
			} while (result);
		}
	}
	
	if (!config.cache && config.method == 'GET')
		config.url = BX.ajax._uncache(config.url);

	if (config.method == 'POST')
	{
		config.data = BX.ajax.prepareData(config.data);
	}
	
	config.xhr = BX.ajax.xhr();
	if (!config.xhr) return;
	
	config.xhr.open(config.method, config.url, config.async);
	if (config.method == 'POST')
	{
		config.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}

	var bRequestCompleted = false;
	var onreadystatechange = config.xhr.onreadystatechange = function(additional)
	{
		if (bRequestCompleted)
			return;
	
		if (additional === 'timeout')
		{
			if (config.onfailure)
				config.onfailure("timeout");
				
			BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['timeout', '', config]);
			
			config.xhr.onreadystatechange = BX.DoNothing;
			config.xhr.abort();
			
			if (config.async) 
			{
				config.xhr = null;
			}
		}
		else
		{
			if (config.xhr.readyState == 4 || additional == 'run')
			{
				status = BX.ajax.xhrSuccess(config.xhr) ? "success" : "error";
				bRequestCompleted = true;
				config.xhr.onreadystatechange = BX.DoNothing;
				
				if (status == 'success')
				{
					var data = config.xhr.responseText;
					
					if (!config.processData)
					{
						if (config.onsuccess)
						{
							config.onsuccess(data);
						}
						
						BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [data, config]);
					}
					else
					{
						try 
						{
							data = BX.ajax.processRequestData(data, config);
						} 
						catch (e) 
						{
							if (config.onfailure)
								config.onfailure("processing", e);
							BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['processing', e, config]);
						}
					}
				}
				else if (config.onfailure) 
				{
					config.onfailure("status", config.xhr.status);
					BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['status', config.xhr.status, config]);
				}
				
				if (config.async) 
				{
					config.xhr = null;
				}
			}
		}
	}
	
	if (config.async && config.timeout > 0) 
	{
		setTimeout(function() {
			if (config.xhr && !bRequestCompleted) 
			{
				onreadystatechange("timeout");
			}
		}, config.timeout * 1000);
	}
	
	if (config.start)
	{
		config.xhr.send(config.data);
		
		if (!config.async)
		{
			onreadystatechange('run');
		}
	}
	
	return config.xhr;
}

BX.ajax.xhr = function()
{
	if (window.XMLHttpRequest)
	{
		try {return new XMLHttpRequest();} catch(e){}
	}
	else if (window.ActiveXObject)
	{
		try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
			catch(e) {}
		try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
			catch(e) {}
		try { return new ActiveXObject("Msxml2.XMLHTTP"); }
			catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP"); }
			catch(e) {}
		throw new Error("This browser does not support XMLHttpRequest.");
	}
	
	return null;
}

BX.ajax.__prepareOnload = function(scripts)
{
	if (scripts.length > 0)
	{
		BX.ajax['onload_' + ajax_session] = null;
		
		for (var i=0,len=scripts.length;i<len;i++)
		{
			if (scripts[i].isInternal)
			{
				scripts[i].JS = scripts[i].JS.replace(r.script_onload, 'BX.ajax.onload_' + ajax_session);
			}
		}
	}
}

BX.ajax.__runOnload = function()
{
	if (null != BX.ajax['onload_' + ajax_session])
	{
		BX.ajax['onload_' + ajax_session].apply(window);
		BX.ajax['onload_' + ajax_session] = null;
	}
}

BX.ajax.processRequestData = function(data, config)
{
	var result, scripts = [];
	switch (config.dataType.toUpperCase())
	{
		case 'JSON':
			if (data.indexOf("\n") >= 0)
				eval('result = ' + data);
			else
				result = (new Function("return " + data))();
		break;
		case 'SCRIPT':
			scripts.push({"isInternal": true, "JS": data, bRunFirst: config.scriptsRunFirst});
			result = data;
		break;
		
		default: // HTML
			var ob = BX.processHTML(data, config.scriptsRunFirst);
			result = ob.HTML; scripts = ob.SCRIPT;
		break;
	}

	var bSessionCreated = false;
	if (null == ajax_session)
	{
		ajax_session = parseInt(Math.random() * 1000000);
		bSessionCreated = true;
	}
	
	if (config.emulateOnload)
		BX.ajax.__prepareOnload(scripts);
	
	BX.ajax.processScripts(scripts, true);
	
	if (config.onsuccess)
	{
		config.onsuccess(result);
	}
		
	BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [result, config]);

	BX.ajax.processScripts(scripts, false);

	if (config.emulateOnload)
		BX.ajax.__runOnload();
	
	if (bSessionCreated)
	{
		ajax_session = null;
	}
}

BX.ajax.processScripts = function(scripts, bRunFirst)
{
	for (var i = 0, length = scripts.length; i < length; i++)
	{
		if (null != bRunFirst && bRunFirst != !!scripts[i].bRunFirst)
			continue;

		if (scripts[i].isInternal)
		{
			BX.evalGlobal(scripts[i].JS);
		}
		else
		{
			BX.ajax.loadScriptAjax([scripts[i].JS]);
		}
	}
}

// TODO: extend this function to use with any data objects or forms
BX.ajax.prepareData = function(arData, prefix)
{
	var data = '';
	if (BX.type.isString(arData))
		data = arData;
	else if (null != arData)
	{
		for(var i in arData)
		{
			if (data.length > 0) data += '&';
			var name = BX.util.urlencode(i);
			if(prefix)
				name = prefix + '[' + name + ']';
			if(typeof arData[i] == 'object')
				data += BX.ajax.prepareData(arData[i], name)
			else
				data += name + '=' + BX.util.urlencode(arData[i])
		}
	}
	return data;
}

BX.ajax.xhrSuccess = function(xhr)
{
	return (xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 || xhr.status === 1223 || xhr.status === 0;
}

BX.ajax.Setup = function(config, bTemp)
{
	bTemp = !!bTemp;

	for (var i in config)
	{
		if (bTemp)
			tempDefaultConfig[i] = config[i];
		else
			defaultConfig[i] = config[i];
	}
}

BX.ajax._uncache = function(url)
{
	return url + ((url.indexOf('?') !== -1 ? "&" : "?") + '_=' + (new Date).getTime());
}

/* simple interface */
BX.ajax.get = function(url, data, callback)
{
	if (BX.type.isFunction(data))
	{
		callback = data;
		data = '';
	}
	
	data = BX.ajax.prepareData(data);
	
	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}
	
	return BX.ajax({
		'method': 'GET',
		'dataType': 'html',
		'url': url,
		'data':  '',
		'onsuccess': callback
	});
}

BX.ajax.insertToNode = function(url, node)
{
	if (node = BX(node))
	{
		var show = BX.showWait(node);
		return BX.ajax.get(url, function(data) {
			node.innerHTML = data;
			BX.closeWait(node, show);
		});
	}
}

BX.ajax.post = function(url, data, callback)
{
	data = BX.ajax.prepareData(data);
	
	return BX.ajax({
		'method': 'POST',
		'dataType': 'html',
		'url': url,
		'data':  data,
		'onsuccess': callback
	});
}

/* load and execute external file script with onload emulation */
BX.ajax.loadScriptAjax = function(script_src, callback)
{
	if (BX.type.isArray(script_src))
	{
		for (var i=0,len=script_src.length;i<len;i++)
			BX.ajax.loadScriptAjax(script_src[i], callback);
	}
	else
	{
		if (r.script_self.test(script_src)) return;
		if (r.script_self_window.test(script_src) && BX.CWindow) return;
		if (r.script_self_admin.test(script_src) && BX.admin) return;
		
		if (!loadedScripts[script_src])
		{
			return BX.ajax({
				url: script_src,
				method: 'GET',
				dataType: 'script',
				processData: true,
				async: false,
				start: true,
				onsuccess: function(result) {
					loadedScripts[script_src] = result;
					if (callback)
						callback(result);
				}
			});
		}
		else if (callback)
		{
			callback(loadedScripts[script_src]);
		}
	}
}

/* non-xhr loadings */
BX.ajax.loadJSON = function(url, data, callback)
{
	if (BX.type.isFunction(data))
	{
		callback = data;
		data = '';
	}
	
	data = BX.ajax.prepareData(data);
	
	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}

	return BX.ajax({
		'method': 'GET',
		'dataType': 'json',
		'url': url,
		'onsuccess': callback
	});
}

/*
arObs = [{
	url: url,
	type: html|script|json|css,
	callback: function
}]
*/
BX.ajax.load = function(arObs, callback)
{
	if (!BX.type.isArray(arObs))
		arObs = [arObs];
	
	var cnt = 0;
	
	if (!BX.type.isFunction(callback))
		callback = BX.DoNothing;
	
	var handler = function(data)
		{
			if (BX.type.isFunction(this.callback))
				this.callback(data);
			
			if (++cnt >= len)
				callback();
		};
	
	for (var i = 0, len = arObs.length; i<len; i++)
	{
		switch(arObs.type.toUpperCase())
		{
			case 'SCRIPT':
				BX.loadScript([arObs[i].url], jsBX.proxy(handler, arObs[i]));
			break;
			case 'CSS':
				BX.loadCSS([arObs[i].url]);

				if (++cnt >= len)
					callback();
			break;
			case 'JSON':
				BX.ajax.loadJSON(arObs.url, jsBX.proxy(handler, arObs[i]));
			break;
			
			default:
				BX.ajax.get(arObs.url, '', jsBX.proxy(handler, arObs[i]));
			break;
		}
	}
}

/* ajax form sending */
BX.ajax.submit = function(obForm, callback)
{
	if (!obForm.target) 
	{
		if (null == obForm.BXFormTarget)
		{
			var frame_name = 'formTarget_' + Math.random();
			obForm.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
				props: {
					name: frame_name,
					id: frame_name,
					src: 'javascript:void(0)'
				},
				style: {
					display: 'none'
				}
			}));
		}
		
		obForm.target = obForm.BXFormTarget.name;
	}
	
	if (!obForm.BXFormSubmit)
	{
		obForm.BXFormSubmit = obForm.appendChild(BX.create('INPUT', {
			'props': {
				'type': 'submit',
				'name': 'save',
				'value': 'Y'
			},
			'style': {
				'display': 'none'
			}
		}));
	}
	
	obForm.BXFormCallback = callback;
	
	setTimeout(function() {
		BX.bind(obForm.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, obForm));
		obForm.BXFormSubmit.click();
	}, 10);
}

// func will be executed in form context
BX.ajax._submit_callback = function()
{
	if (this.BXFormCallback)
		this.BXFormCallback.apply(this, [this.BXFormTarget.contentWindow.document.body.innerHTML]);
	
	BX.unbindAll(this.BXFormTarget);
}

// TODO: currently in window extension. move it here.
BX.ajax.submitAjax = function(obForm, callback)
{

}

/* user options handling */
BX.userOptions = {
	options: null,
	bSend: false,
	delay: 5000
}

BX.userOptions.save = function(sCategory, sName, sValName, sVal, bCommon)
{
	if (null == BX.userOptions.options)
		BX.userOptions.options = {};

	bCommon = !!bCommon;
	BX.userOptions.options[sCategory+'.'+sName+'.'+sValName] = [sCategory, sName, sValName, sVal, bCommon];
	
	var sParam = BX.userOptions.__get();
	if (sParam != '')
		document.cookie = BX.message('COOKIE_PREFIX')+"_LAST_SETTINGS=" + sParam + "&sessid="+BX.bitrix_sessid()+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";
	
	if(!BX.userOptions.bSend)
	{
		BX.userOptions.bSend = true;
		setTimeout(function(){BX.userOptions.send(null)}, BX.userOptions.delay);
	}
}
	
BX.userOptions.send = function(callback)
{
	var sParam = BX.userOptions.__get();
	BX.userOptions.options = null;
	BX.userOptions.bSend = false;
	
	if (sParam != '')
	{
		document.cookie = BX.message('COOKIE_PREFIX') + "_LAST_SETTINGS=; path=/;";
		BX.ajax({
			'method': 'GET',
			'dataType': 'html',
			'processData': false,
			'cache': false,
			'url': '/bitrix/admin/user_options.php?'+sParam+'&sessid='+BX.bitrix_sessid(),
			'onsuccess': callback
		});
	}
}

BX.userOptions.del = function(sCategory, sName, bCommon, callback)
{
	BX.ajax.get('/bitrix/admin/user_options.php?action=delete&c='+sCategory+'&n='+sName+(bCommon == true? '&common=Y':'')+'&sessid='+BX.bitrix_sessid(), callback);
}
	
BX.userOptions.__get = function()
{
	if (!BX.userOptions.options) return '';

	var sParam = '', n = -1, prevParam = '', arOpt, i;

	for (i in BX.userOptions.options)
	{
		aOpt = BX.userOptions.options[i];
		
		if (prevParam != aOpt[0]+'.'+aOpt[1])
		{
			n++;
			sParam += '&p['+n+'][c]='+BX.util.urlencode(aOpt[0]);
			sParam += '&p['+n+'][n]='+BX.util.urlencode(aOpt[1]);
			if (aOpt[4] == true)
				sParam += '&p['+n+'][d]=Y';
			prevParam = aOpt[0]+'.'+aOpt[1];
		}
		
		sParam += '&p['+n+'][v]['+BX.util.urlencode(aOpt[2])+']='+BX.util.urlencode(aOpt[3]);
	}

	return sParam.substr(1);
}


})(window)