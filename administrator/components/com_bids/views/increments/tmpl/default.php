<?php
    defined('_JEXEC') or die('Restricted access');
    JHTML::_('behavior.formvalidation');
?>

<form action="index.php" name="addForm" method="post" class="form-validate" onSubmit="return document.formvalidator.isValid(this);">
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="increments.save" />
    <table class="paramlist admintable" border=0>
        <tr>
            <td class="paramlist key"><?php echo JText::_('COM_BIDS_RANGE'); ?>&nbsp;
                <input class="required" type="text" name="min_bid" style="width:50px;" value="" /> -
                <input class="required" type="text" name="max_bid" style="width:50px;" value="" />
                <?php echo JText::_('COM_BIDS_INCREMENT'); ?>&nbsp;
                <input class="required" type="text" name="value" style="width:35px;" value="" />&nbsp;&nbsp;
                <input type="submit" value="Add" />
            </td>
        </tr>
    </table>
</form>

<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="increments.listing" />
<input type="hidden" name="tmpl" value="component" />
<table class="adminlist">
<tr>
	<th><?php echo JText::_("COM_BIDS_FROM_BID");?></th>
	<th><?php echo JText::_("COM_BIDS_TO_BID");?></th>
	<th><?php echo ucfirst(JText::_("COM_BIDS_INCREMENT")); ?></th>
	<th>&nbsp;</th>
</tr>
<?php 
$odd=0;
foreach ($this->increments as $k => $increment) {?>
<tr class="row<?php echo ($odd=1-$odd);?>">

	<td><?php echo number_format($increment->min_bid,2);?></td>
	<td><?php echo number_format($increment->max_bid,2);?></td>
	<td><?php echo number_format($increment->value,2); ?></td>
    <td style="text-align: center;">
        <?php
            echo JHtml::link('index.php?option=com_bids&task=increments.edit&tmpl=component&cid='.$increment->id, JHtml::image( JUri::root().'components/com_bids/images/edit.png', 'edit', 'style="width: 16px;"' ) );
            echo JHtml::link('index.php?option=com_bids&task=increments.delete&tmpl=component&cid='.$increment->id, JHtml::image(JUri::root().'components/com_bids/images/delete.png', 'delete', 'style="width: 16px;"') );
        ?>
    </td>
</tr>
<?php } ?>
<tr>
	<th colspan="6" align="right">
		<?php echo $this->pagination->getListFooter();?>
	</th>
</tr>
</table>
</form>
