function ForumInitSpoiler(oHead)
{
	if (typeof oHead != "object" || !oHead)
		return false; 
	var oBody = oHead.nextSibling; 
	oBody.style.display = (oBody.style.display == 'none' ? '' : 'none'); 
	oHead.className = (oBody.style.display == 'none' ? '' : 'forum-spoiler-head-open'); 
}