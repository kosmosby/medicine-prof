<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $result=new stdClass();
    $result->result=0;
    $result->errorText=$this->message;
    $reg=new JRegistry($result);
    echo $reg->toString('JSON');

?>
