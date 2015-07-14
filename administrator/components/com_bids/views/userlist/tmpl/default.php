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
$page = $this->page;
$rows = $this->rows;

?>

<form action="index.php?option=com_bids&task=users" method="post" name="adminForm">
    <?php echo JText::_('COM_BIDS_FILTER'); ?>:
    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($lists['search']); ?>" class="text_area" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_BIDS_FILTER_BY_TITLE_OR_ENTER_ARTICLE_ID'); ?>"/>
    <button onclick="this.form.submit();"><?php echo JText::_('COM_BIDS_GO'); ?></button>
    <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_BIDS_RESET'); ?></button>

    <table width="100%">
        <tr>
            <td colspan="2">
                <table class="adminlist" cellpadding="0" cellspacing="0" border="0">
                    <thead>
                        <tr>
                            <th width="7">#</th>
                            <th width="15" style="text-align: center;">
                                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
                            </th>
                            <th width="69" class="title" style="text-align: left;">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_USERNAME'), 'username', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="69" style="text-align: left;">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_NAME'), 'name', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="69" style="text-align: left;">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_EMAIL'), 'email', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="69"><?php echo JText::_('COM_BIDS_STATUS'); ?></th>
                            <th width="73">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_AUCTIONS_PLACED'), 'nr_auctions', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="73">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_CLOSED_BIDS'), 'nr_closed_bids', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="80">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_CLOSED_OFFER'), 'nr_closed_offers', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="80">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_OPENED_OFFERS'), 'nr_open_offers', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="80">
                                <?php echo JHTML::_('grid.sort', "Featured auctions", 'nr_featured_offers', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                            <th width="41">
                                <?php echo JHTML::_('grid.sort', JText::_('COM_BIDS_RATING'), 'rating_user', @$lists['order_Dir'], @$lists['order']); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $k = 0;
                        for ($i = 0, $n = count($rows); $i < $n; $i++) {
                            $row = $rows[$i];
                            $link = 'index.php?option=com_bids&task=detailuser&hidemainmenu=0&id=' . $row->userid1;
                            $img = ($row->block == 1) ? 'user_blocked.png' : 'user_available.png';
                            $task1 = ($row->block == 1) ? 'unblockuser' : 'blockuser';
                            $alt = ($row->block == 1) ? JText::_('COM_BIDS_USER_BLOCKED') : JText::_('COM_BIDS_ACTIVE');

                            if($this->cfg->bid_opt_enable_acl) {
                                $img_seller = (isset($row->isSeller) && $row->isSeller) ? "f_can_sell1.gif" : "f_can_sell2.gif";
                                $img_bidder = (isset($row->isBidder) && $row->isBidder) ? "f_can_buy1.gif" : "f_can_buy2.gif";
                            }
                            $img_verified = (isset($row->verified) && $row->verified) ? "verified_1.gif" : "verified_0.gif";
                            $img_powerseller = (isset($row->powerseller) && $row->powerseller) ? "powerseller1.png" : "powerseller0.png";
                            ?>
                            <tr class="<?php echo 'row', $k; ?>">
                                <td style="text-align: center;"><?php echo $page->getRowOffset($i); ?></td>
                                <td style="text-align: center;"><?php echo JHTML::_('grid.id', $i, $row->userid1); ?></td>
                                <td>
    
                                        <a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_BIDS_EDIT'); ?> "><?php echo $row->username; ?></a>

                                </td>
                                <td><?php echo $row->name; ?></td>
                                <td><?php echo $row->email; ?></td>
                                <td width="73" align="center">
    <?php if ($row->profid) { ?>
                                        <a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $task1; ?>')">
                                            <img src="<?php echo JURI::root() . "components/".APP_EXTENSION."/images/user/" . $img; ?>" height="16" border="0" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>" class="hasTip"/>
                                        </a>
    <?php if($this->cfg->bid_opt_enable_acl) { ?>
                                        <img src="<?php echo JURI::root() . "components/".APP_EXTENSION."/images/user/" . $img_seller; ?>" height="16" border="0" title="<?php echo JText::_('COM_BIDS_ACL_GROUP_SELLER'), ": ", ( (isset($row->isSeller) && $row->isSeller) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO') ); ?>" class="hasTip" />
                                        <img src="<?php echo JURI::root() . "components/".APP_EXTENSION."/images/user/" . $img_bidder; ?>" height="16" border="0" title="<?php echo JText::_('COM_BIDS_ACL_GROUP_BIDDER'), ": ", ( (isset($row->isBidder) && $row->isBidder) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO') ); ?>" class="hasTip" />
    <?php } ?>
                                        <a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','toggleverify')">
                                            <img src="<?php echo JURI::root() . "components/".APP_EXTENSION."/images/user/" . $img_verified; ?>" height="16" border="0" title="<?php echo JText::_('COM_BIDS_USER_VERIFIED'), ": ", ( (isset($row->verified) && $row->verified) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO') ); ?>" class="hasTip" />
                                        </a>
                                        <a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','togglepowerseller')">
                                            <img src="<?php echo JURI::root() . "components/".APP_EXTENSION."/images/user/" . $img_powerseller; ?>" height="16" border="0" title="<?php echo "Powerseller: ", ( (isset($row->powerseller) && $row->powerseller) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO') ); ?>" class="hasTip" />
                                        </a>
    <?php } else {
                                        echo JText::_('COM_BIDS_PROFILE_UNFILLED');
                                     } ?>
                                </td>
                                <td align="center"><?php echo JHTML::link( 'index.php?option=com_bids&task=offers&filter_authorid='.$row->userid1, $row->nr_auctions); ?></td>
                                <td align="center"><?php echo $row->nr_closed_bids; ?></td>
                                <td align="center"><?php echo $row->nr_closed_offers; ?></td>
                                <td align="center"><?php echo $row->nr_open_offers; ?></td>
                                <td align="center"><?php echo $row->nr_featured_offers; ?></td>
                                <td align="center"><?php echo ($row->rating_user > 0) ? $row->rating_user : "-"; ?></td>
                            </tr>
                        </tbody>
    <?php
    $k = 1 - $k;
}
?>
                        <tfoot>
                        <tr>
                            <td colspan="12">
                                <?php echo $page->getListFooter(); ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>

    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="users" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="hidemainmenu" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
</form>
