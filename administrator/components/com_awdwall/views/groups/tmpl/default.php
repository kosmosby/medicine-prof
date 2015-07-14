<?php
/**
 * @version 2.1
 * @package JomwallPRO-Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
Awdwalladminhelper::awdadmintoolbar('groups');
?>
<?php $path= substr(JURI::base(),0,-14).'joomla.php';?>
<div class="awdm">
<form name="adminForm" id="adminForm" action="" method="post">
	<table >
			<tr>
				<td width="100%">  <?php echo JText::_( 'Filter' ); ?>:
					<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onChange="document.adminForm.submit();" />
					<button onClick="this.form.submit();" class="btn"><?php echo JText::_( 'Go' ); ?></button>
					<button onClick="document.getElementById('search').value='';this.form.getElementById('filter_type').value='0';this.form.getElementById('filter_logged').value='0';this.form.submit();" class="btn"><?php echo JText::_( 'Reset' ); ?></button>
			  </td>
				<td nowrap="nowrap">
					<?php echo $this->lists['creatorlist'];?>
				</td>
			</tr>
	</table>
	<table class="table table-striped" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th style="text-align:left" width="5%" class="title">
					<?php echo JText::_( 'NUM' ); ?>				</th>
				<th style="text-align:left" width="5%" class="title">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->groups); ?>);" />				</th>
				<th style="text-align:left"  class="nowrap center" nowrap="nowrap">
					<?php echo JText::_( 'Group Name (Total Members)' );  ?>				</th>
				<th style="text-align:left"  class="nowrap center" nowrap="nowrap">
					<?php echo JText::_( 'Creator' );  ?>				</th>
				
				<th style="text-align:left" class="nowrap center" nowrap="nowrap">
					<?php echo JText::_( 'Privacy' );  ?>				</th>
			</tr>
		</thead>
		
		<tbody>
		<?php
			$k = 0;
			$app = &JFactory::getApplication();
			$db				=& JFactory::getDBO();
			for ($i=0, $n=count( $this->groups ); $i < $n; $i++)
			{
				$row 	= $this->groups[$i];
				$user = JUser::getInstance( $row->creator );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo  $j+$i+1;?>				</td>
				
				<td>
					<?php echo JHTML::_('grid.id', $i, $row->id ); ?>				</td>
				
				<td>
					<?php 
					$sql='select count(*) from #__awd_groups_members where group_id ='.$row->id;
					$db->setQuery($sql);
					$totalmem=$db->loadResult();
					$totalmem=$totalmem+1;
					echo stripcslashes($row->title ).' ('.$totalmem.')'; 
					?>				</td>
						
				<td>
					<?php echo $user->username; ?>
				</td>		
				<td>
					<?php if($row->privacy ==1)echo 'Public'; else echo 'Private'; ?>
				</td>		
			</tr>
			<?php
				$k = 1 - $k;
				}
			?>
		</tbody>
		<tbody>
        <tr>
          <td width="100%" colspan="12"><?php //echo $this->pageNav->getListFooter('pages'); ?> </td></tr>
      </tbody>
      <tbody>
	</table>
	<input type="hidden" name="option" value="com_awdwall" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="groups" />
	<input type="hidden" name="operation" value="" />
	<input type="hidden" name="id" value="<?php echo $row->id;?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
</div>