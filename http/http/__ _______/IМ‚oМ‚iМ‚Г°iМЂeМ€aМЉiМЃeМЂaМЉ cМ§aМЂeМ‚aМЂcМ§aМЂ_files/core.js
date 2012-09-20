/**********************************************************************/
/*********** Bitrix JS Core library ver 0.9.0 beta ********************/
/**********************************************************************/

(function(window){
if (window.BX) return;


var BX = function(node, bCache)
{
	if (BX.type.isNotEmptyString(node))
	{
		var ob;
	
		if (!!bCache && null != NODECACHE[node]) ob = NODECACHE[node];
		ob = ob || document.getElementById(node);
		if (!!bCache) NODECACHE[node] = ob;
		
		return ob;
	}
	else if (BX.type.isDomNode(node))
		return node;
	else if (BX.type.isFunction(node))
		return BX.ready(node);

	return null;
},

/* language messages */
MESS = {},

/* ready */
__readyHandler = null,
readyBound = false,
readyList = [],

/* list of registered proxy functions */
proxyId = 1,
proxyList = [],

/* getElementById cache */
NODECACHE = {},

/* List of denied event handlers */
deniedEvents = [],

/* list of registered event handlers */
eventsList = [],

/* list of registered custom events */
customEvents = {},

/* list of external garbage collectors */
garbageCollectors = [],

/* browser detection */
bOpera = navigator.userAgent.toLowerCase().indexOf('opera') != -1,
bSafari = navigator.userAgent.toLowerCase().indexOf('webkit') != -1,
bIE = document.attachEvent && !bOpera,

/* regexps */
r = {
	script: /<script([^>]*)>/i,
	script_src: /src=["\']([^"\']+)["\']/i,
	space: /\s+/,
	ltrim: /^[\s\r\n]+/g,
	rtrim: /[\s\r\n]+$/g
},

lastWait = [];

BX.ext = function(ob) {for (var i in ob) this[i] = ob[i];}

/* OO emulation utility */
BX.ext({
	extend: function(child, parent)
	{
		var f = function() {};
		f.prototype = parent.prototype;
		child.prototype = new f();
		child.prototype.constructor = child;
		child.superclass = parent.prototype;
	},

	is_subclass_of: function(ob, parent_class)
	{
		if (ob instanceof parent_class)
			return true;

		if (parent_class.superclass)
			return BX.is_subclass_of(ob, parent_class.superclass);

		return false;
	}
});

// language utility
BX.ext({
	message: function(mess)
	{
		if (BX.type.isString(mess))
			return MESS[mess];
		else
		{
			for (var i in mess)
			{
				MESS[i] = mess[i];
			}
		}
	},
	bitrix_sessid: function() {return MESS.bitrix_sessid;}
});

/* DOM manipulation */
BX.ext({
	create: function(tag, data, context)
	{
		context = context || document;

		if (null == data && typeof tag == 'object' && tag.constructor !== String)
		{
			data = tag; tag = tag.tag;
		}

		if (BX.browser.IsIE() && !BX.browser.IsIE9() && null != data && null != data.props && (data.props.name || data.props.id))
		{
			var elem = context.createElement('<' + tag + (data.props.name ? ' name="' + data.props.name + '"' : '') + (data.props.id ? ' id="' + data.props.id + '"' : '') + '>');
		}
		else
		{
			var elem = context.createElement(tag);
		}

		return data ? BX.adjust(elem, data) : elem;
	},

	adjust: function(elem, data)
	{
		var j,len;

		if (!elem.nodeType)
			return;

		if (elem.nodeType == 9)
			elem = elem.body;

		if (data.attrs)
		{
			for (j in data.attrs)
			{
				if (j == 'class' || j == 'className')
					elem.className = data.attrs[j];
				else if(data.attrs[j] == "")
					elem.removeAttribute(j);
				else
					elem.setAttribute(j, data.attrs[j]);
			}
		}

		if (data.style)
		{
			for (j in data.style)
				elem.style[j] = data.style[j];
		}

		if (data.props)
		{
			for (j in data.props)
				elem[j] = data.props[j];
		}

		if (data.events)
		{
			for (j in data.events)
				BX.bind(elem, j, data.events[j]);
		}

		if (data.children && data.children.length > 0)
		{
			for (j=0,len=data.children.length; j<len; j++)
			{
				if (BX.type.isNotEmptyString(data.children[j]))
					elem.innerHTML += data.children[j];
				else if (BX.type.isElementNode(data.children[j]))
					elem.appendChild(data.children[j]);
			}
		}
		else if (data.text)
		{
			BX.cleanNode(elem);
			elem.appendChild((elem.ownerDocument || document).createTextNode(data.text));
		}
		else if (data.html)
		{
			elem.innerHTML = data.html;
		}

		return elem;
	},

	remove: function(ob)
	{
		if (null != ob.parentNode)
			ob.parentNode.removeChild(ob);
		ob = null;
		return null;
	},

	cleanNode: function(node, bSuicide)
	{
		node = BX(node);
		bSuicide = !!bSuicide;

		if (node && node.childNodes)
		{
			while(node.childNodes.length > 0)
				node.removeChild(node.firstChild);
		}

		if (bSuicide)
		{
			node = BX.remove(node);
		}

		return node;
	},

	addClass: function(ob, value)
	{
		var classNames;

		if (ob = BX(ob))
		{
			if (!ob.className)
			{
				ob.className = value
			}
			else
			{
				classNames = (value || "").split(r.space);

				var className = " " + ob.className + " ";
				for (var j = 0, cl = classNames.length; j < cl; j++)
				{
					if (className.indexOf(" " + classNames[j] + " ") < 0)
					{
						ob.className += " " + classNames[j];
					}
				}
			}
		}

		return ob;
	},

	removeClass: function(ob, value)
	{
		if (ob = BX(ob))
		{
			if (ob.className)
			{
				if (BX.type.isString(value))
				{
					var classNames = value.split(r.space), className = " " + ob.className + " ";
					for (var j = 0, cl = classNames.length; j < cl; j++)
					{
						className = className.replace(" " + classNames[j] + " ", " ");
					}

					ob.className = BX.util.trim(className);
				}
				else
				{
					ob.className = "";
				}
			}
		}

		return ob;
	},

	toggleClass: function(ob, value)
	{
		if (BX.type.isArray(value))
		{
			var className = ' ' + ob.className + ' ';
			for (var j = 0, len = value.length; j < len; j++)
			{
				if (BX.hasClass(ob, value[j]))
				{
					className = (' ' + className + ' ').replace(' ' + value[j] + ' ', ' ');
					className += ' ' + value[j >= len-1 ? 0 : j+1];

					j--;
					break;
				}
			}

			if (j == len)
				ob.className += ' ' + value[0];
			else
				ob.className = className;

			ob.className = BX.util.trim(ob.className);
		}
		else if (BX.type.isNotEmptyString(value))
		{
			var className = ob.className;
			if (BX.hasClass(ob, value))
			{
				className = (' ' + className + ' ').replace(' ' + value + ' ', ' ');
			}
			else
			{
				className += ' ' + value;
			}

			ob.className = BX.util.trim(className);
		}

		return ob;
	},

	hasClass: function(el, className)
	{
		if (!el.className)
			return false;
		return ((" " + el.className + " ").indexOf(" " + className + " ")) >= 0;
	},
	
	setUnselectable: function(node)
	{
		BX.addClass(node, 'bx-unselectable');
		node.setAttribute('unSelectable', 'on');
	},

	_styleIEPropertyName: function(name)
	{
		if (name == 'float')
			name = 'cssFloat';
		else
		{
			var reg = /(\-([a-z]){1})/g;
			if (reg.test(name))
			{
				name = name.replace(reg, function () {return arguments[2].toUpperCase();});
			}
		}
		return name;
	},

	/* CSS-notation should be used here */
	style: function(el, property, value)
	{
		if (!BX.type.isElementNode(el))
			return;

		if (value == null)
		{
			var res;
			if(el.currentStyle)
				res = el.currentStyle[BX._styleIEPropertyName(property)];
			else if(window.getComputedStyle)
				res = BX.GetContext(el).getComputedStyle(el, null).getPropertyValue(property);

			if(!res)
				res = '';
			return res;
		}
		else
		{
			el.style[BX._styleIEPropertyName(property)] = value;
			return el;
		}
	},
	
	focus: function(el) 
	{
		try
		{
			el.focus();
			return true;
		}
		catch (e) 
		{
			return false;
		}
	},

/*
	params: {
		tagName|tag : 'tagName',
		className|class : 'className',
		attribute : {attribute : value, attribute : value} | attribute | [attribute, attribute....],
		property : {prop: value, prop: value} | prop | [prop, prop]
	}

	all values can be RegExps or strings
*/
	_checkNode: function(obj, params)
	{
		params = params || {};
		if (!params.allowTextNodes && !BX.type.isElementNode(obj))
			return false;
		var i,j,len;
		for (i in params)
		{
			switch(i)
			{
				case 'tag':
				case 'tagName':
					if (BX.type.isString(params[i]))
					{
						if (obj.tagName.toUpperCase() != params[i].toUpperCase())
							return false;
					}
					else if (params[i] instanceof RegExp)
					{
						if (!params[i].test(obj.tagName))
							return false;
					}
				break;

				case 'class':
				case 'className':
					if (BX.type.isString(params[i]))
					{
						if (!BX.hasClass(obj, params[i]))
							return false;
					}
					else if (params[i] instanceof RegExp)
					{
						if (!BX.type.isString(obj.className) || !params[i].test(obj.className))
							return false;
					}
				break;

				case 'attr':
				case 'attribute':
					if (BX.type.isString(params[i]))
					{
						if (!obj.getAttribute(params[i]))
							return false;
					}
					else if (BX.type.isArray(params[i]))
					{
						for (j = 0, len = params[i].length; j < len; j++)
						{
							if (params[i] && !obj.getAttribute(params[i]))
								return false;
						}
					}
					else
					{
						for (j in params[i])
						{
							var q = obj.getAttribute(j);
							if (BX.type.isString(params[i][j]))
							{
								if (q != params[i][j])
									return false;
							}
							else if (params[i][j] instanceof RegExp)
							{
								if (!BX.type.isString(q) || !params[i][j].test(q))
									return false;
							}
						}
					}
				break;

				case 'property':
					if (BX.type.isString(params[i]))
					{
						if (!obj[params[i]])
							return false;
					}
					else if (BX.type.isArray(params[i]))
					{
						for (j = 0, len = params[i].length; j < len; j++)
						{
							if (params[i] && !obj[params[i]])
								return false;
						}
					}
					else
					{
						for (j in params[i])
						{
							if (BX.type.isString(params[i][j]))
							{
								if (obj[j] != params[i][j])
									return false;
							}
							else if (params[i][j] instanceof RegExp)
							{
								if (!BX.type.isString(obj[j]) || !params[i][j].test(obj[j]))
									return false;
							}
						}
					}
				break;
			}
		}

		return true;
	},

	findChildren: function(obj, params, recursive)
	{
		return BX.findChild(obj, params, recursive, true);
	},
	
	findChild: function(obj, params, recursive, get_all)
	{
		if(!obj || !obj.childNodes) return null;

		recursive = !!recursive; get_all = !!get_all;

		var n = obj.childNodes.length, result = [];

		for (var j=0; j<n; j++)
		{
			var child = obj.childNodes[j];

			if (BX._checkNode(child, params))
			{
				if (get_all)
					result.push(child)
				else
					return child;
			}

			if(recursive == true)
			{
				var res = BX.findChild(child, params, recursive, get_all);
				if (res)
				{
					if (get_all)
						result = BX.util.array_merge(result, res);
					else
						return res;
				}
			}
		}
		
		if (get_all && result.length > 0)
			return result;
		else
			return null;
	},

	findParent: function(obj, params)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.parentNode)
		{
			var parent = o.parentNode;

			if (BX._checkNode(parent, params))
				return parent;

			o = parent;
		}
		return null;
	},

	findNextSibling: function(obj, params)
	{
		if(!obj)
			return null;
		var o = obj;
		while(o.nextSibling)
		{
			var sibling = o.nextSibling;
			if (BX._checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	},

	findPreviousSibling: function(obj, params)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.previousSibling)
		{
			var sibling = o.previousSibling;
			if(BX._checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	},

	clone: function(obj, bCopyObj)
	{
		var _obj, i;
		if (bCopyObj !== false)
			bCopyObj = true;

		if (BX.type.isDomNode(obj))
		{
			_obj = obj.cloneNode(bCopyObj);
		}
		else if (typeof obj == 'object')
		{
			if (bCopyObj)
			{
				if (BX.type.isArray(obj))
				{
					_obj = [];
				}
				else
				{
					_obj =  {};
					if (obj.constructor)
					{
						_obj = new obj.constructor();
					}
				}
			}

			for (i in obj)
			{
				if (typeof obj[i] == "object" && bCopyObj)
					_obj[i] = BX.clone(obj[i], bCopyObj);
				else
					_obj[i] = obj[i];
			}
		}
		else
		{
			_obj = obj;
		}

		return _obj;
	}
});

/* events */
BX.ext({
	bind: function(el, evname, func)
	{
		if(el.attachEvent) // IE
		{
			el.attachEvent("on" + evname, BX.proxy(func, el));
		}
		else
		{
			if(el.addEventListener) // Gecko / W3C
				el.addEventListener(evname, func, false);
			else
				el["on" + evname] = func;
		}

		eventsList[eventsList.length] = {'element': el, 'event': evname, 'fn': func};
	},

	unbind: function(el, evname, func)
	{
		if(el.detachEvent) // IE
			el.detachEvent("on" + evname, BX.proxy(func, el));
		else if(el.removeEventListener) // Gecko / W3C
			el.removeEventListener(evname, func, false);
		else
			el["on" + evname] = null;
	},

	unbindAll: function(el)
	{
		for (var i=0,len=eventsList.length; i<len; i++)
		{
			if (eventsList[i] && (null==el || el==eventsList[i].element))
			{
				BX.unbind(eventsList[i].element, eventsList[i].event, eventsList[i].fn);
				eventsList[i] = null;
			}
		}

		if (null==el)
		{
			eventsList = [];
		}
	},

	proxy_context: null,

	delegate: function (func, thisObject) {
		return function() {
			BX.proxy_context = this;
			var res = func.apply(thisObject, arguments);
			BX.proxy_context = null;
			return res;
		}
	},

	proxy: function(func, thisObject)
	{
		if (null == thisObject.__proxy_id)
		{
			proxyList[thisObject.__proxy_id = proxyList.length] = {};
		}

		if (null == func.__proxy_id)
			func.__proxy_id = proxyId++;

		if (null == proxyList[thisObject.__proxy_id][func.__proxy_id])
			proxyList[thisObject.__proxy_id][func.__proxy_id] = BX.delegate(func, thisObject);

		return proxyList[thisObject.__proxy_id][func.__proxy_id];
	},

	False: function() {return false;},

	DoNothing: function() {},

	// TODO: also check event handlers set via BX.bind()
	denyEvent: function(el, ev)
	{
		deniedEvents.push([el, ev, el['on' + ev]]);
		el['on' + ev] = BX.DoNothing;
	},

	allowEvent: function(el, ev)
	{
		for(var i=0, len=deniedEvents.length; i<len; i++)
		{
			if (deniedEvents[i][0] == el && deniedEvents[i][1] == ev)
			{
				el['on' + ev] = deniedEvents[i][2];
				BX.util.deleteFromArray(deniedEvents, i);
				return;
			}
		}
	},

	PreventDefault: function(e)
	{
		if(!e) e = window.event;
		if(e.stopPropagation)
		{
			e.preventDefault();
			e.stopPropagation();
		}
		else
		{
			e.cancelBubble = true;
			e.returnValue = false;
		}
		return false;
	},

	eventReturnFalse: function(e)
	{
		e=e||window.event;
		if (e && e.preventDefault) e.preventDefault();
		else e.returnValue = false;
		return false;
	},
	
	eventCancelBubble: function(e)
	{
		e=e||window.event;
		if(e && e.stopPropagation)
			e.stopPropagation();
		else
			e.cancelBubble = true;
	}
});

/* custom events */
BX.ext({
	/*
		BX.addCustomEvent(eventObject, eventName, eventHandler) - set custom event handler for particular object
		BX.addCustomEvent(eventName, eventHandler) - set custom event handler for all objects
	*/
	addCustomEvent: function(eventObject, eventName, eventHandler)
	{
		/* shift parameters for short version */
		if (BX.type.isString(eventObject))
		{
			eventHandler = eventName;
			eventName = eventObject;
			eventObject = window;
		}

		eventName = eventName.toUpperCase();

		if (!customEvents[eventName])
			customEvents[eventName] = [];

		customEvents[eventName].push(
			{
				handler: eventHandler,
				obj: eventObject
			}
		);
	},

	removeCustomEvent: function(eventObject, eventName, eventHandler)
	{
		/* shift parameters for short version */
		if (BX.type.isString(eventObject))
		{
			eventHandler = eventName;
			eventName = eventObject;
			eventObject = window;
		}

		eventName = eventName.toUpperCase();
		
		if (!customEvents[eventName])
			return;

		for (var i = 0, l = customEvents[eventName].length; i < l; i++)
		{
			if (!customEvents[eventName][i])
				continue;
			if (customEvents[eventName][i].handler == eventHandler && customEvents[eventName][i].obj == eventObject)
			{
				delete customEvents[eventName][i];
				return;
			}
		}
	},

	onCustomEvent: function(eventObject, eventName, arEventParams)
	{
		/* shift parameters for short version */
		if (BX.type.isString(eventObject))
		{
			arEventParams = eventName;
			eventName = eventObject;
			eventObject = window;
		}

		eventName = eventName.toUpperCase();

		if (!customEvents[eventName])
			return;

		if (!arEventParams)
			arEventParams = [];

		var h;
		for (var i = 0, l = customEvents[eventName].length; i < l; i++)
		{
			h = customEvents[eventName][i];
			if (!h || !h.handler)
				continue;

			if (h.obj == window || /*eventObject == window || */h.obj == eventObject) //- only global event handlers will be called
			{
				h.handler.apply(eventObject, arEventParams);
			}
		}
	}
});

/* ready */
BX.ext({
	isReady: false,

	ready: function(handler)
	{
		BX.bindReady();

		if (BX.isReady)
			handler.call(document);
		else if (readyList)
			readyList.push(handler);
	},

	runReady: function()
	{
		if (!BX.isReady)
		{
			if (!document.body)
			{
				return setTimeout(BX.runReady, 15);
			}

			BX.isReady = true;

			if (readyList && readyList.length > 0)
			{
				var fn, i = 0;
				while (fn = readyList[i++])
				{
					fn.call(document);
				}

				readyList = null;
			}

			// TODO: check ready handlers binded some other way;
		}
	},

	bindReady: function ()
	{
		if (readyBound)
			return;

		readyBound = true;

		if (document.readyState === "complete")
		{
			return BX.runReady();
		}

		if (document.addEventListener)
		{
			document.addEventListener("DOMContentLoaded", __readyHandler, false);
			window.addEventListener("load", BX.runReady, false);
		}
		else if (document.attachEvent) // IE
		{
			document.attachEvent("onreadystatechange", __readyHandler);
			window.attachEvent("onload", BX.runReady);

			var toplevel = false;
			try {toplevel = (window.frameElement == null);} catch(e) {}

			if (document.documentElement.doScroll && toplevel)
				doScrollCheck();
		}
	}
});

/* browser detection */
BX.ext({
	browser:{
		IsIE: function()
		{
			return bIE;
		},

		IsIE6: function()
		{
			return (/MSIE 6/i.test(navigator.userAgent));
		},

		IsIE9: function() 
		{
			return !!document.documentMode && document.documentMode >= 9;
        },
		
		IsOpera: function()
		{
			return bOpera;
		},

		IsSafari: function()
		{
			return bSafari;
		},

		IsDoctype: function(pDoc)
		{
			pDoc = pDoc || document;

			if (pDoc.compatMode)
				return (pDoc.compatMode == "CSS1Compat");

			if (pDoc.documentElement && pDoc.documentElement.clientHeight)
				return true;

			return false;
		}
	}
});

/* low-level fx funcitons*/
BX.ext({
	toggle: function(ob, values)
	{
		if (BX.type.isArray(values))
		{
			for (var i=0,len=values.length; i<len; i++)
			{
				if (ob == values[i])
				{
					ob = values[i==len-1 ? 0 : i+1]
					break;
				}
			}
			if (i==len)
				ob = values[0];
		}

		return ob;
	}
});

/* some useful util functions */
BX.ext({
	util:{
		array_merge: function(first, second)
		{
			if (!BX.type.isArray(first)) first = [];
			if (!BX.type.isArray(second)) second = [];

			var i = first.length, j = 0;

			if (typeof second.length === "number")
			{
				for (var l = second.length; j < l; j++)
				{
					first[i++] = second[j];
				}
			}
			else
			{
				while (second[j] !== undefined)
				{
					first[i++] = second[j++];
				}
			}

			first.length = i;

			return first;
		},

		array_unique: function(ar)
		{
			var i=0,j,len=ar.length;
			if(len<2) return ar;

			for (; i<len-1;i++)
			{
				for (j=i+1; j<len;j++)
				{
					if (ar[i]==ar[j])
					{
						ar.splice(j--,1); len--;
					}
				}
			}

			return ar;
		},

		in_array: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return true;
			}
			return false;
		},

		array_search: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return i;
			}
			return -1;
		},

		array_values: function(ar)
		{
			if (!BX.type.isArray(ar)) return false;
			var ar1 = [],j=0;
			for (var i=0,len=ar.length; i < len; i++)
			{
				if (null != ar[i]) ar1[j++]=ar[i];
			}
			return ar1;
		},

		trim: function(s)
		{
			if (BX.type.isString(s))
				return s.replace(r.ltrim, '').replace(r.rtrim, '');
			else
				return s;
		},

		urlencode: function(s){return encodeURIComponent(s);},

		// it may also be useful. via sVD.
		deleteFromArray: function(ar, ind) {return ar.slice(0, ind).concat(ar.slice(ind + 1));},
		insertIntoArray: function(ar, ind, el) {return ar.slice(0, ind).concat([el]).concat(ar.slice(ind));},

		htmlspecialchars: function(str)
		{
			if(!str.replace) return str;

			return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		},

		htmlspecialcharsback: function(str)
		{
			if(!str.replace) return str;

			return str.replace(/\&quot;/g, '"').replace(/&#39;/g, "'").replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&amp;/g, '&');
		},

		// Quote regular expression characters plus an optional character
		preg_quote: function(str, delimiter)
		{
			if(!str.replace)
				return str;
			return str.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
		},
		
		jsencode: function(str)
		{
			if (!str || !str.replace)
				return str;
			
			var escapes = 
			[
				{ c: "\\\\", r: "\\\\" }, // should be first
				{ c: "\\t", r: "\\t" },
				{ c: "\\n", r: "\\n" },
				{ c: "\\r", r: "\\r" },
				{ c: "\"", r: "\\\"" },
				{ c: "'", r: "\\'" },
				{ c: "<", r: "\\x3C" },
				{ c: ">", r: "\\x3E" },
				{ c: "\\u2028", r: "\\u2028" },
				{ c: "\\u2029", r: "\\u2029" }
			];
			for (var i = 0; i < escapes.length; i++)
				str = str.replace(new RegExp(escapes[i].c, 'g'), escapes[i].r);
			return str;
		},
		
		str_pad: function(input, pad_length, pad_string, pad_type)
		{
			pad_string = pad_string || ' ';
			pad_type = pad_type || 'right';
			input = input.toString();
			
			if (pad_type == 'left')
				return BX.util.str_pad_left(input, pad_length, pad_string);
			else
				return BX.util.str_pad_right(input, pad_length, pad_string);
		
		},
		
		str_pad_left: function(input, pad_length, pad_string)
		{
			var i = input.length, q=pad_string.length;
			if (i >= pad_length) return input;
		
			for(;i<pad_length;i+=q)
				input = pad_string + input;
			
			return input;
		},
		
		str_pad_right: function(input, pad_length, pad_string)
		{
			var i = input.length, q=pad_string.length;
			if (i >= pad_length) return input;
		
			for(;i<pad_length;i+=q)
				input += pad_string;
			
			return input;
		},
			
		popup: function(url, width, height)
		{
			var w, h;
			if(BX.browser.IsOpera())
			{
				w = document.body.offsetWidth;
				h = document.body.offsetHeight;
			}
			else
			{
				w = screen.width;
				h = screen.height;
			}
			window.open(url, '', 'status=no,scrollbars=yes,resizable=yes,width='+width+',height='+height+',top='+Math.floor((h - height)/2-14)+',left='+Math.floor((w - width)/2-5));
		}
	},

	type: {
		isString: function(item) {
			return typeof (item) == "string" || item instanceof String;
		},
		isNotEmptyString: function(item) {
			return typeof (item) == "string" || item instanceof String ? item.length > 0 : false;
		},
		isBoolean: function(item) {
			return typeof (item) == "boolean" || item instanceof Boolean;
		},
		isNumber: function(item) {
		    return typeof (item) == "number" || item instanceof Number;
		},
		isFunction: function(item) {
		    return typeof (item) == "function" || item instanceof Function;
		},
		isElementNode: function(item) {
			return item && typeof (item) == "object" && "nodeType" in item && item.nodeType == 1; //document.body.ELEMENT_NODE;
		},
		isDomNode: function(item) {
			return item && typeof (item) == "object" && "nodeType" in item;
		},
		isArray: function(item) {
			return typeof (item) == 'array' || item instanceof Array;
		}
	},

	evalGlobal: function(data)
	{
		if (data)
		{
			var head = document.getElementsByTagName("head")[0] || document.documentElement,
				script = document.createElement("script");

			script.type = "text/javascript";

			if (!BX.browser.IsIE())
			{
				script.appendChild(document.createTextNode(data));
			}
			else
			{
				script.text = data;
			}

			head.insertBefore(script, head.firstChild);
			head.removeChild(script);
		}
	},

	processHTML: function(HTML, scriptsRunFirst)
	{
		var matchScript, scripts = [], data = HTML;

		while ((matchScript = data.match(r.script)) !== null)
		{
			var end = data.search('<\/script>', 'i');
			if (end == -1)
				break;

			var bRunFirst = scriptsRunFirst || (matchScript[1].indexOf('bxrunfirst') != '-1');

			var matchSrc;
			if ((matchSrc = matchScript[1].match(r.script_src)) !== null)
				scripts.push({"bRunFirst": bRunFirst, "isInternal": false, "JS": matchSrc[1]});
			else
			{
				var start = matchScript.index + matchScript[0].length;
				var js = data.substr(start, end-start);

				scripts.push({"bRunFirst": bRunFirst, "isInternal": true, "JS": js});
			}

			data = data.substr(0, matchScript.index) + data.substr(end+9);
		}

		return {'HTML': data, 'SCRIPT': scripts};
	},

	garbage: function(call, thisObject)
	{
		garbageCollectors.push({callback: call, context: thisObject});
	}
});


/* window pos functions */
BX.ext({
	GetDocElement: function (pDoc)
	{
		pDoc = pDoc || document;
		return (BX.browser.IsDoctype(pDoc) ? pDoc.documentElement : pDoc.body);
	},

	GetContext: function(node)
	{
		if (BX.type.isElementNode(node))
			return node.ownerDocument.parentWindow || node.ownerDocument.defaultView || window;
		else if (BX.type.isDomNode(node))
			return node.parentWindow || node.defaultView || window;
		else
			return window;
	},

	GetWindowInnerSize: function(pDoc)
	{
		var width, height;

		pDoc = pDoc || document;

		if (self.innerHeight) // all except Explorer
		{
			width = BX.GetContext(pDoc).innerWidth;
			height = BX.GetContext(pDoc).innerHeight;
		}
		else if (pDoc.documentElement && (pDoc.documentElement.clientHeight || pDoc.documentElement.clientWidth)) // Explorer 6 Strict Mode
		{
			width = pDoc.documentElement.clientWidth;
			height = pDoc.documentElement.clientHeight;
		}
		else if (pDoc.body) // other Explorers
		{
			width = pDoc.body.clientWidth;
			height = pDoc.body.clientHeight;
		}
		return {innerWidth : width, innerHeight : height};
	},

	GetWindowScrollPos: function(pDoc)
	{
		var left, top;

		pDoc = pDoc || document;

		if (self.pageYOffset) // all except Explorer
		{
			left = BX.GetContext(pDoc).pageXOffset;
			top = BX.GetContext(pDoc).pageYOffset;
		}
		else if (pDoc.documentElement && (pDoc.documentElement.scrollTop || pDoc.documentElement.scrollLeft)) // Explorer 6 Strict
		{
			left = pDoc.documentElement.scrollLeft;
			top = pDoc.documentElement.scrollTop;
		}
		else if (pDoc.body) // all other Explorers
		{
			left = pDoc.body.scrollLeft;
			top = pDoc.body.scrollTop;
		}
		return {scrollLeft : left, scrollTop : top};
	},

	GetWindowScrollSize: function(pDoc)
	{
		var width, height;
		if (!pDoc)
			pDoc = document;

		if ( (pDoc.compatMode && pDoc.compatMode == "CSS1Compat"))
		{
			width = pDoc.documentElement.scrollWidth;
			height = pDoc.documentElement.scrollHeight;
		}
		else
		{
			if (pDoc.body.scrollHeight > pDoc.body.offsetHeight)
				height = pDoc.body.scrollHeight;
			else
				height = pDoc.body.offsetHeight;

			if (pDoc.body.scrollWidth > pDoc.body.offsetWidth ||
				(pDoc.compatMode && pDoc.compatMode == "BackCompat") ||
				(pDoc.documentElement && !pDoc.documentElement.clientWidth)
			)
				width = pDoc.body.scrollWidth;
			else
				width = pDoc.body.offsetWidth;
		}
		return {scrollWidth : width, scrollHeight : height};
	},

	GetWindowSize: function(pDoc)
	{
		var innerSize = this.GetWindowInnerSize(pDoc);
		var scrollPos = this.GetWindowScrollPos(pDoc);
		var scrollSize = this.GetWindowScrollSize(pDoc);

		return  {
			innerWidth : innerSize.innerWidth, innerHeight : innerSize.innerHeight,
			scrollLeft : scrollPos.scrollLeft, scrollTop : scrollPos.scrollTop,
			scrollWidth : scrollSize.scrollWidth, scrollHeight : scrollSize.scrollHeight
		};
	},

	is_relative: function(el)
	{
		var p = BX.style(el, 'position');
		return p == 'relative' || p == 'absolute';
	},

	is_float: function(el)
	{
		var p = BX.style(el, 'float');
		return p == 'right' || p == 'left';
	},

	pos: function(el, bRelative)
	{
		var r = { top: 0, right: 0, bottom: 0, left: 0, width: 0, height: 0 };
		bRelative = !!bRelative;
		if (!el)
			return r;
		if (typeof (el.getBoundingClientRect) != "undefined" && el.ownerDocument == document && !bRelative)
		{
			var clientRect = el.getBoundingClientRect();
			var root = document.documentElement;
			var body = document.body;

			r.top = clientRect.top + root.scrollTop + body.scrollTop;
			r.left = clientRect.left + root.scrollLeft + body.scrollLeft;
			r.width = clientRect.right - clientRect.left;
			r.height = clientRect.bottom - clientRect.top;
			r.right = clientRect.right + root.scrollLeft + body.scrollLeft;
			r.bottom = clientRect.bottom + root.scrollTop + body.scrollTop;
		}
		else
		{
			var x = 0, y = 0, w = el.offsetWidth, h = el.offsetHeight;
			var first = true;
			for (; el != null; el = el.offsetParent)
			{
				if (!first && bRelative && BX.is_relative(el))
					break;

				x += el.offsetLeft;
				y += el.offsetTop;
				if (first)
				{
					first = false;
					continue;
				}

				var elBorderLeftWidth = parseInt(BX.style(el, 'border-left-width')),
					elBorderTopWidth = parseInt(BX.style(el, 'border-top-width'));

				if (!isNaN(elBorderLeftWidth) && elBorderLeftWidth > 0)
					x += elBorderLeftWidth;
				if (!isNaN(elBorderTopWidth) && elBorderTopWidth > 0)
					y += elBorderTopWidth;
			}

			r.top = y;
			r.left = x;
			r.width = w;
			r.height = h;
			r.right = r.left + w;
			r.bottom = r.top + h;
		}

		for (var i in r) r[i] = parseInt(r[i]);

		return r;
	},

	align: function(pos, w, h)
	{
		var pDoc = document;
		if (BX.type.isElementNode(pos))
		{
			pDoc = pos.ownerDocument;
			pos = BX.pos(pos);
		}

		var x = pos["left"], y = pos["bottom"];

		var scroll = BX.GetWindowScrollPos(pDoc);
		var size = BX.GetWindowInnerSize(pDoc);

		if((size.innerWidth + scroll.scrollLeft) - (pos["left"] + w) < 0)
		{
			if(pos["right"] - w >= 0 )
				x = pos["right"] - w;
			else
				x = scroll.scrollLeft;
		}

		if((size.innerHeight + scroll.scrollTop) - (pos["bottom"] + h) < 0)
		{
			if(pos["top"] - h >= 0)
				y = pos["top"] - h;
			else
				y = scroll.scrollTop;
		}

		return {'left':x, 'top':y};
	}
});

/* non-xhr loadings */
BX.ext({
	showWait: function(node, msg)
	{
		node = BX(node) || document.body || document.documentElement;
		msg = msg || BX.message('JS_CORE_LOADING');

		var container_id = node.id || Math.random();

		var obMsg = node.bxmsg = document.body.appendChild(BX.create('DIV', {
			props: {
				id: 'wait_' + container_id,
				className: 'bx-core-waitwindow'
			},
			text: msg
		}));

		setTimeout(BX.delegate(_adjustWait, node), 10);
		
		lastWait[lastWait.length] = obMsg;
		return obMsg;
	},

	closeWait: function(node, obMsg)
	{
		obMsg = obMsg || node && (node.bxmsg || BX('wait_' + node.id)) || lastWait.pop();
		if (obMsg && obMsg.parentNode)
		{
			for (var i=0,len=lastWait.length;i<len;i++)
			{
				if (obMsg == lastWait[i])
				{
					lastWait = BX.util.deleteFromArray(lastWait, i);
					break;
				}
			}

			obMsg.parentNode.removeChild(obMsg);
			if (node) node.bxmsg = null;
			BX.cleanNode(obMsg, true);
		}
	},

	loadScript: function(script, callback, doc)
	{
		if (!BX.isReady)
		{
			var _args = arguments;
			BX.ready(function() {
				BX.loadScript.apply(this, _args);
			});
			return;
		}

		doc = doc || document;

		if (BX.type.isString(script))
			script = [script];
		var _callback = function()
		{
			if (!callback)
				return;
			else if (BX.type.isFunction(callback))
				return callback();
			else
				return null;
		};
		var load_js = function(ind)
		{
			if (ind >= script.length)
				return _callback();

			var oHead = doc.getElementsByTagName("head")[0] || doc.documentElement;
			var oScript = doc.createElement('script');
			oScript.src = script[ind];

			var bLoaded = false;
			oScript.onload = oScript.onreadystatechange = function()
			{
				if (!bLoaded && (!oScript.readyState || oScript.readyState == "loaded" || oScript.readyState == "complete"))
				{
					bLoaded = true;
					setTimeout(function (){load_js(++ind);}, 50);

					oScript.onload = oScript.onreadystatechange = null;
					if (oHead && oScript.parentNode)
					{
						oHead.removeChild(oScript);
					}
				}
			};

			oHead.insertBefore(oScript, oHead.firstChild);
		};

		load_js(0);
	},

	loadCSS: function(arCSS, doc, win)
	{
		if (!BX.isReady)
		{
			var _args = arguments;
			BX.ready(function() {
				BX.loadCSS.apply(this, _args);
			});
			return;
		}

		if (BX.type.isString(arCSS))
		{
			var bSingle = true;
			arCSS = [arCSS];
		}
		var i, l = arCSS.length, pLnk = [];

		if (l == 0)
			return;

		doc = doc || document;
		win = win ||window;

		if (!win.bxhead)
		{
			var heads = doc.getElementsByTagName('HEAD');
			win.bxhead = heads[0];
		}

		if (!win.bxhead)
			return;

		for (i = 0; i < l; i++)
		{
			var lnk = document.createElement('LINK');
			lnk.href = arCSS[i];
			lnk.rel = 'stylesheet';
			lnk.type = 'text/css';
			win.bxhead.appendChild(lnk);
			pLnk.push(lnk);
		}

		if (bSingle)
			return lnk;

		return pLnk;
	}
});

BX.ext({
	reload: function(back_url, bAddClearCache)
	{
		if (back_url === true)
		{
			bAddClearCache = true;
			back_url = null;
		}

		var new_href = back_url || top.location.href;

		var hashpos = new_href.indexOf('#'), hash = '';

		if (hashpos != -1)
		{
			hash = new_href.substr(hashpos);
			new_href = new_href.substr(0, hashpos);
		}

		if (bAddClearCache && new_href.indexOf('clear_cache=Y') < 0)
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + 'clear_cache=Y';

		if (hash)
		{
			// hack for clearing cache in ajax mode components with history emulation
			if (bAddClearCache && (hash.substr(0, 5) == 'view/' || hash.substr(0, 6) == '#view/') && hash.indexOf('clear_cache%3DY') < 0)
				hash += (hash.indexOf('%3F') == -1 ? '%3F' : '%26') + 'clear_cache%3DY'

			new_href = new_href.replace(/(\?|\&)_r=[\d]*/, '');
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + '_r='+Math.round(Math.random()*10000) + hash;
		}

		top.location.href = new_href;
	},

	clearCache: function()
	{
		BX.showWait();
		BX.reload(true);
	}
});

/* ready */
if (document.addEventListener)
{
	__readyHandler = function()
	{
		document.removeEventListener("DOMContentLoaded", __readyHandler, false);
		BX.runReady();
	};
}
else if (document.attachEvent)
{
	__readyHandler = function()
	{
		if (document.readyState === "complete")
		{
			document.detachEvent("onreadystatechange", __readyHandler);
			BX.runReady();
		}
	};
}

// hack for IE
function doScrollCheck()
{
	if (BX.isReady)
		return;

	try {document.documentElement.doScroll("left");} catch( error ) {setTimeout(doScrollCheck, 1); return;}

	BX.runReady();
}
/* \ready */

/* garbage collector */
function Trash()
{
	var i,len;

	for (i = 0, len = garbageCollectors.length; i<len; i++)
	{
		garbageCollectors[i].callback.apply(garbageCollectors[i].context || window);

		try {
			delete garbageCollectors[i];
			garbageCollectors[i] = null;
		} catch (e) {}
	}

	for (i = 0, len = proxyList.length; i < len; i++)
	{
		try {
			delete proxyList[i];
			proxyList[i] = null;
		} catch (e) {}
	}
	
	try {BX.unbindAll();} catch(e) {}

	NODECACHE = null;
	garbageCollectors = null;
	proxyList = null;
	eventsList = null;
	readyList = null;
	deniedEvents = null;
	customEvents = null;
	__readyHandler = null;
}

function _adjustWait()
{
	if (!this.bxmsg) return;
	
	var arContainerPos = BX.pos(this),
		div_top = arContainerPos.top;

	if (div_top < BX.GetDocElement().scrollTop) 
		div_top = BX.GetDocElement().scrollTop + 5;

	this.bxmsg.style.top = (div_top + 5) + 'px';
	
	if (this == BX.GetDocElement())
	{
		this.bxmsg.style.right = '5px';
	}
	else
	{
		this.bxmsg.style.left = (arContainerPos.right - this.bxmsg.offsetWidth - 5) + 'px';
	}
}

if(window.attachEvent) // IE
	window.attachEvent("onunload", Trash);
else if(window.addEventListener) // Gecko / W3C
	window.addEventListener('unload', Trash, false);
else
	window.onunload = Trash;
/* \garbage collector */

// set empty ready handler
BX(BX.DoNothing);

window.BX = BX;
})(window)