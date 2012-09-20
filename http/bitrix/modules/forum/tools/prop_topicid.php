<?
IncludeModuleLangFile(__FILE__);
class CIBlockPropertyTopicID
{
	function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"S",
			"USER_TYPE"		=>"TopicID",
			"DESCRIPTION"		=> GetMessage("IBLOCK_PROP_TOPICID_DESC"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyTopicID","GetPropertyFieldHtml"),
			//optional handlers
			"ConvertToDB"		=>array("CIBlockPropertyTopicID","ConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyTopicID","ConvertFromDB"),
		);
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		if (CModule::IncludeModule("forum"))
		{
			if (intVal($value["VALUE"]) <= 0)
			{
				$value["VALUE"] = '';
				$res = '';
			}
			else
			{
				$value["VALUE"] = intVal($value["VALUE"]);
				$arTopic = CForumTopic::GetByID($value["VALUE"]);
				if ($arTopic)
					$res = "[<a title='".GetMessage("IBLOCK_PROP_FORUM_VIEW_TOPIC")."' class='tablebodylink' href='/bitrix/admin/forum_topics.php?lang=".LANG."'>".intVal($arTopic["ID"])."</a>] (".htmlspecialcharsEx($arTopic["TITLE"]).") ";
				else
					$res = "&nbsp;".GetMessage("MAIN_NOT_FOUND");
			}
			return FindTopicID(htmlspecialchars($strHTMLControlName["VALUE"]), $value["VALUE"], $res, htmlspecialchars($strHTMLControlName["FORM_NAME"]));
		}
		return false;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	function ConvertToDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
			$value["VALUE"] = intVal($value["VALUE"]);
		return $value;
	}

	function ConvertFromDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
		{
			$value["VALUE"] = intVal($value["VALUE"]);
		}
		return $value;
	}
}

function FindTopicID($tag_name, $tag_value, $topic_name="", $form_name = "form1", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton", $search_page="/bitrix/admin/forum_topics_search.php")
{
	global $APPLICATION;
	$tag_name_x = preg_replace("/([^a-z0-9]|\[|\])/is", "x", $tag_name);
	if($APPLICATION->GetGroupRight("forum") >= "R")
	{
		$strReturn = "
<input type=\"text\" name=\"".$tag_name."\" id=\"".$tag_name."\" value=\"".$tag_value."\" size=\"".$tag_size."\" maxlength=\"".$tag_maxlength."\" class=\"".$tag_class."\">
<IFRAME style=\"width:0px; height:0px; border: 0px\" src=\"javascript:void(0)\" name=\"hiddenframe".$tag_name."\" id=\"hiddenframe".$tag_name."\"></IFRAME>
<input class=\"".$button_class."\" type=\"button\" name=\"FindTopic\" id=\"FindTopic\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=".$form_name."&FC=".$tag_name."', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"".$button_value."\">
<span id=\"div_".$tag_name."\">".$topic_name."</span>
<script type=\"text/javascript\">
";
		if($topic_name=="")
			$strReturn.= "var tv".$tag_name_x."='';\n";
		else
			$strReturn.= "var tv".$tag_name_x."='".CUtil::JSEscape($tag_value)."';\n";

		$strReturn.= "
function Change".$tag_name_x."()
{
	var DV_".$tag_name_x.";
	DV_".$tag_name_x." = document.getElementById(\"div_".$tag_name."\");";
		if (strLen(trim($form_name)) <= 0)
		{
			$strReturn .= "
	if (tv".$tag_name_x."!=document.getElementById('".$tag_name."').value)
	{
		tv".$tag_name_x."=document.getElementById('".$tag_name."').value;
		if (tv".$tag_name_x."!='')
		{
			DV_".$tag_name_x.".innerHTML = '<i>".GetMessage("MAIN_WAIT")."</i>';
			document.getElementById(\"hiddenframe".$tag_name."\").src='/bitrix/admin/get_topics.php?ID='+tv".$tag_name_x."+'&strName=".$tag_name."&lang=".LANG.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")."';
		}
		else
			DV_".$tag_name_x.".innerHTML = '';
	}";
		}
		else 
		{
			$strReturn .= "
	if (tv".$tag_name_x."!=document.".$form_name."['".$tag_name."'].value)
	{
		tv".$tag_name_x."=document.".$form_name."['".$tag_name."'].value;
		if (tv".$tag_name_x."!='')
		{
			DV_".$tag_name_x.".innerHTML = '<i>".GetMessage("MAIN_WAIT")."</i>';
			document.getElementById(\"hiddenframe".$tag_name."\").src='/bitrix/admin/get_topics.php?ID='+tv".$tag_name_x."+'&strName=".$tag_name."&lang=".LANG.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")."';
		}
		else
			DV_".$tag_name_x.".innerHTML = '';
	}";
		}
	$strReturn .= "
	setTimeout(function(){Change".$tag_name_x."()},1000);
}
Change".$tag_name_x."();

</script>
";
	}
	else
	{
		$strReturn = "
			<input type=\"text\" name=\"$tag_name\" id=\"$tag_name\" value=\"$tag_value\" size=\"$tag_size\" maxlength=\"strMaxLenght\">
			<input type=\"button\" name=\"FindTopic\" id=\"FindTopic\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=$form_name&FC=$tag_name', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"$button_value\">
			$topic_name
			";
	}
	return $strReturn;
}
?>
