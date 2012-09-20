<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Печать");
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0"> 
  <tbody>
    <tr> <td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">&nbsp;
        <br />
      </td> <td height="50" colspan="2" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
        <p><img src="/bitrix/templates/mir/images/Header_Print.png" width="347" height="25"  /></p>
       </td> <td valign="top" align="left" class="C10" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
        <p><a href="/personal/pprint/">Заказать<br>широкоформатную печать</a></p>
          <br />
         </p>
      </td> <td valign="top" align="left" class="C10" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
        <p>&nbsp;</p>
       
        <br />
      </td> <td valign="top" align="left" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">&nbsp;
        <br />
      </td> </tr>
   
    <tr> <td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">&nbsp;
        <br />
      </td> <td valign="top" class="C10_Left_Column" colspan="3" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;"> <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.element",
	"",
	Array(
		"IBLOCK_TYPE" => "articles",
		"IBLOCK_ID" => "8",
		"ELEMENT_ID" => $_REQUEST["ELEMENT_ID"],
		"ELEMENT_CODE" => "",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_CODE" => "",
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"BASKET_URL" => "",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"BROWSER_TITLE" => "-",
		"SET_TITLE" => "Y",
		"SET_STATUS_404" => "N",
		"ADD_SECTIONS_CHAIN" => "Y",
		"PROPERTY_CODE" => array(),
		"PRICE_CODE" => array(),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"PRODUCT_PROPERTIES" => array(),
		"USE_PRODUCT_QUANTITY" => "N",
		"LINK_IBLOCK_TYPE" => "",
		"LINK_IBLOCK_ID" => "",
		"LINK_PROPERTY_SID" => "",
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y"
	),
false
);?> 
        <p align="left">&nbsp;</p>
      
        <br />
      </td> <td valign="top" class="C10_Right_Column Lleft" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
        <table width="240" cellspacing="0" cellpadding="0" border="0"> 
          <tbody>
            <tr> <td class="Ltop2" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p>&nbsp;</p>
               
                <p class="Header">Заголовок Анонса блога</p>
               
                <p>Автор, Тэги, Дата, </p>
               
                <p>&nbsp;</p>
              </td> </tr>
           
            <tr> <td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p>&nbsp;</p>
               
                <p>Сеть рекламоносителей компании представлена конструкциями различного формата. В данном разделе Вы сможете подобрать рекламные конструкции, а так же рекламные щиты тюмени, отвечающие требованиям.</p>
               </td> </tr>
           
            <tr> <td align="right" class="Lbott" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p align="left" class="Grd">&nbsp;</p>
               
                <p align="left" class="Grd">Дата. Автор/ Комметариев/ Просмотров/Рейинг</p>
               
                <p align="left" class="Grd">&nbsp;</p>
              </td> </tr>
           </tbody>
        </table>
       
        <p>&nbsp;</p>
       
        <table width="240" cellspacing="0" cellpadding="0" border="0"> 
          <tbody>
            <tr> <td class="Ltop2" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p>&nbsp;</p>
               
                <p class="Header">Заголовок Анонса Акции </p>
               
                <p>Автор, Тэги, Дата, </p>
               
                <p>&nbsp;</p>
              </td> </tr>
           
            <tr> <td height="100" background="/bitrix/templates/mir/images/Pantone.jpg" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">&nbsp;
                <br />
              </td> </tr>
           
            <tr> <td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p>&nbsp;</p>
               
                <p>Сеть рекламоносителей компании представлена конструкциями различного формата. В данном разделе Вы сможете подобрать рекламные конструкции, а так же рекламные щиты тюмени, отвечающие требованиям.</p>
               </td> </tr>
           
            <tr> <td align="right" class="Lbott" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">
                <p align="left" class="Grd">&nbsp;</p>
               
                <p align="left" class="Grd">Дата. Автор</p>
               
                <p align="left" class="Grd">&nbsp;</p>
              </td> </tr>
           </tbody>
        </table>
       </td> <td valign="top" align="left" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">&nbsp;
        <br />
      </td> </tr>
   </tbody>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>