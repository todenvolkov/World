<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?//elements list?>
<a name="top"></a>
<?foreach ($arResult['ITEMS'] as $key=>$val):?>
	<li class="point-faq"><a href="#<?=$val["ID"]?>"><?=$val['NAME']?></a><br/></li>
<?endforeach;?>
<br/>
<?foreach ($arResult['ITEMS'] as $key=>$val):?>
<?
	$this->AddEditAction($val['ID'],$val['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($val['ID'],$val['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BSFE_ELEMENT_DELETE_CONFIRM')));
?>
<a name="<?=$val["ID"]?>"></a>
<table cellpadding="0" cellspacing="0" class="data-table" width="100%"  id="<?=$this->GetEditAreaId($val['ID']);?>">
	<tr>
		<th>
		<?=$val['NAME']?>
		</th>
	</tr>
	<tr>
		<td>
		<?=$val['PREVIEW_TEXT']?>
		<?=$val['DETAIL_TEXT']?>
		<br/>
		<a href="#top"><?=GetMessage("SUPPORT_FAQ_GO_UP")?></a>
		</td>
	</tr>
</table>
<br/>
<?endforeach;?>