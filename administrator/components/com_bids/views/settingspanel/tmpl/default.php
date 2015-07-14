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
?>
<div id = "cpanel">
    <div style="float:left; width: 435px;">
        <div>
            <?php
            $link = 'index.php?option=com_bids&amp;task=config.display';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'settings.png', JText::_('COM_BIDS_GENERAL_SETTINGS'));

            $link = 'index.php?option=com_categories&amp;extension='.APP_EXTENSION;
            echo JHTML::_('bidsettings.quickIconButton', $link, 'categories.png', "Categories");

            $link = 'index.php?option=com_bids&amp;task=fields.listfields';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'fieds_manager.png', 'Custom fields');

            ?>
        </div>
        <br clear="all" />
        <div>
            <?php
            $link = 'index.php?option=com_bids&amp;task=integration';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'cb_integration.png', "Profile <br /> Integration");

            ?>
            <div style="width:125px; height: 100px; float: left;">&nbsp;</div>
            <?php

            $link = 'index.php?option=com_bids&amp;task=themes.listthemes';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'themes.png', JText::_('COM_BIDS_THEMES_MANAGER'));
            ?>
        </div>
        <br clear="all"/>
        <div>
            <?php
            $link = 'index.php?option=com_bids&amp;task=currencies.listing';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'currency_manager.png', JText::_('COM_BIDS_CURRENCY_MANAGER'));

            $link = 'index.php?option=com_bids&amp;task=pricing.listing';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'paymentitems.png', JText::_('COM_BIDS_PAYMENT_ITEMS'));

            $link = 'index.php?option=com_bids&amp;task=gateways.listing';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'paymentconfig.png', JText::_('COM_BIDS_PAYMENT_GATEWAYS'));

            ?>
        </div>
        <br clear="all" />
        <div>
            <?php
            $link = 'index.php?option=com_bids&amp;task=mailman.mails';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'mailsettings.png', JText::_('COM_BIDS_MAIL_SETTINGS'));

            $link = 'index.php?option=com_bids&amp;task=countries.listing';
            echo JHTML::_('bidsettings.quickIconButton', $link, 'country_manager.png', JText::_('COM_BIDS_COUNTRY_MANAGER') );

            ?>
        </div>
        <br clear="all" />
    </div>
    <div style = "float:left;width: 500px;">
        <div class = 'width-100 fltlft'>
            <fieldset class = "adminform">
                <legend><?php echo JText::_('COM_BIDS_VERSION_INFORMATION'); ?></legend>
                <table class = "paramlist admintable">
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_YOUR_INSTALLED_VERSION");?></td>
                        <td><?php echo COMPONENT_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_ACTIVE_PROFILE_INTEGRATION");?></td>
                        <td><?php
                            switch ($this->cfg->profile_mode) {
                                case 'component':
                                    echo JText::_('COM_BIDS_COMPONENT_PROFILE');
                                    break;
                                case 'cb':
                                    echo JText::_('COM_BIDS_COMMUNITY_BUILDER');
                                    break;
                                case 'love':
                                    echo JText::_('COM_BIDS_LOVE_FACTORY');
                                    break;
                            }
                            ?></td>
                    </tr>
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_CURRENT_ACTIVE_THEME");?></td>
                        <td><?php echo $this->cfg->theme; ?></td>
                    </tr>
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_CURRENT_ENABLED_GATEWAYS");?></td>
                        <td>
                            <ul>
                                <?php
                                foreach ($this->gateways as $gateway) {
                                    echo "<li><strong>", $gateway->paysystem, "</strong></li>";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_CURRENT_ENABLED_PAYMENT_ITEMS");?></td>
                        <td>
                            <ul>
                                <?php
                                foreach ($this->items as $item) {
                                    echo "<li><strong>", $item->itemname, "</strong></li>";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <div class = 'width-100 fltlft'>
            <fieldset class = "adminform">
                <legend><?php echo JText::_('COM_BIDS_CRON_INFORMATION'); ?></legend>
                <table class = "paramlist admintable">
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_LATEST_CRON_TASK_RUN");?></td>
                        <td><?php echo intval($this->latest_cron_time) ? JHtml::date( $this->latest_cron_time, $this->cfg->bid_opt_date_format.' '.$this->cfg->bid_opt_date_time_format) : $this->latest_cron_time; ?></td>
                    </tr>
                    <tr>
                        <td class = "paramlist_key" width = "190"><?php echo JText::_("COM_BIDS_READ_MORE_ABOUT_CRON_JOB_SETUP");?></td>
                        <td><a href = "index.php?option=com_bids&task=cronjob_info"><?php echo JText::_("COM_BIDS_CLICK_HERE");?></a></td>
                    </tr>
                </table>
            </fieldset>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>