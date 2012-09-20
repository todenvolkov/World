(function(window){

var defaultOptions = {
	time: 1.0,
	step: 0.05,
	type: 'linear',
	
	allowFloat: false
}

/*
options: {
	start: start value or {param: value, param: value}
	finish: finish value or {param: value, param: value}
	time: time to transform in seconds
	type: linear|accelerated|decelerated|custom func name
	callback,
	callback_start,
	callback_complete,
	
	step: time between steps in seconds
	allowFloat: false|true
}
*/
BX.fx = function(options)
{
	this.options = options;
	
	if (!this.__checkOptions())
		return false;
	
	this.__go = BX.delegate(this.go, this);
	
	this.PARAMS = {};
}

BX.fx.prototype.__checkOptions = function()
{
	if (typeof this.options.start != typeof this.options.finish)
		return false;
	
	if (null == this.options.time) this.options.time = defaultOptions.time;
	if (null == this.options.step) this.options.step = defaultOptions.step;
	if (null == this.options.type) this.options.type = defaultOptions.type;
	if (null == this.options.allowFloat) this.options.allowFloat = defaultOptions.allowFloat;
	
	this.options.time *= 1000;
	this.options.step *= 1000;
	
	if (typeof this.options.start != 'object')
	{
		this.options.start = {_param: this.options.start};
		this.options.finish = {_param: this.options.finish};
	}
	
	var i;
	for (i in this.options.start)
	{
		if (null == this.options.finish[i])
		{
			this.options.start[i] = null;
			delete this.options.start[i];
		}
	}
	
	if (!BX.type.isFunction(this.options.type))
	{
		if (BX.type.isFunction(window[this.options.type]))
			this.options.type = window[this.options.type];
		else if (BX.type.isFunction(BX.fx.RULES[this.options.type]))
			this.options.type = BX.fx.RULES[this.options.type];
		else
			this.options.type = BX.fx.RULES[defaultOptions.type];
	}
	
	return true;
}

BX.fx.prototype.go = function()
{
	var timeCurrent = new Date().valueOf();
	if (timeCurrent < this.PARAMS.timeFinish)
	{
		for (var i in this.PARAMS.current)
		{
			this.PARAMS.current[i][0] = this.options.type.apply(this, [{
				start_value: this.PARAMS.start[i][0],
				finish_value: this.PARAMS.finish[i][0],
				current_value: this.PARAMS.current[i][0],
				current_time: timeCurrent - this.PARAMS.timeStart, 
				total_time: this.options.time
			}]);
		}
		
		this._callback(this.options.callback);
		
		if (!this.paused)
			this.PARAMS.timer = setTimeout(this.__go, this.options.step);
	}
	else
	{
		this.stop();
	}
}

BX.fx.prototype._callback = function(cb)
{
	var tmp = {};
	
	cb = cb || this.options.callback;
	
	for (var i in this.PARAMS.current)
	{
		tmp[i] = (this.options.allowFloat ? this.PARAMS.current[i][0] : Math.round(this.PARAMS.current[i][0])) + this.PARAMS.current[i][1];
	}
	
	return cb.apply(this, [null != tmp['_param'] ? tmp._param : tmp]);
}

BX.fx.prototype.start = function()
{
	var i,value, unit;
	
	this.PARAMS.start = {};
	this.PARAMS.current = {};
	this.PARAMS.finish = {};
	
	for (i in this.options.start)
	{
		value = parseInt(this.options.start[i]);
		unit = (this.options.start[i]+'').substring((value+'').length);
		this.PARAMS.start[i] = [value, unit];
		this.PARAMS.current[i] = [value, unit];
		this.PARAMS.finish[i] = [parseInt(this.options.finish[i]), unit];
	}

	this._callback(this.options.callback_start);
	this._callback(this.options.callback);
	
	this.PARAMS.timeStart = new Date().valueOf();
	this.PARAMS.timeFinish = this.PARAMS.timeStart + this.options.time;
	this.PARAMS.timer = setTimeout(BX.delegate(this.go, this), this.options.step);
	
	return this;
}

BX.fx.prototype.pause = function()
{
	if (this.paused)
	{
		this.PARAMS.timer = setTimeout(this.__go, this.options.step);
		this.paused = false;
	}
	else
	{
		clearTimeout(this.PARAMS.timer);
		this.paused = true;
	}
}

BX.fx.prototype.stop = function()
{
	if (this.PARAMS.timer)
		clearTimeout(this.PARAMS.timer);

	this.PARAMS.current = this.PARAMS.finish;
	this._callback(this.options.callback);
	this._callback(this.options.callback_complete);
}

/* 
type rules of animation 
 - linear - simple linear animation
 - accelerated
 - decelerated
*/

/*
	params: {
		start_value, finish_value, current_time, total_time
	}
*/
BX.fx.RULES = 
{
	linear: function(params)
	{
		return params.start_value + (params.current_time/params.total_time) * (params.finish_value - params.start_value);
	},

	decelerated: function(params)
	{
		return params.start_value + Math.sqrt(params.current_time/params.total_time) * (params.finish_value - params.start_value);
	},

	accelerated: function(params)
	{
		var q = params.current_time/params.total_time;
		return params.start_value + q * q * (params.finish_value - params.start_value);
	}
}

/****************** effects realizaion ************************/

/*
	type = 'fade' || 'scroll' || 'scale' || 'fold'
*/

BX.fx.hide = function(el, type, opts)
{
	el = BX(el);
	
	if (typeof type == 'object' && null == opts)
	{
		opts = type;
		type = opts.type
	}
	
	if (!BX.type.isNotEmptyString(type))
	{
		el.style.display = 'none'; 
		return;
	}
	
	var fxOptions = BX.fx.EFFECTS[type](el, opts, 0);
	fxOptions.callback_complete = function () {
		if (opts.hide !== false)
			el.style.display = 'none';
		
		if (opts.callback_complete)
			opts.callback_complete.apply(this, arguments);
	}
	
	return (new BX.fx(fxOptions)).start();
}

BX.fx.show = function(el, type, opts)
{
	el = BX(el);
	
	if (typeof type == 'object' && null == opts)
	{
		opts = type;
		type = opts.type
	}
	
	if (!opts) opts = {};
	
	if (!BX.type.isNotEmptyString(type))
	{
		el.style.display = 'block';
		return;
	}
	
	var fxOptions = BX.fx.EFFECTS[type](el, opts, 1);

	fxOptions.callback_complete = function () {
		if (opts.show !== false)
			el.style.display = 'block';
		
		if (opts.callback_complete)
			opts.callback_complete.apply(this, arguments);
	}
	
	return (new BX.fx(fxOptions)).start();
}

BX.fx.EFFECTS = {
	scroll: function(el, opts, action)
	{
		if (!opts.direction) opts.direction = 'vertical';
		
		var param = opts.direction == 'horizontal' ? 'width' : 'height';
		
		var val = parseInt(BX.style(el, param));
		if (isNaN(val))
		{
			val = BX.pos(el)[param];
		}
		
		if (action == 0)
			var start = val, finish = opts.min_height ? parseInt(opts.min_height) : 0;
		else
			var finish = val, start = opts.min_height ? parseInt(opts.min_height) : 0;
		
		return {
			'start': start,
			'finish': finish,
			'time': opts.time || defaultOptions.time,
			'type': 'linear',
			callback_start: function () {
				if (BX.style(el, 'position') == 'static')
					el.style.position = 'relative';
				
				el.style.overflow = 'hidden';
				el.style[param] = start + 'px';
				el.style.display = 'block';
			},
			callback: function (val) {el.style[param] = val + 'px';}
		}
	},
	
	fade: function(el, opts, action)
	{
		var fadeOpts = {
			'time': opts.time || defaultOptions.time,
			'type': action == 0 ? 'decelerated' : 'linear',
			'start': action == 0 ? 1 : 0,
			'finish': action == 0 ? 0 : 1,
			'allowFloat': true
		};
		
		if (BX.browser.IsIE())
		{
			fadeOpts.start *= 100; fadeOpts.finish *= 100; fadeOpts.allowFloat = false;
			
			fadeOpts.callback_start = function() {
				el.style.display = 'block';
				el.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=" + fadeOpts.start + ")";
			};
			
			fadeOpts.callback = function (val) {
				(el.filters['DXImageTransform.Microsoft.alpha']||el.filters.alpha).opacity = val;
			}
		}
		else
		{
			fadeOpts.callback_start = function () {
				el.style.display = 'block';
			}
			
			fadeOpts.callback = function (val) {
				el.style.opacity = el.style.KhtmlOpacity = el.style.MozOpacity = val;
			};
		}
		
		return fadeOpts;
	},
	
	fold: function (el, opts, action) // 'fold' is a combination of two consequential 'scroll' hidings.
	{
		if (action != 0) return;
	
		var pos = BX.pos(el);
		var coef = pos.height / (pos.width + pos.height);
		var old_opts = {time: opts.time || defaultOptions.time, callback_complete: opts.callback_complete, hide: opts.hide};
	
		opts.type = 'scroll';
		opts.direction = 'vertical';
		opts.min_height = opts.min_height || 10;
		opts.hide = false;
		opts.time = coef * old_opts.time;
		opts.callback_complete = function()
		{
			el.style.whiteSpace = 'nowrap';
		
			opts.direction = 'horizontal';
			opts.min_height = null;
			
			opts.time = old_opts.time - opts.time;
			opts.hide = old_opts.hide;
			opts.callback_complete = old_opts.callback_complete;
			
			BX.fx.hide(el, opts);
		}
		
		return BX.fx.EFFECTS.scroll(el, opts, action);
	}, 
	
	scale: function (el, opts, action)
	{
		var val = {width: parseInt(BX.style(el, 'width')), height: parseInt(BX.style(el, 'height'))};
		if (isNaN(val.width) || isNaN(val.height))
		{
			var pos = BX.pos(el)
			val = {width: pos.width, height: pos.height};
		}
		
		if (action == 0)
			var start = val, finish = {width: 0, height: 0};
		else
			var finish = val, start = {width: 0, height: 0};
		
		return {
			'start': start,
			'finish': finish,
			'time': opts.time || defaultOptions.time,
			'type': 'linear',
			callback_start: function () {
				el.style.position = 'relative';
				el.style.overflow = 'hidden';
				el.style.height = start.height + 'px';
				el.style.width = start.width + 'px';
				el.style.display = 'block';
			},
			callback: function (val) {
				el.style.height = val.height + 'px'; 
				el.style.width = val.width + 'px';
			}
		}
	}
}

})(window)