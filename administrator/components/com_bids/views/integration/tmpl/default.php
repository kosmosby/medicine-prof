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
<form name="adminForm" method="post" action="index.php">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="changeprofileintegration" />
    <div class='width-100 fltlft'>
    	<fieldset class="adminform">
    		<legend><?php echo JText::_( 'COM_BIDS_USER_PROFILE_SETTINGS' ); ?></legend> 
    		<table class="paramlist admintable" width="100%">
    			<tr>
    				<td width="20%" class="paramlist_key"><?php echo JText::_("COM_BIDS_USER_PROFILE");  ?>: </td>
    				<td width="80%">
                        <?php echo $this->profile_select_list;?>
                        <input name="save" type="submit" value="<?php echo JText::_('COM_BIDS_SAVE');?>"/>
    				</td>
    			</tr>
                <?php if ($this->current_profile_mode<>'component'):?>
    			<tr>
    				<td width="20%" class="paramlist_key"><?php echo JText::_("COM_BIDS_CONFIGURE_INTEGRATION");  ?>:</td>
    				<td width="80%">
                        <a href="<?php echo $this->configure_link;?>"><?php echo JText::_("COM_BIDS_SET_UP_FIELD_ASSIGNMENTS");  ?></a>
    				</td>
    			</tr>
                <?php endif; ?>
                <?php if ($this->current_profile_mode == 'component'): ?>
                <tr>
                    <td width="20%" class="paramlist_key"><?php echo JText::_("COM_BIDS_CONFIGURE_REGISTRATION");  ?>:
                    </td>
                    <td width="80%">
                        <?php echo $this->registration_select_list;?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
    				<td width="20%"></td>
    				<td width="80%">
    				</td>
    			</tr>
    		</table> 
        </fieldset>
    </div>
</form>
