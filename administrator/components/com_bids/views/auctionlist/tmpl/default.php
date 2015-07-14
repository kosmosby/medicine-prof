<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

$lists = $this->lists;
$filters = $this->filters;
$rows = $this->rows;
$pageNav = $this->pageNav;

JHTML::_('behavior.modal');
?>
<script type="text/javascript">
    function submitbutton(task)
    {
        if(task=='purgeauctions'){
            if (!confirm('<?php echo JText::_('COM_BIDS_CONFIRM_PURGE'); ?>')) return;
        }
        submitform(task);
    }
    function resetSearch() {
        document.adminForm.keyword.value = '';
        document.adminForm.category.selectedIndex = 0;
        document.adminForm.filter_authorid.value = '';
        document.adminForm.submit();
    }
</script>

<form action="index.php" method="get" name="adminForm">

    <table width="100%" class="adminlist">
        <tr>
            <td>
                <table  cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="100%">
                            <?php
                                echo JText::_('COM_BIDS_KEYWORD') . ' ' . $filters['keyword'] .
                                ' ' . JText::_('COM_BIDS_IN_CATEGORY') . ' ' . $filters['category'].
                                ' ' . JText::_('COM_BIDS_BY_USER') . ' ' . $filters['filter_authorid'];
                            ?>
                            <input type="submit" value="Search">&nbsp;<input type="button" value="Reset" onclick="resetSearch();" />
                        </td>
                        <td>
                            <?php echo $lists['filter_bidtype']; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table class="adminlist">
                    <thead>
                        <tr>
                            <th align="center" class="title" style="width: 120px !important;">
                                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
                                <?php echo JHTML::_('grid.sort', JText::_("COM_BIDS_REF_NUMBER"), 'a.auction_nr', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th align="center" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_("COM_BIDS_FEATURED"), 'a.featured', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th align="center" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_("COM_BIDS_MESSAGES"), 'a.newmessages', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th align="center" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_TITLE'), 'a.title', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_USERNAME'), 'username', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th align="center" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_("COM_BIDS_NR_IMAGES"), 'nr_pix', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_START_DATE'), 'start_date', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_INITIAL_PRICE'), 'initial_price', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_("COM_BIDS_BIN"), 'BIN_price', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="8%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_HIGHEST_BID'), 'min_bid', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_BIDDERS'), 'nr_bidders', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_END_DATE'), 'end_date', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="5%" class="title"><?php echo JText::_('COM_BIDS_AUCTION_STATUS'); ?></th>

                        </tr>
                    </thead>
                    <?php
                    $k = 0;
                    for ($i = 0, $n = count($rows); $i < $n; $i++) {

                        $row = $rows[$i];

                        $link = 'index.php?option=com_bids&task=editoffer&id=' . $row->id;

                        $img = JURI::root() . "administrator/images/" . ($row->close_offer ? 'publish_g.png' : 'publish_x.png');
                        $alt = $row->close_offer ? 'Closed' : 'Opened';

                        $img_admin = JURI::root() . "administrator/images/" . ($row->close_by_admin ? 'publish_g.png' : 'publish_x.png');
                        $task_admin = $row->close_by_admin ? 'opened' : 'closed';
                        $alt_admin = $row->close_by_admin ? 'Close' : 'Open';

                        $msg = $row->close_by_admin ? JText::_('COM_BIDS_CONFIRM_OPEN_AUCTION') : JText::_('COM_BIDS_CONFIRM_CLOSE_AUCTION');

                        $row->checked_out = null;
                        $checked = JHTML::_('grid.checkedout', $row, $i);

                        $max_bid = ($row->min_bid > 0) ? $row->min_bid : "0";
                        $nr_bids = $row->nr_bidders;

                        $class = "row$k";

                        if ($row->close_by_admin) {
                            $class = "row-canceled";
                        } elseif ($row->close_offer) {
                            $class = "row-closed";
                        } elseif (!$row->published) {
                            $class = "row-unpublished";
                        }

                        $img_featured = "";
                        if ($row->featured && $row->featured != 'none') {
                            $class.=" row-" . $row->featured;
                        }
                        ?>
                        <tr class="<?php echo $class; ?>">
                            <td>
                                <?php echo $checked . '&nbsp;' . $row->auction_nr; ?>
                            </td>
                            <td align="center">
                                <?php
                                if ($row->featured && $row->featured != 'none') {
                                    ?>
                                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/<?php echo $row->featured; ?>.jpg" title="<?php echo $row->featured; ?>" height="20" />
                                    <?php
                                    echo ucfirst($row->featured);
                                }else
                                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                                ?>
                            </td>
                            <td align="center">
                                <?php
                                if ($row->newmessages == 1) {
                                    ?>
                                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/f_message_1.png" border="0" title="<?php echo JText::_('COM_BIDS_NEWMESSAGES'); ?>" width="20" />
                                <?php } else { ?>
                                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/f_message_0.png" border="0" title="<?php echo JText::_('COM_BIDS_NONEWMESSAGES'); ?>" width="20" />
                                <?php } ?>
                            </td>
                            <td>
                                <a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_BIDS_EDIT'); ?>"><?php echo $row->title; ?></a><br />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo JText::_('COM_BIDS_CATEGORY'); ?>: <?php echo $row->name; ?>
                            </td>
                            <td align="center">
                                <?php echo '<a href="index.php?option=com_bids&task=detailuser&hidemainmenu=0&id=' . $row->userid . '">' . $row->username . '</a>'; ?>
                            </td>
                            <td align="center">
                                <?php echo ($row->nr_pix > 0) ? $row->nr_pix : '-'; ?>
                            </td>
                            <td align="center">
                                <?php echo BidsHelperAuction::formatDate($row->start_date); ?>
                            </td>
                            <td align="center">
                                <?php echo $row->initial_price == 0 ? ' - ' : (BidsHelperAuction::formatPrice($row->initial_price) . " " . $row->currency); ?>
                            </td>
                            <td align="center">
                                <?php echo $row->BIN_price > 0 ? (BidsHelperAuction::formatPrice($row->BIN_price) . " " . $row->currency):'-'; ?>
                            </td>
                            <td align="center">
                                <?php if ($max_bid) echo number_format($max_bid, 2) . " " . $row->currency; ?>
                            </td>
                            <td align="center">
                                <?php echo JHTML::link('index.php?option='.APP_EXTENSION.'&task=bidshistory&tmpl=component&id='.$row->id,$nr_bids,'class="modal" rel="{handler: \'iframe\'}"'); ?>
                            </td>

                            <td align="center">
                                <?php echo BidsHelperAuction::formatDate($row->end_date); ?>
                            </td>

                            <td align="center">
                                <?php
                                if ($row->closed_date != '0000-00-00 00:00:00' && $row->close_by_admin != 1 && $row->close_offer == 1)
                                    echo JText::_('COM_BIDS_CLOSED_DATE') . ':' . BidsHelperAuction::formatDate($row->closed_date);
                                elseif ($row->close_by_admin == 1)
                                    echo JText::_('COM_BIDS_CLOSED_BY_ADMIN') . " " . BidsHelperAuction::formatDate($row->closed_date);
                                elseif ($row->published == 0)
                                    echo JText::_('COM_BIDS_UNPUBLISHED');
                                elseif ($row->start_date > gmdate('Y-m-d H:i:s'))
                                    echo JText::_('COM_BIDS_FUTURE');
                                elseif ($row->end_date < gmdate('Y-m-d H:i:s'))
                                    echo JText::_('COM_BIDS_EXPIRED');
                                else
                                    echo JText::_('COM_BIDS_ACTIVE');
                                ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </table>
            </td>
        </tr>
        <tfoot>
            <tr>
                <td>
                    <del class="container">
                        <?php echo $pageNav->getListFooter(); ?>
                    </del>
                </td>
            </tr>
        </tfoot>
        <table cellspacing="0" cellpadding="4" border="0" align="center">
            <tr align="center">
                <td><?php echo JText::_('COM_BIDS_BIN_EXPLAIN'); ?></td>
                <td>
                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/f_message_1.png" border="0" title="<?php echo JText::_('COM_BIDS_NEWMESSAGES'); ?>" width="20" /> Has unread messages ,
                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/f_message_0.png" border="0" title="<?php echo JText::_('COM_BIDS_NEWMESSAGES'); ?>" width="20" /> No unread messages
                </td>
                <td>
                    <img src="<?php echo JURI::root(); ?>components/com_bids/images/featured.jpg" title="Gold" height="20" /> <?php echo JText::_('COM_BIDS_FEATURED'); ?>
                </td>
            </tr>
        </table>

        <input type="hidden" name="option" value="com_bids" />
        <input type="hidden" name="task" value="offers" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
</form>
</table>
