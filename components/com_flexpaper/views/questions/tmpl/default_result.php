<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'questions'.DS.'tmpl' );

?>
<div><?php echo $this->coursename;?> <br /><br /></div>

<div style="height: 130%;">

    <?php for($i=0;$i<count($this->questions);$i++) {
        $this->i=$i;

        switch($this->questions[$i]->qtype) {
            case 'sa':
                echo $this->loadTemplate('results_sa');
                break;
            case 'mc5':
                echo $this->loadTemplate('results_mc5');
                break;
        }
    }?>

    <div style="padding-top: 50px; color:<?php echo $this->passed?'#358256':'#cc0033';?> ">

        <?php if($this->passed) {

            echo JText::_('COM_FLEXPAPER_PASSED_QUIZ');

        } else {

        echo JText::_('COM_FLEXPAPER_HAVENT_PASSED_QUIZ');
        }?>
    </div>

    <div><?php echo JText::_('COM_FLEXPAPER_PASSED_MARK_FOR_THE_QUIZ');?> <?php echo $this->passed_mark;?>%</div>
    <div><?php echo JText::_('COM_FLEXPAPER_USER_SCORE');?> <?php echo round($this->user_score);?>%</div>

</div>

