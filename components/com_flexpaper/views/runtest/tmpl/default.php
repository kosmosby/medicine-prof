<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
echo $this->loadTemplate('topmenu');

if(!count($this->answers)) {
?>
    <div id="test_content">
           <div style="height: 300px;">
                <?php echo JText::_('COM_FLEXPAPER_RUNTEST_INFO');?>
           </div>
           <div>
                <input type="button" id="runtest" testid="<?php echo $this->testid;?>" course_id="<?php echo $this->course_id;?>" value="<?php echo JText::_('COM_FLEXPAPER_RUNTEST_BUTTON');?>">
           </div>
    </div>

<?php } else {
    $this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'questions'.DS.'tmpl' );
    echo $this->loadTemplate('result');
}

