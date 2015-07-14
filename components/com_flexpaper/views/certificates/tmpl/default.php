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
        <th align="left" width="300" height="50"><?php echo JText::_('COM_FLEXPAPER_GET_CERTIFICATE');?></th>
    </tr>

    <?php for($i=0;$i<count($this->items);$i++) {?>
        <tr>
            <td>
                <div style="float: left;">
                    <a href="javascript:void(0);" class="get_certificate" script_path="<?php echo $this->path;?>" cert_type="<?php echo $this->items[$i]->passed?'bs_certificate':'ks_certificate';?>" user_id="<?php echo $this->items[$i]->userid;?>" cert_id="<?php echo $this->items[$i]->tid;?>">
                        <?php echo $this->items[$i]->name;?>
                    </a>
                    <br />
                    <a style="font-size: 12px;" href="javascript:void(0);" class="get_results" script_path="<?php echo $this->path;?>" user_id="<?php echo $this->items[$i]->userid;?>" cert_id="<?php echo $this->items[$i]->tid;?>">
                        <?php echo JText::_('COM_FLEXPAPER_GET_RESULTS');?>
                    </a>
                </div>
                <div style="float: left; padding-left: 20px;" id="response_message"></div>
            </td>
        </tr>
    <?php }?>
</table>