<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="countries.listing" />
<input type="hidden" name="boxchecked" value="" />
<table class="adminlist">
<tr>
	<th colspan="4" align="right">
		<?php echo JText::_("COM_BIDS_FILTER"),':';?><input type="text" name="searchfilter" value="<?php echo $this->search;?>" />
		<?php echo JText::_("COM_BIDS_PUBLISHED"),':';?> 
		<?php echo $this->active_filter;?>
	</th>
</tr>
<tr>
	<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->countries ); ?>);" /></th>
	<th><?php echo JText::_("COM_BIDS_COUNTRY");?></th>
	<th align="center"><?php echo JText::_("COM_BIDS_COUNTRY_CODE");?></th>
	<th align="center" width="35"><?php echo JText::_("COM_BIDS_PUBLISHED");?></th>
</tr>
<?php 
$odd=0;
foreach ($this->countries as $k => $country) {?>
<tr class="row<?php echo ($odd=1-$odd);?>">
	<td align="center">
		<?php echo JHTML::_('grid.id', $k, $country->id );?>
	</td>
	<td><?php echo $country->name;?></td>
	<td align="center"><?php echo $country->simbol;?></td>
	<td align="center">
		<?php 
		$link 	= 'index.php?option=com_bids&task=countries.toggle&cid[]='. $country->id;
		if ( $country->active == 1 ) {
			$img = 'publish_g.png';
			$alt = JText::_( 'COM_BIDS_PUBLISHED_UNPUBLISH' );
		} else {
			$img = 'publish_x.png';
			$alt = JText::_( 'COM_BIDS_UNPUBLISHED_PUBLISH' );
		}
		?>
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Publish Information<br />'.$alt );?>">
			<a href="<?php echo $link;?>" >
            <?php echo JHtml::_('image.administrator','admin/'.$img,$alt);?>
		</a>
		</span>
	</td>
</tr>
<?php } ?>
<tr>
	<th colspan="4" align="right">
		<?php echo $this->pagination->getListFooter();?>
	</th>
</tr>
</table>
</form>
