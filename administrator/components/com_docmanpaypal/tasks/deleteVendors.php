<?php
@$cid = JRequest::getVar('cid');

$database->setQuery("delete from #__docmanpaypalvendors where vendor_id in ('" . implode("','",$cid) . "');");
$database->query();
$mainframe->redirect('index.php?&option=com_docmanpaypal&task=vendors',_DMP_VENDORDELETED);
?>