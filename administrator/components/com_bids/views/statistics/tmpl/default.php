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

$totals = $this->totals;
$userlist = $this->userlist;
$pageNav = $this->pageNav;

$database = JFactory::getDBO();
$my =  JFactory::getUser();
?>

<form action="index.php" method="post" name="adminForm">
    <input type="hidden" name="task" value="statistics">
    <input type="hidden" name="option" value="com_bids">


    <link rel="stylesheet" href="<?php echo JURI::root(); ?>/templates/joomla_admin/css/template_css.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo JURI::root(); ?>/templates/joomla_admin/css/theme.css" type="text/css" />


    <table width="100%">
        <tr>
            <td colspan="2">
                <table class="adminheading" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <th width="40%"><?php echo JText::_('COM_BIDS_USER_STATISTICS') ?></th>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table width="80%" >
        <tr>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_TOTAL_REGISTRED'); ?>&nbsp;<?php echo $totals['r_users']; ?>
            </td>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_A_AUCTIONS'); ?>&nbsp;<?php echo $totals['a_auctions']; ?>
            </td>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_A_BIDS'); ?>&nbsp;<?php echo $totals['a_bids']; ?>
            </td>
        </tr>
        <tr>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_ACTIVE_REGISTRED'); ?>&nbsp;<?php echo $totals['a_users']; ?>
            </td>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_C_AUCTIONS'); ?>&nbsp;<?php echo $totals['c_auctions']; ?>
            </td>
            <td class="stat_users_titles">
                <?php echo JText::_('COM_BIDS_USER_STAT_ACC_BIDS'); ?>&nbsp;<?php echo $totals['ac_bids']; ?>
            </td>
        </tr>
    </table>
    <br />

    <table width="100%">
        <tr>
            <td>
                <table class="adminheading"  cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <th><?php echo JText::_('COM_BIDS_AUCTION_STATS'); ?></th>
                    </tr>
                </table>
            </td>
        </tr>
        <table class="adminlist" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th width="42">#</th>
                    <th width="69" class="title"><?php echo JText::_('COM_BIDS_USERNAME'); ?></th>
                    <th width="69"><?php echo JText::_('COM_BIDS_REGISTER_DATE'); ?></th>
                    <th width="69"><?php echo JText::_('COM_BIDS_LASTVISIT'); ?></th>
                    <th width="69"><?php echo JText::_('COM_BIDS_AUCTIONS_PLACED'); ?></th>
                    <th width="69"><?php echo JText::_('COM_BIDS_NR_BIDS_PLACED'); ?></th>
                    <th width="73"><?php echo JText::_('COM_BIDS_NR_AUCTIONS_CLOSED'); ?></th>
                    <th width="80"><?php echo JText::_('COM_BIDS_NR_BIDS_WON') ?></th>
                </tr>
            </thead>
            <?php
            $i = 0;
            foreach ($userlist as $user) {

                $link = 'index.php?option=com_bids&task=detailuser&hidemainmenu=0&id=' . $user->id;

                $query = "select count(*) from #__bid_auctions where published=1 and close_offer != 1 and close_by_admin != 1 and userid =" . $user->id;
                $database->setQuery($query);
                $nr_p_auctions = $database->loadResult();

                $query = "select count(*) from #__bids where accept != 1 and cancel!=1 and userid=" . $user->id;
                $database->setQuery($query);
                $nr_p_bids = $database->loadResult();

                $query = "select count(*) from #__bid_auctions where close_offer = 1 and userid=" . $user->id;
                $database->setQuery($query);
                $nr_c_auctions = $database->loadResult();

                $query = "select count(*) from #__bids where accept =1 and userid=" . $user->id;
                $database->setQuery($query);
                $nr_w_bids = $database->loadResult();
                ?>
                <tr class="row<?php echo (int) ($i % 2); ?>">
                    <td align="center"><?php //echo $pageNav->rowNumber( $i );             ?></td>
                    <td><a href="<?php echo $link; ?>" title="Details"><?php echo $user->username; ?></a></td>
                    <td align="center"><?php echo $user->registerDate; ?></td>
                    <td align="center"><?php echo $user->lastvisitDate; ?></td>
                    <td align="center"><?php echo $nr_p_auctions; ?></td>
                    <td align="center"><?php echo $nr_p_bids; ?></td>
                    <td align="center"><?php echo $nr_c_auctions; ?></td>
                    <td align="center"><?php echo $nr_w_bids; ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
            <tr>
                <td colspan="8">
                    <del class="container">
<?php echo $pageNav->getListFooter(); ?>
                    </del>
                </td>
            </tr>

        </table>
</form>
