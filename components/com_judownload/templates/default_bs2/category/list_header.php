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

if ($this->allow_user_select_view_mode || $this->params->get('show_header_sort', 1) || $this->params->get('show_pagination', 1) == 1)
{
	?>
	<div class="sort-pagination row-fluid clearfix">
		<div class="pull-right">
			<?php
			if ($this->params->get('show_header_sort', 1))
			{
				?>
				<div id="sort" class="judl-sort">
					<select name="filter_order" class="input-medium sort-by" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->order_name_array, 'value', 'text', $this->document_order); ?>
					</select>
					<select name="filter_order_Dir" class="input-medium sort-direction"
					        onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->order_dir_array, 'value', 'text', $this->document_dir); ?>
					</select>
				</div>
			<?php
			} ?>

			<?php
			if ($this->params->get('show_pagination', 1))
			{
				?>
				<div class="pagination-wrap">
					<div class="limitbox">
						<div class="display-number"><?php echo JText::_('COM_JUDOWNLOAD_PAGINATION_DISPLAY'); ?></div>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
			<?php
			} ?>
		</div>
	</div>
<?php
} ?>