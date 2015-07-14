<?php
$jc = new JConfig();
$mosConfig_db = $jc->db;
$mosConfig_dbprefix = $jc->dbprefix;
$mosConfig_host = $jc->host;
$mosConfig_password = $jc->password;
$mosConfig_user = $jc->user;

$link = mysql_connect($mosConfig_host,$mosConfig_user,$mosConfig_password);
mysql_select_db($mosConfig_db,$link);

$dm = new docmanpaypal();
if (!$dm->constructRun) {
	$dm->__construct();
}

$seller_user_id = mysql_result(mysql_query("select dmsubmitedby from $mosConfig_dbprefix" . "docman where id = " . (int)JRequest::getVar('id') . " limit 1"),0);

$cfg = $dm->getConfig($seller_user_id);
if ($dm->hasLicense == false) {
	$product_price = 0.10;
}

if ($task == 'submit_order') {
	mysql_query("insert into $mosConfig_dbprefix" . "docmanpaypalsessions values ('$key','0');");
	if (is_numeric(JRequest::getVar('id')) and JRequest::getVar('id') > 0) {
		$product_price = mysql_result(mysql_query("select price from $mosConfig_dbprefix" . "docmanpaypal where `id` = " . JRequest::getVar('id')),0);
		$product_name = mysql_result(mysql_query("select dmname from $mosConfig_dbprefix" . "docman where `id` = " . JRequest::getVar('id')),0);
	} else {
	die("<h1>Failure.</h1>");
}
?>
<body onload="document.BB_BuyButtonForm.submit();">
<form action="https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/<?php echo $cfg['googleCheckout_MerchantID']; ?>" id="BB_BuyButtonForm" method="post" name="BB_BuyButtonForm" target="_top">
    <input name="item_name_1" type="hidden" value="<?php echo $product_name; ?>"/>
    <input name="item_description_1" type="hidden" value="Digital Delivery"/>
    <input name="item_quantity_1" type="hidden" value="1"/>
    <input name="item_price_1" type="hidden" value="<?php echo $product_price; ?>"/>
    <input name="item_currency_1" type="hidden" value="<?php echo $cfg['googleCheckout_currency']; ?>"/>
    <input name="shopping-cart.items.item-1.digital-content.description" type="hidden" value="<?php echo $cfg['googleCheckout_description']; ?>"/>
    <input name="shopping-cart.items.item-1.digital-content.url" type="hidden" value="<?php echo "$cfg[live_site]/index.php?option=com_docmanpaypal&task=doc_download&gid=" . (int)JRequest::getVar('id') . "&dmp_key=$key"; ?>"/>
    <input name="_charset_" type="hidden" value="utf-8"/><!--
    <input alt="" src="https://sandbox.google.com/checkout/buttons/buy.gif?merchant_id=<?php echo $cfg['googleCheckout_MerchantID']; ?>&amp;w=117&amp;h=48&amp;style=white&amp;variant=text&amp;loc=en_US" type="image"/>
--><?php
echo $cfg['googleCheckout_processing_page'];
?>
	</form>
</body>
<?php
}
if ($task == 'ipn') {
$result = mysql_query("select * from $mosConfig_dbprefix" . "docmanpaypalconfig");
while ($row = mysql_fetch_assoc($result)) {
	$$row['name'] = $row['value'];
}
foreach ($_REQUEST as $key => $val) {
	$$key = $val;
	$mailbody .= "$key = $val\n";
}
$sql = "update $mosConfig_dbprefix" . "docmanpaypalsessions set valid = 1 where session = '" . $custom . "';";
mysql_query($sql,$link); 
mail($googleCheckout_notifyemail,'DOCman PayPal IPN (Pay Per Download) - Google Checkout Order Received!',$mailbody);
}
?>