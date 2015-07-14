<?php
/**
 * @version 3.0
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

defined('_JEXEC') or die('Restricted access');
JToolBarHelper::title(JText::_('awdjomalbum'), 'awdwallprogalleryextrafield');
JToolBarHelper::publish('publish','Publish info',true);
JToolBarHelper::unpublish('unpublish','Unpublish info',true);
JToolBarHelper::preferences('com_awdjomalbum');
AwdjomalbumHelper::awdadmintoolbar('info');
?>
<div class="awdm">
<form name="adminForm" id="adminForm" action="" method="post" >
	
	<table class="table table-striped">
		<thead>
			<tr>
				<th style="text-align:left" width="5%" class="nowrap">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th style="text-align:left" width="5%" class="nowrap">
					<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count($this->rows); ?>);" />
				</th>
				<th style="text-align:left"  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Fields' );  ?>
				</th>
				<th style="text-align:left"  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Value' );  ?>
				</th>
				<th  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Action' );  ?>
				</th>
				
			</tr>
		</thead>
		<tbody>
		<?php
			$k = 0;
			$db				=& JFactory::getDBO();
			for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
			{
				$row	=& $this->rows[$i];
				$link='index.php?option=com_awdjomalbum&view=info&layout=editform&cid[]='.$row->id;
				$db		=& JFactory::getDBO();
?>
				<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo  $j+$i+1;?> 
				</td>
				
				<td>
					<?php echo JHTML::_('grid.id', $i, $row->id ); ?>
				</td>
				<td>
					<a href="<?php echo $link;?>" ><?php echo "Field".($i+1);?></a>
				</td>
				<td>
					<a href="<?php echo $link;?>" ><?php echo $row->value;?></a>
				</td>
				<td align="center">
                
					<?php echo JHtml::_('jgrid.published', $row->published, $i);?>
					
					
				</td>
															
			</tr>
			<?php
				$k = 1 - $k;
				}
			?>
	  </tbody>
	</table>
	<input type="hidden" name="option" value="com_awdjomalbum" />
		<input type="hidden" name="view" value="info" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $row->id;?>" />
    <input type="hidden" name="boxchecked" value="0" />
</form>
</div>
