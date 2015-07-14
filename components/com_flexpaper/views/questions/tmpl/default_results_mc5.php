<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
    <div>
        <div style="color: black; font-weight: bold;"><?php echo JText::_('COM_FLEXPAPER_Q');?><?php echo $this->i+1;?>:<?php echo $this->questions[$this->i]->question;?></div>
    </div>

    <div>
        <?php echo JText::_('COM_FLEXPAPER_YOUR_ANSWER');?>
    </div>

    <div>
        <?php

        if(!isset($this->is_passed)) {
            $answer_value = isset($this->answers[$this->questions[$this->i]->id])?$this->answers[$this->questions[$this->i]->id]:'';
            echo isset($this->questions[$this->i]->$answer_value)?$this->questions[$this->i]->$answer_value:'';
        }
        else {
            echo isset($this->answers[$this->questions[$this->i]->id])?$this->answers[$this->questions[$this->i]->id]:'';
        }
        ?>
    </div>

    <div>
        <?php echo JText::_('COM_FLEXPAPER_CORRECT_ANSWER');?>
    </div>

    <div>
        <?php
        $answer_value = 'a'.$this->questions[$this->i]->answer;
        echo isset($this->questions[$this->i]->$answer_value)?$this->questions[$this->i]->$answer_value:'';?>
    </div>

<br />