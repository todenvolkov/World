<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

ClearVars();

if(!$USER->CanDoOperation('edit_ratings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

// set default values
$bTypeChange 			 = isset($_POST["ACTION"]) && $_POST["ACTION"] == 'type_changed' ? true : false;
$ratingId	 			 = isset($_POST["RATING_ID"]) ? intval($_POST["RATING_ID"]) : 0;
$sRatingWeightType 	 	 = isset($_POST["RATING_WEIGHT_TYPE"]) && $_POST["RATING_WEIGHT_TYPE"] == 'auto' ? 'auto' : 'manual';
$sRatingAuthrorityWeight = isset($_POST["RATING_AUTHORITY_WEIGHT"]) && $_POST["RATING_AUTHORITY_WEIGHT"] == 'N' ? 'N' : 'Y';
$ratingNormalization	 = isset($_POST["RATING_NORMALIZATION"]) ? intval($_POST["RATING_NORMALIZATION"]) : 1000;
$ratingCountVote	 	 = isset($_POST["RATING_COUNT_VOTE"]) ? intval($_POST["RATING_COUNT_VOTE"]) : 10;
$ratingStartValue	 	 = isset($_POST["RATING_START_AUTHORITY"]) ? intval($_POST["RATING_START_AUTHORITY"]) : 3;
$communityLastVisit	 	 = isset($_POST["RATING_COMMUNITY_LAST_VISIT"]) && intval($_POST["RATING_COMMUNITY_LAST_VISIT"]) > 0 ? intval($_POST["RATING_COMMUNITY_LAST_VISIT"]) : 90;
$ratingAuthorityDefault  = isset($_POST["RATING_AUTHORITY_DEFAULT"]) ? intval($_POST["RATING_AUTHORITY_DEFAULT"]) : 0;


// save settings
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['save']<>"" && check_bitrix_sessid())
{
	if ($sRatingWeightType == 'auto') 
	{
		COption::SetOptionString("main", "rating_normalization", $ratingNormalization);
		COption::SetOptionString("main", "rating_count_vote", $ratingCountVote);
		COption::SetOptionString("main", "rating_authority_weight_formula", $sRatingAuthrorityWeight);
		COption::SetOptionString("main", "rating_community_last_visit", $communityLastVisit);
	}
	if ($sRatingWeightType == 'manual') 
	{
		CRatings::SetWeight($_POST['CONFIG']);
	}

	COption::SetOptionString("main", "rating_weight_type", $sRatingWeightType);
	COption::SetOptionString("main", "rating_start_authority", $ratingStartValue);

	CRatings::SetAuthorityRating($ratingId);	
	CRatings::SetVoteGroup($_POST['RATING_VOTE_GROUP_ID'], 'R');
	CRatings::SetVoteGroup($_POST['RATING_VOTE_AUTHORITY_GROUP_ID'], 'A');
	
	if ($ratingAuthorityDefault > 0)
	{
		$arParams = array();
		
		if ($ratingAuthorityDefault == 1)
			$arParams['DEFAULT_CONFIG_NEW_USER'] = 'Y';
			
		if ($ratingAuthorityDefault == 2)
			$arParams['DEFAULT_USER_ACTIVE'] = 'Y';
			
		if ($ratingAuthorityDefault == 3)
		{
			$arParams['DEFAULT_USER_ACTIVE'] = 'Y';
			$arParams['DEFAULT_CONFIG_NEW_USER'] = 'Y';
		}
		CRatings::SetAuthorityDefaultValue($arParams);
	}
	$_SESSION["SESS_ADMIN"]["RATING_CONFIG_SUCCESS"]=array("MESSAGE"=>GetMessage("RATING_CONFIG_SUCCESS"), "TYPE"=>"OK");
}

// if you changed the type of calculation or choose a different rating for the calculation of the authority, change the default values
if(!$bTypeChange) 
{
	$ratingId = CRatings::GetAuthorityRating();
	$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
	if ($sRatingWeightType == 'auto')
	{
		$ratingNormalization = COption::GetOptionString("main", "rating_normalization", 1000);
		$ratingCountVote = COption::GetOptionString("main", "rating_count_vote", 10);
		$sRatingAuthrorityWeight = COption::GetOptionString("main", "rating_authority_weight_formula", 'Y');
		$communityLastVisit = COption::GetOptionString("main", "rating_community_last_visit", '90');
	}
	$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
}

$APPLICATION->SetTitle(GetMessage("MAIN_RATING_SETTINGS"));
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/ratings.css");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

// displaying a message on the action taken
if(is_array($_SESSION["SESS_ADMIN"]["RATING_CONFIG_SUCCESS"]))
{
	CAdminMessage::ShowMessage($_SESSION["SESS_ADMIN"]["RATING_CONFIG_SUCCESS"]);
	$_SESSION["SESS_ADMIN"]["RATING_CONFIG_SUCCESS"]=false;
}
if($message)
	echo $message->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("RATING_SETTINGS_TAB_WEIGHT"), "TITLE"=>''),
	array("DIV" => "edit2", "TAB" => GetMessage("RATING_SETTINGS_TAB_MAIN"), "TITLE"=>''),
);
$editTab = new CAdminTabControl("editTab", $aTabs);
?>
<form name="form1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="POST">
<input type="hidden" name="ACTION" value="" id="ACTION">
<?
echo bitrix_sessid_post();

$editTab->Begin();
$editTab->BeginNextTab();
?>
	<tr>
		<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_RATING_WEIGHT_TYPE')?>:</td>
		<td>
			<?=InputType("radio", 'RATING_WEIGHT_TYPE', 'auto', $sRatingWeightType, false, GetMessage('RATING_SETTINGS_FRM_TYPE_AUTO'), "onclick=\"jsTypeChanged('form1')\"");?> 
			<?=InputType("radio", 'RATING_WEIGHT_TYPE', 'manual', $sRatingWeightType, false, GetMessage('RATING_SETTINGS_FRM_TYPE_MANUAL'),  "onclick=\"jsTypeChanged('form1')\"");?>  
		</td>
	</tr>
<?
$arRatingsList = array();
$db_res = CRatings::GetList(array("ID" => "ASC"), array("ENTITY_ID" => "USER"));
while ($res = $db_res->Fetch())
{
	$arRatingsList['reference'][] = "[ ".$res["ID"]." ] ".$res["NAME"];
	$arRatingsList['reference_id'][] = $res["ID"];
}
?>
	<tr>
		<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_RATING_ID')?>:</td>
		<td><?=SelectBoxFromArray("RATING_ID", $arRatingsList, $ratingId, "", "onChange=\"jsTypeChanged('form1')\"");?></td>
	</tr>
<?
if ($sRatingWeightType == 'auto')
{
	$communitySize = COption::GetOptionString("main", "rating_community_size", 3);
	$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);
	?>

		<tr>
			<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_COMMUNITY_SIZE_USER')?>:</td>
			<td><?=($communitySize>0? $communitySize: GetMessage('RATING_SETTINGS_FRM_COMMUNITY_SIZE_ZERO'))?></td>
		</tr>
		<tr>
			<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_RATING_NORMALIZATION')?>:</td>
			<td><input type="text" size="2" value="<?=$ratingNormalization?>" name="RATING_NORMALIZATION"> / <?=GetMessage('RATING_SETTINGS_FRM_COMMUNITY_SIZE')?></td>
		</tr>
		<tr>
			<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_RATING_COUNT_VOTE')?>:</td>
			<td><input type="text" size="2" value="<?=$ratingCountVote?>" name="RATING_COUNT_VOTE"> + <?=GetMessage('RATING_SETTINGS_FRM_AUTHORITY')?></td>
		</tr>
		<tr>
			<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_AUTHORITY_WEIGHT')?>:</td>
			<td>
				<?=InputType("radio", 'RATING_AUTHORITY_WEIGHT', 'Y', $sRatingAuthrorityWeight, false, GetMessage('RATING_SETTINGS_FRM_AUTHORITY_WEIGHT_Y'));?> 
				<?=InputType("radio", 'RATING_AUTHORITY_WEIGHT', 'N', $sRatingAuthrorityWeight, false, GetMessage('RATING_SETTINGS_FRM_AUTHORITY_WEIGHT_N'));?>  
			</td>
		</tr>
	<?
}
if ($sRatingWeightType == 'manual')
{
	$db_res = CRatings::GetRatingValueInfo($ratingId);
	$arValueInfo = $db_res->Fetch();
	?>
		<tr class="heading">
			<td colspan="2"><?=GetMessage('RATING_SETTINGS_CAT_RATING_INFO')?></td>
		</tr>		
		<tr>
			<td><?=GetMessage('RATING_SETTINGS_FRM_RATING_INFO_MAX')?>:</td>
			<td><?=round($arValueInfo['MAX'],2)?></td>
		</tr>
		<tr>
			<td><?=GetMessage('RATING_SETTINGS_FRM_RATING_INFO_MIN')?>:</td>
			<td><?=round($arValueInfo['MIN'],2)?></td>
		</tr>
		<tr>
			<td><?=GetMessage('RATING_SETTINGS_FRM_RATING_INFO_AVG')?>:</td>
			<td><?=round($arValueInfo['AVG'],2)?></td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?=GetMessage('RATING_SETTINGS_CAT_CONFIG')?></td>
		</tr>	
	<?
	$db_res = CRatings::GetWeightList(array("RATING_TO" => "ASC"), array());
	$conditionCount = 0;
	$conditionMaxCount = 0;
	?>
		<tr>
			<td colspan="2" align="center" class="rating_settings" id="rating_settings_weight">
				<?
				$arCondition = array();
				while ($res = $db_res->Fetch())
				{
					$arCondition[] = $res;
					$conditionMaxCount++;
				}
				foreach($arCondition as $key => $res)
				{
					$conditionCount++;
				?>
					<div id="rating_settings_weight_<?=$conditionCount?>">
						<?if($conditionCount == $conditionMaxCount):?>
							<span><?=GetMessage('RATING_SETTINGS_FRM_FROM')?> <input type="text" size="6" value="<?=($res['RATING_FROM'] == -1000000? 0 : floatVal($res['RATING_FROM']-0.0001))?>" id="rating_settings_weight_<?=$conditionCount?>_from" name="CONFIG[<?=$conditionCount?>][RATING_FROM]" class="rating_settings_from" readonly></span>
						<?else:?>
							<span><?=GetMessage('RATING_SETTINGS_FRM_TO')?> <input type="text" size="7" value="<?=$res['RATING_TO']?>" id="rating_settings_weight_<?=$conditionCount?>_to" name="CONFIG[<?=$conditionCount?>][RATING_TO]" onchange="jsChangeRatingWeight()"></span> 
						<?endif;?>
						<span><?=GetMessage('RATING_SETTINGS_FRM_WEIGHT')?> <input type="text" size="6" value="<?=$res['WEIGHT']?>" id="rating_settings_weight_<?=$conditionCount?>_weight" name="CONFIG[<?=$conditionCount?>][WEIGHT]"></span> 
						<span><?=GetMessage('RATING_SETTINGS_FRM_COUNT')?> <input type="text" size="3" value="<?=$res['COUNT']?>" id="rating_settings_weight_<?=$conditionCount?>_count" name="CONFIG[<?=$conditionCount?>][COUNT]"></span>
						<?if($conditionCount != $conditionMaxCount):?>
							<a href="#delete" onclick="jsDeleteRatingWeight(<?=$conditionCount?>);return false;"><img src="/bitrix/themes/.default/images/cross.gif" title="<?=GetMessage('RATING_SETTINGS_FRM_DELETE')?>" border="0" align="absmiddle"></a>
						<?endif;?>
					</div>
				<?}?>
				<div id="rating_settings_weight_add" rel="<?=$conditionMaxCount?>"><span class="settings_add"><a href="#add" onclick="jsAddRatingWeight();return false;"><?=GetMessage('RATING_SETTINGS_FRM_ADD')?></a></span></div>
			</td>
		</tr>
	<?
}
$editTab->BeginNextTab();
?>
	<tr>
		<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_RATING_START_AUTHORITY')?>:</td>
		<td><input type="text" size="2" value="<?=$ratingStartValue?>" name="RATING_START_AUTHORITY"> <?=($sRatingWeightType == 'auto'? 'x '.GetMessage('RATING_SETTINGS_FRM_RATING_NORMALIZATION'):'')?></td>
	</tr>	
<?
if ($sRatingWeightType == 'auto')
{
	?>		
		<tr>
			<td width="50%"><?=GetMessage('RATING_SETTINGS_FRM_COMMUNITY_LAST_VISIT')?>:</td>
			<td><input type="text" size="2" value="<?=$communityLastVisit?>" name="RATING_COMMUNITY_LAST_VISIT"></td>
		</tr>
	<?	
}
$arRatingVoteGroupIdList = Array();
$rsGroups = CGroup::GetList($by="c_sort", $order="asc", $filter=array());
while($arGroup = $rsGroups->Fetch())
{
	$arRatingVoteGroupIdList["REFERENCE"][] = $arGroup["NAME"];
	$arRatingVoteGroupIdList["REFERENCE_ID"][] = $arGroup["ID"];
}

$arRatingVoteGroupID = array();
$rsGroups = CRatings::GetVoteGroup('R');
while($arGroup = $rsGroups->Fetch())
	$arRatingVoteGroupID[] = $arGroup["GROUP_ID"];

?>
	<tr>
		<td width="50%" valign="top"><?=GetMessage('RATING_SETTINGS_FRM_RATING_VOTE_GROUP_ID')?>:</td>
		<td><?=SelectBoxMFromArray("RATING_VOTE_GROUP_ID[]", $arRatingVoteGroupIdList, $arRatingVoteGroupID, "", true, 3);?></td>
	</tr>
<?

$arRatingVoteAuthorityGroupID = array();
$rsGroups = CRatings::GetVoteGroup('A');
while($arGroup = $rsGroups -> Fetch())
	$arRatingVoteAuthorityGroupID[] = $arGroup["GROUP_ID"];
?>	
	<tr>
		<td width="50%" valign="top"><?=GetMessage('RATING_SETTINGS_FRM_RATING_VOTE_AUTHORITY_GROUP_ID')?></td>
		<td><?=SelectBoxMFromArray("RATING_VOTE_AUTHORITY_GROUP_ID[]", $arRatingVoteGroupIdList, $arRatingVoteAuthorityGroupID, "", true, 3);?></td>
	</tr>
	<tr>
		<td width="50%" valign="top" style="padding-top: 9px;"><?=GetMessage('RATING_SETTINGS_FRM_DEF_VALUE')?>:</td>
		<td>
			<?=InputType("radio", 'RATING_AUTHORITY_DEFAULT', '1', '', false, GetMessage('RATING_SETTINGS_FRM_DEF_VALUE_1'));?> 
			<?
			if (IsModuleInstalled("forum"))
			{
				echo '<br>'.InputType("radio", 'RATING_AUTHORITY_DEFAULT', '2', '', false, GetMessage('RATING_SETTINGS_FRM_DEF_VALUE_2'));
				echo '<br>'.InputType("radio", 'RATING_AUTHORITY_DEFAULT', '3', '', false, GetMessage('RATING_SETTINGS_FRM_DEF_VALUE_3'));
			}
			?>
			<br><?=InputType("radio", 'RATING_AUTHORITY_DEFAULT', '0', '0', false, GetMessage('RATING_SETTINGS_FRM_DEF_VALUE_4'));?> 
		</td>
	</tr>
<?
$editTab->Buttons();
?>
	<input type="submit" accesskey="x" name="save" value="<?=GetMessage("RATING_SETTINGS_BUTTON_SAVE")?>">
	<input type="button" name="cancel" value="<?=GetMessage("RATING_SETTINGS_BUTTON_RESET")?>" title="<?=GetMessage("RATING_SETTINGS_BUTTON_RESET_TITLE")?>" onclick="window.location='<?=($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"rating_index.php?lang=".LANG)?>'">
<?
$editTab->End();
?>
</form>
<script>
<?if ($sRatingWeightType == 'manual'):?>
    var new_weight_config_to = 50;
    var new_weight_config_weight = 1;
    var new_weight_config_count = 10;
	var div_settings_next = <?=$conditionMaxCount?>;
	
	function jsAddRatingWeight()
	{
		// Variable definition references to DOM objects
		var div_add_button		= BX('rating_settings_weight_add');
		var div_settings_end	= BX('rating_settings_weight_<?=$conditionMaxCount?>');
		var div_settings_last	= parseInt(div_add_button.getAttribute('rel'));
		div_settings_next		= div_settings_next+1;
		
		if (div_settings_last != 0)
		{
			var div_settings_to		= parseFloat(BX('rating_settings_weight_'+div_settings_last+(div_settings_last == <?=$conditionMaxCount?> ? '_from' : '_to')).value);
			var div_settings_weight = parseFloat(BX('rating_settings_weight_'+div_settings_last+'_weight').value);
			var div_settings_count  = parseFloat(BX('rating_settings_weight_'+div_settings_last+'_count').value);
		}
		else
		{
			var div_settings_to		= 0;
			var div_settings_weight = new_weight_config_weight;
			var div_settings_count  = new_weight_config_count;
			div_settings_end = div_add_button;
		}
		
		// iterate value if it is not first condition in list
		if (div_settings_last != <?=$conditionMaxCount?>)
		{
			div_settings_to = div_settings_to + new_weight_config_to;
			div_settings_weight = div_settings_weight + new_weight_config_weight;
			div_settings_count = div_settings_count + new_weight_config_count;
		} 
		else
		{
			div_settings_to = 0;
			div_settings_weight = 0;
			div_settings_count = 0;
		}
		
		div_settings_to 	= isNaN(div_settings_to)? 0: div_settings_to;
		div_settings_weight = isNaN(div_settings_weight)? 0: div_settings_weight;
		div_settings_count  = isNaN(div_settings_count)? 0: div_settings_count;
		
		// Create new DOM object
		var el=document.createElement('div');
		el.id='rating_settings_weight_'+div_settings_next;
		el.innerHTML = '<span><?=GetMessage('RATING_SETTINGS_FRM_TO')?> <input type="text" size="7" value="'+div_settings_to+'" id="rating_settings_weight_'+div_settings_next+'_to" name="CONFIG['+div_settings_next+'][RATING_TO]" onchange="jsChangeRatingWeight()"></span>\
						<span><?=GetMessage('RATING_SETTINGS_FRM_WEIGHT')?> <input type="text" size="7" value="'+div_settings_weight+'" id="rating_settings_weight_'+div_settings_next+'_weight" name="CONFIG['+div_settings_next+'][WEIGHT]"></span>\
						<span><?=GetMessage('RATING_SETTINGS_FRM_COUNT')?> <input type="text" size="6" value="'+div_settings_count+'" id="rating_settings_weight_'+div_settings_next+'_count" name="CONFIG['+div_settings_next+'][COUNT]"></span>\
						<a href="#delete" onclick="jsDeleteRatingWeight('+div_settings_next+');return false;"><img src="/bitrix/themes/.default/images/cross.gif" title="<?=GetMessage('RATING_SETTINGS_FRM_DELETE')?>" border="0" align="absmiddle"></a>';
		BX('rating_settings_weight').insertBefore(el, div_settings_end);
		
		div_add_button.setAttribute('rel', div_settings_next);
		
		// define "from" config variable
		div_settings_end_from 	= BX('rating_settings_weight_<?=$conditionMaxCount?>_from');
		div_settings_end_weight = BX('rating_settings_weight_<?=$conditionMaxCount?>_weight');
		div_settings_end_count	= BX('rating_settings_weight_<?=$conditionMaxCount?>_count');
		
		div_settings_end_from.value = div_settings_to;
		
		// replace values of variables only if previous value is more
		if (div_settings_end_weight.value < div_settings_weight + new_weight_config_weight)
			div_settings_end_weight.value = div_settings_weight + new_weight_config_weight;		
		if (div_settings_end_count.value < div_settings_count + new_weight_config_count)	
			div_settings_end_count.value = div_settings_count + new_weight_config_count;
	}
	
	function jsDeleteRatingWeight(num)
	{
		var last_item = parseInt(BX('rating_settings_weight_add').getAttribute('rel'));
		
		BX.remove(BX('rating_settings_weight_'+num));
		
		// iterate through available configs, that would get last config
		while( last_item > 0 ) 
		{ 
			if (BX('rating_settings_weight_'+last_item) !== null && last_item != <?=$conditionMaxCount?>)
				break;
			last_item--; 
		}
		if (last_item == 0)
			last_item = <?=$conditionMaxCount?>;
			
		BX('rating_settings_weight_add').setAttribute('rel', last_item);
		// finding maximum weight
		jsChangeRatingWeight();
	}

	function jsChangeRatingWeight()
	{
		var max_weight = 0;
		var input_end = BX('rating_settings_weight_<?=$conditionMaxCount?>_from');
		var last_item = parseInt(BX('rating_settings_weight_add').getAttribute('rel'));
		// iterate through available configs, that would get max weight
		while( last_item > 1 ) 
		{ 
			if (BX('rating_settings_weight_'+last_item+'_to') !== null)
			{
				current_item = parseFloat(BX('rating_settings_weight_'+last_item+'_to').value);
				if (max_weight < current_item )
					max_weight = current_item;
			}	
			last_item--; 
		}
		input_end.value = max_weight;
	}
<?endif;?>
	function jsTypeChanged(form_id)
	{
		var _form = document.forms[form_id];
		var _flag = document.getElementById('ACTION');
		if(_form)
		{
			_flag.value = 'type_changed';
			_form.submit();
		}
	} 
</script>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>