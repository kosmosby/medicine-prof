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
<table width="100%">
    <tr>
        <td valign="top" width="325px;">
            <div id="cpanel">
                <?php
                $link = 'index.php?option=com_bids&amp;task=showadmimportform';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'importauctions.png', JText::_('COM_BIDS_IMPORT_AUCTIONS'));

                $link = 'index.php?option=com_bids&amp;task=exportToXls';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'xlsexport.png', JText::_('COM_BIDS_EXPORTTOXLS'));
                ?>
            </div>
        </td>
        <td></td>

        <td valign="top" align="center" width="450px; !important" rowspan="4">
            <table class="adminform">
                <tr>
                    <td style="text-align: left; font-weight: bold;">
                        <div style="float: left; width: 250px;;">
                            Check please our <a
                                href="http://wiki.thefactory.ro/doku.php#auction_factory">Documentation</a> and our <a
                                href="http://www.thefactory.ro/joomla-forum/">Forum</a> which is an important knowledgebase.
                        </div>
                        <div style="float: right;">
                            <a href="http://www.thefactory.ro/shop/joomla-components/auction-factory.html"><img
                                    src="http://www.thefactory.ro/images/vm/auction_j25.jpg" alt=""/></a>
                        </div>
                        <div style="clear: both;">&nbsp;</div>
                        <div>
                            For support issues use please our <a
                                href="http://www.thefactory.ro/contact-us/customer-support.html">ticket system</a>.
                            Please follow the <a
                                href="http://www.thefactory.ro/articles/articles/explanatory....html#ticket">ticket
                            system proceedings</a> and make sure you provide all requested details, as well a clear issue description.<br/><br/>
                            To request an update use always only the <a
                                href="http://www.thefactory.ro/contact-us/product-update-request.html">update request
                            form</a>.<br/><br/>
                            Support our extension and further development with a forum post or an email to us. We are always happy to receive feedback and we are constantly improving our products taking under consideration all received requests. Please <a
                                href="http://extensions.joomla.org/extensions/e-commerce/auction/8772?qh=YToyOntpOjA7czo3OiJhdWN0aW9uIjtpOjE7czo4OiJhdWN0aW9ucyI7fQ%3D%3D">support
                            and vote</a> our extensions!
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top" width="240">
            <div id="cpanel">
                <?php
                $link = 'index.php?option=com_bids&amp;task=purgeauctions';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'purgeauctions.png', JText::_('COM_BIDS_PURGE_AUCTIONS'));

                ?>
            </div>
        </td>
    </tr>
    <tr>
        <td valign="top" >
            <div id="cpanel">
                <?php
                $link = 'index.php?option=com_bids&amp;task=backupform';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'backupauctions.png', JText::_('COM_BIDS_BACKUP_AUCTIONS'));

                $link = 'index.php?option=com_bids&amp;task=showrestoreform';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'restoreauctions.png', JText::_('COM_BIDS_RESTORE_AUCTIONS'));
                ?>
            </div>
        </td>
    </tr>
</table>
