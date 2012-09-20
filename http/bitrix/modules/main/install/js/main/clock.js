function JCClock(config)
{
	this.config = config;
	this.config.AmPmMode = false;
	this.deltaHour = 0;
	this.MESS = this.config.MESS;
	this.bCreated = false;
}

JCClock.prototype = {
Create: function()
{
	this.bCreated = true;
	this.pInput = document.getElementById(this.config.inputId);
	this.pIcon = document.getElementById(this.config.iconId);

	// Create div
	this.oDiv = jsUtils.CreateElement('DIV', {className: 'bx-clock-div', id: this.config.inputId + '_div'}, {zIndex: 150});
	document.body.appendChild(this.oDiv);

	// Create analog clock
	var clockContDiv = jsUtils.CreateElement('DIV', {className: 'bxc-clock-cont bxc-iconkit-c'});
	this.arrowsContDiv = jsUtils.CreateElement('DIV', {className: 'bxc-arrows-cont h0 m0'});
	this.MACDiv = this.arrowsContDiv.appendChild(jsUtils.CreateElement('DIV', {className: 'bxc-mouse-control-cont'})); // Mouse-Arrow Control Div

	this.arrowsContDiv.appendChild(jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-min-arr-cont bxc-iconkit-a'}));
	this.arrowsContDiv.appendChild(jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-hour-arr-cont bxc-iconkit-a'}));
	clockContDiv.appendChild(this.arrowsContDiv);
	this.oDiv.appendChild(clockContDiv);

	this.CreateControls();

	this.InitMouseArrowControl();
},

CreateControls: function()
{
	this.ControlsCont = jsUtils.CreateElement('DIV', {className: 'bxc-controls-cont'});
	var
		i, opt, k,
		arHours = [],
		arMinutes = [],
		_this = this;

	for (i = 0; i < 24; i++)
		arHours.push(this.Int2Str(i));

	for (i = 0; i < 60; i += this.config.step)
		arMinutes[i] = (this.Int2Str(i));

	var oSelH = this.CreateSelect(arHours, 1, this.MESS.Hours);
	this.hourSelect = oSelH.pSelect;
	var oSelM = this.CreateSelect(arMinutes, this.config.step, this.MESS.Minutes);
	this.minSelect = oSelM.pSelect;

	this.hourSelect.onchange = function(){_this.SetTime(this.value, _this.curMin, true);}
	this.minSelect.onchange = function(){_this.SetTime(_this.curHour, this.value, true);}
	this.minSelect.onfocus = function(){_this.lastArrow = 'min';};
	this.hourSelect.onfocus = function(){_this.lastArrow = 'hour';};

	var insBut = jsUtils.CreateElement('INPUT', {type: 'button', value: this.MESS.Insert});
	insBut.onclick = function(){_this.Submit();}

	// Close button
	var closeImg = jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-close bxc-iconkit-c', title: this.MESS.Close});
	closeImg.onclick = function(){_this.Close();};

	var span = jsUtils.CreateElement('SPAN', {className: 'double-dot'});
	span.innerHTML = ':';
	oSelH.pWnd.style.marginLeft = '15px';
	this.ControlsCont.appendChild(oSelH.pWnd);
	this.ControlsCont.appendChild(span);
	this.ControlsCont.appendChild(oSelM.pWnd);

	if (this.config.AmPmMode)
	{
		this.AmPm = jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-iconkit-c bxc-am', title: 'a.m.'});
		this.AmPm.onclick = function(){};
		this.ControlsCont.appendChild(this.AmPm);
	}
	this.ControlsCont.appendChild(insBut);
	this.ControlsCont.appendChild(closeImg);
	this.pTitle = this.ControlsCont.appendChild(jsUtils.CreateElement('DIV', {className: 'bxc-title'}));
	this.pTitle.onmousedown = function(e) {jsFloatDiv.StartDrag(e, _this.oDiv); _this.bRecalculateCoordinates = true;};
	this.oDiv.appendChild(this.ControlsCont);
},

CalculateCoordinates: function()
{
	// GetPosition
	var pos = jsUtils.GetRealPos(this.oDiv);
	this.top = pos.top;
	this.left = pos.left;
	this.centerX = pos.left + 55;
	this.centerY = pos.top + 71;

	var
		_this = this,
		mal = 32, // minute arrow length (in px)
		hal = 25, // hour arrow length (in px)
		x = this.centerX, y = this.centerY, // Coordinates of center
		i, deg, xi, yi, abs_x, abs_y, xi1, yi1, abs_x1, abs_y1,
		delta = 8; // inaccuracy

	this.arHourCoords = [];
	this.bJumpByMinArrow30 = false;

	for(i = 0; i < 12; i++)
	{
		deg = (i * 30) * Math.PI / 180;
		xi = Math.round(hal * Math.sin(deg));
		yi = Math.round(hal * Math.cos(deg));
		abs_x = x + xi;
		abs_y = y - yi;

		xi1 = Math.round(16 * Math.sin(deg));
		yi1 = Math.round(16 * Math.cos(deg));
		abs_x1 = x + xi1;
		abs_y1 = y - yi1;

		this.arHourCoords[i] = {
			x : abs_x,
			y : abs_y,
			x_min: abs_x - delta, x_max: abs_x + delta,
			y_min: abs_y - delta, y_max: abs_y + delta,
			x_min1: abs_x1 - delta, x_max1: abs_x1 + delta,
			y_min1: abs_y1 - delta, y_max1: abs_y1 + delta
		};
	}

	this.arMinCoords = {};
	for(i = 0; i < 12; i++)
	{
		deg = (i * 30) * Math.PI / 180;
		xi = Math.round(mal * Math.sin(deg));
		yi = Math.round(mal * Math.cos(deg));
		abs_x = x + xi;
		abs_y = y - yi;

		xi1 = Math.round(18 * Math.sin(deg));
		yi1 = Math.round(18 * Math.cos(deg));
		abs_x1 = x + xi1;
		abs_y1 = y - yi1;

		this.arMinCoords[i * 5] =
		{
			x : abs_x,
			y : abs_y,
			x_min: abs_x - delta, x_max: abs_x + delta,
			y_min: abs_y - delta, y_max: abs_y + delta,
			x_min1: abs_x1 - delta, x_max1: abs_x1 + delta,
			y_min1: abs_y1 - delta, y_max1: abs_y1 + delta
		};
	}
	this.bRecalculateCoordinates = false;
},

Show: function()
{
	if (!this.bCreated)
		this.Create();
	this.lastArrow = 'min';
	var startValue = this.pInput.value.toString();
	if (startValue.indexOf(':') == -1)
	{
		if (this.config.initTime.length <= 0 || this.config.initTime.indexOf(':') == -1)
			startValue = new Date().getHours() + ':' + new Date().getMinutes();
		else
			startValue = this.config.initTime;
	}
	var arT = startValue.split(':');
	this.SetTime(parseInt(arT[0], 10) || 0, parseInt(arT[1], 10) || 0);

	var pos = this.AlignToPos(jsUtils.GetRealPos(this.pIcon));
	this.top = pos.top;
	this.left = pos.left;

	jsFloatDiv.Show(this.oDiv, this.left, this.top);
	this.oDiv.style.display = 'block';
	jsFloatDiv.AdjustShadow(this.oDiv);
	this.CalculateCoordinates();

	var _this = this;
	window['_bxc_onmousedown' + this.config.inputId] = function(e){_this.CheckClick(e);};
	window['_bxc_onkeypress' + this.config.inputId] = function(e){_this.OnKeyDown(e);};
	setTimeout(function(){jsUtils.addEvent(document, "mousedown", window['_bxc_onmousedown' + _this.config.inputId])}, 10);
	jsUtils.addEvent(document, "keypress", window['_bxc_onkeypress' + this.config.inputId]);
},

AlignToPos: function(pos)
{
	var
		w = 110,
		h = 170,
		x = pos.left,
		y = pos.top - h,
		//size = jsUtils.GetWindowInnerSize(),
		scroll = jsUtils.GetWindowScrollPos();

	if (scroll.scrollTop > y || y < 0)
		y = pos.top + 20;

	return {left: x, top: y};
},

Close: function()
{
	jsUtils.removeEvent(document, "mousedown", window['_bxc_onmousedown' + this.config.inputId]);
	jsUtils.removeEvent(document, "keypress", window['_bxc_onkeypress' + this.config.inputId]);
	jsFloatDiv.Close(this.oDiv);
	this.oDiv.style.display = 'none';
},

Submit: function()
{
	var value = this.Int2Str(this.curHour) + ':' + this.Int2Str(this.curMin);
	this.pInput.value = value;
	if (this.pInput.onchange && typeof this.pInput.onchange == 'function')
		this.pInput.onchange();
	this.Close();
},

SetTime: function(h, m, bDontSetDigClock, bJumpByHourArrow)
{
	h = parseInt(h, 10);
	if (h < 0 || h > 23)
		h = 0;

	m = parseInt(m, 10);
	var step = this.config.step;
	if (Math.round(m / step) != m / step)
		m = Math.round(m / step) * step;
	if (m < 0 || m > 59)
		m = 0;

	if (!bJumpByHourArrow)
		this.deltaHour = h >= 12 ? 12 : 0;

	this.curMin = m;
	this.curHour = h;

	this.pTitle.innerHTML = this.Int2Str(h) + ':' + this.Int2Str(m);
	this.SetTimeAn(h, m);
	if (!bDontSetDigClock)
		this.SetTimeDig(h, m);
},

SetTimeAnH: function(h, m)
{
	if (h == 0)
	{
		if(this.curHour < 12 && this.curHour > 6)
			this.deltaHour = 12;
		if(this.curHour < 24 && this.curHour > 18)
			this.deltaHour = 0;
	}

	if (this.curHour == 0 && h == 11)
	{
		h = 23;
		this.deltaHour = 12;
	}
	else if (this.curHour == 12 && h == 11)
	{
		h = 11;
		this.deltaHour = 0;
	}
	else
	{
		h += this.deltaHour;
	}
	this.SetTime(h, m, false, true);
},

SetTimeAnM: function(h, m)
{
	m = parseInt(m, 10);
	var step = this.config.step;
	if (Math.round(m / step) != m / step)
		m = Math.round(m / step) * step;
	if (m < 0 || m > 59)
		m = 0;

	if (m == 30)
	{
		this.bJumpByMinArrow30 = true;
	}
	else if (this.bJumpByMinArrow30 && m == 0)
	{
		if (this.curMin > 30 && this.curMin < 59)
		{
			this.bJumpByMinArrow30 = false;
			return this.SetTime(++h, m);
		}
		if (this.curMin > 0 && this.curMin < 30)
		{
			this.bJumpByMinArrow30 = false;
			if (h == 0)
				h = 24;
			return this.SetTime(--h, m);
		}
	}
	this.SetTime(h, m);
},

SetTimeAn: function(h, m)
{
	h = parseInt(h, 10);
	if (isNaN(h))
		h = 0;
	m = parseInt(m, 10);
	if (isNaN(m))
		m = 0;

	if (h >= 12)
		h -= 12;
	var cn = 'bxc-arrows-cont';
	if (h * 5 == m)
		cn += ' hideh hm' + h;
	else
		cn += ' h' + h + ' m' + m;
	this.arrowsContDiv.className = cn;
},

CreateSelect: function(arValues, step, title)
{
	var select = jsUtils.CreateElement('INPUT', {type: 'text', className: 'bxc-cus-sel', size: "1", title: title});
	var spinStop = function(d)
	{
		select._bxmousedown = false;
		clearInterval(window.bxinterval);
	}
	var spinChange = function(d)
	{
		if (!select._bxmousedown)
			return spinStop();
		var k = parseInt(select.value, 10);
		if (isNaN(k))
			k = 0;
		k = k + d;
		if (k >= arValues.length)
			k = 0;
		else if (k < 0)
			k = arValues.length - 1;
		select.value = arValues[k];
		select.onchange();
	}
	var spinStart = function(d)
	{
		if (window.bxinterval)
			spinStop();
		window.bxinterval = setInterval(function(){spinChange(d);}, 150);
		select._bxmousedown = true;
		jsUtils.addEvent(document, "mouseup", spinStop);
		spinChange(d);
	};
	select.onkeydown = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 38) // Up
		{
			spinStart(step);
			spinStop();
		}
		else if (e.keyCode == 40) // Down
		{
			spinStart(-step);
			spinStop();
		}
	};

	var
		tbl = jsUtils.CreateElement('TABLE', {className: 'bxc-cus-sel-tbl'}),
		r = tbl.insertRow(-1),
		c = r.insertCell(-1);

	//tbl.border = "1";
	//c.setAttribute("rowSpan", "2");
	c.rowSpan = 2;
	c.appendChild(select);
	

	c = r.insertCell(-1);
	c.appendChild(jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-slide-up bxc-iconkit-c'}));
	c.title = this.MESS.Up;
	c.className = 'bxc-pointer';
	c.onmousedown = function(){spinStart(step)};
	c = tbl.insertRow(-1).insertCell(-1);
	c.appendChild(jsUtils.CreateElement('IMG', {src: '/bitrix/images/1.gif', className: 'bxc-slide-down bxc-iconkit-c'}));
	c.title = this.MESS.Down;
	c.className = 'bxc-pointer';
	c.onmousedown = function(){spinStart(-step)};

	return {pSelect: select, pWnd: tbl};
},

SetTimeDig: function(h, m)
{
	this.hourSelect.value = this.Int2Str(h);
	this.minSelect.value = this.Int2Str(m);
},

InitMouseArrowControl: function()
{
	var _this = this;
	this.MACDiv.onmousedown = function(e){_this.MACMouseDown(e)};
	this.MACDiv.ondrag = jsUtils.False;
	this.MACDiv.onselectstart = jsUtils.False;
	this.MACDiv.style.MozUserSelect = 'none';
},

MACMouseDown: function(e)
{
	if (this.bRecalculateCoordinates)
		this.CalculateCoordinates();

	if(!e) e = window.event;
	var
		_this = this,
		ar,
		mode = false,
		windowSize = jsUtils.GetWindowSize(),
		mouseX = e.clientX + windowSize.scrollLeft,
		mouseY = e.clientY + windowSize.scrollTop;

	this.ddMode = false;
	ar = this.arMinCoords[this.curMin];
	if ((mouseX > ar.x_min && mouseX < ar.x_max && mouseY > ar.y_min && mouseY < ar.y_max) ||
		(mouseX > ar.x_min1 && mouseX < ar.x_max1 && mouseY > ar.y_min1 && mouseY < ar.y_max1))
	{
		this.ddMode = 'min';
		this.lastArrow = 'min';
	}

	if (!this.ddMode)
	{
		ar = this.arHourCoords[this.curHour >= 12 ? this.curHour - 12 : this.curHour];
		if ((mouseX > ar.x_min && mouseX < ar.x_max && mouseY > ar.y_min && mouseY < ar.y_max) ||
			(mouseX > ar.x_min1 && mouseX < ar.x_max1 && mouseY > ar.y_min1 && mouseY < ar.y_max1))
		{
			this.ddMode = 'hour';
			this.lastArrow = 'hour';
		}
	}

	if (this.ddMode === false)
	{
		var
			dist,
			min = 1000,
			min_ind = 0;

		if (this.lastArrow == 'hour')
		{
			for(i = 0; i < 12; i++)
			{
				dist = this.GetDistance(this.arHourCoords[i].x, this.arHourCoords[i].y, mouseX, mouseY);
				if (dist <= min)
				{
					min = dist;
					min_ind = i;
				}
			}
			this.SetTimeAnH(min_ind, this.curMin);
		}
		else if (this.lastArrow == 'min')
		{
			for(i = 0; i < 12; i++)
			{
				dist = this.GetDistance(this.arMinCoords[i * 5].x, this.arMinCoords[i * 5].y, mouseX, mouseY);
				if (dist <= min)
				{
					min = dist;
					min_ind = i;
				}
			}
			this.SetTimeAnM(this.curHour, min_ind * 5);
		}
		return;
	}

	this.ControlsCont.style.zIndex = '145'; // Put controls div under MACDiv
	this.MACDiv.onmousemove = function(e){_this.MACMouseMove(e)};
	this.MACDiv.onmouseup = function(e){_this.MACMouseUp(e)};
},

MACMouseMove: function(e)
{
	if (!this.ddMode)
		return this.StopDD();

	if(!e) e = window.event;
	var
		dist,
		min = 1000,
		min_ind = 0,
		windowSize = jsUtils.GetWindowSize(),
		mouseX = e.clientX + windowSize.scrollLeft,
		mouseY = e.clientY + windowSize.scrollTop;

	if (this.ddMode == 'hour')
	{
		for(i = 0; i < 12; i++)
		{
			dist = this.GetDistance(this.arHourCoords[i].x, this.arHourCoords[i].y, mouseX, mouseY);
			if (dist <= min)
			{
				min = dist;
				min_ind = i;
			}
		}
		this.SetTimeAnH(min_ind, this.curMin);
	}
	else if (this.ddMode == 'min')
	{
		for(i = 0; i < 12; i++)
		{
			dist = this.GetDistance(this.arMinCoords[i * 5].x, this.arMinCoords[i * 5].y, mouseX, mouseY);
			if (dist <= min)
			{
				min = dist;
				min_ind = i;
			}
		}
		this.SetTimeAnM(this.curHour, min_ind * 5);
	}
},

GetDistance: function(x1, y1, x2, y2)
{
	return Math.round(Math.sqrt((Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2))));
},

MACMouseUp: function(e)
{
	this.StopDD();
},

StopDD: function()
{
	this.ddMode = false;
	this.ControlsCont.style.zIndex = '156';  // Put controls div over MACDiv
	this.MACDiv.onmousemove = null;
	this.MACDiv.onmouseup = null;
	return false;
},

Int2Str: function(i)
{
	i = parseInt(i, 10);
	if (isNaN(i))
		i = 0;
	return i < 10 ? '0' + i.toString() : i.toString();
},

CheckClick: function(e)
{
	if (this.bRecalculateCoordinates)
		return;
	if(!e) e = window.event
	if(!e) return;
	var
		windowSize = jsUtils.GetWindowSize(),
		mouseX = e.clientX + windowSize.scrollLeft,
		mouseY = e.clientY + windowSize.scrollTop;
	if (mouseX < this.left - 2 || mouseX > this.left + 112 || mouseY < this.top - 2 || mouseY > this.top + 172)
		this.Close();
},

OnKeyDown: function(e)
{
	if(!e) e = window.event
	if(!e) return;
	if(e.keyCode == 27)
		this.Close();
}
}