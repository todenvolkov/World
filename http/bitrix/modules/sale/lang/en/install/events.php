<?
$MESS ['SALE_NEW_ORDER_NAME'] = "New order";
$MESS ['SALE_NEW_ORDER_DESC'] = "#ORDER_ID# - Order ID
#ORDER_DATE# - Order date
#ORDER_USER# - User
#EMAIL# - User E-Mail
#BCC# - BCC E-Mail
#ORDER_LIST# - Order list
#SALE_EMAIL# - Sales department e-mail";
$MESS ['SALE_NEW_ORDER_SUBJECT'] = "#SITE_NAME#: New order N#ORDER_ID#";
$MESS ['SALE_NEW_ORDER_MESSAGE'] = "Order confirmation from #SITE_NAME#
------------------------------------------

Dear #ORDER_USER#,

Your order #ORDER_ID# from #ORDER_DATE# has been accepted.

Order value: #PRICE#.

Ordered items:
#ORDER_LIST#

You can monitor processing of your order (view current status 
of order) by entering your personal site section at  #SITE_NAME#.
Note that that you will need login and password for entering this
site section at #SITE_NAME#.

To cancel your order please use special option available in your
personal section at #SITE_NAME#.

Please note that you should specify your order ID:  #ORDER_ID#
when requesting any information from site administration at  #SITE_NAME#.

Thanks for ordering!
";
$MESS ['SALE_ORDER_CANCEL_NAME'] = "Cancel order";
$MESS ['SALE_ORDER_CANCEL_DESC'] = "#ORDER_ID# - Order ID
#ORDER_DATE# - Order date
#EMAIL# - User E-Mail
#ORDER_CANCEL_DESCRIPTION# - Order cancel description
#SALE_EMAIL# - Sales department e-mail";
$MESS ['SALE_ORDER_CANCEL_SUBJECT'] = "#SITE_NAME#: Order N#ORDER_ID# was canceled";
$MESS ['SALE_ORDER_CANCEL_MESSAGE'] = "Informational message from #SITE_NAME#
------------------------------------------

Order ##ORDER_ID# from #ORDER_DATE# is canceled.

#ORDER_CANCEL_DESCRIPTION#

#SITE_NAME#
";
$MESS ['SALE_ORDER_PAID_NAME'] = "Paid order";
$MESS ['SALE_ORDER_PAID_DESC'] = "#ORDER_ID# - Order ID
#ORDER_DATE# - Order date
#EMAIL# - User E-Mail
#SALE_EMAIL# - Sales department e-mail";
$MESS ['SALE_ORDER_PAID_SUBJECT'] = "#SITE_NAME#: Order N#ORDER_ID# was paid";
$MESS ['SALE_ORDER_PAID_MESSAGE'] = "Informational message from #SITE_NAME#
------------------------------------------

Order ##ORDER_ID# from #ORDER_DATE# was paid.

#SITE_NAME#
";
$MESS ['SALE_ORDER_DELIVERY_NAME'] = "Order delivery allowed";
$MESS ['SALE_ORDER_DELIVERY_DESC'] = "#ORDER_ID# - Order ID
#ORDER_DATE# - Order date
#EMAIL# - User E-Mail";
$MESS ['SALE_ORDER_DELIVERY_SUBJECT'] = "#SITE_NAME#: Delivery of order N#ORDER_ID# is allowed";
$MESS ['SALE_ORDER_DELIVERY_MESSAGE'] = "Informational message from #SITE_NAME#
------------------------------------------

Delivery of order ##ORDER_ID# from #ORDER_DATE# is allowed.

#SITE_NAME#
";
$MESS ['SALE_RECURRING_CANCEL_NAME'] = "Recurring payment canceled";
$MESS ['SALE_RECURRING_CANCEL_DESC'] = "#ORDER_ID# - Order ID
#ORDER_DATE# - Order date
#EMAIL# - User E-Mail
#CANCELED_REASON# - Reason
#SALE_EMAIL# - Sales department e-mail";
$MESS ['SALE_RECURRING_CANCEL_SUBJECT'] = "#SITE_NAME#: Recurring payment was canceled";
$MESS ['SALE_RECURRING_CANCEL_MESSAGE'] = "Informational message from #SITE_NAME#
------------------------------------------

Recurring payment was canceled

#CANCELED_REASON#
#SITE_NAME#
";
$MESS ['SALE_NEW_ORDER_RECURRING_NAME'] = "New Order for Subscription Renewal";
$MESS ['SALE_NEW_ORDER_RECURRING_DESC'] = "#ORDER_ID# - order ID\\r\\n#ORDER_DATE# - order date\\r\\n#ORDER_USER# - customer\\r\\n#PRICE# - order amount\\r\\n#EMAIL# - customer's e-mail address\\r\\n#BCC# - blind copy e-mail address\\r\\n#ORDER_LIST# - order items\\r\\n#SALE_EMAIL# - sales dept. e-mail address";
$MESS ['SALE_NEW_ORDER_RECURRING_SUBJECT'] = "#SITE_NAME#: New order ##ORDER_ID# for subscription renewal";
$MESS ['SALE_NEW_ORDER_RECURRING_MESSAGE'] = "Information from #SITE_NAME#\\r\\n------------------------------------------\\r\\n\\r\\nDear #ORDER_USER#,\\r\\n\\r\\nYour order ##ORDER_ID# of #ORDER_DATE# for subscription renewal has been accepted.\\r\\n\\r\\nOrder amount: #PRICE#.\\r\\n\\r\\nOrder items:\\r\\n#ORDER_LIST#\\r\\n\\r\\nYou can track the status of your order in your private area at #SITE_NAME#. Note that you will have to enter your login and password you usually use to log in to #SITE_NAME#.\\r\\n\\r\\nYou can cancel your order in your private area at #SITE_NAME#.\\r\\n\\r\\nYou are kindly asked to include your order number #ORDER_ID# in all messages you send to the #SITE_NAME# administration.\\r\\n\\r\\nThank you for you purchase!";
$MESS ['SALE_ORDER_REMIND_PAYMENT_NAME'] = "Order Paymenr Reminder";
$MESS ['SALE_ORDER_REMIND_PAYMENT_DESC'] = "#ORDER_ID# - order ID
#ORDER_DATE# - order date
#ORDER_USER# - customer
#PRICE# - order amount
#EMAIL# - customer's e-mail address
#BCC# - blind copy e-mail address
#ORDER_LIST# - order items
#SALE_EMAIL# - sales dept. e-mail address";
$MESS ['SALE_ORDER_REMIND_PAYMENT_SUBJECT'] = "#SITE_NAME#: Payment reminder for order ##ORDER_ID#";
$MESS ['SALE_ORDER_REMIND_PAYMENT_MESSAGE'] = "Information from #SITE_NAME#
------------------------------------------

Dear #ORDER_USER#,

You have placed an order ##ORDER_ID# of #ORDER_DATE#, amount: #PRICE#.

Unfortunately, it looks like your payment has not been completed. No funds has been transfered to our account. 

You can track the status of your order in your private area 
at #SITE_NAME#. Note that you will have to enter your login 
and password you usually use to log in to #SITE_NAME#.

You can cancel your order in your private area at #SITE_NAME#.

You are kindly asked to include your order number #ORDER_ID# in all messages you send to the #SITE_NAME# administration.

Thank you for your purchase!
";
?>