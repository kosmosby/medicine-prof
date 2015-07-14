<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<div class="sort-pagination clearfix">
	<?php
	if ($this->params->get('show_pagination', 1))
	{
		?>
		<div class="pull-right">
			<div class="limitbox">
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="judl-sort pull-right">
		<select name="filter_order" class="judl-order-sort input-medium" onchange="this.form.submit()">
			<?php echo JHtml::_('select.options', $this->order_name_array, 'value', 'text', $this->listOrder); ?>
		</select>
		<select name="filter_order_Dir" class="judl-order-dir input-small" onchange="this.form.submit()">
			<?php echo JHtml::_('select.options', $this->order_dir_array, 'value', 'text', $this->listDirn); ?>
		</select>
	</div>
</div>