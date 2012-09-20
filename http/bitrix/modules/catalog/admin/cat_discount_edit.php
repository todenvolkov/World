<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*$catalogPermissions = $APPLICATION->GetGroupRight("catalog");
if ($catalogPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bReadOnly = !$USER->CanDoOperation('catalog_discount');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);

function CatalogGenerateCoupon()
{
	$events = GetModuleEvents("catalog", "OnGenerateCoupon");
	if ($arEvent = $events->Fetch())
		return ExecuteModuleEventEx($arEvent);

	$allchars = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789';
	$string1 = '';
	$string2 = '';
	for ($i = 0; $i < 5; $i++)
		$string1 .= substr($allchars, rand(0, StrLen($allchars) - 1), 1);

	for ($i = 0; $i < 7; $i++)
		$string2 .= substr($allchars, rand(0, StrLen($allchars) - 1), 1);

	return "CP-".$string1."-".$string2;
}

if (!$bReadOnly && $REQUEST_METHOD=="POST" && strlen($Update)>0 && /*$catalogPermissions=="W" &&*/ check_bitrix_sessid())
{
	$arProductID = array();
	$PRODUCT_IDS_CNT = IntVal($PRODUCT_IDS_CNT);
	if ($PRODUCT_IDS_CNT >= 0)
	{
		for ($i = 0; $i <= $PRODUCT_IDS_CNT; $i++)
		{
			if (IntVal(${"PRODUCT_IDS_".$i}) > 0)
			{
				$arProductID[] = IntVal(${"PRODUCT_IDS_".$i});
			}
		}
	}

	$arSectionID = array();
	if (isset($SECTION_IDS) && is_array($SECTION_IDS))
	{
		for ($i = 0; $i < count($SECTION_IDS); $i++)
		{
			if (IntVal($SECTION_IDS[$i]) > 0)
			{
				$arSectionID[] = IntVal($SECTION_IDS[$i]);
			}
		}
	}

	$arGroupID = array();
	if (isset($GROUP_IDS) && is_array($GROUP_IDS))
	{
		for ($i = 0; $i < count($GROUP_IDS); $i++)
		{
			if (IntVal($GROUP_IDS[$i]) > 0)
			{
				$arGroupID[] = IntVal($GROUP_IDS[$i]);
			}
		}
	}

	$arCatalogGroupID = array();
	if (isset($CAT_IDS) && is_array($CAT_IDS))
	{
		for ($i = 0; $i < count($CAT_IDS); $i++)
		{
			if (IntVal($CAT_IDS[$i]) > 0)
			{
				$arCatalogGroupID[] = IntVal($CAT_IDS[$i]);
			}
		}
	}

	// this validation is inside CCatalogDiscount::CheckFields
	/*
	if (strlen($SITE_ID) <= 0)
		$errorMessage .= GetMessage("DSC_EMPTY_SITE")."<br>";
	if (strlen($CURRENCY) <= 0)
		$errorMessage .= GetMessage("DSC_EMPTY_CURR")."<br>";
	*/

	if (strlen($errorMessage) <= 0)
	{
		$DB->StartTransaction();

		$arFields = Array(
			"SITE_ID" => $SITE_ID,
			"ACTIVE" => (($ACTIVE=="Y") ? "Y" : "N"),
			"ACTIVE_FROM" => $ACTIVE_FROM,
			"ACTIVE_TO" => $ACTIVE_TO,
			"RENEWAL" => $RENEWAL,
			"NAME" => $NAME,
			"MAX_USES" => $MAX_USES,
			"COUNT_USES" => $COUNT_USES,
			"COUPON" => $COUPON,
			"SORT" => $SORT,
			"MAX_DISCOUNT" => $MAX_DISCOUNT,
			"VALUE_TYPE" => $VALUE_TYPE,
			"VALUE" => $VALUE,
			"CURRENCY" => $CURRENCY,
			"MIN_ORDER_SUM" => $MIN_ORDER_SUM,
			"NOTES" => $NOTES,
			"PRODUCT_IDS" => $arProductID,
			"SECTION_IDS" => $arSectionID,
			"GROUP_IDS" => $arGroupID,
			"CATALOG_GROUP_IDS" => $arCatalogGroupID
		);

		if ($ID > 0)
		{
			$res = CCatalogDiscount::Update($ID, $arFields);
		}
		else
		{
			$ID = CCatalogDiscount::Add($arFields);
			$res = ($ID > 0);
		}

		if (!$res)
		{
			$ex = $APPLICATION->GetException();
			$errorMessage .= $ex->GetString()."<br>";
			$bVarsFromForm = true;
			$DB->Rollback();
		}
		else
		{
			$COUPON_SELECTOR = IntVal($COUPON_SELECTOR);
			if ($COUPON_SELECTOR == 1)
			{
				$COUPON = Trim($COUPON);
				if (StrLen($COUPON) <= 0)
				{
					CCatalogDiscountCoupon::DeleteByDiscountID($ID);
				}
				else
				{
					$couponsCnt = CCatalogDiscountCoupon::GetList(
						array(),
						array("DISCOUNT_ID" => $ID),
						array()
					);
					if (IntVal($couponsCnt) > 1)
						CCatalogDiscountCoupon::DeleteByDiscountID($ID);

					$dbCoupons = CCatalogDiscountCoupon::GetList(
						array(),
						array("DISCOUNT_ID" => $ID),
						false,
						false,
						array("ID")
					);

					$arCouponFields = array(
						"DISCOUNT_ID" => $ID,
						"ACTIVE" => "Y",
						"ONE_TIME" => "N",
						"COUPON" => $COUPON,
						"DATE_APPLY" => false
					);

					if ($arCoupons = $dbCoupons->Fetch())
						$CID = CCatalogDiscountCoupon::Update($arCoupons["ID"], $arCouponFields);
					else
						$CID = CCatalogDiscountCoupon::Add($arCouponFields);

					$CID = IntVal($CID);
					if ($CID <= 0)
					{
						$ex = $APPLICATION->GetException();
						$errorMessage .= $ex->GetString()."<br>";
						$bVarsFromForm = true;
					}
				}
			}
			else
			{
				$NUM_COUPON = IntVal($NUM_COUPON);
				if ($NUM_COUPON > 0)
				{
					for ($i = 0; $i < $NUM_COUPON; $i++)
					{
						$CID = CCatalogDiscountCoupon::Add(
							array(
								"DISCOUNT_ID" => $ID,
								"ACTIVE" => "Y",
								"COUPON" => CatalogGenerateCoupon(),
								"DATE_APPLY" => false
							)
						);
						$cRes = ($CID > 0);
						if (!$cRes)
						{
							$ex = $APPLICATION->GetException();
							$errorMessage .= $ex->GetString()."<br>";
							$bVarsFromForm = true;
						}
					}
				}
			}

			$DB->Commit();
			if (strlen($apply)<=0)
				LocalRedirect("/bitrix/admin/cat_discount_admin.php?lang=".LANG.GetFilterParams("filter_", false));
			else
				LocalRedirect("/bitrix/admin/cat_discount_edit.php?lang=".LANG."&ID=".$ID.GetFilterParams("filter_", false));
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("DSC_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("DSC_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$str_ACTIVE = "Y";
$str_SORT = 100;

$dbDiscount = CCatalogDiscount::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array("ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "RENEWAL", "NAME", "MAX_USES", "COUNT_USES", "COUPON", "SORT", "MAX_DISCOUNT", "VALUE_TYPE", "VALUE", "CURRENCY", "MIN_ORDER_SUM", "TIMESTAMP_X", "NOTES")
	);
if (!$dbDiscount->ExtractFields("str_"))
	$ID = 0;

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_discount", "", "str_");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("CDEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_discount_admin.php?lang=".LANG.GetFilterParams("filter_", false)
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CDEN_DCPN_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_discount_coupon.php?lang=".LANG."&set_filter=Y&filter_discount_id=".$ID
	);

	//if ($catalogPermissions == "W")
	if (!$bReadOnly)
	{
		$aMenu[] = array("SEPARATOR" => "Y");

		$aMenu[] = array(
			"TEXT" => GetMessage("CDEN_NEW_DISCOUNT"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/cat_discount_edit.php?lang=".LANG.GetFilterParams("filter_", false)
		);

		$aMenu[] = array(
			"TEXT" => GetMessage("CDEN_DELETE_DISCOUNT"), 
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("CDEN_DELETE_DISCOUNT_CONFIRM")."')) window.location='/bitrix/admin/cat_discount_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
			"WARNING" => "Y"
		);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fdiscount_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CDEN_TAB_DISCOUNT"), "ICON" => "catalog", "TITLE" => GetMessage("CDEN_TAB_DISCOUNT_DESCR")),
		array("DIV" => "edit2", "TAB" => GetMessage("CDEN_TAB_DISCOUNT_PAR"), "ICON" => "catalog", "TITLE" => GetMessage("CDEN_TAB_DISCOUNT_PAR_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?= $ID ?></td>
		</tr>
		<tr>
			<td><?= GetMessage("DSC_TIMESTAMP") ?>:</td>
			<td><?= $str_TIMESTAMP_X ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?= GetMessage("DSC_SITE") ?>:</td>
		<td width="60%"><?echo CSite::SelectBox("SITE_ID", $str_SITE_ID, ""); ?></td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_ACTIVE") ?>:</td>
		<td>
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_PERIOD") ?>:</td>
		<td>
			<?= CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "fdiscount_edit", "N", "", "", "19")?>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?= GetMessage("DSC_NAME") ?>:</td>
		<td>
			<input type="text" name="NAME" size="50" maxlength="255" value="<?= $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_SORT") ?>:</td>
		<td>
			<input type="text" name="SORT" size="7" maxlength="10" value="<?= $str_SORT ?>">
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?= GetMessage("DSC_TYPE") ?>:</td>
		<td>
			<select name="VALUE_TYPE">
				<option value="P"<?if ($str_VALUE_TYPE == "P") echo " selected";?>><?= GetMessage("DSC_TYPE_PERCENT") ?></option>
				<option value="F"<?if ($str_VALUE_TYPE == "F") echo " selected";?>><?= GetMessage("DSC_TYPE_FIX") ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?= GetMessage("DSC_VALUE") ?>:</td>
		<td>
			<input type="text" name="VALUE" size="20" maxlength="20" value="<?= (($str_VALUE_TYPE == "P") ? roundEx($str_VALUE, 4) : roundEx($str_VALUE, CATALOG_VALUE_PRECISION)) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_CURRENCY") ?>:</td>
		<td>
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "", "class='typeselect'")?>
		</td>
	</tr>
	<tr>
		<td valign="top"><?= GetMessage("DSC_MAX_SUM") ?>:</td>
		<td valign="top">
			<input type="text" name="MAX_DISCOUNT" size="20" maxlength="20" value="<?= roundEx($str_MAX_DISCOUNT, CATALOG_VALUE_PRECISION) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_RENEW") ?>:</td>
		<td>
			<input type="checkbox" name="RENEWAL" value="Y"<?if ($str_RENEWAL=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td valign="top"><?= GetMessage("DSC_DESCR") ?>:</td>
		<td valign="top">
			<textarea cols="35" rows="3" class="typearea" name="NOTES" wrap="virtual"><?= $str_NOTES ?></textarea>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td valign="top" width="40%"><?= GetMessage("DSC_COUPON") ?>:</td>
		<td valign="top" width="60%">

			<script language="JavaScript">
			<!--
			function CouponSelectorClicked(val, flag)
			{
				var bCanChange = true;
				if (flag && (val == 1))
				{
					bCanChange = false;
					if (confirm('<?= GetMessage("CDEN_DCPN_DC_CONF") ?>'))
						bCanChange = true;
				}

				var fl;
				if (val == 1)
					fl = true;
				else
					fl = false;

				if (!bCanChange)
					fl = !fl;

				document.fdiscount_edit.COUPON.disabled = !fl;
				document.fdiscount_edit.COUPON_BTN.disabled = !fl;
				document.getElementById("ID_TR_COUPON1").disabled = !fl;
				document.getElementById("ID_COUPON_SELECTOR1").checked = fl;

				document.fdiscount_edit.NUM_COUPON.disabled = fl;
				document.getElementById("ID_TR_COUPON2").disabled = fl;
				document.getElementById("ID_COUPON_SELECTOR2").checked = !fl;
			}
			//-->
			</script>

			<?
			$dbCoupons = CCatalogDiscountCoupon::GetList(
				array(),
				array("DISCOUNT_ID" => $ID),
				false,
				false,
				array("ID", "DISCOUNT_ID", "ACTIVE", "ONE_TIME", "COUPON", "DATE_APPLY")
			);
			$bOneCoupon = True;
			$COUPON = "";
			$ind1 = 0;
			while ($arCoupons = $dbCoupons->Fetch())
			{
				$COUPON = $arCoupons["COUPON"];
				$ind1++;
				if ($ind1 > 1)
				{
					$bOneCoupon = False;
					break;
				}
				if ($arCoupons["ACTIVE"] != "Y")
				{
					$bOneCoupon = False;
					break;
				}
				if ($arCoupons["ONE_TIME"] == "Y")
				{
					$bOneCoupon = False;
					break;
				}
			}
			?>

			<table>
			<tr>
				<td>
					<input type="radio" name="COUPON_SELECTOR" id="ID_COUPON_SELECTOR1" value="1" OnClick="CouponSelectorClicked(1, true)">
					<label for="ID_COUPON_SELECTOR1"><?= GetMessage("CDEN_DCPN_1_COUPON") ?></label>
					<br><br>
				</td>
			</tr>
			<tr id="ID_TR_COUPON1">
				<td>
					<input type="text" name="COUPON" size="30" maxlength="30" value="<?= ($bOneCoupon) ? htmlspecialchars($COUPON) : "" ?>">
					&nbsp;&nbsp;&nbsp;
					<input type="button" name="COUPON_BTN" value="<?= GetMessage("DSC_COUPON_GENERATE") ?>" OnClick="GenerateCheck()">
					<script language="JavaScript">
					<!--
					function GenerateCheck()
					{
						var oCoupon = document.fdiscount_edit.COUPON;

						var allchars = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789';
						var string1 = '';
						var string2 = '';
						for (var i = 0; i < 5; i++)
							string1 = string1 + allchars.substr(Math.round((Math.random())*(allchars.length-1)), 1);

						for (var i = 0; i < 7; i++)
							string2 = string2 + allchars.substr(Math.round((Math.random())*(allchars.length-1)), 1);

						oCoupon.value = "CP-"+string1+"-"+string2;
					}
					//-->
					</script>
					<br><br>
				</td>
			</tr>
			<tr>
				<td>
					<input type="radio" name="COUPON_SELECTOR" id="ID_COUPON_SELECTOR2" value="2" OnClick="CouponSelectorClicked(2, true)">
					<label for="ID_COUPON_SELECTOR2"><?= GetMessage("CDEN_DCPN_2_COUPON") ?></label>
					<br><br>
				</td>
			</tr>
			<tr id="ID_TR_COUPON2">
				<td>
					<?
					if ($ID > 0)
					{
						?>
						<a href="/bitrix/admin/cat_discount_coupon.php?lang=<?= LANG ?>&set_filter=Y&filter_discount_id=<?= $ID ?>"><?= GetMessage("CDEN_DCPN_LIST_ALT") ?></a><br>
						<?
					}
					?>
					<?= GetMessage("CDEN_DCPN_AUTOGEN1") ?>
					<input type="text" name="NUM_COUPON" size="3" maxlength="5" value="<?= ($bVarsFromForm ? $NUM_COUPON : 0) ?>" >
					<?= GetMessage("CDEN_DCPN_AUTOGEN2") ?>
				</td>
			</tr>
			</table>

			<script language="JavaScript">
			<!--
			CouponSelectorClicked(<?= ($bOneCoupon) ? "1" : "2" ?>, false);
			//-->
			</script>

		</td>
	</tr>

	<tr>
		<td valign="top" width="40%"><?= GetMessage("DSC_USER_GROUPS") ?>:</td>
		<td valign="top" width="60%">
			<select name="GROUP_IDS[]" multiple size="5">
			<?
			$arDiscountGroups = array();
			if ($bVarsFromForm)
			{
				if (isset($GROUP_IDS) && is_array($GROUP_IDS))
				{
					for ($i = 0; $i < count($GROUP_IDS); $i++)
					{
						if (IntVal($GROUP_IDS[$i]) > 0)
							$arDiscountGroups[] = IntVal($GROUP_IDS[$i]);
					}
				}
			}
			else
			{
				if ($ID > 0)
				{
					$dbDiscountGroupsList = CCatalogDiscount::GetDiscountGroupsList(array(), array("DISCOUNT_ID" => $ID));
					while ($arDiscountGroup = $dbDiscountGroupsList->Fetch())
					{
						$arDiscountGroups[] = IntVal($arDiscountGroup["GROUP_ID"]);
					}
				}
			}

			$dbGroups = CGroup::GetList(($b="c_sort"), ($o="asc"), array());
			while ($arGroups = $dbGroups->Fetch())
			{
				?><option value="<?= $arGroups["ID"] ?>"<?if (in_array(IntVal($arGroups["ID"]), $arDiscountGroups)) echo " selected";?>>[<?= $arGroups["ID"] ?>] <?= htmlspecialcharsEx($arGroups["NAME"]) ?></option><?
			}
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td valign="top" width="40%"><?= GetMessage("DSC_PRICE_TYPES") ?>:</td>
		<td valign="top" width="60%">
			<select name="CAT_IDS[]" multiple size="5">
			<?
			$arDiscountCats = array();
			if ($bVarsFromForm)
			{
				if (isset($CAT_IDS) && is_array($CAT_IDS))
				{
					for ($i = 0; $i < count($CAT_IDS); $i++)
					{
						if (IntVal($CAT_IDS[$i]) > 0)
							$arDiscountCats[] = IntVal($CAT_IDS[$i]);
					}
				}
			}
			else
			{
				if ($ID > 0)
				{
					$dbDiscountCatsList = CCatalogDiscount::GetDiscountCatsList(array(), array("DISCOUNT_ID" => $ID));
					while ($arDiscountCat = $dbDiscountCatsList->Fetch())
					{
						$arDiscountCats[] = IntVal($arDiscountCat["CATALOG_GROUP_ID"]);
					}
				}
			}

			$dbCats = CCatalogGroup::GetList(array("NAME" => "ASC"), array("LID" => LANG));
			while ($arCats = $dbCats->Fetch())
			{
				?><option value="<?= $arCats["ID"] ?>"<?if (in_array(IntVal($arCats["ID"]), $arDiscountCats)) echo " selected";?>>[<?= $arCats["ID"] ?>] <?= htmlspecialcharsEx($arCats["NAME"]) ?> (<?= htmlspecialcharsEx($arCats["NAME_LANG"]) ?>)</option><?
			}
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td valign="top" width="40%"><?= GetMessage("DSC_SECTIONS") ?>:</td>
		<td valign="top" width="60%">
			<table width="100%" border="0" cellpadding="0" cellspacing="1">
			<tr><td>
				<script language="JavaScript" type="text/javascript">
					<!--
					var itm_id = new Object();
					var itm_name = new Object();

					function ChlistIBlock(n_id)
					{
						var max_lev = itm_lev;
						var nex = document.fdiscount_edit["SECTION_SELECTOR_LEVEL[0]"];
						var iBlock = document.fdiscount_edit["SECTION_IBLOCK_ID"];
						var iBlockID = iBlock[iBlock.selectedIndex].value;
						if (itm_id[iBlockID])
						{
							var curlist = itm_id[iBlockID][0];
							if (curlist && curlist.length > 1)
							{
								var curlistname = itm_name[iBlockID][0];
								var nex_length = nex.length;
								while (nex_length > 1)
								{
									nex_length--;
									nex.options[nex_length] = null;
								}
								nex_length = 1;

								for (i = 1; i < curlist.length; i++)
								{
									var newoption = new Option(curlistname[i], curlist[i], false, false);
									nex.options[nex_length] = newoption;
									if (n_id == curlist[i]) nex.selectedIndex = nex_length;
									nex_length++;
								}
								startClear = 1;
							}
							else
							{
								startClear = 0;
							}
						}
						else
						{
							startClear = 0;
						}

						for (i = startClear; i < max_lev; i++)
						{
							nex = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+i+"]"];
							var nex_length = nex.length;
							while (nex_length > 1)
							{
								nex_length--;
								nex.options[nex_length] = null;
							}
						}
					}

					function Chlist(num, n_id)
					{
						var max_lev = itm_lev;
						var cur = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+num+"]"];
						var nex = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+(parseInt(num)+1)+"]"];
						var iBlock = document.fdiscount_edit["SECTION_IBLOCK_ID"];
						var id = cur[cur.selectedIndex].value;
						var iBlockID = iBlock[iBlock.selectedIndex].value;
						var curlist = itm_id[iBlockID][id];
						if (curlist && curlist.length>1)
						{
							var curlistname = itm_name[iBlockID][id];
							var nex_length = nex.length;
							while (nex_length>1)
							{
								nex_length--;
								nex.options[nex_length] = null;
							}
							nex_length = 1;

							for (i = 1; i < curlist.length; i++)
							{
								var newoption = new Option(curlistname[i], curlist[i], false, false);
								nex.options[nex_length] = newoption;
								if (n_id == curlist[i]) nex.selectedIndex = nex_length;
								nex_length++;
							}
						}
						else
							num--;

						for (i = num + 2; i < max_lev; i++)
						{
							nex = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+i+"]"];
							var nex_length = nex.length;
							while (nex_length>1)
							{
								nex_length--;
								nex.options[nex_length] = null;
							}
						}
					}

					function Fnd(ar)
					{
						var iBlock = document.fdiscount_edit["SECTION_IBLOCK_ID"];
						var fst = document.fdiscount_edit["SECTION_SELECTOR_LEVEL[0]"];
						var i = 0;

						for (i = 0; i < iBlock.length; i++)
							if (iBlock[i].value == ar[1])
								iBlock.selectedIndex = i;

						if (ar.length > 2)
							ChlistIBlock(ar[2]);

						for (i = 0; i < ar.length - 3; i++)
							Chlist(i, ar[i + 3]);

						Chlist(i);
					}

					function AddS()
					{
						var i = itm_lev;
						var curlist = false;
						var s_id = false;
						var s_name = "";
						while (i > 0)
						{
							i--;
							var cur = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+i+"]"];
							if ((i > 0 && cur.selectedIndex > 0) || i == 0)
							{
								s_id = cur[cur.selectedIndex].value;
								s_name = cur[cur.selectedIndex].text;
								break;
							}
						}

						if (s_id)
						{
							var trs = document.getElementById("SECTION_SELECTOR").rows;
							for (j = 0; j < trs.length; j++)
								if (trs[j].id == s_id)
									return;

							var res = "";
							var str = "0";

							var t = document.fdiscount_edit["SECTION_IBLOCK_ID"];
							t_id = t[t.selectedIndex].value;
							str += ', '+t_id;
							t_name = t[t.selectedIndex].text.replace(/</g,'&lt;').replace(/>/g,'&gt;');
							t_name = '<a href="javascript:void(0)" onclick="Fnd(new Array('+str+'))">' + t_name + '</a>';
							res += t_name+" / ";

							for (j = 0; j <= i; j++)
							{
								var t = document.fdiscount_edit["SECTION_SELECTOR_LEVEL["+j+"]"];
								t_id = t[t.selectedIndex].value;
								str += ', '+t_id;
								t_name = t[t.selectedIndex].text.replace(/</g,'&lt;').replace(/>/g,'&gt;');
								t_name = '<a href="javascript:void(0)" onclick="Fnd(new Array('+str+'))">' + t_name + '</a>';
								res += t_name+" / ";
							}
							var tr = document.getElementById("SECTION_SELECTOR").insertRow(document.getElementById("SECTION_SELECTOR").rows.length);
							tr.id = s_id;
							var td = tr.insertCell(0);
							td.innerHTML = '<font class="tableheadtext">'+res+'';
							td = tr.insertCell(1);
							td.innerHTML = '<input type="hidden" name="SECTION_IDS[]" value="'+s_id+'"><input type="button" value="<?= GetMessage("DSC_DELETE_SECT") ?>" class="tablebodybutton" onClick="DelS('+s_id+')">';
						}
					}

					function DelS(s_id)
					{
						var trs = document.getElementById("SECTION_SELECTOR").rows;
						for (i = 0; i < trs.length; i++)
						{
							if (trs[i].id == s_id)
							{
								document.getElementById("SECTION_SELECTOR").deleteRow(i);
								return;
							}
						}
					}
					//-->
				</script>
				<table id="SECTION_SELECTOR" width="100%" border="0" cellpadding="0">
				<?
				$arDiscountSections = array();
				if ($bVarsFromForm)
				{
					if (isset($SECTION_IDS) && is_array($SECTION_IDS))
					{
						for ($i = 0; $i < count($SECTION_IDS); $i++)
						{
							if (IntVal($SECTION_IDS[$i]) > 0)
								$arDiscountSections[] = IntVal($SECTION_IDS[$i]);
						}
					}
				}
				else
				{
					if ($ID > 0)
					{
						$dbDiscountSectionsList = CCatalogDiscount::GetDiscountSectionsList(
								array(),
								array("DISCOUNT_ID" => $ID)
							);
						while ($arDiscountSection = $dbDiscountSectionsList->Fetch())
						{
							$arDiscountSections[] = IntVal($arDiscountSection["SECTION_ID"]);
						}
					}
				}

				$LOCAL_IBLOCK_CACHE = array();
				for ($i = 0; $i < count($arDiscountSections); $i++)
				{
					$dbSection = CIBlockSection::GetByID($arDiscountSections[$i]);
					if (!($arSection = $dbSection->Fetch()))
						continue;
					if (!isset($LOCAL_IBLOCK_CACHE[IntVal($arSection["IBLOCK_ID"])]))
					{
						$dbIBlock = CIBlock::GetByID($arSection["IBLOCK_ID"]);
						if ($arIBlock = $dbIBlock->Fetch())
						{
							if (!isset($LOCAL_IBLOCKTYPE_CACHE[$arIBlock["IBLOCK_TYPE_ID"]]))
							{
								if ($arIBlockType = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG, true))
									$LOCAL_IBLOCKTYPE_CACHE[$arIBlock["IBLOCK_TYPE_ID"]] = $arIBlockType["NAME"];
							}

							$LOCAL_IBLOCK_CACHE[IntVal($arSection["IBLOCK_ID"])] = "[".$LOCAL_IBLOCKTYPE_CACHE[$arIBlock["IBLOCK_TYPE_ID"]]."] ".$arIBlock["NAME"];
						}
					}
					$dbNavChain = CIBlockSection::GetNavChain($arSection["IBLOCK_ID"], $arDiscountSections[$i]);
					?>
					<tr id="<?= $arDiscountSections[$i] ?>"><td width="100%">
					<?
					$str = "";

					$str .= $arSection["IBLOCK_ID"];
					?><a href="javascript:void(0)" onclick="Fnd(new Array(0, <?= $str ?>));"><?echo htmlspecialcharsex($LOCAL_IBLOCK_CACHE[IntVal($arSection["IBLOCK_ID"])])?></a> / <?

					while ($arNavChain = $dbNavChain->Fetch())
					{
						$str .= ",".$arNavChain["ID"];
						?><a href="javascript:void(0)" onclick="Fnd(new Array(0, <?= $str ?>));"><?echo htmlspecialcharsex($arNavChain["NAME"])?></a> / <?
					}
					?>
					</td><td width="0%">
					<input type="hidden" name="SECTION_IDS[]" value="<?= $arDiscountSections[$i] ?>">
					<input type="button" value="<?= GetMessage("DSC_DELETE_SECT") ?>" onClick="DelS(<?= $arDiscountSections[$i] ?>)"></td></tr>
					<?
				}
				?>
				</table>
			</td>
			</tr>

			<tr>
				<td align="center" nowrap>
					<table border="0">
					<tr><td><?= GetMessage("DSC_ADD_TO") ?>:</td></tr>
					<tr><td>
						<?
						$SECTION_IBLOCK_ID = 22;
						$dbIBlockList = CIBlock::GetList(
								array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"),
								array("ACTIVE" => "Y")
							);
						?>
						<select name="SECTION_IBLOCK_ID" onChange="ChlistIBlock()">
							<option value="0"> - </option>
							<?
							$currentType = "";
							$currentTypeName = "";
							while ($arIBlock = $dbIBlockList->Fetch())
							{
								if (strlen($currentType)<=0 || $currentType!=$arIBlock["IBLOCK_TYPE_ID"])
								{
									if ($arIBlockType = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG, true))
										$currentTypeName = $arIBlockType["NAME"];
									$currentType = $arIBlock["IBLOCK_TYPE_ID"];
								}
								?><option value="<?= $arIBlock["ID"] ?>"<?if (IntVal($arIBlock["ID"])==IntVal($SECTION_IBLOCK_ID)) echo " selected";?>><?= htmlspecialchars("[".$currentTypeName."] ".$arIBlock["NAME"]) ?></option><?
							}
							?>
						</select><br>
						<?
						$maxLevel = 0;
						$dbIBlockList = CIBlock::GetList(
								array(),
								array("ACTIVE" => "Y")
							);
						while ($arIBlock = $dbIBlockList->Fetch())
						{
							$arSections = Array();

							$dbSectionTree = CIBlockSection::GetTreeList(
									array("IBLOCK_ID" => $arIBlock["ID"])
								);
							while ($arSectionTree = $dbSectionTree->Fetch())
							{
								if ($maxLevel < $arSectionTree["DEPTH_LEVEL"])
									$maxLevel = $arSectionTree["DEPTH_LEVEL"];

								$arSectionTree["IBLOCK_SECTION_ID"] = IntVal($arSectionTree["IBLOCK_SECTION_ID"]);

								if (!is_array($arSections[$arSectionTree["IBLOCK_SECTION_ID"]]))
									$arSections[$arSectionTree["IBLOCK_SECTION_ID"]] = array();

								$arSections[$arSectionTree["IBLOCK_SECTION_ID"]][] = array(
										"ID" => $arSectionTree["ID"],
										"NAME" => $arSectionTree["NAME"]
									);
							}

							$str1 = "";
							$str2 = "";
							foreach ($arSections as $sectionID => $arSubSection)
							{
								$str1 .= "itm_id['".$arIBlock["ID"]."']['".$sectionID."'] = new Array(0";
								$str2 .= "itm_name['".$arIBlock["ID"]."']['".$sectionID."'] = new Array(''";
								for ($i = 0; $i < count($arSubSection); $i++)
								{
									$str1 .= ", ".$arSubSection[$i]["ID"];
									$str2 .= ", '".CUtil::JSEscape($arSubSection[$i]["NAME"])."'";
								}
								$str1 .= ");\r\n";
								$str2 .= ");\r\n";
							}
							?>
							<script type="text/javascript">
							<!--
							itm_name['<?= $arIBlock["ID"] ?>'] = new Object();
							itm_id['<?= $arIBlock["ID"] ?>'] = new Object();
							<?=$str1;?>
							<?=$str2;?>
							//-->
							</script>
							<?
						}
						?>
						<script type="text/javascript">
						<!--
						itm_lev = <?= $maxLevel ?>;
						//-->
						</script>
						<?
						$initValue = 0;
						for ($i = 0; $i < $maxLevel; $i++)
						{
							?>
							<select name="SECTION_SELECTOR_LEVEL[<?= $i ?>]" onChange="Chlist(<?= $i ?>)">
								<option value="">(<?= GetMessage("DSC_NOT") ?>)</option>
							</select><br>
							<?
						}
						?>
						<script type="text/javascript">
							<!--
							ChlistIBlock();
							//-->
						</script>
						<input type="button" class="tablebodybutton" value="<?= GetMessage("DSC_BUT_ADD") ?>" onClick="AddS();">

				</td></tr></table>
				</td></tr></table>
		</td>
	</tr>

	<tr>
		<td valign="top" width="40%"><?= GetMessage("DSC_PRODUCTS") ?>:</td>
		<td valign="top" width="60%">
			<table cellpadding="0" cellspacing="0" border="0" id="products_table">
				<?
				$ind = -1;
				$dbDiscountProductsList = CCatalogDiscount::GetDiscountProductsList(array(), array("DISCOUNT_ID" => $ID));
				while ($arDiscountProduct = $dbDiscountProductsList->Fetch())
				{
					$ind++;
					$elementName = "";
					$dbElement = CIBlockElement::GetByID($arDiscountProduct["PRODUCT_ID"]);
					if ($arElement = $dbElement->Fetch())
						$elementName = htmlspecialcharsEx($arElement["NAME"]);
					?>
					<tr>
						<td>
							<input name="PRODUCT_IDS_<?= $ind ?>" value="<?= $arDiscountProduct["PRODUCT_ID"] ?>" size="5" type="text"><input type="button" class="tablebodybutton" value="..." onClick="window.open('cat_product_search.php?field_name=PRODUCT_IDS_<?= $ind ?>&amp;alt_name=product_alt_<?= $ind ?>&amp;form_name=fdiscount_edit', '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));">&nbsp;<span id="product_alt_<?= $ind ?>"><?= $elementName ?></span>
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<td>
						<input type="button" value="<?= GetMessage("DSC_ADD_PROD") ?>" onClick="window.open('cat_product_search.php?new_value=Y&amp;form_name=fdiscount_edit&amp;func_name=InsertProduct&amp;lang=<?=LANGUAGE_ID?>', '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));">
					</td>
				</tr>
			</table>
			<input type="hidden" name="PRODUCT_IDS_CNT" value="<?= $ind ?>">
			<script type="text/javascript">
			var numberOfProducts = <?= $ind ?>;
			function InsertProduct(id, name)
			{
				numberOfProducts = numberOfProducts + 1;
				var tbl = document.getElementById("products_table");
				var oRow = tbl.insertRow(tbl.rows.length - 1);
				var oCell = oRow.insertCell(0);
				oCell.innerHTML = 
					'<input name="PRODUCT_IDS_' + numberOfProducts + 
					'" value="' + id + '" size="5" type="text">' +
					'<input type="button" class="tablebodybutton" value="..." '+
					'onClick="window.open(\'cat_product_search.php?field_name=PRODUCT_IDS_' + 
					numberOfProducts + '&amp;alt_name=product_alt_' + numberOfProducts + 
					'&amp;form_name=fdiscount_edit&amp;lang=<?=LANGUAGE_ID?>\', ' +
					'\'\', \'scrollbars=yes,resizable=yes,width=600,height=500,' +
					'top=\'+Math.floor((screen.height - 500)/2-14)+\',' + 
					'left=\'+Math.floor((screen.width - 600)/2-5));">' + 
					'&nbsp;<span id="product_alt_' + numberOfProducts + 
					'" class="tablebodytext">' + name + '</span>';
				document.fdiscount_edit.PRODUCT_IDS_CNT.value = numberOfProducts;
			}
			</script>
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->Buttons(
	array(
		//"disabled" => ($catalogPermissions < "W"),
		"disabled" => $bReadOnly,
		"back_url" => "/bitrix/admin/cat_discount_admin.php?lang=".LANG.GetFilterParams("filter_", false)
	)
);
$tabControl->End();
?>
</form>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote(); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
