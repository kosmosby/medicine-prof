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
<div class="judl-frontend-toolbar clearfix">
	<div class="pull-right">
		<button id="judl-edit-document" class="btn btn-default"><i
				class="fa fa-edit"></i> <?php echo JText::_('COM_JUDOWNLOAD_EDIT'); ?></button>
		<button id="judl-delete-documents" class="btn btn-default"><i
				class="fa fa-times"></i> <?php echo JText::_('COM_JUDOWNLOAD_REJECT'); ?></button>
	</div>
</div>

<div class="row">
	<div class="col-sm-6">
		<div class="judl-filter-search pull-left">
			<div class="input-group">
				<input type="text" name="filter_search" id="filter_search" class="form-control"
					   placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
					   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					   title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"/>
				<span class="input-group-btn">	   
					<button type="submit" class="btn btn-default"><?php echo JText::_('COM_JUDOWNLOAD_FILTER_SUBMIT'); ?></button>
					<button type="button" class="btn btn-default"
							onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_JUDOWNLOAD_FILTER_CLEAR'); ?></button>
				</span>			
			</div>			
		</div>
	</div>
	
	<div class="col-sm-6">
		<div class="judl-sort pull-right form-inline">
			<select name="filter_order" class="judl-order-sort input-medium" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->order_name_array, 'value', 'text', $this->listOrder); ?>
			</select>
			<select name="filter_order_Dir" class="judl-order-dir input-small" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->order_dir_array, 'value', 'text', $this->listDirn); ?>
			</select>
		</div>
	</div>
</div>