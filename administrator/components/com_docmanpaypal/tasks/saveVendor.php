<?php

$row =& JTable::getInstance('vendors','Table');

@$cid = JRequest::getVar('cid');

if ($cid[0] > 0) {

	$row->load($cid[0]);

} else {

	$row->load(0);

}

$post = JRequest::get('post');
$row->bind($post);
if ($row->name != '' and $row->paypalemail != '') {
	$row->store();
	$mainframe->redirect('index.php?&option=com_docmanpaypal&task=vendors',_DMP_VENDORSAVED);
} else {
	$mainframe->enqueueMessage(_DMP_VENDORNOTSAVED,'error');
	$mainframe->redirect('index.php?&option=com_docmanpaypal&task=vendors');
}

?>