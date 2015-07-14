<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5">
        <?php echo JText::_('COM_FLEXPAPER_HEADING_JUSTID');?>
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th>
        <?php echo JText::_('COM_FLEXPAPER_HEADING_JUSTNAME');?>
	</th>
	<th>
        <?php echo JText::_('COM_FLEXPAPER_HEADING_CATEGORY');?>
    </th>
</tr>