<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" method="post" action="index.php">
    <div>
        Search after Ref # <input type="text" name="refno" value="<?php echo JRequest::getVar('refno', ''); ?>" />&nbsp;
        <input type="submit" value="Search" />
        <input type="button" value="Reset" onclick="document.adminForm.refno.value='';document.adminForm.submit();">
    </div>
    <table class="adminlist" width="100%">
        <tr>
            <th width="120"><?php echo JText::_('COM_BIDS_AUCTION_REF_NR'); ?></th>
            <th><?php echo JText::_('COM_BIDS_AUCTIONTITLE'); ?></th>
            <th><?php echo JText::_('COM_BIDS_BID'); ?></th>
            <th><?php echo JText::_('COM_BIDS_DATE'); ?></th>
            <th><?php echo JText::_('COM_BIDS_USERNAME'); ?></th>
            <th><?php echo JText::_('COM_BIDS_ACCEPTED'); ?></th>
        </tr>
<?php
        $k=1;
        foreach ($this->bids as $li => $item) {
?>
            <tr class="row<?php echo $k; ?>">
                <td align="center"><?php echo $item->auction_nr; ?></td>
                <td align="center"><a href="index.php?option=com_bids&task=editoffer&id=<?php echo $item->auction_id; ?>"><?php echo $item->offer; ?></a></td>
                <td align="center">
                    <?php
                        if($item->BIN_price>0 && $item->bid_price>=$item->BIN_price) {
                            $type = JText::_('COM_BIDS_TYPE_BIN');
                        } else if ($item->id_proxy) {
                            $type = JText::_('COM_BIDS_TYPE_PROXY');
                        } else {
                            $type = JText::_('COM_BIDS_TYPE_REGULAR');
                        }
                        echo JText::_('COM_BIDS_TYPE').': '.$type.'<br />';
                        echo JText::_('COM_BIDS_AMOUNT').': '.number_format($item->bid_price,2).' '.$item->currency.'<br />';
                        if(JText::_('COM_BIDS_TYPE_PROXY')==$type) {
                            echo JText::_('COM_BIDS_PROXY_MAX_VAL').': '.number_format((int)$item->max_proxy_price,2).' '.$item->currency.'<br />';
                        }
                    ?>
                </td>

                <td align="center"><?php echo $item->modified; ?></td>
                <td align="center"><?php echo $item->username; ?></td>
                <td align="center"><?php echo $item->accept ? JText::_('COM_BIDS_WINNER') : '-'; ?></td>
            </tr>
            <?php
            $k=1-$k;
        }
        ?>
    </table>
    <?php echo $this->pageNav->getListFooter(); ?>
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="bid_stats" />
</form>
