<?php
define('_JEXEC', true);
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
require_once(dirname(__FILE__).'/osemscapi.php');
$api = new osemscAPI();

// Creating a Membership with Titile 'test membership';
// $msc_id = $api ->createPlan('test membership'); 

// Create an order
//$order_id = $api -> createOrder('a', 'paypal', 1, '4e249e97e5358', 51);

// Confirming an order
//$api ->confirmOrder($order_id);
 
// Update the Payment gateway profile ID 
//$api ->updateSerial($order_id, 'adadf'); 
?>