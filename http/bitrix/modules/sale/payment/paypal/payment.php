<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$domain = "";
if(CSalePaySystemAction::GetParamValue("TEST") == "Y")
	$domain = "sandbox.";
?>
<table border="0" width="100%" cellpadding="2" cellspacing="2">
<form action="https://www.<?=$domain?>paypal.com/cgi-bin/webscr" method="post">
  <tr>
	<td>
		PayPal’s service builds on the existing financial infrastructure of bank 
		accounts and credit cards and utilizes the world’s most advanced proprietary 
		fraud prevention systems to create a safe, global, real-time payment solution.
	</td>
  </tr>
  <tr>
	<td align="center">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("BUSINESS")) ?>">
		<input type="hidden" name="item_name" value="Invoice <?= CSalePaySystemAction::GetParamValue("ORDER_ID")." (".CSalePaySystemAction::GetParamValue("DATE_INSERT").")"?>">
		<input type="hidden" name="currency_code" value="<?= CSalePaySystemAction::GetParamValue("CURRENCY")?>">
		<input type="hidden" name="amount" value="<?= CSalePaySystemAction::GetParamValue("SHOULD_PAY")?>">
		<input type="hidden" name="custom" value="<?= CSalePaySystemAction::GetParamValue("ORDER_ID")?>">
		
		<?
		if(strlen(CSalePaySystemAction::GetParamValue("ON0"))>0)
		{
			?>
			<input type="hidden" name="on0" value="<?=urlencode(CSalePaySystemAction::GetParamValue("ON0"))?>">
			<input type="hidden" name="os0" value="<?=urlencode(CSalePaySystemAction::GetParamValue("OS0"))?>">
			<?
		}
		if(strlen(CSalePaySystemAction::GetParamValue("ON1"))>0 && strlen(CSalePaySystemAction::GetParamValue("ON0"))>0)
		{
			?>
			<input type="hidden" name="on1" value="<?=urlencode(CSalePaySystemAction::GetParamValue("ON1"))?>">
			<input type="hidden" name="os1" value="<?=urlencode(CSalePaySystemAction::GetParamValue("OS1"))?>">
			<?
		}		
		if(strlen(CSalePaySystemAction::GetParamValue("NOTIFY_URL"))>0)
		{
			?>
			<input type="hidden" name="notify_url" value="<?=CSalePaySystemAction::GetParamValue("NOTIFY_URL")?>">
			<?
		}
		if(strlen(CSalePaySystemAction::GetParamValue("RETURN"))>0)
		{
			?>
			<input type="hidden" name="return" value="<?=CSalePaySystemAction::GetParamValue("RETURN")?>">
			<?
		}
		$buttonSrc = (strlen(CSalePaySystemAction::GetParamValue("BUTTON_SRC"))>0) ? CSalePaySystemAction::GetParamValue("BUTTON_SRC") : "http://www.paypal.com/en_US/i/btn/x-click-but01.gif";
		?>
		
		<input type="image" src="<?=$buttonSrc?>" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
	</td>
  </tr>
</FORM>
</table>
