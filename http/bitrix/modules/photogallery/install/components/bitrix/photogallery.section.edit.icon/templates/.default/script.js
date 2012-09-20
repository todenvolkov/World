if (window.__photo_send_by_covers == null)
{
	function __photo_send_by_covers(anchor)
	{
		if (!anchor) { return false; }
		var TID = jsAjax.InitThread();
		jsAjax.AddAction(TID, function(data){
			try
			{
				var id_differ = Math.random();
				var id_for_replace = ["photo-cover-images", "photo-cover-navigation"];
				var div = document.body.appendChild(document.createElement("DIV"));
				div.style.display = 'none';
				for (var ii in id_for_replace)
					data = data.replace(id_for_replace[ii], id_for_replace[ii] + '' + id_differ);
	
				div.innerHTML = data;
				for (var ii in id_for_replace)
				{
					var main_div = document.getElementById(id_for_replace[ii]);
					if (!main_div) { continue; }
					else if (id_for_replace[ii] == "photo-cover-images")
						main_div.innerHTML += document.getElementById(id_for_replace[ii] + id_differ).innerHTML;
					else
						main_div.innerHTML = document.getElementById(id_for_replace[ii] + id_differ).innerHTML;
				}
				div.parentNode.removeChild(div);
			} catch (e) { }
		});
		jsAjax.Send(TID, anchor.href, {"AJAX_CALL" : "Y"});
		anchor.parentNode.innerHTML += '<span>...</span>'
		return false;
	}
}