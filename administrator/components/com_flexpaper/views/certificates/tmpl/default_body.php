<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">

		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>

        <td align="center">
            <?php echo $item->id; ?>
        </td>

		<td>
			<a href="index.php?option=com_flexpaper&task=certificate.edit&id=<?php echo $item->id;?>"><?php echo $item->name; ?></a>
		</td>
	</tr>
<?php endforeach; ?>