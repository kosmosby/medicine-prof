<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberOrder
{
	public function getOrders()
	{
		$db = oseDB::instance();
		//$my = JFactory::getUser();
		$member_id = JRequest::getInt('member_id',0);
		
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);

		$where = array();

		$where[] = "o.user_id = '{$member_id}'";
		$where[] = "o.entry_type IN ('msc','msc_list')";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT u.username,u.name, o.* "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				." LIMIT {$start},{$limit} "
				;
		
		$db->setQuery($query);
		//oseExit($db->_sql);
		//oseExit($db->_sql);
		$items = oseDB::loadList();
		$result = array();
		$result['total'] = $this->getTotal();
		$result['results'] = $items;
		return $result;
	}

	private function getTotal()
	{
		$db = oseDB::instance();
		//$my = JFactory::getUser();
		$member_id = JRequest::getInt('member_id',0);
		
		$where = array();

		$where[] = "o.user_id = '{$member_id}'";
		$where[] = "o.entry_type IN ('msc','msc_list')";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$result = $db->loadResult();
		return $result;
	}

	function orderView()
	{
		$db = oseDB::instance();

		$order_id = JRequest::getInt('order_id',0);

		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$orderInfo = oseRegistry::call('payment')->getOrder($where,'obj');

		$receipt = oseRegistry::call('member')->getReceipt($orderInfo);

		return $receipt;
	}

    function orderViewPDF()
    {
   		$l = null;
    	require_once(OSEMSC_F_PATH.DS.'libraries'.DS.'tcpdf'.DS.'tcpdf.php');
    	require_once(OSEMSC_F_PATH.DS.'libraries'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');

    	$order_id = JRequest::getInt('order_id',0);

    	//$my = JFactory::getUser();
		$member_id = JRequest::getInt('member_id',0);
		
		$where = array();
		$where[] = " `order_id` = {$order_id}";
		$where[] = " `user_id` = {$member_id}";

		$orderInfo = oseRegistry::call('payment')->getOrder($where,'obj');

		if(empty($orderInfo))
		{
			$result = array();
			$result['title'] = 'Error';
			$result['content'] = 'Error';

			oseExit('Error');
		}

		$receipt = oseRegistry::call('member')->getReceipt($orderInfo);

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('OSEMSC');
		$pdf->SetTitle('Invoice #'.$order_id);
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('invoice');


		// set default header data
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->SetMargins(30, 18, 30);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->AddPage();

		//ob_get_clean();
		
	
		$pdf->WriteHTML($receipt->body, true);
		$pdf->Output("Invoice-#{$order_id}.pdf", "I");

		oseExit();


    	/*
    	//$receipt = self::orderView();
    	$order_id = JRequest::getInt('order_id');

    	$app = JFactory::getApplication('SITE');
    	//oseExit('dfdf');
    	$app->redirect( JRoute::_('index.php?option=com_osemsc&view=member&format=pdf&memberTask=generateOrderView&order_id='.$order_id));
    	*/
    }
    
	
	function getOrder()
	{
		$order_id = JRequest::getInt('order_id',0);
		
		$where = array();
		$where[] = "order_id = {$order_id}";
		
		$payment = oseRegistry::call('payment');
		$order = $payment->getOrder($where,'obj');
		
		$result = array();
		$result['total'] = empty($order)?0:1;
		$result['result'] = $order;
		$result['success'] = true;
		
		return $result;
	}

}
?>