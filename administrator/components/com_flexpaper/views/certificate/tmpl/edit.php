<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_flexpaper&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="flexpaper-form" enctype="multipart/form-data">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_FLEXPAPER_USER_DETAILS');?></legend>
		<ul class="adminformlist">
            <li><?php echo JText::_('COM_FLEXPAPER_USERNAME');?>: <?php echo $this->item->name;?></li>
            <li><?php echo JText::_('COM_FLEXPAPER_EMAIL');?>: <a href="mailto:<?php echo $this->item->email;?>"><?php echo $this->item->email;?></a></li>
            <li><?php echo JText::_('COM_FLEXPAPER_DATE_CREATED');?>: <?php echo $this->item->registerDate;?></li>
		</ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_FLEXPAPER_CERTIFICATES');?></legend>

        <?php for($i=0;$i<count($this->item->certificates);$i++) {?>

        <div>
            <div style="float: left;"> <?php echo $this->item->certificates[$i]->title;?> </div>

            <?php if(isset($this->item->CreatedCertificates[$this->item->certificates[$i]->id]) && count($this->item->CreatedCertificates[$this->item->certificates[$i]->id])) {
               if(substr($this->item->CreatedCertificates[$this->item->certificates[$i]->id]->cert_id,0,2) == 'KS') {?>
                   <div style="float: left; padding-left: 30px; margin-top: -6px;"> <input type="button" value="<?php echo JText::_('COM_FLEXPAPER_SEND_KS_OLUSTUR');?>" class="get_certificate" cert_type="ks_certificate" user_id="<?php echo $this->item->id;?>" cert_id="<?php echo $this->item->CreatedCertificates[$this->item->certificates[$i]->id]->test_id;?>" script_path="<?php echo $this->path;?>" recipient_admin="0"> </div>
                <?php }
                elseif(substr($this->item->CreatedCertificates[$this->item->certificates[$i]->id]->cert_id,0,2) == 'BS') {?>
                <div style="float: left; padding-left: 30px; margin-top: -6px;"> <input type="button" value="<?php echo JText::_('COM_FLEXPAPER_SEND_BS_OLUSTUR');?>" class="get_certificate" cert_type="bs_certificate" user_id="<?php echo $this->item->id;?>" cert_id="<?php echo $this->item->CreatedCertificates[$this->item->certificates[$i]->id]->test_id;?>" script_path="<?php echo $this->path;?>" recipient_admin="0"> </div>
                <?php }?>
            <?php }?>

            <div id="response_message" style="float: left; padding-left: 30px; "></div>
        </div>
        <div style="clear: both;"></div>

        <?php }?>

    </fieldset>


    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_FLEXPAPER_CREATED_CERTIFICATES');?></legend>

        <?php for($i=0;$i<count($this->item->certificates);$i++) {?>
            <?php if(isset($this->item->CreatedCertificates[$this->item->certificates[$i]->id]) && count($this->item->CreatedCertificates[$this->item->certificates[$i]->id])) {?>
                <div>
                    <div style="padding-bottom: 10px;"><?php echo $this->item->certificates[$i]->title;?></div>
                    <div style="float: left;"><b><?php echo $this->item->CreatedCertificates[$this->item->certificates[$i]->id]->cert_id;?></b></div>
                    <div style="float: left; padding-left: 5px;">- <?php echo date("d.m.Y", strtotime($this->item->CreatedCertificates[$this->item->certificates[$i]->id]->date_created));?></div>
                    <div style="float: left; padding-left: 30px; margin-top: -6px;"> <input type="button" value="<?php echo JText::_('COM_FLEXPAPER_DELETE_CERTIFICATE');?>" class="delete_certificate" cert_type="ks_certificate" user_id="<?php echo $this->item->id;?>" cert_id="<?php echo $this->item->CreatedCertificates[$this->item->certificates[$i]->id]->tid;?>" script_path="<?php echo $this->path;?>"> </div>
                </div>
		<div style="clear: both;"></div>
            <?php }
            else {?>
                <div>
                    <div style="padding-bottom: 10px; float: left;"><?php echo $this->item->certificates[$i]->title;?></div>
                    <div style="float: left; padding-left: 30px; margin-top: -6px;"> <input type="button" value="<?php echo JText::_('COM_FLEXPAPER_CREATE_KS_CERTIFICATE');?>" class="create_certificate" passed="0" user_id="<?php echo $this->item->id;?>" course_id="<?php echo $this->item->certificates[$i]->id;?>" script_path="<?php echo $this->path;?>" testid="<?php echo @$this->item->CreatedCertificates[$this->item->certificates[$i]->id]->cert_id;?>"> </div>
                    <div style="float: left; padding-left: 30px; margin-top: -6px;"> <input type="button" value="<?php echo JText::_('COM_FLEXPAPER_CREATE_BS_CERTIFICATE');?>" class="create_certificate" passed="1" user_id="<?php echo $this->item->id;?>" course_id="<?php echo $this->item->certificates[$i]->id;?>" script_path="<?php echo $this->path;?>" testid="<?php echo @$this->item->CreatedCertificates[$this->item->certificates[$i]->id]->cert_id;?>"> </div>
                    <div style="clear: both; padding-bottom: 20px;"><b><?php echo JText::_('COM_FLEXPAPER_NO_CERTIFICATE_CREATED');?></b></div>

                </div>
		<div style="clear: both;"></div>
            <?php }?>

        <?php }?>
    </fieldset>

	<div>
		<input type="hidden" name="task" value="flexpaper.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
