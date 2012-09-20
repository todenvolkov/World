(function(window){
if (window.BX.timer) return;

var timers = [],
	timeout = 100,
	current_moment = null,
	obTimer = null;

BX.timer = function(container, params)
{
	params = params || {};
	if (BX.type.isString(container) || BX.type.isElementNode(container))
		params.container = container;
	else if (typeof (container) == "object")
		params = container;

	if (!params.container)
		return false;

	var ob = new BX.CTimer(params);
	ob.TIMER_INDEX = timers.length;
	timers[timers.length] = ob;

	if (null == obTimer)
	{
		obTimer = setInterval(_RunTimer, timeout);
		BX.garbage(BX.timer.clear);
	}

	return ob;
}

BX.timer.stop = function(timer)
{
	timers[timer.TIMER_INDEX] = null;
}

BX.timer.clock = function(cont)
{
	return BX.timer({container: cont});
}

BX.timer.clear = function()
{
	clearInterval(obTimer);
	timers = null;
}

BX.CTimer = function(params, index)
{
	this.container = params.container;
	this.from = params.from ? parseInt(params.from.valueOf()) : null;
	this.to = params.to ? parseInt(params.to.valueOf()) : null;
	this.index = index;

	this.accuracy = params.accuracy || 60000; // default timing acccuracy is 1 minute

	if (this.from)
		this.from = new Date(parseInt(this.from.valueOf() / this.accuracy) * this.accuracy);
	if (this.to)
		this.to = new Date(parseInt(this.to.valueOf() / this.accuracy) * this.accuracy);

	this.callback = this.from ? this._callback_from : (this.to ? this._callback_to : this._callback);
	this.callback_finish = params.callback_finish;
}

BX.CTimer.prototype._callback = function(date)
{
	this.setValue(this.formatValue(date.getHours(), date.getMinutes(), date.getSeconds()));
}

BX.CTimer.prototype._callback_from = function(date)
{
	var diff = (date.valueOf() - this.from.valueOf())/1000;
	this.setValue(
		this.formatValue(
			parseInt(diff / 3600),
			parseInt((diff % 3600) / 60),
			parseInt(diff % 60)
		)
	);
}

BX.CTimer.prototype._callback_to = function(date)
{
	var diff = (this.to.valueOf() - date.valueOf())/1000;
	if (diff > 0)
	{
		this.setValue(
			this.formatValue(
				parseInt(diff / 3600),
				parseInt((diff % 3600) / 60),
				parseInt(diff % 60)
			)
		);
	}
	else
	{
		this.Finish();
	}
}

BX.CTimer.prototype.formatValue = function(h, m, s)
{
	var d = '<span' + (current_moment.getMilliseconds() > 500 ? ' style="visibility:hidden;"' : '') + '>:</span>';

	return BX.util.str_pad(h, 2, '0', 'left')
		+ d + BX.util.str_pad(m, 2, '0', 'left')
		+ (this.accuracy >= 60000
			? ''
			:
			(d + BX.util.str_pad(s, 2, '0', 'left'))
		);
}

BX.CTimer.prototype.setValue = function(value)
{
	if (BX.isReady) BX(this.container, true).innerHTML = value;
}

BX.CTimer.prototype.Finish = function()
{
	BX.timer.stop(this);

	if (this.callback_finish)
		this.callback_finish.apply(this);
}

function _RunTimer()
{
	current_moment = new Date();

	for (var i=0,len=timers.length;i<len;i++)
	{
		if (timers[i] && timers[i].callback)
			timers[i].callback.apply(timers[i], [current_moment]);
	}
}
})(window)