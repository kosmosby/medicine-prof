<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
    <div>
        <div style="color: black; font-weight: bold;"><?php echo JText::_('COM_FLEXPAPER_Q');?><?php echo $this->i+1;?>:<?php echo $this->items[$this->i]->question;?></div>
    </div>
<div><?php

        if($this->items[$this->i]->a1) {
            ?><input type="radio" name="answer[<?php echo $this->items[$this->i]->id;?>]" value="a1"><?php echo $this->items[$this->i]->a1;?><br /><?
        }
        if($this->items[$this->i]->a2) {
            ?><input type="radio" name="answer[<?php echo $this->items[$this->i]->id;?>]" value="a2"><?php echo $this->items[$this->i]->a2;?><br /><?
        }
        if($this->items[$this->i]->a3) {
            ?><input type="radio" name="answer[<?php echo $this->items[$this->i]->id;?>]" value="a3"><?php echo $this->items[$this->i]->a3;?><br /><?
        }
        if($this->items[$this->i]->a4) {
            ?><input type="radio" name="answer[<?php echo $this->items[$this->i]->id;?>]" value="a4"><?php echo $this->items[$this->i]->a4;?><br /><?
        }
        if($this->items[$this->i]->a5) {
            ?><input type="radio" name="answer[<?php echo $this->items[$this->i]->id;?>]" value="a5"><?php echo $this->items[$this->i]->a5;?><br /><?
        }?>
    </div>
    <br />