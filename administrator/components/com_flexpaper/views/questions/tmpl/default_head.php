<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5">
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_JUSTID', 'a.id', $this->sortDirection, $this->sortColumn); ?>
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th>
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_JUSTQUESTIONNAME', 'a.question', $this->sortDirection, $this->sortColumn); ?>
	</th>
	<th>
        <?php echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_TEST', 'b.name', $this->sortDirection, $this->sortColumn); ?>
	</th>	
<!--    <th>-->
<!--        --><?php //echo JHTML::_( 'grid.sort', 'COM_FLEXPAPER_HEADING_CATEGORY', 'b.title', $this->sortDirection, $this->sortColumn); ?>
<!--    </th>-->
</tr>