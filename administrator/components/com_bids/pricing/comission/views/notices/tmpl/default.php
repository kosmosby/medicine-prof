<?php defined('_JEXEC') or die('Restricted access'); ?>
<div style="float:left">
    <form name="adminForm" action="index.php" method="get">
        <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
        <input type="hidden" name="task" value="pricing.sendnotice" />
        <input type="hidden" name="item" value="comission" />
        <input type="hidden" name="userid" value="0" />
        <input type="hidden" name="commissionType" value="<?php echo JRequest::getVar('commissionType'); ?>" />
        <button><?php echo JText::_("COM_BIDS_NOTIFY_ALL_USERS");?></button>
        <table class="adminlist" >
        <thead>
        <tr>
        	<th width="5"><?php echo JText::_("COM_BIDS_USERID");?></th>
            <th width="150"><?php echo JText::_("COM_BIDS_USER_NAME");?></th>
            <th width="150"><?php echo JText::_("COM_BIDS_BALANCE");?></th>
            <th width="*%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $odd=0;
        if (!count($this->userbalances)) echo "<td colspan=4>",JText::_("COM_BIDS_THERE_ARE_NO_USERS_SELECTED"),"</td>";
        foreach ($this->userbalances as $userbalance) {?>
            <tr class="row<?php echo ($odd=1-$odd);?>">
            	<td align="center">
                    <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=detailUser&cid[]=<?php echo $userbalance->userid?>">
                        <?php echo $userbalance->userid;?>
                    </a>
                </td>
            	<td>
                    <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=detailUser&cid[]=<?php echo $userbalance->userid?>">
                        <?php echo $userbalance->username;?>
                    </a>
                </td>
                <td style="text-align: right;"><?php echo number_format($userbalance->balance,2);?>&nbsp;<?php echo $userbalance->currency;?></td>
                <td><button onclick="javascript:this.form.userid.value='<?php echo $userbalance->userid;?>'"><?php echo JText::_("COM_BIDS_SEND_NOTICE");?></button></td>
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
