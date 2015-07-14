<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class flexpaperModelPay extends JModelList
{

    public function getPaypalForm($data = array(), $loadData = true)
    {
        $user = JFactory::getUser();
        $user_id = $user->id;
        $id = JRequest::getInt('id');
        $size = JRequest::getVar('size');

        $row = new stdClass();

        $row->id = $id;

        $row->user_id = $user_id;

        return $row;
    }


    public function getResponsePost() {

        $user_id = JRequest::getInt('custom');
        $payment_status = JRequest::get('payment_status');
//        $item_name = JRequest::getInt('item_name');
//
//        $sum = JRequest::getFloat('sum');
//
//
//
//        $clientid = JRequest::getInt('clientid');
//        $order_id = JRequest::getInt('orderid');
//        $key = JRequest::getString('key');


/*

        $payments =& JTable::getInstance('orders', 'isearchTable');

        $payments->load($order_id);


        if($payments->status == 0 && $payments->payment_id ==0 && $payments->user_id == $clientid) {

            $row = array();

            $row['status']=1;
            $row['sum']=$sum;
            $row['payment_id']=$payment_id;

            if (!$payments->bind($row)) {
                return JError::raiseWarning( 500, $row->getError() );
            }

            if (!$payments->store()) {
                JError::raiseError(500, $row->getError() );
            }

            $credits =& JTable::getInstance('credit', 'VideoTranslationTable');

            $creditId = $credits->getIdByUserId($clientid);

            $row = array();

            if($clientid) {
                $credits->load($creditId);
            }
            else {
                $row['id'] = '';
            }

            $row['user_id'] = $clientid;
            $row['amount'] = $credits->amount + $sum;

            if (!$credits->bind($row)) {
                return JError::raiseWarning( 500, $row->getError() );
            }

            if (!$credits->store()) {
                JError::raiseError(500, $row->getError() );
            }

        }


        $hash = md5($payment_id.$secret_seed);
        echo "OK $hash";
*/
//        echo "<pre>";
//        print_r($payments->id); die;

        // multiple recipients
        $to  = 'kosmos.by@gmail.com'; // note the comma
//
//
//        // subject
        $subject = 'post response';
//
        //$string = print_r($_REQUEST);
//
//        // message
//        //$message= "<pre>";
        $message = http_build_query($_REQUEST);
//
//        // Mail it
        mail($to, $subject, $message);
    }

}
