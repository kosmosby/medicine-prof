<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="get" name="adminForm">
<table width="100%" cellpadding="0" cellspacing="1">
    <tr>
        <?php echo JText::_("FACTORY_SHOW_ONLY_FIELDS_FOR_PAGE"),":&nbsp;",$this->filter_html['page'];?>
    </tr>
</table>
	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5" align="center">#</th>
			<th width="80" align="center">
				<a href="javascript:submitbutton('fields.reorder')" class="saveorder" title="<?php echo JText::_('FACTORY_SAVE_ORDERING');?>" ></a>
			</th>
			<th class="title" width="10%" nowrap="nowrap"><?php echo JText::_("FACTORY_SECTION");?></th>
			<th class="title"><?php echo JText::_("FACTORY_FIELD_NAME");?></th>
			<th class="title" width="10%"><?php echo JText::_("FACTORY_HTML_ID"); ?></th>
			<th class="title" width="10%" nowrap="nowrap"><?php echo JText::_("FACTORY_FIELD_TYPE");?></th>
			<th class="title" width="3%"><?php echo JText::_("FACTORY_ENABLED");?></th>
			<th class="title" width="3%"><?php echo JText::_("FACTORY_COMPULSORY");?></th>
			<th class="title" width="3%"><?php echo JText::_("FACTORY_SEARCHABLE");?></th>
			<th class="title" width="3%"><?php echo JText::_("FACTORY_CATEGORY_FILTER");?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
		$row = $this->rows[$i];
		$link 	= 'index.php?option='.APP_EXTENSION.'&task=fields.edit&id='. $row->id;
		$link_page='index.php?option='.APP_EXTENSION.'&task=fields.listfields&filter_page='. $row->page;
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center"><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
            <td align="center">
                <input type="text"  size="5" name="order_<?php echo $row->id;?>" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
            </td>
			<td align="center"><a href="<?php echo $link_page;?>"><?php echo $row->page; ?></a></td>
			<td><a href="<?php echo $link; ?>"><?php echo htmlspecialchars($row->name, ENT_QUOTES); ?></a></td>
			<td align="center"><?php echo $row->id; ?></td>
			<td align="center"><?php echo $row->ftype; ?></td>
			<td align="center"><?php echo ($row->status)?JText::_("FACTORY_YES"):JText::_("FACTORY_NO"); ?></td>
			<td align="center"><?php echo ($row->compulsory)?JText::_("FACTORY_YES"):JText::_("FACTORY_NO"); ?></td>
			<td align="center"><?php echo ($row->search)?JText::_("FACTORY_YES"):JText::_("FACTORY_NO"); ?></td>
			<td align="center"><?php echo ($row->categoryfilter)?JText::_("FACTORY_YES"):JText::_("FACTORY_NO"); ?></td>
            
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="11">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
	</table>
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="fields.listfields" />
<input type="hidden" name="boxchecked" value="0" />
</form>
