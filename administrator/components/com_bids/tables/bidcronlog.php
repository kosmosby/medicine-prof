<?php

defined('_JEXEC') or die('Restricted access');

class JTableBidCronLog extends JTable
{
    var $id;
    var $priority;
    var $event;
    var $logtime;
    var $log;  

	function __construct(&$db)
	{
		parent::__construct( '#__bid_cronlog', 'id', $db );
	}
 
}

?>
