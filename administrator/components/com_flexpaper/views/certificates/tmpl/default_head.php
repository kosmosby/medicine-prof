<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
    <th width="100">
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_ID', 'a.id', $this->sortDirection, $this->sortColumn); ?>
    </th>
	<th align="left" style="text-align: left; padding-left: 20px;">
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_NAME', 'a.name', $this->sortDirection, $this->sortColumn); ?>
	</th>
</tr>