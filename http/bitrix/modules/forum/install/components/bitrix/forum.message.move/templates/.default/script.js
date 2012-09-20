function ForumSearchTopic(oObj, bSetControl, url)
{
	if (typeof(oForum['topic_search']['object']) == "object")
		oObj = oForum['topic_search']['object'];
	if (typeof(oObj) != "object" || oObj == null)
		return false;
	oForum['topic_search']['object'] = oObj;
	
	if (url && url.length > 0)
		oForum['topic_search']['url'] = url;
	bSetControl = (bSetControl == "Y" || bSetControl == "N" ? bSetControl : "U");
	if (bSetControl == "N")
		oForum['topic_search']['action'] = 'dont_search';
	else if (bSetControl == "Y")
		oForum['topic_search']['action'] = 'search';
	
	var res = parseInt(oObj.value);

	if (res <= 0 || !parseInt(res))
	{
		document.getElementById('TOPIC_INFO').innerHTML = oText['topic_bad'];
	}
	else if (parseInt(oForum['topic_search']['value']) != res)
	{
		document.getElementById('TOPIC_INFO').innerHTML = oText['topic_wait'];
		oForum['topic_search']['value'] = oObj.value;
		ForumSendMessage(oObj.value, oForum['topic_search']['url']);
	}
	if (oForum['topic_search']['action'] == "search")
	{
		setTimeout(ForumSearchTopic, 1000);
	}
	return false;
}


function ForumSendMessage(id, url)
{
	id = parseInt(id);
	if (id <= 0 || !id)
		return false;
	if (!((typeof(url) == "string") && (url.length > 0)))
		return false;
	
	var TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID,
		function(data, TID)
		{
			try
			{
				eval('result = ' + data + ';');	
			}
			catch(e)
			{
				result = false;
			}
			
			if (typeof(result) == "object" && result != null)
				document.getElementById('TOPIC_INFO').innerHTML = result['TOPIC_TITLE'];
			else
				document.getElementById('TOPIC_INFO').innerHTML = oText['topic_not_found'];

			return;
		}
	);
	CPHttpRequest.Send(TID, url, {"AJAX_CALL" : "Y", "TID" : id});
	return false;
}