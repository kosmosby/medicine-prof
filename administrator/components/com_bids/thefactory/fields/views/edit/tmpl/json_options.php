<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $reg=new JRegistry($this->field->getOptions());
    echo $reg->toString('JSON');
?>
