<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R);
*/
define("PAYPAL_DEBUG", 0);

if($_POST) {

	//global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mailfrom, $fromname;
	/*** access Joomla's configuration file ***/
	// Set flag that this is a parent file
	define('_JEXEC', 1);
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define('DS', DIRECTORY_SEPARATOR);
	define( 'PF_ERR_BAD_ACCESS', 'Bad access of page' );
	define( 'PF_ERR_INVALID_SIGNATURE', 'Security signature mismatch' );
	define( 'PF_ERR_BAD_SOURCE_IP', 'Bad source IP address' );
	define( 'PF_ERR_AMOUNT_MISMATCH', 'Amount mismatch' );
	define( 'PF_ERR_ORDER_NUMBER_MISMATCH', 'Order Number mismatch' );
	define( 'PF_USER_AGENT','Open Source Membership Control V5');
	define( 'PF_TIMEOUT', 15 );
	define( 'PF_EPSILON', 0.01 );
	require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
	require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$app = & JFactory :: getApplication('site');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/

	$oseMscConfig= oseRegistry :: call('msc')->getConfig('', 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');

	//$debug = $oseMscConfig->payfast_debug;
	//$debugEmail = $oseMscConfig->payfast_debug_email;
	$test_mode= $oseMscConfig->payfast_testmode;

	$PF = new oseMscIpnPayFast();
	// Variable Initialization
	$pfError = false;
	$pfErrMsg = '';
	$pfData = array();
	$pfHost = ( $test_mode ? 'sandbox' : 'www' ) .'.payfast.co.za';
	$pfOrderId = '';
	$pfParamString = '';

	//// Notify PayFast that information has been received
	if( !$pfError )
	{
	    header( 'HTTP/1.0 200 OK' );
	    flush();
	}

	//// Get data sent by PayFast
	if( !$pfError )
	{

	    // Posted variables from ITN
	    $pfData = $PF->pfGetData();

	    if( $pfData === false )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_BAD_ACCESS;

	        $mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= $pfErrMsg;
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	}

	//// Verify security signature
	if( !$pfError )
	{
	    // If signature different, log for debugging
	    if( !$PF->pfValidSignature( $pfData, $pfParamString ) )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
	        $mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= $pfErrMsg;
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	}

	//// Verify source IP (If not in debug mode)
	if( !$pfError)
	{

	    if( !$PF->pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
	        $mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= $pfErrMsg;
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	}

	//// Verify data received
	if( !$pfError )
	{

	    $pfValid = $PF->pfValidData( $pfHost, $pfParamString );

	    if( !$pfValid )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_BAD_ACCESS;
	        $mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= $pfErrMsg;
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	}

	//// Check data against MSC order
	if( !$pfError )
	{

	   	// Get the Order Details from the database
	    $db= oseDB :: instance();
		$where= array();
		$where[]= "`order_id` = ".$db->quote($pfData['m_payment_id']);
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		$order_id= $orderInfo->order_id;
		$member_id= $orderInfo->user_id;

		if(empty($orderInfo))
		{
			$mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= "Can not search any order information with utilizing the order number feedbacked by The PayFast IPN
					----------------------------------\n
					Hostname: $pfHost\n
					Order ID: ".$pfData['m_payment_id']."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
		}
	    // Check order amount
	    if( !$PF->pfAmountsEqual( $pfData['amount_gross'], $orderInfoParams->total ) )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;

	        $PF->blockUser($member_id);
	        $mailsubject= "Invalid PayPal IPN Transaction on your site";
			$mailbody= "Dear Administrator,<br /><br />";
			$mailbody .= "An Invalid PayPal Transaction requires your attention.<br /><br />";
			$mailbody .= "-----------------------------------------------------------<br /><br />";
			$mailbody .= "REMOTE IP ADDRESS: ".$_SERVER['REMOTE_ADDR']."<br /><br />";
			$mailbody .= "Order ID: ".$order_id."<br /><br />";
			$mailbody .= "User ID: ".$member_id."<br /><br />";
			$mailbody .= "Error: The amount customer paid does not match the price we set in the Membership!!<br /><br />";
			$mailbody .= "PayPal Parameters : Price:{$orderInfo->payment_price}&Customer Paid:{$pfData['amount_gross']}<br /><br />";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	    // Check order number
	    elseif( strcasecmp( $pfData['custom_str1'], $orderInfo->order_number ) != 0 )
	    {
	        $pfError = true;
	        $pfErrMsg = PF_ERR_ORDER_NUMBER_MISMATCH;

	        $mailsubject= "PayFast IPN Fatal Error on your Site";
			$mailbody= "Order Number is not match order number feedbacked by The PayFast IPN
					----------------------------------\n
					Hostname: $pfHost\n
					Order Number: ".$orderInfo->order_number."& PayFast Feedback Order Number:".$pfData['custom_str1']."\n";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

			exit;
	    }
	}

	//// Check status and update order
	if( !$pfError )
	{
	    // Check the payment_status is Completed
		if( $pfData['payment_status'] == 'COMPLETE')
	    {
	        $paymentOrder = oseRegistry :: call('payment')->getInstance('Order');

			$paymentOrder->confirmOrder($order_id, array(), 0, $member_id);

		}
		elseif( $pfData['payment_status'] == 'FAILED' )
		{
			//----------------------------------------------------------------------
			// If the payment_status is not Completed... do nothing but mail
			//----------------------------------------------------------------------
			// UPDATE THE ORDER STATUS to 'INVALID'
			oseRegistry :: call('payment')->updateOrder($order_id, "invalid");
			$process->blockUser($member_id);
			$payment_status= "Invalid";
			$mailsubject= "PayPal IPN Transaction on your site";
			$mailbody= "Hello,
			a Failed PayPal Transaction requires your attention.
			-----------------------------------------------------------
			Order ID: ".$order_id."
			User ID: ".$member_id."
			Payment Status returned by PayPal: $payment_status";
			$emailObj= new stdClass();
			$emailObj->subject= $mailsubject;
			$emailObj->body= $mailbody;
			$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);

	    }
	}

	// Close log

}

class oseMscIpnPayFast {

	function __construct()
	{

	}

	function pfGetData()
	{
	    // Posted variables from ITN
	    $pfData = $_POST;

	    // Strip any slashes in data
	    foreach( $pfData as $key => $val )
	    {
	        $pfData[$key] = stripslashes( $val );
	    }
	    // Return "false" if no data was received
	    if( sizeof( $pfData ) == 0 )
	    {
	    	 return( false );
	    }
	    else{
	    	return( $pfData );
	    }

	}

	function pfValidSignature( $pfData = null, &$pfParamString = null )
	{
	    // Dump the submitted variables and calculate security signature
	    foreach( $pfData as $key => $val )
	    {
	    	if( $key != 'signature' )
	    	{
	    		$pfParamString .= $key .'='. urlencode( $val ) .'&';
	    	}
	    }

	    // Remove the last '&' from the parameter string
	    $pfParamString = substr( $pfParamString, 0, -1 );
	    $signature = md5( $pfParamString );

	    $result = ( $pfData['signature'] == $signature );

	     return( $result );
	}

	function pfValidData( $pfHost = 'www.payfast.co.za', $pfParamString = '', $pfProxy = null )
	{

	    // Use cURL (if available)
	    if( defined( 'PF_CURL' ) )
	    {
	        // Variable initialization
	        $url = 'https://'. $pfHost .'/eng/query/validate';

	        // Create default cURL object
	        $ch = curl_init();

	        // Set cURL options - Use curl_setopt for freater PHP compatibility
	        // Base settings
	        curl_setopt( $ch, CURLOPT_USERAGENT, PF_USER_AGENT );  // Set user agent
	        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );      // Return output as string rather than outputting it
	        curl_setopt( $ch, CURLOPT_HEADER, false );             // Don't include header in output
	        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, true );
	        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

	        // Standard settings
	        curl_setopt( $ch, CURLOPT_URL, $url );
	        curl_setopt( $ch, CURLOPT_POST, true );
	        curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
	        curl_setopt( $ch, CURLOPT_TIMEOUT, PF_TIMEOUT );
	        if( !empty( $pfProxy ) )
	            curl_setopt( $ch, CURLOPT_PROXY, $pfProxy );

	        // Execute CURL
	        $response = curl_exec( $ch );
	        curl_close( $ch );
	    }
	    // Use fsockopen
	    else
	    {
	        // Variable initialization
	        $header = '';
	        $res = '';
	        $headerDone = false;

	        // Construct Header
	        $header = "POST /eng/query/validate HTTP/1.0\r\n";
	       	$header .= "Host: ". $pfHost ."\r\n";
	        $header .= "User-Agent: ". PF_USER_AGENT ."\r\n";
	        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	        $header .= "Content-Length: " . strlen( $pfParamString ) . "\r\n\r\n";

	        // Connect to server
	        $socket = fsockopen( 'ssl://'. $pfHost, 443, $errno, $errstr, PF_TIMEOUT );

	        // Send command to server
	        fputs( $socket, $header . $pfParamString );

	        // Read the response from the server
	        while( !feof( $socket ) )
	        {
	            $line = fgets( $socket, 1024 );

	            // Check if we are finished reading the header yet
	            if( strcmp( $line, "\r\n" ) == 0 )
	            {
	                // read the header
	                $headerDone = true;
	            }
	            // If header has been processed
	            else if( $headerDone )
	            {
	                // Read the main response
	                $response .= $line;
	            }
	        }

	    }

	     // Interpret Response
	    $lines = explode( "\r\n", $response );
	    $verifyResult = trim( $lines[0] );

	    if( strcasecmp( $verifyResult, 'VALID' ) == 0 )
	    {
	    	 return( true );
	    }else{
	    	return( false );
	    }

	}

	function pfValidIP( $sourceIP )
	{
	    // Variable initialization
	    $validHosts = array(
	        'www.payfast.co.za',
	        'sandbox.payfast.co.za',
	        'w1w.payfast.co.za',
	        'w2w.payfast.co.za',
	        );

	    $validIps = array();

	    foreach( $validHosts as $pfHostname )
	    {
	        $ips = gethostbynamel( $pfHostname );

	        if( $ips !== false )
	        {
	        	$validIps = array_merge( $validIps, $ips );
	        }

	    }

	    // Remove duplicates
	    $validIps = array_unique( $validIps );

	    if( in_array( $sourceIP, $validIps ) )
	    {
	    	return( true );
	    }
	    else{
	    	 return( false );
	    }

	}

	function pfAmountsEqual( $amount1, $amount2 )
	{
	    if( abs( floatval( $amount1 ) - floatval( $amount2 ) ) > PF_EPSILON )
	    {
	        return( false );
	    }else{
	        return( true );
	    }
	}

	function blockUser($member_id) {
		// Block the user immediately;
		$db= & JFactory :: getDBO();
		$query= "UPDATE `#__users` SET `block` =  '1' WHERE `id` = ".(int) $member_id;
		$db->setQuery($query);
		$db->query();
		// Logout the user as well;
//		$query= "DELETE FROM `#__session` WHERE `userid` = ".(int) $member_id." AND `client_id` = 0";
//		$db->setQuery($query);
//		$db->query();
//	}
}
?>