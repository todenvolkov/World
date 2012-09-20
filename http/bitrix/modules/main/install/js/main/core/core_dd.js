(function(window){
BX.DD = function(params)
{
	return new BX.DD.dragdrop(params);
}

BX.DD.allowSelection = function()
{
	document.onmousedown = null;
	var b = document.body;
	b.ondrag = null;
	b.onselectstart = null;
	b.style.MozUserSelect = '';
	
	// if (jsDD.current_node)
	// {
		// jsDD.current_node.ondrag = null;
		// jsDD.current_node.onselectstart = null;
		// jsDD.current_node.style.MozUserSelect = '';
	// }
}
	
BX.DD.denySelection: function()
{
	document.onmousedown = BX.False;
	var b = document.body;
	b.ondrag = BX.False;
	b.onselectstart = BX.False;
	b.style.MozUserSelect = 'none';
	// if (jsDD.current_node) 
	// {
		// jsDD.current_node.ondrag = jsUtils.False;
		// jsDD.current_node.onselectstart = jsUtils.False;
		// jsDD.current_node.style.MozUserSelect = 'none';
	// }
}

BX.DD.dragdrop = function(params)
{


}


})(window)