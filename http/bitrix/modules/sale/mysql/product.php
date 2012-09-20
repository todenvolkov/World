<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/product.php");

class CSaleProduct extends CALLSaleProduct
{
	function GetProductList($ID, $minCNT, $limit)
	{
		$ID = IntVal($ID);
		$limit = IntVal($limit);
		$minCNT = IntVal($minCNT);
		if($ID <= 0)
			return false;
			
		global $DB;
		
		$strSql = "select * from b_sale_product2product where PRODUCT_ID=".$DB->ForSQL($ID);
		if($minCNT > 0)
			$strSql .= " AND CNT >= ".$DB->ForSQL($minCNT)." ";
		$strSql .= " ORDER BY CNT DESC, PRODUCT_ID ASC";
		if($limit > 0)
			$strSql .= " LIMIT ".$limit;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		return $dbRes;
	}

	function GetBestSellerList($by = "AMOUNT", $arFilter = Array(), $arOrderFilter = Array(), $limit = 0)
	{
		global $DB;
		
		$byQuantity = false;
		if($by == "QUANTITY")
			$byQuantity = true;

		$arJoin = Array();
		$arWhere = Array();
		$orderFilter = "";
		$i = 1;

		if(is_array($arFilter) && count($arFilter) > 0)
		{
			foreach($arFilter as $key => $value)
			{
				$arJoin[] = "LEFT JOIN b_sale_basket_props p".$i." ON (b.ID = p".$i.".BASKET_ID)";
				$arFilter = CSaleProduct::GetFilterOperation($key, $value);
				$arWhere[] = "   AND p".$i.".CODE = '".$arFilter["field"]."' AND p".$i.".VALUE ".$arFilter["operation"]." ".$arFilter["value"];
				$i++;
			}
		}
		
		$arFields = array(
			"ID" => array("FIELD_NAME" => "O.ID", "FIELD_TYPE" => "int"),
			"LID" => array("FIELD_NAME" => "O.LID", "FIELD_TYPE" => "string"),
			"PERSON_TYPE_ID" => array("FIELD_NAME" => "O.PERSON_TYPE_ID", "FIELD_TYPE" => "int"),
			"PAYED" => array("FIELD_NAME" => "O.PAYED", "FIELD_TYPE" => "string"),
			"DATE_PAYED" => array("FIELD_NAME" => "O.DATE_PAYED", "FIELD_TYPE" => "datetime"),
			"EMP_PAYED_ID" => array("FIELD_NAME" => "O.EMP_PAYED_ID", "FIELD_TYPE" => "int"),
			"CANCELED" => array("FIELD_NAME" => "O.CANCELED", "FIELD_TYPE" => "string"),
			"DATE_CANCELED" => array("FIELD_NAME" => "O.DATE_CANCELED", "FIELD_TYPE" => "datetime"),
			"EMP_CANCELED_ID" => array("FIELD_NAME" => "O.EMP_CANCELED_ID", "FIELD_TYPE" => "int"),
			"REASON_CANCELED" => array("FIELD_NAME" => "O.REASON_CANCELED", "FIELD_TYPE" => "string"),
			"STATUS_ID" => array("FIELD_NAME" => "O.STATUS_ID", "FIELD_TYPE" => "string"),
			"DATE_STATUS" => array("FIELD_NAME" => "O.DATE_STATUS", "FIELD_TYPE" => "datetime"),
			"PAY_VOUCHER_NUM" => array("FIELD_NAME" => "O.PAY_VOUCHER_NUM", "FIELD_TYPE" => "string"),
			"PAY_VOUCHER_DATE" => array("FIELD_NAME" => "O.PAY_VOUCHER_DATE", "FIELD_TYPE" => "date"),
			"EMP_STATUS_ID" => array("FIELD_NAME" => "O.EMP_STATUS_ID", "FIELD_TYPE" => "int"),
			"PRICE_DELIVERY" => array("FIELD_NAME" => "O.PRICE_DELIVERY", "FIELD_TYPE" => "double"),
			"ALLOW_DELIVERY" => array("FIELD_NAME" => "O.ALLOW_DELIVERY", "FIELD_TYPE" => "string"),
			"DATE_ALLOW_DELIVERY" => array("FIELD_NAME" => "O.DATE_ALLOW_DELIVERY", "FIELD_TYPE" => "datetime"),
			"EMP_ALLOW_DELIVERY_ID" => array("FIELD_NAME" => "O.EMP_ALLOW_DELIVERY_ID", "FIELD_TYPE" => "int"),
			"PRICE" => array("FIELD_NAME" => "O.PRICE", "FIELD_TYPE" => "double"),
			"CURRENCY" => array("FIELD_NAME" => "O.CURRENCY", "FIELD_TYPE" => "string"),
			"DISCOUNT_VALUE" => array("FIELD_NAME" => "O.DISCOUNT_VALUE", "FIELD_TYPE" => "double"),
			"SUM_PAID" => array("FIELD_NAME" => "O.SUM_PAID", "FIELD_TYPE" => "double"),
			"USER_ID" => array("FIELD_NAME" => "O.USER_ID", "FIELD_TYPE" => "int"),
			"PAY_SYSTEM_ID" => array("FIELD_NAME" => "O.PAY_SYSTEM_ID", "FIELD_TYPE" => "int"),
			"DELIVERY_ID" => array("FIELD_NAME" => "O.DELIVERY_ID", "FIELD_TYPE" => "string"),
			"DATE_INSERT" => array("FIELD_NAME" => "O.DATE_INSERT", "FIELD_TYPE" => "datetime"),
			"DATE_INSERT_FORMAT" => array("FIELD_NAME" => "O.DATE_INSERT", "FIELD_TYPE" => "datetime"),
			"DATE_UPDATE" => array("FIELD_NAME" => "O.DATE_UPDATE", "FIELD_TYPE" => "datetime"),
			"USER_DESCRIPTION" => array("FIELD_NAME" => "O.USER_DESCRIPTION", "FIELD_TYPE" => "string"),
			"ADDITIONAL_INFO" => array("FIELD_NAME" => "O.ADDITIONAL_INFO", "FIELD_TYPE" => "string"),
			"PS_STATUS" => array("FIELD_NAME" => "O.PS_STATUS", "FIELD_TYPE" => "string"),
			"PS_STATUS_CODE" => array("FIELD_NAME" => "O.PS_STATUS_CODE", "FIELD_TYPE" => "string"),
			"PS_STATUS_DESCRIPTION" => array("FIELD_NAME" => "O.PS_STATUS_DESCRIPTION", "FIELD_TYPE" => "string"),
			"PS_STATUS_MESSAGE" => array("FIELD_NAME" => "O.PS_STATUS_MESSAGE", "FIELD_TYPE" => "string"),
			"PS_SUM" => array("FIELD_NAME" => "O.PS_SUM", "FIELD_TYPE" => "double"),
			"PS_CURRENCY" => array("FIELD_NAME" => "O.PS_CURRENCY", "FIELD_TYPE" => "string"),
			"PS_RESPONSE_DATE" => array("FIELD_NAME" => "O.PS_RESPONSE_DATE", "FIELD_TYPE" => "datetime"),
			"COMMENTS" => array("FIELD_NAME" => "O.COMMENTS", "FIELD_TYPE" => "string"),
			"TAX_VALUE" => array("FIELD_NAME" => "O.TAX_VALUE", "FIELD_TYPE" => "double"),
			"STAT_GID" => array("FIELD_NAME" => "O.STAT_GID", "FIELD_TYPE" => "string"),
			"RECURRING_ID" => array("FIELD_NAME" => "O.RECURRING_ID", "FIELD_TYPE" => "int"),
			"RECOUNT_FLAG" => array("FIELD_NAME" => "O.RECOUNT_FLAG", "FIELD_TYPE" => "string"),
			"AFFILIATE_ID" => array("FIELD_NAME" => "O.AFFILIATE_ID", "FIELD_TYPE" => "int"),
			"DELIVERY_DOC_NUM" => array("FIELD_NAME" => "O.DELIVERY_DOC_NUM", "FIELD_TYPE" => "string"),
			"DELIVERY_DOC_DATE" => array("FIELD_NAME" => "O.DELIVERY_DOC_DATE", "FIELD_TYPE" => "date"),
		);
		if(is_array($arOrderFilter) && count($arOrderFilter) > 0)
		{
			$sqlWhere = new CSQLWhere;
			$sqlWhere->SetFields($arFields, $arJ);
			$orderFilter = $sqlWhere->GetQueryEx($arOrderFilter, $arJ);
		}

		if($byQuantity)
			$strSql = "SELECT b.PRODUCT_ID, b.CATALOG_XML_ID, b.PRODUCT_XML_ID, SUM(b.QUANTITY) as QUANTITY \n";
		else
			$strSql = "SELECT b.PRODUCT_ID, b.CATALOG_XML_ID, b.PRODUCT_XML_ID, SUM(b.PRICE*b.QUANTITY) as PRICE \n";
		
		$strSql .= "FROM b_sale_basket b \n";

		foreach($arJoin as $v)
			$strSql .= $v."\n";
		if(strlen($orderFilter) > 0)
			$strSql .= "INNER JOIN b_sale_order O ON (b.ORDER_ID = O.ID) \n";
			
		$strSql .= "WHERE \n".
			" b.ORDER_ID is not null \n";

		foreach($arWhere as $v)
			$strSql .= $v."\n";
			
		if(strlen($orderFilter) > 0)
			$strSql .= " AND ".$orderFilter."\n";
			
		$strSql .= " GROUP BY b.PRODUCT_ID, b.CATALOG_XML_ID, b.PRODUCT_XML_ID \n";
		if($byQuantity)
			$strSql .= " ORDER BY QUANTITY DESC\n";
		else
			$strSql .= " ORDER BY PRICE DESC\n";
			
		if(IntVal($limit) > 0)
			$strSql .= "LIMIT ".IntVal($limit);
		
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		return $dbRes;
	}
	
	function GetFilterOperation($key, $value)
	{
		global $DB;
		$field = "";
		$operation = "";
		$field_val = "";
		
		if(is_array($value))
		{
			$field_val = "(";
			foreach($value as $val)
			{
				if(strlen($val) > 0)
					$field_val .= "\"".$DB->ForSQL($val)."\", ";
			}
			$field_val = substr($field_val, 0, -2);
			$field_val .= ")";

			if (substr($key, 0, 1) == "!")
			{
				$operation = "NOT IN";
				$field = $DB->ForSQL(substr($key, 1));
			}
			else
			{
				$operation = "IN";
				$field = $key;
			}
		}
		else
		{
			$field_val = "\"".$DB->ForSQL($value)."\"";

			if (substr($key, 0, 1) == "!")
			{
				$operation = "<>";
				$field = $DB->ForSQL(substr($key, 1));
				
			}
			elseif (substr($key, 0, 1) == "%")
			{
				$operation = "LIKE";
				$field = $DB->ForSQL(substr($key, 1));
			}
			elseif (substr($key, 0, 2) == "<=")
			{
				$operation = "<=";
				$field = $DB->ForSQL(substr($key, 2));
			}				
			elseif (substr($key, 0, 2) == ">=")
			{
				$operation = ">=";
				$field = $DB->ForSQL(substr($key, 2));
			}
			elseif (substr($key, 0, 1) == ">")
			{
				$operation = ">";
				$field = $DB->ForSQL(substr($key, 1));
			}
			elseif (substr($key, 0, 1) == "<")
			{
				$operation = "<";
				$field = $DB->ForSQL(substr($key, 1));
			}
			else
			{
				$operation = "=";
				$field = $DB->ForSQL($key);
			}
		}
		return array("field" => $field, "operation" => $operation, "value" => $field_val);
	}
}