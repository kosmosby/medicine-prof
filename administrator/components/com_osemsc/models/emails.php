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
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");
class oseMscModelEmails extends oseMscModel
{
	protected $total = 0;

    public function __construct()
    {
        parent::__construct();
    } //function

	function getList()
	{
		$db = oseDB::instance();

		$where = array();

        $search = JRequest::getString('search',null);

    	if (isset( $search ) && $search!= '')
		{
			$searchEscaped = $this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );
			$where[] = ' subject LIKE '.$searchEscaped
					  ;
		}

		$where = array_merge($where,oseJSON::generateQueryWhere());

		// Generate the where query
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$query = " SELECT id,subject,body,type, count(*) As total FROM `#__osemsc_email`"
				. $where
				;
		//$db->setQuery($query);
		$this->total = $this->_getListCount($query);
       	//oseExit($query);
        $start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$db->setQuery($query,$start,$limit);

		//oseExit($db->_sql);
		$rows = $db->loadAssocList();
		//oseExit($rows);
		return $rows;
	}

	function getTotal()
	{
		if($this->total < 0)
        {
        	$this->getList();
        }

        return $this->total;
	}

	function getEmails($type='array')
	{
		$email_type = JRequest::getWord('email_type',null);
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$email = oseRegistry::call('member')->getInstance('Email');
		return $email->getEmails($email_type,$start,$limit,$type);
	}

	function getItem($id)
	{
		$email = oseRegistry::call('member')->getInstance('Email');
		return $email->getDoc($id);
	}

	function save()
	{
		$post = array();

		$email = oseRegistry::call('member')->getInstance('Email');

		$id = JRequest::getInt('id',0);
		$body = JRequest::getString('ebody','', 'post', JREQUEST_ALLOWRAW);
		$subject = JRequest::getString('subject',null);
		$type = JRequest::getString('type','email');
		$msc_id = JRequest::getInt('msc_id',0);
		$post['id'] = $id;
		$post['body'] = $body;
		$post['subject'] = $subject;
		$post['type'] = $type;
		$post['msc_id'] = $msc_id;
		$post['params'] = oseJSON::encode($email->buildEmailParams($type));

		return $email->save($post);
	}

	function remove($email_id)
	{
		$email = oseRegistry::call('member')->getInstance('Email');

		return $email->remove($email_id);
	}

	function getEmailParams()
	{
	    require_once(OSEMSC_B_PATH.DS.'libraries'.DS.'email_params_defination.php' );

	    $db = oseDB::instance();

	    $email = oseRegistry::call('member')->getInstance('Email');

	    $id = JRequest::getInt('id');
	    $type = JRequest::getVar('type');

	    $query = "SELECT * FROM `#__osemsc_email` WHERE `id` = '{$id}'";
	    $db->setQuery($query);
	    $obj = $db->loadObject();

	    if(empty($id) || $obj->type != $type)
	    {
	    	$params = $email->buildEmailParams($type);
	    }else
	    {
	      	$params = oseJson::decode($obj->params);
	    }

	    $results=array();

	    foreach ($params as $key => $param)
	    {
	        $arr = explode('.',$key);
	        $paramType = $arr[0];
	        $paramName = $arr[1];

	        $tKey = 'OSEEMAIL_'.strtoupper($paramType.'_'.$paramName);
	        //$result= '[$key] : '.constant($tKey);
	        //eval("\$result = \"$result\";");
	        $results[$key]=constant($tKey);
	    }
	    return $results;
	}

	function loadEmailTemplate()
	{
		$db = oseDB::instance();
		$query = "INSERT INTO `#__osemsc_email` (`id`, `msc_id`, `subject`, `body`, `type`, `params`) VALUES" .
				"(NULL, 0, 'Sample Sales Receipt', '<div style=\"margin-left: 50px;\">\n<div style=\"margin-top: 20px; margin-left: 10px; display: block;\">\n<table width=\"100%\">\n<tbody>\n<tr>\n<td width=\"80px\">YOUR LOGO<br /></td>\n<td style=\"color: #333333;\">&nbsp;</td>\n</tr>\n</tbody>\n</table>\n</div>\n<div id=\"invoice-to\" style=\"margin-left: 10px; margin-top: 10px;\">\n<div style=\"margin-left: 1px; margin-bottom: 0px; margin-top: 0px; font-family: Tahoma,Geneva,Kalimati,sans-serif; color: #238db4; vertical-align: top; font-weight: bold; text-align: left;\">Thank you for your order!</div>\n<br />\n<div style=\"background-color: #e8f1fa; padding: 5px;\"><span style=\"font-weight: bold;\">Invoice To:</span></div>\n<div style=\"padding: 5px;\">[user.firstname] [user.lastname]<br /> [user.company]<br /> [user.email]<br /> [user.address1]<br /> [user.address2]<br /> [user.city] [user.state]<br /> [user.country] [user.postcode]<br /> [user.telephone]</div>\n</div>\n<div style=\"margin-left: 10px; margin-top: 10px;\">\n<div style=\"background-color: #e8f1fa; padding: 5px;\"><span style=\"font-weight: bold;\">Order Information</span></div>\n<div style=\"padding: 5px;\">Order Number: [order.order_number] <br /> Order Date: [order.date] <br /> Order Status: [order.order_status]</div>\n<div style=\"background-color: #e8f1fa; padding: 5px; margin-top: 10px;\"><span style=\"font-weight: bold;\">Membership Detail</span></div>\n<div>[order.itemlist]</div>\n<div>\n<p style=\"text-align: justify;\"><strong><span style=\"color: #cc6600;\">Subscriptions - About Recurring Subscriptions<br /></span></strong></p>\n</div>\n</div>\n</div>', 'receipt', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}')," .
				"(NULL, 0, 'Sample Terms of Service', '<h4>Sample Terms of Service</h4>\n<h4>About Recurring Subscriptions</h4>\n<p>For  Paypal, you can simply cancel the recurring payment in your  Paypal  account, or please contact us by  email before the expiration of your  subscription term and we''ll do it for you.</p>\n<p style=\"text-align: justify;\">To  cancel your  recurring Visa or Mastercard subscription payment, please  contact us by  email before the expiration of your subscription term and  we will remove  you from any future recurring billing.</p>\n<p style=\"text-align: justify;\">Please notify us at least 48 hours if you wish to cancel your  Paypal or  Credit Card recurring  billing before your next renewal date. Please  note there are no refunds for recurring billing once the charge has been  processed.</p>', 'term', '[]')," .
				"(NULL, 0, 'Sample Cancellation Confirmation', '<p>Dear [user.firstname]</p>\n<p>Thank you for your email indicating you would like to cancel your recurring billing. You will be removed from any future recurring billing and your subscription will end at the end of your current term.</p>\n<p>I hope you have enjoyed being a member of our website and I hope you will re-join us sometime in the near future.</p>\n<p>If you have any questions or concerns, please, I invite you to write me back personally.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team<br /></strong></p>', 'cancel_email', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
				"(NULL, 0, 'Sample Your Username + Password Details', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Here are your login credentials:</p>\n<p>LOGIN USERNAME: [user.username]</p>\n<p>LOGIN PASSWORD: [user.password]</p>\n<p>Please keep these details for future reference.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'reg_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.name\",\"user.password\":\"user.password\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.user_status\":\"user.block\"}')," .
				"(NULL, 0, 'Sample Membership Expiration Alert', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Your membership is coming close to it''s expiration date [member.expired_date].</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'notification', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.block\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
				"(NULL, 0, 'Sample Membership Expiration', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Your membership has expired. Thank you for having been a member.</p>\n<p>Should you like to renew please go to our website to sign up.</p>\n<p>If you have any questions or concerns, please do not hesitate to personally contact me.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'exp_email', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.block\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
				"(NULL, 0, 'Sample Successful Sign Up email', '<div class=\"item\">\n<div class=\"pos-content\">\n<div class=\"element element-textarea  first last\">Hi [user.firstname]</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\">Your membership has been activated successfully.</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\">Best wishes</div>\n<div class=\"element element-textarea  first last\">Management Team</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\"></div>\n</div>\n</div>', 'wel_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.real_expired_date\",\"member.period\":\"member.period\",\"member.msc_title\":\"member.msc_title\",\"member.msc_des\":\"member.msc_des\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.discount\":\"order.discount\",\"order.table\":\"order.table\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\"}')," .
				"(NULL, 0, 'Sample Sales Receipt 2', '<div class=\"osereceipt\">\n<div class=\"receipt-content\">\n<table width=\"100%\">\n<tbody>\n<tr>\n<td width=\"70%\">\n<p><span class=\"invoice-header\">INVOICE</span><br /><br /> <span class=\"date\">Date: [order.date]</span><br /><br /><span class=\"invoice-number\">Invoice # 2011-[order.order_id]</span></p>\n<p class=\"billing-info\"><strong><span class=\"billing-header\">Billing Address</span></strong><br /> [user.company]<br /> [user.address1]<br /> [user.city], [user.state], [user.postcode]<br /> [user.firstname] [user.lastname]</p>\n<p class=\"customer-ref\">Customer Reference Number: <br /> [order.order_number]</p>\n</td>\n<td style=\"color: #666666;\">&nbsp;YOUR LOGO HERE</td>\n</tr>\n</tbody>\n</table>\n<br /> \n<table class=\"receipt-list\" width=\"100%\">\n<tbody>\n<tr class=\"rows\">\n<td class=\"header\" height=\"25px\" valign=\"middle\">Subscription Detail</td>\n</tr>\n<tr class=\"rows\">\n<td class=\"items\" height=\"70px\" valign=\"middle\">[order.itemlist]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">SUBTOTAL EUR [order.subtotal]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">SALES TAX EUR [order.gross_tax]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">TOTAL EUR [order.total]</td>\n</tr>\n</tbody>\n</table>\n<br /><br />\n<div style=\"text-align: center;\"><span class=\"thank-you\">Thank you for your business!</span></div>\n<br /><br />\n<div style=\"text-align: center;\"><span class=\"slogan\">YOUR SLOGAN HERE</span></div>\n</div>\n</div>', 'receipt', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.vat_number\":\"order.vat_number\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}'),".
				"(NULL, 0, 'Sample Membership Cancellation Email', '<div>\n<div>Dear [user.name],</div>\n<div></div>\n<div>Your membership has been cancelled.</div>\n</div>\n<div>\n<div>Membership associated order id: [order.order_id]</div>\n</div>\n<div>\n<div>Membership associated order number: [order.order_number]</div>\n</div>\n<div>\n<div>Membership associated order status:[order.order_status]</div>\n</div>\n<p>Best regards</p>\n<p>Management Team</p>', 'cancelorder_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.vat_number\":\"order.vat_number\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}');";
		$db->setQuery($query);
		if ($db->query())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}