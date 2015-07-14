<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="get">
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="ratings.listing" />
    <input type="hidden" name="boxchecked" value="" />
    <table class="adminlist">
        <tr>
            <th colspan="7" align="left">
                <?php echo JText::_("COM_BIDS_FILTER"); ?> <input type="text" name="searchfilter" value="<?php echo $this->search;?>" />
                <input type="submit" value="Search" />
                <input type="button" value="Reset" onclick="document.adminForm.searchfilter.value=''; document.adminForm.submit();" />
            </th>
        </tr>
        <tr>
            <th width="5" align="center"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->ratings ); ?>);" /></th>
            <th><?php echo JText::_("COM_BIDS_DATE");?></th>
            <th align="left"><?php echo JText::_("COM_BIDS_VOTE");?></th>
            <th align="left"><?php echo JText::_("COM_BIDS_FROM");?></th>
            <th align="left"><?php echo JText::_("COM_BIDS_TO");?></th>
            <th align="left"><?php echo JText::_("COM_BIDS_MESSAGE");?></th>
            <th align="left"><?php echo JText::_("COM_BIDS_AUCTION");?></th>
        </tr>
        <?php
        $odd=0;
        foreach ($this->ratings as $k => $review) {?>
        <tr class="row<?php echo ($odd=1-$odd);?>">
            <td align="center">
                <?php echo JHTML::_('grid.id', $k, $review->id );?>
            </td>
            <td><?php echo $review->modified;?></td>
            <td align="left"><?php echo $review->rating;?></td>
            <td align="left"><?php echo $review->username1;?></td>
            <td align="left"><?php echo $review->username2;?></td>
            <td align="left"><?php echo $review->review;?></td>
            <td align="left"><?php echo $review->title;?></td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="7" align="right">
                <?php echo $this->pagination->getListFooter();?>
            </th>
        </tr>
    </table>
</form>
