<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5">
		Id
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th>
        <?php echo JText::_('COM_FLEXPAPER_QUIZ_COURSE_NAME');?>
	</th>
    <th>
        <?php echo JText::_('COM_FLEXPAPER_QUIZ_TEST_NAME');?>
    </th>
</tr>