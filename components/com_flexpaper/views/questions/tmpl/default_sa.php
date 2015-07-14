<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
        <div>
             <div style="color: black; font-weight: bold;"><?php echo JText::_('COM_FLEXPAPER_Q');?><?php echo $this->i+1;?>:<?php echo $this->items[$this->i]->question;?></div>
        </div>
        <div>
            <input type="text" name="answer[<?php echo $this->items[$this->i]->id;?>]">
        </div>
        <br />