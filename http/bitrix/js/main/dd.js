jsDD = {
	arObjects: [],
	arDestinations: [],
	arContainers: [],
	arContainersPos: [],

	current_dest_index: false,
	current_node: null,

	wndSize: null,

	bStarted: false,
	bDisable: false,
	bDisableDestRefresh: false,
	
	Reset: function()
	{
		jsDD.arObjects = [];
		jsDD.arDestinations = [];
		jsDD.bStarted = false;
		jsDD.current_node = null;
		jsDD.current_dest_index = false;
		jsDD.bDisableDestRefresh = false;
		jsDD.bDisable = false;
		jsDD.x = null;
		jsDD.y = null;
		jsDD.wndSize = null;
	},
	
	registerObject: function (obNode)
	{
		obNode.onmousedown = jsDD.startDrag;
		obNode.__bxddid = jsDD.arObjects.length;
		
		jsDD.arObjects[obNode.__bxddid] = obNode;
	},

	registerDest: function (obDest)
	{
		obDest.__bxddid = jsDD.arDestinations.length;
		
		jsDD.arDestinations[obDest.__bxddid] = obDest;
		jsDD.refreshDestArea(obDest.__bxddid);
	},

	registerContainer: function (obCont)
	{
		jsDD.arContainers[jsDD.arContainers.length] = obCont;
	},

	getContainersScrollPos: function(x, y)
	{
		var pos = {'left':0, 'top':0};
		for(var i=0, n=jsDD.arContainers.length; i<n; i++)
		{
			if(jsDD.arContainers[i] && x >= jsDD.arContainersPos[i]["left"] && x <= jsDD.arContainersPos[i]["right"] && y >= jsDD.arContainersPos[i]["top"] && y <= jsDD.arContainersPos[i]["bottom"])
			{
				pos.left = jsDD.arContainers[i].scrollLeft;
				pos.top = jsDD.arContainers[i].scrollTop;
			}
		}
		return pos;
	},

	setContainersPos: function()
	{
		for(var i=0, n=jsDD.arContainers.length; i<n; i++)
		{
			if(jsDD.arContainers[i])
				jsDD.arContainersPos[i] = jsUtils.GetRealPos(jsDD.arContainers[i]);
		}
	},

	refreshDestArea: function(id)
	{
		if (typeof id == 'undefined')
		{
			for (var i = 0, cnt = jsDD.arDestinations.length; i < cnt; i++)
			{
				jsDD.refreshDestArea(i);
			}
		}
		else
		{
			if (null == jsDD.arDestinations[id]) 
				return;
			
			if (window.getBoundingClientRect)
			{
				var wndSize = jsUtils.GetWindowScrollSize();
			
				var arPos = jsDD.arDestinations[id].getBoundingClientRect();
				arPos = {
					left: arPos.left + wndSize.scrollLeft,
					top: arPos.top + wndSize.scrollTop,
					right: arPos.right + wndSize.scrollLeft,
					bottom: arPos.bottom + wndSize.scrollTop
				}
			}
			else
				var arPos = jsUtils.GetRealPos(jsDD.arDestinations[id]);
			
			jsDD.arDestinations[id].__bxpos = [arPos.left, arPos.top, arPos.right, arPos.bottom];
		}
	},
	
	/* DD process */
	
	startDrag: function(e)
	{
		if (jsDD.bDisable) return true;
	
		if(!e)
			e = window.event;
		
		jsDD.current_node = null;
		if (e.currentTarget)
		{
			jsDD.current_node = e.currentTarget;
			if (null == jsDD.current_node || null == jsDD.current_node.__bxddid) 
			{
				jsDD.current_node = null;
				return;
			}
		}
		else
		{
			jsDD.current_node = e.srcElement;
			if (null == jsDD.current_node)
				return;
			
			while (null == jsDD.current_node.__bxddid)
			{
				jsDD.current_node = jsDD.current_node.parentNode;
				if (jsDD.current_node.tagName == 'BODY')
					return;
			}
		}
		
		jsDD.bStarted = false;
		jsDD.bPreStarted = true;
		
		jsDD.wndSize = jsUtils.GetWindowSize();
		
		document.onmouseup = jsDD.stopDrag;
		jsUtils.addEvent(document, "mousemove", jsDD.drag);
		
		if(document.body.setCapture)
			document.body.setCapture();

		jsDD.denySelection();
		document.body.style.cursor = 'move';
		
		if (!jsDD.bDisableDestRefresh)
			jsDD.refreshDestArea();
			
		jsDD.setContainersPos();
	},
	
	start: function()
	{
		if (jsDD.bDisable) return true;
	
		if (jsDD.current_node.onbxdragstart)
			jsDD.current_node.onbxdragstart();

		for (var i = 0, cnt = jsDD.arDestinations.length; i < cnt; i++)
		{
			if (jsDD.arDestinations[i].onbxdestdragstart)
				jsDD.arDestinations[i].onbxdestdragstart(jsDD.current_node);
		}

		jsDD.bStarted = true;
		jsDD.bPreStarted = false;
	},
	
	drag: function(e)
	{
		if (jsDD.bDisable) 
			return true;
	
		if (!jsDD.bStarted)
			jsDD.start();

		if(!e)
			e = window.event;
		
		jsDD.x = e.clientX + jsDD.wndSize.scrollLeft;
		jsDD.y = e.clientY + jsDD.wndSize.scrollTop;

		if (jsDD.current_node.onbxdrag)
		{
			jsDD.current_node.onbxdrag(jsDD.x, jsDD.y);
		}
		
		var containersScroll = jsDD.getContainersScrollPos(jsDD.x, jsDD.y);
		var current_dest_index = jsDD.searchDest(jsDD.x+containersScroll.left, jsDD.y+containersScroll.top);
		
		if (current_dest_index !== jsDD.current_dest_index)
		{
			if (jsDD.current_dest_index !== false)
			{
				if (jsDD.current_node.onbxdraghout)
					jsDD.current_node.onbxdraghout(jsDD.arDestinations[jsDD.current_dest_index], jsDD.x, jsDD.y);

				if (jsDD.arDestinations[jsDD.current_dest_index].onbxdestdraghout)
					jsDD.arDestinations[jsDD.current_dest_index].onbxdestdraghout(jsDD.current_node, jsDD.x, jsDD.y);
			}
			
			if (current_dest_index !== false)
			{
				if (jsDD.current_node.onbxdraghover)
					jsDD.current_node.onbxdraghover(jsDD.arDestinations[current_dest_index], jsDD.x, jsDD.y);

				if (jsDD.arDestinations[current_dest_index].onbxdestdraghover)
					jsDD.arDestinations[current_dest_index].onbxdestdraghover(jsDD.current_node, jsDD.x, jsDD.y);
			}
		}
		
		jsDD.current_dest_index = current_dest_index;
	},
	
	stopDrag: function(e)
	{
		if(!e)
			e = window.event;
	
		jsDD.bPreStarted = false;
	
		if (jsDD.bStarted)
		{
			jsDD.x = e.clientX + jsDD.wndSize.scrollLeft;
			jsDD.y = e.clientY + jsDD.wndSize.scrollTop;

			if (null != jsDD.current_node.onbxdragstop)
				jsDD.current_node.onbxdragstop(jsDD.x, jsDD.y);
			
			var containersScroll = jsDD.getContainersScrollPos(jsDD.x, jsDD.y);
			var dest_index = jsDD.searchDest(jsDD.x+containersScroll.left, jsDD.y+containersScroll.top);

			if (false !== dest_index)
			{
				if (null != jsDD.arDestinations[dest_index].onbxdestdragfinish)
				{
					if (!jsDD.arDestinations[dest_index].onbxdestdragfinish(jsDD.current_node, jsDD.x, jsDD.y, e))
						dest_index = false;
					else
					{
						if (null != jsDD.current_node.onbxdragfinish)
							jsDD.current_node.onbxdragfinish(jsDD.arDestinations[dest_index], jsDD.x, jsDD.y);
					}
				}
			}
			
			if (false === dest_index)
			{
				if (null != jsDD.current_node.onbxdragrelease)
					jsDD.current_node.onbxdragrelease(jsDD.x, jsDD.y);
			}
			else
			{
				for (var i = 0, cnt = jsDD.arDestinations.length; i < cnt; i++)
				{
					if (i != dest_index && null != jsDD.arDestinations[i].onbxdestdragrelease)
						jsDD.arDestinations[i].onbxdestdragrelease(jsDD.current_node, jsDD.x, jsDD.y);
				}
			}
			
			for (var i = 0, cnt = jsDD.arDestinations.length; i < cnt; i++)
			{
				if (null != jsDD.arDestinations[i].onbxdestdragstop)
					jsDD.arDestinations[i].onbxdestdragstop(jsDD.current_node, jsDD.x, jsDD.y);
			}
		}
		
		if(document.body.releaseCapture)
			document.body.releaseCapture();

		jsUtils.removeEvent(document, "mousemove", jsDD.drag);

		document.onmouseup = null;
		
		jsDD.allowSelection();
		document.body.style.cursor = '';
		
		jsDD.current_node = null;
		
		if (jsDD.bStarted && !jsDD.bDisableDestRefresh)
			jsDD.refreshDestArea();

		jsDD.bStarted = false;
	},
	
	searchDest: function(x, y)
	{
		for (var i = 0, len = jsDD.arDestinations.length; i < len; i++)
		{
			if (
				jsDD.arDestinations[i].__bxpos[0] <= x && 
				jsDD.arDestinations[i].__bxpos[2] >= x &&
				
				jsDD.arDestinations[i].__bxpos[1] <= y && 
				jsDD.arDestinations[i].__bxpos[3] >= y
				)
			{
				return i;
			}
		}
		
		return false;
	},
	
	allowSelection: function()
	{
		document.onmousedown = null;
		var b = document.body;
		b.ondrag = null;
		b.onselectstart = null;
		b.style.MozUserSelect = '';
		
		if (jsDD.current_node)
		{
			jsDD.current_node.ondrag = null;
			jsDD.current_node.onselectstart = null;
			jsDD.current_node.style.MozUserSelect = '';
		}
	},
	
	denySelection: function()
	{
		document.onmousedown = jsUtils.False;
		var b = document.body;
		b.ondrag = jsUtils.False;
		b.onselectstart = jsUtils.False;
		b.style.MozUserSelect = 'none';
		if (jsDD.current_node)
		{
			jsDD.current_node.ondrag = jsUtils.False;
			jsDD.current_node.onselectstart = jsUtils.False;
			jsDD.current_node.style.MozUserSelect = 'none';
		}
	},
	
	Disable: function() {jsDD.bDisable = true;},
	Enable: function() {jsDD.bDisable = false;}
}