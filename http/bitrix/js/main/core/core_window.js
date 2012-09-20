(function(window) {
if (BX.WindowManager) return;


/* windows manager */
BX.WindowManager = {
	_stack: [],
	_runtime_resize: {},
	_delta: 5,
	_delta_start: 1000,
	currently_loaded: null,

	settings_category: 'BX.WindowManager.9.5',

	register: function (w)
	{
		this.currently_loaded = null;
		var div = w.Get();

		div.style.zIndex = w.zIndex = this.GetZIndex();

		w.WM_REG_INDEX = this._stack.length;
		this._stack.push(w);

		if (this._stack.length < 2)
		{
			BX.bind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
		}
	},

	unregister: function (w)
	{
		if (null == w.WM_REG_INDEX)
			return;

		var _current;
		if (this._stack.length > 0)
		{
			while ((_current = this.__pop_stack()) != w)
			{
				if (!_current)
				{
					_current = null;
					break;
				}
			}

			if (this._stack.length <= 0)
			{
				this.enableKeyCheck();
			}

			return _current;
		}
		else
		{
			return null;
		}
	},

	__pop_stack: function(clean)
	{
		if (this._stack.length > 0)
		{
			var _current = this._stack.pop();
			_current.WM_REG_INDEX = null;
			BX.onCustomEvent(_current, 'onWindowUnRegister', [clean === true])

			return _current;
		}
		else
			return null;
	},

	clean: function()
	{
		while (this.__pop_stack(true)){};
		this._stack = null;
		this.disableKeyCheck();
	},

	Get: function()
	{
		if (this.currently_loaded)
			return this.currently_loaded;
		else if (this._stack.length > 0)
			return this._stack[this._stack.length-1];
		else
			return null;
	},

	setStartZIndex: function(value)
	{
		this._delta_start = value;
	},

	restoreStartZIndex: function()
	{
		this._delta_start = 1000;
	},

	GetZIndex: function()
	{
		var val =
			(null != (_current = this._stack[this._stack.length-1]))
			? parseInt(_current.Get().style.zIndex) + this._delta
			: this._delta_start;
		return val;
	},

	__get_check_url: function(url)
	{
		var pos = url.indexOf('?');
		return pos == -1 ? url : url.substring(0, pos);
	},

	saveWindowSize: function(url, params)
	{
		var check_url = this.__get_check_url(url);
		if (BX.userOptions)
		{
			BX.userOptions.save(this.settings_category, 'size_' + check_url, 'width', params.width);
			BX.userOptions.save(this.settings_category, 'size_' + check_url, 'height', params.height);
		}

		this._runtime_resize[check_url] = params;
	},

	saveWindowOptions: function(wnd_id, opts)
	{
		if (BX.userOptions)
		{
			for (var i in opts)
			{
				BX.userOptions.save(this.settings_category, 'options_' + wnd_id, i, opts[i]);
			}
		}
	},

	getRuntimeWindowSize: function(url)
	{
		return this._runtime_resize[this.__get_check_url(url)];
	},

	disableKeyCheck: function()
	{
		BX.unbind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
	},

	enableKeyCheck: function()
	{
		BX.bind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
	},

	__checkKeyPress: function(e)
	{
		if (null == e)
			e = window.event;

		if (e.keyCode == 27)
		{
			var wnd = BX.WindowManager.Get();
			if (wnd && !wnd.unclosable) wnd.Close();
		}
	}
}

BX.garbage(BX.WindowManager.clean, BX.WindowManager);

/* base button class */
BX.CWindowButton = function(params)
{
	if (params.btn)
	{
		this.btn = params.btn;
		this.parentWindow = params.parentWindow;

		if (/save|apply/i.test(this.btn.name))
		{
			BX.bind(this.btn, 'click', BX.delegate(this.disableUntilError, this));
		}
	}
	else
	{
		this.title = params.title; // html value attr
		this.hint = params.hint; // html title attr
		this.id = params.id; // html name and id attrs
		this.name = params.name // html name or value attrs when id and title 're absent

		this.action = params.action;
		this.onclick = params.onclick;

		// you can override button creation method
		if (params.Button && BX.type.isFunction(params.Button))
			this.Button = params.Button;

		this.btn = null;
	}
}

BX.CWindowButton.prototype.disable = function(){if (this.btn) this.btn.disabled = true;};
BX.CWindowButton.prototype.enable = function(){if (this.btn) this.btn.disabled = false;};

BX.CWindowButton.prototype.emulate = function()
{
	if (this.btn && this.btn.disabled)
		return;

	var act =
		this.action
		? BX.delegate(this.action, this)
		: (
			this.onclick
			? this.onclick
			: (
				this.btn
				? this.btn.getAttribute('onclick')
				: ''
			)
		);

	if (act)
	{
		setTimeout(act, 50);
		if (this.btn && /save|apply/i.test(this.btn.name) && !this.action)
		{
			this.disableUntilError();
		}
	}
}

BX.CWindowButton.prototype.Button = function(parentWindow)
{
	this.parentWindow = parentWindow;

	var btn = {
		props: {
			'type': 'button',
			'name': this.id ? this.id : this.name,
			'value': this.title ? this.title : this.name,
			'id': this.id
		}
	};

	if (this.hint)
		btn.props.title = this.hint;

	if (this.action)
	{
		btn.events = {
			'click': BX.delegate(this.action, this)
		};
	}
	else if (this.onclick)
	{
		if (BX.browser.IsIE())
		{
			btn.events = {
				'click': BX.delegate(function() {eval(this.onclick)}, this)
			};
		}
		else
		{
			btn.attrs = {
				'onclick': this.onclick
			};
		}
	}

	this.btn = BX.create('INPUT', btn);

	return this.btn;
}

BX.CWindowButton.prototype.disableUntilError = function() {
	this.disable();
	if (!this.__window_error_handler_set)
	{
		BX.addCustomEvent(this.parentWindow, 'onWindowError', BX.delegate(this.enable, this));
		this.__window_error_handler_set = true;
	}
}

/* base window class */
BX.CWindow = function(div, type)
{
	this.DIV = div || document.createElement('DIV');

	this.SETTINGS = {
		resizable: false,
		min_height: 0,
		min_width: 0,
		draggable: false,
		drag_restrict: true,
		resize_restrict: true
	};

	this.ELEMENTS = {
		draggable: [],
		resizer: [],
		close: []
	};

	this.type = type == 'float' ? 'float' : 'dialog';

	BX.adjust(this.DIV, {
		props: {
			className: 'bx-core-window'
		},
		style: {
			'zIndex': 0,
			'position': 'absolute',
			'display': 'none',
			'top': '0px',
			'left': '0px',
			'height': '100px',
			'width': '100px'
		}
	});

	this.isOpen = false;

	BX.addCustomEvent(this, 'onWindowRegister', BX.delegate(this.onRegister, this));
	BX.addCustomEvent(this, 'onWindowUnRegister', BX.delegate(this.onUnRegister, this));

	this.MOUSEOVER = null;
	BX.bind(this.DIV, 'mouseover', BX.delegate(this.__set_msover, this));
	BX.bind(this.DIV, 'mouseout', BX.delegate(this.__unset_msover, this));

	BX.ready(BX.delegate(function() {
		document.body.appendChild(this.DIV);
	}, this));
}

BX.CWindow.prototype.Get = function () {return this.DIV};
BX.CWindow.prototype.visible = function() {return this.isOpen;}

BX.CWindow.prototype.Show = function(bNotRegister)
{
	this.DIV.style.display = 'block';

	if (!bNotRegister)
	{
		BX.WindowManager.register(this);
		BX.onCustomEvent(this, 'onWindowRegister');
	}
}

BX.CWindow.prototype.Hide = function()
{
	BX.WindowManager.unregister(this);
	this.DIV.style.display = 'none';
}

BX.CWindow.prototype.onRegister = function()
{
	this.isOpen = true;
}

BX.CWindow.prototype.onUnRegister = function(clean)
{
	this.isOpen = false;

	if (clean || (this.PARAMS && this.PARAMS.content_url))
	{
		if (clean) {BX.onCustomEvent(this, 'onWindowClose', [this, true]);}

		if (this.DIV.parentNode)
			this.DIV.parentNode.removeChild(this.DIV);
	}
	else
	{
		this.DIV.style.display = 'none';
	}
}

BX.CWindow.prototype.CloseDialog = // compatibility
BX.CWindow.prototype.Close = function(bImmediately)
{
	BX.onCustomEvent(this, 'onBeforeWindowClose', [this]);
	if (bImmediately !== true)
	{
		if (this.denyClose) return false;
	}

	BX.onCustomEvent(this, 'onWindowClose', [this]);

	//this crashes vis editor in ie via onWindowResizeExt event handler
	//if (this.bExpanded) this.__expand();
	// alternative version:
	if (this.bExpanded)
	{
		var pDocElement = BX.GetDocElement();
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));
		pDocElement.style.overflow = this.__expand_settings.overflow;
	}

	BX.WindowManager.unregister(this);
}

BX.CWindow.prototype.SetResize = function(elem)
{
	elem.style.cursor = 'se-resize';
	BX.bind(elem, 'mousedown', BX.proxy(this.__startResize, this));

	this.ELEMENTS.resizer.push(elem);
	this.SETTINGS.resizable = true;
}

BX.CWindow.prototype.SetExpand = function(elem, event_name)
{
	event_name = event_name || 'click';
	BX.bind(elem, event_name, BX.proxy(this.__expand, this));
}

BX.CWindow.prototype.__expand_onresize = function()
{
	var windowSize = BX.GetWindowInnerSize();
	this.DIV.style.width = windowSize.innerWidth + "px";
	this.DIV.style.height = windowSize.innerHeight + "px";

	BX.onCustomEvent(this, 'onWindowResize');
}

BX.CWindow.prototype.__expand = function()
{
	var pDocElement = BX.GetDocElement();

	if (!this.bExpanded)
	{
		var wndScroll = BX.GetWindowScrollPos();

		this.__expand_settings = {
			resizable: this.SETTINGS.resizable,
			draggable: this.SETTINGS.draggable,
			width: this.DIV.style.width,
			height: this.DIV.style.height,
			left: this.DIV.style.left,
			top: this.DIV.style.top,
			scroll: wndScroll.scrollTop,
			overflow: BX.style(pDocElement, 'overflow')
		}

		this.SETTINGS.resizable = false;
		this.SETTINGS.draggable = false;

		pDocElement.style.overflow = 'hidden';

		var wndSize = BX.GetWindowInnerSize();

		pDocElement.scrollTop = 0;

		this.DIV.style.top = '0px';
		this.DIV.style.left = '0px';

		this.DIV.style.width = wndSize.innerWidth + 'px';
		this.DIV.style.height = wndSize.innerHeight + 'px';

		this.bExpanded = true;

		BX.onCustomEvent(this, 'onWindowExpand');
		BX.onCustomEvent(this, 'onWindowResize');

		BX.bind(window, 'resize', BX.proxy(this.__expand_onresize, this));

	}
	else
	{
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));

		this.SETTINGS.resizable = this.__expand_settings.resizable;
		this.SETTINGS.draggable = this.__expand_settings.draggable;

		pDocElement.style.overflow = this.__expand_settings.overflow;

		this.DIV.style.top = this.__expand_settings.top;
		this.DIV.style.left = this.__expand_settings.left;
		this.DIV.style.width = this.__expand_settings.width;
		this.DIV.style.height = this.__expand_settings.height;

		pDocElement.scrollTop = this.__expand_settings.scroll;

		this.bExpanded = false;

		BX.onCustomEvent(this, 'onWindowNarrow');
		BX.onCustomEvent(this, 'onWindowResize');

	}
}

BX.CWindow.prototype.Resize = function(x, y)
{
	var new_width = Math.max(x - this.pos.left + this.dx, this.SETTINGS.min_width);
	var new_height = Math.max(y - this.pos.top + this.dy, this.SETTINGS.min_height);

	if (this.SETTINGS.resize_restrict)
	{
		var scrollSize = BX.GetWindowScrollSize();

		if (this.pos.left + new_width > scrollSize.scrollWidth - this.dw)
			new_width = scrollSize.scrollWidth - this.pos.left - this.dw;
	}

	this.DIV.style.width = new_width + 'px';
	this.DIV.style.height = new_height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
}

BX.CWindow.prototype.__startResize = function(e)
{
	if (!this.SETTINGS.resizable)
		return false;

	if(!e) e = window.event;

	this.wndSize = BX.GetWindowScrollPos();
	this.wndSize.innerWidth = BX.GetWindowInnerSize().innerWidth;

	this.pos = BX.pos(this.DIV);

	this.x = e.clientX + this.wndSize.scrollLeft;
	this.y = e.clientY + this.wndSize.scrollTop;

	this.dx = this.pos.left + this.pos.width - this.x;
	this.dy = this.pos.top + this.pos.height - this.y;
	this.dw = this.pos.width - parseInt(this.DIV.style.width);

	BX.bind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopResize, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'se-resize';

	BX.onCustomEvent(this, 'onWindowResizeStart');
}

BX.CWindow.prototype.__moveResize = function(e)
{
	if(!e) e = window.event;

	var windowScroll = BX.GetWindowScrollPos();

	var x = e.clientX + windowScroll.scrollLeft;
	var y = e.clientY + windowScroll.scrollTop;

	if(this.x == x && this.y == y)
		return;

	this.Resize(x, y);

	this.x = x;
	this.y = y;
}

BX.CWindow.prototype.__stopResize = function()
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.unbind(document, "mouseup", BX.proxy(this.__stopResize, this));

	document.onmousedown = null;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = '';
	b.style.cursor = '';

	BX.onCustomEvent(this, 'onWindowResizeFinished')
}

BX.CWindow.prototype.SetClose = function(elem)
{
	BX.bind(elem, 'click', BX.proxy(this.Close, this));
	this.ELEMENTS.close.push(elem);
}

BX.CWindow.prototype.SetDraggable = function(elem)
{
	BX.bind(elem, 'mousedown', BX.proxy(this.__startDrag, this));

	elem.style.cursor = 'move';

	this.ELEMENTS.draggable.push(elem);
	this.SETTINGS.draggable = true;
}

BX.CWindow.prototype.Move = function(x, y)
{
	var dxShadow = 1; // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	var left = parseInt(this.DIV.style.left)+x;
	var top = parseInt(this.DIV.style.top)+y;

	if (this.SETTINGS.drag_restrict)
	{
		//Left side
		if (left < 0)
			left = 0;

		//Right side
		var scrollSize = BX.GetWindowScrollSize();
		var floatWidth = this.DIV.offsetWidth;
			floatHeight = this.DIV.offsetHeight;

		if (left > (scrollSize.scrollWidth - floatWidth - dxShadow))
			left = scrollSize.scrollWidth - floatWidth - dxShadow;

		if (top > (scrollSize.scrollHeight - floatHeight - dxShadow))
			top = scrollSize.scrollHeight - floatHeight - dxShadow;

		//Top side
		if (top < 0)
			top = 0;
	}

	this.DIV.style.left = left+'px';
	this.DIV.style.top = top+'px';

	//this.AdjustShadow(div);
}

BX.CWindow.prototype.__startDrag = function(e)
{
	if (!this.SETTINGS.draggable)
		return false;

	if(!e) e = window.event;

	this.x = e.clientX + document.body.scrollLeft;
	this.y = e.clientY + document.body.scrollTop;

	this.__bWasDragged = false;
	BX.bind(document, "mousemove", BX.proxy(this.__moveDrag, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopDrag, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'move';
	return BX.PreventDefault(e);
}

BX.CWindow.prototype.__moveDrag = function(e)
{
	if(!e) e = window.event;

	var x = e.clientX + document.body.scrollLeft;
	var y = e.clientY + document.body.scrollTop;

	if(this.x == x && this.y == y)
		return;

	this.Move((x - this.x), (y - this.y));
	this.x = x;
	this.y = y;

	if (!this.__bWasDragged)
	{
		BX.onCustomEvent(this, 'onWindowDragStart');
		this.__bWasDragged = true;
		BX.bind(BX.proxy_context, "click", BX.PreventDefault);
	}

	BX.onCustomEvent(this, 'onWindowDrag');
}

BX.CWindow.prototype.__stopDrag = function(e)
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.__moveDrag, this));
	BX.unbind(document, "mouseup", BX.proxy(this.__stopDrag, this));

	document.onmousedown = null;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = '';
	b.style.cursor = '';

	if (this.__bWasDragged)
	{
		BX.onCustomEvent(this, 'onWindowDragFinished');
		var _proxy_context = BX.proxy_context;
		setTimeout(function(){BX.unbind(_proxy_context, "click", BX.PreventDefault)}, 100);
		this.__bWasDragged = false;
	}
	return BX.PreventDefault(e);
}

BX.CWindow.prototype.DenyClose = function()
{
	this.denyClose = true;
}

BX.CWindow.prototype.AllowClose = function()
{
	this.denyClose = false;
}

BX.CWindow.prototype.ShowError = function(str)
{
	BX.onCustomEvent(this, 'onWindowError', [str]);

	if (this._wait)
		BX.closeWait(this._wait);

	alert(str);
}

BX.CWindow.prototype.__set_msover = function() {this.MOUSEOVER = true;}
BX.CWindow.prototype.__unset_msover = function() {this.MOUSEOVER = false;}

/* dialog window class extends window class */
BX.CWindowDialog = function() {
	arguments[1] = 'dialog';
	BX.CWindowDialog.superclass.constructor.apply(this, arguments);

	this.DIV.style.top = '10px';
	this.OVERLAY = null;
}
BX.extend(BX.CWindowDialog, BX.CWindow);

BX.CWindowDialog.prototype.__resizeOverlay = function()
{
	var windowSize = BX.GetWindowScrollSize();
	this.OVERLAY.style.width = windowSize.scrollWidth + "px";
}

BX.CWindowDialog.prototype.CreateOverlay = function(zIndex)
{
	if (null == this.OVERLAY)
	{
		var windowSize = BX.GetWindowScrollSize();
		
		/*
		this.OVERLAY = document.body.appendChild(BX.create("DIV", {
			style: {
				position: 'absolute',
				top: '0px',
				left: '0px',
				zIndex: zIndex || (parseInt(this.DIV.style.zIndex)-2),
				width: windowSize.scrollWidth + "px",
				height: windowSize.scrollHeight + "px"
			}
		}));
		*/
		
                // fixed by me
		var aa = BX.create("DIV", {
			style: {
				position: 'absolute',
				top: '0px',
				left: '0px',
				zIndex: zIndex || (parseInt(this.DIV.style.zIndex)-2),
				width: windowSize.scrollWidth + "px",
				height: windowSize.scrollHeight + "px"
			}
		});
		document.body.appendChild(aa);
		this.OVERLAY = aa;
	}

	return this.OVERLAY;
}

BX.CWindowDialog.prototype.Show = function()
{
	BX.CWindowDialog.superclass.Show.apply(this, arguments);

	this.CreateOverlay();

	this.OVERLAY.style.display = 'block';
	this.OVERLAY.style.zIndex = parseInt(this.DIV.style.zIndex)-2;

	BX.unbind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
	BX.bind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
}

BX.CWindowDialog.prototype.onUnRegister = function(clean)
{
	BX.CWindowDialog.superclass.onUnRegister.apply(this, arguments);

	if (this.clean)
	{
		if (this.OVERLAY.parentNode)
			this.OVERLAY.parentNode.removeChild(this.OVERLAY);
	}
	else
	{
		this.OVERLAY.style.display = 'none';
	}

	BX.unbind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
}

/* standard bitrix dialog extends BX.CWindowDialog */
/*
	arParams = {
		(
			title: 'dialog title',
			head: 'head block html',
			content: 'dialog content',
			icon: 'head icon classname or filename',

			resize_id: 'some id to save resize information'// useless if resizable = false
		)
		or
		(
			content_url: url to content load
				loaded content scripts can use BX.WindowManager.Get() to get access to the current window object
		)

		height: window_height_in_pixels,
		width: window_width_in_pixels,

		draggable: true|false,
		resizable: true|false,

		min_height: min_window_height_in_pixels, // useless if resizable = false
		min_width: min_window_width_in_pixels, // useless if resizable = false

		buttons: [
			'html_code',
			BX.CDialog.btnSave, BX.CDialog.btnCancel, BX.CDialog.btnClose
		]
	}
*/
BX.CDialog = function(arParams)
{
	BX.CDialog.superclass.constructor.apply(this);

	this.PARAMS = arParams || {};

	for (var i in this.defaultParams)
	{
		if (typeof this.PARAMS[i] == 'undefined')
			this.PARAMS[i] = this.defaultParams[i];
	}

	this.PARAMS.width = (!isNaN(parseInt(this.PARAMS.width)))
		? this.PARAMS.width
		: this.defaultParams['width'];
	this.PARAMS.height = (!isNaN(parseInt(this.PARAMS.height)))
		? this.PARAMS.height
		: this.defaultParams['height'];

	if (this.PARAMS.resize_id || this.PARAMS.content_url)
	{
		var arSize = BX.WindowManager.getRuntimeWindowSize(this.PARAMS.resize_id || this.PARAMS.content_url);
		if (arSize)
		{
			this.PARAMS.width = arSize.width;
			this.PARAMS.height = arSize.height;
		}
	}

	BX.addClass(this.DIV, 'bx-core-dialog');

	this.PARTS = {};

	this.DIV.style.height = null;
	this.DIV.style.width = null;

	this.PARTS.CONTENT = this.DIV.appendChild(BX.create('DIV', {
		props: {className: 'dialog-center'}
	}));


	this.PARTS.CONTENT_DATA = this.PARTS.CONTENT.appendChild(BX.create('DIV', {
		props: {className: 'bx-core-dialog-content'}
	}));

	BX.adjust(this.PARTS.CONTENT_DATA, {
		style: {
			height: this.PARAMS.height + 'px',
			width: this.PARAMS.width + 'px'
		}
	});

	this.PARTS.HEAD = this.PARTS.CONTENT_DATA.appendChild(BX.create('DIV', {
		props: {
			className: 'bx-core-dialog-head'
		},
		children: [
			BX.create('DIV', {
				props: {className: 'bx-core-dialog-head-content' + (this.PARAMS.icon ? ' ' + this.PARAMS.icon : '')}
			})
		]
	}));

	this.SetHead(this.PARAMS.head);
	this.SetContent(this.PARAMS.content);

	this.PARTS.TITLEBAR = this.DIV.appendChild(BX.create('DIV', {props: {
			className: 'dialog-head'
		},
		html: '<div class="l"><div class="r"><div class="c"><span>'+this.PARAMS.title+'</span></div></div></div>'
	}));
	this.PARTS.TITLE_CONTAINER = this.PARTS.TITLEBAR.firstChild.firstChild.firstChild.firstChild;
	this.SetTitle(this.PARAMS.title);

	this.PARTS.TITLEBAR_ICONS = this.DIV.appendChild(BX.create('DIV', {
		props: {
			className: 'dialog-head-icons'
		},
		children: (this.PARAMS.resizable ? [
			BX.create('A', {props: {className: 'bx-icon-expand', title: BX.message('JS_CORE_WINDOW_EXPAND')}}),
			BX.create('A', {props: {className: 'bx-icon-close', title: BX.message('JS_CORE_WINDOW_CLOSE')}})
		] : [
			BX.create('A', {props: {className: 'bx-icon-close', title: BX.message('JS_CORE_WINDOW_CLOSE')}})
		])
	}));

	this.SetClose(this.PARTS.TITLEBAR_ICONS.lastChild);

	if (this.PARAMS.resizable)
	{
		this.SetExpand(this.PARTS.TITLEBAR_ICONS.firstChild);
		BX.addCustomEvent(this, 'onWindowExpand', BX.proxy(this.__onexpand, this));
		BX.addCustomEvent(this, 'onWindowNarrow', BX.proxy(this.__onexpand, this));
	}


	this.PARTS.FOOT = this.DIV.appendChild(BX.create('DIV', {props: {
			className: 'dialog-foot'
		},
		html: '<div class="l"><div class="r"><div class="c"><img src="/bitrix/js/main/core/images/line.png" height="1" width="90%" border="0" style="position: absolute; top: 0; left: 0;" /><span></span></div></div></div>'
	}));

	this.PARTS.BUTTONS_CONTAINER = this.PARTS.FOOT.firstChild.firstChild.firstChild.lastChild;
	BX.adjust(this.PARTS.BUTTONS_CONTAINER, {
		children: this.ShowButtons()
	});

	if (this.PARAMS.draggable)
		this.SetDraggable(this.PARTS.TITLEBAR);

	this.SetExpand(this.PARTS.TITLEBAR.firstChild, 'dblclick');

	if (this.PARAMS.resizable)
	{
		this.PARTS.RESIZER = this.DIV.appendChild(BX.create('DIV', {
			props: {className: 'bx-core-resizer'}
		}));

		this.SetResize(this.PARTS.RESIZER);

		this.SETTINGS.min_width = this.PARAMS.min_width;
		this.SETTINGS.min_height = this.PARAMS.min_height;
	}
}
BX.extend(BX.CDialog, BX.CWindowDialog);

BX.CDialog.prototype.defaultParams = {
	width: 700,
	height: 400,
	min_width: 500,
	min_height: 300,

	resizable: true,
	draggable: true,

	title: '',
	icon: ''
}

BX.CDialog.prototype.__expandGetSize = function()
{
	var pDocElement = BX.GetDocElement();
	pDocElement.style.overflow = 'hidden';

	var wndSize = BX.GetWindowInnerSize();

	pDocElement.scrollTop = 0;

	this.DIV.style.top = '-' + this.dxShadow + 'px';
	this.DIV.style.left = '-' + this.dxShadow + 'px';

	return {
		width: (wndSize.innerWidth - parseInt(BX.style(this.PARTS.CONTENT, 'padding-right'))),
		height: (wndSize.innerHeight - this.PARTS.TITLEBAR.offsetHeight - (this.PARTS.FOOT.offsetHeight) + this.dxShadow)
	};
}

BX.CDialog.prototype.__expand = function()
{
	var pDocElement = BX.GetDocElement();
	this.dxShadow = 7;

	if (!this.bExpanded)
	{
		var wndScroll = BX.GetWindowScrollPos();

		this.__expand_settings = {
			resizable: this.SETTINGS.resizable,
			draggable: this.SETTINGS.draggable,
			width: this.PARTS.CONTENT_DATA.style.width,
			height: this.PARTS.CONTENT_DATA.style.height,
			left: this.DIV.style.left,
			top: this.DIV.style.top,
			scroll: wndScroll.scrollTop,
			overflow: BX.style(pDocElement, 'overflow')
		}

		this.SETTINGS.resizable = false;
		this.SETTINGS.draggable = false;

		var pos = this.__expandGetSize();

		this.PARTS.CONTENT_DATA.style.width = pos.width + 'px';
		this.PARTS.CONTENT_DATA.style.height = pos.height + 'px';

		this.bExpanded = true;

		BX.onCustomEvent(this, 'onWindowExpand');
		BX.onCustomEvent(this, 'onWindowResize');
		BX.onCustomEvent(this, 'onWindowResizeExt', [{'width': pos.width, 'height': pos.height}]);

		BX.bind(window, 'resize', BX.proxy(this.__expand_onresize, this));
	}
	else
	{
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));

		this.SETTINGS.resizable = this.__expand_settings.resizable;
		this.SETTINGS.draggable = this.__expand_settings.draggable;

		pDocElement.style.overflow = this.__expand_settings.overflow;

		this.DIV.style.top = this.__expand_settings.top;
		this.DIV.style.left = this.__expand_settings.left;
		this.PARTS.CONTENT_DATA.style.width = this.__expand_settings.width;
		this.PARTS.CONTENT_DATA.style.height = this.__expand_settings.height;
		pDocElement.scrollTop = this.__expand_settings.scroll;
		this.bExpanded = false;

		BX.onCustomEvent(this, 'onWindowNarrow');
		BX.onCustomEvent(this, 'onWindowResize');
		BX.onCustomEvent(this, 'onWindowResizeExt', [{'width': parseInt(this.__expand_settings.width), 'height': parseInt(this.__expand_settings.height)}]);
	}
}

BX.CDialog.prototype.__expand_onresize = function()
{
	var pos = this.__expandGetSize();

	this.PARTS.CONTENT_DATA.style.width = pos.width + 'px';
	this.PARTS.CONTENT_DATA.style.height = pos.height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [pos]);
}

BX.CDialog.prototype.__onexpand = function()
{
	var ob = this.PARTS.TITLEBAR_ICONS.firstChild;
	ob.className = BX.toggle(ob.className, ['bx-icon-expand', 'bx-icon-narrow']);
	ob.title = BX.toggle(ob.title, [BX.message('JS_CORE_WINDOW_EXPAND'), BX.message('JS_CORE_WINDOW_NARROW')]);

	if (this.PARTS.RESIZER)
	{
		this.PARTS.RESIZER.style.display = this.bExpanded ? 'none' : 'block';
	}
}


BX.CDialog.prototype.__startResize = function(e)
{
	if (!this.SETTINGS.resizable)
		return false;

	if(!e) e = window.event;

	this.wndSize = BX.GetWindowScrollPos();
	this.wndSize.innerWidth = BX.GetWindowInnerSize().innerWidth;

	this.pos = BX.pos(this.PARTS.CONTENT_DATA);

	this.x = e.clientX + this.wndSize.scrollLeft;
	this.y = e.clientY + this.wndSize.scrollTop;

	this.dx = this.pos.left + this.pos.width - this.x;
	this.dy = this.pos.top + this.pos.height - this.y;
	this.dw = this.pos.width - parseInt(this.PARTS.CONTENT_DATA.style.width) + parseInt(BX.style(this.PARTS.CONTENT, 'padding-right'));

	BX.bind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopResize, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'se-resize';

	BX.onCustomEvent(this, 'onWindowResizeStart');
}

BX.CDialog.prototype.Resize = function(x, y)
{
	var new_width = Math.max(x - this.pos.left + this.dx, this.SETTINGS.min_width);
	var new_height = Math.max(y - this.pos.top + this.dy, this.SETTINGS.min_height);

	if (this.SETTINGS.resize_restrict)
	{
		var scrollSize = BX.GetWindowScrollSize();

		if (this.pos.left + new_width > scrollSize.scrollWidth - this.dw)
			new_width = scrollSize.scrollWidth - this.pos.left - this.dw;
	}

	this.PARTS.CONTENT_DATA.style.width = new_width + 'px';
	this.PARTS.CONTENT_DATA.style.height = new_height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [{'height': new_height, 'width': new_width}]);
}

BX.CDialog.prototype.SetSize = function(obSize)
{
	this.PARTS.CONTENT_DATA.style.width = obSize.width + 'px';
	this.PARTS.CONTENT_DATA.style.height = obSize.height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [obSize]);
}

BX.CDialog.prototype.GetParameters = function(form_name)
{
	var form = this.GetForm();

	if(!form)
		return "";

	var i, s = "";
	var n = form.elements.length;

	var delim = '';
	for(i=0; i<n; i++)
	{
		if (s != '') delim = '&';
		var el = form.elements[i];
		if (el.disabled)
			continue;

		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
				if (null == form_name && el.name.substr(el.name.length-4) == '_alt' && form.elements[el.name.substr(0, el.name.length-4)])
					break;
				s += delim + el.name + '=' + BX.util.urlencode(el.value);
				break;
			case 'radio':
				if(el.checked)
					s += delim + el.name + '=' + BX.util.urlencode(el.value);
				break;
			case 'checkbox':
				s += delim + el.name + '=' + BX.util.urlencode(el.checked ? 'Y':'N');
				break;
			case 'select-one':
				var val = "";
				if (null == form_name && form.elements[el.name + '_alt'] && el.selectedIndex == 0)
					val = form.elements[el.name+'_alt'].value;
				else
					val = el.value;
				s += delim + el.name + '=' + BX.util.urlencode(val);
				break;
			case 'select-multiple':
				var j, bAdded = false;
				var l = el.options.length;
				for (j=0; j<l; j++)
				{
					if (el.options[j].selected)
					{
						s += delim + el.name + '=' + BX.util.urlencode(el.options[j].value);
						bAdded = true;
					}
				}
				if (!bAdded)
					s += delim + el.name + '=';
				break;
			default:
				break;
		}
	}

	return s;
};

BX.CDialog.prototype.PostParameters = function(params)
{
	var url = this.PARAMS.content_url;
	if (null != params)
	{
		index = url.indexOf('?')
		if (index == -1)
			url += '?' + params;
		else
			url = url.substring(0, index) + '?' + params + "&" + url.substring(index+1);
	}

	BX.showWait();
	BX.ajax.post(url, this.GetParameters(), BX.delegate(function(result) {
		BX.closeWait();
		this.ClearButtons(); // buttons are appended during form reload, so we should clear footer
		this.SetContent(result);
		this.Show(true);
	}, this));
}

BX.CDialog.prototype.Submit = function(params)
{
	var FORM = this.GetForm();
	if (FORM)
	{
		FORM.onsubmit = null;

		if (!FORM.method) FORM.method = 'POST';
		if (!FORM.action)
		{
			var url = this.PARAMS.content_url;
			if (null != params)
			{
				index = url.indexOf('?')
				if (index == -1)
					url += '?' + params;
				else
					url = url.substring(0, index) + '?' + params + "&" + url.substring(index+1);
			}

			FORM.action = url;
		}

		this._wait = BX.showWait();
		BX.ajax.submit(FORM);
	}
	else
	{
		alert('no form registered!');
	}
}

BX.CDialog.prototype.GetForm = function()
{
	if (null == this.__form)
	{
		var forms = this.PARTS.CONTENT_DATA.getElementsByTagName('FORM');
		this.__form = forms[0] ? forms[0] : null;
	}

	return this.__form;
}

BX.CDialog.prototype._checkButton = function(btn)
{
	var arCustomButtons = ['btnSave', 'btnCancel', 'btnClose'];

	for (var i = 0; i < arCustomButtons.length; i++)
	{
		if (this[arCustomButtons[i]] && (btn == this[arCustomButtons[i]]))
			return arCustomButtons[i];
	}

	return false;
}

BX.CDialog.prototype.ShowButtons = function()
{
	var result = [];
	if (this.PARAMS.buttons)
	{
		if (this.PARAMS.buttons.title) this.PARAMS.buttons = [this.PARAMS.buttons];

		for (var i=0, len=this.PARAMS.buttons.length; i<len; i++)
		{
			if (BX.type.isNotEmptyString(this.PARAMS.buttons[i]))
			{
				result.push(this.PARAMS.buttons[i]);
			}
			else if (this.PARAMS.buttons[i])
			{
				//if (!(this.PARAMS.buttons[i] instanceof BX.CWindowButton))
				if (!BX.is_subclass_of(this.PARAMS.buttons[i], BX.CWindowButton))
				{
					var b = this._checkButton(this.PARAMS.buttons[i]); // hack to set links to real CWindowButton object in btnSave etc;
					this.PARAMS.buttons[i] = new BX.CWindowButton(this.PARAMS.buttons[i]);
					if (b) this[b] = this.PARAMS.buttons[i];
				}

				result.push(this.PARAMS.buttons[i].Button(this));
			}
		}
	}

	return result;
}

BX.CDialog.prototype.SetTitle = function(title)
{
	this.PARAMS.title = title;
	BX.cleanNode(this.PARTS.TITLE_CONTAINER).appendChild(document.createTextNode(this.PARAMS.title));
}

BX.CDialog.prototype.SetHead = function(head)
{
	this.PARAMS.head = head;
	this.PARTS.HEAD.firstChild.innerHTML = head || "&nbsp;";
	this.PARTS.HEAD.style.display = this.PARAMS.head ? 'block' : 'none';
	this.adjustSize();
}

BX.CDialog.prototype.__adjustHeadToIcon = function()
{
	if (!this.PARTS.HEAD.firstChild.offsetHeight)
	{
		setTimeout(BX.delegate(this.__adjustHeadToIcon, this), 50);
	}
	else
	{
		if (this.icon_image && this.icon_image.height && this.icon_image.height > this.PARTS.HEAD.firstChild.offsetHeight - 5)
		{
			this.PARTS.HEAD.firstChild.style.height = this.icon_image.height + 5 + 'px';
			this.adjustSize();
		}

		this.icon_image.onload = null;
		this.icon_image = null;
	}
}

BX.CDialog.prototype.SetIcon = function(icon_class)
{
	if (this.PARAMS.icon != icon_class)
	{
		if (this.PARAMS.icon)
			BX.removeClass(this.PARTS.HEAD.firstChild, this.PARAMS.icon);

		this.PARAMS.icon = icon_class

		if (this.PARAMS.icon)
		{
			BX.addClass(this.PARTS.HEAD.firstChild, this.PARAMS.icon);

			var icon_file = (BX.style(this.PARTS.HEAD.firstChild, 'background-image') || BX.style(this.PARTS.HEAD.firstChild, 'backgroundImage')).replace('url("', '').replace('")', '');
			if (BX.type.isNotEmptyString(icon_file) && icon_file != 'none')
			{
				this.icon_image = new Image();
				this.icon_image.onload = BX.delegate(this.__adjustHeadToIcon, this);
				this.icon_image.src = icon_file;
			}
		}
	}
	this.adjustSize();
}

BX.CDialog.prototype.SetIconFile = function(icon_file)
{
	this.icon_image = new Image();
	this.icon_image.onload = BX.delegate(this.__adjustHeadToIcon, this);
	this.icon_image.src = icon_file;

	BX.adjust(this.PARTS.HEAD.firstChild, {style: {backgroundImage: 'url(' + icon_file + ')', backgroundPosition: 'right 9px'/*'99% center'*/}});
	this.adjustSize();
}

/*
BUTTON: {
	title: 'title',
	'action': function executed in window object context
}
BX.CDialog.btnSave || BX.CDialog.btnCancel - standard buttons
*/

BX.CDialog.prototype.SetButtons = function(a)
{
	if (BX.type.isString(a))
	{
		if (a.length > 0)
		{
			this.PARTS.BUTTONS_CONTAINER.innerHTML += a;

			var btns = this.PARTS.BUTTONS_CONTAINER.getElementsByTagName('INPUT');
			if (btns.length > 0)
			{
				this.PARAMS.buttons = [];
				for (var i = 0; i < btns.length; i++)
				{
					this.PARAMS.buttons.push(new BX.CWindowButton({btn: btns[i], parentWindow: this}));
				}
			}
		}
	}
	else
	{
		this.PARAMS.buttons = a;
		BX.adjust(this.PARTS.BUTTONS_CONTAINER, {
			children: this.ShowButtons()
		});
	}
	this.adjustSize();
}

BX.CDialog.prototype.ClearButtons = function()
{
	BX.cleanNode(this.PARTS.BUTTONS_CONTAINER);
	this.adjustSize();
}

BX.CDialog.prototype.SetContent = function(html)
{
	this.__form = null;

	if (BX.type.isElementNode(html))
	{
		if (html.parentNode)
			html.parentNode.removeChild(html);
	}

	this.PARAMS.content = html;
	BX.cleanNode(this.PARTS.CONTENT_DATA);

	BX.adjust(this.PARTS.CONTENT_DATA, {
		children: [
			this.PARTS.HEAD,
			BX.create('DIV', {
				props: {className: 'content-inner'},
				children: [this.PARAMS.content || '&nbsp;']
			})
		]
	});

	if (this.PARAMS.content_url && this.GetForm())
	{
		this.__form.submitbtn = this.__form.appendChild(BX.create('INPUT', {props:{type:'submit'},style:{display:'none'}}));
		this.__form.onsubmit = BX.delegate(this.__submit, this);
	}
}

BX.CDialog.prototype.__submit = function(e)
{
	for (var i=0,len=this.PARAMS.buttons.length; i<len; i++)
	{
		if (
			this.PARAMS.buttons[i]
			&& (
				this.PARAMS.buttons[i].name && /save|apply/i.test(this.PARAMS.buttons[i].name)
				||
				this.PARAMS.buttons[i].btn && this.PARAMS.buttons[i].btn.name && /save|apply/i.test(this.PARAMS.buttons[i].btn.name)
			)
		)
		{
			this.PARAMS.buttons[i].emulate();
			break;
		}
	}

	return BX.PreventDefault(e);
}

BX.CDialog.prototype.SwapContent = function(cont)
{
	cont = BX(cont);

	BX.cleanNode(this.PARTS.CONTENT_DATA);
	cont.parentNode.removeChild(cont);
	this.PARTS.CONTENT_DATA.appendChild(cont);
	cont.style.display = 'block';
	this.SetContent(cont.innerHTML);
}

BX.CDialog.prototype.adjustSize = function()
{
	return;
	setTimeout(BX.delegate(this.__adjustSize, this), 10);
}

BX.CDialog.prototype.__adjustSize = function()
{
	return;
	var height =
		parseInt(this.DIV.style.height) - 2 // strange bug with offsetHeight in IE while expanding
		- (this.PARTS.TITLEBAR.offsetHeight + 1)
		- (this.PARTS.HEAD ? this.PARTS.HEAD.offsetHeight + 10 : 0)
		- (this.PARTS.FOOT ? this.PARTS.FOOT.offsetHeight : 0);

	this.PARTS.CONTENT.style.height = height + 'px';

	// autotraing: calm down, max! calm down....
	if (BX.browser.IsIE())
	{
		var width_tmp = this.PARTS.CONTENT.style.width;
		this.PARTS.CONTENT.style.width = '0px';
		var width = this.DIV.offsetWidth-12;
		this.PARTS.CONTENT.style.width = width > 0 ? (width + 'px') : width_tmp;
	}

	// TODO: optimize it!
	BX.onCustomEvent(this, 'onWindowResizeExt', [{'height': height, 'width': this.PARTS.CONTENT.offsetWidth-(BX.browser.IsIE() ? -5 : 2)}]);
}

BX.CDialog.prototype.adjustSizeEx = function()
{
	setTimeout(BX.delegate(this.__adjustSizeEx, this), 10);
}

BX.CDialog.prototype.__adjustSizeEx = function()
{
	var new_height = this.PARTS.CONTENT_DATA.firstChild.offsetHeight + this.PARTS.CONTENT_DATA.lastChild.offsetHeight;

	if (new_height)
		this.PARTS.CONTENT_DATA.style.height = new_height + 'px';

	return;
	var arMargins = [10, parseInt(BX.style(this.PARTS.CONTENT, 'top')), parseInt(BX.style(this.PARTS.CONTENT.firstChild, 'margin-top')), parseInt(BX.style(this.PARTS.CONTENT.firstChild, 'margin-bottom'))];
	if (BX.browser.IsIE()) arMargins[0] += 5;
	var margins = 0;
	for (var i=0; i < arMargins.length; i++)
		if (!isNaN(arMargins[i]))
			margins += arMargins[i];

	var height = this.PARTS.CONTENT.firstChild.offsetHeight
		+ margins
		+ this.PARTS.TITLEBAR.offsetHeight
		+ (this.PARTS.FOOT ? this.PARTS.FOOT.offsetHeight : 0)
		+ (this.PARTS.HEAD ? this.PARTS.HEAD.offsetHeight : 0);

	this.DIV.style.height = height + 'px';
	this.adjustSize();
}


BX.CDialog.prototype.__onResizeFinished = function()
{
	BX.WindowManager.saveWindowSize(
		this.PARAMS.resize_id || this.PARAMS.content_url, {height: parseInt(this.PARTS.CONTENT_DATA.style.height), width: parseInt(this.PARTS.CONTENT_DATA.style.width)}
	);
}

BX.CDialog.prototype.Show = function(bNotRegister)
{
	if ((!this.PARAMS.content) && this.PARAMS.content_url && BX.ajax && !bNotRegister)
	{
		var wait = BX.showWait();
		
		BX.WindowManager.currently_loaded = this;

		this.CreateOverlay(parseInt(BX.style(wait, 'z-index'))-1);
		this.OVERLAY.style.display = 'block';
		this.OVERLAY.className = 'bx-core-dialog-overlay';

		var post_data = '', method = 'get';
		if (this.PARAMS.content_post)
		{
			post_data = this.PARAMS.content_post;
			method = 'post';
		}

		BX.ajax[method](this.PARAMS.content_url, post_data, BX.delegate(function(data) {
			BX.closeWait(null, wait);

			this.SetContent(data || '&nbsp;');
			this.Show();
		}, this));
	}
	else
	{
		BX.WindowManager.currently_loaded = null;
		BX.CDialog.superclass.Show.apply(this, arguments);
		
		this.adjustPos();

		this.OVERLAY.className = 'bx-core-dialog-overlay';

		this.__adjustSize();

		BX.addCustomEvent(this, 'onWindowResize', BX.proxy(this.__adjustSize, this))

		if (this.PARAMS.resizable && (this.PARAMS.content_url || this.PARAMS.resize_id))
			BX.addCustomEvent(this, 'onWindowResizeFinished', BX.delegate(this.__onResizeFinished, this));
	}
}

BX.CDialog.prototype.GetInnerPos = function()
{
	return {'width': parseInt(this.PARTS.CONTENT_DATA.style.width), 'height': parseInt(this.PARTS.CONTENT_DATA.style.height)};
}

BX.CDialog.prototype.adjustPos = function()
{
	if (!this.bExpanded)
	{
		var windowSize = BX.GetWindowInnerSize();
		var windowScroll = BX.GetWindowScrollPos();

		BX.adjust(this.DIV, {
			style: {
				left: parseInt(windowScroll.scrollLeft + windowSize.innerWidth / 2 - parseInt(this.DIV.offsetWidth) / 2) + 'px',
				top: Math.max(parseInt(windowScroll.scrollTop + windowSize.innerHeight / 2 - parseInt(this.DIV.offsetHeight) / 2), 0) + 'px'
			}
		});
	}
}

BX.CDialog.prototype.GetContent = function () {return this.PARTS.CONTENT_DATA};

BX.CDialog.prototype.btnSave = BX.CDialog.btnSave = {
	title: BX.message('JS_CORE_WINDOW_SAVE'),
	id: 'savebtn',
	name: 'savebtn',
	action: function () {
		this.disableUntilError();
		this.parentWindow.PostParameters();
	}
};

BX.CDialog.prototype.btnCancel = BX.CDialog.btnCancel = {
	title: BX.message('JS_CORE_WINDOW_CANCEL'),
	id: 'cancel',
	name: 'cancel',
	action: function () {
		this.parentWindow.Close();
	}
};

BX.CDialog.prototype.btnClose = BX.CDialog.btnClose = {
	title: BX.message('JS_CORE_WINDOW_CLOSE'),
	id: 'close',
	name: 'close',
	action: function () {
		this.parentWindow.Close();
	}
};

/* special child for admin forms loaded into public page */
BX.CAdminDialog = function(arParams)
{
	BX.CAdminDialog.superclass.constructor.apply(this, arguments);


	if (this.PARTS.HEAD.parentNode)
		this.PARTS.HEAD.parentNode.removeChild(this.PARTS.HEAD);

	this.PARTS.CONTENT.insertBefore(this.PARTS.HEAD, this.PARTS.CONTENT.firstChild);

	this.PARTS.HEAD.className = 'bx-core-admin-dialog-head';
	this.PARTS.CONTENT.className += ' bx-core-admin-dialog-content';
}
BX.extend(BX.CAdminDialog, BX.CDialog);


BX.CAdminDialog.prototype.SetHead = function()
{
	BX.CAdminDialog.superclass.SetHead.apply(this, arguments);

	if (this.PARTS.HEAD.firstChild.firstChild && BX.type.isElementNode(this.PARTS.HEAD.firstChild.firstChild))
	{
		this.SETTINGS.min_width = Math.max(this.PARTS.HEAD.firstChild.firstChild.offsetWidth, this.SETTINGS.min_width);
	}
}

BX.CAdminDialog.prototype.SetContent = function(html)
{
	if (BX.type.isElementNode(html))
	{
		if (html.parentNode)
			html.parentNode.removeChild(html);
	}

	this.PARAMS.content = html;
	BX.cleanNode(this.PARTS.CONTENT_DATA);

	BX.adjust(this.PARTS.CONTENT_DATA, {
		children: [
			BX.create('DIV', {
				props: {className: 'content-inner'},
				children: [this.PARAMS.content || '&nbsp;']
			})
		]
	});

	if (this.PARAMS.content_url && this.GetForm())
	{
		this.__form.appendChild(BX.create('INPUT', {props:{type:'submit'},style:{display:'none'}}));
		this.__form.onsubmit = BX.delegate(this.__submit, this);
	}
}

BX.CAdminDialog.prototype.__adjustSizeEx = function()
{
	var new_height = this.PARTS.CONTENT_DATA.firstChild.offsetHeight;
	if (new_height)
		this.PARTS.CONTENT_DATA.style.height = new_height + 'px';
}

BX.CAdminDialog.prototype.__expandGetSize = function()
{
	var res = BX.CAdminDialog.superclass.__expandGetSize.apply(this, arguments);

	res.height -= this.PARTS.HEAD.offsetHeight;
	return res;
}

BX.CAdminDialog.prototype.Submit = function()
{
	var FORM = this.GetForm();
	if (FORM && !FORM['bxpublic'] && !/bxpublic=/.test(FORM.action))
	{
		FORM.appendChild(BX.create('INPUT', {
			props: {
				type: 'hidden',
				name: 'bxpublic',
				value: 'Y'
			}
		}));
	}

	return BX.CAdminDialog.superclass.Submit.apply(this, arguments);
}

BX.CAdminDialog.prototype.btnSave = BX.CAdminDialog.btnSave = {
	title: BX.message('JS_CORE_WINDOW_SAVE'),
	id: 'savebtn',
	name: 'savebtn',
	action: function () {
		this.disableUntilError();
		this.parentWindow.Submit();
	}
};

BX.CAdminDialog.btnCancel = BX.CAdminDialog.superclass.btnCancel;
BX.CAdminDialog.btnClose = BX.CAdminDialog.superclass.btnClose;

BX.CDebugDialog = function(arParams)
{
	BX.CDebugDialog.superclass.constructor.apply(this, arguments);
}
BX.extend(BX.CDebugDialog, BX.CDialog);

BX.CDebugDialog.prototype.Show = function()
{
	BX.CDebugDialog.superclass.Show.apply(this, arguments);

	/*
	if (BX.browser.IsIE())
	{
		BX.cleanNode(this.PARTS.CONTENT_DATA).style.padding = '0px !important';
		this.PARTS.CONTENT_BOTTOM = this.PARTS.CONTENT_DATA.appendChild(BX.create('DIV', {style: {
			width: '100%', overflow: 'auto'
		}, html: this.PARAMS.content}));

		BX.adjust(this.PARTS.CONTENT_DATA.parentNode.parentNode.parentNode, {
			style: {marginTop: this.PARTS.CONTENT_TOP ? this.PARTS.CONTENT_TOP.style.height : '0px'},
			attrs: {cellSpacing: '0px', cellPadding: '0px'}
		});

		BX.adjust(this.PARTS.CONTENT_DATA, {style: {
			padding: '0px', backgroundImage: 'none'
		}});

		this.__adjustSize();
	}
	else
	{
		if (this.PARTS.CONTENT_TOP)
			this.PARTS.CONTENT_DATA.style.marginTop = this.PARTS.CONTENT_TOP.style.height;

		this.PARTS.CONTENT_DATA.style.overflow = 'auto';
	}
	*/
}

BX.CDebugDialog.prototype.ShowDetails = function(div_id)
{
	var div = BX(div_id);
	if (div)
	{
		if (this.div_detail_current)
			this.div_detail_current.style.display = 'none';

		div.style.display = 'block';
		this.div_detail_current = div;
	}
}

BX.CDebugDialog.prototype.SetContent = function(html)
{
	if (!html) return;

	var arHtml = html.split('#DIVIDER#');
	if (arHtml.length > 1)
	{
		this.PARAMS.content = arHtml[1];

		this.PARTS.CONTENT_DATA.style.overflow = 'hidden';

		BX.CDebugDialog.superclass.SetContent.apply(this, [arHtml[1]]);

		this.PARTS.CONTENT_INNER = this.PARTS.CONTENT_DATA.firstChild.nextSibling;
		this.PARTS.CONTENT_TOP = this.PARTS.CONTENT_DATA.insertBefore(BX.create('DIV', {
			style: {
				position: 'relative',
				top: '0px',
				left: '0px',
				height: '120px',
				overflow: 'auto',
				backgroundColor: 'white',
				borderBottom: '1px solid #D0D0D0'
			},
			html: arHtml[0]
		}), this.PARTS.CONTENT_INNER);
		this.PARTS.CONTENT_INNER.style.overflow = 'auto';
	}
	else
	{
		BX.CDebugDialog.superclass.SetContent.apply(this, arguments);
	}
}


BX.CDebugDialog.prototype.__adjustSize = function()
{
	BX.CDebugDialog.superclass.__adjustSize.apply(this, arguments);

	if (this.PARTS.CONTENT_TOP)
	{
		var new_height = this.PARTS.CONTENT_DATA.offsetHeight - this.PARTS.HEAD.offsetHeight - this.PARTS.CONTENT_TOP.offsetHeight - 20;

		if (new_height > 0)
		{
			this.PARTS.CONTENT_INNER.style.height = new_height + 'px';

		}
	}
}


/* MENU CLASSES */

BX.CWindowFloat = function(node)
{
	BX.CWindowFloat.superclass.constructor.apply(this, [node, 'float']);

	this.SETTINGS.resizable = false;
}
BX.extend(BX.CWindowFloat, BX.CWindow);

BX.CWindowFloat.prototype.adjustPos = function()
{
	if (this.PARAMS.parent)
		this.adjustToNode()
	else if (this.PARAMS.x && this.PARAMS.y)
		this.adjustToPos([this.PARAMS.x, this.PARAMS.y]);
}

BX.CWindowFloat.prototype.adjustToPos = function(pos)
{
	this.DIV.style.left = parseInt(pos[0]) + 'px';
	this.DIV.style.top = parseInt(pos[1]) + 'px';
}

BX.CWindowFloat.prototype.adjustToNodeGetPos = function()
{
	var pos = BX.pos(this.PARAMS.parent, true);

	if (this.DIV.parentNode != this.PARAMS.parent)
	{
		this.DIV.parentNode.removeChild(this.DIV);
		this.PARAMS.parent.insertBefore(this.DIV, this.PARAMS.parent.firstChild);
	}

	return pos;
}

BX.CWindowFloat.prototype.adjustToNode = function(el)
{
	el = el || this.PARAMS.parent;

	this.PARAMS.parent = BX(el);

	if (this.PARAMS.parent)
	{
		var pos = this.adjustToNodeGetPos();

		this.DIV.style.top = pos.top + 'px';//(pos.top - 26) + 'px';
		this.DIV.style.left = pos.left + 'px';

		this.PARAMS.parent.OPENER = this;
	}
}

BX.CWindowFloat.prototype.Show = function()
{
	BX.CWindowFloat.superclass.Show.apply(this, arguments);
	this.adjustPos();
}

/* menu opener class */
/*
{
	DOMNode DIV,
	BX.CMenu or Array MENU,
	TYPE = 'hover' | 'click',
	TIMEOUT: 500
}
*/
BX.COpener = function(arParams)
{
	this.PARAMS = arParams || {};

	this.MENU = arParams.MENU;

	this.DIV = arParams.DIV;
	this.ATTACH = arParams.ATTACH || arParams.DIV;

	this.TYPE = this.PARAMS.TYPE == 'hover' ? 'hover' : 'click';

	if (arParams.TIMEOUT !== 0)
		this.TIMEOUT = arParams.TIMEOUT || 10;
	else
		this.TIMEOUT = 0;

	this.bOpen = false;

	BX.ready(BX.delegate(this.Init, this));
}

BX.COpener.prototype.Init = function()
{
	this.DIV = BX(this.DIV);

	switch (this.TYPE)
	{
		case 'hover':
			BX.adjust(this.DIV, {'events': {'mouseover': BX.delegate(this.Open, this)}});
		break;

		case 'click':
			this.DIV.onclick = BX.delegate(this.Toggle, this);
		break;
	}

	//BX.bind(window, 'scroll', BX.delegate(this.__close_immediately, this));

	this.bMenuInit = false;
}

BX.COpener.prototype.Toggle = function(e)
{
	if (!this.bOpen)
		this.Open(e);

	//closing will be done by window itself
}

BX.COpener.prototype.Open = function(e)
{
	if (!this.bMenuInit)
	{
		if (BX.type.isArray(this.MENU))
		{
			this.MENU = new BX.CMenu({
				ITEMS: this.MENU,
				ATTACH_MODE: 'bottom',
				parent: BX(this.DIV),
				parent_attach: BX(this.ATTACH)
			});
		}

		BX.addCustomEvent(this.MENU, 'onMenuOpen', BX.delegate(this.handler_onopen, this));
		BX.addCustomEvent(this.MENU, 'onMenuClose', BX.delegate(this.handler_onclose, this));

		this.bMenuInit = true;
	}

	this.bOpen = true;
	if (this.TIMEOUT > 0)
		setTimeout(BX.proxy(this.__open, this), this.TIMEOUT);
	else
		this.__open();

	//return BX.PreventDefault(e);
}

BX.COpener.prototype.handler_onopen = function(){
	this.bOpen = true;
	BX.onCustomEvent(this, 'onOpenerMenuOpen');
}
BX.COpener.prototype.handler_onclose = function(){
	this.bOpen = false;
	BX.onCustomEvent(this, 'onOpenerMenuClose');
}

BX.COpener.prototype.Close = function(e)
{
	if (!this.bMenuInit)
		return;

	this.bOpen = false;
	if (this.TIMEOUT > 0)
		setTimeout(BX.proxy(this.__close, this), this.TIMEOUT-10);
	else
		this.__close();
}

BX.COpener.prototype.__open = function(){
	if (this.bMenuInit && this.bOpen && !this.MENU.visible())
		this.MENU.Show();
}
BX.COpener.prototype.__close = function(){
	if (this.bMenuInit && !this.bOpen && this.MENU.visible()) this.MENU.Hide();
}
BX.COpener.prototype.__close_immediately = function() {
	this.bOpen = false; this.__close();
}
BX.COpener.prototype.isMenuVisible = function() {
	return null != this.MENU.visible && this.MENU.visible()
}

/* components toolbar class */

BX.CMenuOpener = function(arParams)
{
	BX.CMenuOpener.superclass.constructor.apply(this);

	this.PARAMS = arParams || {};
	this.PARTS = {};

	this.SETTINGS.drag_restrict = true;

	this.defaultAction = null;

	this.timeout = 500;

	this.DIV.className = 'bx-component-opener';
	this.DIV.ondblclick = BX.PreventDefault;

	if (this.PARAMS.component_id)
	{
		this.PARAMS.transform = !!this.PARAMS.transform;
	}

	this.OPENERS = [];

	this.DIV.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar' + (this.PARAMS.transform ? ' bx-context-toolbar-vertical-mode' : '')}
	}));

	//set internal structure and register draggable element
	this.PARTS.INNER = this.DIV.firstChild.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-inner'},
		html: '<span class="bx-context-toolbar-drag-icon"></span><br>'
	}));

	this.EXTRA_BUTTONS = {};

	var btnCount = 0;
	for (var i = 0, len = this.PARAMS.menu.length; i < len; i++)
	{
		var item = this.addItem(this.PARAMS.menu[i]);
		if (null != item)
		{
			btnCount++;
			this.PARTS.INNER.appendChild(item);
			this.PARTS.INNER.appendChild(BX.create('BR'));
		}
	}
	var bHasButtons = btnCount > 0;

	//menu items will be attached here

	this.PARTS.ICONS = this.PARTS.INNER.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-icons'}
	}));

	if (this.PARAMS.component_id)
	{
		this.PARAMS.pin = !!this.PARAMS.pin;

		if (bHasButtons)
			this.PARTS.ICONS.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-separator'}}));

		this.PARTS.ICON_PIN = this.PARTS.ICONS.appendChild(BX.create('A', {
			attrs: {
				href: 'javascript:void(0)'
			},
			props: {
				className: this.PARAMS.pin
							? 'bx-context-toolbar-pin-fixed'
							: 'bx-context-toolbar-pin'
			},
			events: {
				click: BX.delegate(this.__pin_btn_clicked, this)
			}
		}));
	}


	if (this.EXTRA_BUTTONS['components2_props'])
	{
		this.PARTS.ICONS.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-separator'}}));

		var btn = this.EXTRA_BUTTONS['components2_props'] || {URL: 'javascript:void(0)'};
		if (null == this.defaultAction)
		{
			this.defaultAction = btn.ONCLICK;
			this.defaultActionTitle = btn.TITLE || btn.TEXT;
		}

		btn.URL = 'javascript:' + btn.ONCLICK;

		this.ATTACH = this.PARTS.ICONS.appendChild(BX.create('SPAN', {
			props: {className: 'bx-context-toolbar-button'},
			children:
			[
				BX.create('SPAN',
				{
					props:{className: 'bx-context-toolbar-button-inner'},
					children:
					[
						BX.create('A', {
							attrs: {href: btn.URL},
							events: {
								'mouseover': function() {this.parentNode.parentNode.className+=' bx-context-toolbar-button-text-hover'},
								'mouseout': function() {this.parentNode.parentNode.className='bx-context-toolbar-button'}
							},
							html: '<span class="bx-context-toolbar-button-icon bx-context-toolbar-settings-icon"></span>'
						}),
						BX.create('A', {
							attrs: {href: 'javascript: void(0)'},
							props: {className: 'bx-context-toolbar-button-arrow'},
							events: {
								'mouseover': function() {this.parentNode.parentNode.className += ' bx-context-toolbar-button-arrow-hover'},
								'mouseout': function() {this.parentNode.parentNode.className='bx-context-toolbar-button'}
							},
							html: '<span class="bx-context-toolbar-button-arrow"></span>'
						})
					]
				})
			]
		}));

		this.OPENER = this.ATTACH.firstChild.lastChild;
		this.attachMenu(this.EXTRA_BUTTONS['components2_submenu']['MENU']);
	}

	if (btnCount > 1)
	{
		this.ICON_TRANSFORM = this.PARTS.ICONS.appendChild(BX.create('A', {
			attrs: {href: 'javascript: void(0)'},
			props: {className: 'bx-context-toolbar-switcher'},
			events: {
				click: BX.delegate(this.__trf_btn_clicked, this)
			}
		}));
	}

	if (this.PARAMS.HINT)
	{
		this.DIV.BXHINT = this.HINT = new BX.CHint({
			parent: this.DIV,
			hint:this.PARAMS.HINT.TEXT || '',
			title: this.PARAMS.HINT.TITLE || '',
			hide_timeout: this.timeout/2,
			preventHide: false
		});
	}

	BX.addCustomEvent(this, 'onWindowDragFinished', BX.delegate(this.__onMoveFinished, this));
	BX.addCustomEvent('onDynamicModeChange', BX.delegate(this.__onDynamicModeChange, this));
	BX.addCustomEvent('onTopPanelCollapse', BX.delegate(this.__onPanelCollapse, this));

	BX.addCustomEvent('onMenuOpenerMoved', BX.delegate(this.checkPosition, this));
	BX.addCustomEvent('onMenuOpenerUnhide', BX.delegate(this.checkPosition, this));

	if (this.OPENERS)
	{
		for (var i=0,len=this.OPENERS.length; i<len; i++)
		{
			BX.addCustomEvent(this.OPENERS[i], 'onOpenerMenuOpen', BX.proxy(this.__hide_hint, this));
		}
	}
}
BX.extend(BX.CMenuOpener, BX.CWindowFloat);

BX.CMenuOpener.prototype.unclosable = true;

BX.CMenuOpener.prototype.__check_intersection = function(pos_self, pos_other)
{
	return !(pos_other.right <= pos_self.left || pos_other.left >= pos_self.right
			|| pos_other.bottom <= pos_self.top || pos_other.top >= pos_self.bottom);
}

BX.CMenuOpener.prototype.checkPosition = function()
{
	if (this.isMenuVisible() || this.DIV.style.display == 'none'
		|| this == BX.proxy_context || BX.proxy_context.zIndex > this.zIndex)
		return;

	this.correctPosition(BX.proxy_context);
}

BX.CMenuOpener.prototype.correctPosition = function(opener)
{
	var pos_self = BX.pos(this.DIV), pos_other = BX.pos(opener.Get());
	if (this.__check_intersection(pos_self, pos_other))
	{
		var new_top = pos_other.top - pos_self.height;
		if (new_top < 0)
			new_top = pos_other.bottom;

		this.DIV.style.top = new_top + 'px';

		BX.addCustomEvent(opener, 'onMenuOpenerHide', BX.proxy(this.restorePosition, this));
		BX.onCustomEvent(this, 'onMenuOpenerMoved');
	}
}

BX.CMenuOpener.prototype.restorePosition = function()
{
	if (!this.MOUSEOVER && !this.isMenuVisible())
	{
		if (this.originalPos)
			this.DIV.style.top = this.originalPos.top + 'px';

		BX.removeCustomEvent(BX.proxy_context, 'onMenuOpenerHide', BX.proxy(this.restorePosition, this));
		if (this.restore_pos_timeout) clearTimeout(this.restore_pos_timeout);
	}
	else
	{
		this.restore_pos_timeout = setTimeout(BX.proxy(this.restorePosition, this), this.timeout);
	}
}


BX.CMenuOpener.prototype.Show = function()
{
	BX.CMenuOpener.superclass.Show.apply(this, arguments);

	this.SetDraggable(this.PARTS.INNER.firstChild);

	this.DIV.style.width = 'auto';
	this.DIV.style.height = 'auto';

	if (!this.PARAMS.pin)
	{
		this.DIV.style.left = '-1000px';
		this.DIV.style.top = '-1000px';

		this.Hide();
	}
	else
	{
		this.bPosAdjusted = true;
		this.bMoved = true;

		if (this.PARAMS.top) this.DIV.style.top = this.PARAMS.top + 'px';
		if (this.PARAMS.left) this.DIV.style.left = this.PARAMS.left + 'px';

		this.DIV.style.display = (!BX.admin.dynamic_mode || BX.admin.dynamic_mode_show_borders) ? 'block' : 'none';

		if (this.DIV.style.display == 'block')
		{
			setTimeout(BX.delegate(function() {BX.onCustomEvent(this, 'onMenuOpenerUnhide')}, this), 50);
		}
	}
}

BX.CMenuOpener.prototype.executeDefaultAction = function()
{
	if (this.defaultAction)
	{
		if (BX.type.isFunction(this.defaultAction))
			this.defaultAction();
		else if(BX.type.isString(this.defaultAction))
			BX.evalGlobal(this.defaultAction);
	}
}

BX.CMenuOpener.prototype.__onDynamicModeChange = function(val)
{
	this.DIV.style.display = val ? 'block' : 'none';
}

BX.CMenuOpener.prototype.__onPanelCollapse = function(bCollapsed, dy)
{
	this.DIV.style.top = (parseInt(this.DIV.style.top) + dy) + 'px';
	if (this.PARAMS.pin)
	{
		this.__savePosition();
	}
}

BX.CMenuOpener.prototype.__onMoveFinished = function()
{
	BX.onCustomEvent(this, 'onMenuOpenerMoved');

	this.bMoved = true;

	if (this.PARAMS.pin)
		this.__savePosition();
}

BX.CMenuOpener.prototype.__savePosition = function()
{
	var arOpts = {};

	arOpts.pin = this.PARAMS.pin
	if (!this.PARAMS.pin)
	{
		arOpts.top = false; arOpts.left = false; arOpts.transform = false;
	}
	else
	{
		arOpts.transform = this.PARAMS.transform;
		if (this.bMoved)
		{
			arOpts.left = parseInt(this.DIV.style.left);
			arOpts.top = parseInt(this.DIV.style.top);
		}
	}

	BX.WindowManager.saveWindowOptions(this.PARAMS.component_id, arOpts);
}

BX.CMenuOpener.prototype.__pin_btn_clicked = function() {this.Pin()}
BX.CMenuOpener.prototype.Pin = function(val)
{
	if (null == val)
		this.PARAMS.pin = !this.PARAMS.pin;
	else
		this.PARAMS.pin = !!val;

	this.PARTS.ICON_PIN.className = (this.PARAMS.pin ? 'bx-context-toolbar-pin-fixed' : 'bx-context-toolbar-pin');

	this.__savePosition();
}

BX.CMenuOpener.prototype.__trf_btn_clicked = function() {this.Transform()}
BX.CMenuOpener.prototype.Transform = function(val)
{
	if (null == val)
		this.PARAMS.transform = !this.PARAMS.transform
	else
		this.PARAMS.transform = !!val

	if (this.bMoved)
	{
		var pos = BX.pos(this.DIV);
	}

	if (this.PARAMS.transform)
		BX.addClass(this.DIV.firstChild, 'bx-context-toolbar-vertical-mode');
	else
		BX.removeClass(this.DIV.firstChild, 'bx-context-toolbar-vertical-mode');

	if (!this.bMoved)
	{
		this.adjustPos();
	}
	else
	{
		this.DIV.style.left = (pos.right - this.DIV.offsetWidth - (BX.browser.IsIE() && !BX.browser.IsDoctype() ? 2 : 0)) + 'px';
	}

	this.__savePosition();
}

BX.CMenuOpener.prototype.adjustToNodeGetPos = function()
{
	var pos = BX.pos(this.PARAMS.parent/*, true*/);

	var scrollSize = BX.GetWindowScrollSize();
	var floatWidth = this.DIV.offsetWidth;

	pos.left -= BX.admin.__border_dx;
	pos.top -= BX.admin.__border_dx;

	if (true || !this.PARAMS.transform)
	{
		pos.top -= 26;
	}

	if (pos.left > scrollSize.scrollWidth - floatWidth)
	{
		pos.left = scrollSize.scrollWidth - floatWidth;
	}

	return pos;
}

BX.CMenuOpener.prototype.addItem = function(item)
{
	var s = '';
	if (item.TYPE)
	{
		this.EXTRA_BUTTONS[item.TYPE] = item;
		return null;
	}
	else
	{
		var q = new BX.CMenuOpenerItem(item);
		if (null == this.defaultAction)
		{
			if (q.item.ONCLICK)
			{
				this.defaultAction = item.ONCLICK;
			}
			else if (q.item.MENU)
			{
				this.defaultAction = BX.delegate(function() {this.Open()}, q.item.OPENER);
			}

			this.defaultActionTitle = item.TITLE || item.TEXT;

			BX.addClass(q.Get(), 'bx-content-toolbar-default');
		}
		if (q.item.OPENER) this.OPENERS[this.OPENERS.length] = q.item.OPENER
		return q.Get();
	}
}

BX.CMenuOpener.prototype.attachMenu = function(menu)
{
	var opener = new BX.COpener({
		'DIV':  this.OPENER,
		'ATTACH': this.ATTACH,
		'MENU': menu,
		'TYPE': 'click'
	});

	this.OPENERS[this.OPENERS.length] = opener;
}

BX.CMenuOpener.prototype.__hide_hint = function()
{
	if (this.HINT) this.HINT.__hide_immediately();
}

BX.CMenuOpener.prototype.isMenuVisible = function()
{
	for (var i=0,len=this.OPENERS.length; i<len; i++)
	{
		if (this.OPENERS[i].isMenuVisible())
			return true;
	}

	return false;
}

BX.CMenuOpener.prototype.Hide = function()
{
	if (!this.PARAMS.pin)
	{
		this.DIV.style.display = 'none';
		BX.onCustomEvent(this, 'onMenuOpenerHide');
	}
}
BX.CMenuOpener.prototype.UnHide = function()
{
	this.DIV.style.display = 'block';
	if (!this.bPosAdjusted && !this.PARAMS.pin)
	{
		this.adjustPos();
		this.bPosAdjusted = true;
	}

	if (null == this.originalPos && !this.bMoved)
	{
		this.originalPos = BX.pos(this.DIV);
	}

	BX.onCustomEvent(this, 'onMenuOpenerUnhide');
}

BX.CMenuOpenerItem = function(item)
{
	this.item = item;

	if (this.item.ACTION && !this.item.ONCLICK)
	{
		this.item.ONCLICK = this.item.ACTION;
	}

	this.DIV = BX.create('SPAN');
	this.DIV.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-button-underlay'}}));

	this.WRAPPER = this.DIV.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-wrapper'},
		children: [
			BX.create('SPAN', {
				props: {className: 'bx-context-toolbar-button', title: item.TITLE},
				children: [
					BX.create('SPAN', {
						props: {className: 'bx-context-toolbar-button-inner'}
					})
				]
			})
		]
	}));

	var btn_icon = BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-icon' + (this.item.ICON || this.item.ICONCLASS ? ' ' + (this.item.ICON || this.item.ICONCLASS) : '')},
		attrs: (
				!(this.item.ICON || this.item.ICONCLASS)
				&&
				(this.item.SRC || this.item.IMAGE)
			)
			? {
				style: 'background: scroll transparent url(' + (this.item.SRC || this.item.IMAGE) + ') no-repeat center center !important;'
			}
			: {}
	}), btn_text = BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-text'},
		text: this.item.TEXT
	});

	if (this.item.ACTION && !this.item.ONCLICK)
	{
		this.item.ONCLICK = this.item.ACTION;
	}

	this.bHasMenu = !!this.item.MENU;
	this.bHasAction = !!this.item.ONCLICK;

	if (this.bHasAction)
	{
		this.LINK = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
			attrs: {
				'href': 'javascript: void(0)'
			},
			events: {
				mouseover: this.bHasMenu ? this.__msover_text : this.__msover,
				mouseout: this.bHasMenu ? this.__msout_text : this.__msout
			},
			children: [btn_icon, btn_text]
		}));

		if (this.bHasMenu)
		{
			this.LINK_MENU = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
				props: {className: 'bx-context-toolbar-button-arrow'},
				attrs: {
					'href': 'javascript: void(0)'
				},
				events: {
					mouseover: this.__msover_arrow,
					mouseout: this.__msout_arrow
				},
				children: [
					BX.create('SPAN', {props: {className: 'bx-context-toolbar-button-arrow'}})
				]
			}));
		}

	}
	else if (this.bHasMenu)
	{
		this.item.ONCLICK = null;

		this.LINK = this.LINK_MENU = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
			attrs: {
				'href': 'javascript: void(0)'
			},
			events: {
				mouseover: this.__msover,
				mouseout: this.__msout
			},
			children: [
				btn_icon,
				btn_text
			]
		}));

		this.LINK.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-single-button-arrow'}}));

	}

	if (this.bHasMenu)
	{
		this.item.SUBMENU = new BX.CMenu({
			ATTACH_MODE:'bottom',
			ITEMS:this.item['MENU'],
			//PARENT_MENU:this.parentMenu,
			parent: this.LINK_MENU,
			parent_attach: this.WRAPPER.firstChild
		});

		this.item.OPENER = new BX.COpener({
			DIV: this.LINK_MENU,
			TYPE: 'click',
			MENU: this.item.SUBMENU
		});
	}

	if (this.bHasAction)
	{
		BX.bind(this.LINK, 'click', BX.delegate(this.__click, this));
	}
}

BX.CMenuOpenerItem.prototype.Get = function() {return this.DIV;}
BX.CMenuOpenerItem.prototype.__msover = function() {
	BX.addClass(this.parentNode.parentNode, 'bx-context-toolbar-button-hover');
}
BX.CMenuOpenerItem.prototype.__msout = function() {
	BX.removeClass(this.parentNode.parentNode, 'bx-context-toolbar-button-hover');
}
BX.CMenuOpenerItem.prototype.__msover_text = function() {
	BX.addClass(this.parentNode.parentNode, 'bx-context-toolbar-button-text-hover');
}
BX.CMenuOpenerItem.prototype.__msout_text = function() {
	BX.removeClass(this.parentNode.parentNode, 'bx-context-toolbar-button-text-hover');
}
BX.CMenuOpenerItem.prototype.__msover_arrow = function() {
	BX.addClass(this.parentNode.parentNode, 'bx-context-toolbar-button-arrow-hover');
}
BX.CMenuOpenerItem.prototype.__msout_arrow = function() {
	BX.removeClass(this.parentNode.parentNode, 'bx-context-toolbar-button-arrow-hover');
}

BX.CMenuOpenerItem.prototype.__click = function() {BX.evalGlobal(this.item.ONCLICK)}

/* global page opener class */
BX.CPageOpener = function(arParams)
{
	//if (null == arParams.pin) arParams.pin = true;
	BX.CPageOpener.superclass.constructor.apply(this, arguments);

	this.timeout = 505;

	window.PAGE_EDIT_CONTROL = this;
}
BX.extend(BX.CPageOpener, BX.CMenuOpener);

BX.CPageOpener.prototype.checkPosition = function()
{
	if (/*this.isMenuVisible() || this.DIV.style.display == 'none' || */this == BX.proxy_context)
		return;

	this.correctPosition(BX.proxy_context);
}

BX.CPageOpener.prototype.correctPosition = function(opener)
{
	if (this.bPosCorrected) return;
	if (this.DIV.style.display == 'none')
	{
		var pos_self = this.adjustToNodeGetPos();
		pos_self.bottom = pos_self.top + 30;
		pos_self.right = pos_self.left + 300;
	}
	else
	{
		var pos_self = BX.pos(this.DIV);
	}

	var pos_other = BX.pos(opener.Get());
	if (this.__check_intersection(pos_self, pos_other))
	{
		this.DIV.style.display = 'none';
		BX.addCustomEvent(opener, 'onMenuOpenerHide', BX.proxy(this.restorePosition, this));

		this.bPosCorrected = true;
	}
}

BX.CPageOpener.prototype.restorePosition = function()
{
	if (BX.proxy_context && BX.proxy_context.Get().style.display == 'none')
	{
		this.bPosCorrected = false;

		if (this.PARAMS.parent.bx_over || this.PARAMS.pin)
			this.UnHide();

		BX.removeCustomEvent('onMenuOpenerHide', BX.proxy(this.restorePosition, this));
	}
}

BX.CPageOpener.prototype.UnHide = function()
{
	if (!this.bPosCorrected)
		BX.CPageOpener.superclass.UnHide.apply(this, arguments);
}

BX.CPageOpener.prototype.Remove = function()
{
	BX.admin.removeComponentBorder(this.PARAMS.parent);
	BX.userOptions.save('global', 'settings', 'page_edit_control_enable', 'N');
	this.DIV.style.display = 'none';
}

/* common menu class */
BX.CMenu = function(arParams)
{
	BX.CMenu.superclass.constructor.apply(this);

	this.DIV.style.width = 'auto';//this.DIV.firstChild.offsetWidth + 'px';
	this.DIV.style.height = 'auto';//this.DIV.firstChild.offsetHeight + 'px';

	this.PARAMS = arParams || {};
	this.PARTS = {};

	this.PARAMS.ATTACH_MODE = this.PARAMS.ATTACH_MODE || 'bottom';

	this.DIV.className = 'bx-popup-menu';

	this.PARTS.LAYOUT_TABLE = this.DIV.appendChild(BX.create('TABLE', {
		props: {className: 'bx-popup-menu-wrapper'},
		attrs: {cellPadding: "0", cellSpacing: "0", border: "0"},
		children: [BX.create('TBODY')]
	}));
	this.PARTS.TABLE = BX.adjust(
		this.PARTS.LAYOUT_TABLE.tBodies[0].insertRow(-1).insertCell(-1),
		{props: {className: 'bx-popup-menu'}}
	).appendChild(BX.create('TABLE', {
		props: {className: 'bx-popup-menu-layout'},
		attrs: {cellPadding: "0", cellSpacing: "0", border: "0"},
		children: [BX.create('TBODY')]
	}));

	if (this.PARAMS.ITEMS)
	{
		this.PARAMS.ITEMS = BX.util.array_values(this.PARAMS.ITEMS);
		for (var i = 0, len = this.PARAMS.ITEMS.length; i < len; i++)
		{
			if ((i == 0 || i == len-1) && this.PARAMS.ITEMS[i].SEPARATOR)
				continue;

			this.addItem(this.PARAMS.ITEMS[i], i);
		}
	}

	BX.addCustomEvent(this, 'onMenuItemClick', BX.delegate(this.Hide, this));

	if (this.PARAMS.PARENT_MENU)
		BX.addCustomEvent(this, 'onMenuItemClick', BX.delegate(this.__call_parent, this));

	//BX.bind(this.DIV, 'mousedown', function(e) {BX.PreventDefault(e);});
}
BX.extend(BX.CMenu, BX.CWindowFloat);

BX.CMenu.prototype.Show = function()
{
	if (this.visible()) return;
	if (this.PARAMS.PARENT_MENU && !this.PARAMS.PARENT_MENU.visible()) return;

	BX.CMenu.superclass.Show.apply(this, []);

	BX.bind(document, 'click', BX.proxy(this.Hide, this));
	this.DIV.onclick = BX.PreventDefault;
	//this.PARAMS.parent.onclick = BX.PreventDefault;

	BX.onCustomEvent(this, 'onMenuOpen');
	BX.addClass(this.PARAMS.parent_attach, 'bx-pressed');

	this.adjustPos();
}

BX.CMenu.prototype.ShowMenu = function(parent, items)
{
	this.PARAMS.parent = parent;
	this.PARAMS.parent_attach = null;
	if (items)
		this.SetItems(items);

	setTimeout(BX.delegate(this.Show, this), 50);
}

BX.CMenu.prototype.SetItems = function(items)
{
	if (null != this.PARAMS.ITEMS)
	{
		for (var i = 0; i < this.PARAMS.ITEMS.length; i++)
		{
			if (BX.is_subclass_of(this.PARAMS.ITEMS[i], BX.CMenuItem))
				this.PARAMS.ITEMS[i].Destroy();

			this.PARAMS.ITEMS[i] = null;
		}
		this.PARAMS.ITEMS = null;
	}

	this.PARAMS.ITEMS = BX.util.array_values(items);
	for (var i = 0, len = this.PARAMS.ITEMS.length; i < len; i++)
	{
		if ((i == 0 || i == len-1) && this.PARAMS.ITEMS[i].SEPARATOR)
			continue;

		this.addItem(this.PARAMS.ITEMS[i], i);
	}
}

BX.CMenu.prototype.__call_parent = function()
{
	BX.onCustomEvent(this.PARAMS.PARENT_MENU, 'onMenuItemClick', arguments);
}

BX.CMenu.prototype.Close = // we shouldn't 'Close' window - only hide
BX.CMenu.prototype.Hide = function()
{
	if (!this.visible()) return;

	BX.CMenu.superclass.Hide.apply(this, arguments);

	BX.unbind(document, 'click', BX.proxy(this.Close, this));

	BX.onCustomEvent(this, 'onMenuClose');
	BX.removeClass(this.PARAMS.parent_attach, 'bx-pressed');

	this.DIV.onclick = null;
	//this.PARAMS.parent.onclick = null;
}

BX.CMenu.prototype.onUnRegister = function(clean)
{
	//if (!this.PARAMS.SUBMENU)
	//	BX.unbind(document, 'mousedown', BX.proxy(this.Hide, this));

	BX.CMenu.superclass.onUnRegister.apply(this, arguments);

	if (clean)
	{
		// TODO: kill menu items
	}
}

BX.CMenu.prototype.addItem = function(item, index)
{
	if (null == index)
		index = this.PARAMS.ITEMS.length;

	var ob = new BX.CMenuItem(item, this)

	//if (!ob.hasSubmenu())
	//	BX.bind(ob.Get(), 'click', BX.proxy(this.Hide, this))

	if (ob.Get())
	{
		this.PARTS.TABLE.tBodies[0].insertRow(-1).insertCell(-1).appendChild(ob.Get());
	}

	this.PARAMS.ITEMS[index] = ob;
}

BX.CMenu.prototype.__adjustMenuToNode = function()
{
	var pos = BX.pos(this.PARAMS.parent_attach);

	if (!pos.top)
	{
		this.DIV.style.top = '-1000px';
		this.DIV.style.left = '-1000px';
	}

	if (this.bTimeoutSet) return;

	var floatWidth = this.DIV.offsetWidth, floatHeight = this.DIV.offsetHeight;
	if (!floatWidth)
	{
		setTimeout(BX.delegate(function(){
			this.bTimeoutSet = false; this.__adjustMenuToNode();
		}, this), 100);

		this.bTimeoutSet = true;
		return;
	}

	var menu_pos = {};
	var scrollSize = BX.GetWindowScrollSize();

	if (BX.browser.IsIE() && !BX.browser.IsDoctype())
	{
		pos.top -= 4; pos.bottom -= 4;
		pos.left -= 2; pos.right -= 2;
	}

	switch (this.PARAMS.ATTACH_MODE)
	{
		case 'bottom':
			menu_pos.top = pos.bottom;
			menu_pos.left = pos.left;

			if (menu_pos.left > scrollSize.scrollWidth - floatWidth)
			{
				menu_pos.left = scrollSize.scrollWidth - floatWidth;
			}
		break;
		case 'right':
			menu_pos.top = pos.top;
			menu_pos.left = pos.right;

			if (menu_pos.left > scrollSize.scrollWidth - floatWidth)
			{
				menu_pos.left = pos.left - floatWidth - 1;
			}
		break;
	}

	if (menu_pos.top > scrollSize.scrollHeight - floatHeight)
	{
		menu_pos.top = scrollSize.scrollHeight - floatHeight;
	}

	if (menu_pos.top + menu_pos.left == 0)
		this.Hide();
	else
	{
		this.DIV.style.top = menu_pos.top + 'px';
		this.DIV.style.left = menu_pos.left + 'px';
	}
}

BX.CMenu.prototype.adjustToNode = function(el)
{
	this.PARAMS.parent_attach = BX(el) || this.PARAMS.parent_attach || this.PARAMS.parent;
	this.__adjustMenuToNode();
}

/* menu item class */
BX.CMenuItem = function(item, parentMenu)
{
	this.item = item
	this.parentMenu = parentMenu;

	if (this.item.SEPARATOR)
	{
		this.node = BX.create(
			'DIV', {props: {className: 'bx-popup-menu-separator'}}
		);
	}
	else
	{
		if (this.item.MENU && BX.type.isArray(this.item.MENU) && this.item.MENU.length <= 0)
		{
			this.node = null;
			return false;
		}


		if (this.item.MENU)
		{
			this.item.ONCLICK = null;
		}
		else if (this.item.ACTION && !this.item.ONCLICK)
		{
			this.item.ONCLICK = this.item.ACTION;
		}

		var events = {
			mouseover: function () {BX.addClass(this, 'bx-popup-item-over')},
			mouseout: function () {BX.removeClass(this, 'bx-popup-item-over')}
		};

		this.node = BX.create(
			'DIV', {
				'props': {className: 'bx-popup-item'},
				'events': events,
				'children': [BX.create('DIV', {style: {width: '100%'}})]
			}
		);

		if (this.item.ID)
			this.node.id = this.item.ID;

		if (this.item.MENU)
		{
			this.item.SUBMENU = new BX.CMenu({
				ATTACH_MODE:'right',
				ITEMS:this.item['MENU'],
				PARENT_MENU:this.parentMenu,
				parent: this.node
			});

			this.item.OPENER = new BX.COpener({
				DIV: this.node,
				TYPE: 'hover',
				MENU: this.item.SUBMENU,
				TIMEOUT: 500,
				mirror_events: true
			});

			BX.addCustomEvent(this.parentMenu, 'onItemMouseOver', BX.delegate(this.__other_mouseover, this));
		}
		else
		{
			BX.bind(this.node, 'click', BX.delegate(this.__clicked, this));
			BX.bind(this.node, 'mouseup', BX.delegate(this.__action_done, this));
		}

		var obRow = this.node.firstChild.appendChild(BX.create('TABLE', {
			props: {className: 'bx-popup-item'},
			attrs: {cellPadding: "0", cellSpacing: "0", border: "0", dir: "ltr"}, // !!!!!!!!!!!!!!!!!
			style: {width: '100%'},
			children: [BX.create('TBODY')]
		})).tBodies[0].insertRow(-1);

		BX.adjust(obRow.insertCell(-1), {
			props: {className: 'gutter'},
			children: [
				BX.create('DIV', {
					props: {className: (this.item.ICON || this.item.ICONCLASS) ? 'icon ' + (this.item.ICON || this.item.ICONCLASS) : 'icon'},
					style: (this.item.SRC || this.item.IMAGE) ? {
						'backgroundImage': 'url(' + (this.item.SRC || this.item.IMAGE) + ')'
					} : {}
				})
			]
		});

		BX.adjust(obRow.insertCell(-1), {
			props: {
				className: 'item' + (this.item.DEFAULT ? ' default' : ''),
				title: this.item.TITLE || ''
			},
			style: {whiteSpace: 'nowrap'},
			html: this.item.TEXT
		});

		if (this.item.MENU)
		{
			obRow.insertCell(-1).className = 'arrow';
		}

		BX.bind(this.node, 'mouseover', BX.delegate(this.__mouseover, this));
	}
}

BX.CMenuItem.prototype.__mouseover = function()
{
	BX.onCustomEvent(this.parentMenu, 'onItemMouseOver', [this]);
}

BX.CMenuItem.prototype.__other_mouseover = function(item)
{
	if (item !== this)
	{
		this.item.OPENER.Close();
	}
}

BX.CMenuItem.prototype.Get = function() {return this.node;}
BX.CMenuItem.prototype.GetItem = function() {return this.item;}
BX.CMenuItem.prototype.hasSubmenu = function() {return !!this.item.SUBMENU;}
BX.CMenuItem.prototype.Destroy = function() {
	this.node.parentNode.removeChild(this.node);
	this.node = null;
}

BX.CMenuItem.prototype.__clicked = function()
{
	if (this.item.ONCLICK)
	{
		setTimeout(this.item.ONCLICK, 10);
	}
}

BX.CMenuItem.prototype.__action_done = function ()
{
	if (this.parentMenu && !this.hasSubmenu())
	{
		setTimeout(BX.delegate(this.__call_parent, this), 10);
	}
}

BX.CMenuItem.prototype.__call_parent = function()
{
	BX.onCustomEvent(this.parentMenu, 'onMenuItemClick', [this]);
}

/******* HINT ***************/
// if function has 2 params - the 2nd one is hint html. otherwise hint_html is third and hint_title - 2nd;
// '<div onmouseover="BX.hint(this, 'This is &lt;b&gt;Hint&lt;/b&gt;')"'>;
// BX.hint(el, 'This is <b>Hint</b>') - this won't work, use constructor
BX.hint = function(el, hint_title, hint_html, hint_id)
{
	if (null == hint_html)
	{
		hint_html = hint_title;
		hint_title = '';
	}

	if (null == el.BXHINT)
	{
		el.BXHINT = new BX.CHint({
			parent: el, hint: hint_html, title: hint_title, id: hint_id
		});
		el.BXHINT.Show();
	}
}

BX.hint_replace = function(el, hint_title, hint_html)
{
	if (null == hint_html)
	{
		hint_html = hint_title;
		hint_title = '';
	}

	if (!el || !el.parentNode || !hint_html)
			return;

	var obHint = new BX.CHint({
		hint: hint_html,
		title: hint_title
	});

	obHint.CreateParent();

	el.parentNode.insertBefore(obHint.PARENT, el);
	el.parentNode.removeChild(el);

	obHint.PARENT.style.marginLeft = '5px';

	return el;
}

BX.CHint = function(params)
{
	this.PARENT = BX(params.parent);

	this.HINT = params.hint;
	this.HINT_TITLE = params.title;

	this.PARAMS = {}
	for (var i in this.defaultSettings)
	{
		if (null == params[i])
			this.PARAMS[i] = this.defaultSettings[i];
		else
			this.PARAMS[i] = params[i];
	}

	if (null != params.id)
		this.ID = params.id;

	this.timer = null;
	this.bInited = false;
	this.msover = true;

	if (this.PARAMS.showOnce)
	{
		this.__show();
		this.msover = false;
		this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
	}
	else if (this.PARENT)
	{
		BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
		BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
	}

	BX.addCustomEvent('onMenuOpen', BX.delegate(this.disable, this));
	BX.addCustomEvent('onMenuClose', BX.delegate(this.enable, this));
}

BX.CHint.prototype.defaultSettings = {
	show_timeout: 1000,
	hide_timeout: 500,
	dx: 2,
	showOnce: false,
	preventHide: true,
	min_width: 250
}

BX.CHint.prototype.CreateParent = function(element, params)
{
	if (this.PARENT)
	{
		BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
		BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
	}

	if (!params) params = {}
	var type = 'icon';

	if (params.type && (params.type == "link" || params.type == "icon"))
		type = params.type;

	if (element)
		type = "element";

	if (type == "icon")
	{
		var element = BX.create('IMG', {
			props: {
				src: params.iconSrc
					? params.iconSrc
					: "/bitrix/js/main/core/images/hint.gif"
			}
		});
	}
	else if (type == "link")
	{
		var element = BX.create("A", {
			props: {href: 'javascript:void(0)'},
			html: '[?]'
		});
	}

	this.PARENT = element;

	BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
	BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));

	return this.PARENT;
}

BX.CHint.prototype.Show = function()
{
	this.msover = true;

	if (null != this.timer)
		clearTimeout(this.timer);

	this.timer = setTimeout(BX.proxy(this.__show, this), this.PARAMS.show_timeout);
}

BX.CHint.prototype.Hide = function()
{
	this.msover = false;

	if (null != this.timer)
		clearTimeout(this.timer);

	this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
}

BX.CHint.prototype.__show = function()
{
	if (!this.msover || this.disabled) return;
	if (!this.bInited) this.Init();

	this.prepareAdjustPos();
	this.DIV.style.display = 'block';
	this.adjustPos();

	BX.bind(window, 'scroll', BX.proxy(this.__onscroll, this));

	if (this.PARAMS.showOnce)
	{
		this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
	}
}

BX.CHint.prototype.__onscroll = function()
{
	if (!BX.admin || !BX.admin.panel || !BX.admin.panel.isFixed()) return;

	if (this.scrollTimer) clearTimeout(this.scrollTimer);

	this.DIV.style.display = 'none';
	this.scrollTimer = setTimeout(BX.proxy(this.Reopen, this), this.PARAMS.show_timeout);
}

BX.CHint.prototype.Reopen = function()
{
	if (null != this.timer) clearTimeout(this.timer);
	this.timer = setTimeout(BX.proxy(this.__show, this), 50);
}

BX.CHint.prototype.__hide = function()
{
	if (this.msover) return;
	if (!this.bInited) return;

	BX.unbind(window, 'scroll', BX.proxy(this.Reopen, this));

	if (this.PARAMS.showOnce)
	{
		this.Destroy();
	}
	else
	{
		this.DIV.style.display = 'none';
	}
}

BX.CHint.prototype.__hide_immediately = function()
{
	this.msover = false;
	this.__hide();
}

BX.CHint.prototype.Init = function()
{
	this.DIV = document.body.appendChild(BX.create('DIV', {
		props: {className: 'bx-panel-tooltip'},
		style: {display: 'none'},
		children: [
			BX.create('DIV', {
				props: {className: 'bx-panel-tooltip-top-border'},
				html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
			}),
			(this.CONTENT = BX.create('DIV', {
				props: {className: 'bx-panel-tooltip-content'},
				children: [
					BX.create('DIV', {
						props: {className: 'bx-panel-tooltip-underlay'},
						children: [
							BX.create('DIV', {props: {className: 'bx-panel-tooltip-underlay-bg'}})
						]
					})
				]
			})),

			BX.create('DIV', {
				props: {className: 'bx-panel-tooltip-bottom-border'},
				html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
			})
		]
	}));

	if (this.ID)
	{
		this.CONTENT.insertBefore(BX.create('A', {
			attrs: {href: 'javascript:void(0)'},
			props: {className: 'bx-panel-tooltip-close'},
			events: {click: BX.delegate(this.Close, this)}
		}), this.CONTENT.firstChild)
	}

	if (this.HINT_TITLE)
	{
		this.CONTENT.appendChild(
			BX.create('DIV', {
				props: {className: 'bx-panel-tooltip-title'},
				text: this.HINT_TITLE
			})
		)
	}

	if (this.HINT)
	{
		this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV', {props: {className: 'bx-panel-tooltip-text'}})).appendChild(BX.create('SPAN', {html: this.HINT}));
	}

	if (this.PARAMS.preventHide)
	{
		BX.bind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));
		BX.bind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
	}

	this.bInited = true;
}

BX.CHint.prototype.prepareAdjustPos = function()
{
	this._wnd = {scrollPos: BX.GetWindowScrollPos(),scrollSize:BX.GetWindowScrollSize()};
}

BX.CHint.prototype.getAdjustPos = function()
{
	var res = {}, pos = BX.pos(this.PARENT);

	res.top = pos.bottom + this.PARAMS.dx;

	if (BX.admin && BX.admin.panel.DIV)
	{
		var min_top = BX.admin.panel.DIV.offsetHeight + this.PARAMS.dx;

		if (BX.admin.panel.isFixed())
		{
			min_top += this._wnd.scrollPos.scrollTop;
		}
	}

	if (res.top < min_top)
		res.top = min_top;
	else
	{
		if (res.top + this.DIV.offsetHeight > this._wnd.scrollSize.scrollHeight)
			res.top = pos.top - this.PARAMS.dx - this.DIV.offsetHeight;
	}

	res.left = pos.left;
	if (pos.left < this.PARAMS.dx) pos.left = this.PARAMS.dx;
	else
	{
		floatWidth = this.DIV.offsetWidth;

		var max_left = this._wnd.scrollSize.scrollWidth - floatWidth - this.PARAMS.dx;

		if (res.left > max_left)
			res.left = max_left;
	}

	return res;
}

BX.CHint.prototype.adjustWidth = function()
{
	if (this.bWidthAdjusted) return;

	var w = this.DIV.offsetWidth, h = this.DIV.offsetHeight;

	if (w > this.PARAMS.min_width)
		w = Math.round(Math.sqrt(1.618*w*h));

	if (w < this.PARAMS.min_width)
		w = this.PARAMS.min_width;

	this.DIV.style.width = w + "px";

	if (this._adjustWidthInt)
		clearInterval(this._adjustWidthInt);
	this._adjustWidthInt = setInterval(BX.delegate(this._adjustWidthInterval, this), 5);

	this.bWidthAdjusted = true;
}

BX.CHint.prototype._adjustWidthInterval = function()
{
	if (!this.DIV || this.DIV.style.display == 'none')
		clearInterval(this._adjustWidthInt);

	var
		dW = 20,
		maxWidth = 1500,
		w = this.DIV.offsetWidth,
		w1 = this.CONTENT_TEXT.offsetWidth;

	if (w > 0 && w1 > 0 && w - w1 < dW && w < maxWidth)
	{
		this.DIV.style.width = (w + dW) + "px";
		return;
	}

	clearInterval(this._adjustWidthInt);
}

BX.CHint.prototype.adjustPos = function()
{
	this.adjustWidth();

	var pos = this.getAdjustPos();

	this.DIV.style.top = pos.top + 'px';
	this.DIV.style.left = pos.left + 'px';
}

BX.CHint.prototype.Close = function()
{
	if (this.ID) BX.WindowManager.saveWindowOptions(this.ID, {display: 'off'});
	this.__hide_immediately();
	this.Destroy();
}

BX.CHint.prototype.Destroy = function()
{
	if (this.PARENT)
	{
		BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
		BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
	}

	if (this.DIV)
	{
		BX.unbind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
		BX.unbind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));

		BX.cleanNode(this.DIV, true);
	}
}

BX.CHint.prototype.enable = function(){this.disabled = false;}
BX.CHint.prototype.disable = function(){this.__hide_immediately(); this.disabled = true;}

BX.CHintSimple = function()
{
	BX.CHintSimple.superclass.constructor.apply(this, arguments);
}
BX.extend(BX.CHintSimple, BX.CHint);

BX.CHintSimple.prototype.Init = function()
{
	this.DIV = document.body.appendChild(BX.create('DIV', {props: {className: 'bx-tooltip-simple'}, style: {display: 'none'}, children: [(this.CONTENT = BX.create('DIV'))]}));

	if (this.HINT_TITLE)
		this.CONTENT.appendChild(BX.create('B', {text: this.HINT_TITLE}));

	if (this.HINT)
		this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV')).appendChild(BX.create('SPAN', {html: this.HINT}));

	if (this.PARAMS.preventHide)
	{
		BX.bind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));
		BX.bind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
	}

	this.bInited = true;
}

})(window)