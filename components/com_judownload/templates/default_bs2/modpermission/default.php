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
<div id="judl-container" class="jubootstrap component judl-container view-modpermission">

<h2 class="judl-view-title"><?php echo JText::_('COM_JUDOWNLOAD_MODERATOR_PERMISSION'); ?></h2>

<table class="table table-striped table-bordered">
<thead>
<tr>
	<th style="width: 200px" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD'); ?>
	</th>

	<th class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_VALUE'); ?>
	</th>
</tr>
</thead>

<tbody>
<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_NAME'); ?>
	</td>
	<td>
		<?php echo $this->item->name; ?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DESCRIPTION'); ?>
	</td>
	<td>
		<?php echo $this->item->description; ?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CATEGORIES'); ?>
	</td>
	<td>
		<?php
		echo $this->item->assignedCategories;
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_VIEW'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_view ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_VIEW_UNPUBLISHED'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_view_unpublished ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_CREATE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_create ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_EDIT'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_edit ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_EDIT_STATE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_edit_state ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_EDIT_OWN'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_edit_own ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_DELETE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_delete ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_DELETE_OWN'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_delete_own ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_DOWNLOAD'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_download ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_APPROVE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->document_approve ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_COMMENT_EDIT'); ?>
	</td>
	<td>
		<?php
		echo $this->item->comment_edit ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_COMMENT_EDIT_STATE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->comment_edit_state ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_COMMENT_DELETE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->comment_delete ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>

<tr>
	<td>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_COMMENT_APPROVE'); ?>
	</td>
	<td>
		<?php
		echo $this->item->comment_approve ? JText::_('JYES') : JText::_('JNO');
		?>
	</td>
</tr>
</tbody>
</table>
</div>
