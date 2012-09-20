/////////////////////////////////////////////////////////////////////////////////////
// SequenceActivity
/////////////////////////////////////////////////////////////////////////////////////

SequenceActivity = function()
{
	var ob = new BizProcActivity();
	ob.Type = 'SequenceActivity';
	ob.childsContainer = null; //
	ob.iHead = 0;

	ob.LineMouseOver = function (e)
	{
		this.parentNode.style.backgroundImage = 'url(/bitrix/images/bizproc/arr_over.gif)';
	}

	ob.LineMouseOut = function (e)
	{
		this.parentNode.style.backgroundImage = 'url(/bitrix/images/bizproc/arr.gif)';
	}

	ob.OnClick = function (e)
	{
		/*
		var oActivity = eval("new "+"BizProcActivity"+"()");
		oActivity.Init({'id': Math.random(), 'childs': []});
		ob.AddActivity(oActivity, this.ind);
		*/
	}

	ob.lastDrop = false;
	ob.ondragging = function (e, X, Y)
	{
		if(!ob.childsContainer)
		 	return false;

		for(var i = 0; i <= ob.childActivities.length; i++)
		{
			var arrow = ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes[0];

			var pos = ActGetRealPos(arrow);
			if(pos.left < X && X < pos.right
				&& pos.top < Y && Y < pos.bottom)
			{
				arrow.onmouseover();
				ob.lastDrop = arrow;
				//console.debug(ob.Name + ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes.length + '('+pos.left+', '+pos.right+', '+pos.top+', '+pos.bottom+')' + '; X = ' + X + '; Y = ' + Y + '; ');
				return;
			}

			//console.debug(ob.Name + ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes.length + '('+pos.left+', '+pos.right+', '+pos.top+', '+pos.bottom+')' + '; X = ' + X + '; Y = ' + Y + '; ');
		}

		if(ob.lastDrop)
		{
			ob.lastDrop.onmouseout();
			ob.lastDrop = false;
		}
	}

	ob.h1id = DragNDrop.AddHandler('ondragging', ob.ondragging);

	ob.ondrop = function (e, X, Y)
	{
		if(!ob.childsContainer)
		 	return false;

		if(ob.lastDrop)
		{
			var oActivity;
			if(DragNDrop.obj.parentActivity)
			{

				// найдем на какой позиции перетаскиваемый элемент был в своем действии
				var i, pos = -1, pa = DragNDrop.obj.parentActivity;
				for(i = 0; i<pa.childActivities.length; i++)
				{
					if(pa.childActivities[i].Name == DragNDrop.obj.Name)
					{
						pos = i;
						break;
					}
				}

				// найдем на какую позицию его перетаскивают
				if(pa.Name != ob.Name || (pos != ob.lastDrop.ind && pos+1 != ob.lastDrop.ind))
				{
					// проверить чтобы не в самого себя был перенос
					var d = ob, s = false;

					while(d)
					{
						if(DragNDrop.obj.Namr == d.Name)
						{
							s = true;
							break;
						}
						d = d.parentActivity;
					}

					if(s)
					{
						alert('Вы не можете переместить действие в подчиненное этому же действию!');
					}
					else
					{
						// удалим его со старого места
						pa.childsContainer.deleteRow(pos*2 + 1 + pa.iHead);
						pa.childsContainer.deleteRow(pos*2 + 1 + pa.iHead);

						// пересчитаем все счетчики на прошлом месте
						for(var j = pos; j<pa.childActivities.length - 1; j++)
							pa.childActivities[j] = pa.childActivities[j+1];

						pa.childActivities.pop();

						for(j = 0; j <= pa.childActivities.length; j++)
							pa.childsContainer.rows[j*2 + pa.iHead].cells[0].childNodes[0].ind = j;

						// переставим все действие на новое место
						oActivity = DragNDrop.obj;
						ob.AddActivity(oActivity, ob.lastDrop.ind);
					}
				}
			}
			else
			{
				oActivity = CreateActivity(DragNDrop.obj);
				ob.AddActivity(oActivity, ob.lastDrop.ind);
			}
			ob.lastDrop.onmouseout();
			ob.lastDrop = false;
		}
	}

	ob.h2id = DragNDrop.AddHandler('ondrop', ob.ondrop);

	ob.ActivityRemoveChild = ob.RemoveChild;

	ob.RemoveChild = function (ch)
	{
		var i, j;
		for(i = 0; i<ob.childActivities.length; i++)
		{
			if(ob.childActivities[i].Name == ch.Name)
			{
				if(ob.childsContainer)
				{
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onmouseover = null;
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onmouseout = null;
					ob.childsContainer.rows[i*2+1 + ob.iHead].cells[0].childNodes[0].onclick = null;
				}

				ob.ActivityRemoveChild(ch);

				if(ob.childsContainer)
				{
					ob.childsContainer.deleteRow(i*2 + 1 + ob.iHead);
					ob.childsContainer.deleteRow(i*2 + 1 + ob.iHead);

					for(j = 0; j <= ob.childActivities.length; j++)
						ob.childsContainer.rows[j*2 + ob.iHead].cells[0].childNodes[0].ind = j;
				}

				break;
			}
		}
	}

	ob.RemoveResources = function (self)
	{
		//
		DragNDrop.RemoveHandler('ondragging', ob.h1id);
		DragNDrop.RemoveHandler('ondrop', ob.h2id);

		if(ob.childsContainer && ob.childsContainer.parentNode)
		{
			ob.childsContainer.parentNode.removeChild(ob.childsContainer);
			ob.childsContainer = null;
		}
	}

	ob.AddActivity = function (oActivity, pos)
	{
		var i;

		for(i = ob.childActivities.length; i>pos; i--)
			ob.childActivities[i] = ob.childActivities[i-1];

		ob.childActivities[pos] = oActivity;

		oActivity.parentActivity = ob;

		var c = ob.childsContainer.insertRow(pos*2 + 1 + ob.iHead).insertCell(-1);
		c.align = 'center';
		c.vAlign = 'center';

		oActivity.Draw(c);

		c = ob.childsContainer.insertRow(pos*2 + 2 + ob.iHead).insertCell(-1);
		c.align = 'center';
		c.vAlign = 'center';

		ob.CreateLine(pos+1);

		for(i = 0; i <= ob.childActivities.length; i++)
			ob.childsContainer.rows[i*2 + ob.iHead].cells[0].childNodes[0].ind = i;

		//alert(document.styleSheets[0].rules[0]);
		//setTimeout(ob.DDD2, 110);
	}

	ob.CreateLine = function(ind)
	{
		ob.childsContainer.rows[ind*2 + ob.iHead].cells[0].style.height = '40px';
		ob.childsContainer.rows[ind*2 + ob.iHead].cells[0].style.background = 'url(/bitrix/images/bizproc/arr.gif) no-repeat scroll 50% 50%';

		var i = ob.childsContainer.rows[ind * 2 + ob.iHead].cells[0].appendChild(document.createElement('IMG'));
		i.src = '/bitrix/images/1.gif';
		i.width = '28';
		i.height = '21';
		i.onmouseover = ob.LineMouseOver;
		i.onmouseout = ob.LineMouseOut;
		i.onclick = ob.OnClick;
		i.ind = ind;
	}

	ob.ActivityDraw = ob.Draw;
	ob.Draw = function (container)
	{
		ob.childsContainer = container.appendChild(_crt(1 + ob.childActivities.length*2 + ob.iHead, 1));
		ob.childsContainer.className = 'seqactivitycontainer';
		ob.childsContainer.id = ob.Name;

		ob.CreateLine(0);
		for(var i in ob.childActivities)
		{
			ob.childActivities[i].Draw(ob.childsContainer.rows[i*2 + 1 + ob.iHead].cells[0]);
			ob.CreateLine(parseInt(i) + 1);
		}

		if(ob.AfterSDraw)
			ob.AfterSDraw();
	}

	return ob;
}
