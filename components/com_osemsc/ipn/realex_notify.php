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
	$app = JFactory :: getApplication('site');
	jimport('joomla.plugin.helper');
	/*** END of Joomla config ***/
	/*** OSE part ***/
	require_once(JPATH_BASE.DS."components".DS."com_osemsc".DS."init.php");
	/*** END OSE part ***/

	$oseMscConfig= oseRegistry :: call('msc')->getConfig(null, 'obj');
	$apiEmail= oseRegistry :: call('member')->getInstance('email');
	$config= new JConfig();
	$mailfrom= $config->mailfrom;
	$fromname= $config->fromname;
	$merchantid = $oseMscConfig->realex_merchant_id;
	$secret = $oseMscConfig->realex_secret;
	$account = $oseMscConfig->realex_account;
		
	$process = new oseMscIpnRealex($_POST);
	$timestamp = $process->get('TIMESTAMP');
	$result = $process->get('RESULT');
	$orderid = $process->get('ORDER_ID');
	$message = $process->get('MESSAGE');
	$authcode = $process->get('AUTHCODE');
	$pasref = $process->get('PASREF');
	$realexmd5 = $process->get('MD5HASH');
	$amount = $process->get('AMOUNT');
	$currency = $process->get('CURRENCY');
	$pas_step = $process->get('pas_step');
	$number = $process->get('number');
	$realexsha1 = $process->get('SHA1HASH');
	/*
	$tmp = "$timestamp.$merchantid.$orderid.$result.$message.$pasref.$authcode";
	$md5hash = md5($tmp);
	$tmp = "$md5hash.$secret";
	$md5hash = md5($tmp);
	*/
	//TIMESTAMP.MERCHANT_ID.ORDER_ID.RESULT.MESSAGE.PASREF.AUTHCODE
	
	$tmp = "$timestamp.$merchantid.$orderid.$result.$message.$pasref.$authcode";
	$sha1hash = sha1($tmp);
	$tmp = "$sha1hash.$secret";
	$sha1 = sha1($tmp);
	
	//Check to see if hashes match or not
	if ($sha1 != $realexsha1) 
	{
		$mailsubject= "Realex IPN Transaction Error on your site";
		$mailbody= "Validation Failed,hashes is not match";
		$emailObj= new stdClass();
		$emailObj->subject= $mailsubject;
		$emailObj->body= $mailbody;
		$apiEmail->sendToAdminGroup($emailObj, $oseMscConfig->admin_group);
		$app->redirect(JURI :: root()."index.php?option=com_osemsc&view=register",'Error');
		return;
	} else {
		if ($result == "00") {
			$db = oseDB::instance();
			$where = array();
			$where[] = "`order_id` = " . $db->quote($orderid);
			$payment = oseRegistry::call('payment');
			$orderInfo = $payment->getOrder($where, 'obj');
			$orderInfoParams = oseJson::decode($orderInfo->params);
			$order_id = $orderInfo->order_id;
			$member_id = $orderInfo->user_id;
			$server = str_replace('/components/com_osemsc/ipn', '', JURI::base());
			$returnUrl = JROUTE::_($server . "index.php?option=com_osemsc&view=thankyou&order_id=" . $orderInfo->order_id);
			$redirectUrl = (!empty($returnUrl)) ? $returnUrl : JURI::root() . "index.php?option=com_osemsc&view=member";
			
			$payment = oseRegistry::call('payment')->getInstance('Order');
			$payment->confirmOrder($order_id, array(), 0, $member_id);
			
			$message = $process->getMessage($redirectUrl);
			echo $message.''; exit;
			//$app->redirect($redirectUrl, $message);
		} else {
			echo 'There is an error in your transaction. Please retry.'; exit;
			//$app->redirect(JURI::root() . "index.php?option=com_osemsc&view=register", $message);
		}
	}
}

class oseMscIpnRealex {
	
	function __construct($post) {
		// Notify string
		foreach($post as $key=>$value)
		{
			$this->{$key} = JRequest :: getVar($key,null);
		}
	}
	
	
	function get($key, $default= null) {
		if(empty($this-> {
			$key })) {
			$this-> {
				$key }
			= $default;
		}
		return $this-> {
			$key };
	}
	
	function getMessage($redirectUrl)
	{
		return '<div align="center" style="padding: 50px 0px;" width="900px">
				<table id="realexresult"  cellspacing="0" >
					<!-- Table header -->
						<thead>
							<tr>
								<th scope="col"  style="font-family: arial;color: #fff; font-weight: bold; background-color: #66BCDA; border-radius: 5px 5px 0px 0px;" align="center">Transaction Result</th>
							</tr>
						</thead>
					<!-- Table footer -->
						<tfoot>
					        <tr >
					              <td style="font-family: arial;color: #9a9a9a; font-size: 70%; padding: 5px 0px;" align="center">Secure transaction made by Realex and OSE Membership	 </td>
					        </tr>
						</tfoot>
					
					<!-- Table body -->
					
						<tbody>
							<tr>
								<td style="font-family: arial;color: #444; padding: 30px 0px; font-size: 90%; border: 1px solid #dadada;">Your transaction is successful, you will be redirected back to the merchant\'s website shortly.</td>
							</tr>
						</tbody>
				</table>
				</div>
				<script type="text/javascript">
					window.location = "'.$redirectUrl.'"
					</script>
				';
	}
}
?>