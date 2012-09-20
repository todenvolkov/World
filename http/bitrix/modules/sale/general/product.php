<?
IncludeModuleLangFile(__FILE__);

class CALLSaleProduct
{
	function RefreshProductList()
	{
		global $DB;
		$strSql = "truncate table b_sale_product2product";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		$strSql = "INSERT INTO b_sale_product2product (PRODUCT_ID, PARENT_PRODUCT_ID, CNT)
			select b.PRODUCT_ID as PRODUCT_ID, b1.PRODUCT_ID as PARENT_PRODUCT_ID, COUNT(b1.PRODUCT_ID) as CNT
			from b_sale_basket b
			left join b_sale_basket b1 on (b.ORDER_ID = b1.ORDER_ID)
			inner join b_sale_order o on (o.ID = b.ORDER_ID)
			where
			  o.ALLOW_DELIVERY = 'Y'
			  AND b.ID <> b1.ID
			GROUP BY b.PRODUCT_ID, b1.PRODUCT_ID";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		
		return "CSaleProduct::RefreshProductList();";
	}
}
