<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="shipping.save" />
<input type="hidden" name="id" value="<?php echo $this->zone->id;?>" />
<table class="paramlist admintable">
	<tr>
		<td class="paramlist key">ID</td>
		<td><?php echo $this->zone->id;?></td>
	</tr>
	<tr>
		<td class="paramlist key"><?php echo JText::_('COM_BIDS_SHIPPING_ZONE');?>:</td>
		<td><input type="text" name="name" style="width:150px;" value="<?php echo $this->zone->name;?>" /> </td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="Save" />
            <input type="button" value="<?php echo JText::_('COM_BIDS_CANCEL'); ?>"
                   onclick="window.location='index.php?option=com_bids&task=shipping.listing&tmpl=component';"/>
		</td>
	</tr>
</table>
</form>
