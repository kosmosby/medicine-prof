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
JToolBarHelper::title(JText::_('awdjomalbum'), 'awdwallprogalleryalbumphoto');
JToolBarHelper::deleteList('Delete','deletecomment','Delete'); // Will call the task/function "remove" in your controller
AwdjomalbumHelper::awdadmintoolbar('albumphotos');

?>
<div class="awdm">
<form name="adminForm" id="adminForm" action="" method="post">
	<table class="table table-striped">
		
		<thead>
			<tr>
				<th style="text-align:left" width="5%" class="nowrap">
					<?php echo JText::_( 'NUM' ); ?>				</th>
				<th style="text-align:left" width="5%" class="nowrap">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->commentrows); ?>);" />				</th>
				<th style="text-align:left"  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Comments' );  ?>				</th>
			
				<th  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Name' );  ?>				</th>
				<th  class="nowrap" nowrap="nowrap">
					<?php echo JText::_( 'Delete' );  ?>				</th>
			</tr>
		</thead>
		
		<tbody>
		<?php
			$k = 0;
			$db				=& JFactory::getDBO();
			for ($i=0, $n=count($this->commentrows ); $i < $n; $i++)
			{
				$row 	= $this->commentrows[$i];
			
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo  $j+$i+1;?>				</td>
				
				<td>
					<?php echo JHTML::_('grid.id', $i, $row->id ); ?>				</td>
				
				<td>
					
						<?php echo stripcslashes(nl2br($row->comments)); ?></td>
				<?php
				$sql3="select username from   #__users where  id=".$row->userid ;
				//echo $sql3;
				$db->setQuery( $sql3 );
				$username = $db->loadResult();
				//echo $username;
				?>
				<td align="center">
					<?php echo $username;?>
				</td>
				<td align="center">
				<?php
					echo '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\'deletecomment\')" title="Delete Comment"><img src="components/com_awdjomalbum/images/delete.png"  border="0"></a>';		?>	
				</td>
			</tr>
			<?php
				$k = 1 - $k;
				}
			?>
		</tbody>
		<tbody>
			<tr>
			  <td width="100%" colspan="12"><?php //echo $this->pageNav->getListFooter('pages'); ?> </td>
			</tr>
      </tbody>
	</table>
	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="view" value="albumphotos" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="commentid" value="<?php echo $row->id;?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
</div>
