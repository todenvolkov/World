function LearnTabs(testID, activeTab)
{
	this.activeTab = activeTab;
	this.testID = testID;
	this.questionPrefix = "learn_question_"+this.testID+"_";
	this.tabPrefix = "learn_tab_"+this.testID+"_";
	this.formPrefix = "form_self_"+this.testID+"_";


	this.DisableTab = function(tabIndex)
	{
		var question = document.getElementById(this.questionPrefix+tabIndex);
		var tab = document.getElementById(this.tabPrefix+tabIndex);

		if (question && tab)
		{
			question.style.display = 'none';
			tab.className = 'learn-tab';
		}
	}

	this.SelectTab = function(tabIndex)
	{
		var question = document.getElementById(this.questionPrefix+tabIndex);
		var tab = document.getElementById(this.tabPrefix+tabIndex);

		if (question && tab)
		{
			this.DisableTab(this.activeTab);
			this.activeTab = tabIndex;
			question.style.display = 'block';
			tab.className = 'learn-tab-selected';
		}
	}

	//Actiate first question
	this.SelectTab(this.activeTab);



	this.OnChangeAnswer = function()
	{
		var form = document.forms[this.formPrefix+this.activeTab];

		if(!form)
			return;

		var j = 0;
		for(i=0; i<form.elements.length; i++)
		{
			var el = form.elements[i];
			var type = el.type.toLowerCase();
			var tag = el.tagName.toLowerCase();
			if (type == 'radio' || type == 'checkbox')
			{
				if (el.checked == true)
				{
					form.submit.disabled = false;
					return;
				}
				j++;
			}
			else if (tag == 'select')
			{
				if (el.selectedIndex == 0)
				{
					form.submit.disabled = true;
					return;
				}
			}
		}
		if (document.getElementById('question_type').value == 'R')
		{
			form.submit.disabled = false;
		}
		else
		{
			form.submit.disabled = true;
		}
	}

	this.CheckAnswer = function()
	{
		var form = document.forms[this.formPrefix+this.activeTab];

		if(!form)
			return;

		var j = 0;
		for(i=0; i<form.elements.length; i++)
		{
			var el = form.elements[i];
			var type = el.type.toLowerCase();
			var tag = el.tagName.toLowerCase();
			if (type == 'radio' || type == 'checkbox')
			{
				if (form.elements['right_'+j].value == 'Y')
					document.getElementById('correct_'+this.testID+'_'+this.activeTab+'_'+j).className = 'learn-answer learn-answer-right';
				else
					document.getElementById('correct_'+this.testID+'_'+this.activeTab+'_'+j).className = 'learn-answer learn-answer-wrong';
				
				el.disabled = true;
				form.submit.disabled = true;
				j++;
			}
			else if (tag == 'select')
			{
				if (el.name == 'answer['+el.options[el.selectedIndex].value+']')
					document.getElementById('correct_'+this.testID+'_'+this.activeTab+'_'+j).className = 'learn-answer learn-answer-right';
				else
					document.getElementById('correct_'+this.testID+'_'+this.activeTab+'_'+j).className = 'learn-answer learn-answer-wrong';
				
				el.disabled = true;
				form.submit.disabled = true;
				j++;
			}
		}
		return false;
	}
}