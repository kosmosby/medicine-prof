<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $result=new stdClass();
    $result->result=1;
    $reg=new JRegistry($result);
    echo $reg->toString('JSON');

?>
