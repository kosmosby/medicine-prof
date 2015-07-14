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
        <?php echo $this->answers[$this->questions[$this->i]->id];?>
    </div>

    <div>
        <?php echo JText::_('COM_FLEXPAPER_CORRECT_ANSWER');?>
    </div>

    <div>
        <?php
            $correct_answer_ = array();
            $correct_answer_[] = $this->questions[$this->i]->a1;
            $correct_answer_[] = $this->questions[$this->i]->a2;
            $correct_answer_[] = $this->questions[$this->i]->a3;
            $correct_answer_[] = $this->questions[$this->i]->a4;
            $correct_answer_[] = $this->questions[$this->i]->a5;

            $array_empty = array(null);
            $correct_answer_ = array_diff($correct_answer_, $array_empty);

            if(in_array($this->answers[$this->questions[$this->i]->id],$correct_answer_)) {
                    echo $this->answers[$this->questions[$this->i]->id];
                }
                else {
                    echo $this->questions[$this->i]->a1;
                }
        ?>

      </div>

    <br />