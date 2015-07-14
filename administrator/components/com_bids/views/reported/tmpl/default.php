<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="reported.listing" />
<input type="hidden" name="boxchecked" value="" />
<table class="adminlist">
<tr>
	<th colspan="6" align="right">
		<?php echo JText::_("COM_BIDS_PUBLISHED"),':';?> 
		<?php echo $this->active_filter;?>
	</th>
</tr>
<tr>
    <th width="5" align="center">
        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->reported); ?>);" />
    </th>
    <th width="10%" class="title" align="left"><?php echo JText::_('COM_BIDS_REPORTED_DATE'); ?></th>
    <th width="10%" align="left"><?php echo JText::_("COM_BIDS_REPORTED_BY"); ?></th>
    <th width="25%" align="left"><?php echo JText::_('COM_BIDS_MESSAGE'); ?></th>
    <th width="*%" align="left"><?php echo JText::_('COM_BIDS_AUCTION'); ?></th>
    <th width="5%" align="center"><?php echo JText::_('COM_BIDS_STATUS'); ?></th>
</tr>
<?php 
$odd=0;
foreach ($this->reported as $k => $reported) {
    $message = (strlen($reported->message) > 30) ? substr($reported->message, 0, 30) . ".." : $reported->message;
    $title = (strlen($reported->title) > 30) ? substr($reported->title, 0, 30) . ".." : $reported->title;
    
?>
<tr class="row<?php echo ($odd=1-$odd);?>">
	<td align="center" >
		<?php echo JHTML::_('grid.id', $k, $reported->id );?>
	</td>
	<td align="left"><?php echo $reported->modified;?></td>
	<td align="left"><?php echo $reported->username;?></td>
    <td align="left" title="<?php echo htmlentities($reported->message); ?>" class="hasTip">
        <a href="index.php?option=com_bids&task=write_admin_message&auction_id=<?php echo $reported->auction_id; ?>">
            <?php echo $message; ?>
        </a>
    </td>
    
	<td align="left">
        <a href="index.php?option=com_bids&task=editoffer&id=<?php echo $reported->auction_id; ?>">
        <?php echo $title;?>
        </a>
    </td>
	<td align="center">
		<?php 
		$link 	= 'index.php?option=com_bids&task=reported.toggle&cid[]='. $reported->id;
		if ( $reported->solved == 1 ) {
			$img = 'publish_g.png';
			$alt = JText::_( 'COM_BIDS_SOLVED' );
		} else {
			$img = 'publish_x.png';
			$alt = JText::_( 'COM_BIDS_UNSOLVED' );
		}
		?>
		<span class="editlinktip hasTip" title="<?php echo JText::_( $alt );?>">
			<a href="<?php echo $link;?>" >
            <?php echo JHtml::_('image.administrator','admin/'.$img,$alt);?>
		</a>
		</span>
	</td>
</tr>
<?php } ?>
<tr>
	<th colspan="6" align="right">
		<?php echo $this->pagination->getListFooter();?>
	</th>
</tr>
</table>
</form>
