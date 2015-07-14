<?php
    defined('_JEXEC') or die('Restricted access');
    JHTML::_('behavior.formvalidation');
?>

<form action="index.php" name="addForm" method="post" class="form-validate"
      onsubmit="return document.formvalidator.isValid(this);">
    <input type="hidden" name="option" value="com_bids"/>
    <input type="hidden" name="task" value="shipping.save"/>
    <table class="paramlist admintable" border=0>
        <tr>
            <td class="paramlist key"><?php echo JText::_('COM_BIDS_SHIPPING_ZONE'); ?>&nbsp;
                <input class="required" type="text" name="name" style="width:150px;" value=""/>
                <input type="submit" value="Add"/>
            </td>
        </tr>
    </table>
</form>

<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="shipping.listing" />
<input type="hidden" name="tmpl" value="component" />
<table class="adminlist">
<tr>
	<th colspan="3" align="right">
		<?php echo JText::_("COM_BIDS_FILTER");?>&nbsp;<input type="text" name="searchfilter" value="<?php echo $this->search;?>" />
	</th>
</tr>

<?php 
$odd=0;
foreach ($this->zones as $k => $zone) {?>
<tr class="row<?php echo ($odd=1-$odd);?>">
	<td><?php echo $zone->name;?></td>
	<td align="center">
		<?php 
		$link_delete 	= 'index.php?option=com_bids&task=shipping.delete&tmpl=component&cid[]='. $zone->id;
		$link_edit 	= 'index.php?option=com_bids&task=shipping.edit&tmpl=component&cid[]='. $zone->id;
		?>
		<a href="<?php echo $link_edit;?>" title="<?php echo JText::_('COM_BIDS_EDIT_SHIPPING');?>">
		  <?php echo JHtml::image(JUri::root() . 'components/com_bids/images/edit.png', 'edit', 'style="width: 16px;"'); ?>
		</a>
		<a href="<?php echo $link_delete;?>" title="<?php echo JText::_('COM_BIDS_DELETE_SHIPPING');?>" onclick="return confirm('<?php echo JText::_('COM_BIDS_CONFIRM_DELETE');?>');" >
            <?php echo JHtml::image(JUri::root() . 'components/com_bids/images/delete.png', 'delete', 'style="width: 16px;"'); ?>
		</a>
	</td>
</tr>
<?php } ?>
<tr>
	<th colspan="3" align="right">
		<?php echo $this->pagination->getListFooter();?>
	</th>
</tr>
</table>
</form>
