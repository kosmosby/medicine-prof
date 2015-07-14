<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'questions'.DS.'tmpl' );
?>
<div><?php echo $this->coursename;?> <br /><br /></div>

<div style="height: 130%;">
    <form action="<?php echo JRoute::_('index.php?option=com_flexpaper');?>"
          method="post" name="questionsForm" id="questions-form" enctype="multipart/form-data">

            <?php for($i=0;$i<count($this->items);$i++) {
                $this->i=$i;
                switch($this->items[$i]->qtype) {
                    case 'sa':
                        echo $this->loadTemplate('sa');
                        break;
                    case 'mc5':
                        echo $this->loadTemplate('mc5');
                        break;
                }
            }

        ?>
        <input type="hidden" name="testid" value="<?php echo $this->testid;?>">
        <input type="hidden" name="user_id" value="<?php echo $this->user_id;?>">
        <input type="hidden" name="course_id" value="<?php echo $this->course_id;?>">
    </form>
</div>

<div>
    <input type="button" id="finish-test" value="<?php echo JText::_('COM_FLEXPAPER_FINISH_TESRT_BUTTON');?>">
</div>