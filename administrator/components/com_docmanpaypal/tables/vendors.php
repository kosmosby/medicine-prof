<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filter.input');

//die('opaa');

class TableVendors extends JTable
{
var $name = null;
var $paypalemail = null;
var $mypercent = null;
	function __construct(& $db) {

		parent::__construct('#__docmanpaypalvendors', 'vendor_id', $db);

	}

}

?>