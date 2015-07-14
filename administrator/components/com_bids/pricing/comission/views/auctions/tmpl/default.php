<?php defined('_JEXEC') or die('Restricted access'); ?>
<div style="float: left;">
    <form name="adminForm" action="index.php" method="get">
        <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
        <input type="hidden" name="task" value="pricing.auctions" />
        <input type="hidden" name="item" value="comission" />
        <input type="hidden" name="boxchecked" value="" />
        <input type="hidden" name="commissionType" value="<?php echo JRequest::getVar('commissionType'); ?>" />
        <table class="adminlist" >
        <thead>
        <tr>
        	<th width="50"><?php echo JText::_("COM_BIDS_REF_NUMBER");?></th>
            <th width="*%"><?php echo JText::_("COM_BIDS_AUCTION_TITLE");?></th>
            <th width="80"><?php echo JText::_("COM_BIDS_WINNING_BID");?></th>
            <th width="150"><?php echo JText::_("COM_BIDS_DATE");?></th>
            <th width="80"><?php echo JText::_("COM_BIDS_COMMISSION");?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $odd=0;
        foreach ($this->auctions as $auctions) {?>
            <tr class="row<?php echo ($odd=1-$odd);?>">
                <td><?php echo $auctions->auction_nr;?></td>
                <td>
                    <a href="index.php?option=com_bids&task=editoffer&cid[]=<?php echo $auctions->auction_id;?>">
                    <?php echo $auctions->title;?>
                    </a>
                </td>
                <td style="text-align: right;"><?php echo number_format($auctions->bid_price,2)," ",$auctions->currency;?></td>
                <td><?php echo $auctions->comission_date;?></td>
                <td style="text-align: right;"><?php echo $auctions->amount," ",$auctions->currency;?></td>
            </tr>
        <?php } ?>
        </tbody>
            <tfoot>
                <tr>
                    <td colspan="15">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        
        </table>
    </form>
</div>
