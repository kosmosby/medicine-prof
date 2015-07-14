<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//echo "<pre>";
//print_r($this->items); die;

$this->addTemplatePath( JPATH_COMPONENT.DS.'views'.DS.'quizes'.DS.'tmpl' );
echo $this->loadTemplate('topmenu');
?>

<table border="0" width="100%">
    <tr>
        <th align="left" width="50%" height="50"><?php echo JText::_('COM_FLEXPAPER_COURSE_NAME');?></th>
    </tr>

    <?php for($i=0;$i<count($this->items);$i++) {?>
    <tr>
        <td>
            <div>
                <?php echo $this->items[$i]->title;?>
            </div>

            <div>
                <video src="/docs/video/<?php echo $this->items[$i]->video_file;?>" width="320" height="240"></video>
            </div>
        </td>
    </tr>
    <?php }?>
</table>