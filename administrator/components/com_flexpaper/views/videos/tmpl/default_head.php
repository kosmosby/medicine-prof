<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5">
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_JUSTID', 'a.id', $this->sortDirection, $this->sortColumn); ?>
	</th>
	<th width="5">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th>
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_VIDEONAME', 'a.video_file', $this->sortDirection, $this->sortColumn); ?>
	</th>
    <th>
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_COURSENAME', 'b.title', $this->sortDirection, $this->sortColumn); ?>
    </th>
</tr>