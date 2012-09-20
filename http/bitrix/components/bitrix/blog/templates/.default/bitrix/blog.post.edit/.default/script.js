function show_special()
{
	o = document.getElementById('special_perms');
	if (document.getElementById('blog_perms_1').checked==true)
		o.style.display='block';
	else
		o.style.display='none';
}

function changeDate()
{
	document.getElementById('date-publ').style.display = 'block';
	document.getElementById('date-publ-text').style.display = 'none';
	document.getElementById('DATE_PUBLISH_DEF').value = '';
}