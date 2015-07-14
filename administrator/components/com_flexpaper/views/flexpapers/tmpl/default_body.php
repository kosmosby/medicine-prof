<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<a href="index.php?option=com_flexpaper&task=flexpaper.edit&id=<?php echo $item->id;?>"><?php echo $item->name; ?></a>
		</td>
		<td>
			<?php echo $item->course;?>
		</td>	
        <td>
            <?php echo $item->title;?>

        </td>
	</tr>
<?php endforeach; ?>