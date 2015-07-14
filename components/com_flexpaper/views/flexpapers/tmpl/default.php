<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//echo "<pre>";
//print_r($this->items); die;


    $this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
    echo $this->loadTemplate('topmenu');




echo $this->loadTemplate('menu');

?>
<div class="grid_18 egitim_sag">
    <?php echo $this->description;?>
</div>
