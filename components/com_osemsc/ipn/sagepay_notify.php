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

if($_REQUEST) {
	header("HTTP/1.0 200 OK");
	//global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mailfrom, $fromname;
	/*** access Joomla's configuration file ***/
	// Set flag that this is a parent file
	define('_JEXEC', 1);
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define('DS', DIRECTORY_SEPARATOR);
	require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
	require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$mainframe= & JFactory :: getApplication('site');
	jimport('joomla.plugin.helper');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/

	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$vendorname = $oseMscConfig->sagepay_vendorname;	
	$password = $oseMscConfig->sagepay_password;
	$sagepay_mode = $oseMscConfig->sagepay_mode;
	$vendoremail = $oseMscConfig->sagepay_vendoremail;
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	$server = str_replace('/components/com_osemsc/ipn','',JURI :: base());
	$process= new oseMscIpnSagepay($password);
	
	$strDecoded=$process->decodeAndDecrypt($process->get('strCrypt'));
	$values = $process->getToken($strDecoded);
	
	$db= oseDB :: instance();
	$where= array();
	$where[]= "`order_number`=".$db->quote($values["VendorTxCode"]);
	$payment= oseRegistry :: call('payment');
	$orderInfo = $payment->getOrder($where, 'obj');
	if(empty($orderInfo)) {
		$mailsubject= "IPN Fatal Error on your Site";
		$mailbody= "Can not search any order information with utilizing the order number feedbacked by The IPN
				----------------------------------\n
				Invoice: ".$values["VendorTxCode"]."\n";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$mainframe->redirect($server."index.php?option=com_osemsc&view=register",'Transaction Failed, Order Not Found!');
		return;
	}
	
	$order_id= $orderInfo->order_id;
	$member_id= $orderInfo->user_id;
	$orderInfoParams= oseJson :: decode($orderInfo->params);
	$returnUrl = JROUTE::_($server."index.php?option=com_osemsc&view=thankyou&order_id=".$orderInfo->order_id);
	$returnUrl = $returnUrl?$returnUrl:$server."index.php?option=com_osemsc&view=member";
	
	if($values['Status'] == 'OK')
	{
		if($values["3DSecureStatus"] == 'NOTCHECKED' || $values["3DSecureStatus"] == 'OK')
		{
			$payment= oseRegistry :: call('payment')->getInstance('Order');
			$payment->confirmOrder($order_id, array());
			$mainframe->redirect($returnUrl);
		}else{
			$mainframe->redirect($server."index.php?option=com_osemsc&view=register","3D-Secure Status:".$values["3DSecureStatus"]);
		}
		
	}else
	{
		$mainframe->redirect($server."index.php?option=com_osemsc&view=register",$values['StatusDetail']);
	}
}
class oseMscIpnSagepay {
	function __construct($password) {
		// Notify string
		$this->strCrypt= JRequest :: getVar('crypt');
		$this->EncryptionPassword = $password;
	}
	
	function decodeAndDecrypt($strIn) 
	{
		
		$strEncryptionPassword = $this->EncryptionPassword;
		
		if (substr($strIn,0,1)=="@") 
		{
			//** HEX decoding then AES decryption, CBC blocking with PKCS5 padding - DEFAULT **
			
			//** use initialization vector (IV) set from $strEncryptionPassword
	    	$strIV = $strEncryptionPassword;
	    	
	    	//** remove the first char which is @ to flag this is AES encrypted
	    	$strIn = substr($strIn,1); 
	    	
	    	//** HEX decoding
	    	$strIn = pack('H*', $strIn);
	    	
	    	//** perform decryption with PHP's MCRYPT module
			return $this->removePKCS5Padding(
				mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV)); 
		} 
		else 
		{
			//** Base 64 decoding plus XOR decryption **
			return simpleXor(base64Decode($strIn),$strEncryptionPassword);
		}
	}
	
	function removePKCS5Padding($decrypted) 
	{
		$padChar = ord($decrypted[strlen($decrypted) - 1]);
	    return substr($decrypted, 0, -$padChar); 
	}

	function getToken($thisString) 
	{
	
	  // List the possible tokens
	  $Tokens = array(
	    "Status",
	    "StatusDetail",
	    "VendorTxCode",
	    "VPSTxId",
	    "TxAuthNo",
	    "Amount",
	    "AVSCV2", 
	    "AddressResult", 
	    "PostCodeResult", 
	    "CV2Result", 
	    "GiftAid", 
	    "3DSecureStatus", 
	    "CAVV",
		"AddressStatus",
		"CardType",
		"Last4Digits",
		"PayerStatus");
	
	  // Initialise arrays
	  $output = array();
	  $resultArray = array();
	  
	  // Get the next token in the sequence
	  for ($i = count($Tokens)-1; $i >= 0 ; $i--){
	    // Find the position in the string
	    $start = strpos($thisString, $Tokens[$i]);
		// If it's present
	    if ($start !== false){
	      // Record position and token name
	      $resultArray[$i]->start = $start;
	      $resultArray[$i]->token = $Tokens[$i];
	    }
	  }
	  
	  // Sort in order of position
	  sort($resultArray);
		// Go through the result array, getting the token values
	  for ($i = 0; $i<count($resultArray); $i++){
	    // Get the start point of the value
	    $valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
		// Get the length of the value
	    if ($i==(count($resultArray)-1)) {
	      $output[$resultArray[$i]->token] = substr($thisString, $valueStart);
	    } else {
	      $valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
		  $output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
	    }      
	
	  }
	
	  // Return the ouput array
	  return $output;
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
	}
	function get($key, $default= null) {
		if(empty($this->{$key})) {
			$this->{$key}= $default;
		}
		return $this-> {$key};
	}
}
?>