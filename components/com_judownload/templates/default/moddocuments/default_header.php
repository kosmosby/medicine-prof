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
<div class="row">
	<div class="col-sm-6">
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
	<div class="col-sm-6">
		<button id="judl-edit-document" class="btn btn-default"><i
				class="fa fa-edit"></i> <?php echo JText::_('COM_JUDOWNLOAD_EDIT'); ?></button>
		<button id="judl-delete-documents" class="btn btn-default"><i
				class="fa fa-trash-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?></button>
		<button id="judl-publish-documents" class="btn btn-default"><i
				class="fa fa-check"></i> <?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?></button>
		<button id="judl-unpublish-documents" class="btn btn-default"><i
				class="fa fa-close"></i> <?php echo JText::_('COM_JUDOWNLOAD_UNPUBLISH'); ?></button>
	</div>
</div>
<br/>
<div class="row">
	<div class="col-sm-12">
		<div class="form-inline">
			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CATEGORY'); ?></option>
				<?php echo JHtml::_('select.options', $this->options_cat, 'value', 'text', $this->state->get('filter.category_id')); ?>
			</select>
			<select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_STATE'); ?></option>
				<?php echo JHtml::_('select.options', $this->options_published, 'value', 'text', $this->state->get('filter.published')); ?>
			</select>
			<select name="filter_access" class="inputbox input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_ACCESS'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
			</select>
			<select name="filter_language" class="inputbox input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_LANGUAGE'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
			</select>
			<select name="filter_order" class="judl-order-sort input-medium" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->order_name_array, 'value', 'text', $this->listOrder); ?>
			</select>
			<select name="filter_order_Dir" class="judl-order-dir input-small" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->order_dir_array, 'value', 'text', $this->listDirn); ?>
			</select>
		</div>
	</div>
</div>
