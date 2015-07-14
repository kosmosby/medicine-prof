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

?><form name="adminForm">
	<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
	<input type="hidden" name="task" value="backup" />
	<fieldset>
		<legend><?php echo JText::_('Backup'); ?></legend>
        <table class="admintable paramlist">
            <tr>
                <td class="paramlist key">
                    <?php echo JText::_('Backups path'); ?>
                </td>
                <td>
                    <?php echo AUCTION_BACKUPS_PATH; ?>
                    <?php echo (is_writable(AUCTION_BACKUPS_PATH))?"<strong style='color:green;'>Writable</strong>":"<strong style='color:red;'>Not writable!</strong>";?>
                </td>
            </tr>
            <tr>
                <td class="paramlist key">
                    <label for="download_backup"><?php echo JText::_('Download'); ?></label>
                </td>
                <td class="paramlist value">
                    <input type="checkbox" name="download_backup" id="download_backup" value="1" />
                </td>
            </tr>
            <tr>
                <td class="paramlist key">
                    <label for="remove_backup"><?php echo JText::_('Remove backup after download'); ?></label>
                </td>
                <td class="paramlist value">
                    <input type="checkbox" name="remove_backup" id="remove_backup" value="1" />
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <br />
                    <br />
                    <div class="button1">
                        <div class="next">
                            <a href="javascript:document.adminForm.submit()">Backup!</a>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
	</fieldset>
</form>