<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="currencies.listing" />
<input type="hidden" name="boxchecked" value="" />
<table class="adminlist" >
<tr>
	<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->currencies ); ?>);" /></th>
	<th width="80"><?php echo JText::_("FACTORY_CURRENCY");?></th>
    <th width="150"><?php echo JText::_("FACTORY_DEFAULT_CURRENCY");?></th>
    <th><?php echo JText::_("FACTORY_CONVERSION_RATE");?></th>
    <th width="20">
        <a href="javascript:submitbutton('currencies.reorder')" class="saveorder" title="<?php echo JText::_('FACTORY_SAVE_ORDERING');?>" ></a>
    </th>
	<th align="left" width="20"><?php echo JText::_("FACTORY_DELETE");?></th>
</tr>
<?php 
$odd=0;
foreach ($this->currencies as $k => $currency) {?>
<tr class="row<?php echo ($odd=1-$odd);?>">
	<td align="center">
		<?php echo JHTML::_('grid.id', $k, $currency->id );?>
	</td>
	<td><?php echo $currency->name;?></td>
	<?php if ($currency->default):?>
	    <td><?php echo JHtml::_('image.administrator','admin/featured.png');?></td>
	<?php else: ?>
        <td><a href="index.php?option=<?php echo APP_EXTENSION;?>&task=currencies.setdefault&cid=<?php echo $currency->id;?>"><?php echo JHtml::_('image.administrator','admin/disabled.png');?></a></td>
	<?php endif;?>
    <td><?php echo number_format($currency->convert,4);?></td>
    <td><input type="text"  size="5" name="order_<?php echo $currency->id;?>" value="<?php echo $currency->ordering;?>" class="text_area" style="text-align: center" /></td>
	<td align="center">
		<?php 
    		$link 	= 'index.php?option='.APP_EXTENSION.'&task=currencies.delete&cid[]='. $currency->id;
		?>
		<a href="<?php echo $link;?>" >
            <?php echo JHtml::_('image.administrator','admin/publish_x.png',JText::_( 'FACTORY_DELETE' ));?>
		</a>
	</td>
</tr>
<?php } ?>
</table>
</form>
