<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

require_once(realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'classes'.DS.'gateways.php'));

class Pay_Sagepayments extends TheFactoryPaymentGateway
{
    var $name='pay_sagepayments';
    var $fullname='SagePayments Payment method';
    
    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);

        if ($params->get('use_sandbox',0))
            $form_action="https://www.sandbox.paypal.com/cgi-bin/webscr";
        else
            $form_action="https://www.paypal.com/cgi-bin/webscr";
        $paypal_address=$params->get('paypalemail','');
        $result ="<form action='https://www.sagepayments.net/eftcart/forms/order.asp' name=sagePaymentForm method=post>";
        $result.="<input type='hidden' name='m_id' value='".$params->get("Terminal_id")."'>";
        $result.="<input type='hidden' name='M_image' value='".$params->get("Logo_url")."'>";
        $result.="<input type='hidden' name='B_color' value='".$params->get("B_color")."'>";
        $result.="<input type='hidden' name='BF_color' value='".$params->get("BF_color")."'>";
        $result.="<input type='hidden' name='M_color' value='".$params->get("M_color")."'>";
        $result.="<input type='hidden' name='F_color' value='".$params->get("F_color")."'>";
        $result.="<input type='hidden' name='t_amt' value='{$order->order_total}'>";

        $result.="<input type='hidden' name='Approved_url' value='".$urls['return_url']."'>";
        $result.="<input type='hidden' name='Declined_url' value='".$urls['cancel_url']."'>";
        $result.="<input type='image' src='http://www.sagenorthamerica.com/~/media/Images/logo_sage.gif' border='0' name='submit' alt='".JText::_("FACTORY_BUY_NOW")."' style='margin-left: 30px;'>";
        for($i=0;$i<count($items);$i++)
        {
            $result.="<input type='hidden' name='P_desc".($i+1)."' value='".$items[$i]->itemdetails." '>";
            $result.="<input type='hidden' name='P_part".($i+1)."' value='".$items[$i]->itemname." '>";
            $result.="<input type='hidden' name='P_price".($i+1)."' value='".$items[$i]->price." '>";
            $result.="<input type='hidden' name='P_qty".($i+1)."' value='".$items[$i]->quantity." '>";
        }
        $result.="</form>";
        return $result;
    }
    function processIPN()
    {
    }
}


